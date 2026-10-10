<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Front\ShopController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\ReviewController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\WishlistController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\FaqController;
use App\Http\Controllers\Front\NewsletterController;
use App\Http\Controllers\Support\SupportController;
use App\Models\Product;
use App\Models\Ad;
use App\Models\Setting;
use App\Support\PageHeaders;
use App\Support\HomeBrandSlider;

Route::get('/ads/{ad}/click', [\App\Http\Controllers\Admin\AdsController::class, 'click'])
    ->whereNumber('ad')->name('ads.click');
Route::get('/ads.txt', fn () => response(
    (string) Setting::get('ads_txt_content', ''),
    200,
    ['Content-Type' => 'text/plain; charset=UTF-8']
))->name('ads.txt');
Route::get('/faqs', [FaqController::class, 'index'])->name('faqs.index');
Route::get('/faqs/category/{faqCategory:slug}', [FaqController::class, 'category'])->name('faqs.category');
Route::get('/shop/products/{slug}/quick-view', [ShopController::class, 'quickView'])
    ->name('shop.quick-view');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])
    ->middleware('throttle:5,1')->name('newsletter.subscribe');

/*
|--------------------------------------------------------------------------
| Front-end Store (public)
|--------------------------------------------------------------------------
*/

/* 🏠 HOME — dynamic: real products + REAL approved testimonials */
Route::get('/', function () {
    $homeSettings = [];
    foreach (config('homepage.content') as $key => $default) {
        $homeSettings[$key] = Setting::get($key, $default);
    }
    foreach (config('homepage.images') as $key => $default) {
        $path = Setting::get($key);
        $homeSettings[$key] = $path ? asset('storage/' . $path) : $default;
    }

    /* Newest active products drive both product sections. */
    $featured = Product::where('is_active', true)
        ->with(['images', 'category', 'labels'])
        ->withCount('skuVariants')
        ->latest()
        ->take(8)
        ->get();

    /* Keep the latest strip focused on the newest products, including featured items. */
    $latest = Product::where('is_active', true)
        ->with(['images', 'category', 'labels'])
        ->withCount('skuVariants')
        ->latest()
        ->take(4)
        ->get();

    /* ⭐ REAL TESTIMONIALS — sirf admin-approved, latest 3 */
    $homeReviews = \App\Models\Review::where('status', 'approved')
        ->latest()
        ->take(3)
        ->get();

    $homeReviewProducts = \App\Models\Product::whereIn('id', $homeReviews->pluck('product_id')->unique())
        ->get()
        ->keyBy('id');
    $homeBrands = HomeBrandSlider::visibleItems();
    $activeProductCollections = \App\Models\ProductCollection::active()
        ->with(['products' => fn ($query) => $query->where('is_active', true)
            ->where('review_status', 'approved')
            ->with(['images', 'category', 'labels'])
            ->withCount('skuVariants')
            ->withSum(['orderItems as paid_sold_quantity' => fn ($items) => $items->whereHas(
                'order', fn ($orders) => $orders->where('payment_status', 'paid')
            )], 'qty')])
        ->orderBy('ends_at')
        ->get()
        ->filter(fn ($collection) => $collection->products->isNotEmpty())
        ->values();
    $adsEnabled = Setting::get('ads_enabled', '0') === '1';
    $activeAds = $adsEnabled
        ? Ad::published()->orderBy('sort_order')->orderByDesc('id')->get()
        : collect();

    return view('front-end.pages.home', [
        'featuredProducts'   => $featured,
        'latestProducts'     => $latest,
        'wishIds'            => auth()->check()
            ? auth()->user()->wishlist()->pluck('product_id')->toArray()
            : [],
        'homeReviews'        => $homeReviews,
        'homeReviewProducts' => $homeReviewProducts,
        'homeBrands'        => $homeBrands,
        'activeProductCollections' => $activeProductCollections,
        'activeAds'          => $activeAds,
        'adsRotationSeconds' => max(3, min(30, (int) Setting::get('ads_rotation_seconds', '6'))),
        'homeSettings'       => $homeSettings,
    ]);
})->name('home');

/* ⭐ ABOUT — DYNAMIC: products slider + admin-controlled stats */
Route::get('/about', function () {
    $featured = Product::where('is_active', true)
        ->with(['images', 'category'])
        ->withCount('skuVariants')
        ->latest()
        ->take(10)
        ->get();

    $wishIds = auth()->check()
        ? auth()->user()->wishlist()->pluck('product_id')->toArray()
        : [];

    $aboutSettings = [];
    foreach (config('about_page.content') as $key => $default) {
        $aboutSettings[$key] = Setting::get($key, $default);
    }
    $aboutImages = [];
    foreach (array_keys(config('about_page.images')) as $key) {
        $path = Setting::get($key);
        $aboutImages[$key] = $path ? asset('storage/' . $path) : config("about_page.images.{$key}");
    }
    $aboutProductId = Setting::get('about_product_id');
    $aboutProduct = $aboutProductId
        ? Product::with(['images', 'category'])->find($aboutProductId)
        : null;
    $homeBrands = HomeBrandSlider::visibleItems();

    return view('front-end.pages.about', [
        'featured' => $featured,
        'wishIds'  => $wishIds,
        'aboutSettings' => $aboutSettings,
        'aboutImages' => $aboutImages,
        'aboutProduct' => $aboutProduct,
        'homeBrands' => $homeBrands,
        'pageHeader' => PageHeaders::for('about'),
    ]);
})->name('about');

