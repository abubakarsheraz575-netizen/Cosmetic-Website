{{-- ============================================================
     TESTIMONIALS PAGE — DYNAMIC (admin-approved reviews only)
============================================================ --}}
@include('front-end.include.header')
@include('front-end.include.loader')
@php
    /* 🛡️ SELF-SUFFICIENT: agar route variables na bheje to khud load karo */
    $reviews  = $reviews  ?? \App\Models\Review::where('status', 'approved')->latest()->get();
    $products = $products ?? \App\Models\Product::whereIn('id', $reviews->pluck('product_id')->unique())->get()->keyBy('id');

    $hasReal = $reviews->count() > 0;
    $featured = $hasReal ? $reviews->first() : null;
    $fProd = $featured ? ($products[$featured->product_id] ?? null) : null;
    $avg = $hasReal ? round($reviews->avg('rating'), 1) : 0;
    $total = $reviews->count();
    $dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($reviews as $rv) { if (isset($dist[$rv->rating])) $dist[$rv->rating]++; }
@endphp
<style>
    /* ---------- PAGE HERO ---------- */
    .page-hero {
        position: relative;
        min-height: 46vh;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        background:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%);
    }

    .page-hero::after {
        content: '';
        position: absolute;
        left: 0; right: 0; bottom: 0;
        height: 120px;
        background: linear-gradient(180deg, transparent, rgba(247, 248, 248, .35));
        pointer-events: none;
    }

    .ph-inner { position: relative; z-index: 2; padding: 150px 20px 80px; }

    .ph-title {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(38px, 6vw, 62px);
        font-weight: 300;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: #fff;
        animation: heroUp .9s cubic-bezier(.22, 1, .36, 1) both;
    }

    .ph-crumb {
        margin-top: 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: rgba(255, 255, 255, .85);
        animation: heroUp .9s cubic-bezier(.22, 1, .36, 1) .12s both;
    }

    .ph-crumb a { color: #a9e8ee; }
    .ph-crumb a:hover { color: #fff; }

    @keyframes heroUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: none; }
    }

    /* ---------- FEATURED QUOTE ---------- */
    .feat-sec { padding: 90px 0 40px; background: var(--bg); position: relative; overflow: hidden; }

    .feat-ghost {
        position: absolute;
        top: 30px;
        right: -40px;
        font-family: 'Allura', cursive;
        font-size: clamp(140px, 22vw, 320px);
        line-height: 1;
        color: rgba(56, 170, 180, .06);
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
    }

    .feat-card {
        position: relative;
        z-index: 2;
        max-width: 820px;
        margin: 0 auto;
        background: #fff;
        padding: 55px 60px 45px;
        text-align: center;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .08);
        border-top: 4px solid var(--teal);
        transition: transform .4s ease, box-shadow .4s ease;
    }

    .feat-card:hover { transform: translateY(-6px); box-shadow: 0 34px 80px rgba(0, 0, 0, .12); }

    .feat-card .stars { color: #f0b429; letter-spacing: 5px; font-size: 16px; margin-bottom: 18px; }
    .feat-card .st { color: #dfe3e5; }
    .feat-card .st.on { color: #f0b429; }

    .feat-card blockquote {
        font-family: 'Montserrat', sans-serif;
        font-size: clamp(17px, 2.2vw, 22px);
        font-weight: 300;
        color: #2c3036;
        line-height: 1.7;
    }

    .feat-person { margin-top: 26px; display: flex; align-items: center; justify-content: center; gap: 14px; }

    .feat-person .nm { font-family: 'Montserrat', sans-serif; font-weight: 600; font-size: 14px; color: #222; }
    .feat-person .rl { font-size: 12.5px; color: #9aa0a4; }

    /* initials avatar (reviews mein photo nahi hoti) */
    .u-av {
        width: 58px; height: 58px; border-radius: 50%;
        background: linear-gradient(135deg, var(--teal), #2b7fb8);
        color: #fff; display: flex; align-items: center; justify-content: center;
        font: 700 20px 'Montserrat', sans-serif;
        border: 2px solid rgba(56, 170, 180, .5);
        box-shadow: 0 6px 16px rgba(56, 170, 180, .3);
        flex-shrink: 0;
    }

    /* ---------- SUMMARY STRIP ---------- */
    .sum-sec { padding: 50px 0 20px; background: var(--bg); }

    .sum-card {
        max-width: 820px;
        margin: 0 auto;
        background: linear-gradient(135deg, #0d3b40, #2a9aa4);
        color: #fff;
        padding: 34px 40px;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 40px;
        align-items: center;
        box-shadow: 0 20px 50px rgba(13, 59, 64, .3);
    }

    .sum-score { text-align: center; }

    .sum-score .big {
        font-family: 'Montserrat', sans-serif;
        font-size: 52px;
        font-weight: 800;
        line-height: 1;
    }

    .sum-score .stars { color: #ffd76e; letter-spacing: 3px; margin-top: 8px; font-size: 13px; }
    .sum-score .st { color: rgba(255, 255, 255, .3); }
    .sum-score .st.on { color: #ffd76e; }
    .sum-score p { font-size: 11.5px; color: rgba(255, 255, 255, .75); margin-top: 6px; letter-spacing: 1px; text-transform: uppercase; }

    .sum-bars { display: grid; gap: 9px; }

    .sum-row { display: flex; align-items: center; gap: 12px; font-size: 12px; }

    .sum-row b { width: 42px; font-weight: 600; color: rgba(255, 255, 255, .9); }

    .sum-track { flex: 1; height: 8px; border-radius: 20px; background: rgba(255, 255, 255, .15); overflow: hidden; }

    .sum-track i {
        display: block;
        height: 100%;
        width: 0;
        border-radius: 20px;
        background: linear-gradient(90deg, #a9e8ee, #ffd76e);
        transition: width 1.2s cubic-bezier(.22, 1, .36, 1);
    }

    .sum-row span { width: 44px; text-align: right; color: rgba(255, 255, 255, .7); }

    /* ---------- TESTIMONIALS LIST ---------- */
    .tlist-sec { padding: 70px 0 95px; background: var(--bg); position: relative; overflow: hidden; }

    .tlist-ghost {
        position: absolute;
        bottom: 40px;
        left: -60px;
        font-family: 'Allura', cursive;
        font-size: clamp(160px, 24vw, 360px);
        line-height: 1;
        color: rgba(56, 170, 180, .05);
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
    }

    .tlist-grid {
        position: relative;
        z-index: 2;
        display: grid;
        grid-template-columns: .85fr 1.15fr;
        gap: 50px;
        align-items: start;
    }

    .tlist-img {
        position: sticky;
        top: 30px;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: -24px 24px 0 rgba(56, 170, 180, .14), 0 30px 60px rgba(0, 0, 0, .12);
    }

    .tlist-img img { width: 100%; aspect-ratio: 3/3.8; object-fit: cover; transition: transform 4s ease; }
    .tlist-img:hover img { transform: scale(1.06); }

    .tlist-img figcaption {
        position: absolute;
        left: 22px;
        bottom: 22px;
        background: rgba(255, 255, 255, .93);
        padding: 12px 18px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--teal);
        box-shadow: 0 12px 28px rgba(0, 0, 0, .15);
    }

    /* cards */
    .tstack { display: grid; gap: 22px; }

    .tcard {
        background: #fff;
        padding: 28px 30px;
        box-shadow: 0 14px 40px rgba(0, 0, 0, .07);
        border-left: 3px solid transparent;
        transition: transform .35s ease, box-shadow .35s ease, border-color .35s ease;
    }

    .tcard:hover {
        transform: translateY(-5px) translateX(4px);
        border-left-color: var(--teal);
        box-shadow: 0 24px 55px rgba(0, 0, 0, .11);
    }

    .tcard .stars { color: #f0b429; font-size: 13px; letter-spacing: 3px; margin-bottom: 12px; }
    .tcard .st { color: #dfe3e5; }
    .tcard .st.on { color: #f0b429; }

    .tcard p { font-size: 14px; color: #4a4f55; margin-bottom: 18px; }

    .tcard-prod {
        display: inline-flex; align-items: center; gap: 6px;
        font: 600 11.5px 'Montserrat', sans-serif; letter-spacing: .5px;
        color: var(--teal); background: rgba(56, 170, 180, .08);
        padding: 5px 12px; border-radius: 20px;
        margin-bottom: 12px; transition: .25s;
    }
    .tcard-prod:hover { background: rgba(56, 170, 180, .16); color: var(--teal-dark); }

    .tcard-foot { display: flex; align-items: center; gap: 12px; }

    .tcard-foot .u-av { width: 44px; height: 44px; font: 700 15px 'Montserrat', sans-serif; }

    .tcard-foot .nm { font-family: 'Montserrat', sans-serif; font-size: 13.5px; font-weight: 600; color: #222; }
    .tcard-foot .dt { font-size: 11.5px; color: #9aa0a4; }

    .tcard-foot .verified {
        margin-left: auto;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: #2bb673;
        background: rgba(43, 182, 115, .1);
        padding: 4px 10px;
        border-radius: 20px;
    }

    /* ---------- EMPTY STATE (jab koi approved review na ho) ---------- */
    .empty-sec { padding: 90px 0; background: var(--bg); }
    .empty-card {
        max-width: 560px; margin: 0 auto; text-align: center;
        background: #fff; padding: 60px 40px;
        box-shadow: 0 24px 70px rgba(0, 0, 0, .08);
        border-top: 4px solid var(--teal);
    }
    .empty-card .e-ic { font-size: 56px; margin-bottom: 14px; }
    .empty-card h3 { font-family: 'Montserrat', sans-serif; font-size: 20px; color: var(--dark); margin-bottom: 8px; }
    .empty-card p { color: #7a7f84; font-size: 14px; margin-bottom: 26px; }

    /* ---------- RESPONSIVE ---------- */
    @media (max-width: 991px) {
        .tlist-grid { grid-template-columns: 1fr; }
        .tlist-img { position: static; max-width: 440px; margin: 0 auto; }
        .sum-card { grid-template-columns: 1fr; text-align: center; gap: 26px; }
        .feat-card { padding: 40px 26px 36px; }
    }
</style>

<main>
    <!-- ================= PAGE HERO ================= -->
    <section class="page-hero" @if($pageHeader['image']) style="background-image:linear-gradient(115deg, rgba(9, 52, 58, .88) 0%, rgba(24, 116, 126, .72) 50%, rgba(111, 211, 218, .55) 100%), url('{{ $pageHeader['image'] }}'); background-position:center, center; background-size:cover;" @endif>
        <div class="ph-inner">
            <h1 class="ph-title">{{ $pageHeader['title'] }}</h1>
            <p class="ph-crumb"><a href="{{ url('/') }}">Home</a> &nbsp;/&nbsp; Testimonials</p>
        </div>
    </section>

    @if ($hasReal)
    <!-- ================= FEATURED QUOTE (latest approved) ================= -->
    <section class="feat-sec">
        <span class="feat-ghost" aria-hidden="true">Cosmetics</span>
        <div class="container">
            <div class="feat-card reveal">
                <p class="stars">
                    @for ($i = 1; $i <= 5; $i++)
                    <span class="st {{ $i <= $featured->rating ? 'on' : '' }}">★</span>
                    @endfor
                </p>
                <blockquote>{{ $featured->comment }}</blockquote>
                <div class="feat-person">
                    <span class="u-av">{{ strtoupper(substr(trim($featured->name), 0, 1)) }}</span>
                    <div style="text-align:left">
                        <p class="nm">{{ $featured->name }}</p>
                        <p class="rl">Verified Buyer{{ $fProd ? ' · ' . $fProd->name : '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= RATINGS SUMMARY (REAL) ================= -->
    <section class="sum-sec">
        <div class="container">
            <div class="sum-card reveal" id="sumCard">
                <div class="sum-score">
                    <div class="big">{{ number_format($avg, 1) }}</div>
                    <div class="stars">
                        @for ($i = 1; $i <= 5; $i++)
                        <span class="st {{ $i <= round($avg) ? 'on' : '' }}">★</span>
                        @endfor
                    </div>
                    <p>Based on {{ $total }} review{{ $total === 1 ? '' : 's' }}</p>
                </div>
                <div class="sum-bars">
                    @for ($s = 5; $s >= 1; $s--)
                    @php $pct = $total ? round($dist[$s] / $total * 100, 1) : 0; @endphp
                    <div class="sum-row">
                        <b>{{ $s }} star</b>
                        <span class="sum-track"><i data-w="{{ $pct }}"></i></span>
                        <span>{{ rtrim(rtrim(number_format($pct, 1, '.', ''), '0'), '.') }}%</span>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </section>

    <!-- ================= TESTIMONIALS LIST (approved) ================= -->
    <section class="tlist-sec">
        <span class="tlist-ghost" aria-hidden="true">Glow</span>
        <div class="container">
            <div class="tlist-grid">

                <figure class="tlist-img reveal">
                    <img src="https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80" alt="Happy client">
                    <figcaption>Real People · Real Glow</figcaption>
                </figure>

                <div class="tstack">
                    @foreach ($reviews as $i => $r)
                    @php $p = $products[$r->product_id] ?? null; @endphp
                    <article class="tcard reveal" style="transition-delay: {{ ($i % 2) * 0.08 }}s">
                        <p class="stars">
                            @for ($si = 1; $si <= 5; $si++)
                            <span class="st {{ $si <= $r->rating ? 'on' : '' }}">★</span>
                            @endfor
                        </p>
                        @if ($p)
                        <a class="tcard-prod" href="{{ route('product.show', $p->slug) }}">🛍️ {{ $p->name }}</a>
                        @endif
                        <p>{{ $r->comment }}</p>
                        <div class="tcard-foot">
                            <span class="u-av">{{ strtoupper(substr(trim($r->name), 0, 1)) }}</span>
                            <div>
                                <p class="nm">{{ $r->name }}</p>
                                <p class="dt">{{ $r->created_at->format('F j, Y') }}</p>
                            </div>
                            <span class="verified">✓ Verified</span>
                        </div>
                    </article>
                    @endforeach
                </div>

            </div>
        </div>
    </section>
    @else
    <!-- ================= EMPTY STATE (koi approved review nahi) ================= -->
    <section class="empty-sec">
        <div class="container">
            <div class="empty-card reveal">
                <div class="e-ic">💬</div>
                <h3>No reviews published yet</h3>
                <p>Approved customer reviews will appear here. Shop karo, apna experience share karo — aur admin approval ke baad yahan shine karo! ✨</p>
                <a href="{{ route('shop') }}" class="btn btn-teal">Explore Products</a>
            </div>
        </div>
    </section>
    @endif

    <!-- ================= NEWSLETTER ================= -->
    
</main>

@include('front-end.include.newsletter')
@include('front-end.include.footer')
@include('front-end.include.js')

<script>
    /* ---------- Ratings bars animate ---------- */
    document.addEventListener('DOMContentLoaded', function () {
        var card = document.getElementById('sumCard');
        if (!card) return;

        function fire() {
            card.querySelectorAll('.sum-track i').forEach(function (bar) {
                bar.style.width = bar.dataset.w + '%';
            });
        }

        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) { setTimeout(fire, 300); io.disconnect(); }
            }, { threshold: .4 });
            io.observe(card);
        } else {
            fire();
        }
    });
</script>