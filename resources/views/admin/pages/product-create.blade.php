{{-- ==========================================================
     ADMIN · ADD NEW PRODUCT — Live preview + Multi-photo upload
     After save → redirects to the FRONT-END SHOP
========================================================== --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Product — NiceDash Admin</title>
    <link rel="shortcut icon" href="{{ asset('favicon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box
        }

        :root {
            --bg: #f3f5f7;
            --card: #fff;
            --text: #101828;
            --muted: #68788f;
            --border: #e8edf3;
            --forest: #0e5a75;
            --forest-2: #17739b;
            --lime: #3ec5e0;
            --lime-soft: #e4f6fb;
            --shadow: 0 1px 2px rgba(16, 24, 40, .04), 0 8px 24px rgba(16, 24, 40, .05);
            --radius: 16px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px
        }

        a {
            text-decoration: none;
            color: inherit
        }

        button {
            font-family: inherit;
            user-select: none
        }

        .layout {
            min-height: 100vh
        }

        .main {
            margin-left: 268px !important;
            transition: margin .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .main {
            margin-left: 84px !important
        }

        .content {
            padding: 24px 26px 40px;
            display: grid;
            gap: 22px
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 268px;
            background: var(--card);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: width .35s cubic-bezier(.4, 0, .2, 1), transform .35s cubic-bezier(.4, 0, .2, 1)
        }

        .layout.collapsed .sidebar {
            width: 84px
        }

        .s-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 14px
        }

        .backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, .55);
            backdrop-filter: blur(3px);
            z-index: 45;
            opacity: 0;
            visibility: hidden;
            transition: .3s
        }

        .backdrop.show {
            opacity: 1;
            visibility: visible
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(16px)
            }

            to {
                opacity: 1;
                transform: none
            }
        }

        .fx {
            animation: fadeUp .55s cubic-bezier(.22, 1, .36, 1) both
        }

        .d1 {
            animation-delay: .06s
        }

        .d2 {
            animation-delay: .12s
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px
        }

        .page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px
        }

        .page-head h2 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -.4px;
            display: flex;
            align-items: center;
            gap: 11px
        }

        .ph-ic {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, var(--forest), var(--forest-2));
            color: var(--lime);
            font-size: 21px;
            box-shadow: 0 10px 22px rgba(14, 90, 117, .22)
        }

        .page-head p {
            color: var(--muted);
            font-size: 13px;
            margin-top: 3px
        }

        .btn {
            padding: 11px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            border: none;
            transition: .25s;
            display: inline-flex;
            align-items: center;
            gap: 8px
        }

        .btn-ghost2 {
            background: var(--card);
            border: 1px solid var(--border);
            color: var(--text)
        }

        .btn-ghost2:hover {
            border-color: var(--forest);
            color: var(--forest);
            transform: translateY(-1px)
        }

        .btn-lime {
            background: linear-gradient(135deg, var(--lime), #29afd0);
            color: #08394b;
            box-shadow: 0 8px 20px rgba(62, 197, 224, .3)
        }

        .btn-lime:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(62, 197, 224, .4)
        }

        .btn-lime:disabled {
            opacity: .6;
            cursor: wait;
            transform: none !important
        }

        .p-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.55fr) minmax(300px, .85fr);
            gap: 22px;
            align-items: start
        }
        .product-form { display:grid;gap:14px;min-width:0 }
        .editor-section { padding:22px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow) }
        .product-form > *, .pv-wrap > * { min-width:0 }
        .editor-section .sec-title { margin-bottom:18px }
        .editor-section .fld:last-child { margin-bottom:0 }
        .editor-section .fld > .hint { line-height:1.5 }
        .editor-section + .editor-section { margin-top:0 }
        .form-actions { padding:15px;background:#fff;border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow) }
        .form-actions .switch-row { margin-bottom:12px }
        .editor-section .frow { gap:18px }
        .editor-section textarea { min-height:110px }
        .inline-creator { margin-top:10px;padding:13px;border:1px solid var(--border);border-radius:12px;background:#f8fafc }
        .inline-creator[hidden] { display:none }
        .inline-creator h4 { margin-bottom:9px;font-size:12px }
        .inline-creator-row { display:grid;grid-template-columns:1fr auto;gap:8px;align-items:end }
        .inline-creator-row .fld { margin-bottom:0 }
        .inline-creator-row button { min-height:42px;white-space:nowrap }
        .variant-list { display:grid;gap:10px;margin-bottom:12px }
        .variant-row { display:grid;grid-template-columns:1.1fr 1fr .8fr minmax(180px,1.2fr) auto;gap:9px;align-items:end;padding:12px;border:1px solid var(--border);border-radius:12px;background:#f8fafc }
        .variant-row .fld { margin:0;min-width:0 }
        .variant-row label { font-size:10px }
        .variant-row input,.variant-row select { padding:10px 11px;font-size:12px }
        .variant-row .variant-description { grid-column:1/-1 }
        .variant-row .variant-description textarea { min-height:56px;padding:9px 11px;font-size:12px }
        .variant-row .variant-color-field[hidden] { display:none }
        .variant-image-controls { display:flex;align-items:center;gap:7px;flex-wrap:wrap }
        .variant-image-controls .variant-image-upload { min-width:0;max-width:100%;font-size:10px }
        .variant-image-pick { min-height:36px;padding:8px 10px;border:1px solid var(--border);border-radius:9px;background:#fff;color:var(--forest);font-size:10px;font-weight:800;cursor:pointer }
        .variant-image-preview { display:flex;align-items:center;gap:7px;margin-top:7px }
        .variant-image-preview[hidden] { display:none }
        .variant-image-preview img { width:38px;height:38px;border:1px solid var(--border);border-radius:8px;object-fit:cover }
        .variant-image-remove { width:28px;height:28px;border:1px solid #f2c6c6;border-radius:8px;background:#fff4f4;color:#ae3030;cursor:pointer }
        .variant-remove { min-height:40px;padding:0 11px;border:1px solid #f2c6c6;border-radius:10px;background:#fff4f4;color:#ae3030;cursor:pointer }
        .label-options { display:flex;flex-wrap:wrap;gap:9px }
        .label-option { display:flex;align-items:center;gap:7px;padding:9px 12px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;font-weight:700 }
        .label-option input { accent-color:var(--forest) }
        .label-type-options { gap:10px }
        .label-type-option { min-width:112px;cursor:pointer }
        .label-type-option small { margin-left:auto;color:var(--muted);font-size:10px;font-weight:700 }
        .label-type-hot { border-color:#f2c5cf;background:#fff6f8 }
        .label-type-new { border-color:#cbdcf0;background:#f4f8ff }
        .label-type-sale { border-color:#bce5e4;background:#f1fbfa }
        .custom-label-sets { margin-top:12px;border:1px solid var(--border);border-radius:11px;background:#fff }
        .custom-label-sets summary { padding:11px 13px;color:var(--forest);font-size:11px;font-weight:800;cursor:pointer }
        .custom-label-sets .label-options { padding:0 12px 12px }
        .sku-combinations { display:grid;gap:9px;margin-top:14px }
        .sku-combination { display:grid;grid-template-columns:minmax(0,1fr) 130px;gap:12px;align-items:center;padding:12px;border:1px solid var(--border);border-radius:12px;background:#f8fafc }
        .sku-combination strong { display:block;font-size:12px }
        .sku-combination small { display:block;margin-top:4px;color:var(--muted);font-size:10px }
        .sku-combination .fld { margin:0 }
        .sku-empty { padding:13px;border:1px dashed #cbd5e1;border-radius:12px;color:var(--muted);font-size:12px;line-height:1.5 }
        .identifier-card { margin-top:14px;padding:17px;background:#fff;border:1px solid var(--border);border-radius:16px;box-shadow:var(--shadow) }
        .identifier-card h4 { margin-bottom:12px;font-size:13px }
        .identifier-line { display:flex;justify-content:space-between;gap:12px;padding:9px 0;border-top:1px solid var(--border);font-size:11px }
        .identifier-line span { color:var(--muted) }
        .identifier-line b { text-align:right }
        .status-published { color:#15803d }
        .status-unpublished { color:#b45309;font-size:11px }

        .sec-title {
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px
        }

        .sec-title i {
            color: var(--forest);
            font-size: 16px
        }

        .frow {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px
        }

        .fld {
            margin-bottom: 16px
        }

        .fld label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 7px
        }

        .fld label .req {
            color: #ef4444
        }

        .fld input,
        .fld select,
        .fld textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            font: inherit;
            font-size: 13.5px;
            outline: none;
            transition: .25s;
            resize: vertical;
        }

        .fld input:focus,
        .fld select:focus,
        .fld textarea:focus {
            border-color: var(--lime);
            box-shadow: 0 0 0 4px rgba(62, 197, 224, .16);
            background: var(--card)
        }

        .fld select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2368788f' stroke-width='2.4'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center
        }

        .hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 5px
        }

        .fld input[type=number]::-webkit-outer-spin-button,
        .fld input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none
        }

        .in-ic {
            position: relative
        }

        .in-ic i {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 15px
        }

        .in-ic input {
            padding-left: 36px
        }

        .chip-row {
            display: flex;
            gap: 7px;
            flex-wrap: wrap;
            margin-top: 8px
        }

        .chip {
            padding: 7px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--bg);
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
            cursor: pointer;
            transition: .2s
        }

        .chip:hover {
            transform: translateY(-2px);
            border-color: var(--lime);
            background: var(--lime-soft);
            color: var(--forest)
        }

        /* ================= PHOTO UPLOAD ================= */
        .up-zone {
            border: 2px dashed var(--border);
            border-radius: 16px;
            background: var(--bg);
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: .25s;
        }

        .up-zone:hover {
            border-color: var(--lime);
            background: var(--lime-soft)
        }

        .up-zone.drag {
            border-color: var(--lime);
            background: var(--lime-soft);
            transform: scale(1.01);
            box-shadow: 0 0 0 6px rgba(62, 197, 224, .16)
        }

        .up-zone>i {
            font-size: 38px;
            color: var(--forest);
            display: block;
            margin-bottom: 8px
        }

        .up-zone b {
            display: block;
            font-size: 14px
        }

        .up-zone span {
            display: block;
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 5px
        }

        .up-count {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            font-size: 11px;
            font-weight: 800;
            color: var(--forest);
            background: var(--lime-soft);
            border: 1px solid rgba(62, 197, 224, .4);
            padding: 4px 12px;
            border-radius: 20px
        }

        .thumbs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(86px, 1fr));
            gap: 10px;
            margin-top: 14px
        }

        .thumb {
            position: relative;
            aspect-ratio: 1;
            border-radius: 13px;
            overflow: hidden;
            border: 1px solid var(--border);
            animation: fadeUp .35s ease both;
            box-shadow: var(--shadow)
        }
        .photo-actions { display:flex;flex-wrap:wrap;gap:10px;margin-top:12px }
        .photo-action { display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:9px 13px;border:1px solid var(--border);border-radius:10px;background:#fff;color:var(--forest);font-family:inherit;font-size:12px;font-weight:700;cursor:pointer }
        .photo-action:hover { border-color:var(--forest);background:var(--lime-soft) }
        .photo-upload-input { position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0 }
        .photo-upload-input:focus-visible + .photo-action { outline:2px solid var(--forest);outline-offset:2px }

        .thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block
        }

        .thumb-x {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 22px;
            height: 22px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            background: rgba(2, 6, 23, .65);
            color: #fff;
            font-size: 13px;
            display: grid;
            place-items: center;
            transition: .2s;
            backdrop-filter: blur(4px);
        }

        .thumb-x:hover {
            background: #ef4444;
            transform: scale(1.1)
        }
        .thumb.is-cover{outline:3px solid var(--forest);outline-offset:2px}
        .thumb-cover{position:absolute;bottom:5px;left:5px;max-width:calc(100% - 38px);padding:5px 7px;border:0;border-radius:7px;background:rgba(14,90,117,.94);color:#fff;font-size:8px;font-weight:800;cursor:pointer}
        .thumb-cover:not(.selected){background:rgba(15,23,42,.72)}

        .thumb-first {
            position: absolute;
            bottom: 5px;
            left: 5px;
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: .08em;
            background: linear-gradient(135deg, var(--lime), #29afd0);
            color: #08394b;
            padding: 2px 7px;
            border-radius: 6px
        }

        .switch-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 15px;
            border-radius: 12px;
            background: var(--bg);
            border: 1px solid var(--border);
            margin-bottom: 18px
        }

        .switch-row b {
            font-size: 13px
        }

        .switch-row span {
            display: block;
            font-size: 11px;
            color: var(--muted);
            font-weight: 600
        }

        .switch {
            position: relative;
            width: 46px;
            height: 26px;
            flex-shrink: 0;
            display: inline-block
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0
        }

        .slider {
            position: absolute;
            inset: 0;
            border-radius: 30px;
            background: #cbd5e1;
            cursor: pointer;
            transition: .3s
        }

        .slider::before {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            left: 3px;
            top: 3px;
            background: #fff;
            transition: .3s cubic-bezier(.34, 1.56, .64, 1);
            box-shadow: 0 2px 6px rgba(0, 0, 0, .25)
        }

        .switch input:checked+.slider {
            background: linear-gradient(135deg, var(--forest), var(--lime))
        }

        .switch input:checked+.slider::before {
            transform: translateX(20px)
        }

        /* ================= LIVE PREVIEW ================= */
        .pv-wrap {
            position: sticky;
            top: 80px
        }

        .storefront-picker {
            margin-top: 20px
        }

        .pv-card {
            position: relative;
            overflow: hidden;
            text-align: center;
            padding: 22px 18px 19px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            color: var(--text);
            box-shadow: 0 12px 34px rgba(14, 90, 117, .1)
        }

        .pv-card::before {
            content: 'glow';
            position: absolute;
            right: 14px;
            top: -34px;
            font-family: Georgia, serif;
            font-style: italic;
            font-size: 110px;
            color: rgba(62, 197, 224, .1);
            pointer-events: none
        }

        .pv-badge {
            position: absolute;
            top: 14px;
            left: 14px;
            background: linear-gradient(135deg, #ff6b81, #ef4444);
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 12px;
            border-radius: 20px;
            box-shadow: 0 8px 18px rgba(239, 68, 68, .4);
            display: none
        }

        .pv-media {
            width: 100%;
            aspect-ratio: 1;
            max-width: 145px;
            margin: 6px auto 14px;
            border-radius: 26px;
            display: grid;
            place-items: center;
            font-size: 46px;
            background: linear-gradient(135deg, #f2fbfd, #e4f6fb);
            border: 1px solid rgba(62, 197, 224, .28);
            box-shadow: 0 8px 22px rgba(14, 90, 117, .1);
            overflow: hidden;
            transition: .3s
        }

        .pv-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none
        }

        .pv-media i {
            color: var(--forest);
            font-size: 82px;
            line-height: 1;
        }

        .pv-cat {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--forest);
            background: var(--lime-soft);
            border: 1px solid rgba(62, 197, 224, .3);
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 10px
        }

        .pv-name {
            font-size: 19px;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -.3px
        }

        .pv-desc {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
            margin-top: 8px;
            min-height: 36px
        }

        .pv-priceline {
            display: flex;
            align-items: baseline;
            justify-content: center;
            gap: 10px;
            margin-top: 16px
        }

        .pv-final {
            font-size: 30px;
            font-weight: 800;
            color: var(--forest);
            letter-spacing: -.6px
        }

        .pv-orig {
            font-size: 14px;
            color: #8795a5;
            text-decoration: line-through;
            display: none
        }

        .pv-meta {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap
        }

        .pv-chip {
            font-size: 11px;
            font-weight: 700;
            color: #385464;
            background: #f5fafc;
            border: 1px solid #e0edf2;
            padding: 6px 13px;
            border-radius: 20px
        }

        .pv-note {
            margin-top: 14px;
            font-size: 11px;
            color: var(--muted);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px
        }

        .pv-note i {
            color: var(--lime)
        }

        .storefront-preview {
            margin-top: 20px;
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow)
        }

        .storefront-preview-title {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase
        }

        .storefront-preview-card {
            position: relative;
            overflow: hidden;
            background: #fff
        }

        .storefront-preview-image {
            position: relative;
            overflow: hidden;
            aspect-ratio: 3 / 3.15;
            background: #f4f6f6
        }

        .storefront-preview-image > img {
            width: 100%;
            height: 100%;
            display: none;
            object-fit: cover
        }

        .storefront-preview-fallback {
            width: 100%;
            height: 100%;
            display: grid;
            place-items: center;
            color: #5eaaa2;
            background: linear-gradient(160deg, #f2f7f6, #e6f0ef)
        }

        .storefront-preview-fallback[hidden] { display: none }
        .storefront-preview-fallback i { font-size: 72px }
        .storefront-preview-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1;
            padding: 5px 11px;
            background: #38aab4;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase
        }
        .storefront-preview-labels { position:absolute;top:12px;left:12px;z-index:1;display:grid;justify-items:start;gap:5px;pointer-events:none }
        .storefront-preview-label { padding:5px 9px;color:#fff;font:700 9px 'Montserrat',sans-serif;letter-spacing:.06em;text-transform:uppercase }
        .storefront-preview-label.badge-hot { background:#c83a55 }
        .storefront-preview-label.badge-new { background:#295d8f }
        .storefront-preview-label.badge-sale { background:#38aab4 }

        .storefront-preview-actions {
            position: absolute;
            top: 12px;
            right: 12px;
            display: grid;
            gap: 8px
        }

        .storefront-preview-actions span {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #fff;
            color: #385464;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .13)
        }

        .storefront-preview-thumbs {
            display: flex;
            gap: 7px;
            overflow-x: auto;
            padding: 10px 12px 0
        }

        .storefront-preview-thumb {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            overflow: hidden;
            padding: 0;
            border: 2px solid transparent;
            border-radius: 8px;
            background: #f4f6f6;
            cursor: pointer
        }

        .storefront-preview-thumb.selected { border-color: var(--forest) }
        .storefront-preview-thumb img { width: 100%; height: 100%; display: block; object-fit: cover }

        .storefront-preview-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 15px 16px 0;
            color: #829099;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase
        }

        .storefront-preview-brand {
            overflow: hidden;
            color: #56746f;
            text-overflow: ellipsis;
            white-space: nowrap
        }

        .storefront-preview-name {
            margin: 6px 16px 0;
            color: var(--text);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.45;
            text-transform: uppercase
        }

        .storefront-preview-rating {
            padding: 6px 16px 0;
            color: #e6a92c;
            font-size: 12px;
            letter-spacing: .08em
        }

        .storefront-preview-rating span {
            margin-left: 5px;
            color: #92999d;
            font-size: 10px;
            letter-spacing: 0
        }

        .storefront-preview-cta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            margin-top: 12px;
            padding: 11px 16px;
            background: #38aab4;
            color: #fff;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase
        }

        .storefront-preview-price {
            display: flex;
            align-items: baseline;
            gap: 7px;
            padding: 12px 16px 16px;
            color: #176b72;
            font-size: 16px;
            font-weight: 800
        }

        .storefront-preview-price del {
            color: #aab3b8;
            font-size: 11px;
            font-weight: 500
        }

        .storefront-preview-details { display:flex;flex-wrap:wrap;gap:6px;padding:10px 16px 0 }
        .storefront-preview-details[hidden] { display:none }
        .storefront-preview-detail { padding:5px 8px;border:1px solid #e0edf2;border-radius:8px;background:#f5fafc;color:#385464;font-size:9px;font-weight:700 }
        .collection-options { display:grid;gap:9px;margin-top:12px }
        .collection-choice { display:flex;align-items:flex-start;gap:10px;padding:11px 13px;border:1px solid var(--border);border-radius:11px;background:#f8fafc;cursor:pointer }
        .collection-choice input { margin-top:3px;accent-color:var(--forest) }
        .collection-choice strong,.collection-choice small { display:block }
        .collection-choice strong { font-size:12px }
        .collection-choice small { margin-top:3px;color:var(--muted);font-size:10px;line-height:1.45 }
        .collection-question { display:flex;gap:10px;flex-wrap:wrap }
        .collection-question .label-option { cursor:pointer }
        .attribute-actions { display:flex;align-items:center;gap:8px;flex-wrap:wrap }
        .attribute-actions .btn { white-space:nowrap }
        .attribute-library { display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;padding:12px;border:1px solid var(--border);border-radius:12px;background:#f8fafc }
        .attribute-library-title { width:100%;margin:0;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase }
        .attribute-library-items { display:flex;gap:8px;flex-wrap:wrap }
        .attribute-library-item { display:inline-flex;align-items:center;gap:6px;padding:6px 9px;border:1px solid var(--border);border-radius:9px;background:#fff;color:var(--text);font-size:10px;font-weight:700 }
        .attribute-library-item small { color:var(--muted);font-size:9px;font-weight:600 }
        .attribute-library-link { align-self:center;color:var(--forest);font-size:10px;font-weight:800;text-decoration:underline;text-underline-offset:2px }

        .swal-toast {
            background: #fff !important;
            border: 1px solid rgba(62, 197, 224, .35) !important;
            color: var(--text) !important;
            border-radius: 14px !important
        }

        @media(max-width:1200px) {
            .sidebar {
                transform: translateX(-105%);
                width: 268px !important
            }

            .layout.mobile-open .sidebar {
                transform: none
            }

            .main {
                margin-left: 0 !important
            }
        }

        @media(max-width:900px) {
            .p-grid {
                grid-template-columns: 1fr
            }

            .pv-wrap {
                position: static
            }

            .pv-card { max-width:560px;margin:0 auto }
            .pv-note { margin-bottom:6px }
            .variant-row { grid-template-columns:1fr 1fr }
            .variant-row .variant-value,.variant-row .variant-image-field { grid-column:1/-1 }
        }

        @media(max-width:680px) {
            .frow {
                grid-template-columns: 1fr
            }

            .content {
                padding: 18px 14px 40px
            }
            .editor-section { padding:17px }
            .inline-creator-row { grid-template-columns:1fr }
            .variant-row { grid-template-columns:1fr 1fr }
            .variant-row .variant-value,.variant-row .variant-image-field { grid-column:1/-1 }
            .variant-remove { grid-column:1/-1 }
            .page-head h2 { font-size:20px }
        }
    </style>
</head>

<body>
    <div class="layout" id="layout">

        @include('admin.include.sidebar')

        <div class="main">
            @include('admin.include.navbar')

            <main class="content">

                <div class="page-head fx">
                    <div>
                        <h2><span class="ph-ic"><i class="ri-add-box-line"></i></span>Add New Product</h2>
                        <p>Create a product — photos, category, size, price and discount.</p>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-ghost2"><i class="ri-arrow-left-line"></i>All Products</a>
                </div>

                <div class="p-grid">

                    {{-- ================= FORM ================= --}}
                    <form class="product-form fx d1" id="pForm">
                        <input type="hidden" name="cover_image_index" id="coverImageIndexField" value="">
                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-information-line"></i>Product Information</p>

                        <div class="fld">
                            <label>Product Name <span class="req">*</span></label>
                            <input type="text" name="name" id="fName" oninput="updatePreview()" placeholder="e.g. Rose Luxury Perfume" maxlength="150" required>
                        </div>

                        <div class="frow">
                            <div class="fld">
                                <label>Category <span class="req">*</span></label>
                                <div class="qt-select-row">
                                    <select name="category_id" id="fCat" data-quick-category onchange="updatePreview()" required>
                                        <option value="">Select category…</option>
                                        @foreach ($categories as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="qt-add-btn" onclick="quickTaxonomyOpen('category', 'fCat')" title="Add category" aria-label="Add category"><i class="ri-add-line"></i></button>
                                </div>
                            </div>
                            <div class="fld">
                                <label>Size / Volume</label>
                                <div class="qt-select-row">
                                    <select name="size_id" id="fSize" data-quick-size onchange="updatePreview()">
                                        <option value="">Not specified</option>
                                        @foreach ($sizes as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }} ({{ number_format($s->ml) }} ml)</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="qt-add-btn" onclick="quickTaxonomyOpen('size', 'fSize')" title="Add size" aria-label="Add size"><i class="ri-add-line"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="fld">
                            <label>Brand</label>
                            <div class="qt-select-row">
                                <select name="brand_id" id="fBrand" onchange="updatePreview()">
                                    <option value="">No brand</option>
                                    @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="qt-add-btn" id="toggleBrandCreator" title="Add brand" aria-label="Add brand"><i class="ri-add-line"></i></button>
                            </div>
                            <div class="inline-creator" id="brandCreator" hidden>
                                <h4>Add a brand without leaving this page</h4>
                                <div class="inline-creator-row">
                                    <div class="fld"><label for="quickBrandName">Brand name</label><input id="quickBrandName" maxlength="120" placeholder="Brand name"></div>
                                    <button type="button" class="btn btn-ghost2" id="createBrandButton">Create brand</button>
                                </div>
                                <div class="fld" style="margin-top:9px"><label for="quickBrandLogo">Brand logo (optional)</label><input type="file" id="quickBrandLogo" accept="image/jpeg,image/png,image/webp"></div>
                            </div>
                        </div>
                        </section>

                        <div id="selectedMediaFields" hidden></div>

                        <section class="editor-section">
                            <p class="sec-title"><i class="ri-image-line"></i>Product photos</p>
                            <p class="hint">Upload photos from your device or choose existing images from the Media library. Select one as the storefront cover. Up to 6 images, 2 MB each.</p>
                            <div class="photo-actions">
                                <input class="photo-upload-input" type="file" id="productImageUpload" accept="image/jpeg,image/png,image/webp" multiple>
                                <label class="photo-action" for="productImageUpload"><i class="ri-upload-2-line"></i>Add pictures</label>
                                <button class="photo-action" type="button" onclick="openProductMediaPicker()"><i class="ri-image-add-line"></i>Choose from Media library</button>
                                <span class="up-count" id="upCount" style="display:none"><i class="ri-image-line"></i><span id="upCountTxt">0 photos</span></span>
                            </div>
                            <div class="thumbs" id="thumbs"></div>
                            <p class="hint" style="margin-bottom:0">The selected storefront cover is shown in the live preview.</p>
                        </section>

                        <section class="editor-section">
                        <p class="sec-title" style="margin-top:6px"><i class="ri-price-tag-3-line"></i>Pricing &amp; Discount</p>

                        <div class="frow">
                            <div class="fld">
                                <label>Regular price (£) <span class="req">*</span></label>
                                <div class="in-ic">
                                    <i class="ri-money-pound-circle-line"></i>
                                    <input type="number" name="price" id="fPrice" oninput="updatePreview()" placeholder="0.00" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        <div class="frow">
                            <div class="fld">
                                <label for="fDiscountType">Regular discount type</label>
                                <select name="discount_type" id="fDiscountType" onchange="updateDiscountUnit();updatePreview()">
                                    <option value="percent">Percentage (%)</option>
                                    <option value="fixed">Fixed amount (£)</option>
                                </select>
                            </div>
                            <div class="fld">
                                <label for="fDiscount" id="discountValueLabel">Regular discount percentage</label>
                                <div class="in-ic">
                                    <i class="ri-percent-line" id="discountValueIcon"></i>
                                    <input type="number" name="discount" id="fDiscount" value="0" oninput="updatePreview()" placeholder="0" step="0.01" min="0" max="100">
                                </div>
                                <p class="hint" id="discountValueHint">Enter a percentage from 0 to 100.</p>
                            </div>
                        </div>
                        <p class="hint">This regular discount applies outside any scheduled sale.</p>
                        <div class="scheduled-sale-fields">
                            <p class="sec-title"><i class="ri-calendar-event-line"></i>Scheduled sale</p>
                            <p class="hint">Set a start and end time and choose a separate fixed or percentage sale discount. It overrides the regular discount only during this window.</p>
                            <div class="frow">
                                <div class="fld">
                                    <label for="fSaleStartsAt">Sale starts</label>
                                    <input type="datetime-local" name="sale_starts_at" id="fSaleStartsAt" oninput="syncScheduledSaleFields();updatePreview()">
                                </div>
                                <div class="fld">
                                    <label for="fSaleEndsAt">Sale ends</label>
                                    <input type="datetime-local" name="sale_ends_at" id="fSaleEndsAt" oninput="syncScheduledSaleFields();updatePreview()">
                                </div>
                            </div>
                            <div class="frow">
                                <div class="fld">
                                    <label for="fSaleDiscountType">Scheduled sale discount type</label>
                                    <select name="sale_discount_type" id="fSaleDiscountType" onchange="updateSaleDiscountUnit();updatePreview()">
                                        <option value="percent">Percentage (%)</option>
                                        <option value="fixed">Fixed amount (£)</option>
                                    </select>
                                </div>
                                <div class="fld">
                                    <label for="fSaleDiscount" id="saleDiscountValueLabel">Scheduled sale percentage</label>
                                    <div class="in-ic">
                                        <i class="ri-percent-line" id="saleDiscountValueIcon"></i>
                                        <input type="number" name="sale_discount" id="fSaleDiscount" oninput="syncScheduledSaleFields();updatePreview()" placeholder="0" step="0.01" min="0" max="100">
                                    </div>
                                    <p class="hint" id="saleDiscountValueHint">Enter a percentage from 0 to 100.</p>
                                </div>
                            </div>
                        </div>
                        </section>

                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-price-tag-3-line"></i>Product Badges</p>
                        <p class="hint" style="margin:-8px 0 12px">Choose any storefront badge. Hot highlights a popular product; New marks a recent arrival; Sale is a badge only and does not change pricing.</p>
                        <div class="label-options label-type-options">
                            @foreach(\App\Models\ProductLabel::TYPES as $type)
                            <label class="label-option label-type-option label-type-{{ $type }}">
                                <input type="checkbox" name="label_types[]" value="{{ $type }}" data-label-name="{{ ucfirst($type) }}" onchange="updatePreview()">
                                <span>{{ ucfirst($type) }}</span>
                                <small>Badge</small>
                            </label>
                            @endforeach
                        </div>
                        @if($labels->isNotEmpty())
                        <details class="custom-label-sets">
                            <summary>Optional: choose a managed label set</summary>
                            <div class="label-options">
                                @foreach($labels as $label)
                                <label class="label-option">
                                    <input type="checkbox" name="labels[]" value="{{ $label->id }}" data-label-name="{{ $label->name }}" data-label-type="{{ $label->type }}" onchange="updatePreview()">
                                    <span>{{ $label->name }}</span>
                                    <small>{{ ucfirst($label->type) }}</small>
                                </label>
                                @endforeach
                            </div>
                        </details>
                        @endif
                        </section>

                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-calendar-event-line"></i>Collections</p>
                        <div class="fld">
                            <label>Is this product part of a collection?</label>
                            <div class="collection-question">
                                <label class="label-option"><input type="radio" name="is_collection" value="0" checked><span>No</span></label>
                                <label class="label-option"><input type="radio" name="is_collection" value="1"><span>Yes</span></label>
                            </div>
                        </div>
                        <div id="collectionOptions" hidden>
                            <p class="hint">Choose the scheduled collections this product belongs to.</p>
                            @if($collections->isNotEmpty())
                            <div class="collection-options">
                                @foreach($collections as $collection)
                                <label class="collection-choice">
                                    <input type="checkbox" name="collections[]" value="{{ $collection->id }}" data-collection-name="{{ $collection->name }}">
                                    <span><strong>{{ $collection->name }}</strong><small>{{ $collection->products_count }} products · {{ $collection->starts_at->format('M j, Y') }} – {{ $collection->ends_at->format('M j, Y') }}</small></span>
                                </label>
                                @endforeach
                            </div>
                            @else
                            <p class="hint">No collections exist yet. <a class="attribute-library-link" href="{{ route('admin.ecommerce.collections') }}">Create a scheduled collection</a> first.</p>
                            @endif
                        </div>
                        </section>

                        <section class="editor-section">
                        <p class="sec-title" style="margin-top:6px"><i class="ri-list-settings-line"></i>Product Options</p>
                        <div class="fld">
                            <div class="inline-creator-row">
                                <div><label>Options and values</label><p class="hint">Use for purchasable choices such as Color or Size. Parcel weight and dimensions are set separately below.</p></div>
                                <div class="attribute-actions">
                                    <a class="btn btn-ghost2" href="{{ route('admin.ecommerce.attributes') }}"><i class="ri-settings-3-line"></i>Manage attributes</a>
                                    <button type="button" class="btn btn-ghost2" id="toggleAttributeCreator"><i class="ri-add-line"></i>New attribute</button>
                                </div>
                            </div>
                            <div class="attribute-library" aria-label="Attributes available from Ecommerce Attributes">
                                <p class="attribute-library-title">Available attributes from Ecommerce → Attributes</p>
                                <div class="attribute-library-items" id="attributeLibraryItems">
                                    @foreach($attributes as $attribute)
                                    <span class="attribute-library-item">{{ $attribute->name }} <small>{{ ucfirst($attribute->type) }} · {{ $attribute->variants_count }} assigned values</small></span>
                                    @endforeach
                                </div>
                                <span class="hint" id="attributeLibraryEmpty" @if($attributes->isNotEmpty()) hidden @endif>No attributes created yet.</span>
                                <a class="attribute-library-link" href="{{ route('admin.ecommerce.attributes') }}">Open attributes &amp; variants</a>
                            </div>
                            <div class="inline-creator" id="attributeCreator" hidden>
                                <h4>Create an attribute without leaving this page</h4>
                                <div class="inline-creator-row">
                                    <div class="fld"><label for="quickAttributeName">Attribute name</label><input id="quickAttributeName" maxlength="120" placeholder="Color"></div>
                                    <div class="fld"><label for="quickAttributeType">Type</label><select id="quickAttributeType"><option value="color">Color</option><option value="text">Text</option><option value="size">Size</option></select></div>
                                    <button type="button" class="btn btn-ghost2" id="createAttributeButton">Create</button>
                                </div>
                            </div>
                            <div class="variant-list" id="variantList"></div>
                            <button type="button" class="btn btn-ghost2" id="addVariantButton"><i class="ri-add-line"></i>Add option value</button>
                        </div>
                        </section>

                        <template id="variantRowTemplate">
                            <div class="variant-row">
                                <div class="fld"><label>Attribute</label><select class="variant-attribute" name="variants[__INDEX__][product_attribute_id]" required><option value="">Choose attribute</option>@foreach($attributes as $attribute)<option value="{{ $attribute->id }}" data-type="{{ $attribute->type }}">{{ $attribute->name }} · {{ $attribute->type }}</option>@endforeach</select></div>
                                <div class="fld variant-value"><label>Variant name / color</label><input name="variants[__INDEX__][value]" maxlength="120" placeholder="e.g. Rosewood" required></div>
                                <div class="fld variant-color-field" hidden><label>Swatch color</label><input class="variant-color-input" name="variants[__INDEX__][color_hex]" placeholder="#AABBCC" pattern="#[0-9a-fA-F]{6}"></div>
                                <div class="fld variant-description"><label>Variant description</label><textarea name="variants[__INDEX__][description]" maxlength="500" rows="2" placeholder="Describe this shade, finish, or option"></textarea></div>
                                <div class="fld variant-image-field">
                                    <label>Option photo</label>
                                    <input type="hidden" class="variant-image-path" name="variants[__INDEX__][image_path]">
                                    <div class="variant-image-controls">
                                        <input class="variant-image-upload" type="file" name="variants[__INDEX__][image]" accept="image/jpeg,image/png,image/webp">
                                        <button type="button" class="variant-image-pick"><i class="ri-image-add-line"></i> Media library</button>
                                    </div>
                                    <div class="variant-image-preview" hidden>
                                        <img alt="Option photo preview">
                                        <button type="button" class="variant-image-remove" aria-label="Remove option photo"><i class="ri-close-line"></i></button>
                                    </div>
                                </div>
                                <button type="button" class="variant-remove" aria-label="Remove option"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </template>

                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-archive-stack-line"></i>Inventory &amp; Sellable Variants</p>
                        <p class="hint">Simple products use one stock quantity. If you add options, enter stock for every generated option combination; each combination receives its own SKU and barcode.</p>
                        <div class="fld" id="simpleStockWrap" style="max-width:50%;margin-top:14px">
                            <label>Stock quantity <span class="req">*</span></label>
                            <div class="in-ic">
                                <i class="ri-archive-stack-line"></i>
                                <input type="number" name="stock" id="fStock" oninput="updatePreview()" placeholder="e.g. 25" min="0" required>
                            </div>
                        </div>
                        <div class="sku-combinations" id="skuCombinationRows"></div>
                        </section>

                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-truck-line"></i>Shipping Package</p>
                        <div class="frow">
                            <div class="fld"><label>Weight</label><input type="number" name="shipping_weight" step="0.001" min="0" placeholder="e.g. 0.25"></div>
                            <div class="fld"><label>Weight unit</label><select name="weight_unit"><option value="g">g</option><option value="kg">kg</option><option value="mg">mg</option><option value="oz">oz</option><option value="lb">lb</option></select></div>
                        </div>
                        <div class="frow">
                            <div class="fld"><label>Length</label><input type="number" name="shipping_length" step="0.01" min="0" placeholder="Length"></div>
                            <div class="fld"><label>Width</label><input type="number" name="shipping_width" step="0.01" min="0" placeholder="Width"></div>
                            <div class="fld"><label>Height</label><input type="number" name="shipping_height" step="0.01" min="0" placeholder="Height"></div>
                            <div class="fld"><label>Dimension unit</label><select name="dimension_unit"><option value="cm">cm</option><option value="mm">mm</option><option value="m">m</option><option value="in">in</option></select></div>
                        </div>
                        </section>

                        <section class="editor-section">
                        <p class="sec-title"><i class="ri-file-text-line"></i>Description</p>
                        <div class="fld">
                            <label>Description</label>
                            <textarea name="description" id="fDesc" oninput="updatePreview()" rows="3" placeholder="Short description shown on the store…" maxlength="1000"></textarea>
                        </div>
                        </section>

                        <input type="hidden" name="emoji" value="{{ config('product_icons.choices.0.value', '🧴') }}">

                        <div class="form-actions">
                        <div class="switch-row">
                            <div><b>Publish immediately</b><span>Visible on the store right away</span></div>
                            <label class="switch"><input type="checkbox" name="is_active" id="fActive" checked><span class="slider"></span></label>
                        </div>

                        <button type="submit" class="btn btn-lime" id="saveBtn" style="width:100%;justify-content:center">
                            <i class="ri-save-3-line"></i>Save Product
                        </button>
                        </div>
                    </form>

                    {{-- ================= LIVE PREVIEW ================= --}}
                    <div class="pv-wrap fx d2">
                        <div class="pv-card">
                            <span class="pv-badge" id="pvOff">-0%</span>
                            <div class="pv-media" id="pvMedia">
                                <i id="pvIcon" class="{{ config('product_icons.fallback') }}" aria-hidden="true"></i>
                                <img id="pvImg" alt="preview">
                            </div>
                            <div><span class="pv-cat" id="pvCat"><i class="ri-apps-2-line"></i>Category</span></div>
                            <h3 class="pv-name" id="pvName">Product Name</h3>
                            <p class="pv-desc" id="pvDesc">Description will appear here as you type…</p>
                            <div class="pv-priceline">
                                <span class="pv-final" id="pvFinal">£0.00</span>
                                <span class="pv-orig" id="pvOrig">£0.00</span>
                            </div>
                            <div class="pv-meta">
                                <span class="pv-chip" id="pvSize"><i class="ri-flask-line"></i> No size</span>
                                <span class="pv-chip" id="pvBrand">Brand: No brand</span>
                                <span class="pv-chip" id="pvStock"><i class="ri-archive-line"></i> Stock: 0</span>
                            </div>
                        </div>
                        <div class="identifier-card">
                            <h4><i class="ri-barcode-line"></i> Overview &amp; Identifiers</h4>
                            <div class="identifier-line"><span>Product SKU</span><b id="overviewSku">Generated on save</b></div>
                            <div class="identifier-line"><span>Product barcode</span><b id="overviewBarcode">Internal EAN-13 · generated on save</b></div>
                            <div class="identifier-line"><span>Stock status</span><b id="overviewStockStatus">Set inventory</b></div>
                            <div class="identifier-line"><span>Store status</span><b id="overviewPublishStatus" class="status-published">Published</b></div>
                        </div>
                        <p class="pv-note"><i class="ri-flashlight-line"></i>Live preview — updates as you type</p>
                        <section class="storefront-preview" aria-label="Storefront product card preview">
                            <div class="storefront-preview-title">How this product appears in the shop</div>
                            <article class="storefront-preview-card">
                                <div class="storefront-preview-image">
                                    <span class="storefront-preview-badge" id="storefrontBadge" hidden></span>
                                    <div class="storefront-preview-labels" id="storefrontLabelBadges" aria-label="Selected product badges"></div>
                                    <div class="storefront-preview-fallback" id="storefrontFallback" aria-hidden="true"><i class="{{ config('product_icons.fallback') }}"></i></div>
                                    <img id="storefrontImg" alt="Storefront product preview">
                                    <div class="storefront-preview-actions" aria-hidden="true">
                                        <span><i class="ri-shopping-bag-3-line"></i></span>
                                        <span><i class="ri-heart-line"></i></span>
                                        <span><i class="ri-eye-line"></i></span>
                                    </div>
                                </div>
                                <div class="storefront-preview-thumbs" id="storefrontThumbs" aria-label="Product photo previews"></div>
                                <div class="storefront-preview-meta">
                                    <span id="storefrontCategory">Category</span>
                                    <span class="storefront-preview-brand" id="storefrontBrand">No brand</span>
                                </div>
                                <h3 class="storefront-preview-name" id="storefrontName">Product Name</h3>
                                <div class="storefront-preview-details" id="storefrontDetails" hidden></div>
                                <div class="storefront-preview-rating" aria-label="No reviews yet">★★★★★<span>No reviews</span></div>
                                <div class="storefront-preview-cta"><span id="storefrontCta">View details</span><i class="ri-arrow-right-line"></i></div>
                                <div class="storefront-preview-price"><span id="storefrontPrice">£0.00</span><del id="storefrontOrig" hidden></del></div>
                            </article>
                        </section>
                    </div>

                </div>

            </main>
        </div>
    </div>

    <div class="backdrop" id="backdrop"></div>

    @include('admin.include.quick-taxonomy-modals')

    <script>
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var productIconFallback = @json(config('product_icons.fallback'));

        /* SPA-safe URLs — Blade-rendered into this script.
           (body dataset goes stale after SPA navigation — that was the bug) */
        var prodStore    = "{{ route('admin.products.store') }}";
        var quickBrandStore = "{{ route('admin.products.quick-brand') }}";
        var quickAttributeStore = "{{ route('admin.products.quick-attribute') }}";
        var nextVariantIndex = 0;

        function _toast(icon, msg) {
            if (window.Swal) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2400,
                    timerProgressBar: true,
                    icon: icon,
                    title: msg,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'swal-toast'
                    }
                });
            } else {
                alert(msg);
            }
        }

        var _cur = '£';

        function money(v) {
            return _cur + Number(v || 0).toFixed(2);
        }

        /* ================= MEDIA LIBRARY PHOTOS ================= */
        var pickedImages = [];
        var coverImageIndex = 0;
        var MAX_PHOTOS = 6;
        var MAX_PHOTO_BYTES = 2 * 1024 * 1024;

        function updateCollectionOptions() {
            var isCollection = document.querySelector('input[name="is_collection"]:checked').value === '1';
            var options = document.getElementById('collectionOptions');
            options.hidden = !isCollection;
            options.querySelectorAll('input[name="collections[]"]').forEach(function(input) {
                input.disabled = !isCollection;
                if (!isCollection) input.checked = false;
            });
            updatePreview();
        }

        document.querySelectorAll('input[name="is_collection"]').forEach(function(input) {
            input.addEventListener('change', updateCollectionOptions);
        });
        document.getElementById('collectionOptions').addEventListener('change', updatePreview);

        function sortPickedImages() {
            pickedImages.sort(function(a, b) {
                return (a.type === 'upload' ? 0 : 1) - (b.type === 'upload' ? 0 : 1);
            });
        }

        window.openProductMediaPicker = function() {
            if (typeof window.openMediaPicker !== 'function') {
                _toast('error', 'The Media library is unavailable. Refresh the page and try again.');
                return;
            }
            window.openMediaPicker(function(image) {
                if (pickedImages.some(function(picked) { return picked.type === 'media' && picked.path === image.path; })) return;
                if (pickedImages.length >= MAX_PHOTOS) {
                    _toast('error', 'Maximum ' + MAX_PHOTOS + ' photos allowed.');
                    return;
                }
                pickedImages.push({ type: 'media', path: image.path, url: image.url, name: image.name });
                sortPickedImages();
                renderThumbs();
                updatePreview();
            });
        };

        function removeImage(idx) {
            var removedImage = pickedImages[idx];
            if (removedImage && removedImage.type === 'upload') URL.revokeObjectURL(removedImage.url);
            pickedImages.splice(idx, 1);
            if (coverImageIndex === idx) coverImageIndex = 0;
            else if (coverImageIndex > idx) coverImageIndex--;
            renderThumbs();
            updatePreview();
        }

        function clearVariantImage(row) {
            var previewImage = row.querySelector('.variant-image-preview img');
            if (previewImage.dataset.objectUrl) URL.revokeObjectURL(previewImage.dataset.objectUrl);
            delete previewImage.dataset.objectUrl;
            row.querySelector('.variant-image-path').value = '';
            row.querySelector('.variant-image-upload').value = '';
            row.querySelector('.variant-image-preview').hidden = true;
            previewImage.removeAttribute('src');
        }

        window.openVariantMediaPicker = function(button) {
            if (typeof window.openMediaPicker !== 'function') {
                _toast('error', 'The Media library is unavailable. Refresh the page and try again.');
                return;
            }
            var row = button.closest('.variant-row');
            if (!row) return;
            window.openMediaPicker(function(image) {
                var previewImage = row.querySelector('.variant-image-preview img');
                if (previewImage.dataset.objectUrl) URL.revokeObjectURL(previewImage.dataset.objectUrl);
                delete previewImage.dataset.objectUrl;
                row.querySelector('.variant-image-upload').value = '';
                row.querySelector('.variant-image-path').value = image.path;
                var preview = row.querySelector('.variant-image-preview');
                previewImage.src = image.url;
                previewImage.alt = image.name;
                preview.hidden = false;
            });
        };

        document.getElementById('variantList').addEventListener('click', function(event) {
            var pickerButton = event.target.closest('.variant-image-pick');
            if (pickerButton) {
                openVariantMediaPicker(pickerButton);
                return;
            }
            var removeButton = event.target.closest('.variant-image-remove');
            if (removeButton) clearVariantImage(removeButton.closest('.variant-row'));
        });
        document.getElementById('variantList').addEventListener('change', function(event) {
            var attributeSelect = event.target.closest('.variant-attribute');
            if (attributeSelect) updateVariantRowAppearance(attributeSelect.closest('.variant-row'));
            var upload = event.target.closest('.variant-image-upload');
            if (!upload || !upload.files.length) return;
            var row = upload.closest('.variant-row');
            row.querySelector('.variant-image-path').value = '';
            var preview = row.querySelector('.variant-image-preview');
            var previewImage = preview.querySelector('img');
            if (previewImage.dataset.objectUrl) URL.revokeObjectURL(previewImage.dataset.objectUrl);
            previewImage.dataset.objectUrl = URL.createObjectURL(upload.files[0]);
            previewImage.src = previewImage.dataset.objectUrl;
            previewImage.alt = upload.files[0].name;
            preview.hidden = false;
        });

        function selectCoverImage(index) {
            if (index < 0 || index >= pickedImages.length) return;
            coverImageIndex = index;
            renderThumbs();
            updatePreview();
        }

        function renderThumbs() {
            var wrap = document.getElementById('thumbs');
            var fields = document.getElementById('selectedMediaFields');
            var storefrontThumbs = document.getElementById('storefrontThumbs');
            wrap.innerHTML = '';
            storefrontThumbs.replaceChildren();
            fields.replaceChildren();
            pickedImages.forEach(function(image, i) {
                var d = document.createElement('div');
                d.className = 'thumb';
                var img = document.createElement('img');
                img.src = image.url;
                img.alt = image.name;
                d.classList.toggle('is-cover', i === coverImageIndex);
                if (image.type === 'media') {
                    var pathInput = document.createElement('input');
                    pathInput.type = 'hidden';
                    pathInput.name = 'image_paths[]';
                    pathInput.value = image.path;
                    fields.appendChild(pathInput);
                }
                var x = document.createElement('button');
                x.type = 'button';
                x.className = 'thumb-x';
                x.innerHTML = '<i class="ri-close-line"></i>';
                x.setAttribute('onclick', 'removeImage(' + i + ')');
                d.appendChild(img);
                d.appendChild(x);
                var cover = document.createElement('button');
                cover.type = 'button';
                cover.className = 'thumb-cover' + (i === coverImageIndex ? ' selected' : '');
                cover.setAttribute('aria-pressed', i === coverImageIndex ? 'true' : 'false');
                cover.textContent = i === coverImageIndex ? 'STOREFRONT' : 'Use as storefront';
                cover.addEventListener('click', function(event) {
                    event.stopPropagation();
                    selectCoverImage(i);
                });
                d.appendChild(cover);
                wrap.appendChild(d);

                var storefrontThumb = document.createElement('button');
                storefrontThumb.type = 'button';
                storefrontThumb.className = 'storefront-preview-thumb' + (i === coverImageIndex ? ' selected' : '');
                storefrontThumb.setAttribute('aria-label', 'Use ' + image.name + ' as the storefront cover');
                storefrontThumb.setAttribute('aria-pressed', i === coverImageIndex ? 'true' : 'false');
                storefrontThumb.addEventListener('click', function() {
                    selectCoverImage(i);
                });
                var storefrontThumbImage = document.createElement('img');
                storefrontThumbImage.src = image.url;
                storefrontThumbImage.alt = '';
                storefrontThumb.appendChild(storefrontThumbImage);
                storefrontThumbs.appendChild(storefrontThumb);
            });
            document.getElementById('coverImageIndexField').value = pickedImages.length ? String(coverImageIndex) : '';
            var cnt = document.getElementById('upCount'),
                txt = document.getElementById('upCountTxt');
            if (pickedImages.length) {
                cnt.style.display = 'inline-flex';
                txt.textContent = pickedImages.length + ' / ' + MAX_PHOTOS + ' photos';
            } else {
                cnt.style.display = 'none';
            }
        }
        document.getElementById('productImageUpload').addEventListener('change', function() {
            var availableSlots = MAX_PHOTOS - pickedImages.length;
            var files = Array.from(this.files || []);
            if (files.length > availableSlots) {
                _toast('error', 'Maximum ' + MAX_PHOTOS + ' photos allowed.');
            }
            files.slice(0, availableSlots).forEach(function(file) {
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                    _toast('error', 'Choose a JPG, PNG or WebP image.');
                    return;
                }
                if (file.size > MAX_PHOTO_BYTES) {
                    _toast('error', 'Each image must be 2 MB or smaller.');
                    return;
                }
                pickedImages.push({
                    type: 'upload',
                    file: file,
                    url: URL.createObjectURL(file),
                    name: file.name
                });
            });
            sortPickedImages();
            this.value = '';
            renderThumbs();
            updatePreview();
        });

        /* ================= OTHER FORM HELPERS ================= */
        function updatePreview() {
            var name = document.getElementById('fName').value.trim();
            var desc = document.getElementById('fDesc').value.trim();
            var price = parseFloat(document.getElementById('fPrice').value) || 0;
            var discount = parseFloat(document.getElementById('fDiscount').value) || 0;
            var discountType = document.getElementById('fDiscountType').value;
            var saleDiscountField = document.getElementById('fSaleDiscount');
            var saleIsConfigured = document.getElementById('fSaleStartsAt').value &&
                document.getElementById('fSaleEndsAt').value &&
                saleDiscountField.value !== '';
            var effectiveDiscount = saleIsConfigured
                ? (parseFloat(saleDiscountField.value) || 0)
                : discount;
            var effectiveDiscountType = saleIsConfigured
                ? document.getElementById('fSaleDiscountType').value
                : discountType;
            var discountAmount = effectiveDiscountType === 'fixed'
                ? effectiveDiscount
                : price * Math.min(effectiveDiscount, 100) / 100;
            var final = Math.max(0, price - Math.min(discountAmount, price));
            var stock = Array.from(document.querySelectorAll('.sku-stock-input')).reduce(function(total, input) {
                return total + (parseInt(input.value, 10) || 0);
            }, 0);
            if (document.getElementById('skuCombinationRows').querySelectorAll('.sku-stock-input').length === 0 &&
                !document.getElementById('fStock').disabled) {
                stock = parseInt(document.getElementById('fStock').value, 10) || 0;
            }

            var catSel = document.getElementById('fCat');
            var catTxt = catSel.options[catSel.selectedIndex].text;
            var sizeSel = document.getElementById('fSize');
            var sizeTxt = sizeSel.value ? sizeSel.options[sizeSel.selectedIndex].text.split(' (')[0] : '';
            var brandSel = document.getElementById('fBrand');
            var brandTxt = brandSel.value ? brandSel.options[brandSel.selectedIndex].text : 'No brand';

            /* photo takes over the preview if picked */
            var pvIcon = document.getElementById('pvIcon'),
                pvImg = document.getElementById('pvImg');
            if (pickedImages.length) {
                pvIcon.style.display = 'none';
                pvImg.style.display = 'block';
                pvImg.src = (pickedImages[coverImageIndex] || pickedImages[0]).url;
                pvImg.alt = name || 'Product photo preview';
            } else {
                pvIcon.style.display = 'block';
                pvImg.style.display = 'none';
                pvImg.removeAttribute('src');
                pvIcon.className = productIconFallback;
            }

            var storefrontImg = document.getElementById('storefrontImg');
            var storefrontFallback = document.getElementById('storefrontFallback');
            if (pickedImages.length) {
                storefrontImg.src = (pickedImages[coverImageIndex] || pickedImages[0]).url;
                storefrontImg.alt = (name || 'Product') + ' storefront photo';
                storefrontImg.style.display = 'block';
                storefrontFallback.hidden = true;
            } else {
                storefrontImg.removeAttribute('src');
                storefrontImg.style.display = 'none';
                storefrontFallback.hidden = false;
            }

            document.getElementById('pvName').textContent = name || 'Product Name';
            document.getElementById('pvDesc').textContent = desc || 'Description will appear here as you type…';
            document.getElementById('pvCat').innerHTML = '<i class="ri-apps-2-line"></i>' + (catTxt || 'Category');
            document.getElementById('pvFinal').textContent = money(final);
            document.getElementById('pvSize').innerHTML = '<i class="ri-flask-line"></i> ' + (sizeTxt || 'No size');
            document.getElementById('pvBrand').textContent = 'Brand: ' + brandTxt;
            document.getElementById('pvStock').innerHTML = '<i class="ri-archive-line"></i> Stock: ' + stock;
            document.getElementById('storefrontName').textContent = name || 'Product Name';
            document.getElementById('storefrontCategory').textContent = catTxt || 'Category';
            document.getElementById('storefrontBrand').textContent = brandTxt;
            document.getElementById('storefrontPrice').textContent = money(final);
            var storefrontDetails = document.getElementById('storefrontDetails');
            storefrontDetails.replaceChildren();
            var selectedCollections = Array.from(document.querySelectorAll('input[name="collections[]"]:checked'));
            selectedCollections.forEach(function(collection) {
                var detail = document.createElement('span');
                detail.className = 'storefront-preview-detail';
                detail.textContent = 'Collection: ' + collection.dataset.collectionName;
                storefrontDetails.appendChild(detail);
            });
            var selectedOptions = Array.from(document.querySelectorAll('#variantList .variant-row')).map(function(row) {
                var attribute = row.querySelector('.variant-attribute');
                var value = row.querySelector('input[name$="[value]"]');
                if (!attribute || !attribute.value || !value || !value.value.trim()) return null;
                var attributeName = attribute.options[attribute.selectedIndex].text.split(' · ')[0];
                return attributeName + ': ' + value.value.trim();
            }).filter(Boolean);
            selectedOptions.forEach(function(option) {
                var detail = document.createElement('span');
                detail.className = 'storefront-preview-detail';
                detail.textContent = option;
                storefrontDetails.appendChild(detail);
            });
            storefrontDetails.hidden = selectedCollections.length === 0 && selectedOptions.length === 0;
            document.getElementById('storefrontCta').textContent = selectedOptions.length ? 'Select options' : 'View details';
            var storefrontOrig = document.getElementById('storefrontOrig');
            storefrontOrig.hidden = final >= price;
            storefrontOrig.textContent = final < price ? money(price) : '';
            var storefrontBadge = document.getElementById('storefrontBadge');
            var storefrontLabelBadges = document.getElementById('storefrontLabelBadges');
            storefrontLabelBadges.replaceChildren();
            var selectedLabels = Array.from(document.querySelectorAll('input[name="label_types[]"]:checked, input[name="labels[]"]:checked'));
            selectedLabels.forEach(function(input) {
                var type = input.dataset.labelType || input.value;
                var name = input.dataset.labelName || type;
                if (type === 'sale' && final < price) return;
                var badge = document.createElement('span');
                badge.className = 'storefront-preview-label badge-' + type;
                badge.textContent = name;
                storefrontLabelBadges.appendChild(badge);
            });
            storefrontLabelBadges.style.top = stock <= 0 || final < price ? '44px' : '12px';
            if (stock <= 0) {
                storefrontBadge.textContent = 'Out of stock';
                storefrontBadge.hidden = false;
            } else if (final < price) {
                storefrontBadge.textContent = '-' + Math.round((1 - final / price) * 100) + '%';
                storefrontBadge.hidden = false;
            } else {
                storefrontBadge.hidden = true;
            }
            var stockStatus = document.getElementById('overviewStockStatus');
            stockStatus.textContent = stock > 0 ? 'In stock · ' + stock + ' units' : 'Out of stock';
            stockStatus.className = stock > 0 ? 'status-published' : 'status-unpublished';
            var publishStatus = document.getElementById('overviewPublishStatus');
            publishStatus.textContent = document.getElementById('fActive').checked ? 'Published' : 'Unpublished';
            publishStatus.className = document.getElementById('fActive').checked ? 'status-published' : 'status-unpublished';

            var orig = document.getElementById('pvOrig'),
                off = document.getElementById('pvOff');
            if (final < price) {
                orig.style.display = 'inline';
                orig.textContent = money(price);
                off.style.display = 'block';
                off.textContent = '-' + Math.round((1 - final / price) * 100) + '%';
            } else {
                orig.style.display = 'none';
                off.style.display = 'none';
            }
        }
        function updateDiscountUnit() {
            var fixed = document.getElementById('fDiscountType').value === 'fixed';
            var value = document.getElementById('fDiscount');
            document.getElementById('discountValueLabel').textContent = fixed ? 'Regular fixed discount (£)' : 'Regular discount percentage';
            document.getElementById('discountValueIcon').className = fixed ? 'ri-money-pound-circle-line' : 'ri-percent-line';
            document.getElementById('discountValueHint').textContent = fixed
                ? 'Enter a fixed amount up to the regular price.'
                : 'Enter a percentage from 0 to 100.';
            value.max = fixed ? (parseFloat(document.getElementById('fPrice').value) || '') : '100';
            value.step = fixed ? '0.01' : '1';
            if (!fixed && parseFloat(value.value) > 100) value.value = '100';
        }
        function updateSaleDiscountUnit() {
            var fixed = document.getElementById('fSaleDiscountType').value === 'fixed';
            var value = document.getElementById('fSaleDiscount');
            document.getElementById('saleDiscountValueLabel').textContent = fixed
                ? 'Scheduled sale fixed discount (£)'
                : 'Scheduled sale percentage';
            document.getElementById('saleDiscountValueIcon').className = fixed
                ? 'ri-money-pound-circle-line'
                : 'ri-percent-line';
            document.getElementById('saleDiscountValueHint').textContent = fixed
                ? 'Enter a fixed sale amount up to the regular price.'
                : 'Enter a sale percentage from 0 to 100.';
            value.max = fixed ? (parseFloat(document.getElementById('fPrice').value) || '') : '100';
            value.step = '0.01';
            if (!fixed && parseFloat(value.value) > 100) value.value = '100';
        }
        function syncScheduledSaleFields() {
            var starts = document.getElementById('fSaleStartsAt');
            var ends = document.getElementById('fSaleEndsAt');
            var discount = document.getElementById('fSaleDiscount');
            var type = document.getElementById('fSaleDiscountType');
            var enabled = Boolean(starts.value || ends.value || discount.value !== '');
            starts.required = enabled;
            ends.required = enabled;
            discount.required = enabled;
            type.required = enabled;
        }
        document.getElementById('fPrice').addEventListener('input', updateDiscountUnit);
        document.getElementById('fPrice').addEventListener('input', updateSaleDiscountUnit);
        document.getElementById('fActive').addEventListener('change', updatePreview);
        document.getElementById('pForm').addEventListener('input', function() {
            document.getElementById('overviewSku').textContent = 'Generated on save';
            document.getElementById('overviewBarcode').textContent = 'Internal EAN-13 · generated on save';
        });
        updateCollectionOptions();
        updateSaleDiscountUnit();
        syncScheduledSaleFields();
        renderSkuVariants();
        updatePreview();

        function postQuickCreate(url, formData) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            }).then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) {
                        var errors = data.errors || {};
                        var firstError = Object.keys(errors)[0];
                        throw new Error(firstError ? errors[firstError][0] : (data.message || 'Could not save.'));
                    }
                    return data;
                });
            });
        }

        document.getElementById('toggleBrandCreator').addEventListener('click', function () {
            var panel = document.getElementById('brandCreator');
            panel.hidden = !panel.hidden;
            if (!panel.hidden) document.getElementById('quickBrandName').focus();
        });
        document.getElementById('createBrandButton').addEventListener('click', function () {
            var button = this;
            var data = new FormData();
            data.append('name', document.getElementById('quickBrandName').value.trim());
            var logo = document.getElementById('quickBrandLogo').files[0];
            if (logo) data.append('logo', logo);
            button.disabled = true;
            postQuickCreate(quickBrandStore, data).then(function (result) {
                var option = document.createElement('option');
                option.value = result.brand.id;
                option.textContent = result.brand.name;
                document.getElementById('fBrand').appendChild(option);
                document.getElementById('fBrand').value = result.brand.id;
                updatePreview();
                document.getElementById('quickBrandName').value = '';
                document.getElementById('quickBrandLogo').value = '';
                document.getElementById('brandCreator').hidden = true;
                _toast('success', 'Brand created and selected.');
            }).catch(function (error) {
                _toast('error', error.message);
            }).finally(function () {
                button.disabled = false;
            });
        });

        document.getElementById('toggleAttributeCreator').addEventListener('click', function () {
            var panel = document.getElementById('attributeCreator');
            panel.hidden = !panel.hidden;
            if (!panel.hidden) document.getElementById('quickAttributeName').focus();
        });

        function addAttributeToForm(attribute) {
            var selects = Array.from(document.querySelectorAll('.variant-attribute'));
            selects.push(document.getElementById('variantRowTemplate').content.querySelector('.variant-attribute'));
            selects.forEach(function(select) {
                if (Array.from(select.options).some(function(option) { return option.value === String(attribute.id); })) return;
                var option = document.createElement('option');
                option.value = attribute.id;
                option.textContent = attribute.name + ' · ' + attribute.type;
                option.dataset.type = attribute.type;
                select.appendChild(option);
            });

            var item = document.createElement('span');
            item.className = 'attribute-library-item';
            item.appendChild(document.createTextNode(attribute.name + ' '));
            var details = document.createElement('small');
            details.textContent = attribute.type.charAt(0).toUpperCase() + attribute.type.slice(1) + ' · 0 assigned values';
            item.appendChild(details);
            document.getElementById('attributeLibraryItems').appendChild(item);
            document.getElementById('attributeLibraryEmpty').hidden = true;
        }

        document.getElementById('createAttributeButton').addEventListener('click', function () {
            var button = this;
            var data = new FormData();
            data.append('name', document.getElementById('quickAttributeName').value.trim());
            data.append('type', document.getElementById('quickAttributeType').value);
            button.disabled = true;
            postQuickCreate(quickAttributeStore, data).then(function (result) {
                addAttributeToForm(result.attribute);
                var row = createVariantRow(result.attribute);
                document.getElementById('variantList').appendChild(row);
                renderSkuVariants();
                document.getElementById('quickAttributeName').value = '';
                document.getElementById('attributeCreator').hidden = true;
                _toast('success', 'Attribute created; add its product option.');
            }).catch(function (error) {
                _toast('error', error.message);
            }).finally(function () {
                button.disabled = false;
            });
        });

        function createVariantRow(attribute) {
            var template = document.getElementById('variantRowTemplate').innerHTML.replace(/__INDEX__/g, nextVariantIndex++);
            var holder = document.createElement('div');
            holder.innerHTML = template.trim();
            var row = holder.firstElementChild;
            if (attribute) {
                var select = row.querySelector('.variant-attribute');
                if (!Array.from(select.options).some(function(option) { return option.value === String(attribute.id); })) {
                    var option = document.createElement('option');
                    option.value = attribute.id;
                    option.textContent = attribute.name + ' · ' + attribute.type;
                    option.dataset.type = attribute.type;
                    select.appendChild(option);
                }
                select.value = attribute.id;
            }
            updateVariantRowAppearance(row);
            return row;
        }

        function updateVariantRowAppearance(row) {
            var attribute = row.querySelector('.variant-attribute');
            var selectedOption = attribute.options[attribute.selectedIndex];
            var colorField = row.querySelector('.variant-color-field');
            colorField.hidden = !selectedOption || selectedOption.dataset.type !== 'color';
            if (colorField.hidden) row.querySelector('.variant-color-input').value = '';
        }

        var combinationStocks = Object.create(null);

        function renderSkuVariants() {
            var container = document.getElementById('skuCombinationRows');
            container.querySelectorAll('.sku-stock-input').forEach(function(input) {
                combinationStocks[input.dataset.comboKey] = input.value;
            });

            var optionRows = Array.from(document.querySelectorAll('#variantList .variant-row'));
            var groups = new Map();
            var incomplete = false;
            optionRows.forEach(function(row) {
                var attribute = row.querySelector('.variant-attribute');
                var valueInput = row.querySelector('.variant-value input');
                var attributeId = attribute.value;
                var value = valueInput.value.trim();
                if (!attributeId || !value) {
                    incomplete = true;
                    return;
                }
                var selected = attribute.options[attribute.selectedIndex];
                var attributeName = selected ? selected.text.split(' · ')[0] : 'Option';
                if (!groups.has(attributeId)) {
                    groups.set(attributeId, { id: attributeId, name: attributeName, values: [] });
                }
                var group = groups.get(attributeId);
                if (!group.values.some(function(item) { return item.value.toLowerCase() === value.toLowerCase(); })) {
                    group.values.push({ value: value });
                }
            });

            container.replaceChildren();
            var simpleStock = document.getElementById('simpleStockWrap');
            var stockInput = document.getElementById('fStock');
            if (optionRows.length === 0) {
                simpleStock.hidden = false;
                stockInput.disabled = false;
                stockInput.required = true;
                updatePreview();
                return;
            }

            simpleStock.hidden = true;
            stockInput.disabled = true;
            stockInput.required = false;
            if (incomplete || Array.from(groups.values()).some(function(group) { return group.values.length === 0; })) {
                var incompleteNote = document.createElement('div');
                incompleteNote.className = 'sku-empty';
                incompleteNote.textContent = 'Complete every option name and value to generate sellable combinations.';
                container.appendChild(incompleteNote);
                updatePreview();
                return;
            }

            var combinations = [[]];
            groups.forEach(function(group) {
                var next = [];
                combinations.forEach(function(existing) {
                    group.values.forEach(function(value) {
                        next.push(existing.concat([{ group: group, value: value.value }]));
                    });
                });
                combinations = next;
            });
            if (combinations.length > 500) {
                var limitNote = document.createElement('div');
                limitNote.className = 'sku-empty';
                limitNote.textContent = 'This creates ' + combinations.length + ' combinations. Reduce the option values to 500 combinations or fewer.';
                container.appendChild(limitNote);
                updatePreview();
                return;
            }

            combinations.forEach(function(combination, index) {
                var key = combination.map(function(item) {
                    return item.group.id + '|' + item.value.toLowerCase();
                }).sort().join(';');
                var row = document.createElement('div');
                row.className = 'sku-combination';
                var label = document.createElement('div');
                var title = document.createElement('strong');
                title.textContent = combination.map(function(item) { return item.group.name + ': ' + item.value; }).join(' · ');
                var note = document.createElement('small');
                note.textContent = 'SKU and internal EAN-13 barcode generated automatically';
                label.append(title, note);
                var stockField = document.createElement('div');
                stockField.className = 'fld';
                var stockLabel = document.createElement('label');
                stockLabel.textContent = 'Stock';
                var stock = document.createElement('input');
                stock.type = 'number';
                stock.className = 'sku-stock-input';
                stock.dataset.comboKey = key;
                stock.name = 'sku_variants[' + index + '][stock]';
                stock.min = '0';
                stock.max = '4294967295';
                stock.required = true;
                stock.value = combinationStocks[key] || '0';
                stock.addEventListener('input', updatePreview);
                stockField.append(stockLabel, stock);
                row.append(label, stockField);
                combination.forEach(function(item, optionIndex) {
                    var attributeId = document.createElement('input');
                    attributeId.type = 'hidden';
                    attributeId.name = 'sku_variants[' + index + '][options][' + optionIndex + '][product_attribute_id]';
                    attributeId.value = item.group.id;
                    var optionValue = document.createElement('input');
                    optionValue.type = 'hidden';
                    optionValue.name = 'sku_variants[' + index + '][options][' + optionIndex + '][value]';
                    optionValue.value = item.value;
                    row.append(attributeId, optionValue);
                });
                container.appendChild(row);
            });
            updatePreview();
        }

        document.getElementById('addVariantButton').addEventListener('click', function () {
            document.getElementById('variantList').appendChild(createVariantRow());
            renderSkuVariants();
        });
        document.getElementById('variantList').addEventListener('click', function (event) {
            var removeButton = event.target.closest('.variant-remove');
            if (removeButton) {
                var row = removeButton.closest('.variant-row');
                var objectUrl = row.querySelector('.variant-image-preview img').dataset.objectUrl;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                row.remove();
                renderSkuVariants();
            }
        });
        document.getElementById('variantList').addEventListener('input', renderSkuVariants);
        document.getElementById('variantList').addEventListener('change', renderSkuVariants);

        /* ================= SUBMIT ================= */
        document.getElementById('pForm').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('saveBtn');
            var fd = new FormData(this);
            var uploadedImages = pickedImages.filter(function(image) { return image.type === 'upload'; });
            uploadedImages.forEach(function(image) { fd.append('images[]', image.file); });
            fd.set('is_active', document.getElementById('fActive').checked ? '1' : '0');
            btn.disabled = true;

            var form = this;
            fetch(prodStore, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                })
                .then(function(res) {
                    return res.json().catch(function () { return {}; }).then(function(data) {
                        if (res.status === 419) throw new Error('Session expired — please refresh the page.');
                        if (!res.ok) {
                            var errors = data.errors || {};
                            var fields = Object.keys(errors);
                            throw new Error(fields.length ? errors[fields[0]][0] : (data.message || 'Could not save product.'));
                        }
                        return data;
                    });
                })
                .then(function(data) {
                    document.getElementById('overviewSku').textContent = data.product.sku;
                    document.getElementById('overviewBarcode').textContent = data.product.barcode;
                    _toast('success', (data.message || 'Product saved.') + ' SKU: ' + data.product.sku);
                    form.reset();
                    updateSaleDiscountUnit();
                    syncScheduledSaleFields();
                    updateCollectionOptions();
                    pickedImages = [];
                    coverImageIndex = 0;
                    document.getElementById('productImageUpload').value = '';
                    renderThumbs();
                    updatePreview();
                    combinationStocks = Object.create(null);
                    document.getElementById('variantList').replaceChildren();
                    document.getElementById('brandCreator').hidden = true;
                    document.getElementById('attributeCreator').hidden = true;
                    renderThumbs();
                    renderSkuVariants();
                    updatePreview();
                })
                .catch(function(err) {
                    _toast('error', err.message || 'Something went wrong.');
                })
                .finally(function() {
                    btn.disabled = false;
                });
            return false;
        });
        updateDiscountUnit();
    </script>
    @include('admin.include.media-picker')
</body>

</html>