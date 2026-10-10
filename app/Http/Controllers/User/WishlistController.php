<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

class WishlistController extends Controller
{
    public function index()
    {
        $items = auth()->user()->wishlist()
            ->with(['product.images', 'product.category'])
            ->latest('wishlists.created_at')
            ->get();

        return view('user.pages.wishlist', ['items' => $items]);
    }
}