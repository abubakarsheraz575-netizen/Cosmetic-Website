<?php

use App\Http\Controllers\Admin\AboutSettingController;
use App\Http\Controllers\Admin\AdsController;
use App\Http\Controllers\Admin\FaqManagementController;
use App\Http\Controllers\Admin\NewsletterAdminController;
use App\Http\Controllers\Admin\MediaManagementController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BlogManagementController;
use App\Http\Controllers\Admin\CatalogManagementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerCartController;
use App\Http\Controllers\Admin\CouponTaxController;
use App\Http\Controllers\Admin\CustomerDirectoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EcommerceController;
use App\Http\Controllers\Admin\HomeSettingController;
use App\Http\Controllers\Admin\MailSettingController;
use App\Http\Controllers\Admin\MarketplaceController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageHeaderSettingController;
use App\Http\Controllers\Admin\PaymentSettingController;
use App\Http\Controllers\Admin\PaymentReportController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ShippingController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\ProductSpecificationController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Support\SupportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard (dynamic + AJAX data endpoint)
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
        Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifications/read', [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

        /*
        |--------------------------------------------------------------------------
        | Admin Profile
        |--------------------------------------------------------------------------
        */
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/contacts', [ContactMessageController::class, 'index'])->name('contacts');
        Route::get('/contacts/{contactMessage}', [ContactMessageController::class, 'show'])->name('contacts.show');
        Route::post('/contacts/{contactMessage}/reply', [ContactMessageController::class, 'reply'])
            ->middleware('throttle:30,1')->name('contacts.reply');
        Route::delete('/contacts/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('contacts.destroy');

        Route::get('/support', [SupportController::class, 'adminPage'])->name('support');
        Route::get('/support/inbox', [SupportController::class, 'inbox'])->name('support.inbox');
        Route::post('/support/users/{user}/conversation', [SupportController::class, 'startConversation'])
            ->name('support.conversations.start');
        Route::get('/support/{conversation}/messages', [SupportController::class, 'adminMessages'])
            ->name('support.messages');
        Route::post('/support/{conversation}/heartbeat', [SupportController::class, 'adminHeartbeat'])
            ->name('support.heartbeat');
        Route::post('/support/{conversation}/messages', [SupportController::class, 'adminSend'])
            ->middleware('throttle:30,1')->name('support.send');
        Route::patch('/support/{conversation}/messages/{message}', [SupportController::class, 'adminEditMessage'])
            ->middleware('throttle:30,1')->name('support.messages.edit');
        Route::delete('/support/{conversation}/messages/{message}', [SupportController::class, 'adminDeleteMessage'])
            ->name('support.messages.delete');
        Route::post('/support/{conversation}/messages/{message}/pin', [SupportController::class, 'adminTogglePin'])
            ->name('support.messages.pin');
        Route::post('/support/{conversation}/messages/{message}/star', [SupportController::class, 'adminToggleStar'])
            ->name('support.messages.star');
        Route::post('/support/{conversation}/messages/{message}/report', [SupportController::class, 'adminReportMessage'])
            ->middleware('throttle:10,1')->name('support.messages.report');
        Route::delete('/support/{conversation}', [SupportController::class, 'adminDeleteConversation'])
            ->name('support.conversations.delete');

        Route::get('/marketplaces/stores', [MarketplaceController::class, 'stores'])->name('marketplace.stores');
        Route::get('/marketplaces/vendors', [MarketplaceController::class, 'vendors'])->name('marketplace.vendors');
        Route::get('/marketplaces/vendors/unverified', [MarketplaceController::class, 'unverifiedVendors'])->name('marketplace.vendors.unverified');
        Route::get('/marketplaces/vendors/{user}', [MarketplaceController::class, 'vendor'])->name('marketplace.vendors.show');
        Route::patch('/marketplaces/vendors/{user}/status', [MarketplaceController::class, 'updateVendorStatus'])->name('marketplace.vendors.status');
        Route::delete('/marketplaces/vendors/{user}', [MarketplaceController::class, 'destroyVendor'])->name('marketplace.vendors.destroy');
        Route::get('/marketplaces/withdrawals', [MarketplaceController::class, 'withdrawals'])->name('marketplace.withdrawals');
        Route::patch('/marketplaces/withdrawals/settings/commission', [MarketplaceController::class, 'updateCommissionRate'])->name('marketplace.withdrawals.commission');
        Route::patch('/marketplaces/withdrawals/{withdrawal}', [MarketplaceController::class, 'updateWithdrawal'])->name('marketplace.withdrawals.update');
        Route::delete('/marketplaces/withdrawals/{withdrawal}', [MarketplaceController::class, 'destroyWithdrawal'])->name('marketplace.withdrawals.destroy');
        Route::get('/marketplaces/messages', [MarketplaceController::class, 'messages'])->name('marketplace.messages');
        Route::get('/marketplaces/messages/feed', [MarketplaceController::class, 'messageFeed'])->name('marketplace.messages.feed');
        Route::post('/marketplaces/messages', [MarketplaceController::class, 'sendMessage'])
            ->middleware('throttle:30,1')->name('marketplace.messages.send');

        Route::get('/blog/posts', [BlogManagementController::class, 'posts'])->name('blog.posts');
        Route::get('/blog/posts/create', [BlogManagementController::class, 'create'])->name('blog.posts.create');
        Route::post('/blog/posts', [BlogManagementController::class, 'store'])->name('blog.posts.store');
        Route::get('/blog/posts/{post}/edit', [BlogManagementController::class, 'edit'])->name('blog.posts.edit');
        Route::patch('/blog/posts/{post}', [BlogManagementController::class, 'update'])->name('blog.posts.update');
        Route::delete('/blog/posts/{post}', [BlogManagementController::class, 'destroy'])->name('blog.posts.destroy');
        Route::get('/blog/categories', [BlogManagementController::class, 'categories'])->name('blog.categories');
        Route::post('/blog/categories', [BlogManagementController::class, 'storeCategory'])->name('blog.categories.store');
        Route::patch('/blog/categories/{category}', [BlogManagementController::class, 'updateCategory'])->name('blog.categories.update');
        Route::delete('/blog/categories/{category}', [BlogManagementController::class, 'destroyCategory'])->name('blog.categories.destroy');
        Route::get('/blog/tags', [BlogManagementController::class, 'tags'])->name('blog.tags');
        Route::post('/blog/tags', [BlogManagementController::class, 'storeTag'])->name('blog.tags.store');
        Route::patch('/blog/tags/{tag}', [BlogManagementController::class, 'updateTag'])->name('blog.tags.update');
        Route::delete('/blog/tags/{tag}', [BlogManagementController::class, 'destroyTag'])->name('blog.tags.destroy');
        Route::get('/blog/reports', [BlogManagementController::class, 'reports'])->name('blog.reports');
        Route::get('/blog/seo', [BlogManagementController::class, 'seo'])->name('blog.seo');
        Route::patch('/blog/seo', [BlogManagementController::class, 'updateSeo'])->name('blog.seo.update');


        Route::get('/payments/transactions', [PaymentReportController::class, 'transactions'])->name('payment.transactions');
        Route::get('/payments/logs', [PaymentReportController::class, 'logs'])->name('payment.logs');
        Route::get('/payments/transactions/create', [PaymentReportController::class, 'create'])->name('payment.transactions.create');
        Route::post('/payments/transactions', [PaymentReportController::class, 'store'])->name('payment.transactions.store');
        Route::get('/payments/transactions/{order}/edit', [PaymentReportController::class, 'edit'])->whereNumber('order')->name('payment.transactions.edit');
        Route::patch('/payments/transactions/{order}', [PaymentReportController::class, 'update'])->whereNumber('order')->name('payment.transactions.update');
        Route::delete('/payments/transactions/{order}', [PaymentReportController::class, 'destroy'])->whereNumber('order')->name('payment.transactions.destroy');

        Route::get('/ads', [AdsController::class, 'index'])->name('ads.index');
        Route::get('/ads/create', [AdsController::class, 'create'])->name('ads.create');
        Route::post('/ads', [AdsController::class, 'store'])->name('ads.store');
        Route::get('/ads/{ad}/edit', [AdsController::class, 'edit'])->name('ads.edit');
        Route::patch('/ads/{ad}', [AdsController::class, 'update'])->name('ads.update');
        Route::delete('/ads/{ad}', [AdsController::class, 'destroy'])->name('ads.destroy');
        Route::get('/ads-settings', [AdsController::class, 'settings'])->name('ads.settings');
        Route::patch('/ads-settings', [AdsController::class, 'updateSettings'])->name('ads.settings.update');

        Route::get('/faqs', [FaqManagementController::class, 'index'])->name('faqs.index');
        Route::get('/faqs/create', [FaqManagementController::class, 'create'])->name('faqs.create');
        Route::post('/faqs', [FaqManagementController::class, 'store'])->name('faqs.store');
        Route::get('/faqs/categories', [FaqManagementController::class, 'categories'])->name('faqs.categories');
        Route::get('/faqs/categories/create', [FaqManagementController::class, 'createCategory'])->name('faqs.categories.create');
        Route::post('/faqs/categories', [FaqManagementController::class, 'storeCategory'])->name('faqs.categories.store');
        Route::get('/faqs/categories/{faqCategory}/edit', [FaqManagementController::class, 'editCategory'])->name('faqs.categories.edit');
        Route::patch('/faqs/categories/{faqCategory}', [FaqManagementController::class, 'updateCategory'])->name('faqs.categories.update');
        Route::delete('/faqs/categories/{faqCategory}', [FaqManagementController::class, 'destroyCategory'])->name('faqs.categories.destroy');
        Route::get('/faqs/categories/{faqCategory}', [FaqManagementController::class, 'showCategory'])->name('faqs.categories.show');
        Route::get('/faqs/{faq}/edit', [FaqManagementController::class, 'edit'])->name('faqs.edit');
        Route::patch('/faqs/{faq}', [FaqManagementController::class, 'update'])->name('faqs.update');
        Route::delete('/faqs/{faq}', [FaqManagementController::class, 'destroy'])->name('faqs.destroy');
        Route::get('/newsletters', [NewsletterAdminController::class, 'index'])->name('newsletters.index');
        Route::patch('/newsletters/{subscriber}/status', [NewsletterAdminController::class, 'updateStatus'])->name('newsletters.status');
        Route::delete('/newsletters/{subscriber}', [NewsletterAdminController::class, 'destroy'])->name('newsletters.destroy');
        Route::get('/media', [MediaManagementController::class, 'index'])->name('media.index');
        Route::get('/media/picker', [MediaManagementController::class, 'picker'])->name('media.picker');
        Route::post('/media/upload', [MediaManagementController::class, 'upload'])->name('media.upload');
        Route::post('/media/delete', [MediaManagementController::class, 'delete'])->name('media.delete');
        Route::post('/media/folders', [MediaManagementController::class, 'storeFolder'])->name('media.folders.store');

        /*
        |--------------------------------------------------------------------------
        | Catalog — Categories
        |--------------------------------------------------------------------------
        */
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::patch('/categories/{category}/status', [CategoryController::class, 'updateStatus'])->name('categories.status');
        Route::patch('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        /*
        |--------------------------------------------------------------------------
        | Catalog — Sizes (ML / Litres)
        |--------------------------------------------------------------------------
        */
        Route::get('/sizes', [SizeController::class, 'index'])->name('sizes');
        Route::post('/sizes', [SizeController::class, 'store'])->name('sizes.store');
        Route::patch('/sizes/{size}/status', [SizeController::class, 'updateStatus'])->name('sizes.status');
        Route::patch('/sizes/{size}', [SizeController::class, 'update'])->name('sizes.update');
        Route::delete('/sizes/{size}', [SizeController::class, 'destroy'])->name('sizes.destroy');

        /*
        |--------------------------------------------------------------------------
        | Catalog — Products (show MUST come after create!)
        |--------------------------------------------------------------------------
        */
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::patch('/products/{product}/cover', [ProductController::class, 'updateCover'])->name('products.cover');
        Route::patch('/products/{product}/images', [ProductController::class, 'updateImages'])->name('products.images.update');
        Route::post('/products/quick-brand', [ProductController::class, 'quickStoreBrand'])->name('products.quick-brand');
        Route::post('/products/quick-attribute', [ProductController::class, 'quickStoreAttribute'])->name('products.quick-attribute');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::patch('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/product-submissions', [ProductController::class, 'submissions'])->name('product-submissions.index');
        Route::get('/product-submissions/{product}', [ProductController::class, 'reviewSubmission'])->name('product-submissions.show');
        Route::post('/product-submissions/{product}/approve', [ProductController::class, 'approveSubmission'])->name('product-submissions.approve');
        Route::post('/product-submissions/{product}/reject', [ProductController::class, 'rejectSubmission'])->name('product-submissions.reject');

        /*
        |--------------------------------------------------------------------------
        | Commerce — Orders
        |--------------------------------------------------------------------------
        */
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('/ecommerce/reports', [EcommerceController::class, 'reports'])->name('ecommerce.reports');
        Route::get('/ecommerce/prices', [CatalogManagementController::class, 'prices'])->name('ecommerce.prices');
        Route::post('/ecommerce/prices/random-sales', [CatalogManagementController::class, 'startRandomSales'])->name('ecommerce.prices.random-sales');
        Route::patch('/ecommerce/prices', [CatalogManagementController::class, 'updatePrices'])->name('ecommerce.prices.update');
        Route::get('/ecommerce/inventory', [CatalogManagementController::class, 'inventory'])->name('ecommerce.inventory');
        Route::patch('/ecommerce/inventory', [CatalogManagementController::class, 'updateInventory'])->name('ecommerce.inventory.update');
        Route::get('/ecommerce/categories', [CatalogManagementController::class, 'categories'])->name('ecommerce.categories');
        Route::get('/ecommerce/categories/{category}', [CatalogManagementController::class, 'categoryProducts'])->name('ecommerce.categories.show');
        Route::get('/ecommerce/attributes', [CatalogManagementController::class, 'attributes'])->name('ecommerce.attributes');
        Route::post('/ecommerce/attributes', [CatalogManagementController::class, 'storeAttribute'])->name('ecommerce.attributes.store');
        Route::patch('/ecommerce/attributes/{attribute}', [CatalogManagementController::class, 'updateAttribute'])
            ->whereNumber('attribute')->name('ecommerce.attributes.update');
        Route::delete('/ecommerce/attributes/{attribute}', [CatalogManagementController::class, 'destroyAttribute'])
            ->whereNumber('attribute')->name('ecommerce.attributes.destroy');
        Route::post('/ecommerce/attributes/variants', [CatalogManagementController::class, 'storeVariants'])->name('ecommerce.attributes.variants');
        Route::get('/product-specification/groups', [ProductSpecificationController::class, 'groups'])->name('product-specification.groups');
        Route::get('/product-specification/groups/{category}', [ProductSpecificationController::class, 'showGroup'])->name('product-specification.groups.show');
        Route::get('/product-specification/products/{product}/edit', [ProductSpecificationController::class, 'editProduct'])->name('product-specification.products.edit');
        Route::patch('/product-specification/products/{product}', [ProductSpecificationController::class, 'updateProduct'])->name('product-specification.products.update');
        Route::get('/product-specification/groups/{category}/edit', [ProductSpecificationController::class, 'editGroup'])->name('product-specification.groups.edit');
        Route::patch('/product-specification/groups/{category}', [ProductSpecificationController::class, 'updateGroup'])->name('product-specification.groups.update');
        Route::delete('/product-specification/groups/{category}', [ProductSpecificationController::class, 'destroyGroup'])->name('product-specification.groups.destroy');
        Route::get('/product-specification/attributes', [ProductSpecificationController::class, 'attributes'])->name('product-specification.attributes');
        Route::post('/product-specification/attributes', [ProductSpecificationController::class, 'storeAttribute'])->name('product-specification.attributes.store');
        Route::get('/product-specification/attributes/{specificationAttribute}/edit', [ProductSpecificationController::class, 'editAttribute'])->name('product-specification.attributes.edit');
        Route::patch('/product-specification/attributes/{specificationAttribute}', [ProductSpecificationController::class, 'updateAttribute'])->name('product-specification.attributes.update');
        Route::delete('/product-specification/attributes/{specificationAttribute}', [ProductSpecificationController::class, 'destroyAttribute'])->name('product-specification.attributes.destroy');
        Route::get('/ecommerce/collections', [CatalogManagementController::class, 'collections'])->name('ecommerce.collections');
        Route::get('/ecommerce/collections/create', [CatalogManagementController::class, 'createCollection'])->name('ecommerce.collections.create');
        Route::post('/ecommerce/collections', [CatalogManagementController::class, 'storeCollection'])->name('ecommerce.collections.store');
        Route::delete('/ecommerce/collections/{collection}', [CatalogManagementController::class, 'destroyCollection'])->name('ecommerce.collections.destroy');
        Route::get('/ecommerce/labels', [CatalogManagementController::class, 'labels'])->name('ecommerce.labels');
        Route::get('/ecommerce/labels/create', [CatalogManagementController::class, 'createLabel'])->name('ecommerce.labels.create');
        Route::post('/ecommerce/labels', [CatalogManagementController::class, 'storeLabel'])->name('ecommerce.labels.store');
        Route::delete('/ecommerce/labels/{label}', [CatalogManagementController::class, 'destroyLabel'])->name('ecommerce.labels.destroy');
        Route::get('/ecommerce/brands', [BrandController::class, 'index'])->name('ecommerce.brands');
        Route::get('/ecommerce/brands/create', [BrandController::class, 'create'])->name('ecommerce.brands.create');
        Route::post('/ecommerce/brands', [BrandController::class, 'store'])->name('ecommerce.brands.store');
        Route::get('/ecommerce/brands/{brand}/edit', [BrandController::class, 'edit'])->name('ecommerce.brands.edit');
        Route::get('/ecommerce/brands/{brand}', [BrandController::class, 'show'])->name('ecommerce.brands.show');
        Route::put('/ecommerce/brands/{brand}', [BrandController::class, 'update'])->name('ecommerce.brands.update');
        Route::delete('/ecommerce/brands/{brand}', [BrandController::class, 'destroy'])->name('ecommerce.brands.destroy');
        Route::get('/ecommerce/customers', [CustomerDirectoryController::class, 'index'])->name('ecommerce.customers.index');
        Route::get('/ecommerce/customers/{user}', [CustomerDirectoryController::class, 'show'])
            ->whereNumber('user')->name('ecommerce.customers.show');
        Route::get('/ecommerce/customer-carts', [CustomerCartController::class, 'index'])->name('ecommerce.customer-carts.index');
        Route::get('/ecommerce/customer-carts/{customerCart}', [CustomerCartController::class, 'show'])
            ->whereNumber('customerCart')->name('ecommerce.customer-carts.show');
        Route::get('/ecommerce/coupons', [CouponTaxController::class, 'coupons'])->name('ecommerce.coupons');
        Route::post('/ecommerce/coupons', [CouponTaxController::class, 'storeCoupon'])->name('ecommerce.coupons.store');
        Route::patch('/ecommerce/coupons/{coupon}', [CouponTaxController::class, 'updateCoupon'])->name('ecommerce.coupons.update');
        Route::delete('/ecommerce/coupons/{coupon}', [CouponTaxController::class, 'destroyCoupon'])->name('ecommerce.coupons.destroy');
        Route::get('/ecommerce/taxes', [CouponTaxController::class, 'taxes'])->name('ecommerce.taxes');
        Route::post('/ecommerce/taxes', [CouponTaxController::class, 'storeTax'])->name('ecommerce.taxes.store');
        Route::patch('/ecommerce/taxes/{taxRate}', [CouponTaxController::class, 'updateTax'])->name('ecommerce.taxes.update');
        Route::delete('/ecommerce/taxes/{taxRate}', [CouponTaxController::class, 'destroyTax'])->name('ecommerce.taxes.destroy');
        Route::get('/ecommerce/returns', [EcommerceController::class, 'returns'])->name('ecommerce.returns');
        Route::get('/ecommerce/returns/{orderReturn}/edit', [EcommerceController::class, 'editReturn'])->name('ecommerce.returns.edit');
        Route::patch('/ecommerce/returns/{orderReturn}', [EcommerceController::class, 'updateReturn'])->name('ecommerce.returns.update');
        Route::delete('/ecommerce/returns/{orderReturn}', [EcommerceController::class, 'destroyReturn'])->name('ecommerce.returns.destroy');
        Route::get('/ecommerce/shipments', [EcommerceController::class, 'shipments'])->name('ecommerce.shipments');
        Route::get('/ecommerce/invoices', [EcommerceController::class, 'invoices'])->name('ecommerce.invoices');
        Route::get('/ecommerce/orders/{order}/edit', [EcommerceController::class, 'editOrder'])->name('ecommerce.orders.edit');
        Route::patch('/ecommerce/orders/{order}', [EcommerceController::class, 'updateOrder'])->name('ecommerce.orders.update');

        /*
        |--------------------------------------------------------------------------
        | Commerce — Reviews Moderation
        |--------------------------------------------------------------------------
        */
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
        Route::patch('/reviews/{review}/status', [ReviewController::class, 'status'])->name('reviews.status');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

        /*
        |--------------------------------------------------------------------------
        | Commerce — Payment Settings
        |--------------------------------------------------------------------------
        */
        Route::get('/payment-settings', [PaymentSettingController::class, 'index'])->name('payments');
        Route::patch('/payment-settings', [PaymentSettingController::class, 'update'])->name('payments.update');

        /*
        |--------------------------------------------------------------------------
        | System — Store Settings (contact page + about stats)
        |--------------------------------------------------------------------------
        */
        Route::get('/store-settings', [StoreSettingController::class, 'index'])->name('store-settings');
        Route::patch('/store-settings', [StoreSettingController::class, 'update'])->name('store-settings.update');
        Route::get('/footer-settings', [StoreSettingController::class, 'footer'])->name('footer-settings');
        Route::patch('/footer-settings', [StoreSettingController::class, 'updateFooter'])->name('footer-settings.update');

        /*
        |--------------------------------------------------------------------------
        | System — Mail Settings
        |--------------------------------------------------------------------------
        */
        Route::get('/mail-settings', [MailSettingController::class, 'index'])->name('mail');
        Route::post('/mail-settings', [MailSettingController::class, 'update'])->name('mail.update');

        /* ---------- ABOUT PAGE (story + services + product showcase) ---------- */
        Route::get('about-settings', [AboutSettingController::class, 'index'])->name('about-settings');
        Route::post('about-settings', [AboutSettingController::class, 'update'])->name('about-settings.update');
        Route::get('/home-settings', [HomeSettingController::class, 'index'])->name('home-settings');
        Route::post('/home-settings', [HomeSettingController::class, 'update'])->name('home-settings.update');
        Route::get('/home-slider', [HomeSettingController::class, 'slider'])->name('home-slider');
        Route::put('/home-slider', [HomeSettingController::class, 'updateSlider'])->name('home-slider.update');
        Route::get('/page-headers', [PageHeaderSettingController::class, 'index'])->name('page-headers');
        Route::patch('/page-headers', [PageHeaderSettingController::class, 'update'])->name('page-headers.update');

        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');
        Route::get('/announcements/manage', [AnnouncementController::class, 'manage'])->name('announcements.manage');
        Route::get('/announcements/{announcement}/customers', [AnnouncementController::class, 'customers'])->name('announcements.customers');
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::patch('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
        Route::patch('/announcements/{announcement}/toggle', [AnnouncementController::class, 'toggle'])->name('announcements.toggle');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.delete');

        /*
        |--------------------------------------------------------------------------
        | Shipping settings
        |--------------------------------------------------------------------------
        */
        Route::get('shipping', [ShippingController::class, 'index'])->name('shipping');
        Route::post('shipping/settings', [ShippingController::class, 'saveSettings'])->name('shipping.settings');

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::patch('/users/{user}/block', [UserController::class, 'block'])->name('users.block');
        Route::patch('/users/{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');

        // more admin routes here...
    });
