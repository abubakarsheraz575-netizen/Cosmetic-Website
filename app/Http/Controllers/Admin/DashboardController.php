<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class DashboardController extends Controller
{
    /* ================= DEFENSIVE HELPERS (column-safe) ================= */

    private function oTotal($o): float
    {
        return (float)($o->total ?? $o->grand_total ?? $o->amount ?? 0);
    }

    private function isPaid($o): bool
    {
        return ($o->payment_status ?? null) === 'paid' || (bool)($o->is_paid ?? false);
    }

    private function ordUser($o)
    {
        try { return $o->user; } catch (\Throwable $e) { return null; }
    }

    private function itemProduct($it)
    {
        try { return $it->product; } catch (\Throwable $e) { return null; }
    }

    private function itemQtySum($o): int
    {
        try {
            return (int)$o->items->sum(fn ($it) => (int)($it->qty ?? $it->quantity ?? 1));
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /* ⭐⭐⭐ PAID-ONLY — dashboard ab SIRF paid orders load karta hai.
       Iska matlab: neeche ke SAARE stats (revenue, counts, series,
       donut, recent table) automatically paid-only hain. */
    private function loadOrders()
    {
        try {
            return Order::with('items.product', 'user')
                ->where('payment_status', 'paid')
                ->latest()
                ->get();
        } catch (\Throwable $e1) {
            try {
                return Order::with('items')
                    ->where('payment_status', 'paid')
                    ->latest()
                    ->get();
            } catch (\Throwable $e2) {
                /* last fallback: collection-level filter (agar query-level fail ho) */
                return Order::latest()->get()
                    ->filter(fn ($o) => $this->isPaid($o))
                    ->values();
            }
        }
    }

    private function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string)$name));
        $ini = strtoupper(substr($parts[0] ?? 'G', 0, 1) . substr($parts[1] ?? '', 0, 1));
        return $ini ?: 'G';
    }

    /* ================= PAGES ================= */

    public function index()
    {
        $d = $this->payload();
        return view('admin.pages.dashboard', ['d' => $d]);
    }

    public function data()
    {
        return response()->json(['ok' => true, 'd' => $this->payload()]);
    }

    /* ================= PAYLOAD (single source of truth — PAID ONLY) ================= */

    private function payload(): array
    {
        /* ⭐ paid-only collection — loadOrders() se hi filter ho chuka */
        $orders = $this->loadOrders();

        /* ---- Revenue (sab loaded orders paid hain — direct sum) ---- */
        $revOf = fn ($col) => $col->sum(fn ($o) => $this->oTotal($o));

        $revenue = round($revOf($orders), 2);
        $ordersCount = $orders->count();
        $shippedCount = $orders->filter(fn ($o) => in_array(strtolower($o->status ?? ''), ['shipped', 'delivered']))->count();
        $pendingCount = $orders->filter(fn ($o) => strtolower($o->status ?? '') === 'pending')->count();
        $itemsSold = $orders->sum(fn ($o) => $this->itemQtySum($o));
        if ($itemsSold === 0 && $ordersCount > 0) $itemsSold = $ordersCount;

        /* ---- This month vs last month (paid) ---- */
        $inRange = fn ($a, $b) => $orders->filter(fn ($o) => $o->created_at->between($a, $b));
        $mNow  = $inRange(now()->startOfMonth(), now()->endOfMonth());
        $mPrev = $inRange(now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth());

        $revNow  = round($revOf($mNow), 2);
        $revPrev = round($revOf($mPrev), 2);
        $revGrowth = $revPrev > 0 ? (int)round(($revNow - $revPrev) / $revPrev * 100) : ($revNow > 0 ? 100 : 0);
        $ordGrowth = $mPrev->count() > 0 ? (int)round(($mNow->count() - $mPrev->count()) / $mPrev->count() * 100) : ($mNow->count() > 0 ? 100 : 0);
        $shipPct = $ordersCount > 0 ? (int)round($shippedCount / $ordersCount * 100) : 0;

        /* ---- 8-month series (paid) ---- */
        $months = []; $revSeries = []; $ordSeries = [];
        for ($i = 7; $i >= 0; $i--) {
            $s = now()->subMonths($i)->startOfMonth();
            $e = now()->subMonths($i)->endOfMonth();
            $chunk = $inRange($s, $e);
            $months[]    = $s->format('M');
            $revSeries[] = round($revOf($chunk), 2);
            $ordSeries[] = $chunk->count();
        }

        /* ---- Last 7 days orders (mini bars — paid) ---- */
        $dayLabels = []; $dayVals = [];
        for ($i = 6; $i >= 0; $i--) {
            $s = now()->subDays($i)->startOfDay();
            $e = now()->subDays($i)->endOfDay();
            $dayLabels[] = $s->format('D');
            $dayVals[]   = $inRange($s, $e)->count();
        }

        /* ---- New customers by calendar day for the last seven days ---- */
        $uLabels = []; $uVals = [];
        $customerCountsByDay = User::where('role', 'user')
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->pluck('created_at')
            ->map(fn ($createdAt) => \Illuminate\Support\Carbon::parse($createdAt)->toDateString())
            ->countBy();
        for ($i = 6; $i >= 0; $i--) {
            $s = now()->subDays($i)->startOfDay();
            $uLabels[] = $s->format('D');
            $uVals[] = (int) ($customerCountsByDay[$s->toDateString()] ?? 0);
        }
        $totalCustomers = User::where('role', 'user')->count();
        $newThisWeek = array_sum($uVals);

        /* ---- Order status distribution (doughnut — paid only) ---- */
        $statusColors = [
            'pending'    => '#facc15',
            'processing' => '#3ec5e0',
            'shipped'    => '#17739b',
            'delivered'  => '#16a34a',
            'cancelled'  => '#ef4444',
        ];
        $statusCounts = $orders
            ->groupBy(fn ($o) => strtolower($o->status ?? 'pending'))
            ->map(fn ($g, $k) => [
                'key'   => $k,
                'label' => ucfirst($k),
                'count' => $g->count(),
                'color' => $statusColors[$k] ?? '#94a3b8',
            ])
            ->values()->toArray();

        /* ---- Top categories (unchanged) ---- */
        try {
            $cats = Category::withCount('products')->orderByDesc('products_count')->take(4)->get()
                ->map(fn ($c) => ['name' => $c->name, 'count' => (int)$c->products_count])
                ->toArray();
        } catch (\Throwable $e) {
            $cats = [];
        }

        /* ---- Customer satisfaction from published customer reviews ---- */
        $reviewStats = Review::query()
            ->where('status', 'approved')
            ->selectRaw('COUNT(*) as reviews_count, AVG(rating) as avg_rating')
            ->first();
        $avgRating = round((float) ($reviewStats->avg_rating ?? 0), 1);
        $reviewsCount = (int) ($reviewStats->reviews_count ?? 0);

        /* ---- Recent orders (6 — PAID ONLY, table mein sab "Paid" badge) ---- */
        $recent = $orders->take(6)->map(function ($o) {
            $prodName = 'Cosmetic Item'; $cat = ''; $emoji = '✨'; $img = null;
            try {
                $first = $o->items->first();
                if ($first) {
                    $p = $this->itemProduct($first);
                    if ($p) {
                        $prodName = $p->name;
                        try { $cat = $p->category->name ?? ''; } catch (\Throwable $t) {}
                        $emoji = $p->emoji ?: '✨';
                        try { $img = $p->images->first()->path ?? null; } catch (\Throwable $t) {}
                    } else {
                        $prodName = $first->product_name ?? 'Cosmetic Item';
                    }
                }
            } catch (\Throwable $t) {}

            $u = $this->ordUser($o);
            $custName = $u->name ?? ($o->name ?? 'Guest');

            return [
                'id'       => $o->id,
                'code'     => '#' . str_pad((string)$o->id, 5, '0', STR_PAD_LEFT),
                'product'  => $prodName,
                'cat'      => $cat,
                'emoji'    => $emoji,
                'img'      => $img,
                'customer' => $custName,
                'initials' => $this->initials($custName),
                'qty'      => $this->itemQtySum($o),
                'status'   => strtolower($o->status ?? 'pending'),
                'payment'  => $o->payment_method ?? 'Stripe',
                'paid'     => true,   /* ⭐ sab paid hain — loadOrders filter kar chuka */
                'total'    => $this->oTotal($o),
                'date'     => $o->created_at->format('M d, Y'),
            ];
        })->toArray();

        /* ---- Latest products (4 — unchanged) ---- */
        try { $lp = Product::with('images', 'category')->latest()->take(4)->get(); }
        catch (\Throwable $e) { $lp = Product::latest()->take(4)->get(); }

        $latest = $lp->map(function ($p) {
            $img = null;
            try { $img = $p->images->first()->path ?? null; } catch (\Throwable $t) {}
            try { $cat = $p->category->name ?? ''; } catch (\Throwable $t) { $cat = ''; }
            return [
                'name'  => $p->name,
                'cat'   => $cat,
                'price' => (float)($p->final_price ?? $p->price),
                'emoji' => $p->emoji ?: '✨',
                'img'   => $img,
                'time'  => $p->created_at->diffForHumans(),
            ];
        })->toArray();

        return [
            'revenue'        => $revenue,
            'revNow'         => $revNow,
            'revGrowth'      => $revGrowth,
            'ordGrowth'      => $ordGrowth,
            'shipPct'        => $shipPct,
            'ordersCount'    => $ordersCount,
            'shippedCount'   => $shippedCount,
            'pendingCount'   => $pendingCount,
            'itemsSold'      => $itemsSold,
            'months'         => $months,
            'revSeries'      => $revSeries,
            'ordSeries'      => $ordSeries,
            'dayLabels'      => $dayLabels,
            'dayVals'        => $dayVals,
            'uLabels'        => $uLabels,
            'uVals'          => $uVals,
            'totalCustomers' => $totalCustomers,
            'newThisWeek'    => $newThisWeek,
            'statusCounts'   => $statusCounts,
            'cats'           => $cats,
            'avgRating'      => $avgRating,
            'reviewsCount'   => $reviewsCount,
            'recent'         => $recent,
            'latest'         => $latest,
            'range'          => now()->startOfMonth()->format('M d') . ' — ' . now()->endOfMonth()->format('M d'),
        ];
    }
}