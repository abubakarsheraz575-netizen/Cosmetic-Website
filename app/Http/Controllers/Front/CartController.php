<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\CustomerCart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function show()
    {
        return view('front-end.pages.cart', [
            'items' => self::itemsData(),
            'subtotal' => self::subtotal(),
        ]);
    }

    private static function lineKey(int $productId, ?int $variantId): string
    {
        return $productId.':'.($variantId ?? 0);
    }

    public static function cart(): array
    {
        $sessionCart = session('cart', []);
        $user = Auth::user();
        if ($user && ! $user->isAdmin() && (int) session('customer_cart_loaded_for') !== (int) $user->id) {
            $savedCart = CustomerCart::where('user_id', $user->id)->value('items') ?? [];
            $sessionCart = self::mergeCarts($savedCart, is_array($sessionCart) ? $sessionCart : []);
            session([
                'cart' => $sessionCart,
                'customer_cart_loaded_for' => $user->id,
            ]);
        }

        return self::normalizeCart(is_array($sessionCart) ? $sessionCart : []);
    }

    private static function normalizeCart(array $cart): array
    {
        $normalized = [];
        foreach ($cart as $key => $item) {
            if (! is_array($item)) {
                continue;
            }
            $productId = (int) ($item['product_id'] ?? $key);
            $variantId = isset($item['variant_id']) && (int) $item['variant_id'] > 0
                ? (int) $item['variant_id']
                : null;
            if ($productId > 0) {
                $normalized[self::lineKey($productId, $variantId)] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => (int) ($item['qty'] ?? 0),
                ];
            }
        }

        return $normalized;
    }

    private static function mergeCarts(array $savedCart, array $sessionCart): array
    {
        $merged = self::normalizeCart($savedCart);
        foreach (self::normalizeCart($sessionCart) as $key => $item) {
            if (! isset($merged[$key]) || $item['qty'] > $merged[$key]['qty']) {
                $merged[$key] = $item;
            }
        }

        return $merged;
    }

    private static function syncCustomerCart(): void
    {
        $user = Auth::user();
        if (! $user || $user->isAdmin()) {
            return;
        }

        $cart = self::cart();
        if ($cart === []) {
            CustomerCart::where('user_id', $user->id)->delete();

            return;
        }

        CustomerCart::updateOrCreate(
            ['user_id' => $user->id],
            ['items' => $cart, 'last_activity' => now()]
        );
    }

    public static function clearPersistentCart(): void
    {
        $user = Auth::user();
        if ($user && ! $user->isAdmin()) {
            CustomerCart::where('user_id', $user->id)->delete();
        }
    }

    public static function count(): int
    {
        return collect(self::cart())->sum('qty');
    }

    public static function subtotal(): float
    {
        $cart = self::cart();
        if (empty($cart)) {
            return 0.0;
        }

        $products = Product::whereIn('id', collect($cart)->pluck('product_id')->unique())
            ->where('is_active', true)
            ->where('review_status', 'approved')
            ->with('skuVariants')
            ->get()
            ->keyBy('id');
        $sum = 0;
        foreach ($cart as $item) {
            $product = $products->get($item['product_id']);
            if ($product) {
                $sum += $product->final_price * $item['qty'];
            }
        }

        return round($sum, 2);
    }

    public static function itemsData(): array
    {
        $cart = self::cart();
        if (empty($cart)) {
            return [];
        }

        $products = Product::whereIn('id', collect($cart)->pluck('product_id')->unique())
            ->where('is_active', true)
            ->where('review_status', 'approved')
            ->with(['images', 'skuVariants.options.attribute'])
            ->get()
            ->keyBy('id');
        $items = [];

        foreach ($cart as $key => $cartItem) {
            $product = $products->get($cartItem['product_id']);
            if (! $product) {
                continue;
            }
            $variant = $cartItem['variant_id']
                ? $product->skuVariants->firstWhere('id', $cartItem['variant_id'])
                : null;
            if ($cartItem['variant_id'] && ! $variant) {
                continue;
            }
            $options = $variant?->options->map(fn ($option) => [
                'attribute' => $option->attribute?->name,
                'value' => $option->value,
            ])->values()->all() ?? [];
            $items[] = [
                'id' => $product->id,
                'line_key' => $key,
                'variant_id' => $variant?->id,
                'name' => $product->name,
                'variant_label' => collect($options)->map(fn ($option) => $option['attribute'].': '.$option['value'])->implode(' · '),
                'sku' => $variant?->sku ?? $product->sku,
                'slug' => $product->slug,
                'price' => (float) $product->final_price,
                'qty' => (int) $cartItem['qty'],
                'stock' => (int) ($variant?->stock ?? $product->stock),
                'shipping_weight' => $product->shipping_weight,
                'weight_unit' => $product->weight_unit,
                'emoji' => $product->emoji,
                'img' => $product->images->first() ? asset('storage/'.$product->images->first()->path) : null,
                'line' => round($product->final_price * $cartItem['qty'], 2),
            ];
        }

        return $items;
    }

    public function items()
    {
        self::syncCustomerCart();

        return response()->json([
            'status' => 'success',
            'count' => self::count(),
            'subtotal' => self::subtotal(),
            'items' => self::itemsData(),
        ]);
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'sku_variant_id' => ['nullable', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);
        $product = Product::whereKey($data['product_id'])
            ->where('is_active', true)
            ->where('review_status', 'approved')
            ->with('skuVariants')
            ->first();
        if (! $product) {
            return response()->json(['status' => 'error', 'message' => 'Product not available.'], 404);
        }

        $variant = null;
        if ($product->skuVariants->isNotEmpty()) {
            $variant = isset($data['sku_variant_id'])
                ? $product->skuVariants->firstWhere('id', (int) $data['sku_variant_id'])
                : null;
            if (! $variant) {
                return response()->json(['status' => 'error', 'message' => 'Choose a valid product option before adding this item.'], 422);
            }
        } elseif (isset($data['sku_variant_id'])) {
            return response()->json(['status' => 'error', 'message' => 'This product does not have sellable options.'], 422);
        }

        $stock = (int) ($variant?->stock ?? $product->stock);
        if ($stock <= 0) {
            return response()->json(['status' => 'error', 'message' => 'This product is out of stock.'], 422);
        }

        $variantId = $variant?->id;
        $key = self::lineKey($product->id, $variantId);
        $cart = self::cart();
        $current = $cart[$key]['qty'] ?? 0;
        $qty = (int) ($data['qty'] ?? 1);
        if ($current + $qty > $stock) {
            return response()->json(['status' => 'error', 'message' => 'Only '.$stock.' left in stock.'], 422);
        }
        $cart[$key] = ['product_id' => $product->id, 'variant_id' => $variantId, 'qty' => $current + $qty];
        session(['cart' => $cart]);
        self::syncCustomerCart();

        return response()->json([
            'status' => 'success',
            'message' => $product->name.' added to your bag',
            'count' => self::count(),
            'subtotal' => self::subtotal(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'sku_variant_id' => ['nullable', 'integer'],
            'qty' => ['required', 'integer', 'min:0', 'max:99'],
        ]);
        $key = self::lineKey((int) $data['product_id'], isset($data['sku_variant_id']) ? (int) $data['sku_variant_id'] : null);
        $cart = self::cart();
        if (! isset($cart[$key])) {
            return response()->json(['status' => 'error', 'message' => 'Item not in bag.'], 404);
        }
        if ($data['qty'] === 0) {
            unset($cart[$key]);
            session(['cart' => $cart]);
            self::syncCustomerCart();

            return response()->json([
                'status' => 'success',
                'count' => self::count(),
                'subtotal' => self::subtotal(),
                'items' => self::itemsData(),
            ]);
        }

        $product = Product::whereKey($data['product_id'])
            ->where('is_active', true)
            ->where('review_status', 'approved')
            ->with('skuVariants')
            ->first();
        if (! $product) {
            unset($cart[$key]);
            session(['cart' => $cart]);
            self::syncCustomerCart();

            return response()->json(['status' => 'error', 'message' => 'Product not available.'], 404);
        }
        $variant = isset($data['sku_variant_id'])
            ? $product->skuVariants->firstWhere('id', (int) $data['sku_variant_id'])
            : null;
        if (isset($data['sku_variant_id']) && ! $variant) {
            return response()->json(['status' => 'error', 'message' => 'This product option is no longer available.'], 404);
        }
        $stock = (int) ($variant?->stock ?? $product->stock);
        if ($data['qty'] > $stock) {
            return response()->json(['status' => 'error', 'message' => 'Only '.$stock.' in stock.'], 422);
        }
        $cart[$key]['qty'] = $data['qty'];
        session(['cart' => $cart]);
        self::syncCustomerCart();

        return response()->json([
            'status' => 'success',
            'count' => self::count(),
            'subtotal' => self::subtotal(),
            'items' => self::itemsData(),
        ]);
    }

    public function remove(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'sku_variant_id' => ['nullable', 'integer'],
        ]);
        $cart = self::cart();
        unset($cart[self::lineKey((int) $data['product_id'], isset($data['sku_variant_id']) ? (int) $data['sku_variant_id'] : null)]);
        session(['cart' => $cart]);
        self::syncCustomerCart();

        return response()->json([
            'status' => 'success',
            'count' => self::count(),
            'subtotal' => self::subtotal(),
            'items' => self::itemsData(),
        ]);
    }
}
