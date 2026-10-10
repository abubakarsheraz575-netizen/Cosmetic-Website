<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcommerceController extends Controller
{
    public function reports()
    {
        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->with(['items.returns', 'items.product.images'])
            ->latest()
            ->get();
        $monthlyLabels = [];
        $monthlyRevenue = [];
        $monthlyOrders = [];

        for ($monthsAgo = 7; $monthsAgo >= 0; $monthsAgo--) {
            $month = now()->subMonths($monthsAgo);
            $monthlyLabels[] = $month->format('M');
            $monthOrders = $paidOrders->filter(fn (Order $order) => $order->created_at->isSameMonth($month));
            $monthlyRevenue[] = round((float) $monthOrders->sum('total'), 2);
            $monthlyOrders[] = $monthOrders->count();
        }

        $statusLabels = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        $statusCounts = array_map(
            fn (string $status) => $paidOrders->where('status', strtolower($status))->count(),
            $statusLabels
        );
        $returnsByStatus = OrderReturn::query()->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')->pluck('total', 'status');

        return view('admin.pages.ecommerce.reports', [
            'metrics' => [
                'revenue' => (float) $paidOrders->sum('total'),
                'orders' => $paidOrders->count(),
                'average' => $paidOrders->isEmpty() ? 0 : (float) $paidOrders->avg('total'),
                'returns' => OrderReturn::count(),
            ],
            'monthlyLabels' => $monthlyLabels,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyOrders' => $monthlyOrders,
            'statusLabels' => $statusLabels,
            'statusCounts' => $statusCounts,
            'returnsByStatus' => $returnsByStatus,
            'recentOrders' => $paidOrders->take(12),
        ]);
    }

    public function returns()
    {
        $returns = OrderReturn::with(['user', 'order', 'item'])->latest()->get();

        return view('admin.pages.ecommerce.returns', [
            'returns' => $returns,
            'metrics' => [
                'total' => $returns->count(),
                'pending' => $returns->where('status', 'pending')->count(),
                'approved' => $returns->where('status', 'approved')->count(),
                'refunded' => $returns->where('status', 'refunded')->count(),
            ],
        ]);
    }

    public function editReturn(OrderReturn $orderReturn)
    {
        $orderReturn->load(['user', 'order', 'item']);

        return view('admin.pages.ecommerce.return-edit', ['returnRequest' => $orderReturn]);
    }

    public function updateReturn(Request $request, OrderReturn $orderReturn)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,approved,rejected,refunded',
            'admin_note' => 'nullable|string|max:2000',
        ]);

        $orderReturn->update($data);

        return redirect()->route('admin.ecommerce.returns.edit', $orderReturn)
            ->with('success', 'Return request updated.');
    }

    public function destroyReturn(OrderReturn $orderReturn)
    {
        $orderReturn->delete();

        return redirect()->route('admin.ecommerce.returns')->with('success', 'Return request deleted.');
    }

    public function shipments()
    {
        $orders = Order::where('payment_status', 'paid')
            ->with(['items.product.images', 'user'])
            ->latest()
            ->get();

        return view('admin.pages.ecommerce.shipments', [
            'orders' => $orders,
            'metrics' => [
                'total' => $orders->count(),
                'pending' => $orders->whereIn('status', ['pending', 'processing'])->count(),
                'shipped' => $orders->where('status', 'shipped')->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
                'cancelled' => $orders->where('status', 'cancelled')->count(),
                'revenue' => (float) $orders->sum('total'),
            ],
        ]);
    }

    public function invoices()
    {
        $orders = Order::where('payment_status', 'paid')
            ->with(['items.returns', 'items.product.images'])
            ->latest()
            ->get();

        return view('admin.pages.ecommerce.invoices', [
            'orders' => $orders,
            'metrics' => [
                'total' => $orders->count(),
                'revenue' => (float) $orders->sum('total'),
            ],
        ]);
    }

    public function editOrder(Order $order)
    {
        $order->load(['items.product.images', 'items.returns']);

        return view('admin.pages.ecommerce.order-edit', ['order' => $order]);
    }

    public function updateOrder(Request $request, Order $order)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'shipping' => 'required|numeric|min:0|max:99999999.99',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|distinct',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1|max:100000',
            'items.*.product_price' => 'required|numeric|min:0|max:99999999.99',
        ]);

        $items = collect($data['items']);
        $submittedIds = $items->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $existingIds = $order->items()->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        abort_unless($submittedIds === $existingIds, 422);

        DB::transaction(function () use ($data, $items, $order): void {
            foreach ($items as $itemData) {
                $item = $order->items()->findOrFail($itemData['id']);
                $item->update([
                    'product_name' => $itemData['product_name'],
                    'qty' => $itemData['qty'],
                    'product_price' => $itemData['product_price'],
                    'line_total' => round($itemData['qty'] * $itemData['product_price'], 2),
                ]);
            }

            $subtotal = (float) $order->items()->sum('line_total');
            $discountValue = $order->coupon_discount_value;
            $discount = match ($order->coupon_discount_type) {
                'percent' => round($subtotal * (float) $discountValue / 100, 2),
                'fixed' => min($subtotal, (float) $discountValue),
                default => min((float) $order->discount, $subtotal),
            };
            $taxable = max(0, round($subtotal - $discount, 2));
            $taxBreakdown = collect($order->tax_breakdown ?? [])->map(function (array $tax) use ($taxable): array {
                $tax['amount'] = round($taxable * (float) ($tax['percentage'] ?? 0) / 100, 2);
                return $tax;
            })->all();
            $taxTotal = round(array_sum(array_column($taxBreakdown, 'amount')), 2);
            $order->update([
                ...collect($data)->except('items')->all(),
                'paid_at' => $data['payment_status'] === 'paid' && $order->payment_status !== 'paid'
                    ? now()
                    : $order->paid_at,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_breakdown' => $taxBreakdown,
                'tax_total' => $taxTotal,
                'total' => round($taxable + $taxTotal + (float) $data['shipping'], 2),
            ]);
        });

        return redirect()->route('admin.ecommerce.invoices')->with('success', 'Invoice and order details updated.');
    }
}
