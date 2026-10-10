<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentReportController extends Controller
{
    public function transactions(Request $request): View
    {
        return $this->renderPayments($request, 'transactions');
    }

    public function logs(Request $request): View
    {
        return $this->renderPayments($request, 'logs');
    }

    private function renderPayments(Request $request, string $view): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'provider' => ['nullable', 'in:stripe,paystack'],
            'per_page' => ['nullable', 'in:10,25,50,100'],
        ]);

        $paymentQuery = $this->paymentQuery($filters);
        if ($view === 'logs') {
            $paymentQuery->whereRaw('LOWER(payment_method) IN (?, ?)', ['stripe', 'paystack']);
            if (empty($filters['provider'])) {
                $paymentQuery->whereRaw('1 = 0');
            }
        }
        $payments = $paymentQuery
            ->with(['user' => fn (BelongsTo $query) => $query
                ->select('id', 'name', 'email'), 'items.product.images'])
            ->latest('paid_at')
            ->latest('created_at')
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();

        $paidOrders = Order::where('payment_status', 'paid');
        $stats = [
            'transactions' => (clone $paidOrders)->count(),
            'revenue' => (float) (clone $paidOrders)->sum('total'),
            'stripe' => (clone $paidOrders)->whereRaw('LOWER(payment_method) = ?', ['stripe'])->count(),
            'paystack' => (clone $paidOrders)->whereRaw('LOWER(payment_method) = ?', ['paystack'])->count(),
        ];

        return view('admin.pages.payment.index', [
            'payments' => $payments,
            'filters' => $filters,
            'stats' => $stats,
            'isLogs' => $view === 'logs',
        ]);
    }

    public function create(): View
    {
        return $this->manualPaymentForm(new Order(), 'Record a payment');
    }

    public function edit(Order $order): View
    {
        abort_unless($order->is_manual_payment && $order->payment_status === 'paid', 404);
        $order->load('items.product.images');

        return $this->manualPaymentForm($order, 'Edit manual payment');
    }

    private function manualPaymentForm(Order $order, string $title): View
    {
        $customers = User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'email']);
        $products = Product::with('images')->orderBy('name')->get(['id', 'name', 'price']);

        return view('admin.pages.payment.form', compact('order', 'title', 'customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateManualPayment($request);
        DB::transaction(function () use ($data, $request): void {
            $order = Order::create([
                'user_id' => $data['user_id'] ?? null,
                'order_number' => 'MANUAL-PENDING-'.Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'address' => 'Manually recorded payment',
                'city' => 'N/A',
                'postal_code' => 'N/A',
                'country' => 'N/A',
                'subtotal' => $data['amount'],
                'shipping' => 0,
                'total' => $data['amount'],
                'payment_method' => 'manual',
                'payment_status' => 'paid',
                'status' => 'processing',
                'transaction_id' => $data['transaction_id'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'is_manual_payment' => true,
                'payment_note' => $data['payment_note'] ?? null,
                'payment_recorded_by' => $request->user()->id,
            ]);
            $order->update([
                'order_number' => 'MAN-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'transaction_id' => $data['transaction_id'] ?? 'MAN-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
            ]);
            $product = isset($data['product_id']) ? Product::findOrFail($data['product_id']) : null;
            $order->items()->create([
                'product_id' => $product?->id,
                'product_name' => $product?->name ?? ($data['description'] ?? 'Manual payment'),
                'product_price' => $data['amount'],
                'qty' => 1,
                'line_total' => $data['amount'],
                'emoji' => $product?->emoji,
            ]);
        });

        return redirect()->route('admin.payment.transactions')->with('success', 'Manual paid transaction recorded.');
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->is_manual_payment && $order->payment_status === 'paid', 404);
        $data = $this->validateManualPayment($request, $order);

        DB::transaction(function () use ($data, $request, $order): void {
            $product = isset($data['product_id']) ? Product::findOrFail($data['product_id']) : null;
            $item = $order->items()->firstOrFail();
            $order->update([
                'user_id' => $data['user_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'subtotal' => $data['amount'],
                'total' => $data['amount'],
                'transaction_id' => $data['transaction_id'] ?? 'MAN-'.str_pad((string) $order->id, 6, '0', STR_PAD_LEFT),
                'paid_at' => $data['paid_at'] ?? $order->paid_at,
                'payment_note' => $data['payment_note'] ?? null,
                'payment_recorded_by' => $request->user()->id,
            ]);
            $item->update([
                'product_id' => $product?->id,
                'product_name' => $product?->name ?? ($data['description'] ?? 'Manual payment'),
                'product_price' => $data['amount'],
                'qty' => 1,
                'line_total' => $data['amount'],
                'emoji' => $product?->emoji,
            ]);
        });

        return redirect()->route('admin.payment.transactions')->with('success', 'Manual transaction updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        abort_unless($order->payment_status === 'paid', 404);
        $order->delete();

        return back()->with('success', 'Paid transaction deleted.');
    }

    private function validateManualPayment(Request $request, ?Order $order = null): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'user')],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:99999999.99'],
            'transaction_id' => ['nullable', 'string', 'max:255', Rule::unique('orders', 'transaction_id')->ignore($order?->id)],
            'paid_at' => ['nullable', 'date'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'description' => ['nullable', 'string', 'max:255', 'required_without:product_id'],
            'payment_note' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function paymentQuery(array $filters): Builder
    {
        return Order::query()
            ->where('payment_status', 'paid')
            ->when($filters['provider'] ?? null, fn (Builder $query, string $provider) => $query
                ->whereRaw('LOWER(payment_method) = ?', [$provider]))
            ->when(trim($filters['q'] ?? '') !== '', function (Builder $query) use ($filters): void {
                $term = trim($filters['q']);
                $query->where(function (Builder $query) use ($term): void {
                    $query->where('order_number', 'like', '%'.$term.'%')
                        ->orWhere('transaction_id', 'like', '%'.$term.'%')
                        ->orWhere('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%');
                });
            });
    }
}
