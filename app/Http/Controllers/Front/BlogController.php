<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostView;
use App\Models\BlogTag;
use App\Models\Setting;
use App\Support\PageHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request, ?string $category = null): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'tag' => ['nullable', 'string', 'max:100'],
        ]);
        $selectedCategory = $category
            ? BlogCategory::where('slug', $category)->where('is_active', true)->firstOrFail()
            : null;
        $posts = BlogPost::published()
            ->with(['author:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->when($selectedCategory, fn ($query) => $query->whereHas(
                'categories', fn ($categories) => $categories->whereKey($selectedCategory->id)
            ))
            ->when(trim($filters['q'] ?? '') !== '', function ($query) use ($filters) {
                $term = trim($filters['q']);
                $query->where(fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('content', 'like', "%{$term}%"));
            })
            ->when($filters['tag'] ?? null, fn ($query, $slug) => $query->whereHas(
                'tags', fn ($tags) => $tags->where('slug', $slug)
            ))
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();
        $sidebarCategories = BlogCategory::where('is_active', true)
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')->get();
        $sidebarTags = BlogTag::whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')->get();
        $latestPosts = BlogPost::published()->with('categories:id,name,slug')
            ->latest('published_at')->take(4)->get();
        $settings = $this->seoSettings();
        $metaTitle = $selectedCategory
            ? $selectedCategory->name.' · '.$settings['title']
            : $settings['title'];
        $seoMeta = [
            'title' => $metaTitle,
            'description' => $selectedCategory?->description ?: $settings['description'],
            'canonical' => $selectedCategory ? route('blog.category', $selectedCategory->slug) : route('blog.index'),
            'indexable' => $settings['indexable'],
        ];
        $pageHeader = PageHeaders::for('blog');

        return view('front-end.pages.blog.index', compact(
            'posts', 'selectedCategory', 'sidebarCategories', 'sidebarTags', 'latestPosts', 'seoMeta', 'filters', 'pageHeader'
        ));
    }

    public function show(Request $request, string $slug): View
    {
        $post = BlogPost::published()
            ->with(['author:id,name', 'categories:id,name,slug', 'tags:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();
        $visitorHash = hash_hmac('sha256', $request->session()->getId(), (string) config('app.key'));
        DB::transaction(function () use ($post, $visitorHash): void {
            BlogPostView::create([
                'blog_post_id' => $post->id,
                'visitor_hash' => $visitorHash,
                'viewed_at' => now(),
            ]);
            BlogPost::whereKey($post->id)->increment('views_count');
        });
        $post->views_count++;

        $sidebarCategories = BlogCategory::where('is_active', true)
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')->get();
        $sidebarTags = BlogTag::whereHas('posts', fn ($query) => $query->published())
            ->withCount(['posts' => fn ($query) => $query->published()])
            ->orderBy('name')->get();
        $latestPosts = BlogPost::published()->where('id', '!=', $post->id)
            ->with('categories:id,name,slug')->latest('published_at')->take(4)->get();
        $relatedPosts = BlogPost::published()->where('id', '!=', $post->id)
            ->whereHas('categories', fn ($query) => $query->whereIn('blog_categories.id', $post->categories->modelKeys()))
            ->with('categories:id,name,slug')->latest('published_at')->take(3)->get();
        if ($relatedPosts->isEmpty()) {
            $relatedPosts = $latestPosts->take(3);
        }
        $settings = $this->seoSettings();
        $seoMeta = [
            'title' => $post->meta_title ?: $post->title.' · '.$settings['title'],
            'description' => $post->meta_description ?: $post->excerpt ?: $settings['description'],
            'canonical' => $post->canonical_url ?: route('blog.show', $post->slug),
            'indexable' => $settings['indexable'] && $post->is_indexable,
        ];
        $pageHeader = PageHeaders::for('blog');

        return view('front-end.pages.blog.show', compact(
            'post', 'sidebarCategories', 'sidebarTags', 'latestPosts', 'relatedPosts', 'seoMeta', 'pageHeader'
        ));
    }

    private function seoSettings(): array
    {
        return [
            'title' => Setting::get('blog_seo_title', 'Beauty Notes | Cosmetics'),
            'description' => Setting::get('blog_seo_description', 'Thoughtful skincare and beauty guides from Cosmetics.'),
            'indexable' => Setting::get('blog_seo_indexable', '1') === '1',
        ];
    }
}
