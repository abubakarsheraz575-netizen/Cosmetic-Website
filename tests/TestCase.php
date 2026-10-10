<?php

namespace Tests;

use App\Http\Controllers\Admin\CouponTaxController;
use App\Http\Controllers\Front\CheckoutController;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Route;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!Route::has('admin.ecommerce.coupons.store')) {
            Route::post('/__tests/admin/ecommerce/coupons', [CouponTaxController::class, 'storeCoupon'])
                ->name('admin.ecommerce.coupons.store');
        }
        if (!Route::has('admin.ecommerce.coupons')) {
            Route::middleware('web')->get('/__tests/admin/ecommerce/coupons', [CouponTaxController::class, 'coupons'])
                ->name('admin.ecommerce.coupons');
        }
        if (!Route::has('admin.ecommerce.coupons.update')) {
            Route::middleware('web')->patch('/__tests/admin/ecommerce/coupons/{coupon}', [CouponTaxController::class, 'updateCoupon'])
                ->name('admin.ecommerce.coupons.update');
        }
        if (!Route::has('admin.ecommerce.coupons.destroy')) {
            Route::middleware('web')->delete('/__tests/admin/ecommerce/coupons/{coupon}', [CouponTaxController::class, 'destroyCoupon'])
                ->name('admin.ecommerce.coupons.destroy');
        }
        if (!Route::has('admin.ecommerce.taxes.store')) {
            Route::post('/__tests/admin/ecommerce/taxes', [CouponTaxController::class, 'storeTax'])
                ->name('admin.ecommerce.taxes.store');
        }
        if (!Route::has('admin.ecommerce.taxes.update')) {
            Route::middleware('web')->patch('/__tests/admin/ecommerce/taxes/{taxRate}', [CouponTaxController::class, 'updateTax'])
                ->name('admin.ecommerce.taxes.update');
        }
        if (!Route::has('admin.ecommerce.taxes.destroy')) {
            Route::middleware('web')->delete('/__tests/admin/ecommerce/taxes/{taxRate}', [CouponTaxController::class, 'destroyTax'])
                ->name('admin.ecommerce.taxes.destroy');
        }
        if (!Route::has('admin.ecommerce.taxes')) {
            Route::middleware('web')->get('/__tests/admin/ecommerce/taxes', [CouponTaxController::class, 'taxes'])
                ->name('admin.ecommerce.taxes');
        }
        if (!Route::has('checkout.coupon')) {
            Route::post('/__tests/checkout/coupon', [CheckoutController::class, 'applyCoupon'])
                ->name('checkout.coupon');
        }

        $routes = app('router')->getRoutes();
        $routes->refreshNameLookups();
        app('url')->setRoutes($routes);
    }
}
