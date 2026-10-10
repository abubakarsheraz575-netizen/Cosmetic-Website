@extends('admin.layouts.ecommerce')
@section('title', 'FAQ categories')
@section('content')
<style>
    .faq-cats-page{color:#173640;min-width:0}.faq-cats-head{display:flex;justify-content:space-between;align-items:end;gap:15px;flex-wrap:wrap;margin-bottom:18px}.faq-cats-head h1{margin:0;font-size:26px}.faq-cats-head p{margin:5px 0 0;color:#738392;font-size:12px}
    .faq-cats-head-actions{display:flex;gap:8px;flex-wrap:wrap}.faq-cats-button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:40px;padding:0 14px;border:1px solid #e2eaf0;border-radius:10px;background:#fff;color:#315567;font-size:11px;font-weight:800;text-decoration:none}.faq-cats-button.primary{border-color:#2167c9;background:#2167c9;color:#fff}
    .faq-cats-card{min-width:0;padding:20px;border:1px solid #e4ebf0;border-radius:17px;background:#fff;box-shadow:0 10px 32px rgba(25,54,71,.055)}.faq-cats-card-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:13px}.faq-cats-card h2{margin:0;font-size:14px}.faq-cats-total{padding:7px 10px;border-radius:20px;background:#f1f6f9;color:#536b7a;font-size:10px;font-weight:700}
    .faq-cats-table{width:100%;min-width:650px;border-collapse:collapse}.faq-cats-table th{padding:11px;background:#f5f8fa;color:#778695;text-align:left;font-size:9px;letter-spacing:.07em;text-transform:uppercase}.faq-cats-table td{padding:13px 10px;border-bottom:1px solid #edf1f4;font-size:11px}.faq-cats-link{color:#2267ba;font-weight:800;text-decoration:none}.faq-cats-desc{display:block;max-width:380px;margin-top:4px;color:#7b8a95;font-size:10px;line-height:1.5}.faq-cats-status{padding:5px 8px;border-radius:15px;background:#e9f6ed;color:#188052;font-size:9px;font-weight:800}.faq-cats-status.off{background:#f1f2f3;color:#71808b}.faq-cats-actions{display:flex;gap:6px;white-space:nowrap}.faq-cats-actions a,.faq-cats-actions button{display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 9px;border:1px solid #e3eaf0;border-radius:8px;background:white;color:#28717f;font-size:10px;font-weight:700;text-decoration:none;cursor:pointer}.faq-cats-actions button{color:#b13c43;border-color:#f2dddd;background:#fff7f7}
    .faq-cats-pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:14px;color:#738392;font-size:10px}.faq-cats-pager nav{display:flex;gap:5px}.faq-cats-pager a,.faq-cats-pager span{display:grid;min-width:32px;height:32px;place-items:center;padding:0 8px;border:1px solid #e4ebf0;border-radius:8px;color:#173640;text-decoration:none}.faq-cats-pager [aria-current]{border-color:#2167c9;background:#2167c9;color:#fff}
    @media(max-width:600px){.faq-cats-card{padding:13px}.faq-cats-head-actions{width:100%}.faq-cats-head-actions>*{flex:1}}
</style>
<main class="faq-cats-page">
    <header class="faq-cats-head">
        <div><h1>FAQ categories</h1><p>Group customer answers into clear topics and manage each category’s questions.</p></div>
        <div class="faq-cats-head-actions">
            <a class="faq-cats-button" href="{{ route('admin.faqs.index') }}"><i class="ri-question-answer-line"></i>Manage questions</a>
            <a class="faq-cats-button primary" href="{{ route('admin.faqs.categories.create') }}"><i class="ri-add-line"></i>Add category</a>
        </div>
    </header>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    <section class="faq-cats-card">
        <div class="faq-cats-card-head"><h2>All categories</h2><span class="faq-cats-total">{{ number_format($categories->total()) }} categories</span></div>
        <div class="table-wrap"><table class="faq-cats-table" data-server-pagination="true"><thead><tr><th>ID</th><th>Name</th><th>Questions</th><th>Status</th><th>Operations</th></tr></thead><tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td><a class="faq-cats-link" href="{{ route('admin.faqs.categories.show',$category) }}">{{ $category->name }}</a><span class="faq-cats-desc">{{ $category->description ?: 'No description added.' }}</span></td>
                    <td>{{ number_format($category->faqs_count) }}</td>
                    <td><span class="faq-cats-status {{ $category->is_active?'':'off' }}">{{ $category->is_active?'Published':'Hidden' }}</span></td>
                    <td><div class="faq-cats-actions"><a href="{{ route('admin.faqs.categories.edit',$category) }}"><i class="ri-edit-line"></i>Edit</a><form method="POST" action="{{ route('admin.faqs.categories.destroy',$category) }}" data-confirm-modal data-confirm-title="Delete this category?" data-confirm-message="Deleting this category will also remove its FAQ questions. This cannot be undone.">@csrf @method('DELETE')<button type="submit"><i class="ri-delete-bin-line"></i>Delete</button></form></div></td>
                </tr>
            @empty<tr><td colspan="5" style="padding:32px;text-align:center;color:#738392">No FAQ categories yet. Add a category to organize your questions.</td></tr>@endforelse
        </tbody></table></div>
        <footer class="faq-cats-pager"><span>Showing {{ $categories->firstItem()??0 }}–{{ $categories->lastItem()??0 }} of {{ number_format($categories->total()) }}</span><nav aria-label="Category pages">
            @if($categories->onFirstPage())<span aria-disabled="true">‹</span>@else<a href="{{ $categories->previousPageUrl() }}">‹</a>@endif
            @foreach($categories->getUrlRange(max(1,$categories->currentPage()-2),min($categories->lastPage(),$categories->currentPage()+2)) as $page=>$url)@if($page===$categories->currentPage())<span aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}">{{ $page }}</a>@endif @endforeach
            @if($categories->hasMorePages())<a href="{{ $categories->nextPageUrl() }}">›</a>@else<span aria-disabled="true">›</span>@endif
        </nav></footer>
    </section>
</main>
@endsection
