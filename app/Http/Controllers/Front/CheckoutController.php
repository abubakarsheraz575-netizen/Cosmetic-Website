<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\OrderReceipt;
use App\Mail\MarketplaceSaleNotification;
use App\Models\Coupon;
use App\Models\CouponRedemption;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductSkuVariant;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Support\CheckoutTotals;
use App\Support\CheckoutShipping;
use App\Support\AdminEmailNotifier;
use App\Support\PaystackSecretKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    const HTTP_CONNECT = 10;
    const HTTP_TIMEOUT = 25;

    /* ============================================================
       ⭐ DYNAMIC SHIPPING SETTINGS — admin Shipping page se
    ============================================================ */
    private function shipSettings(): array
    {
        $freeOver = Setting::get('free_ship_over');
        $cost = Setting::get('ship_cost');

        return [
            'free_over' => is_numeric($freeOver) ? (float) $freeOver : 50.0,
            'cost'      => is_numeric($cost) ? (float) $cost : 4.99,
            'included_weight_kg' => (float) Setting::get('ship_included_weight_kg', '1'),
            'surcharge_per_kg' => (float) Setting::get('ship_surcharge_per_kg', '0'),
            'min_days'  => (int)   (Setting::get('delivery_min_days') ?: 5),
            'max_days'  => (int)   (Setting::get('delivery_max_days') ?: 7),
        ];
    }

    private function shippingBreakdown(float $subtotal, array $items, array $settings): array
    {
        $weightKg = 0.0;
        foreach ($items as $item) {
            $weightKg += CheckoutShipping::weightInKilograms(
                isset($item['shipping_weight']) ? (float) $item['shipping_weight'] : null,
                (string) ($item['weight_unit'] ?? 'g')
            ) * (int) ($item['qty'] ?? 0);
        }

        return CheckoutShipping::calculate($subtotal, $weightKg, $settings);
    }

    /* ============================================================
       CHECKOUT PAGE
    ============================================================ */
    public function show()
    {
        $items = CartController::itemsData();
        if (empty($items)) {
            return redirect()->route('shop');
        }

        $ship     = $this->shipSettings();
        $subtotal = CartController::subtotal();
        $shippingBreakdown = $this->shippingBreakdown($subtotal, $items, $ship);
        $shipping = $shippingBreakdown['total'];
        $couponCode = session('checkout_coupon_code');
        $coupon = $couponCode ? Coupon::available()->where('code', $couponCode)->first() : null;
        if ($couponCode && !$coupon) {
            session()->forget('checkout_coupon_code');
        }
        $totals = CheckoutTotals::calculate($subtotal, $shipping, $coupon, TaxRate::where('is_active', true)->orderBy('id')->get());

        return view('front-end.pages.checkout', [
            'items'            => $items,
            'subtotal'         => $subtotal,
            'shipping'         => $shipping,
            'shippingBase'     => $shippingBreakdown['base'],
            'weightSurcharge'  => $shippingBreakdown['weight_surcharge'],
            'billableExtraKg'  => $shippingBreakdown['billable_extra_kg'],
            'cartWeightKg'     => $shippingBreakdown['weight_kg'],
            'shipIncludedWeightKg' => $ship['included_weight_kg'],
            'total'            => $totals['total'],
            'discount'         => $totals['discount'],
            'taxTotal'         => $totals['tax_total'],
            'taxBreakdown'     => $totals['tax_breakdown'],
            'couponCode'       => $coupon?->code,
            'shipFreeOver'     => $ship['free_over'],
            'shipCost'         => $ship['cost'],
            'delMin'           => $ship['min_days'],
            'delMax'           => $ship['max_days'],
            'stripeKey'        => Setting::get('stripe_key'),
            'stripeReady'      => (bool) Setting::get('stripe_key') && (bool) Setting::get('stripe_secret'),
            'paystackKey'      => Setting::get('paystack_key'),
            'paystackReady'    => (bool) Setting::get('paystack_key') && (bool) Setting::get('paystack_secret'),
            'paystackCurrency' => $this->paystackCurrency(),
            'paystackRate'     => $this->paystackRate(),
            'checkoutError'    => session('checkout_error'),
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['coupon_code' => 'nullable|string|max:32']);
        $couponCode = Str::upper(trim((string) ($data['coupon_code'] ?? '')));
        $coupon = null;

        if ($couponCode !== '') {
            $coupon = Coupon::available()->where('code', $couponCode)->first();
            if (!$coupon) {
                return response()->json(['status' => 'error', 'message' => 'That coupon is invalid, expired, inactive, or fully redeemed.'], 422);
            }

            session(['checkout_coupon_code' => $coupon->code]);
        } else {
            session()->forget('checkout_coupon_code');
        }

        $subtotal = CartController::subtotal();
        $ship = $this->shipSettings();
        $shippingBreakdown = $this->shippingBreakdown($subtotal, CartController::itemsData(), $ship);
        $shipping = $shippingBreakdown['total'];
        $totals = CheckoutTotals::calculate($subtotal, $shipping, $coupon, TaxRate::where('is_active', true)->orderBy('id')->get());

        return response()->json([
            'status' => 'success',
            'message' => $coupon ? 'Coupon applied.' : 'Coupon removed.',
            'coupon_code' => $coupon?->code,
            'removed' => !$coupon,
            'discount' => $totals['discount'],
            'tax_total' => $totals['tax_total'],
            'tax_breakdown' => $totals['tax_breakdown'],
            'shipping' => $totals['shipping'],
            'weight_surcharge' => $shippingBreakdown['weight_surcharge'],
            'total' => $totals['total'],
        ]);
    }

    /* ============================================================
       PLACE ORDER (AJAX) — Stripe + Paystack
    ============================================================ */
    public function process(Request $request)
    {
        Log::info('════ [v5] CHECKOUT PROCESS START ════', ['method' => $request->input('payment_method')]);

        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'email'          => 'required|email|max:150',
            'phone'          => 'required|string|max:30',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'postal_code'    => 'required|string|max:20',
            'country'        => 'required|string|max:100',
            'notes'          => 'nullable|string|max:1000',
            'payment_method' => 'required|in:stripe,paystack',
            'coupon_code'    => 'nullable|string|max:32',
        ]);

        $cart = CartController::cart();
        if (empty($cart)) {
            return response()->json(['status' => 'error', 'message' => 'Your bag is empty.'], 422);
        }

        $method = $data['payment_method'];

        if ($method === 'stripe') {
            $secret = $this->stripeSecret();
            if (!$secret) {
                return response()->json(['status' => 'error', 'message' => 'Card payment is not configured yet. Please add your Stripe keys in the admin panel.'], 422);
            }
        }

        $order = DB::transaction(function () use ($cart, $data, $method) {
            $productIds = collect($cart)->pluck('product_id')->unique()->all();
            $products = Product::whereKey($productIds)
                ->with(['images', 'skuVariants.options.attribute'])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $items = [];
            foreach ($cart as $cartItem) {
                $product = $products->get((int) $cartItem['product_id']);
                if (!$product || !$product->is_active || $product->review_status !== 'approved') {
                    throw ValidationException::withMessages(['cart' => 'An item in your bag is no longer available.']);
                }
                $variant = isset($cartItem['variant_id'])
                    ? $product->skuVariants->firstWhere('id', (int) $cartItem['variant_id'])
                    : null;
                if ($product->skuVariants->isNotEmpty() && ! $variant) {
                    throw ValidationException::withMessages(['cart' => 'Choose a valid product option before checkout.']);
                }
                $qty = (int) ($cartItem['qty'] ?? 0);
                $availableStock = (int) ($variant?->stock ?? $product->stock);
                if ($qty < 1 || $qty > $availableStock) {
                    throw ValidationException::withMessages(['cart' => 'Only '.$availableStock.' left of '.$product->name.'.']);
                }
                $price = (float) $product->final_price;
                $items[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'qty' => $qty,
                    'price' => $price,
                    'line' => round($price * $qty, 2),
                    'shipping_weight' => $product->shipping_weight,
                    'weight_unit' => $product->weight_unit,
                ];
            }

            $subtotal = round(array_sum(array_column($items, 'line')), 2);
            $ship = $this->shipSettings();
            $shipping = $this->shippingBreakdown($subtotal, $items, $ship)['total'];
            $couponCode = Str::upper(trim((string) ($data['coupon_code'] ?? '')));
            $coupon = null;
            if ($couponCode !== '') {
                if (Str::upper((string) session('checkout_coupon_code')) !== $couponCode) {
                    throw ValidationException::withMessages(['coupon_code' => 'Apply and validate this coupon before placing your order.']);
                }
                $coupon = Coupon::where('code', $couponCode)->lockForUpdate()->first();
                if (!$coupon || !$coupon->is_active || !$coupon->starts_at->lte(now()) || !$coupon->ends_at->gte(now()) || $coupon->uses_count + $coupon->reserved_count >= $coupon->usage_limit) {
                    throw ValidationException::withMessages(['coupon_code' => 'That coupon is invalid, expired, inactive, or fully redeemed.']);
                }
            } else {
                session()->forget('checkout_coupon_code');
            }

            $taxes = TaxRate::where('is_active', true)->orderBy('id')->lockForUpdate()->get();
            $totals = CheckoutTotals::calculate($subtotal, $shipping, $coupon, $taxes);
            $order = Order::create([
                'user_id' => auth()->id(),
                'order_number' => 'TMP-'.Str::uuid(),
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'],
                'country' => $data['country'],
                'notes' => $data['notes'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'coupon_code' => $totals['coupon_code'],
                'coupon_discount_type' => $coupon?->discount_type,
                'coupon_discount_value' => $coupon?->discount_value,
                'shipping' => $totals['shipping'],
                'tax_total' => $totals['tax_total'],
                'tax_breakdown' => $totals['tax_breakdown'],
                'total' => $totals['total'],
                'payment_method' => $method,
            ]);
            $order->update(['order_number' => 'GL-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_price' => $item['price'],
                    'qty' => $item['qty'],
                    'line_total' => $item['line'],
                    'emoji' => $item['product']->emoji,
                    'product_sku_variant_id' => $item['variant']?->id,
                    'product_sku' => $item['variant']?->sku ?? $item['product']->sku,
                    'product_barcode' => $item['variant']?->barcode ?? $item['product']->barcode,
                    'variant_options' => $item['variant']?->options->map(fn ($option) => [
                        'attribute' => $option->attribute?->name,
                        'value' => $option->value,
                    ])->values()->all() ?? [],
                    'shipping_weight' => $item['product']->shipping_weight,
                    'weight_unit' => $item['product']->weight_unit,
                    'shipping_length' => $item['product']->shipping_length,
                    'shipping_width' => $item['product']->shipping_width,
                    'shipping_height' => $item['product']->shipping_height,
                    'dimension_unit' => $item['product']->dimension_unit,
                ]);
            }

            if ($coupon) {
                $reserved = Coupon::whereKey($coupon->id)
                    ->where('is_active', true)
                    ->where('starts_at', '<=', now())
                    ->where('ends_at', '>=', now())
                    ->whereRaw('uses_count + reserved_count < usage_limit')
                    ->increment('reserved_count');
                if ($reserved !== 1) {
                    throw ValidationException::withMessages(['coupon_code' => 'That coupon has just reached its usage limit.']);
                }
                CouponRedemption::create([
                    'coupon_id' => $coupon->id,
                    'order_id' => $order->id,
                    'coupon_code' => $coupon->code,
                    'discount_amount' => $totals['discount'],
                    'status' => 'reserved',
                ]);
            }

            return $order;
        });
        session()->forget('checkout_coupon_code');
        Log::info('[v5] ORDER CREATED', ['order_id' => $order->id]);

        if ($method === 'paystack') {
            return $this->initPaystack($order, $data['email']);
        }

        /* ---------- STRIPE (+ timeout) ---------- */
        try {
            $resp = Http::asForm()
                ->withToken($secret)
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->post('https://api.stripe.com/v1/payment_intents', [
                    'amount'                  => (int) round((float) $order->total * 100),
                    'currency'                => 'gbp',
                    'payment_method_types[0]' => 'card',
                    'description'             => 'Cosmetics order ' . $order->order_number,
                    'metadata[order_id]'      => $order->id,
                    'metadata[order_number]'  => $order->order_number,
                ]);
        } catch (\Throwable $e) {
            Log::error('[v5] STRIPE unreachable: ' . $e->getMessage());
            $this->markFailed($order);
            return response()->json(['status' => 'error', 'message' => 'Stripe API not reachable — check your internet connection.'], 422);
        }

        $stripeIntentId = $resp->json('id');
        $stripeClientSecret = $resp->json('client_secret');
        if ($resp->failed() || $resp->json('error') || !$stripeIntentId || !$stripeClientSecret) {
            $this->markFailed($order);
            return response()->json([
                'status'  => 'error',
                'message' => $resp->json('error.message') ?? 'Stripe could not start this payment.',
            ], 422);
        }

        $order->update(['transaction_id' => $stripeIntentId]);

        return response()->json([
            'status'        => 'success',
            'order_id'      => $order->id,
            'payment_intent_id' => $stripeIntentId,
            'client_secret' => $stripeClientSecret,
        ]);
    }

    /* ============================================================
       PAYSTACK INITIALIZE (+ limit check + authorization_url)
    ============================================================ */
    private function initPaystack(Order $order, string $email)
    {
        $psKey    = Setting::get('paystack_key');
        $psSecret = $this->paystackSecret();

        if (!$psKey || !$psSecret) {
            $this->markFailed($order);
            return response()->json(['status' => 'error', 'message' => 'Paystack is not configured. Please add Paystack keys in the admin panel.'], 422);
        }
        $publicMode = PaystackSecretKey::mode($psKey, 'pk');
        $secretMode = PaystackSecretKey::mode($psSecret, 'sk');
        if (! $publicMode || ! $secretMode || $publicMode !== $secretMode) {
            $this->markFailed($order);

            return response()->json([
                'status' => 'error',
                'message' => $this->paystackAuthorizationMessage(),
            ], 422);
        }

        $currency    = $this->paystackCurrency();
        $rate        = $this->paystackRate();
        $amountMinor = (int) round($order->total * $rate * 100);

         /* ⭐⭐⭐ PER-TRANSACTION LIMIT CHECK — Paystack ke cryptic
           "Watch your spending" error ki jagah CLEAN message.
           Paystack limits (major units — phir minor mein convert):
           NGN ₦10M · GHS ₵100k · ZAR R1M · KES KSh5M · USD $10k */
        $majorLimits = [
            'NGN' => 10000000,   /* ₦10 million */
            'GHS' => 100000,     /* ₵100 thousand */
            'ZAR' => 1000000,    /* R1 million */
            'KES' => 5000000,    /* KSh5 million */
            'USD' => 10000,      /* $10 thousand */
        ];
        /* AUTO = merchant ki apni currency (99% Nigerian = NGN) — NGN limit use karo.
           Paystack ki sab se badi limit NGN ki hai, isliye AUTO pe wahi safe default. */
        $majorLimit = ($currency !== 'AUTO' && isset($majorLimits[$currency])) ? $majorLimits[$currency] : $majorLimits['NGN'];
        $limit = (int) round($majorLimit * 100);   /* minor units (kobo/cents) */

        if ($amountMinor > $limit) {
            $this->markFailed($order);
            Log::warning('[v5-PAYSTACK] amount limit exceeded', [
                'order_id'    => $order->id,
                'amountMinor' => $amountMinor,
                'limit'       => $limit,
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Ye order Paystack ki per-transaction limit se zyada hai (£' . number_format($order->total, 2) . '). Please Stripe card payment select karein — ya order chote hisson mein complete karein.',
            ], 422);
        }

        $reference = 'GL-' . $order->id . '-' . strtoupper(Str::random(8));
        $order->update(['transaction_id' => $reference]);

        $payload = [
            'email'        => $email,
            'amount'       => $amountMinor,
            'reference'    => $reference,
            'callback_url' => route('checkout.paystack.callback'),
            'metadata'     => [
                'order_id'       => $order->id,
                'order_number'   => $order->order_number,
                'gbp_equivalent' => '£' . number_format($order->total, 2),
            ],
        ];
        if ($currency !== 'AUTO') {
            $payload['currency'] = $currency;
        }

        try {
            $resp = Http::withToken($psSecret)
                ->acceptJson()
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->post('https://api.paystack.co/transaction/initialize', $payload);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[v5-PAYSTACK] connection failed: ' . $e->getMessage());
            $this->markFailed($order);
            return response()->json(['status' => 'error', 'message' => 'Could not reach Paystack — check your internet / VPN and try again.'], 422);
        } catch (\Throwable $e) {
            Log::error('[v5-PAYSTACK] initialize failed: ' . $e->getMessage());
            $this->markFailed($order);
            return response()->json(['status' => 'error', 'message' => 'Paystack request failed — please try again.'], 422);
        }

        Log::info('[v5-PAYSTACK] response', ['status' => $resp->json('status'), 'message' => $resp->json('message')]);

        $authorizationUrl = $resp->json('data.authorization_url');
        $providerReference = $resp->json('data.reference');
        if (
            $resp->failed()
            || !($resp->json('status') ?? false)
            || !$authorizationUrl
            || ($providerReference && $providerReference !== $reference)
        ) {
            $this->markFailed($order);
            if ($this->isPaystackAuthorizationError($resp)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Paystack rejected the secret key. In Admin → Payment Settings, save the raw secret key only (for example, sk_test_…), without “Authorization” or “Bearer”; the server formats the authentication header automatically.',
                ], 422);
            }
            return response()->json([
                'status'  => 'error',
                'message' => $resp->json('message') ?? 'Paystack could not start this payment.',
            ], 422);
        }

        $popupCurrency = ($currency !== 'AUTO') ? $currency : null;

        return response()->json([
            'status'   => 'success',
            'order_id' => $order->id,
            'paystack' => [
                'public_key'        => $psKey,
                'reference'         => $reference,
                'email'             => $email,
                'amount'            => $amountMinor,
                'currency'          => $popupCurrency,
                'authorization_url' => $authorizationUrl,
            ],
        ]);
    }

    /* ============================================================
       ⭐ PAYSTACK CALLBACK — hosted page se wapas (GET)
    ============================================================ */
    public function paystackCallback(Request $request)
    {
        $reference = (string) $request->query('reference');
        Log::info('[v5-PAYSTACK-CALLBACK] hit', ['reference' => $reference]);

        if (!$reference || !preg_match('/^GL-(\d+)-/', $reference, $m)) {
            return redirect()->route('shop')->with('checkout_error', 'Invalid payment reference.');
        }

        $order = Order::find($m[1]);
        if (!$order) {
            return redirect()->route('shop')->with('checkout_error', 'Order not found.');
        }

        if ($order->transaction_id !== $reference) {
            return redirect()->route('shop')->with('checkout_error', 'Invalid payment reference.');
        }

        if ($order->payment_status === 'paid') {
            $this->clearCart();
            return redirect()->route('order.success', $order->order_number);
        }

        $psSecret = $this->paystackSecret();
        if (!$psSecret) {
            return redirect()->route('shop')->with('checkout_error', 'Paystack is not configured.');
        }

        try {
            $resp = Http::withToken($psSecret)
                ->acceptJson()
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->get('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
        } catch (\Throwable $e) {
            Log::error('[v5-PAYSTACK-CALLBACK] verify failed: ' . $e->getMessage());
            return redirect()->route('shop')->with('checkout_error', 'Could not reach Paystack for verification — try again.');
        }

        $vStatus  = $resp->json('data.status');
        $vAmount  = (int) $resp->json('data.amount');
        $expected = (int) round($order->total * $this->paystackRate() * 100);

        if ($resp->successful() && $resp->json('status') === true && $vStatus === 'success' && $vAmount === $expected) {
            if (!$this->markPaid($order, $reference)) {
                return redirect()->route('shop')->with('checkout_error', 'This payment attempt is no longer active.');
            }
            return redirect()->route('order.success', $order->order_number);
        }

        if ($vStatus && $vStatus !== 'success') {
            $this->markFailed($order);
        }
        if ($this->isPaystackAuthorizationError($resp)) {
            Log::error('[v5-PAYSTACK-CALLBACK] authorization rejected', ['http_status' => $resp->status()]);
            return redirect()->route('shop')->with('checkout_error', $this->paystackAuthorizationMessage());
        }
        Log::error('[v5-PAYSTACK-CALLBACK] verify mismatch', ['http_status' => $resp->status(), 'vStatus' => $vStatus, 'vAmount' => $vAmount, 'expected' => $expected]);
        return redirect()->route('shop')->with('checkout_error', 'Payment could not be verified — agar paise kat gaye hain to 24 ghante mein wapas aa jayenge.');
    }

    /* ============================================================
       CONFIRM (AJAX) — Stripe · Paystack popup flow
    ============================================================ */
    public function confirm(Request $request)
    {
        if ($request->input('gateway') === 'paystack') {
            return $this->confirmPaystack($request);
        }
        if ($request->input('gateway') === 'stripe_failed') {
            return $this->releaseFailedStripeAttempt($request);
        }

        $data = $request->validate([
            'order_id'       => 'required|integer',
            'payment_intent' => 'required|string',
        ]);

        $order = Order::find($data['order_id']);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        if ($order->payment_status === 'paid') {
            $this->clearCart();
            return response()->json(['status' => 'success', 'redirect' => route('order.success', $order->order_number)]);
        }

        $secret = $this->stripeSecret();
        if (!$secret || $order->transaction_id !== $data['payment_intent']) {
            return response()->json(['status' => 'error', 'message' => 'Payment intent does not match this order.'], 422);
        }
        try {
            $resp = Http::withToken($secret)
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->get('https://api.stripe.com/v1/payment_intents/' . $data['payment_intent']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Stripe verification failed — try again.'], 422);
        }

        $status = $resp->json('status');
        if (
            !in_array($status, ['succeeded', 'requires_capture'], true)
            || $resp->json('id') !== $order->transaction_id
            || (int) $resp->json('amount') !== (int) round((float) $order->total * 100)
            || strtolower((string) $resp->json('currency')) !== 'gbp'
            || (string) $resp->json('metadata.order_id') !== (string) $order->id
        ) {
            $this->markFailed($order);
            return response()->json(['status' => 'error', 'message' => 'Payment could not be completed.'], 422);
        }

        if (!$this->markPaid($order, $data['payment_intent'])) {
            return response()->json(['status' => 'error', 'message' => 'This payment attempt is no longer active.'], 422);
        }

        return response()->json(['status' => 'success', 'redirect' => route('order.success', $order->order_number)]);
    }

    private function releaseFailedStripeAttempt(Request $request)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'payment_intent' => 'required|string',
        ]);
        $order = Order::find($data['order_id']);
        if (!$order || $order->payment_method !== 'stripe' || $order->transaction_id !== $data['payment_intent']) {
            return response()->json(['status' => 'error', 'message' => 'Payment attempt does not match this order.'], 422);
        }
        if ($order->payment_status === 'paid') {
            return response()->json(['status' => 'error', 'message' => 'This order is already paid.'], 409);
        }

        $secret = $this->stripeSecret();
        if (!$secret) {
            return response()->json(['status' => 'error', 'message' => 'Stripe is not configured.'], 422);
        }

        try {
            $resp = Http::withToken($secret)
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->get('https://api.stripe.com/v1/payment_intents/'.$data['payment_intent']);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Stripe verification failed — try again.'], 422);
        }

        $intentStatus = $resp->json('status');
        if (
            !$resp->successful()
            || $resp->json('id') !== $order->transaction_id
            || (int) $resp->json('amount') !== (int) round((float) $order->total * 100)
            || strtolower((string) $resp->json('currency')) !== 'gbp'
            || (string) $resp->json('metadata.order_id') !== (string) $order->id
        ) {
            return response()->json(['status' => 'error', 'message' => 'Stripe payment status could not be verified.'], 422);
        }
        if (!in_array($intentStatus, ['requires_payment_method', 'canceled'], true)) {
            return response()->json(['status' => 'error', 'message' => 'The Stripe payment attempt is still active.'], 409);
        }

        $this->markFailed($order);

        return response()->json(['status' => 'failed', 'message' => 'Payment failed; coupon use released.']);
    }

    /* ============================================================
       PAYSTACK VERIFY (popup callback — AJAX)
    ============================================================ */
    private function confirmPaystack(Request $request)
    {
        $data = $request->validate([
            'order_id'  => 'required|integer',
            'reference' => 'required|string|max:120',
        ]);

        $order = Order::find($data['order_id']);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        if ($order->payment_status === 'paid') {
            $this->clearCart();
            return response()->json(['status' => 'success', 'redirect' => route('order.success', $order->order_number)]);
        }

        if ($order->transaction_id !== $data['reference']) {
            return response()->json(['status' => 'error', 'message' => 'Payment reference does not match this order.'], 422);
        }

        $psSecret = $this->paystackSecret();
        if (!$psSecret) {
            return response()->json(['status' => 'error', 'message' => 'Paystack is not configured.'], 422);
        }

        try {
            $resp = Http::withToken($psSecret)
                ->acceptJson()
                ->connectTimeout(self::HTTP_CONNECT)
                ->timeout(self::HTTP_TIMEOUT)
                ->get('https://api.paystack.co/transaction/verify/' . rawurlencode($data['reference']));
        } catch (\Throwable $e) {
            Log::error('[v5-PAYSTACK-VERIFY] failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Could not reach Paystack for verification — try again.'], 422);
        }

        $vStatus  = $resp->json('data.status');
        $vAmount  = (int) $resp->json('data.amount');
        $expected = (int) round($order->total * $this->paystackRate() * 100);

        if ($resp->successful() && $resp->json('status') === true && $vStatus === 'success' && $vAmount === $expected) {
            if (!$this->markPaid($order, $data['reference'])) {
                return response()->json(['status' => 'error', 'message' => 'This payment attempt is no longer active.'], 422);
            }
            return response()->json(['status' => 'success', 'redirect' => route('order.success', $order->order_number)]);
        }

        if ($vStatus && $vStatus !== 'success') {
            $this->markFailed($order);
        }
        if ($this->isPaystackAuthorizationError($resp)) {
            Log::error('[v5-PAYSTACK-VERIFY] authorization rejected', ['http_status' => $resp->status()]);
            return response()->json(['status' => 'error', 'message' => $this->paystackAuthorizationMessage()], 422);
        }
        return response()->json(['status' => 'error', 'message' => 'Payment could not be verified.'], 422);
    }

    /* ============================================================
       THANK YOU PAGE
    ============================================================ */
    public function success(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->with('items')->firstOrFail();
        return view('front-end.pages.order-success', ['order' => $order]);
    }

    /* ============================================================
       HELPERS
    ============================================================ */
    private function stripeSecret(): ?string
    {
        $v = Setting::get('stripe_secret');
        if (!$v) return null;
        try { return Crypt::decryptString($v); } catch (\Throwable) { return $v; }
    }

    private function paystackSecret(): ?string
    {
        $v = Setting::get('paystack_secret');
        if (!$v) return null;
        try {
            $v = Crypt::decryptString($v);
        } catch (\Throwable) {
            // Older settings may contain an unencrypted key.
        }

        return PaystackSecretKey::normalize($v);
    }

    private function isPaystackAuthorizationError(\Illuminate\Http\Client\Response $response): bool
    {
        $message = strtolower((string) $response->json('message', ''));

        return in_array($response->status(), [401, 403], true)
            || str_contains($message, 'authorization')
            || str_contains($message, 'bearer');
    }

    private function paystackAuthorizationMessage(): string
    {
        $publicMode = PaystackSecretKey::mode(Setting::get('paystack_key'), 'pk');
        $secretMode = PaystackSecretKey::mode($this->paystackSecret(), 'sk');

        if (! $publicMode || ! $secretMode) {
            return 'Paystack key settings are invalid. In Admin → Payment Settings, enter the complete public key (pk_test_… or pk_live_…) and secret key (sk_test_… or sk_live_…). Paste the actual keys, not the Authorization/Bearer instructions; the server builds that header automatically.';
        }

        if ($publicMode !== $secretMode) {
            return 'Paystack public and secret keys use different modes. In Admin → Payment Settings, use both test keys together or both live keys together.';
        }

        return 'Paystack rejected the configured secret key. In Admin → Payment Settings, replace it with the active secret key from the same Paystack account and mode as the public key. Paste the actual key only; the server builds the Authorization Bearer header automatically.';
    }

    private function paystackCurrency(): string
    {
        $c = strtoupper(trim((string) Setting::get('paystack_currency', 'AUTO')));
        if ($c === 'AUTO') return 'AUTO';
        return in_array($c, ['NGN', 'GHS', 'ZAR', 'KES', 'USD']) ? $c : 'AUTO';
    }

    private function paystackRate(): float
    {
        $r = (float) Setting::get('paystack_rate', '1');
        return $r > 0 ? $r : 1;
    }

    private function markPaid(Order $order, ?string $tx): bool
    {
        $newlyPaid = false;
        $updated = DB::transaction(function () use ($order, $tx, &$newlyPaid): bool {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();
            if (!$lockedOrder) {
                return false;
            }
            if ($lockedOrder->payment_status === 'paid') {
                return true;
            }
            if ($lockedOrder->payment_status === 'failed') {
                return false;
            }

            $inventory = [];
            foreach ($lockedOrder->items as $item) {
                if (! $item->product_id) {
                    continue;
                }
                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                if (! $product) {
                    Log::critical('Payment confirmation found a missing ordered product.', [
                        'order_id' => $lockedOrder->id,
                        'product_id' => $item->product_id,
                    ]);
                    return false;
                }
                $variant = null;
                if ($item->product_sku_variant_id) {
                    $variant = ProductSkuVariant::whereKey($item->product_sku_variant_id)
                        ->where('product_id', $product->id)
                        ->lockForUpdate()
                        ->first();
                }
                $available = (int) ($variant?->stock ?? $product->stock);
                if (($item->product_sku_variant_id && ! $variant) || $available < $item->qty) {
                    Log::critical('Payment confirmed but the ordered item no longer has enough stock.', [
                        'order_id' => $lockedOrder->id,
                        'product_id' => $product->id,
                        'sku_variant_id' => $item->product_sku_variant_id,
                        'ordered_qty' => $item->qty,
                        'available_qty' => $available,
                    ]);
                    return false;
                }
                $inventory[$item->id] = ['product' => $product, 'variant' => $variant, 'qty' => $item->qty];
            }

            $lockedOrder->update(['payment_status' => 'paid', 'transaction_id' => $tx, 'paid_at' => now()]);
            $newlyPaid = true;
            $redemption = CouponRedemption::where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if ($redemption && $redemption->status === 'reserved') {
                $redemption->update(['status' => 'redeemed', 'redeemed_at' => now()]);
                if ($redemption->coupon_id) {
                    Coupon::whereKey($redemption->coupon_id)
                        ->where('reserved_count', '>', 0)
                        ->decrement('reserved_count');
                    Coupon::whereKey($redemption->coupon_id)->increment('uses_count');
                }
            }

            foreach ($inventory as $stockItem) {
                if ($stockItem['variant']) {
                    $stockItem['variant']->decrement('stock', $stockItem['qty']);
                }
                $stockItem['product']->decrement('stock', $stockItem['qty']);
            }

            return true;
        });
        if (!$updated) {
            return false;
        }

        if ($order->fresh()?->payment_status !== 'paid') {
            return false;
        }
        if ($newlyPaid) {
            $paidOrder = $order->fresh(['items']);
            Log::info('[v5] 💰 ORDER MARKED PAID', ['order_number' => $paidOrder->order_number]);
            try {
                Mail::to($paidOrder->email)->send(new OrderReceipt($paidOrder));
            } catch (\Throwable $exception) {
                report($exception);
                Log::error('Customer order receipt could not be delivered.', [
                    'order_number' => $paidOrder->order_number,
                    'recipient' => $paidOrder->email,
                ]);
            }

            AdminEmailNotifier::send('Order paid: '.$paidOrder->order_number, [
                'Order' => $paidOrder->order_number,
                'Customer' => $paidOrder->name.' <'.$paidOrder->email.'>',
                'Payment method' => ucfirst($paidOrder->payment_method),
                'Paid total' => '£'.number_format((float) $paidOrder->total, 2),
                'Items' => $paidOrder->items
                    ->map(fn ($item) => $item->product_name.' × '.$item->qty)
                    ->implode(', '),
                'View order' => route('admin.orders.show', $paidOrder),
            ]);
            $this->notifyMarketplaceVendorsOfSale($paidOrder);
        }
        $this->clearCart();

        return true;
    }

    private function notifyMarketplaceVendorsOfSale(Order $order): void
    {
        $sellerItems = $order->items()
            ->with('product.submitter')
            ->get()
            ->filter(fn ($item) => $item->product?->submitter && ! $item->product->submitter->isAdmin())
            ->groupBy(fn ($item) => $item->product->submitter->id);

        foreach ($sellerItems as $items) {
            $vendor = $items->first()->product->submitter;
            try {
                Mail::to($vendor->email)->send(new MarketplaceSaleNotification(
                    $vendor->name,
                    $order->order_number,
                    $items->map(fn ($item) => [
                        'name' => $item->product_name,
                        'quantity' => (int) $item->qty,
                    ])->values()->all(),
                    route('user.marketplace.withdrawals'),
                ));
            } catch (\Throwable $exception) {
                report($exception);
                Log::error('Marketplace vendor sale email could not be delivered.', [
                    'order_number' => $order->order_number,
                    'vendor_id' => $vendor->id,
                    'recipient' => $vendor->email,
                ]);
            }
        }
    }

    private function markFailed(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->first();
            if (!$lockedOrder || $lockedOrder->payment_status === 'paid') {
                return;
            }

            $lockedOrder->update(['payment_status' => 'failed']);
            $redemption = CouponRedemption::where('order_id', $lockedOrder->id)->lockForUpdate()->first();
            if ($redemption && $redemption->status === 'reserved') {
                $redemption->update(['status' => 'released']);
                if ($redemption->coupon_id) {
                    Coupon::whereKey($redemption->coupon_id)
                        ->where('reserved_count', '>', 0)
                        ->decrement('reserved_count');
                }
            }
        });
    }

    private function clearCart(): void
    {
        session(['cart' => []]);
        CartController::clearPersistentCart();
    }
}