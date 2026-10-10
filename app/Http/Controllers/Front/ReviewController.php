<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /* LIST — frontend ko sirf APPROVED reviews milte hain */
    public function index($productId)
    {
        $reviews = Review::where('product_id', $productId)
            ->where('status', 'approved')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'name'    => $r->name,
                'rating'  => (int) $r->rating,
                'comment' => $r->comment,
                'date'    => optional($r->created_at)->format('M d, Y'),
            ]);

        return response()->json(['status' => 'success', 'reviews' => $reviews]);
    }

    /* STORE — LOGIN ZAROORI 🔒 + hamesha PENDING (admin approve karega) */
    public function store(Request $request, $productId)
    {
        /* 🔒 GUEST BLOCK — login ke baghair review jayega hi nahi */
        if (!auth()->check()) {
            return response()->json([
                'status'   => 'guest',
                'message'  => 'Please login to write a review.',
                'redirect' => '/login-redirect?redirect=' . urlencode(url()->previous()),
            ], 401);
        }

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1500',
        ]);

        $review = new Review();
        $review->product_id = (int) $productId;
        $review->name       = auth()->user()->name;   // naam hamesha logged-in user ka
        $review->rating     = (int) $data['rating'];
        $review->comment    = trim($data['comment']);
        $review->status     = 'pending';              // ✅ seedha admin ke paas
        $review->save();

        return response()->json([
            'status'  => 'success',
            'pending' => true,
            'message' => 'Shukriya! Aapka review submit ho gaya — admin approval ke baad live hoga.',
        ]);
    }
}