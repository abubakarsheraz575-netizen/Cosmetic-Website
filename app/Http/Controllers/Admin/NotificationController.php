<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $this->notifications($request->user());
        $perPage = 15;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $notifications->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $items,
            $notifications->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.pages.notifications', [
            'notifications' => $paginator,
            'unreadCount' => $notifications->where('read', false)->count(),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $notifications = $this->notifications($request->user());

        return response()->json([
            'notifications' => $notifications->take(8)->values(),
            'unread_count' => $notifications->where('read', false)->count(),
            'total_count' => $notifications->count(),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'notification_key' => ['required', 'string', 'max:191'],
        ]);

        $user = $request->user();
        $exists = $this->notifications($user)->contains('key', $data['notification_key']);
        if (! $exists) {
            throw ValidationException::withMessages([
                'notification_key' => 'This notification is no longer available.',
            ]);
        }

        $this->storeRead($user, [$data['notification_key']]);

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $keys = $this->notifications($user)->pluck('key')->all();
        $this->storeRead($user, $keys);

        return response()->json(['ok' => true, 'read_count' => count($keys)]);
    }

    private function notifications(User $admin): Collection
    {
        $items = collect();

        foreach (Order::query()->latest()->get() as $order) {
            $items->push($this->item(
                'order-created:'.$order->id,
                'order',
                'New order received #'.$order->order_number,
                $order->name.' · £'.number_format((float) $order->total, 2),
                $order->created_at,
                'ri-shopping-bag-3-line',
                'i-green',
                route('admin.orders')
            ));

            if ($order->payment_status === 'paid') {
                $items->push($this->item(
                    'payment-confirmed:'.$order->id,
                    'payment',
                    'Payment confirmed',
                    $order->name.' paid £'.number_format((float) $order->total, 2).' via '.str_replace('_', ' ', $order->payment_method),
                    $order->updated_at,
                    'ri-money-dollar-circle-line',
                    'i-amber',
                    route('admin.orders')
                ));
            }
        }

        foreach (Product::query()->where('stock', '<=', 10)->orderBy('stock')->get() as $product) {
            $items->push($this->item(
                'low-stock:'.$product->id.':'.$product->stock,
                'stock',
                'Low stock warning',
                $product->name.' — only '.$product->stock.' left',
                $product->updated_at,
                'ri-error-warning-line',
                'i-red',
                route('admin.products.index')
            ));
        }

        foreach (User::query()->where('role', 'user')->latest()->get() as $customer) {
            $items->push($this->item(
                'customer-registered:'.$customer->id,
                'customer',
                'New customer registered',
                $customer->name.' created an account',
                $customer->created_at,
                'ri-user-add-line',
                'i-blue',
                route('admin.users')
            ));
        }

        foreach (Review::query()->with('product')->latest()->get() as $review) {
            $items->push($this->item(
                'review-created:'.$review->id,
                'review',
                'New '.$review->rating.'★ review',
                ($review->product?->name ?? 'A product').' received a '.$review->rating.'-star review',
                $review->created_at,
                'ri-star-smile-line',
                'i-violet',
                route('admin.reviews')
            ));
        }

        $readKeys = DB::table('admin_notification_reads')
            ->where('admin_id', $admin->id)
            ->pluck('notification_key')
            ->flip();

        return $items
            ->map(function (array $item) use ($readKeys) {
                $item['read'] = $readKeys->has($item['key']);

                return $item;
            })
            ->sortByDesc(fn (array $item) => $item['timestamp'])
            ->values();
    }

    private function item(
        string $key,
        string $type,
        string $title,
        string $description,
        mixed $createdAt,
        string $icon,
        string $iconClass,
        string $url
    ): array {
        return [
            'key' => $key,
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'time' => $createdAt->diffForHumans(),
            'timestamp' => $createdAt->timestamp,
            'icon' => $icon,
            'icon_class' => $iconClass,
            'url' => $url,
            'read' => false,
        ];
    }

    private function storeRead(User $admin, array $keys): void
    {
        if ($keys === []) {
            return;
        }

        $now = now();
        DB::table('admin_notification_reads')->insertOrIgnore(
            collect($keys)->unique()->map(fn (string $key) => [
                'admin_id' => $admin->id,
                'notification_key' => $key,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }
}
