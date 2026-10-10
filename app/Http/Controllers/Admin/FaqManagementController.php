<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FaqManagementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'integer', 'exists:faq_categories,id'],
            'status' => ['nullable', Rule::in(['published', 'draft'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);
        $faqs = Faq::with('category')
            ->when(trim($filters['q'] ?? '') !== '', fn ($query) => $query->where('question', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('faq_category_id', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('faq_category_id')->orderBy('sort_order')->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();

        return view('admin.pages.faqs.index', [
            'faqs' => $faqs,
            'categories' => FaqCategory::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $faq = new Faq();
        if ($request->filled('category')) {
            $faq->faq_category_id = $request->integer('category');
        }

        return view('admin.pages.faqs.form', ['faq' => $faq, 'categories' => FaqCategory::orderBy('name')->get()]);
    }

    public function edit(Faq $faq): View
    {
        return view('admin.pages.faqs.form', ['faq' => $faq, 'categories' => FaqCategory::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validatedFaq($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validatedFaq($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    private function validatedFaq(Request $request): array
    {
        return $request->validate([
            'faq_category_id' => ['required', 'integer', 'exists:faq_categories,id'],
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'source_url' => ['nullable', 'url:https', 'max:2048'],
            'status' => ['required', Rule::in(['published', 'draft'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'FAQ deleted.');
    }

    public function categories(): View
    {
        return view('admin.pages.faqs.categories', [
            'categories' => FaqCategory::withCount('faqs')->orderBy('sort_order')->orderBy('name')->paginate(10),
        ]);
    }

    public function createCategory(): View
    {
        return view('admin.pages.faqs.category-form', ['category' => new FaqCategory()]);
    }

    public function editCategory(FaqCategory $faqCategory): View
    {
        return view('admin.pages.faqs.category-form', ['category' => $faqCategory]);
    }

    public function showCategory(FaqCategory $faqCategory): View
    {
        return view('admin.pages.faqs.category-show', [
            'category' => $faqCategory,
            'faqs' => $faqCategory->faqs()->orderBy('sort_order')->orderByDesc('id')->paginate(10),
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:faq_categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);
        $data['slug'] = $this->uniqueCategorySlug($data['name']);
        FaqCategory::create($data);

        return redirect()->route('admin.faqs.categories')->with('success', 'FAQ category created.');
    }

    public function updateCategory(Request $request, FaqCategory $faqCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('faq_categories', 'name')->ignore($faqCategory->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['slug'] = $this->uniqueCategorySlug($data['name'], $faqCategory->id);
        $faqCategory->update($data);

        return redirect()->route('admin.faqs.categories')->with('success', 'FAQ category updated.');
    }

    private function uniqueCategorySlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;
        while (FaqCategory::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    public function destroyCategory(FaqCategory $faqCategory): RedirectResponse
    {
        $faqCategory->delete();

        return redirect()->route('admin.faqs.categories')->with('success', 'FAQ category and its questions deleted.');
    }
}
