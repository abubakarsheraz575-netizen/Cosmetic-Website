<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit a Product — Cosmetics Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    @include('user.include.base')
    <style>
        .product-submissions-main{min-width:0}
        .submission-wrap{max-width:1240px;margin:0 auto;padding:0 0 38px}
        .submission-page-title{min-width:0;flex:1}
        .submission-page-title h1{font-size:21px;color:var(--ink);font-weight:600}
        .submission-page-title p{font-size:12.5px;color:rgba(22,35,60,.58)}
        .submission-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:4px 0 22px}
        .submission-head h2{font-size:26px;line-height:1.2;color:var(--ink);font-weight:700;letter-spacing:-.5px}
        .submission-head p{font-size:13px;color:var(--txt);margin-top:6px;max-width:650px}
        .submission-head-icon{width:48px;height:48px;flex:none;display:grid;place-items:center;border-radius:15px;background:linear-gradient(135deg,#14532d,#1b6a3c);color:#c3ed6c;font-size:23px;box-shadow:0 10px 22px rgba(20,83,45,.22)}
        .submission-head-copy{display:flex;align-items:center;gap:14px}
        .submission-layout{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(280px,.8fr);gap:22px;align-items:start}
        .submission-card{min-width:0;background:rgba(255,255,255,.88);border:1px solid var(--glass-border);border-radius:18px;padding:23px;backdrop-filter:blur(14px);box-shadow:0 10px 28px rgba(22,35,60,.06)}
        .submission-section-title{display:flex;align-items:center;gap:9px;margin:1px 0 17px;color:var(--ink);font-size:14px;font-weight:700}
        .submission-section-title i{font-size:18px;color:#1b6a3c}
        .submission-section-title.section-gap{margin-top:24px}
        .submission-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start;gap:0 18px}
        .submission-field{display:block;min-width:0;margin-bottom:15px;color:var(--ink);font-size:12px;font-weight:600;line-height:1.45}
        .submission-field.full{grid-column:1/-1}
        .submission-field .required{color:#c83b51}
        .submission-field input:not([type=file]),.submission-field select,.submission-field textarea{display:block;width:100%;min-width:0;min-height:46px;margin-top:7px;padding:11px 13px;border:1px solid #e1e7ed;border-radius:11px;background:#f8fafb;color:var(--ink);font:500 13px Poppins,sans-serif;outline:none;transition:border-color .2s,box-shadow .2s,background .2s}
        .submission-field input:focus,.submission-field select:focus,.submission-field textarea:focus{border-color:#63b957;box-shadow:0 0 0 4px rgba(99,185,87,.13);background:#fff}
        .submission-field select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2368788f' stroke-width='2.4'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 13px center;padding-right:38px}
        .submission-field>.site-select-wrap{margin-top:7px}
        .submission-field>.site-select-wrap .site-select-trigger{min-height:46px;border-radius:11px;background:#f8fafb;font-family:Poppins,sans-serif;font-size:13px;font-weight:500}
        .submission-field textarea{min-height:92px;resize:vertical;line-height:1.6}
        .submission-hint{display:block;margin-top:6px;color:#78889a;font-size:10.5px;font-weight:400;line-height:1.55}
        .submission-price-wrap{position:relative}
        .submission-price-wrap i{position:absolute;top:18px;left:13px;color:#78889a;font-size:15px}
        .submission-price-wrap input{padding-left:36px!important}
        .submission-chips{display:flex;gap:7px;flex-wrap:wrap;margin-top:8px}
        .submission-chip{padding:6px 11px;border:1px solid #e1e7ed;border-radius:9px;background:#f8fafb;color:#68788f;font:700 10px Poppins,sans-serif;cursor:pointer;transition:.2s}
        .submission-chip:hover,.submission-chip:focus-visible{border-color:#a3e635;background:#f2fde0;color:#14532d}
        .submission-upload{position:relative;display:grid;place-items:center;min-height:158px;padding:20px;border:2px dashed #d9e2e8;border-radius:16px;background:linear-gradient(145deg,#f8fafb,#f3f8f3);text-align:center;transition:.2s}
        .submission-upload:hover,.submission-upload:focus-within,.submission-upload.drag{border-color:#91c957;background:#f2fde0}
        .submission-upload input[type=file]{position:absolute;inset:0;width:100%;height:100%;opacity:0;cursor:pointer}
        .submission-upload>div>i{display:block;color:#1b6a3c;font-size:31px;margin-bottom:4px}
        .submission-upload strong{display:block;color:var(--ink);font-size:12px}
        .submission-upload span{display:block;margin-top:4px;color:#78889a;font-size:10px}
        .submission-upload-cta{display:inline-flex!important;align-items:center;gap:5px;margin-top:10px!important;padding:7px 12px;border-radius:9px;background:#fff;color:#14532d!important;font-size:10px!important;font-weight:700;box-shadow:0 2px 7px rgba(22,35,60,.08)}
        .submission-upload-count{margin-top:10px;color:#14532d;font-size:10px;font-weight:700}
        .submission-preview{display:grid;grid-template-columns:repeat(auto-fill,minmax(76px,1fr));gap:9px;margin-top:11px}
        .submission-thumb{position:relative;aspect-ratio:1;overflow:hidden;border:1px solid #e1e7ed;border-radius:12px;background:#fff;box-shadow:0 4px 12px rgba(22,35,60,.07);cursor:pointer}
        .submission-thumb.is-cover{outline:3px solid #176d8e;outline-offset:1px}
        .submission-thumb img{width:100%;height:100%;object-fit:cover}
        .submission-thumb-main{position:absolute;left:5px;bottom:5px;padding:3px 6px;border-radius:5px;background:linear-gradient(135deg,#a3e635,#93d92a);color:#17341c;font-size:8px;font-weight:800;letter-spacing:.06em}
        .submission-clear-images{margin-top:8px;padding:0;border:0;background:none;color:#68788f;font:600 10px Poppins,sans-serif;text-decoration:underline;cursor:pointer}
        .submission-actions{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:22px}
        .submission-review-note{display:flex;align-items:flex-start;gap:7px;color:#68788f;font-size:10px;line-height:1.5}
        .submission-review-note i{color:#1b6a3c;font-size:15px}
        .submission-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:none;border:0;border-radius:11px;background:linear-gradient(135deg,#a3e635,#93d92a);color:#18351c;font:800 12px Poppins,sans-serif;padding:12px 17px;cursor:pointer;box-shadow:0 8px 18px rgba(163,230,53,.26);transition:transform .2s,box-shadow .2s}
        .submission-button:hover{transform:translateY(-2px);box-shadow:0 12px 22px rgba(163,230,53,.32)}
        .submission-button:disabled{opacity:.65;cursor:wait;transform:none}
        .submission-alert{padding:12px 15px;border-radius:11px;margin-bottom:16px;background:#e9f9f0;color:#176a3b;font-size:12px}
        .submission-alert.errors{background:#fff0f1;color:#a52b40}
        .submission-alert:focus{outline:3px solid rgba(56,170,180,.25);outline-offset:2px}
        .submission-side{position:sticky;top:20px;display:grid;gap:17px}
        .submission-preview-card{position:relative;overflow:hidden;padding:22px 20px 18px;border-radius:19px;background:linear-gradient(160deg,#0a1120 0%,#0d1526 58%,#0a1222 100%);color:#fff;text-align:center;box-shadow:0 18px 42px rgba(10,17,32,.24)}
        .submission-preview-card:before{content:'glow';position:absolute;right:8px;top:-32px;color:rgba(163,230,53,.08);font:italic 100px Georgia,serif;pointer-events:none}
        .submission-preview-media{position:relative;display:grid;place-items:center;width:min(100%,196px);aspect-ratio:1;margin:4px auto 14px;overflow:hidden;border:1px solid rgba(163,230,53,.3);border-radius:25px;background:linear-gradient(135deg,rgba(163,230,53,.2),rgba(56,170,180,.15));box-shadow:0 14px 34px rgba(0,0,0,.4)}
        .submission-preview-media i{color:#c3ed6c;font-size:75px}
        .submission-preview-media img{display:none;width:100%;height:100%;object-fit:cover}
        .submission-preview-category{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border:1px solid rgba(163,230,53,.25);border-radius:20px;background:rgba(163,230,53,.1);color:#a3e635;font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .submission-preview-card h3{margin-top:10px;color:#fff;font-size:18px;font-weight:800;letter-spacing:-.25px;overflow-wrap:anywhere}
        .submission-preview-description{min-height:36px;margin-top:7px;color:rgba(255,255,255,.6);font-size:11px;line-height:1.6;overflow-wrap:anywhere}
        .submission-preview-price{display:flex;align-items:baseline;justify-content:center;gap:9px;margin-top:14px}
        .submission-preview-price strong{color:#a3e635;font-size:28px;letter-spacing:-.5px}
        .submission-preview-price del{color:rgba(255,255,255,.45);font-size:12px}
        .submission-preview-meta{display:flex;justify-content:center;gap:8px;flex-wrap:wrap;margin-top:13px}
        .submission-preview-meta span{padding:6px 10px;border:1px solid rgba(255,255,255,.12);border-radius:20px;background:rgba(255,255,255,.06);color:rgba(255,255,255,.74);font-size:9px;font-weight:700}
        .submission-preview-meta i{margin-right:4px}
        .submission-live-note{margin-top:12px;color:#68788f;font-size:10px;text-align:center}
        .submission-live-note i{color:#1b6a3c}
        .submission-tip{display:flex;gap:12px;align-items:flex-start;padding:15px 16px;border:1px solid rgba(20,83,45,.1);border-radius:14px;background:rgba(242,253,224,.72);color:#38543d}
        .submission-tip>i{color:#1b6a3c;font-size:19px}
        .submission-tip strong{display:block;margin-bottom:3px;color:#17341c;font-size:11px}
        .submission-tip p{font-size:10px;line-height:1.55}
        .emoji-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-top:8px}
        .emoji-pick{position:relative;display:flex;min-width:0;flex-direction:column;align-items:center;justify-content:center;gap:6px;padding:11px 4px 9px;border:1.5px solid #e5ebee;border-radius:14px;background:linear-gradient(160deg,#fff,#f6fafc);cursor:pointer;transition:.2s}
        .emoji-pick:hover{transform:translateY(-2px);border-color:#a3e635;box-shadow:0 8px 17px rgba(163,230,53,.18)}
        .emoji-pick:focus-visible{outline:3px solid rgba(99,185,87,.35);outline-offset:2px}
        .emoji-pick .pick-icon{display:grid;place-items:center;width:42px;height:42px;border-radius:13px;background:#f2fde0;color:#14532d;font-size:23px}
        .emoji-pick small{color:#68788f;font-size:8px;font-weight:800;letter-spacing:.04em;text-transform:uppercase;text-align:center}
        .emoji-pick.sel{border-color:#a3e635;background:linear-gradient(160deg,#f2fde0,#fff);box-shadow:0 0 0 3px rgba(163,230,53,.15),0 7px 16px rgba(163,230,53,.17)}
        .emoji-pick.sel:after{content:'✓';position:absolute;top:4px;right:5px;display:grid;place-items:center;width:16px;height:16px;border-radius:50%;background:#1b6a3c;color:#fff;font-size:10px;font-weight:800}
        .submission-shipping{margin:18px 0;border:1px solid rgba(22,35,60,.09);border-radius:14px;background:rgba(248,250,251,.8)}
        .submission-shipping summary{display:flex;align-items:center;gap:11px;padding:14px 16px;list-style:none;cursor:pointer}
        .submission-shipping summary::-webkit-details-marker{display:none}
        .submission-shipping summary>i:first-child{color:var(--teal-2);font-size:19px}
        .submission-shipping summary span{display:grid;gap:3px;flex:1}
        .submission-shipping summary strong{color:var(--ink);font-size:11px}
        .submission-shipping summary small{color:#78889a;font-size:9px;font-weight:400}
        .submission-shipping .submission-grid{padding:0 16px 5px}
        .submission-shipping-chevron{color:#78889a;transition:transform .2s}
        .submission-shipping[open] .submission-shipping-chevron{transform:rotate(180deg)}
        @media(max-width:980px){.submission-layout{grid-template-columns:minmax(0,1.25fr) minmax(250px,.8fr);gap:16px}.submission-card{padding:19px}.submission-side{top:12px}}
        @media(max-width:760px){.submission-wrap{padding-bottom:28px}.submission-head h2{font-size:23px}.submission-layout{grid-template-columns:1fr}.submission-side{position:static;grid-template-columns:minmax(0,1fr) minmax(0,.8fr);align-items:start}.submission-preview-card{padding:19px 16px}.submission-preview-media{max-width:160px}}
        @media(max-width:560px){.submission-grid{grid-template-columns:1fr}.submission-field.full{grid-column:auto}.submission-side{grid-template-columns:1fr}.submission-preview-card{max-width:none}.submission-preview-media{max-width:184px}.submission-actions{align-items:stretch;flex-direction:column}.submission-button{width:100%}.submission-head{align-items:flex-start}.submission-head-copy{align-items:flex-start}.submission-head-icon{width:42px;height:42px}.submission-card{padding:16px}.emoji-row{gap:7px}}
        .submission-head{position:relative;overflow:hidden;min-height:112px;padding:22px 24px;border:1px solid rgba(56,170,180,.18);border-radius:19px;background:linear-gradient(115deg,rgba(255,255,255,.96),rgba(228,247,245,.86) 62%,rgba(237,241,255,.78));box-shadow:0 12px 28px rgba(22,35,60,.055)}
        .submission-head:after{content:"";position:absolute;width:180px;height:180px;right:-48px;top:-104px;border-radius:50%;background:rgba(56,170,180,.12);pointer-events:none}
        .submission-head-copy{position:relative;z-index:1}.submission-head h2{font-size:23px;letter-spacing:-.6px}.submission-head p{max-width:600px;line-height:1.65}
        .submission-head-icon{background:linear-gradient(135deg,var(--teal),var(--teal-2));color:#fff;box-shadow:0 10px 22px rgba(43,127,184,.22)}
        .submission-review-pill{position:relative;z-index:1;display:inline-flex;align-items:center;gap:7px;flex:none;padding:9px 12px;border:1px solid rgba(56,170,180,.2);border-radius:999px;background:rgba(255,255,255,.72);color:#19725f;font-size:10px;font-weight:700}
        .submission-card{border-color:rgba(22,35,60,.09);box-shadow:0 15px 38px rgba(22,35,60,.075)}
        .submission-section-title{padding-bottom:10px;border-bottom:1px solid rgba(22,35,60,.07)}.submission-section-title i{color:var(--teal-2)}
        .submission-field input:focus,.submission-field select:focus,.submission-field textarea:focus{border-color:var(--teal);box-shadow:0 0 0 4px rgba(56,170,180,.13)}
        .submission-chip:hover,.submission-chip:focus-visible{border-color:var(--teal);background:#e9f8f6;color:#14665f}
        .submission-upload:hover,.submission-upload:focus-within,.submission-upload.drag{border-color:var(--teal);background:linear-gradient(145deg,#effaf8,#f3f8ff)}
        .submission-upload>div>i{color:var(--teal-2)}
        .submission-upload-cta{color:#14665f!important}
        .submission-upload-count{color:#14665f}
        .submission-thumb-main{background:linear-gradient(135deg,#38aab4,#2b7fb8);color:#fff}
        .submission-preview-card{background:radial-gradient(circle at 90% 5%,rgba(56,170,180,.28),transparent 34%),linear-gradient(155deg,#102333 0%,#102b3a 56%,#101b30 100%)}
        .submission-preview-card:before{color:rgba(125,219,226,.09)}
        .submission-preview-media{border-color:rgba(125,219,226,.32);background:linear-gradient(135deg,rgba(56,170,180,.2),rgba(43,127,184,.14))}
        .submission-preview-media i{color:#7ddbe2}
        .submission-preview-category{border-color:rgba(125,219,226,.25);background:rgba(56,170,180,.12);color:#b9f1ef}
        .submission-preview-price strong{color:#9ce8e2}
        .submission-live-note i,.submission-review-note i{color:var(--teal-2)}
        .submission-tip{border-color:rgba(56,170,180,.16);background:linear-gradient(140deg,rgba(255,255,255,.92),rgba(230,247,246,.72))}
        .submission-tip>i{color:var(--teal-2)}
        .emoji-pick.sel{border-color:var(--teal);background:linear-gradient(160deg,#e7f8f5,#fff);box-shadow:0 0 0 3px rgba(56,170,180,.14),0 7px 16px rgba(56,170,180,.13)}
        .emoji-pick.sel:after{background:linear-gradient(135deg,var(--teal),var(--teal-2))}
        .submission-button{background:linear-gradient(135deg,var(--teal),var(--teal-2));color:#fff;box-shadow:0 8px 18px rgba(43,127,184,.22)}
        .submission-button:hover{box-shadow:0 12px 22px rgba(43,127,184,.28)}
        @media(max-width:760px){.submission-review-pill{display:none}}
        @media(max-width:560px){.submission-head{padding:17px}.submission-head h2{font-size:20px}.submission-head-icon{width:42px;height:42px}}
        @media(prefers-reduced-motion:reduce){.submission-button,.emoji-pick,.submission-upload{transition:none}}
    </style>
</head>
<body>
<div class="layout">
    @include('user.include.sidebar')
    <main class="main product-submissions-main">
        <header class="topbar">
            <button class="hamburger" type="button" id="hamburger" aria-label="Open navigation" aria-controls="sidebar" aria-expanded="false">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>
            <div class="submission-page-title"><h1>Product submissions</h1><p>Share a beauty find with the community.</p></div>
        </header>
        <div class="submission-wrap">
            <div class="submission-head">
                <div class="submission-head-copy">
                    <span class="submission-head-icon" aria-hidden="true"><i class="ri-add-box-line"></i></span>
                    <div><h2>Submit a product</h2><p>Add the details and clear photos so our team can review your product for the shop.</p></div>
                </div>
                <span class="submission-review-pill"><i class="ri-shield-check-line" aria-hidden="true"></i>Reviewed before listing</span>
            </div>

            @if (session('status'))
                <div class="submission-alert" role="status">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="submission-alert errors" role="alert" tabindex="-1"><strong>Please check the form:</strong> {{ $errors->first() }}</div>
            @endif
            <div class="submission-alert" id="submissionFeedback" role="status" aria-live="polite" tabindex="-1" hidden></div>

            <div class="submission-layout">
                <section class="submission-card" aria-labelledby="productInformationTitle">
                    <h3 class="submission-section-title" id="productInformationTitle"><i class="ri-information-line" aria-hidden="true"></i>Product information</h3>
                    <form method="post" id="productSubmissionForm" action="{{ route('user.product-submissions.store') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="submission-field" for="submissionName">Product name <span class="required" aria-hidden="true">*</span>
                            <input id="submissionName" name="name" value="{{ old('name') }}" maxlength="150" placeholder="e.g. Rose Luxury Perfume" autocomplete="off" required>
                        </label>
                        <div class="submission-grid">
                            <label class="submission-field" for="submissionCategory">Category <span class="required" aria-hidden="true">*</span>
                                <select id="submissionCategory" name="category_id" required>
                                    <option value="">Choose a category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" data-specifications="{{ $category->specificationAttributes->map(fn ($attribute) => ['id' => $attribute->id, 'name' => $attribute->name, 'type' => $attribute->type, 'required' => $attribute->is_required, 'options' => $attribute->options ?? []])->toJson(JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG) }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="submission-field" for="submissionSize">Size / volume <span class="submission-hint">Optional</span>
                                <select id="submissionSize" name="size_id">
                                    <option value="">Not specified</option>
                                    @foreach ($sizes as $size)
                                        <option value="{{ $size->id }}" @selected(old('size_id') == $size->id)>{{ $size->name }} ({{ number_format($size->ml) }} ml)</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="submission-field" for="submissionBrand">Brand <span class="submission-hint">Optional</span>
                                <select id="submissionBrand" name="brand_id">
                                    <option value="">Not specified</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="submission-field" for="submissionPrice">Price (£) <span class="required" aria-hidden="true">*</span>
                                <span class="submission-price-wrap"><i class="ri-money-pound-circle-line" aria-hidden="true"></i><input id="submissionPrice" type="number" name="price" value="{{ old('price') }}" min="0" step="0.01" placeholder="0.00" inputmode="decimal" required></span>
                            </label>
                            <label class="submission-field" for="submissionDiscount">Discount (%)
                                <input id="submissionDiscount" type="number" name="discount" value="{{ old('discount', 0) }}" min="0" max="100" step="0.01" inputmode="decimal">
                                <span class="submission-chips" aria-label="Discount shortcuts">
                                    <button class="submission-chip" type="button" data-discount="5">5%</button>
                                    <button class="submission-chip" type="button" data-discount="10">10%</button>
                                    <button class="submission-chip" type="button" data-discount="20">20%</button>
                                    <button class="submission-chip" type="button" data-discount="0">Clear</button>
                                </span>
                            </label>
                            <label class="submission-field" for="submissionStock">Stock quantity <span class="required" aria-hidden="true">*</span>
                                <input id="submissionStock" type="number" name="stock" value="{{ old('stock') }}" min="0" step="1" placeholder="e.g. 25" inputmode="numeric" required>
                            </label>
                        </div>

                        <section id="submissionSpecifications" hidden>
                            <h3 class="submission-section-title section-gap"><i class="ri-list-settings-line" aria-hidden="true"></i>Product specifications</h3>
                            <p class="submission-hint" style="margin:-8px 0 14px">Fields depend on the selected category. Accurate specifications help the review team and customers.</p>
                            <div class="submission-grid" id="submissionSpecificationFields"></div>
                        </section>

                        <h3 class="submission-section-title section-gap"><i class="ri-image-line" aria-hidden="true"></i>Product photos</h3>
                        <div class="submission-field">
                            <div class="submission-upload" id="submissionUploadZone">
                                <input type="file" id="submissionImages" name="images[]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required aria-label="Choose product photos" aria-describedby="submissionPhotoHelp submissionPhotoCount">
                                <input type="hidden" id="coverImageIndex" name="cover_image_index" value="{{ old('cover_image_index', '0') }}">
                                <div aria-hidden="true">
                                    <i class="ri-image-add-line"></i>
                                    <strong>Click or drop photos here</strong>
                                    <span>JPG · PNG · WebP — up to 6 photos, max 2 MB each</span>
                                    <span class="submission-upload-cta"><i class="ri-upload-2-line"></i>Choose photos</span>
                                </div>
                            </div>
                            <span class="submission-hint" id="submissionPhotoHelp">Choose at least one clear photo, then select which one should appear on the storefront.</span>
                            <div class="submission-upload-count" id="submissionPhotoCount" aria-live="polite"></div>
                            <div class="submission-preview" id="productImagePreview" aria-label="Selected product photo previews"></div>
                            <button class="submission-clear-images" type="button" id="clearSubmissionImages" hidden>Remove selected photos</button>
                        </div>

                        <h3 class="submission-section-title section-gap"><i class="ri-sparkling-2-line" aria-hidden="true"></i>Product icon</h3>
                        <div class="submission-field">
                            <span class="submission-hint" id="submissionIconHelp">This icon appears on the store card if your product has no photo.</span>
                            <input type="hidden" name="emoji" id="submissionEmoji" value="{{ old('emoji', '🧴') }}">
                            <div class="emoji-row" id="submissionIconChoices" role="group" aria-label="Choose a product icon">
                                @foreach (config('product_icons.choices') as $choice)
                                    <button class="emoji-pick" type="button" data-value="{{ $choice['value'] }}" data-icon="{{ $choice['class'] }}" aria-pressed="{{ old('emoji', '🧴') === $choice['value'] ? 'true' : 'false' }}">
                                        <span class="pick-icon"><i class="{{ $choice['class'] }}" aria-hidden="true"></i></span><small>{{ $choice['label'] }}</small>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <details class="submission-shipping">
                            <summary><i class="ri-truck-line" aria-hidden="true"></i><span><strong>Shipping package</strong><small>Optional — add accurate package measurements to help calculate delivery</small></span><i class="ri-arrow-down-s-line submission-shipping-chevron" aria-hidden="true"></i></summary>
                            <div class="submission-grid">
                                <label class="submission-field" for="submissionWeight">Package weight
                                    <input id="submissionWeight" type="number" name="shipping_weight" value="{{ old('shipping_weight') }}" min="0" step="0.001" inputmode="decimal" placeholder="e.g. 250">
                                </label>
                                <label class="submission-field" for="submissionWeightUnit">Weight unit
                                    <select id="submissionWeightUnit" name="weight_unit">@foreach(['g', 'kg', 'mg', 'oz', 'lb'] as $unit)<option value="{{ $unit }}" @selected(old('weight_unit', 'g') === $unit)>{{ $unit }}</option>@endforeach</select>
                                </label>
                                <label class="submission-field" for="submissionLength">Length <input id="submissionLength" type="number" name="shipping_length" value="{{ old('shipping_length') }}" min="0" step="0.01" inputmode="decimal"></label>
                                <label class="submission-field" for="submissionWidth">Width <input id="submissionWidth" type="number" name="shipping_width" value="{{ old('shipping_width') }}" min="0" step="0.01" inputmode="decimal"></label>
                                <label class="submission-field" for="submissionHeight">Height <input id="submissionHeight" type="number" name="shipping_height" value="{{ old('shipping_height') }}" min="0" step="0.01" inputmode="decimal"></label>
                                <label class="submission-field" for="submissionDimensionUnit">Dimension unit
                                    <select id="submissionDimensionUnit" name="dimension_unit">@foreach(['cm', 'mm', 'm', 'in'] as $unit)<option value="{{ $unit }}" @selected(old('dimension_unit', 'cm') === $unit)>{{ $unit }}</option>@endforeach</select>
                                </label>
                            </div>
                        </details>

                        <label class="submission-field" for="submissionDescription">Description <span class="submission-hint">Optional · up to 1,000 characters</span>
                            <textarea id="submissionDescription" name="description" maxlength="1000" placeholder="Share a little about the product…">{{ old('description') }}</textarea>
                        </label>

                        <div class="submission-actions">
                            <p class="submission-review-note"><i class="ri-shield-check-line" aria-hidden="true"></i><span>Submissions are checked by our team before they’re listed in the shop.</span></p>
                            <button class="submission-button" type="submit"><i class="ri-send-plane-line" aria-hidden="true"></i><span>Submit for review</span></button>
                        </div>
                    </form>
                </section>

                <aside class="submission-side" aria-label="Live product preview">
                    <div>
                        <div class="submission-preview-card">
                            <div class="submission-preview-media"><i id="previewIcon" class="{{ config('product_icons.fallback') }}" aria-hidden="true"></i><img id="previewImage" alt="Preview of the selected main product photo"></div>
                            <span class="submission-preview-category" id="previewCategory"><i class="ri-apps-2-line" aria-hidden="true"></i><span>Category</span></span>
                            <h3 id="previewName">Product name</h3>
                            <p class="submission-preview-description" id="previewDescription">Your product description will appear here.</p>
                            <div class="submission-preview-price"><strong id="previewPrice">£0.00</strong><del id="previewOriginalPrice" hidden></del></div>
                            <div class="submission-preview-meta"><span id="previewSize"><i class="ri-flask-line" aria-hidden="true"></i><span>No size</span></span><span id="previewBrand"><i class="ri-price-tag-3-line" aria-hidden="true"></i><span>No brand</span></span><span id="previewStock"><i class="ri-archive-line" aria-hidden="true"></i><span>Stock: 0</span></span></div>
                        </div>
                        <p class="submission-live-note"><i class="ri-flashlight-line" aria-hidden="true"></i> Live preview updates as you type</p>
                    </div>
                    <div class="submission-tip"><i class="ri-lightbulb-line" aria-hidden="true"></i><div><strong>Make a great first impression</strong><p>Use well-lit photos and a clear, accurate description to help our review team and future customers.</p></div></div>
                </aside>
            </div>

        </div>
    </main>
</div>
<div class="overlay" id="overlay"></div>
<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('overlay');
        var hamburger = document.getElementById('hamburger');
        var form = document.getElementById('productSubmissionForm');
        var feedback = document.getElementById('submissionFeedback');
        var button = form.querySelector('[type="submit"]');
        var imageInput = document.getElementById('submissionImages');
        var imagePreview = document.getElementById('productImagePreview');
        var uploadZone = document.getElementById('submissionUploadZone');
        var photoCount = document.getElementById('submissionPhotoCount');
        var clearImages = document.getElementById('clearSubmissionImages');
        var previewImage = document.getElementById('previewImage');
        var previewIcon = document.getElementById('previewIcon');
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var iconChoices = @json(config('product_icons.choices'));
        var fallbackIcon = @json(config('product_icons.fallback'));
        var oldSpecificationValues = @json(old('specifications', []));
        var specificationSizes = @json($sizes->map(fn ($size) => $size->name.' ('.$size->ml.' ml)')->values());
        var previewUrl = null;
        var maxPhotos = 6;
        var maxPhotoBytes = 2 * 1024 * 1024;
        var selectedCoverIndex = 0;

        if (sidebar && overlay && hamburger) {
            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('show');
                hamburger.setAttribute('aria-expanded', 'false');
            }
            hamburger.addEventListener('click', function () {
                var open = !sidebar.classList.contains('open');
                sidebar.classList.toggle('open', open);
                overlay.classList.toggle('show', open);
                hamburger.setAttribute('aria-expanded', String(open));
            });
            overlay.addEventListener('click', closeSidebar);
        }

        function showFeedback(message, isError) {
            feedback.textContent = message;
            feedback.classList.toggle('errors', isError);
            feedback.hidden = false;
            feedback.focus();
        }

        function selectedIconClass(value) {
            var choice = iconChoices.find(function (item) { return item.value === value; });
            return choice ? choice.class : fallbackIcon;
        }

        function renderSpecificationFields() {
            var category = document.getElementById('submissionCategory');
            var section = document.getElementById('submissionSpecifications');
            var fields = document.getElementById('submissionSpecificationFields');
            var selected = category.options[category.selectedIndex];
            var specifications = selected && selected.dataset.specifications
                ? JSON.parse(selected.dataset.specifications)
                : [];
            fields.replaceChildren();
            section.hidden = specifications.length === 0;

            specifications.forEach(function (specification) {
                var wrapper = document.createElement('label');
                wrapper.className = 'submission-field';
                var controlId = 'submission-specification-' + specification.id;
                wrapper.htmlFor = controlId;
                wrapper.appendChild(document.createTextNode(specification.name + (specification.required ? ' *' : '')));

                var control;
                if (specification.type === 'select' || specification.type === 'boolean' || specification.type === 'size') {
                    control = document.createElement('select');
                    var prompt = document.createElement('option');
                    prompt.value = '';
                    prompt.textContent = specification.type === 'size' ? 'Choose size' : 'Choose ' + specification.name.toLowerCase();
                    control.appendChild(prompt);
                    var choices = specification.type === 'boolean'
                        ? ['Yes', 'No']
                        : (specification.type === 'size' ? specificationSizes : (specification.options || []));
                    choices.forEach(function (choice) {
                        var option = document.createElement('option');
                        option.value = choice;
                        option.textContent = choice;
                        control.appendChild(option);
                    });
                } else {
                    control = document.createElement('input');
                    control.type = specification.type === 'number' ? 'number' : 'text';
                    control.maxLength = 1000;
                    if (specification.type === 'number') {
                        control.step = 'any';
                        control.inputMode = 'decimal';
                    }
                }
                control.id = controlId;
                control.name = 'specifications[' + specification.id + ']';
                control.required = Boolean(specification.required);
                control.value = oldSpecificationValues[specification.id] || '';
                wrapper.appendChild(control);

                var hint = document.createElement('span');
                hint.className = 'submission-hint';
                hint.textContent = specification.required ? 'Required' : 'Optional';
                wrapper.appendChild(hint);
                fields.appendChild(wrapper);
            });
        }

        function renderImagePreviews() {
            imagePreview.replaceChildren();
            var files = Array.from(imageInput.files || []);
            photoCount.textContent = files.length ? files.length + ' / ' + maxPhotos + ' photos selected' : '';
            clearImages.hidden = !files.length;
            files.forEach(function (file, index) {
                var tile = document.createElement('div');
                tile.className = 'submission-thumb';
                tile.setAttribute('role', 'button');
                tile.setAttribute('tabindex', '0');
                tile.setAttribute('aria-pressed', String(index === selectedCoverIndex));
                tile.classList.toggle('is-cover', index === selectedCoverIndex);
                var image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = 'Selected product photo ' + (index + 1) + ': ' + file.name;
                image.onload = function () { URL.revokeObjectURL(image.src); };
                tile.appendChild(image);
                if (index === selectedCoverIndex) {
                    var mainLabel = document.createElement('span');
                    mainLabel.className = 'submission-thumb-main';
                    mainLabel.textContent = 'STOREFRONT';
                    tile.appendChild(mainLabel);
                }
                tile.addEventListener('click', function () {
                    selectedCoverIndex = index;
                    document.getElementById('coverImageIndex').value = String(index);
                    renderImagePreviews();
                });
                tile.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        tile.click();
                    }
                });
                imagePreview.appendChild(tile);
            });
            if (previewUrl) {
                URL.revokeObjectURL(previewUrl);
                previewUrl = null;
            }
            if (files.length) {
                previewUrl = URL.createObjectURL(files[selectedCoverIndex] || files[0]);
                previewImage.src = previewUrl;
                previewImage.style.display = 'block';
                previewIcon.style.display = 'none';
            } else {
                previewImage.removeAttribute('src');
                previewImage.style.display = 'none';
                previewIcon.style.display = 'block';
            }
        }

        function validateImages(files) {
            if (!files.length) return 'Choose at least one product photo.';
            if (files.length > maxPhotos) return 'Choose no more than ' + maxPhotos + ' photos.';
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var extensionOk = /\.(jpe?g|png|webp)$/i.test(file.name);
                var mimeOk = !file.type || /^image\/(jpeg|png|webp)$/i.test(file.type);
                if (!extensionOk || !mimeOk) return file.name + ' must be a JPG, PNG or WebP image.';
                if (file.size > maxPhotoBytes) return file.name + ' is larger than 2 MB.';
            }
            return '';
        }

        function updatePreview() {
            var category = document.getElementById('submissionCategory');
            var size = document.getElementById('submissionSize');
            var brand = document.getElementById('submissionBrand');
            var price = parseFloat(document.getElementById('submissionPrice').value) || 0;
            var discount = Math.min(100, Math.max(0, parseFloat(document.getElementById('submissionDiscount').value) || 0));
            var categoryText = category.value ? category.options[category.selectedIndex].text : 'Category';
            var sizeText = size.value ? size.options[size.selectedIndex].text.split(' (')[0] : 'No size';
            var name = document.getElementById('submissionName').value.trim();
            var description = document.getElementById('submissionDescription').value.trim();
            var iconValue = document.getElementById('submissionEmoji').value;
            var stock = document.getElementById('submissionStock').value;

            document.getElementById('previewName').textContent = name || 'Product name';
            document.getElementById('previewCategory').lastElementChild.textContent = categoryText;
            document.getElementById('previewDescription').textContent = description || 'Your product description will appear here.';
            document.getElementById('previewPrice').textContent = '£' + (price * (1 - discount / 100)).toFixed(2);
            document.getElementById('previewSize').lastElementChild.textContent = sizeText;
            document.getElementById('previewBrand').lastElementChild.textContent = brand.value ? brand.options[brand.selectedIndex].text : 'No brand';
            document.getElementById('previewStock').lastElementChild.textContent = 'Stock: ' + (stock || '0');
            var originalPrice = document.getElementById('previewOriginalPrice');
            if (discount > 0 && price > 0) {
                originalPrice.textContent = '£' + price.toFixed(2);
                originalPrice.hidden = false;
            } else {
                originalPrice.hidden = true;
            }
            previewIcon.className = selectedIconClass(iconValue);
        }

        imageInput.addEventListener('change', function () {
            selectedCoverIndex = 0;
            document.getElementById('coverImageIndex').value = imageInput.files.length ? '0' : '';
            renderImagePreviews();
            var issue = validateImages(Array.from(imageInput.files || []));
            if (issue) showFeedback(issue, true);
            else if (!feedback.hidden && feedback.classList.contains('errors')) feedback.hidden = true;
        });

        ['dragenter', 'dragover'].forEach(function (eventName) {
            uploadZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                uploadZone.classList.add('drag');
            });
        });
        ['dragleave', 'drop'].forEach(function (eventName) {
            uploadZone.addEventListener(eventName, function (event) {
                event.preventDefault();
                uploadZone.classList.remove('drag');
            });
        });
        uploadZone.addEventListener('drop', function (event) {
            if (!event.dataTransfer || !event.dataTransfer.files.length) return;
            try {
                var transfer = new DataTransfer();
                Array.from(event.dataTransfer.files).forEach(function (file) { transfer.items.add(file); });
                imageInput.files = transfer.files;
                imageInput.dispatchEvent(new Event('change', {bubbles: true}));
            } catch (error) {
                showFeedback('Your browser could not add dropped photos. Please use Choose photos instead.', true);
            }
        });
        clearImages.addEventListener('click', function () {
            imageInput.value = '';
            selectedCoverIndex = 0;
            document.getElementById('coverImageIndex').value = '';
            renderImagePreviews();
            imageInput.focus();
        });

        document.querySelectorAll('.emoji-pick').forEach(function (tile) {
            tile.classList.toggle('sel', tile.dataset.value === document.getElementById('submissionEmoji').value);
            tile.addEventListener('click', function () {
                document.querySelectorAll('.emoji-pick').forEach(function (choice) {
                    var selected = choice === tile;
                    choice.classList.toggle('sel', selected);
                    choice.setAttribute('aria-pressed', String(selected));
                });
                document.getElementById('submissionEmoji').value = tile.dataset.value;
                updatePreview();
            });
        });

        document.querySelectorAll('[data-discount]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                document.getElementById('submissionDiscount').value = chip.dataset.discount;
                updatePreview();
            });
        });
        form.querySelectorAll('input:not([type=file]), select, textarea').forEach(function (field) {
            field.addEventListener('input', updatePreview);
            field.addEventListener('change', updatePreview);
        });
        document.getElementById('submissionCategory').addEventListener('change', function () {
            oldSpecificationValues = {};
            renderSpecificationFields();
        });
        renderSpecificationFields();
        renderImagePreviews();
        updatePreview();

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (!form.reportValidity()) return;
            var imageIssue = validateImages(Array.from(imageInput.files || []));
            if (imageIssue) {
                showFeedback(imageIssue, true);
                imageInput.focus();
                return;
            }
            button.disabled = true;
            button.innerHTML = '<i class="ri-loader-4-line" aria-hidden="true"></i><span>Submitting…</span>';
            try {
                var response = await fetch(form.action, {
                    method: 'POST',
                    headers: {'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf},
                    body: new FormData(form),
                    credentials: 'same-origin'
                });
                var data = await response.json().catch(function () { return {}; });
                if (response.status === 419) throw new Error('Your session has expired. Refresh the page and try again.');
                if (!response.ok) {
                    var errorValues = data.errors ? Object.values(data.errors).reduce(function (all, value) {
                        return all.concat(Array.isArray(value) ? value : [value]);
                    }, []) : [];
                    throw new Error(errorValues[0] || data.message || 'The product could not be submitted. Please check your details and try again.');
                }
                if (!data.submission || !data.redirect) throw new Error('The submission response was incomplete. Please refresh to check your submissions.');

                var successMessage = data.message || 'Your product was submitted for admin review.';
                if (typeof window.ajaxNavigate === 'function') {
                    var navigated = await window.ajaxNavigate(data.redirect);
                    if (navigated && typeof window.showToast === 'function') window.showToast(successMessage, 'success');
                    else if (!navigated) showFeedback(successMessage + ' Open My Submissions to see its status.', false);
                } else {
                    window.location.assign(data.redirect);
                }
            } catch (error) {
                showFeedback(error.message || 'A network error prevented the submission. Please try again.', true);
            } finally {
                button.disabled = false;
                button.innerHTML = '<i class="ri-send-plane-line" aria-hidden="true"></i><span>Submit for review</span>';
            }
        });
    })();
</script>
</body>
</html>
