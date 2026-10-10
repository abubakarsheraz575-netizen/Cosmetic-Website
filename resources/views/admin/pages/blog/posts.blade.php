@extends('admin.layouts.ecommerce')

@section('title', 'Blog posts')

@section('content')
<style>
    .blog-admin-head{display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}
    .blog-admin-head h1{display:flex;align-items:center;gap:12px;margin:0;font-size:25px}
    .blog-admin-head p{margin:5px 0 0;color:var(--muted);font-size:13px}
    .blog-admin-toolbar{display:grid;grid-template-columns:minmax(190px,1.2fr) minmax(150px,.7fr) minmax(130px,.55fr) minmax(110px,.45fr) auto auto;gap:8px;align-items:center}
    .blog-admin-toolbar input,.blog-admin-toolbar select{width:100%;min-width:0;min-height:40px;padding:9px 11px;border:1px solid var(--border);border-radius:10px;background:var(--card);color:var(--text);font:inherit;font-size:12px}
    .blog-admin-table{min-width:980px}.blog-cover-cell{display:flex;align-items:center;gap:11px;min-width:240px}.blog-cover-cell img,.blog-cover-placeholder{width:54px;height:48px;flex:none;object-fit:cover;border-radius:9px;background:linear-gradient(135deg,#dff6f4,#f9e8dd)}
    .blog-cover-placeholder{display:grid;place-items:center;color:#248991;font-size:21px}.blog-cover-copy{min-width:0}.blog-cover-copy a{display:block;max-width:340px;overflow:hidden;color:var(--brand);font-weight:700;text-overflow:ellipsis;white-space:nowrap;text-decoration:none}.blog-cover-copy small{display:block;margin-top:4px;color:var(--muted);font-size:10px}
    .blog-category-chips,.blog-actions{display:flex;align-items:center;gap:5px;flex-wrap:wrap}.blog-chip{display:inline-flex;padding:4px 7px;border:1px solid var(--border);border-radius:20px;color:var(--muted);font-size:10px;white-space:nowrap}
    .blog-action{display:grid;place-items:center;width:34px;height:34px;border:1px solid var(--border);border-radius:9px;background:var(--card);color:var(--brand);font-size:16px;text-decoration:none}.blog-action.danger{color:#c43b3b;background:#fff5f5;border-color:#f5d6d6}
    .blog-status{display:inline-flex;align-items:center;gap:6px;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:800}.blog-status.published{background:#dcf7eb;color:#108255}.blog-status.draft{background:#fff3d6;color:#9a6500}
    .blog-count{font-variant-numeric:tabular-nums;font-weight:700}.blog-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px}.blog-pager nav{display:flex;align-items:center;gap:5px}.blog-pager nav a,.blog-pager nav span{display:grid;place-items:center;min-width:34px;height:34px;padding:0 9px;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:12px;text-decoration:none}.blog-pager nav span[aria-current]{border-color:var(--brand);background:var(--brand);color:#fff}
    .blog-flash{padding:12px 14px;border-radius:11px;background:#e6f8ee;color:#176a3b;font-size:12px;font-weight:700}
    .blog-danger-form{display:inline}
    @media(max-width:930px){.blog-admin-toolbar{grid-template-columns:1fr 1fr 1fr}.blog-admin-toolbar input{grid-column:span 2}}
    @media(max-width:580px){.blog-admin-toolbar{grid-template-columns:1fr 1fr}.blog-admin-toolbar input{grid-column:1/-1}.blog-admin-head h1{font-size:21px}}
</style>

<header class="page-head blog-admin-head">
    <div><h1><i class="ri-article-line"></i>Blog posts</h1><p>Create, organize, publish, and measure your beauty journal articles.</p></div>
    <a href="{{ route('admin.blog.posts.create') }}" class="button primary"><i class="ri-add-line"></i>Create post</a>
</header>
@if(session('success'))<div class="blog-flash" role="status">{{ session('success') }}</div>@endif
@if($errors->any())<div class="errors" role="alert"><strong>Please review these fields:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="card">
    <div class="toolbar">
        <div><h2 class="card-title" style="margin:0">Your content library</h2><span class="count-note">{{ number_format($posts->total()) }} articles</span></div>
        <form method="GET" action="{{ route('admin.blog.posts') }}" class="blog-admin-toolbar">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search post titles..." aria-label="Search posts">
            <select name="category" aria-label="Filter category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int)($filters['category']??0)===$category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="status" aria-label="Filter status"><option value="">All statuses</option><option value="published" @selected(($filters['status']??'')==='published')>Published</option><option value="draft" @selected(($filters['status']??'')==='draft')>Draft</option></select>
            <select name="per_page" aria-label="Rows per page">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??10)===$size)>{{ $size }} rows</option>@endforeach</select>
            <button type="submit" class="button primary"><i class="ri-filter-3-line"></i>Filter</button>
            @if(collect($filters)->filter()->isNotEmpty())<a href="{{ route('admin.blog.posts') }}" class="button">Reset</a>@endif
        </form>
    </div>
    <div class="table-wrap"><table class="blog-admin-table" data-server-pagination="true">
        <thead><tr><th>ID</th><th>Image</th><th>Post title</th><th>Categories</th><th>Author</th><th>Views</th><th>Created</th><th>Status</th><th>Operations</th></tr></thead>
        <tbody>
        @forelse($posts as $post)
            <tr>
                <td>#{{ $post->id }}</td>
                <td>@if($post->featured_image)<img src="{{ asset('storage/'.$post->featured_image) }}" alt="" style="width:48px;height:42px;object-fit:cover;border-radius:8px">@else<span class="blog-cover-placeholder"><i class="ri-image-line"></i></span>@endif</td>
                <td><div class="blog-cover-copy"><a href="{{ route('admin.blog.posts.edit',$post) }}">{{ $post->title }}</a><small>/blog/{{ $post->slug }}</small></div></td>
                <td><div class="blog-category-chips">@forelse($post->categories as $category)<span class="blog-chip">{{ $category->name }}</span>@empty<span class="count-note">Uncategorized</span>@endforelse</div></td>
                <td>{{ $post->author?->name ?? 'Admin' }}</td>
                <td class="blog-count">{{ number_format($post->views_count) }}</td>
                <td>{{ $post->created_at->format('M j, Y') }}</td>
                <td><span class="blog-status {{ $post->status }}">{{ ucfirst($post->status) }}</span></td>
                <td><div class="blog-actions">
                    <a class="blog-action" href="{{ route('admin.blog.posts.edit',$post) }}" aria-label="Edit {{ $post->title }}"><i class="ri-edit-line"></i></a>
                    @if($post->status==='published')<a class="blog-action" href="{{ route('blog.show',$post->slug) }}" target="_blank" rel="noopener" aria-label="View {{ $post->title }}"><i class="ri-external-link-line"></i></a>@endif
                    <form class="blog-danger-form" method="POST" action="{{ route('admin.blog.posts.destroy',$post) }}" data-confirm-modal data-confirm-title="Delete this post?" data-confirm-message="This post and its view history will be permanently removed.">@csrf @method('DELETE')<button class="blog-action danger" type="submit" aria-label="Delete {{ $post->title }}"><i class="ri-delete-bin-line"></i></button></form>
                </div></td>
            </tr>
        @empty<tr><td colspan="9" class="empty">No posts found. Create your first article to start your blog.</td></tr>@endforelse
        </tbody>
    </table></div>
    <div class="blog-pager"><span class="count-note">Showing {{ $posts->firstItem() ?? 0 }}–{{ $posts->lastItem() ?? 0 }} of {{ number_format($posts->total()) }} posts</span>{{ $posts->links() }}</div>
</section>
@endsection
