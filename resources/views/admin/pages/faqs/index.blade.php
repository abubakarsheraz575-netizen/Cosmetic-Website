@extends('admin.layouts.ecommerce')
@section('title', 'FAQs')
@section('content')
<style>
    .faq-admin{--ink:#173640;--muted:#738392;--line:#e4ebf0;--accent:#2167c9;color:var(--ink);min-width:0}
    .faq-admin-head{display:flex;justify-content:space-between;align-items:end;gap:15px;flex-wrap:wrap;margin-bottom:18px}.faq-admin-head h1{margin:0;font-size:26px}.faq-admin-head p{margin:5px 0 0;color:var(--muted);font-size:12px}
    .faq-primary{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:9px;background:var(--accent);color:#fff;font-size:11px;font-weight:800;text-decoration:none}
    .faq-admin-panel{min-width:0;overflow:hidden;padding:20px;border:1px solid var(--line);border-radius:17px;background:#fff;box-shadow:0 10px 32px rgba(25,54,71,.055)}
    .faq-admin-toolbar{display:grid;grid-template-columns:minmax(130px,auto) minmax(0,1fr);align-items:center;gap:18px;margin-bottom:16px}.faq-admin-count{color:#49606e;font-size:12px;font-weight:700}.faq-admin-filter{display:grid;grid-template-columns:minmax(160px,1.25fr) repeat(3,minmax(125px,1fr)) auto;align-items:center;gap:8px;min-width:0}.faq-admin-filter input,.faq-admin-filter select,.faq-admin-filter .site-select-trigger{width:100%;min-width:0;min-height:42px;padding:9px 11px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--ink);font:inherit;font-size:11px}.faq-admin-filter .site-select-wrap{width:100%;min-width:0}.faq-admin-filter button{min-height:42px;padding:0 14px;border:0;border-radius:10px;background:#173e57;color:#fff;font-size:11px;font-weight:800;white-space:nowrap;cursor:pointer}
    .faq-admin-table{width:100%;min-width:760px;border-collapse:collapse}.faq-admin-table th{padding:11px 10px;background:#f5f8fa;color:#778695;text-align:left;font-size:9px;letter-spacing:.07em;text-transform:uppercase}.faq-admin-table td{padding:12px 10px;border-bottom:1px solid #edf1f4;color:#384f61;font-size:11px}.faq-question-link,.faq-cat-link{color:#2267ba;font-weight:700;text-decoration:none}.faq-question-link:hover,.faq-cat-link:hover{text-decoration:underline}.faq-status{display:inline-flex;padding:5px 9px;border-radius:20px;background:#edf2f4;color:#617383;font-size:9px;font-weight:800}.faq-status.published{background:#e8f6ee;color:#14814e}.faq-actions{display:flex;gap:6px;white-space:nowrap}.faq-action{display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 9px;border:1px solid var(--line);border-radius:8px;background:#fff;color:#236a80;font-size:10px;font-weight:700;text-decoration:none;cursor:pointer}.faq-action.danger{border-color:#f2dddd;background:#fff7f7;color:#b23b43}
    .faq-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;color:var(--muted);font-size:10px}.faq-pager nav{display:flex;gap:5px}.faq-pager a,.faq-pager span{display:grid;min-width:32px;height:32px;place-items:center;padding:0 8px;border:1px solid var(--line);border-radius:8px;color:var(--ink);text-decoration:none}.faq-pager [aria-current]{border-color:var(--accent);background:var(--accent);color:#fff}.faq-empty{text-align:center;padding:32px;color:var(--muted)}
    @media(max-width:1050px){.faq-admin-toolbar{grid-template-columns:1fr;gap:10px}.faq-admin-filter{grid-template-columns:repeat(2,minmax(0,1fr))}.faq-admin-filter input{grid-column:1/-1}}
    @media(max-width:640px){.faq-admin-panel{padding:13px}.faq-admin-toolbar{align-items:stretch}.faq-admin-filter{grid-template-columns:1fr}.faq-admin-filter input{grid-column:auto}.faq-admin-filter>*{min-width:0;max-width:100%}.faq-admin-filter button{width:100%}}
</style>
<main class="faq-admin">
    <header class="faq-admin-head"><div><h1>Frequently asked questions</h1><p>Manage published answers and organize them into customer-friendly topics.</p></div><a class="faq-primary" href="{{ route('admin.faqs.create') }}"><i class="ri-add-line"></i>Create FAQ</a></header>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <section class="faq-admin-panel">
        <div class="faq-admin-toolbar"><strong class="faq-admin-count"><i class="ri-question-answer-line"></i> {{ number_format($faqs->total()) }} questions</strong><form class="faq-admin-filter" method="GET" action="{{ route('admin.faqs.index') }}">
            <input type="search" name="q" value="{{ $filters['q']??'' }}" placeholder="Search questions..." aria-label="Search questions">
            <select name="category" aria-label="Filter by category"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)($filters['category']??'')===(string)$category->id)>{{ $category->name }}</option>@endforeach</select>
            <select name="status" aria-label="Filter status"><option value="">All statuses</option><option value="published" @selected(($filters['status']??'')==='published')>Published</option><option value="draft" @selected(($filters['status']??'')==='draft')>Draft</option></select>
            <select name="per_page" aria-label="Rows per page">@foreach([10,25,50] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page']??10)===$size)>{{ $size }} per page</option>@endforeach</select>
            <button type="submit"><i class="ri-filter-3-line"></i> Apply filters</button>
        </form></div>
        <div class="table-wrap"><table class="faq-admin-table" data-server-pagination="true"><thead><tr><th>ID</th><th>Question</th><th>Category</th><th>Created</th><th>Status</th><th>Operations</th></tr></thead><tbody>
            @forelse($faqs as $faq)<tr><td>{{ $faq->id }}</td><td><a class="faq-question-link" href="{{ route('admin.faqs.edit',$faq) }}">{{ $faq->question }}</a></td><td><a class="faq-cat-link" href="{{ route('admin.faqs.categories.show',$faq->category) }}">{{ $faq->category->name }}</a></td><td>{{ $faq->created_at->format('M j, Y') }}</td><td><span class="faq-status {{ $faq->status }}">{{ ucfirst($faq->status) }}</span></td><td><div class="faq-actions"><a class="faq-action" href="{{ route('admin.faqs.edit',$faq) }}"><i class="ri-edit-line"></i>Edit</a><form method="POST" action="{{ route('admin.faqs.destroy',$faq) }}" data-confirm-modal data-confirm-title="Delete this FAQ?" data-confirm-message="This question will be permanently removed from the FAQ list.">@csrf @method('DELETE')<button class="faq-action danger" type="submit"><i class="ri-delete-bin-line"></i>Delete</button></form></div></td></tr>
            @empty<tr><td colspan="6" class="faq-empty">No questions match these filters.</td></tr>@endforelse
        </tbody></table></div>
        <footer class="faq-pager"><span>Showing {{ $faqs->firstItem()??0 }}–{{ $faqs->lastItem()??0 }} of {{ number_format($faqs->total()) }}</span><nav aria-label="FAQ pages">
            @if($faqs->onFirstPage())<span aria-disabled="true">‹</span>@else<a href="{{ $faqs->previousPageUrl() }}">‹</a>@endif
            @foreach($faqs->getUrlRange(max(1,$faqs->currentPage()-2),min($faqs->lastPage(),$faqs->currentPage()+2)) as $page=>$url)
                @if($page===$faqs->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif
            @endforeach
            @if($faqs->hasMorePages())<a href="{{ $faqs->nextPageUrl() }}">›</a>@else<span aria-disabled="true">›</span>@endif
        </nav></footer>
    </section>
</main>
@endsection