/* ⭐ TESTIMONIALS — DYNAMIC: sirf admin-approved reviews live hote hain */
Route::get('/testimonials', function () {
    $reviews = \App\Models\Review::where('status', 'approved')->latest()->get();
    $products = \App\Models\Product::whereIn('id', $reviews->pluck('product_id')->unique())
        ->get()->keyBy('id');
    return view('front-end.pages.testimonials', [
        'reviews'  => $reviews,
        'products' => $products,
        'pageHeader' => PageHeaders::for('testimonials'),
    ]);
})->name('testimonials');

/* ⭐ CONTACT — DYNAMIC: email/phone/address/hours/socials sab admin ke Store Settings se */
Route::get('/contact', function () {
    $s = [
        'site_email'       => Setting::get('site_email') ?: 'hello@cosmetics.co.uk',
        'site_phone'       => Setting::get('site_phone') ?: '+44 20 7946 0958',
        'site_address'     => Setting::get('site_address') ?: '245 Oxford Street, London W1D 2DW, United Kingdom',
        'site_map_query'   => Setting::get('site_map_query') ?: 'Oxford Street, London',
        'hours_weekdays'   => Setting::get('hours_weekdays') ?: '9:00 AM – 6:00 PM',
        'hours_saturday'   => Setting::get('hours_saturday') ?: '10:00 AM – 4:00 PM',
        'hours_sunday'     => Setting::get('hours_sunday') ?: 'Closed',
        'social_instagram' => Setting::get('social_instagram') ?: '#',
        'social_facebook'  => Setting::get('social_facebook') ?: '#',
        'social_twitter'   => Setting::get('social_twitter') ?: '#',
        'social_youtube'   => Setting::get('social_youtube') ?: '#',
    ];
    $contactPage = [];
    foreach (config('contact_page.content') as $key => $default) {
        $contactPage[$key] = Setting::get($key, $default);
    }
    $contactImages = [];
    foreach (array_keys(config('contact_page.images')) as $key) {
        $path = Setting::get($key);
        $contactImages[$key] = $path ? asset('storage/' . $path) : config("contact_page.images.{$key}");
    }

    return view('front-end.pages.contact', [
        's' => $s,
        'contactPage' => $contactPage,
        'contactImages' => $contactImages,
        'pageHeader' => PageHeaders::for('contact'),
    ]);
})->name('contact');

/* ⭐ CONTACT FORM — message DB me save hota hai → admin Messages inbox */
Route::post('/contact/send', [ContactController::class, 'store'])->name('contact.send');

Route::get('/support/attachments/{message}', [SupportController::class, 'downloadAttachment'])
    ->middleware('auth')->name('support.attachments.download');
Route::get('/support/attachments/{message}/preview', [SupportController::class, 'previewAttachment'])
    ->middleware('auth')->name('support.attachments.preview');

/* ================= CHECKOUT ================= */
Route::middleware('auth')->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart.index');
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.coupon');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    Route::get('/cart/items', [CartController::class, 'items'])->name('cart.items');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
});

/* ⭐ PAYSTACK CALLBACK — hosted page se wapas (redirect fallback flow) */
Route::get('/checkout/paystack/callback', [CheckoutController::class, 'paystackCallback'])->name('checkout.paystack.callback');

Route::get('/thank-you/{orderNumber}', [CheckoutController::class, 'success'])->name('order.success');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/product/{slug}', [ShopController::class, 'show'])->name('product.show');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{category}', [BlogController::class, 'index'])->name('blog.category');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::post('/product/{product}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

/*
|--------------------------------------------------------------------------
| Remember-where-you-left (shopping + review flow)
|--------------------------------------------------------------------------
*/
Route::get('/login-redirect', function (Illuminate\Http\Request $r) {
    $to = $r->query('redirect');
    $to = is_string($to) ? $to : '';
    try {
        $parts = $to !== '' ? parse_url($to) : false;
    } catch (\ValueError) {
        $parts = false;
    }
    $path = is_array($parts) ? ($parts['path'] ?? '') : '';
    $frag = is_array($parts) ? ($parts['fragment'] ?? '') : '';
    $relative = str_starts_with($to, '/') && !str_starts_with($to, '//');
    $sameOrigin = $relative;

    if (is_array($parts) && isset($parts['host'])) {
        $scheme = strtolower($parts['scheme'] ?? '');
        $sameOrigin = in_array($scheme, ['http', 'https'], true)
            && strcasecmp($parts['host'], $r->getHost()) === 0
            && !isset($parts['user'])
            && !isset($parts['pass'])
            && ($parts['port'] ?? ($scheme === 'https' ? 443 : 80)) === $r->getPort();
    }

    $isShopLike = $path === '/shop' || $path === '/cart' || $path === '/checkout' || str_starts_with($path, '/product/');

    if ($isShopLike && $sameOrigin) {
        $clean = url($path);
        if (!empty($parts['query'])) $clean .= '?' . $parts['query'];
        if ($frag !== '') $clean .= '#' . $frag;
        session(['url.intended' => $clean]);
    }

    return redirect()->route('login');
})->name('login.redirect');

/*
|--------------------------------------------------------------------------
| Authenticated (profile only)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';