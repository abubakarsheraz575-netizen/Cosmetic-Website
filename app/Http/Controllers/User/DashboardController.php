<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /* ================= DEFENSIVE HELPERS ================= */

    private function oTotal($o): float
    {
        return (float)($o->total ?? $o->grand_total ?? $o->amount ?? 0);
    }

    private function isPaid($o): bool
    {
        return ($o->payment_status ?? null) === 'paid' || (bool)($o->is_paid ?? false);
    }

    private function qtySum($o): int
    {
        try {
            return (int)$o->items->sum(fn ($it) => (int)($it->qty ?? $it->quantity ?? 1));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /* ================= PAGES / API ================= */

    public function index()
    {
        $d = $this->payload();
        return view('user.pages.dashboard', ['d' => $d]);
    }

    public function data()
    {
        return response()->json(['ok' => true, 'd' => $this->payload()]);
    }

    public function removeWishlist(Request $request)
    {
        $request->validate(['product_id' => 'required|integer']);
        auth()->user()->wishlist()->where('product_id', $request->product_id)->delete();
        return response()->json(['ok' => true, 'count' => auth()->user()->wishlist()->count()]);
    }

    /* ================= PAYLOAD ================= */

    private function payload(): array
    {
        $user = auth()->user();

        /* ---- Orders (relation ho ya na ho — dono safe) ---- */
        try { $orders = $user->orders()->with('items')->latest()->get(); }
        catch (\Throwable $e) {
            try { $orders = Order::where('user_id', $user->id)->with('items')->latest()->get(); }
            catch (\Throwable $e2) { $orders = Order::where('user_id', $user->id)->latest()->get(); }
        }

        $ordersCount = $orders->count();
        $thisMonth   = $orders->filter(fn ($o) => $o->created_at->isCurrentMonth())->count();

        try { $wishlistCount = $user->wishlist()->count(); } catch (\Throwable $e) { $wishlistCount = 0; }
        try { $reviewsCount  = $user->reviews()->count(); } catch (\Throwable $e) { $reviewsCount = 0; }

        /* ---- Reward points: £1 spent = 1 pt ---- */
        $spent  = $orders->filter(fn ($o) => $this->isPaid($o))->sum(fn ($o) => $this->oTotal($o));
        $points = (int)floor($spent);

        /* ---- Tier (Silver 500 / Gold 1500 / Platinum 3000) ---- */
        $tiers = [['Silver', 500], ['Gold', 1500], ['Platinum', 3000]];
        $tierName = 'Silver'; $nextGoal = 500;
        foreach ($tiers as $t) { if ($points >= $t[1]) $tierName = $t[0]; }
        foreach ($tiers as $t) { if ($points < $t[1]) { $nextGoal = $t[1]; break; } $nextGoal = 3000; }
        $tierPct = $points > 0 ? min(100, (int)round($points / max(1, $nextGoal) * 100)) : 0;

        /* ---- 8-month chart ---- */
        $chart = [];
        for ($i = 7; $i >= 0; $i--) {
            $s = now()->subMonths($i)->startOfMonth();
            $e = now()->subMonths($i)->endOfMonth();
            $chart[] = [
                'm' => $s->format('M'),
                'v' => $orders->filter(fn ($o) => $o->created_at->between($s, $e))->count(),
            ];
        }

        /* ---- Recent orders (5) ---- */
        $recent = $orders->take(5)->map(fn ($o) => [
            'id'     => $o->id,
            'code'   => '#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT),
            'items'  => $this->qtySum($o),
            'total'  => $this->oTotal($o),
            'status' => ucfirst(strtolower($o->status ?? 'pending')),
            'date'   => $o->created_at->format('M d, Y'),
        ])->toArray();

        /* ---- Wishlist products (4) — hasMany ya belongsToMany dono safe ---- */
        try { $wl = $user->wishlist()->latest()->get(); } catch (\Throwable $e) { $wl = collect(); }

        $wishlist = collect();
        foreach ($wl as $w) {
            $p = null;
            try { $p = $w->product; } catch (\Throwable $e) { $p = $w; }
            if ($p && ($p->name ?? null)) $wishlist->push($p);
            if ($wishlist->count() >= 4) break;
        }

        $wishlist = $wishlist->map(function ($p) {
            $img = null;
            try { $img = $p->images->first()->path ?? null; } catch (\Throwable $t) {}
            return [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => (float)($p->final_price ?? $p->price),
                'img'   => $img,
                'emoji' => $p->emoji ?: '✨',
                'url'   => url('/product/' . $p->slug),
            ];
        })->values()->toArray();

        /* ---- Beauty profile (real user fields) ---- */
        $beauty = [
            ['Skin Type',   $user->skin_type   ?: 'Not set'],
            ['Hair Type',   $user->hair_type   ?: 'Not set'],
            ['Shade Match', $user->shade_match ?: 'Not set'],
            ['Loves',       $user->loves       ?: 'Not set'],
        ];

        $filled = collect([$user->phone, $user->skin_type, $user->hair_type, $user->shade_match, $user->loves])
            ->filter(fn ($v) => $v !== null && trim((string)$v) !== '')->count();
        $completion = min(100, 40 + $filled * 12);

        /* ---- Notifications (real orders se) ---- */
        $colorMap = ['delivered' => '#1f9d63', 'shipped' => '#178f9d', 'processing' => '#b5831f', 'pending' => '#b5831f', 'cancelled' => '#e0566e'];
        $notifs = $orders->take(3)->map(function ($o) use ($colorMap) {
            $st = strtolower($o->status ?? 'pending');
            $msg = match ($st) {
                'delivered'  => 'Order <b>#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT) . '</b> delivered 🎉',
                'shipped'    => 'Order <b>#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT) . '</b> is on its way 🚚',
                'cancelled'  => 'Order <b>#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT) . '</b> was cancelled',
                default      => 'Order <b>#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT) . '</b> is being processed 💄',
            };
            return ['color' => $colorMap[$st] ?? '#178f9d', 'text' => $msg, 'time' => $o->created_at->diffForHumans()];
        })->toArray();

        return [
            'stats' => [
                ['label' => 'Total Orders',  'value' => $ordersCount,   'icon' => 'bag',   'trend' => $thisMonth > 0 ? '+' . $thisMonth . ' this month' : 'No orders yet', 'up' => true],
                ['label' => 'Wishlist',      'value' => $wishlistCount, 'icon' => 'heart', 'trend' => 'Saved for later', 'up' => true],
                ['label' => 'Reviews',       'value' => $reviewsCount,  'icon' => 'star',  'trend' => $reviewsCount > 0 ? 'Thanks for sharing!' : 'Share your first review', 'up' => true],
                ['label' => 'Reward Points', 'value' => $points,        'icon' => 'gem',   'trend' => '£1 spent = 1 pt', 'up' => true],
            ],
            'orders'      => $recent,
            'chart'       => $chart,
            'wishlist'    => $wishlist,
            'wishlistCount' => $wishlistCount,
            'beauty'      => $beauty,
            'completion'  => $completion,
            'notifs'      => $notifs,
            'points'      => $points,
            'nextGoal'    => $nextGoal,
            'tierName'    => $tierName,
            'tierPct'     => $tierPct,
        ];
    }
}