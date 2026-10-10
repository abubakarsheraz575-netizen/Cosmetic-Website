<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Support\AdminEmailNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.product.images', 'items.returns'])
            ->latest()
            ->get();

        return view('user.pages.orders', ['orders' => $orders]);
    }

    public function returns(Request $request)
    {
        $returns = OrderReturn::where('user_id', auth()->id())
            ->with(['order', 'item'])
            ->latest()
            ->get();

        $eligibleOrders = Order::where('user_id', auth()->id())
            ->where('status', 'delivered')
            ->where('payment_status', 'paid')
            ->with(['items.returns', 'items.product.images'])
            ->latest()
            ->get();
        $targetOrder = null;
        if ($request->filled('order')) {
            $targetOrder = Order::where('user_id', auth()->id())
                ->where('status', 'delivered')
                ->where('payment_status', 'paid')
                ->findOrFail($request->integer('order'));
        }

        return view('user.pages.returns', [
            'returns' => $returns,
            'eligibleOrders' => $eligibleOrders,
            'targetOrder' => $targetOrder,
            'metrics' => [
                'total' => $returns->count(),
                'pending' => $returns->where('status', 'pending')->count(),
                'approved' => $returns->where('status', 'approved')->count(),
                'refunded' => $returns->where('status', 'refunded')->count(),
            ],
        ]);
    }

    public function requestReturn(Request $request, Order $order, OrderItem $orderItem)
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        abort_unless($order->status === 'delivered' && $order->payment_status === 'paid', 422, 'Only paid, delivered orders can be returned.');
        abort_unless($order->items()->whereKey($orderItem->id)->exists(), 404);

        $data = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $orderItem->qty,
            'reason' => 'required|string|min:5|max:2000',
        ]);

        $return = DB::transaction(function () use ($data, $order, $orderItem, $request): ?OrderReturn {
            $lockedItem = OrderItem::query()
                ->where('order_id', $order->id)
                ->whereKey($orderItem->id)
                ->lockForUpdate()
                ->firstOrFail();

            $activeReturnExists = $lockedItem->returns()
                ->whereIn('status', ['pending', 'approved', 'refunded'])
                ->exists();
            if ($activeReturnExists) {
                return null;
            }

            $return = OrderReturn::create([
                'return_number' => 'RT-' . Str::upper(Str::random(20)),
                'order_id' => $order->id,
                'order_item_id' => $lockedItem->id,
                'user_id' => $request->user()->id,
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'status' => 'pending',
            ]);
            $return->update(['return_number' => 'RT-' . str_pad((string) $return->id, 6, '0', STR_PAD_LEFT)]);

            return $return;
        });

        if (! $return) {
            return back()->withErrors(['return' => 'A return request for this item is already in progress or completed.']);
        }

        $adminNotified = AdminEmailNotifier::send('New product return request', [
            'Return' => $return->return_number,
            'Customer' => $request->user()->name.' <'.$request->user()->email.'>',
            'Order' => $order->order_number,
            'Product' => $orderItem->product_name,
            'Quantity' => (string) $return->quantity,
            'Reason' => $return->reason,
            'Review requests' => route('admin.ecommerce.returns'),
        ]);

        return back()
            ->with(
                'success',
                'Return request ' . $return->return_number . ' submitted.'
                    .($adminNotified ? '' : ' The admin email alert could not be delivered.')
            );
    }
}