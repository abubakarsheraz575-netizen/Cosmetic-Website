<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items.product.images'])->latest();

        if ($request->filled('search')) {
            $s = trim($request->get('search'));
            $query->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status') && $request->get('status') !== 'all') {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment') && $request->get('payment') !== 'all') {
            $query->where('payment_status', $request->get('payment'));
        }

        $orders = $query->get();

        $stats = [
            'total'     => Order::count(),
            'revenue'   => (float) Order::where('payment_status', 'paid')->sum('total'),
            'unpaid'    => Order::where('payment_status', 'pending')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'count'  => $orders->count(),
                'stats'  => $stats,
                'orders' => $orders->map(fn ($o) => $this->mapOrder($o))->values(),
            ]);
        }

        return view('admin.pages.orders', ['stats' => $stats]);
    }

    private function mapOrder(Order $o): array
    {
        $first = $o->items->first();
        $img = null;
        if ($first && $first->product && $first->product->images->first()) {
            $img = asset('storage/' . $first->product->images->first()->path);
        }

        return [
            'id'     => $o->id,
            'number' => $o->order_number,
            'date'   => $o->created_at->format('M d, Y'),
            'customer' => [
                'name'     => $o->name,
                'email'    => $o->email,
                'initials' => collect(explode(' ', trim($o->name)))->filter()->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode(''),
            ],
            'items' => [
                'count' => $o->items->count(),
                'first' => $first ? ['name' => $first->product_name, 'emoji' => $first->emoji, 'img' => $img] : null,
                'more'  => max($o->items->count() - 1, 0),
            ],
            'total'   => (float) $o->total,
            'payment' => ['method' => $o->payment_method, 'status' => $o->payment_status],
            'status'  => $o->status,
            'eta'     => $o->created_at->copy()->addDays(7)->format('M d'),
        ];
    }

    public function show(Order $order)
    {
        $order->load(['items.product.images']);

        return response()->json([
            'status' => 'success',
            'order'  => [
                'id'        => $order->id,
                'number'    => $order->order_number,
                'date'      => $order->created_at->format('M d, Y — H:i'),
                'name'      => $order->name,
                'email'     => $order->email,
                'phone'     => $order->phone,
                'address'   => $order->address,
                'city'      => $order->city,
                'postal'    => $order->postal_code,
                'country'   => $order->country,
                'notes'     => $order->notes,
                'subtotal'  => (float) $order->subtotal,
                'shipping'  => (float) $order->shipping,
                'total'     => (float) $order->total,
                'method'    => $order->payment_method,
                'payStatus' => $order->payment_status,
                'tx'        => $order->transaction_id,
                'status'    => $order->status,
                'eta5'      => $order->created_at->copy()->addDays(5)->format('D, M j'),
                'eta7'      => $order->created_at->copy()->addDays(7)->format('D, M j'),
                'items'     => $order->items->map(function ($it) {
                    $img = $it->product && $it->product->images->first()
                        ? asset('storage/' . $it->product->images->first()->path)
                        : null;
                    return [
                        'name'  => $it->product_name,
                        'qty'   => $it->qty,
                        'price' => (float) $it->product_price,
                        'line'  => (float) $it->line_total,
                        'emoji' => $it->emoji,
                        'img'   => $img,
                    ];
                }),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Order ' . $order->order_number . ' marked as ' . $data['status'] . '.',
        ]);
    }
}