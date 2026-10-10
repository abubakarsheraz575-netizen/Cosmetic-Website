<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Support\PageHeaders;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return $this->render();
    }

    public function category(FaqCategory $faqCategory): View
    {
        abort_unless($faqCategory->is_active, 404);

        return $this->render($faqCategory);
    }

    private function render(?FaqCategory $selectedCategory = null): View
    {
        $categories = FaqCategory::active()
            ->with(['faqs' => fn ($query) => $query->published()->orderBy('sort_order')->orderBy('id')])
            ->withCount(['faqs' => fn ($query) => $query->published()])
            ->orderBy('sort_order')->orderBy('name')->get()
            ->filter(fn ($category) => $category->faqs->isNotEmpty());

        $faqs = $selectedCategory
            ? $selectedCategory->faqs()->published()->orderBy('sort_order')->orderBy('id')->get()
            : collect();

        return view('front-end.pages.faqs', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
            'faqs' => $faqs,
            'pageHeader' => PageHeaders::for('faqs'),
        ]);
    }
}
