@extends('admin.layouts.ecommerce')
@section('title','Blog tags')
@section('content')
<style>
    .blog-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}.blog-head h1{display:flex;align-items:center;gap:11px;margin:0;font-size:24px}.blog-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .blog-panel{min-width:0;padding:19px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.blog-panel h2{margin:0 0 13px;font-size:15px}
    .blog-field{display:grid;gap:6px;margin-bottom:12px}.blog-field label{font-size:11px;font-weight:800}.blog-field input{width:100%;min-height:40px;padding:10px 11px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}
    .tag-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.tag-card{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:9px;padding:12px;border:1px solid var(--border);border-radius:12px;background:var(--bg)}.tag-edit{display:grid;grid-template-columns:1fr 1fr;gap:7px;grid-column:1/-1}.tag-edit input{width:100%;min-width:0;padding:8px 9px;border:1px solid var(--border);border-radius:8px;background:var(--card);color:var(--text);font:inherit;font-size:11px}.tag-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:800;font-size:12px}.tag-slug{display:block;margin-top:3px;color:var(--muted);font-size:10px}.tag-ops{display:flex;gap:5px}.blog-op{display:grid;place-items:center;width:32px;height:32px;border:1px solid var(--border);border-radius:8px;background:var(--card);color:var(--brand);cursor:pointer}.blog-op.danger{color:#b83232;background:#fff5f5;border-color:#f5d6d6}.tag-footer{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:15px}.tag-footer nav{display:flex;gap:5px}.tag-footer nav a,.tag-footer nav span{display:grid;place-items:center;min-width:32px;height:32px;padding:0 8px;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:11px;text-decoration:none}.tag-footer nav span[aria-current]{background:var(--brand);color:white}.blog-flash{padding:11px 13px;border-radius:10px;background:#e6f8ee;color:#176a3b;font-size:12px;font-weight:700}
    .blog-add-dialog{width:min(500px,calc(100% - 28px));max-width:calc(100% - 28px);padding:24px;border:1px solid var(--border);border-radius:18px;background:var(--card);color:var(--text);box-shadow:0 24px 80px rgba(15,36,48,.25)}.blog-add-dialog[open]{position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%)}.blog-add-dialog::backdrop{background:rgba(7,24,33,.58);backdrop-filter:blur(3px)}.blog-dialog-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}.blog-dialog-head h2{margin:0;font-size:18px}.blog-dialog-close{display:grid;width:36px;height:36px;place-items:center;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font-size:18px;cursor:pointer}.blog-dialog-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}
    @media(max-width:560px){.tag-list{grid-template-columns:1fr}.blog-panel{padding:14px}.blog-add-dialog{padding:19px}}
</style>
<header class="blog-head"><div><h1><i class="ri-price-tag-3-line"></i>Blog tags</h1><p>Attach keywords to posts to connect similar topics across the journal.</p></div><button class="button primary" type="button" data-open-blog-tag><i class="ri-add-line"></i> Add tag</button></header>
@if(session('success'))<div class="blog-flash" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="blog-panel"><div class="toolbar"><div><h2 style="margin:0">All tags</h2><span class="count-note">{{ number_format($tags->total()) }} keywords</span></div></div>
    <div class="tag-list">
        @forelse($tags as $tag)<article class="tag-card">
            <div><span class="tag-name">{{ $tag->name }}</span><span class="tag-slug">/{{ $tag->slug }} · {{ number_format($tag->posts_count) }} posts</span></div>
            <div class="tag-ops"><button class="blog-op" type="submit" form="tag-{{ $tag->id }}" aria-label="Save {{ $tag->name }}"><i class="ri-save-line"></i></button><form method="POST" action="{{ route('admin.blog.tags.destroy',$tag) }}" data-confirm-modal data-confirm-title="Delete this tag?" data-confirm-message="Posts using the tag will remain available.">@csrf @method('DELETE')<button class="blog-op danger" type="submit" aria-label="Delete {{ $tag->name }}"><i class="ri-delete-bin-line"></i></button></form></div>
            <form class="tag-edit" id="tag-{{ $tag->id }}" method="POST" action="{{ route('admin.blog.tags.update',$tag) }}">@csrf @method('PATCH')<input name="name" value="{{ $tag->name }}" required maxlength="80" aria-label="Tag name"><input name="slug" value="{{ $tag->slug }}" maxlength="100" aria-label="Tag slug"></form>
        </article>@empty<p class="empty">No tags yet. Create a keyword to help readers find related stories.</p>@endforelse
    </div><div class="tag-footer"><span class="count-note">Showing {{ $tags->firstItem()??0 }}–{{ $tags->lastItem()??0 }} of {{ $tags->total() }}</span>{{ $tags->links() }}</div>
</section>
<dialog class="blog-add-dialog" id="blogTagDialog" aria-labelledby="blogTagDialogTitle">
    <div class="blog-dialog-head"><h2 id="blogTagDialogTitle"><i class="ri-add-circle-line"></i> Add blog tag</h2><button class="blog-dialog-close" type="button" data-close-blog-tag aria-label="Close dialog">×</button></div>
    <form method="POST" action="{{ route('admin.blog.tags.store') }}">@csrf
        <div class="blog-field"><label for="tagName">Name *</label><input id="tagName" name="name" value="{{ old('name') }}" required maxlength="80" placeholder="e.g. SPF"></div>
        <div class="blog-field"><label for="tagSlug">Slug</label><input id="tagSlug" name="slug" value="{{ old('slug') }}" maxlength="100" placeholder="Generated from the name"></div>
        <div class="blog-dialog-actions"><button class="button" type="button" data-close-blog-tag>Cancel</button><button class="button primary" type="submit"><i class="ri-add-line"></i> Create tag</button></div>
    </form>
</dialog>
@push('scripts')
<script>
    (function () {
        if (!window.__blogTagControlsBound) {
            window.__blogTagControlsBound = true;
            document.addEventListener('click', function (event) {
                var dialog = document.getElementById('blogTagDialog');
                if (event.target.closest('[data-open-blog-tag]') && dialog) dialog.showModal();
                if (event.target.closest('[data-close-blog-tag]') && dialog) dialog.close();
                if (event.target === dialog && dialog) dialog.close();
            });
        }
        var dialog = document.getElementById('blogTagDialog');
        if (dialog && document.querySelector('.errors')) dialog.showModal();
    })();
</script>
@endpush
@endsection
