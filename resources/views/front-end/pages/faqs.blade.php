@include('front-end.include.header')
@include('front-end.include.loader')
<style>
    .faq-page{--faq-ink:#173c38;--faq-muted:#75827f;--faq-teal:#258f88;--faq-line:#e5ece8;color:var(--faq-ink);font-family:'Open Sans',sans-serif}
    .faq-hero{position:relative;isolation:isolate;display:grid;min-height:350px;place-items:center;overflow:hidden;padding:145px 20px 60px;background:linear-gradient(110deg,rgba(15,57,54,.86),rgba(32,114,105,.52)),url('{{ $pageHeader['image'] }}') center/cover no-repeat;color:#fff;text-align:center}
    .faq-hero:after{position:absolute;z-index:-1;right:-120px;bottom:-310px;width:520px;height:520px;border:1px solid rgba(255,255,255,.24);border-radius:50%;content:""}
    .faq-hero-inner{max-width:760px}.faq-hero .faq-kicker{color:#c7ebe1;font:700 10px Montserrat,sans-serif;letter-spacing:2px;text-transform:uppercase}.faq-hero h1{margin:13px 0;color:#fff;font:500 clamp(34px,6vw,60px)/1.1 Montserrat,sans-serif;letter-spacing:-1.5px}.faq-hero p{max-width:570px;margin:0 auto;color:rgba(255,255,255,.82);font-size:14px;line-height:1.8}
    .faq-shell{display:grid;grid-template-columns:245px minmax(0,1fr);gap:35px;width:min(1120px,calc(100% - 40px));margin:0 auto;padding:56px 0 82px}
    .faq-categories{position:sticky;top:22px;align-self:start;padding:18px;border:1px solid var(--faq-line);border-radius:15px;background:#fff;box-shadow:0 9px 30px rgba(27,71,64,.05)}
    .faq-categories h2{margin:0 0 12px;color:#38534e;font:700 10px Montserrat,sans-serif;letter-spacing:1px;text-transform:uppercase}
    .faq-category-link{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 11px;border-radius:9px;color:#526762;font-size:11px;text-decoration:none;transition:background .2s,color .2s}
    .faq-category-link:hover,.faq-category-link.active{background:#eaf5f1;color:#17766a}.faq-category-link small{display:grid;min-width:22px;height:22px;place-items:center;border-radius:20px;background:#f2f5f4;color:#778984;font-size:9px}
    .faq-content-head{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:20px}.faq-content-head p{margin:0;color:var(--faq-teal);font:700 9px Montserrat,sans-serif;letter-spacing:1.6px;text-transform:uppercase}.faq-content-head h2{margin:5px 0 0;color:var(--faq-ink);font:600 clamp(22px,3vw,29px) Montserrat,sans-serif}.faq-count{color:#8b9995;font-size:10px}
    .faq-list{display:grid;gap:10px}.faq-item{overflow:hidden;border:1px solid var(--faq-line);border-radius:13px;background:#fff;box-shadow:0 5px 18px rgba(27,71,64,.035);transition:border-color .2s,box-shadow .2s}.faq-item[open]{border-color:#b9ded1;box-shadow:0 10px 24px rgba(27,71,64,.075)}
    .faq-item summary{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 18px;list-style:none;color:#294b45;font:600 12px Montserrat,sans-serif;cursor:pointer}.faq-item summary::-webkit-details-marker{display:none}.faq-item summary:after{display:grid;width:27px;height:27px;flex:none;place-items:center;border:1px solid #dce9e4;border-radius:50%;color:#378578;content:"+";font:500 17px 'Open Sans',sans-serif}.faq-item[open] summary:after{content:"−";background:#eaf5f1}
    .faq-answer{padding:0 18px 17px;color:#72817c;font-size:12px;line-height:1.8}.faq-source{display:inline-flex;align-items:center;gap:5px;margin-top:10px;color:var(--faq-teal);font-size:10px;font-weight:700;text-decoration:underline;text-underline-offset:3px}
    .faq-empty{padding:30px;border:1px dashed #cbdad4;border-radius:13px;color:var(--faq-muted);text-align:center}
    @media(max-width:760px){.faq-shell{grid-template-columns:1fr;gap:20px;padding:36px 0 55px}.faq-categories{position:static;display:flex;gap:6px;overflow-x:auto;padding:10px}.faq-categories h2{display:none}.faq-category-link{flex:none;white-space:nowrap;background:#f5f8f6}.faq-category-link small{display:none}}
    @media(max-width:520px){.faq-hero{min-height:310px;padding:130px 18px 42px}.faq-shell{width:calc(100% - 30px)}.faq-item summary{padding:14px;font-size:11px}.faq-answer{padding:0 14px 15px}.faq-count{display:none}}
</style>
<main class="faq-page">
    <header class="faq-hero"><div class="faq-hero-inner"><span class="faq-kicker">Here to make things easier</span><h1>{{ $selectedCategory?->name ?? $pageHeader['title'] }}</h1><p>{{ $selectedCategory?->description ?: 'Find clear answers about products, orders, delivery, returns and caring for your routine.' }}</p></div></header>
    <div class="faq-shell">
        <nav class="faq-categories" aria-label="FAQ categories">
            <h2>Browse topics</h2>
            <a class="faq-category-link {{ !$selectedCategory ? 'active' : '' }}" href="{{ route('faqs.index') }}">All questions <small>{{ $categories->sum('faqs_count') }}</small></a>
            @foreach($categories as $category)
            <a class="faq-category-link {{ $selectedCategory?->id === $category->id ? 'active' : '' }}" href="{{ route('faqs.category', $category->slug) }}">{{ $category->name }} <small>{{ $category->faqs_count }}</small></a>
            @endforeach
        </nav>
        <section>
            <header class="faq-content-head"><div><p>{{ $selectedCategory ? 'Questions by topic' : 'A helpful place to start' }}</p><h2>{{ $selectedCategory?->name ?? 'Frequently asked questions' }}</h2></div><span class="faq-count">{{ $selectedCategory ? $faqs->count() : $categories->sum('faqs_count') }} answers</span></header>
            @if($selectedCategory)
                <div class="faq-list">
                    @forelse($faqs as $faq)
                    <details class="faq-item"><summary>{{ $faq->question }}</summary><div class="faq-answer">{{ $faq->answer }}@if($faq->source_url)<br><a class="faq-source" href="{{ $faq->source_url }}" target="_blank" rel="noopener noreferrer"><i class="ri-external-link-line"></i> Further product-safety information</a>@endif</div></details>
                    @empty<div class="faq-empty">There are no published answers in this topic yet.</div>@endforelse
                </div>
            @else
                @forelse($categories as $category)
                <section class="faq-topic-group"><header class="faq-content-head"><div><p>Topic</p><h2><a href="{{ route('faqs.category', $category->slug) }}">{{ $category->name }}</a></h2></div><a class="faq-count" href="{{ route('faqs.category', $category->slug) }}">View all {{ $category->faqs_count }} →</a></header>
                    <div class="faq-list">@foreach($category->faqs as $faq)<details class="faq-item"><summary>{{ $faq->question }}</summary><div class="faq-answer">{{ $faq->answer }}@if($faq->source_url)<br><a class="faq-source" href="{{ $faq->source_url }}" target="_blank" rel="noopener noreferrer"><i class="ri-external-link-line"></i> Further product-safety information</a>@endif</div></details>@endforeach</div>
                </section>
                @empty<div class="faq-empty">Our team is preparing helpful answers. Please check back soon.</div>@endforelse
            @endif
        </section>
    </div>
</main>
@include('front-end.include.newsletter')
@include('front-end.include.footer')
@include('front-end.include.js')
