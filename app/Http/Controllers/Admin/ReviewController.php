<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * ⭐ Reviews moderation page
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'all');
        if (! in_array($filter, ['all', 'pending', 'approved', 'blocked'])) {
            $filter = 'all';
        }

        $reviews = Review::query()
            ->with(['user', 'product.images', 'product.category'])
            ->latest()
            ->get();

        $stats = [
            'total'    => Review::count(),
            'pending'  => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'blocked'  => Review::where('status', 'blocked')->count(),
        ];

        return view('admin.pages.reviews', compact('reviews', 'stats', 'filter'));
    }

    /**
     * ✅ / 🚫 / ⏳ status change
     */
    public function status(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,blocked',
        ]);

        $review->status = $request->status;
        $review->save();

        $msg = match ($review->status) {
            'approved' => 'Review approved — ab Testimonials page pe live hai. ✅',
            'blocked'  => 'Review blocked — website se hide ho gaya. 🚫',
            default    => 'Review pending pe wapas set ho gaya. ⏳',
        };

        return response()->json([
            'status'        => 'success',
            'message'       => $msg,
            'review_status' => $review->status,
        ]);
    }

    /**
     * 🗑️ Delete
     */
    public function destroy(Review $review)
    {
        $review->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Review deleted permanently.',
        ]);
    }
}