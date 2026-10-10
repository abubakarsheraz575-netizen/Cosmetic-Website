@extends('admin.layouts.ecommerce')
@section('title', $category->exists ? 'Edit FAQ category' : 'Add FAQ category')
@section('content')
<style>
    .faq-category-form-page{width:100%;min-width:0;color:#173640}.faq-category-form-head{display:flex;justify-content:space-between;align-items:end;gap:14px;flex-wrap:wrap;margin-bottom:18px}.faq-category-form-head h1{margin:0;font-size:26px}.faq-category-form-head p{margin:5px 0 0;color:#738392;font-size:12px}.faq-category-form-head a{display:inline-flex;align-items:center;gap:7px;min-height:40px;padding:0 13px;border:1px solid #e2eaf0;border-radius:10px;background:#fff;color:#315567;font-size:11px;font-weight:800;text-decoration:none}
    .faq-category-form-card{width:100%;padding:clamp(18px,3vw,30px);border:1px solid #e4ebf0;border-radius:17px;background:#fff;box-shadow:0 10px 32px rgba(25,54,71,.055)}.faq-category-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.faq-category-field{display:grid;align-content:start;gap:7px;min-width:0}.faq-category-field.full{grid-column:1/-1}.faq-category-field label{color:#334f5d;font-size:11px;font-weight:800}.faq-category-field input,.faq-category-field textarea,.faq-category-field select{width:100%;min-height:46px;padding:11px 13px;border:1px solid #dfe7ec;border-radius:10px;background:#fff;color:#173640;font:inherit;font-size:13px}.faq-category-field textarea{min-height:135px;resize:vertical;line-height:1.6}.faq-category-help,.faq-category-error{font-size:11px}.faq-category-help{color:#788995}.faq-category-error{color:#b53242}.faq-category-form-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:22px;padding-top:17px;border-top:1px solid #edf1f4}.faq-category-form-actions a,.faq-category-form-actions button{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;padding:0 16px;border:1px solid #e4ebf0;border-radius:10px;background:#fff;color:#4a6170;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer}.faq-category-form-actions button{border-color:#2167c9;background:#2167c9;color:white}
    @media(max-width:650px){.faq-category-form-grid{grid-template-columns:1fr}.faq-category-field.full{grid-column:auto}.faq-category-form-actions>*{flex:1}}
</style>
<main class="faq-category-form-page">
    <header class="faq-category-form-head">
        <div><h1>{{ $category->exists ? 'Edit category' : 'Add a category' }}</h1><p>{{ $category->exists ? 'Update this topic and its storefront visibility.' : 'Create a topic to organize related customer questions.' }}</p></div>
        <a href="{{ route('admin.faqs.categories') }}"><i class="ri-arrow-left-line"></i>Back to categories</a>
    </header>
    @if($errors->any())<div class="errors" role="alert">Please review the highlighted fields and try again.</div>@endif
    <form class="faq-category-form-card" method="POST" action="{{ $category->exists ? route('admin.faqs.categories.update',$category) : route('admin.faqs.categories.store') }}">
        @csrf @if($category->exists)@method('PATCH')@endif
        <div class="faq-category-form-grid">
            <div class="faq-category-field full"><label for="faqCategoryName">Category name *</label><input id="faqCategoryName" name="name" required maxlength="100" value="{{ old('name',$category->name) }}" autocomplete="off">@error('name')<span class="faq-category-error">{{ $message }}</span>@enderror</div>
            <div class="faq-category-field full"><label for="faqCategoryDescription">Description</label><textarea id="faqCategoryDescription" name="description" maxlength="500" placeholder="Briefly explain the questions grouped here.">{{ old('description',$category->description) }}</textarea><span class="faq-category-help">This description is for admin organization and can be up to 500 characters.</span>@error('description')<span class="faq-category-error">{{ $message }}</span>@enderror</div>
            <div class="faq-category-field"><label for="faqCategoryOrder">Display order</label><input id="faqCategoryOrder" type="number" name="sort_order" min="0" max="100000" required value="{{ old('sort_order',$category->sort_order ?? 0) }}">@error('sort_order')<span class="faq-category-error">{{ $message }}</span>@enderror</div>
            @if($category->exists)<div class="faq-category-field"><label for="faqCategoryVisibility">Visibility</label><select id="faqCategoryVisibility" name="is_active"><option value="1" @selected((string)old('is_active',(int)$category->is_active)==='1')>Visible to customers</option><option value="0" @selected((string)old('is_active',(int)$category->is_active)==='0')>Hidden</option></select>@error('is_active')<span class="faq-category-error">{{ $message }}</span>@enderror</div>@endif
        </div>
        <footer class="faq-category-form-actions"><a href="{{ route('admin.faqs.categories') }}">Cancel</a><button type="submit"><i class="ri-save-line"></i>{{ $category->exists ? 'Save changes' : 'Create category' }}</button></footer>
    </form>
</main>
@endsection
