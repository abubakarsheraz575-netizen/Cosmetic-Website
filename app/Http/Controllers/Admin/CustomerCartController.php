<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerCart;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerCartController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);
        $carts = CustomerCart::with('user:id,name,email')
            ->when(trim($filters['q'] ?? '') !== '', function ($query) use ($filters): void {
                $term = trim($filters['q']);
                $query->whereHas('user', fn ($user) => $user
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->orderByDesc('last_activity')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();

        $this->attachCartSummaries($carts->getCollection());

        return view('admin.pages.ecommerce.customer-carts.index', compact('carts', 'filters'));
    }

    public function show(CustomerCart $customerCart): View
    {
        $customerCart->load('user:id,name,email');
        $this->attachCartSummaries(collect([$customerCart]));

        return view('admin.pages.ecommerce.customer-carts.show', compact('customerCart'));
    }

    private function attachCartSummaries($carts): void
    {
        $productIds = $carts->flatMap(fn (CustomerCart $cart) => collect($cart->items)
            ->pluck('product_id'))
            ->filter()
            ->unique()
            ->values();
        $products = Product::whereKey($productIds)
            ->with(['images', 'skuVariants.options.attribute'])
            ->get()
            ->keyBy('id');

        foreach ($carts as $cart) {
            $lines = collect($cart->items)->map(function (array $item) use ($products): array {
                $product = $products->get((int) ($item['product_id'] ?? 0));
                $variant = $product && ! empty($item['variant_id'])
                    ? $product->skuVariants->firstWhere('id', (int) $item['variant_id'])
                    : null;
                $options = $variant?->options->map(fn ($option) => $option->attribute?->name.': '.$option->value)
                    ->filter()
                    ->implode(' · ');
                $quantity = max(0, (int) ($item['qty'] ?? 0));
                $price = $product ? (float) $product->final_price : 0.0;

                return [
                    'name' => $product?->name ?? 'Unavailable product',
                    'variant' => $options ?: null,
                    'sku' => $variant?->sku ?? $product?->sku,
                    'quantity' => $quantity,
                    'price' => $price,
                    'line_total' => round($price * $quantity, 2),
                    'image' => $product?->images->first()
                        ? asset('storage/'.$product->images->first()->path)
                        : null,
                    'available' => (bool) $product && (bool) $product->is_active && $product->review_status === 'approved',
                ];
            })->filter(fn (array $line) => $line['quantity'] > 0)->values();

            $cart->setAttribute('cart_lines', $lines);
            $cart->setAttribute('item_count', $lines->sum('quantity'));
            $cart->setAttribute('cart_total', round($lines->sum('line_total'), 2));
        }
    }
}
