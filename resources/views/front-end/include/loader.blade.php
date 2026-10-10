{{-- ============================================================
     COSMETIC LOADER — Glassy Green
     1) Line draws + "C" draws itself (stroke)
     2) Ghost outlines appear
     3) Cursor JUMPS letter-by-letter, highlighting (filling) each
     4) Reflection + tagline → loader fades out
     Include right after <body> so it shows on every page.
============================================================ --}}
@php
    $loaderLetters = ['C', 'O', 'S', 'M', 'E', 'T', 'I', 'C'];
@endphp
<div class="cos-loader" id="cosLoader" aria-hidden="true">
    <span class="cl-orb cl-orb-1"></span>
    <span class="cl-orb cl-orb-2"></span>
    <span class="cl-orb cl-orb-3"></span>

    <div class="cl-center">
        <div class="cl-stage">
            <span class="cl-line"></span>

            <svg class="cl-word" viewBox="0 0 1040 250" role="img" aria-label="COSMETIC">
                <defs>
                    <linearGradient id="clGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f2fffb"/>
                        <stop offset="42%" stop-color="#9df8d8"/>
                        <stop offset="100%" stop-color="#27d697"/>
                    </linearGradient>
                </defs>

                @foreach ($loaderLetters as $i => $ch)
                    {{-- outline layer (C = self-drawing, rest = ghosts) --}}
                    <text class="lw-outline {{ $i === 0 ? 'lw-draw' : 'lw-ghost' }}"
                          x="{{ 100 + $i * 120 }}" y="162"
                          @if ($i === 0)
                              style="animation-delay:.25s"
                          @else
                              style="animation-delay:{{ 1 + ($i - 1) * 0.07 }}s"
                          @endif>{{ $ch }}</text>

                    {{-- fill layer (the highlight lands here) --}}
                    <text class="lw-fill" x="{{ 100 + $i * 120 }}" y="162"
                          style="animation-delay: {{ 1.75 + $i * 0.17 }}s, 3.15s">{{ $ch }}</text>
                @endforeach

                {{-- jumping highlight cursor --}}
                <g class="cl-cursor">
                    <g class="cl-cursor-move">
                        <circle cx="0" cy="128" r="16" fill="#3ef2b3" opacity=".35"/>
                        <rect x="-6" y="88" width="12" height="82" rx="6" fill="url(#clGrad)"/>
                    </g>
                </g>
            </svg>
        </div>

        {{-- mirror reflection --}}
        <div class="cl-reflect-wrap" aria-hidden="true">
            <svg class="cl-word cl-reflection" viewBox="0 0 1040 250">
                @foreach ($loaderLetters as $i => $ch)
                    <text class="lw-outline {{ $i === 0 ? 'lw-draw' : 'lw-ghost' }}"
                          x="{{ 100 + $i * 120 }}" y="162"
                          @if ($i === 0)
                              style="animation-delay:.25s"
                          @else
                              style="animation-delay:{{ 1 + ($i - 1) * 0.07 }}s"
                          @endif>{{ $ch }}</text>
                    <text class="lw-fill" x="{{ 100 + $i * 120 }}" y="162"
                          style="animation-delay: {{ 1.75 + $i * 0.17 }}s, 3.15s">{{ $ch }}</text>
                @endforeach
            </svg>
        </div>

        <p class="cl-tag">Beauty Pronounced</p>
    </div>
</div>

