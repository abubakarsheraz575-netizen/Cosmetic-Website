<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceWithdrawal;
use App\Models\Order;
use App\Models\Setting;
use App\Support\AdminEmailNotifier;
use App\Support\MarketplacePayoutCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user();
        abort_unless($vendor->submittedProducts()->exists(), 404);

        MarketplaceMessage::query()
            ->where('vendor_id', $vendor->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn ($query) => $query->where('role', 'admin'))
            ->update(['read_at' => now()]);

        $messages = MarketplaceMessage::with('sender')
            ->where('vendor_id', $vendor->id)
            ->oldest()
            ->get();

        return view('user.pages.marketplace', compact('messages'));
    }

    public function withdrawals(Request $request): View
    {
        $vendor = $request->user();
        abort_unless($vendor->submittedProducts()->exists(), 404);
        $withdrawals = $vendor->marketplaceWithdrawals()->with('order')->latest()->get();
        $commissionRate = Setting::marketplaceCommissionRate();
        $paidOrders = Order::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('paid_at')
            ->whereHas('items.product', fn ($query) => $query->where('submitted_by', $vendor->id))
            ->with(['items.product'])
            ->latest('paid_at')
            ->get();
        $sales = $paidOrders->map(function (Order $order) use ($vendor, $commissionRate, $withdrawals): ?array {
            $payout = MarketplacePayoutCalculator::forVendor($order, $vendor, $commissionRate);
            if (! $payout || $payout['payout_amount'] <= 0) {
                return null;
            }

            $request = $withdrawals->first(
                fn (MarketplaceWithdrawal $withdrawal) => (int) $withdrawal->order_id === $order->id
            );
            $canRequest = ! $request || $request->status === 'rejected';

            return ['order' => $order, 'payout' => $payout, 'request' => $request, 'can_request' => $canRequest];
        })->filter()->values();

        return view('user.pages.marketplace-withdrawals', compact('withdrawals', 'sales', 'commissionRate'));
    }

    public function sendMessage(Request $request): RedirectResponse|JsonResponse
    {
        $vendor = $request->user();
        abort_unless($vendor->submittedProducts()->exists(), 404);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $message = MarketplaceMessage::create([
            'vendor_id' => $vendor->id,
            'sender_id' => $vendor->id,
            'body' => trim($data['body']),
        ]);
        $adminNotified = AdminEmailNotifier::send('New marketplace message from a vendor', [
            'Vendor' => $vendor->name.' <'.$vendor->email.'>',
            'Message' => $message->body,
            'Open conversation' => route('admin.marketplace.messages', ['vendor' => $vendor->id]),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $this->serializeMessage($message, true),
                'admin_notified' => $adminNotified,
                'notification_warning' => $adminNotified ? null : 'Your message was saved, but its admin email alert could not be delivered.',
            ], 201);
        }
        return back()->with(
            'success',
            'Your message was sent to marketplace support.'
                .($adminNotified ? '' : ' Its admin email alert could not be delivered.')
        );
    }

    public function messageFeed(Request $request): JsonResponse
    {
        $vendor = $request->user();
        abort_unless($vendor->submittedProducts()->exists(), 404);
        $data = $request->validate(['after' => ['nullable', 'integer', 'min:0']]);
        $messages = MarketplaceMessage::with('sender')
            ->where('vendor_id', $vendor->id)
            ->where('id', '>', $data['after'] ?? 0)
            ->oldest()
            ->get();

        MarketplaceMessage::where('vendor_id', $vendor->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn ($query) => $query->where('role', 'admin'))
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn (MarketplaceMessage $message) => $this->serializeMessage($message, $message->sender_id === $vendor->id)),
        ]);
    }

    public function requestWithdrawal(Request $request): RedirectResponse
    {
        $vendor = $request->user();
        abort_unless($vendor->submittedProducts()->exists(), 404);
        $data = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'payout_method' => ['required', 'string', Rule::in(['Bank transfer', 'PayPal', 'Other'])],
            'payout_details' => ['required', 'string', 'max:2000'],
        ]);

        $withdrawal = DB::transaction(function () use ($data, $vendor): MarketplaceWithdrawal {
            $order = Order::query()
                ->whereKey($data['order_id'])
                ->lockForUpdate()
                ->first();
            if (! $order || $order->payment_status !== 'paid' || ! $order->paid_at || ! $order->transaction_id) {
                throw ValidationException::withMessages([
                    'order_id' => ['Payout requests are only available for orders with a verified payment transaction.'],
                ]);
            }
            $order->load('items.product');

            $existingRequest = MarketplaceWithdrawal::query()
                ->where('vendor_id', $vendor->id)
                ->where('order_id', $order->id)
                ->whereIn('status', ['pending', 'processing', 'completed'])
                ->lockForUpdate()
                ->exists();
            if ($existingRequest) {
                throw ValidationException::withMessages([
                    'order_id' => ['A payout request for this order is already being reviewed or has been paid.'],
                ]);
            }

            $payout = MarketplacePayoutCalculator::forVendor(
                $order,
                $vendor,
                Setting::marketplaceCommissionRate()
            );
            if (! $payout || $payout['payout_amount'] < 0.01) {
                throw ValidationException::withMessages([
                    'order_id' => ['This order has no eligible marketplace earnings for your account.'],
                ]);
            }

            return $vendor->marketplaceWithdrawals()->create([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_transaction_id' => $order->transaction_id,
                'order_paid_at' => $order->paid_at,
                'sales_amount' => $payout['sales_amount'],
                'coupon_discount_share' => $payout['coupon_discount_share'],
                'commission_rate' => $payout['commission_rate'],
                'commission_amount' => $payout['commission_amount'],
                'amount' => $payout['payout_amount'],
                'payout_method' => $data['payout_method'],
                'payout_details' => trim($data['payout_details']),
                'status' => 'pending',
            ]);
        });
        $adminNotified = AdminEmailNotifier::send('Marketplace payout requested', [
            'Vendor' => $vendor->name.' <'.$vendor->email.'>',
            'Order' => $withdrawal->order_number,
            'Verified customer payment reference' => $withdrawal->payment_transaction_id,
            'Eligible product sales after order discount' => '£'.number_format((float) $withdrawal->sales_amount, 2),
            'Marketplace commission' => number_format((float) $withdrawal->commission_rate, 2).'%',
            'Payout requested' => '£'.number_format((float) $withdrawal->amount, 2),
            'Review request' => route('admin.marketplace.withdrawals', ['status' => 'pending']),
        ]);

        return redirect()->route('user.marketplace.withdrawals')
            ->with('success', 'Paid sale payout request submitted for admin review.'
                .($adminNotified ? '' : ' The admin email alert could not be delivered.'));
    }

    private function serializeMessage(MarketplaceMessage $message, bool $mine): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $mine ? 'You' : 'Marketplace team',
            'created_at' => $message->created_at?->toIso8601String(),
            'mine' => $mine,
        ];
    }
}
