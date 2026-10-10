<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Category;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $editAnnouncement = $request->integer('edit')
            ? Announcement::with('categories:id')->findOrFail($request->integer('edit'))
            : null;

        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'icon']);

        return view('admin.pages.announcements', compact('categories', 'editAnnouncement'));
    }

    public function manage()
    {
        $recipientCounts = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('announcement_category', 'announcement_category.category_id', '=', 'products.category_id')
            ->whereNotNull('orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.status', '!=', 'cancelled')
            ->select('announcement_category.announcement_id')
            ->selectRaw('COUNT(DISTINCT orders.user_id) as recipient_count')
            ->groupBy('announcement_category.announcement_id');

        $announcements = Announcement::query()
            ->leftJoinSub($recipientCounts, 'audience_counts', function ($join) {
                $join->on('audience_counts.announcement_id', '=', 'announcements.id');
            })
            ->select('announcements.*')
            ->selectRaw('COALESCE(audience_counts.recipient_count, 0) as recipient_count')
            ->with('categories:id,name,icon')
            ->latest('announcements.created_at')
            ->paginate(10);

        $announcementStats = [
            'total' => Announcement::count(),
            'published' => Announcement::where('is_active', true)->count(),
            'paused' => Announcement::where('is_active', false)->count(),
        ];

        return view('admin.pages.manage-announcements', compact('announcements', 'announcementStats'));
    }

    public function customers(Announcement $announcement)
    {
        $announcement->load('categories:id,name');
        $categoryIds = $announcement->categories()->select('categories.id');
        $deliveryMaxDays = max(1, (int) Setting::get('delivery_max_days', 7));
        $orders = Order::query()
            ->whereNotNull('user_id')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->whereHas('items', fn ($items) => $items->whereHas('product', fn ($product) => $product->whereIn('category_id', $categoryIds)))
            ->with([
                'user:id,name,email,phone',
                'items' => fn ($items) => $items
                    ->whereHas('product', fn ($product) => $product->whereIn('category_id', $categoryIds))
                    ->with('product:id,name,emoji,category_id'),
            ])
            ->latest()
            ->paginate(15);

        return view('admin.pages.announcement-customers', compact('announcement', 'orders', 'deliveryMaxDays'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $announcement = DB::transaction(function () use ($data) {
            $announcement = Announcement::create([
                'title' => trim($data['title']),
                'message' => trim($data['message']),
                'type' => $data['type'],
                'is_active' => $data['is_active'] ?? true,
            ]);
            $announcement->categories()->sync($data['category_ids']);

            return $announcement;
        });

        return response()->json([
            'message' => 'Announcement created.',
            'announcement' => $announcement->load('categories:id,name'),
            'recipient_count' => $this->recipientCount($announcement),
        ], 201);
    }

    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $announcement) {
            $announcement->update([
                'title' => trim($data['title']),
                'message' => trim($data['message']),
                'type' => $data['type'],
                'is_active' => $data['is_active'] ?? false,
            ]);
            $announcement->categories()->sync($data['category_ids']);
        });

        return response()->json([
            'message' => 'Announcement updated.',
            'announcement' => $announcement->fresh()->load('categories:id,name'),
            'recipient_count' => $this->recipientCount($announcement),
        ]);
    }

    public function toggle(Announcement $announcement): JsonResponse
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return response()->json([
            'message' => $announcement->is_active ? 'Announcement published.' : 'Announcement paused.',
            'is_active' => $announcement->is_active,
        ]);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json(['message' => 'Announcement deleted.']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['required', 'in:info,delay,good,warning,success'],
            'category_ids' => ['required', 'array', 'min:1', 'max:100'],
            'category_ids.*' => ['required', 'integer', 'distinct', 'exists:categories,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function recipientCount(Announcement $announcement): int
    {
        return (int) DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereNotNull('orders.user_id')
            ->where('orders.payment_status', 'paid')
            ->where('orders.status', '!=', 'cancelled')
            ->whereIn('products.category_id', $announcement->categories()->select('categories.id'))
            ->distinct()
            ->count('orders.user_id');
    }
}
