@extends('admin.layouts.ecommerce')
@section('title','Blog SEO settings')
@section('content')
<style>
    .seo-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}.seo-head h1{display:flex;align-items:center;gap:11px;margin:0;font-size:24px}.seo-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .seo-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(260px,.7fr);gap:16px;align-items:start}.seo-panel{padding:21px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.seo-panel h2{margin:0 0 8px;font-size:15px}.seo-panel>p{margin:0 0 17px;color:var(--muted);font-size:11px;line-height:1.6}.seo-field{display:grid;gap:7px;margin-bottom:14px}.seo-field label{font-size:11px;font-weight:800}.seo-field input,.seo-field textarea{width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}.seo-field textarea{min-height:105px;resize:vertical}.seo-switch{display:flex;gap:10px;align-items:flex-start;padding:13px;border:1px solid var(--border);border-radius:11px;background:var(--bg);font-size:12px}.seo-switch input{margin-top:3px;accent-color:var(--brand)}.seo-switch small{display:block;margin-top:3px;color:var(--muted);font-size:10px}.search-preview{padding:18px;border:1px solid var(--border);border-radius:12px;background:var(--bg)}.search-preview small{display:block;color:#188038;font-size:11px}.search-preview strong{display:block;margin:5px 0;color:#1a0dab;font-size:18px;font-weight:500;line-height:1.35}.search-preview p{margin:0;color:var(--text);font-size:12px;line-height:1.5}.seo-note{margin-top:13px;padding:12px;border-radius:10px;background:#eef8fb;color:#285c70;font-size:11px;line-height:1.6}.seo-flash{padding:11px 13px;border-radius:10px;background:#e6f8ee;color:#176a3b;font-size:12px;font-weight:700}
    @media(max-width:780px){.seo-layout{grid-template-columns:1fr}}
</style>
<header class="seo-head"><div><h1><i class="ri-search-eye-line"></i>Blog SEO settings</h1><p>Set the default search appearance for the journal and control indexing.</p></div></header>
@if(session('success'))<div class="seo-flash" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="seo-layout">
    <section class="seo-panel"><h2>Journal defaults</h2><p>Individual published posts can override these values from their editor.</p>
        <form method="POST" action="{{ route('admin.blog.seo.update') }}">@csrf @method('PATCH')<input type="hidden" name="indexable" value="0">
            <div class="seo-field"><label for="seoTitle">Default SEO title *</label><input id="seoTitle" name="title" required maxlength="180" value="{{ old('title',$settings['title']) }}"></div>
            <div class="seo-field"><label for="seoDescription">Default description *</label><textarea id="seoDescription" name="description" required maxlength="320">{{ old('description',$settings['description']) }}</textarea></div>
            <label class="seo-switch"><input type="checkbox" name="indexable" value="1" @checked(old('indexable',$settings['indexable']))><span><strong>Allow search engines to index the blog</strong><small>When disabled, journal and article pages receive a noindex directive.</small></span></label>
            <button class="button primary" type="submit" style="margin-top:15px"><i class="ri-save-line"></i>Save SEO settings</button>
        </form>
    </section>
    <aside class="seo-panel"><h2>Search preview</h2><p>This is an approximate preview; search engines may choose different text.</p>
        <div class="search-preview"><small>{{ url('/blog') }}</small><strong id="seoPreviewTitle">{{ old('title',$settings['title']) }}</strong><p id="seoPreviewDescription">{{ old('description',$settings['description']) }}</p></div>
        <div class="seo-note"><i class="ri-information-line"></i> Post-level SEO titles, descriptions, canonical links, and index settings are managed in each post editor.</div>
    </aside>
</div>
@push('scripts')<script>
(()=>{const title=document.getElementById('seoTitle'),description=document.getElementById('seoDescription');title.addEventListener('input',()=>document.getElementById('seoPreviewTitle').textContent=title.value);description.addEventListener('input',()=>document.getElementById('seoPreviewDescription').textContent=description.value)})();
</script>@endpush
@endsection