<style>
    .cos-loader {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: grid;
        place-items: center;
        background:
            radial-gradient(900px 520px at 50% 42%, rgba(62, 242, 179, .07), transparent 60%),
            radial-gradient(700px 500px at 85% -10%, rgba(56, 170, 180, .12), transparent 60%),
            #04080a;
        animation: clFallback .6s ease 5s forwards; /* no-JS safety net */
    }

    .cos-loader.done { animation: clOut .65s ease forwards; }

    /* ---------- glassy green orbs ---------- */
    .cl-orb { position: absolute; border-radius: 50%; filter: blur(90px); opacity: .55; }
    .cl-orb-1 { width: 380px; height: 380px; background: rgba(62, 242, 179, .22); top: -120px; left: -100px; animation: clFloat1 14s ease-in-out infinite; }
    .cl-orb-2 { width: 320px; height: 320px; background: rgba(56, 170, 180, .25); bottom: -120px; right: -80px; animation: clFloat2 18s ease-in-out infinite; }
    .cl-orb-3 { width: 220px; height: 220px; background: rgba(46, 214, 151, .16); top: 55%; left: 60%; animation: clFloat3 16s ease-in-out infinite; }

    /* ---------- stage ---------- */
    .cl-center { position: relative; z-index: 2; width: min(88vw, 880px); text-align: center; }
    .cl-stage { position: relative; }

    /* the line from pic 1 */
    .cl-line {
        position: absolute;
        left: 0; right: 0; top: 49%;
        height: 2px;
        z-index: 1;
        background: linear-gradient(90deg, transparent, #3ef2b3 25%, #d9fff1 50%, #3ef2b3 75%, transparent);
        box-shadow: 0 0 12px rgba(62, 242, 179, .8), 0 0 34px rgba(62, 242, 179, .4);
        transform: scaleX(0);
        transform-origin: left;
        animation: clLine .8s cubic-bezier(.22, 1, .36, 1) .1s forwards,
                   clLineFade .45s ease 1.2s forwards;
    }

    /* ---------- word ---------- */
    .cl-word {
        position: relative;
        z-index: 2;
        display: block;
        width: 100%;
        height: auto;
        overflow: visible;
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        font-size: 106px;
    }

    .lw-outline {
        fill: none;
        stroke: rgba(210, 255, 239, .9);
        stroke-width: 1.7;
        opacity: 0;
    }

    .lw-ghost { animation: clGhost .38s ease both; transform-box: fill-box; }

    /* your "C" — draws itself just like the circle in pic 1 */
    .lw-draw {
        stroke-dasharray: 1000;
        stroke-dashoffset: 1000;
        filter: drop-shadow(0 0 8px rgba(62, 242, 179, .9));
        animation: clDraw .8s cubic-bezier(.6, .05, .3, 1) both; /* delay set inline (.25s) */
    }

    .lw-fill {
        fill: url(#clGrad);
        fill-opacity: 0;
        filter: drop-shadow(0 0 14px rgba(62, 242, 179, .55));
        transform-box: fill-box;
        transform-origin: center;
        animation: clFill .24s cubic-bezier(.22, 1, .36, 1) both,
                   clGlowPulse 2.4s ease-in-out 3.15s infinite;
    }

    /* ---------- jumping cursor ---------- */
    .cl-cursor {
        opacity: 0;
        filter: drop-shadow(0 0 10px rgba(62, 242, 179, .9)) drop-shadow(0 0 28px rgba(62, 242, 179, .5));
        animation: clCursorIn .2s ease 1.72s both,
                   clCursorOut .3s ease 2.97s forwards;
    }

    .cl-cursor-move {
        animation: clCursorMove 1.19s steps(7, end) 1.75s both; /* 7 jumps = 8 letters */
    }

    /* ---------- reflection ---------- */
    .cl-reflect-wrap {
        -webkit-mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .55) 0%, transparent 80%);
        mask-image: linear-gradient(to bottom, rgba(0, 0, 0, .55) 0%, transparent 80%);
        margin-top: -8%;
        opacity: .45;
        pointer-events: none;
    }

    .cl-reflection { transform: scaleY(-1); filter: blur(1.5px); }

    /* ---------- tagline ---------- */
    .cl-tag {
        margin: 14px 0 0;
        opacity: 0;
        font: 600 11px 'Montserrat', sans-serif;
        letter-spacing: 7px;
        text-transform: uppercase;
        color: #9df3d6;
        animation: clTag .7s ease 3.1s forwards;
    }

    /* ---------- keyframes ---------- */
    @keyframes clDraw { to { stroke-dashoffset: 0; } }

    @keyframes clGhost {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes clFill {
        0% { fill-opacity: 0; transform: scale(1.28); filter: drop-shadow(0 0 26px rgba(62, 242, 179, .95)); }
        60% { fill-opacity: 1; }
        100% { fill-opacity: 1; transform: scale(1); }
    }

    @keyframes clGlowPulse {
        0%, 100% { filter: drop-shadow(0 0 12px rgba(62, 242, 179, .5)); }
        50% { filter: drop-shadow(0 0 22px rgba(62, 242, 179, .8)); }
    }

    @keyframes clCursorMove {
        from { transform: translateX(100px); }
        to { transform: translateX(940px); }
    }

    @keyframes clCursorIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes clCursorOut { to { opacity: 0; } }

    @keyframes clLine { to { transform: scaleX(1); } }
    @keyframes clLineFade { to { opacity: 0; } }

    @keyframes clTag {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }

    @keyframes clFloat1 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(50px, 40px); } }
    @keyframes clFloat2 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-45px, -35px); } }
    @keyframes clFloat3 { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(-35px, -45px); } }

    @keyframes clOut {
        to { opacity: 0; visibility: hidden; transform: scale(1.06); filter: blur(8px); }
    }

    @keyframes clFallback { to { opacity: 0; visibility: hidden; } }

    /* ---------- accessibility ---------- */
    @media (prefers-reduced-motion: reduce) {
        .cl-line, .cl-orb { animation: none !important; opacity: 0; }
        .lw-outline { opacity: 1 !important; animation: none !important; stroke-dashoffset: 0 !important; }
        .lw-fill { fill-opacity: 1 !important; animation: none !important; transform: none !important; }
        .cl-cursor { display: none; }
        .cl-tag { opacity: 1; animation: none; }
    }
</style>

<script>
    (function () {
        var loader = document.getElementById('cosLoader');
        if (!loader) return;

        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var LIFE = reduce ? 700 : 3650;  /* total time before fade-out */
        var FADE = reduce ? 250 : 650;

        /* lock scroll while loading */
        document.documentElement.style.overflow = 'hidden';

        function finish() {
            loader.classList.add('done');
            setTimeout(function () {
                if (loader.parentNode) loader.parentNode.removeChild(loader);
                document.documentElement.style.overflow = '';
            }, FADE);
        }

        setTimeout(finish, LIFE);
    })();
</script>