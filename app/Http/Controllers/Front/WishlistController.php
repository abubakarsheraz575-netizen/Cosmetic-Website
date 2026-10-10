<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'status'  => 'guest',
                'message' => 'Please log in to save favourites 💖',
            ], 401);
        }

        $data = $request->validate(['product_id' => 'required|integer']);

        $existing = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'status'  => 'removed',
                'message' => 'Removed from your wishlist',
                'count'   => auth()->user()->wishlist()->count(),
            ]);
        }

        Wishlist::create([
            'user_id'    => auth()->id(),
            'product_id' => $data['product_id'],
        ]);

        return response()->json([
            'status'  => 'added',
            'message' => 'Saved to your wishlist 💖',
            'count'   => auth()->user()->wishlist()->count(),
        ]);
    }
}