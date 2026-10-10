{{-- ==========================================================
     ADMIN · PRODUCT DETAILS — gallery + fullscreen lightbox
     v5 · Ocean/Aqua · SPA-safe · mobile responsive
========================================================== --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="theme-color" content="#0e5a75">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $product->name }} — NiceDash Admin</title>
<link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{--bg:#f3f5f7;--card:#fff;--text:#101828;--muted:#68788f;--border:#e8edf3;--forest:#0e5a75;--forest-2:#17739b;--lime:#3ec5e0;--lime-soft:#e4f6fb;--shadow:0 1px 2px rgba(16,24,40,.04),0 8px 24px rgba(16,24,40,.05);--radius:16px}
body.dark{--bg:#0b1220;--card:#111c2e;--text:#e7edf5;--muted:#8ba0b8;--border:#1e2c42;--lime-soft:rgba(62,197,224,.08)}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px;-webkit-text-size-adjust:100%;-webkit-tap-highlight-color:transparent;overflow-x:clip}
a{text-decoration:none;color:inherit}
button{font-family:inherit;user-select:none}
.layout{min-height:100vh}
.main{margin-left:268px;min-width:0;transition:margin .35s cubic-bezier(.4,0,.2,1)}
.layout.collapsed .main{margin-left:84px}
.content{padding:24px 26px 40px;display:grid;gap:22px;min-width:0}
.sidebar{position:fixed;left:0;top:0;bottom:0;width:268px;background:var(--card);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:50;transition:width .35s cubic-bezier(.4,0,.2,1),transform .35s cubic-bezier(.4,0,.2,1)}
.layout.collapsed .sidebar{width:84px}
.s-nav{flex:1;overflow-y:auto;padding:12px 14px}
.backdrop{position:fixed;inset:0;background:rgba(2,6,23,.55);z-index:45;opacity:0;visibility:hidden;pointer-events:none;transition:.3s}
.backdrop.show{opacity:1;visibility:visible;pointer-events:auto}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.fx{animation:fadeUp .55s cubic-bezier(.22,1,.36,1) both}
.d1{animation-delay:.06s}.d2{animation-delay:.12s}
.card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow)}
.crumb{display:flex;align-items:center;gap:9px;font-size:12.5px;color:var(--muted);font-weight:600;flex-wrap:wrap}
.crumb a{color:var(--forest);font-weight:800;display:inline-flex;align-items:center;gap:5px;transition:.2s}
.crumb a:hover{color:var(--lime)}
.crumb i.sep{font-size:14px;opacity:.5}
.btn{padding:11px 20px;border-radius:12px;font-weight:800;font-size:13px;cursor:pointer;border:none;transition:.25s;display:inline-flex;align-items:center;gap:8px}
.btn-ghost{background:var(--card);border:1px solid var(--border);color:var(--text)}
.btn-ghost:hover{border-color:var(--forest);color:var(--forest)}
.btn-lime{background:linear-gradient(135deg,var(--lime),#3fb0d8);color:#08394b;box-shadow:0 8px 20px rgba(62,197,224,.35)}
.btn-lime:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(62,197,224,.45)}
.p-grid{display:grid;grid-template-columns:46% 1fr;gap:22px;align-items:start}
.gallery{padding:24px}
.g-main{width:100%;aspect-ratio:1;border-radius:20px;overflow:hidden;background:linear-gradient(160deg,#f8faf9,#e8f4f8);border:1px solid var(--border);display:grid;place-items:center;position:relative;cursor:zoom-in;transition:.3s;box-shadow:0 16px 40px rgba(14,90,117,.10)}
body.dark .g-main{background:linear-gradient(160deg,#0f1a2b,#0c1728)}
.g-main:hover{box-shadow:0 22px 50px rgba(14,90,117,.16)}
.g-main img{width:100%;height:100%;object-fit:cover;display:block}
.g-main .g-emoji{font-size:96px}
.g-zoom{position:absolute;bottom:14px;right:14px;z-index:2;display:inline-flex;align-items:center;gap:7px;background:rgba(2,6,23,.6);color:#fff;font-size:11.5px;font-weight:800;padding:8px 14px;border-radius:11px;opacity:0;transform:translateY(6px);transition:.25s;pointer-events:none}
@media(hover:hover) and (pointer:fine){.g-main:hover .g-zoom{opacity:1;transform:none}}
@media(hover:none){.g-zoom{opacity:1;transform:none}}
.g-off{position:absolute;top:14px;left:14px;z-index:2;background:linear-gradient(135deg,#ff6b81,#ef4444);color:#fff;font-size:12px;font-weight:800;padding:6px 14px;border-radius:20px;box-shadow:0 10px 22px rgba(239,68,68,.4)}
.g-thumbs{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}
.g-th{width:72px;height:72px;border-radius:14px;overflow:hidden;cursor:pointer;border:2px solid transparent;background:var(--bg);padding:0;display:grid;place-items:center;font-size:22px;transition:.22s;box-shadow:var(--shadow);position:relative}
.g-th img{width:100%;height:100%;object-fit:cover;display:block}
@media(hover:hover){.g-th:hover{transform:translateY(-3px)}}
.g-th.on{border-color:var(--forest);box-shadow:0 0 0 3px rgba(62,197,224,.35)}
.details{padding:30px 28px}
.d-cat{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--forest);background:var(--lime-soft);border:1px solid rgba(62,197,224,.4);padding:5px 13px;border-radius:20px}
.d-status{display:inline-flex;align-items:center;gap:6px;margin-left:8px;font-size:11px;font-weight:800;padding:5px 13px;border-radius:20px}
.d-status.pub{background:#dcfce7;color:#16a34a}
.d-status.hid{background:#fef3c7;color:#b45309}
.d-status::before{content:'';width:6px;height:6px;border-radius:50%;background:currentColor;box-shadow:0 0 7px currentColor}
.d-name{font-size:30px;font-weight:800;letter-spacing:-.7px;margin-top:14px;line-height:1.2}
.d-slug{font-size:12.5px;color:var(--muted);font-weight:600;margin-top:5px}
.d-pricebox{display:flex;align-items:baseline;gap:14px;margin-top:22px;flex-wrap:wrap;padding:18px 20px;background:var(--bg);border-radius:16px;border:1px dashed var(--border)}
.d-final{font-size:40px;font-weight:800;color:var(--forest);letter-spacing:-1px}
.d-orig{font-size:18px;color:var(--muted);text-decoration:line-through;display:none}
.d-off{display:none;background:linear-gradient(135deg,#ff6b81,#ef4444);color:#fff;font-size:13px;font-weight:800;padding:5px 14px;border-radius:20px}
.d-save{display:none;width:100%;margin-top:8px;font-size:12px;color:#16a34a;font-weight:800}
.d-desc{font-size:13.5px;color:var(--muted);line-height:1.8;margin-top:20px;white-space:pre-line}
.d-meta{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:22px}
.d-cell{display:flex;align-items:center;gap:12px;padding:14px 16px;background:var(--bg);border:1px solid var(--border);border-radius:14px;transition:.25s}
@media(hover:hover){.d-cell:hover{border-color:rgba(62,197,224,.5);transform:translateY(-2px)}}
.d-cell i{width:40px;height:40px;min-width:40px;border-radius:12px;display:grid;place-items:center;font-size:19px;background:#e0f5fb;color:#0e8fb5}
.d-cell i.blue{background:#e8f1fd;color:#2563eb}
.d-cell i.violet{background:#f1ecfd;color:#7c3aed}
.d-cell i.amber{background:#fdf4e3;color:#d97706}
.d-cell small{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);font-weight:800}
.d-cell b{font-size:13.5px}
.d-actions{display:flex;gap:10px;margin-top:24px;flex-wrap:wrap}
.lb{position:fixed;inset:0;z-index:200;background:rgba(4,8,16,.94);display:grid;place-items:center;opacity:0;visibility:hidden;transition:.3s}
.lb.show{opacity:1;visibility:visible}
.lb-img-wrap{max-width:min(88vw,1000px);max-height:82vh;position:relative;transform:scale(.94);transition:.35s cubic-bezier(.22,1,.36,1)}
.lb.show .lb-img-wrap{transform:none}
.lb-img-wrap img{max-width:100%;max-height:82vh;display:block;border-radius:18px;box-shadow:0 40px 100px rgba(0,0,0,.6)}
.lb-emoji{font-size:160px;display:grid;place-items:center}
.lb-btn{position:fixed;top:50%;transform:translateY(-50%);width:52px;height:52px;border-radius:16px;border:none;cursor:pointer;background:rgba(255,255,255,.08);color:#fff;font-size:22px;display:grid;place-items:center;transition:.25s}
.lb-btn:hover{background:var(--lime);color:#08394b;transform:translateY(-50%) scale(1.08)}
.lb-prev{left:26px}.lb-next{right:26px}
.lb-close{position:fixed;top:22px;right:22px;width:46px;height:46px;border-radius:14px;border:none;cursor:pointer;background:rgba(255,255,255,.08);color:#fff;font-size:20px;display:grid;place-items:center;transition:.25s}
.lb-close:hover{background:#ef4444;transform:rotate(90deg)}
.lb-count{position:fixed;bottom:26px;left:50%;transform:translateX(-50%);background:rgba(255,255,255,.1);color:#fff;font-size:12.5px;font-weight:800;letter-spacing:.1em;padding:9px 20px;border-radius:20px}
.lb-thumbs{position:fixed;bottom:70px;left:50%;transform:translateX(-50%);display:flex;gap:8px;flex-wrap:wrap;justify-content:center;max-width:80vw}
.lb-th{width:48px;height:48px;border-radius:11px;overflow:hidden;border:2px solid transparent;padding:0;background:transparent;cursor:pointer;transition:.2s;opacity:.55}
.lb-th img{width:100%;height:100%;object-fit:cover;display:block}
.lb-th:hover{opacity:1}
.lb-th.on{border-color:var(--lime);opacity:1}
@media(max-width:992px){.sidebar{transform:translateX(-105%);width:268px !important}.layout.mobile-open .sidebar{transform:none}.main{margin-left:0 !important}.p-grid{grid-template-columns:1fr}}
@media(max-width:680px){.content{padding:18px 14px calc(40px + env(safe-area-inset-bottom))}.gallery{padding:16px}.details{padding:22px 18px}.d-meta{grid-template-columns:1fr}.d-name{font-size:24px}.d-final{font-size:32px}.lb-prev{left:10px}.lb-next{right:10px}.lb-btn{width:44px;height:44px}.g-th{width:60px;height:60px}}
@media(max-width:480px){.d-name{font-size:21px}.d-final{font-size:28px}.d-actions .btn{width:100%;justify-content:center}.lb-emoji{font-size:110px}.lb-thumbs{bottom:64px}.lb-th{width:40px;height:40px}.g-zoom{font-size:10.5px;padding:7px 11px}}
@media(max-width:360px){.g-th{width:52px;height:52px}.d-cell{padding:12px}}
</style>
</head>
<body>
<div class="layout" id="layout">

    @include('admin.include.sidebar')

    <div class="main">
        @include('admin.include.navbar')

        <main class="content">

            <div class="crumb fx">
                <a href="{{ route('admin.products.index') }}"><i class="ri-arrow-left-line"></i> All Products</a>
                <i class="ri-arrow-right-s-line sep"></i>
                <span>{{ $product->name }}</span>
            </div>

            <div class="p-grid">

                {{-- GALLERY --}}
                <div class="card gallery fx d1">
                    <div class="g-main" id="gMain" onclick="openLightbox(currentIndex)">
                        @if($product->discount > 0)
                        <span class="g-off">-{{ rtrim(rtrim(number_format($product->discount, 1), '0'), '.') }}%</span>
                        @endif
                        @if($product->images->first())
                        <img id="gMainImg" src="{{ asset('storage/' . $product->images->first()->path) }}" alt="{{ $product->name }}">
                        @else
                        <span class="g-emoji" id="gMainEmoji">{{ $product->emoji }}</span>
                        @endif
                        <span class="g-zoom"><i class="ri-zoom-in-line"></i> Tap to enlarge</span>
                    </div>

                    <div class="g-thumbs" id="gThumbs">
                        @foreach($product->images as $i => $img)
                        <button type="button" class="g-th {{ $i === 0 ? 'on' : '' }}" onclick="openThumb({{ $i }}, this)" title="Photo {{ $i + 1 }}">
                            <img src="{{ asset('storage/' . $img->path) }}" alt="">
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- DETAILS --}}
                <div class="card details fx d2">
                    <div>
                        <span class="d-cat">{{ optional($product->category)->icon }} {{ optional($product->category)->name ?? 'Uncategorized' }}</span>
                        <span class="d-status {{ $product->is_active ? 'pub' : 'hid' }}">{{ $product->is_active ? 'Published' : 'Hidden' }}</span>
                    </div>

                    <h1 class="d-name">{{ $product->name }}</h1>
                    <p class="d-slug">#{{ $product->slug }}</p>

                    <div class="d-pricebox">
                        <div>
                            <span class="d-final">£{{ number_format($product->final_price, 2) }}</span>
                            @if($product->discount > 0)
                            <span class="d-orig" style="display:inline">£{{ number_format($product->price, 2) }}</span>
                            <span class="d-off" style="display:inline">-{{ rtrim(rtrim(number_format($product->discount, 1), '0'), '.') }}%</span>
                            <p class="d-save" style="display:block"><i class="ri-coupon-3-line"></i> Customer saves £{{ number_format($product->price - $product->final_price, 2) }} with this discount</p>
                            @endif
                        </div>
                    </div>

                    <p class="d-desc">{{ $product->description ?: 'No description added for this product.' }}</p>

                    <div class="d-meta">
                        <div class="d-cell">
                            <i class="ri-flask-line"></i>
                            <div><small>Size / Volume</small><b>{{ optional($product->size)->name ?? '—' }}{{ optional($product->size)->ml ? ' · ' . number_format($product->size->ml) . ' ml' : '' }}</b></div>
                        </div>
                        <div class="d-cell">
                            <i class="ri-archive-stack-line {{ $product->stock == 0 ? 'amber' : '' }}"></i>
                            <div><small>Stock</small><b>{{ $product->stock }} units {{ $product->stock == 0 ? '· OUT OF STOCK' : ($product->stock <= 5 ? '· LOW' : '') }}</b></div>
                        </div>
                        <div class="d-cell">
                            <i class="ri-calendar-line blue"></i>
                            <div><small>Added on</small><b>{{ $product->created_at->format('M d, Y') }}</b></div>
                        </div>
                        <div class="d-cell">
                            <i class="ri-refresh-line violet"></i>
                            <div><small>Last updated</small><b>{{ $product->updated_at->format('M d, Y') }}</b></div>
                        </div>
                        <div class="d-cell">
                            <i class="ri-image-line"></i>
                            <div><small>Photos</small><b>{{ $product->images->count() }}</b></div>
                        </div>
                        <div class="d-cell">
                            <i class="ri-price-tag-3-line"></i>
                            <div><small>Discount</small><b>{{ $product->discount > 0 ? $product->discount . '%' : 'None' }}</b></div>
                        </div>
                    </div>

                    <div class="d-actions">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-ghost"><i class="ri-arrow-left-line"></i>Back to Products</a>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-lime"><i class="ri-add-line"></i>Add Another Product</a>
                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

{{-- LIGHTBOX --}}
<div class="lb" id="lightbox" onclick="if (event.target === this) closeLightbox()">
    <button type="button" class="lb-close" onclick="closeLightbox()"><i class="ri-close-line"></i></button>
    <button type="button" class="lb-btn lb-prev" onclick="stepLightbox(-1)"><i class="ri-arrow-left-s-line"></i></button>
    <button type="button" class="lb-btn lb-next" onclick="stepLightbox(1)"><i class="ri-arrow-right-s-line"></i></button>
    <div class="lb-img-wrap" id="lbWrap"></div>
    <div class="lb-thumbs" id="lbThumbs"></div>
    <div class="lb-count" id="lbCount">1 / 1</div>
</div>

<script>
    var galleryImages = @json($product->images->map(fn ($i) => asset('storage/' . $i->path)));
    var galleryEmoji  = @json($product->emoji);
    var currentIndex  = 0;

    function setMainImage(idx, btn) {
        currentIndex = idx;
        var mainImg = document.getElementById('gMainImg');
        var mainEmoji = document.getElementById('gMainEmoji');
        if (mainImg) { mainImg.style.display = 'block'; mainImg.src = galleryImages[idx]; }
        else if (mainEmoji) {
            var img = document.createElement('img');
            img.id = 'gMainImg'; img.src = galleryImages[idx]; img.alt = '';
            mainEmoji.replaceWith(img);
        }
        document.querySelectorAll('#gThumbs .g-th').forEach(function (t) { t.classList.remove('on'); });
        if (btn) btn.classList.add('on');
    }

    function openThumb(idx, btn) { setMainImage(idx, btn); openLightbox(idx); }

    function openLightbox(idx) {
        if (typeof idx === 'number') currentIndex = idx;
        renderLightbox();
        document.getElementById('lightbox').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('show');
        document.body.style.overflow = '';
        var thumb = document.querySelectorAll('#gThumbs .g-th')[currentIndex];
        setMainImage(currentIndex, thumb);
    }

    function stepLightbox(dir) {
        if (!galleryImages.length) return;
        currentIndex = (currentIndex + dir + galleryImages.length) % galleryImages.length;
        renderLightbox();
    }

    function jumpLightbox(idx) { currentIndex = idx; renderLightbox(); }

    function renderLightbox() {
        var wrap = document.getElementById('lbWrap');
        var thumbs = document.getElementById('lbThumbs');
        var count = document.getElementById('lbCount');
        if (!galleryImages.length) {
            wrap.innerHTML = '<div class="lb-emoji">' + galleryEmoji + '</div>';
            thumbs.innerHTML = '';
            count.textContent = 'No photos';
            return;
        }
        wrap.innerHTML = '<img src="' + galleryImages[currentIndex] + '" alt="">';
        count.textContent = (currentIndex + 1) + ' / ' + galleryImages.length;
        thumbs.innerHTML = '';
        galleryImages.forEach(function (url, i) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'lb-th' + (i === currentIndex ? ' on' : '');
            b.innerHTML = '<img src="' + url + '" alt="">';
            b.setAttribute('onclick', 'jumpLightbox(' + i + ')');
            thumbs.appendChild(b);
        });
    }

    /* 🛡️ guarded — SPA re-runs scripts, Escape/arrow listener bind ONCE only */
    if (!window.__psKeys) {
        window.__psKeys = 1;
        document.addEventListener('keydown', function (e) {
            var box = document.getElementById('lightbox');
            if (!box || !box.classList.contains('show')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') stepLightbox(-1);
            if (e.key === 'ArrowRight') stepLightbox(1);
        });
    }
</script>
</body>
</html>