<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceMessage;
use App\Models\MarketplaceWithdrawal;
use App\Models\Setting;
use App\Models\User;
use App\Mail\MarketplacePayoutStatusNotification;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function stores(Request $request): View
    {
        return $this->vendorDirectory($request, 'all');
    }

    public function vendors(Request $request): View
    {
        return $this->vendorDirectory($request, 'verified');
    }

    public function unverifiedVendors(Request $request): View
    {
        return $this->vendorDirectory($request, 'unverified');
    }

    private function vendorDirectory(Request $request, string $verification): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['active', 'blocked'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);

        $vendors = $this->vendorQuery()
            ->when($verification === 'verified', fn ($query) => $query->whereNotNull('users.email_verified_at'))
            ->when($verification === 'unverified', fn ($query) => $query->whereNull('users.email_verified_at'))
            ->when(trim($filters['q'] ?? '') !== '', function ($query) use ($filters) {
                $search = trim($filters['q']);
                $query->where(fn ($query) => $query->where('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%")
                    ->orWhere('users.phone', 'like', "%{$search}%"));
            })
            ->when(isset($filters['status']), fn ($query) => $query->where('users.is_blocked', $filters['status'] === 'blocked'))
            ->withCount('submittedProducts')
            ->withCount([
                'submittedProducts as pending_products_count' => fn ($query) => $query->where('review_status', 'pending'),
                'submittedProducts as published_products_count' => fn ($query) => $query
                    ->where('review_status', 'approved')->where('is_active', true),
            ])
            ->orderByDesc('users.created_at')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        $title = match ($verification) {
            'verified' => 'Vendors',
            'unverified' => 'Unverified vendors',
            default => 'Stores',
        };

        return view('admin.pages.marketplace.stores', compact('vendors', 'filters', 'verification', 'title'));
    }

    public function vendor(User $user): View
    {
        abort_if($user->isAdmin(), 404);
        abort_unless($this->vendorQuery()->whereKey($user->id)->exists(), 404);

        $user->loadCount([
            'submittedProducts',
            'submittedProducts as pending_products_count' => fn ($query) => $query->where('review_status', 'pending'),
            'submittedProducts as published_products_count' => fn ($query) => $query
                ->where('review_status', 'approved')->where('is_active', true),
        ]);
        $products = $user->submittedProducts()->with(['category', 'size', 'images'])->latest()->paginate(10);

        return view('admin.pages.marketplace.vendor', compact('user', 'products'));
    }

    public function updateVendorStatus(Request $request, User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);
        abort_unless($this->vendorQuery()->whereKey($user->id)->exists(), 404);
        $data = $request->validate(['is_blocked' => ['required', 'boolean']]);
        $user->update(['is_blocked' => $data['is_blocked']]);

        return back()->with('success', $user->is_blocked ? 'Vendor access blocked.' : 'Vendor access restored.');
    }

    public function destroyVendor(User $user): RedirectResponse
    {
        abort_if($user->isAdmin(), 404);
        abort_unless($this->vendorQuery()->whereKey($user->id)->exists(), 404);
        $products = $user->submittedProducts()->with(['images', 'variants'])->get();
        $imagePaths = $products
            ->flatMap(fn ($product) => $product->images->pluck('path')->merge($product->variants->pluck('image_path')))
            ->push($user->profile_photo)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $name = $user->name;

        DB::transaction(function () use ($products, $user): void {
            foreach ($products as $product) {
                $product->delete();
            }
            $user->delete();
        });

        if ($imagePaths !== []) {
            MediaLibrary::deleteIfUnused($imagePaths);
        }

        return redirect()->route('admin.marketplace.stores')->with('success', "{$name} and their submitted products were deleted.");
    }

    public function withdrawals(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'processing', 'completed', 'rejected'])],
        ]);
        $counts = MarketplaceWithdrawal::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');
        $withdrawals = MarketplaceWithdrawal::with(['vendor', 'order.items.product'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();
        $commissionRate = Setting::marketplaceCommissionRate();

        return view('admin.pages.marketplace.withdrawals', compact('withdrawals', 'counts', 'commissionRate', 'filters'));
    }

    public function updateWithdrawal(Request $request, MarketplaceWithdrawal $withdrawal): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['processing', 'completed', 'rejected'])],
        ]);
        $allowedTransitions = [
            'pending' => ['processing', 'rejected'],
            'processing' => ['completed', 'rejected'],
            'completed' => [],
            'rejected' => [],
        ];
        abort_unless(in_array($data['status'], $allowedTransitions[$withdrawal->status] ?? [], true), 422);

        if ($data['status'] !== 'rejected') {
            $order = $withdrawal->order;
            abort_unless(
                $order
                    && $order->payment_status === 'paid'
                    && $order->paid_at
                    && $order->transaction_id
                    && hash_equals((string) $withdrawal->payment_transaction_id, (string) $order->transaction_id),
                422,
                'Payout approval requires a verified paid order and matching payment transaction.'
            );
        }

        $withdrawal->update($data);
        if ($withdrawal->vendor) {
            try {
                Mail::to($withdrawal->vendor->email)->send(new MarketplacePayoutStatusNotification($withdrawal->fresh(['order'])));
            } catch (\Throwable $exception) {
                report($exception);
                Log::error('Marketplace payout status email could not be delivered.', [
                    'withdrawal_id' => $withdrawal->id,
                    'recipient' => $withdrawal->vendor->email,
                    'status' => $data['status'],
                ]);
            }
        }

        return back()->with('success', 'Payout request status updated.');
    }

    public function updateCommissionRate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100', 'decimal:0,2'],
        ]);
        Setting::set('marketplace_commission_rate', number_format((float) $data['commission_rate'], 2, '.', ''));

        return back()->with('success', 'Marketplace commission rate saved. It applies to new payout requests.');
    }

    public function destroyWithdrawal(MarketplaceWithdrawal $withdrawal): RedirectResponse
    {
        abort_if($withdrawal->status === 'completed', 422, 'Completed payout records are kept for audit history.');
        $withdrawal->delete();

        return back()->with('success', 'Withdrawal request deleted.');
    }

    public function messages(Request $request): View|JsonResponse
    {
        $vendors = $this->vendorQuery()->withCount('submittedProducts')->orderBy('name')->get();
        $selected = null;
        $messages = collect();

        if ($request->filled('vendor')) {
            $request->validate(['vendor' => ['required', 'integer']]);
            $selected = $vendors->firstWhere('id', (int) $request->query('vendor'));
            abort_unless($selected, 404);
            MarketplaceMessage::query()
                ->where('vendor_id', $selected->id)
                ->whereNull('read_at')
                ->whereHas('sender', fn ($query) => $query->where('role', '!=', 'admin'))
                ->update(['read_at' => now()]);
            $messages = MarketplaceMessage::with('sender')
                ->where('vendor_id', $selected->id)
                ->oldest()
                ->get();
            if ($request->expectsJson()) {
                return response()->json([
                    'messages' => $messages->map(fn (MarketplaceMessage $message) => $this->serializeMessage($message)),
                    'last_id' => $messages->max('id') ?? 0,
                ]);
            }
        }

        $unreadCounts = MarketplaceMessage::query()
            ->selectRaw('vendor_id, count(*) as total')
            ->whereNull('read_at')
            ->whereHas('sender', fn ($query) => $query->where('role', '!=', 'admin'))
            ->groupBy('vendor_id')
            ->pluck('total', 'vendor_id');

        return view('admin.pages.marketplace.messages', compact('vendors', 'selected', 'messages', 'unreadCounts'));
    }

    public function sendMessage(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'vendor_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['required', 'string', 'max:5000'],
        ]);
        $vendor = $this->vendorQuery()->whereKey($data['vendor_id'])->firstOrFail();

        $message = MarketplaceMessage::create([
            'vendor_id' => $vendor->id,
            'sender_id' => $request->user()->id,
            'body' => trim($data['body']),
        ]);
        $message->load('sender');

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->serializeMessage($message)], 201);
        }
        return redirect()->route('admin.marketplace.messages', ['vendor' => $vendor->id])
            ->with('success', 'Message sent to '.$vendor->name.'.');
    }

    public function messageFeed(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vendor' => ['required', 'integer'],
            'after' => ['nullable', 'integer', 'min:0'],
        ]);
        $vendor = $this->vendorQuery()->whereKey($data['vendor'])->firstOrFail();
        $messages = MarketplaceMessage::with('sender')
            ->where('vendor_id', $vendor->id)
            ->where('id', '>', $data['after'] ?? 0)
            ->oldest()
            ->get();

        MarketplaceMessage::where('vendor_id', $vendor->id)
            ->whereNull('read_at')
            ->whereHas('sender', fn ($query) => $query->where('role', '!=', 'admin'))
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn (MarketplaceMessage $message) => $this->serializeMessage($message)),
            'unread_count' => MarketplaceMessage::where('vendor_id', $vendor->id)
                ->whereNull('read_at')
                ->whereHas('sender', fn ($query) => $query->where('role', '!=', 'admin'))
                ->count(),
        ]);
    }

    private function serializeMessage(MarketplaceMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name ?? 'Marketplace team',
            'created_at' => $message->created_at?->toIso8601String(),
            'mine' => $message->sender_id === auth()->id(),
        ];
    }

    private function vendorQuery()
    {
        return User::query()
            ->where(function ($query) {
                $query->whereNull('users.role')->orWhere('users.role', '!=', 'admin');
            })
            ->whereHas('submittedProducts');
    }
}
