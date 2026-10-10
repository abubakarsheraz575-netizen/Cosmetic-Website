@extends('admin.layouts.ecommerce')
@section('title','Blog categories')
@section('content')
<style>
    .blog-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap}.blog-head h1{display:flex;align-items:center;gap:11px;margin:0;font-size:24px}.blog-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .blog-list-card{min-width:0;padding:19px;border:1px solid var(--border);border-radius:15px;background:var(--card);box-shadow:var(--shadow)}.blog-list-card h2{margin:0;font-size:15px}
    .blog-field{display:grid;gap:6px;margin-bottom:14px}.blog-field label{font-size:12px;font-weight:800}.blog-field input,.blog-field textarea{width:100%;min-height:42px;padding:10px 12px;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font:inherit;font-size:12px}.blog-field textarea{min-height:90px;resize:vertical}
    .blog-table{min-width:920px}.blog-table input:not([type=checkbox]){width:100%;min-width:90px;padding:9px 10px;border:1px solid var(--border);border-radius:9px;background:var(--bg);color:var(--text);font:inherit;font-size:11px}.blog-table input:focus,.blog-field input:focus,.blog-field textarea:focus{outline:2px solid rgba(62,197,224,.35);border-color:var(--brand)}
    .blog-status-control{display:inline-flex;min-width:94px;align-items:center;justify-content:center;gap:7px;padding:7px 10px;border:1px solid #b9ebd4;border-radius:999px;background:#e4f8ef;color:#08784d;font-size:11px;font-weight:800;cursor:pointer;white-space:nowrap}.blog-status-control.is-inactive{border-color:#f2deb0;background:#fff7e1;color:#946300}.blog-status-control input{width:15px;height:15px;margin:0;accent-color:#0c9161}.blog-op{display:grid;place-items:center;width:36px;height:36px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--brand);cursor:pointer}.blog-op:hover{border-color:var(--brand);background:#f0fbfd}.blog-op.danger{color:#b83232;background:#fff5f5;border-color:#f5d6d6}.blog-flash{padding:11px 13px;border-radius:10px;background:#e6f8ee;color:#176a3b;font-size:12px;font-weight:700}
    .blog-add-dialog{width:min(500px,calc(100% - 28px));max-width:calc(100% - 28px);padding:24px;border:1px solid var(--border);border-radius:18px;background:var(--card);color:var(--text);box-shadow:0 24px 80px rgba(15,36,48,.25)}.blog-add-dialog[open]{position:fixed;inset:50% auto auto 50%;margin:0;transform:translate(-50%,-50%)}.blog-add-dialog::backdrop{background:rgba(7,24,33,.58);backdrop-filter:blur(3px)}.blog-dialog-head{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}.blog-dialog-head h2{margin:0;font-size:18px}.blog-dialog-close{display:grid;width:36px;height:36px;place-items:center;border:1px solid var(--border);border-radius:10px;background:var(--bg);color:var(--text);font-size:18px;cursor:pointer}.blog-dialog-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}
    .blog-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:13px}.blog-foot nav{display:flex;gap:5px}.blog-foot nav a,.blog-foot nav span{display:grid;place-items:center;min-width:32px;height:32px;padding:0 8px;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:11px;text-decoration:none}.blog-foot nav span[aria-current]{background:var(--brand);color:#fff}
    @media(max-width:520px){.blog-list-card{padding:14px}.blog-add-dialog{padding:19px}}
</style>
<header class="blog-head"><div><h1><i class="ri-folders-line"></i>Blog categories</h1><p>Group related posts and keep category pages organized for readers.</p></div><button class="button primary" type="button" data-open-blog-category><i class="ri-add-line"></i> Add category</button></header>
@if(session('success'))<div class="blog-flash" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<section class="blog-list-card"><div class="toolbar"><div><h2>All categories</h2><span class="count-note">{{ number_format($categories->total()) }} categories</span></div></div>
    <div class="table-wrap"><table class="blog-table" data-server-pagination="true"><thead><tr><th>Category</th><th>Slug</th><th>Description</th><th>Posts</th><th>Status</th><th>Save</th><th>Delete</th></tr></thead><tbody>
    @forelse($categories as $category)
        <tr>
            <td><form id="category-{{ $category->id }}" method="POST" action="{{ route('admin.blog.categories.update',$category) }}">@csrf @method('PATCH')</form><input form="category-{{ $category->id }}" aria-label="Category name" name="name" required maxlength="100" value="{{ $category->name }}"></td>
            <td><input form="category-{{ $category->id }}" aria-label="Category slug" name="slug" maxlength="120" value="{{ $category->slug }}"></td>
            <td><input form="category-{{ $category->id }}" aria-label="Category description" name="description" maxlength="500" value="{{ $category->description }}" placeholder="Optional description"></td>
            <td><strong>{{ number_format($category->posts_count) }}</strong> posts</td>
            <td><label class="blog-status-control {{ $category->is_active?'':'is-inactive' }}"><input form="category-{{ $category->id }}" type="hidden" name="is_active" value="0"><input form="category-{{ $category->id }}" type="checkbox" name="is_active" value="1" @checked($category->is_active) aria-label="Toggle {{ $category->name }} status"><span>{{ $category->is_active?'Active':'Inactive' }}</span></label></td>
            <td><button class="blog-op" type="submit" form="category-{{ $category->id }}" aria-label="Save category"><i class="ri-save-line"></i></button></td>
            <td><form method="POST" action="{{ route('admin.blog.categories.destroy',$category) }}" data-confirm-modal data-confirm-title="Delete this category?" data-confirm-message="Posts will remain in any other assigned categories.">@csrf @method('DELETE')<button class="blog-op danger" type="submit" aria-label="Delete {{ $category->name }}"><i class="ri-delete-bin-line"></i></button></form></td>
        </tr>
    @empty<tr><td colspan="7" class="empty">No categories yet. Use “Add category” to create your first one.</td></tr>@endforelse
        </tbody></table></div>
    <div class="blog-foot"><span class="count-note">Showing {{ $categories->firstItem()??0 }}–{{ $categories->lastItem()??0 }} of {{ $categories->total() }}</span>{{ $categories->links() }}</div>
</section>
<dialog class="blog-add-dialog" id="blogCategoryDialog" aria-labelledby="blogCategoryDialogTitle">
    <div class="blog-dialog-head"><h2 id="blogCategoryDialogTitle"><i class="ri-add-circle-line"></i> Add blog category</h2><button class="blog-dialog-close" type="button" data-close-blog-category aria-label="Close dialog">×</button></div>
    <form method="POST" action="{{ route('admin.blog.categories.store') }}">@csrf
        <div class="blog-field"><label for="categoryName">Name *</label><input id="categoryName" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="e.g. Skincare"></div>
        <div class="blog-field"><label for="categorySlug">Slug</label><input id="categorySlug" name="slug" maxlength="120" value="{{ old('slug') }}" placeholder="Generated from the name"></div>
        <div class="blog-field"><label for="categoryDescription">Description</label><textarea id="categoryDescription" name="description" maxlength="500" placeholder="A short introduction for this category.">{{ old('description') }}</textarea></div>
        <div class="blog-dialog-actions"><button class="button" type="button" data-close-blog-category>Cancel</button><button class="button primary" type="submit"><i class="ri-add-line"></i> Create category</button></div>
    </form>
</dialog>
@push('scripts')
<script>
    (function () {
        if (!window.__blogCategoryControlsBound) {
            window.__blogCategoryControlsBound = true;
            document.addEventListener('click', function (event) {
                var dialog = document.getElementById('blogCategoryDialog');
                if (event.target.closest('[data-open-blog-category]') && dialog) dialog.showModal();
                if (event.target.closest('[data-close-blog-category]') && dialog) dialog.close();
                if (event.target === dialog && dialog) dialog.close();
            });
            document.addEventListener('change', function (event) {
                var statusInput = event.target.closest('.blog-status-control input[type="checkbox"]');
                if (!statusInput) return;
                var statusLabel = statusInput.closest('.blog-status-control');
                statusLabel.classList.toggle('is-inactive', !statusInput.checked);
                statusLabel.querySelector('span').textContent = statusInput.checked ? 'Active' : 'Inactive';
            });
        }
        var dialog = document.getElementById('blogCategoryDialog');
        if (dialog && (dialog.querySelector('.errors') || document.querySelector('.errors'))) dialog.showModal();
    })();
</script>
@endpush
@endsection
