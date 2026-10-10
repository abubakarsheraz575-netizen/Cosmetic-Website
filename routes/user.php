<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\WishlistController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\ProductSubmissionController;
use App\Http\Controllers\User\MarketplaceController as UserMarketplaceController;
use App\Http\Controllers\User\AnnouncementController;
use App\Http\Controllers\Support\SupportController;


Route::middleware(['auth', 'is_user'])
    ->prefix('user')
    ->name('user.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard (dynamic + AJAX data + wishlist remove)
        | ⭐ LOGIN-RETURN ENFORCER — review/shop flow se aaya tha to dashboard
        |   khulte hi product#reviews pe bounce. Login page ka JS jo bhi kare.
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', function (Illuminate\Http\Request $request) {
            if ($request->session()->has('auth_return_to')) {
                $to = $request->session()->get('auth_return_to');
                $request->session()->forget('auth_return_to');

                /* safety: sirf shop/product URLs pe bounce */
                $path = (string) (parse_url($to, PHP_URL_PATH) ?: '');
                if ($path === '/shop' || str_starts_with($path, '/product/')) {
                    return redirect()->to($to);
                }
            }

            return app(\App\Http\Controllers\User\DashboardController::class)->index();
        })->name('dashboard');

        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
        Route::post('/dashboard/wishlist/remove', [DashboardController::class, 'removeWishlist'])->name('dashboard.wishlist.remove');
        Route::get('/dashboard/products', [ProductSubmissionController::class, 'index'])->name('product-submissions.index');
        Route::post('/dashboard/products', [ProductSubmissionController::class, 'store'])->name('product-submissions.store');
        Route::get('/dashboard/products/submissions', [ProductSubmissionController::class, 'submissions'])->name('product-submissions.submissions');
        Route::get('/marketplace', [UserMarketplaceController::class, 'index'])->name('marketplace');
        Route::get('/marketplace/withdrawals', [UserMarketplaceController::class, 'withdrawals'])->name('marketplace.withdrawals');
        Route::get('/marketplace/messages', [UserMarketplaceController::class, 'messageFeed'])->name('marketplace.messages');
        Route::post('/marketplace/messages', [UserMarketplaceController::class, 'sendMessage'])
            ->middleware('throttle:30,1')->name('marketplace.messages.send');
        Route::post('/marketplace/withdrawals', [UserMarketplaceController::class, 'requestWithdrawal'])
            ->middleware('throttle:5,1')->name('marketplace.withdrawals.store');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/returns', [OrderController::class, 'returns'])->name('returns');
        Route::post('/orders/{order}/items/{orderItem}/returns', [OrderController::class, 'requestReturn'])
            ->middleware('throttle:10,1')->name('orders.returns.store');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements');

        Route::get('/profile', fn () => view('user.pages.profile'))->name('profile');

        Route::get('/support', [SupportController::class, 'userPage'])->name('support');
        Route::get('/support/messages', [SupportController::class, 'userMessages'])->name('support.messages');
        Route::post('/support/heartbeat', [SupportController::class, 'userHeartbeat'])->name('support.heartbeat');
        Route::post('/support/messages', [SupportController::class, 'userSend'])
            ->middleware('throttle:30,1')->name('support.send');
        Route::patch('/support/messages/{message}', [SupportController::class, 'userEditMessage'])
            ->middleware('throttle:30,1')->name('support.messages.edit');
        Route::delete('/support/messages/{message}', [SupportController::class, 'userDeleteMessage'])
            ->name('support.messages.delete');
        Route::post('/support/messages/{message}/pin', [SupportController::class, 'userTogglePin'])
            ->name('support.messages.pin');
        Route::post('/support/messages/{message}/star', [SupportController::class, 'userToggleStar'])
            ->name('support.messages.star');
        Route::post('/support/messages/{message}/report', [SupportController::class, 'userReportMessage'])
            ->middleware('throttle:10,1')->name('support.messages.report');
        Route::delete('/support/conversation', [SupportController::class, 'userDeleteConversation'])
            ->name('support.conversation.delete');

        /* profile actions — always JSON */
        Route::patch('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/prefs', [ProfileController::class, 'updatePrefs'])->name('profile.prefs');
        Route::patch('/password/update', [ProfileController::class, 'updatePassword'])->name('password.update');

        // more user routes here...
    });