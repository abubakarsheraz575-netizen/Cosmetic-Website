<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostView;
use App\Models\BlogTag;
use App\Models\Setting;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogManagementController extends Controller
{
    public function posts(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);
        $posts = BlogPost::query()
            ->with(['author:id,name', 'categories:id,name', 'tags:id,name'])
            ->when(trim($filters['q'] ?? '') !== '', function ($query) use ($filters) {
                $term = trim($filters['q']);
                $query->where(fn ($query) => $query->where('title', 'like', "%{$term}%")
                    ->orWhere('slug', 'like', "%{$term}%"));
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->whereHas(
                'categories', fn ($categories) => $categories->whereKey($category)
            ))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();
        $categories = BlogCategory::orderBy('name')->get(['id', 'name']);

        return view('admin.pages.blog.posts', compact('posts', 'categories', 'filters'));
    }

    public function create(): View
    {
        return $this->postForm(new BlogPost(['status' => 'draft', 'is_indexable' => true]), 'Create post');
    }

    public function edit(BlogPost $post): View
    {
        $post->load(['categories:id', 'tags:id']);

        return $this->postForm($post, 'Edit post');
    }

    private function postForm(BlogPost $post, string $title): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        $tags = BlogTag::orderBy('name')->get();
        return view('admin.pages.blog.form', compact('post', 'title', 'categories', 'tags'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePost($request);
        $uploadedPath = $request->file('featured_image')?->store('blog', 'public');
        if ($request->hasFile('featured_image') && ! $uploadedPath) {
            throw new \RuntimeException('The featured image could not be saved. Please try again.');
        }
        $path = $uploadedPath ?: ($data['featured_image_path'] ?? null);
        $post = DB::transaction(function () use ($data, $path, $request): BlogPost {
            $post = BlogPost::create($this->postAttributes($data, $path, null, $request));
            $post->categories()->sync($data['categories'] ?? []);
            $post->tags()->sync($data['tags'] ?? []);

            return $post;
        });

        return $this->saveRedirect($request, $post, 'Blog post created.');
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $data = $this->validatePost($request, $post);
        $uploadedPath = $request->file('featured_image')?->store('blog', 'public');
        if ($request->hasFile('featured_image') && ! $uploadedPath) {
            throw new \RuntimeException('The featured image could not be saved. Please try again.');
        }
        $path = $uploadedPath ?: ($data['featured_image_path'] ?? null);
        $oldImage = $post->featured_image;
        DB::transaction(function () use ($data, $path, $post, $request): void {
            $post->update($this->postAttributes($data, $path, $post, $request));
            $post->categories()->sync($data['categories'] ?? []);
            $post->tags()->sync($data['tags'] ?? []);
        });
        if ($path && $oldImage) {
            MediaLibrary::deleteIfUnused($oldImage);
        }

        return $this->saveRedirect($request, $post, 'Blog post updated.');
    }

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        if (filled($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('blog_posts', 'slug')->ignore($post?->id)],
            'excerpt' => ['nullable', 'string', 'max:320'],
            'content' => ['required', 'string', 'max:50000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'featured_image_path' => MediaLibrary::imagePathRules(),
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_featured' => ['nullable', 'boolean'],
            'is_indexable' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url', 'max:2048'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'distinct', 'exists:blog_categories,id'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'distinct', 'exists:blog_tags,id'],
            'source_links' => ['nullable', 'array', 'max:10'],
            'source_links.*.label' => ['required_with:source_links.*.url', 'nullable', 'string', 'max:120'],
            'source_links.*.url' => ['required_with:source_links.*.label', 'nullable', 'url', 'max:2048'],
            'save_action' => ['nullable', Rule::in(['save', 'save-exit'])],
        ]);
    }

    private function postAttributes(array $data, ?string $path, ?BlogPost $post, Request $request): array
    {
        $status = $data['status'];
        $sourceLinks = collect($data['source_links'] ?? [])
            ->filter(fn ($source) => filled($source['label'] ?? null) && filled($source['url'] ?? null))
            ->values()
            ->all();

        return [
            'author_id' => $post?->author_id ?? $request->user()->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['title'], 'blog_posts', $post?->id),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured_image' => $path ?? $post?->featured_image,
            'status' => $status,
            'is_featured' => $request->boolean('is_featured'),
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'canonical_url' => $data['canonical_url'] ?? null,
            'source_links' => $sourceLinks,
            'is_indexable' => $request->boolean('is_indexable'),
            'published_at' => $status === 'published' ? ($post?->published_at ?? now()) : $post?->published_at,
        ];
    }

    private function saveRedirect(Request $request, BlogPost $post, string $message): RedirectResponse
    {
        return redirect()
            ->route($request->input('save_action') === 'save-exit' ? 'admin.blog.posts' : 'admin.blog.posts.edit', $request->input('save_action') === 'save-exit' ? [] : [$post])
            ->with('success', $message);
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $image = $post->featured_image;
        $post->delete();
        if ($image) {
            MediaLibrary::deleteIfUnused($image);
        }

        return back()->with('success', 'Blog post deleted.');
    }

    public function categories(): View
    {
        $categories = BlogCategory::withCount('posts')->orderBy('name')->paginate(20);

        return view('admin.pages.blog.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse|JsonResponse
    {
        if (filled($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:blog_categories,name'],
            'slug' => ['nullable', 'string', 'max:120', 'unique:blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $data['slug'] = $this->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['name'], 'blog_categories');
        $category = BlogCategory::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'category' => ['id' => $category->id, 'name' => $category->name],
            ], 201);
        }

        return back()->with('success', 'Blog category created.');
    }

    public function updateCategory(Request $request, BlogCategory $category): RedirectResponse
    {
        if (filled($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }
        if (! $request->has('is_active')) {
            $request->merge(['is_active' => false]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('blog_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:120', Rule::unique('blog_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['required', 'boolean'],
        ]);
        $data['slug'] = $this->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['name'], 'blog_categories', $category->id);
        $category->update($data);

        return back()->with('success', 'Blog category updated.');
    }

    public function destroyCategory(BlogCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Blog category deleted; its posts remain available in other categories.');
    }

    public function tags(): View
    {
        $tags = BlogTag::withCount('posts')->orderBy('name')->paginate(25);

        return view('admin.pages.blog.tags', compact('tags'));
    }

    public function storeTag(Request $request): RedirectResponse|JsonResponse
    {
        if (filled($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', 'unique:blog_tags,name'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:blog_tags,slug'],
        ]);
        $data['slug'] = $this->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['name'], 'blog_tags');
        $tag = BlogTag::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'tag' => ['id' => $tag->id, 'name' => $tag->name],
            ], 201);
        }

        return back()->with('success', 'Blog tag created.');
    }

    public function updateTag(Request $request, BlogTag $tag): RedirectResponse
    {
        if (filled($request->input('slug'))) {
            $request->merge(['slug' => Str::slug($request->input('slug'))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80', Rule::unique('blog_tags', 'name')->ignore($tag->id)],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('blog_tags', 'slug')->ignore($tag->id)],
        ]);
        $data['slug'] = $this->uniqueSlug(filled($data['slug'] ?? null) ? $data['slug'] : $data['name'], 'blog_tags', $tag->id);
        $tag->update($data);

        return back()->with('success', 'Blog tag updated.');
    }

    public function destroyTag(BlogTag $tag): RedirectResponse
    {
        $tag->delete();

        return back()->with('success', 'Blog tag deleted; the related posts remain.');
    }

    public function reports(): View
    {
        $since = now()->subDays(29)->startOfDay();
        $posts = BlogPost::withCount(['views as recorded_views_count', 'views as visitors_count' => fn ($query) => $query->select(DB::raw('count(distinct visitor_hash)'))])
            ->with('categories:id,name')
            ->orderByDesc('recorded_views_count')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $dailyViews = BlogPostView::query()
            ->selectRaw('DATE(viewed_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_hash) as visitors')
            ->where('viewed_at', '>=', $since)
            ->groupByRaw('DATE(viewed_at)')
            ->orderBy('day')
            ->get();
        $stats = [
            'posts' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->count(),
            'views' => BlogPostView::count(),
            'visitors' => BlogPostView::distinct('visitor_hash')->count('visitor_hash'),
        ];
        $categoryStats = BlogCategory::withCount(['posts' => fn ($query) => $query->published()])
            ->orderByDesc('posts_count')->get();

        return view('admin.pages.blog.reports', compact('posts', 'dailyViews', 'stats', 'categoryStats'));
    }

    public function seo(): View
    {
        return view('admin.pages.blog.seo', [
            'settings' => [
                'title' => Setting::get('blog_seo_title', 'Beauty Notes | Cosmetics'),
                'description' => Setting::get('blog_seo_description', 'Thoughtful skincare and beauty guides from Cosmetics.'),
                'indexable' => Setting::get('blog_seo_indexable', '1') === '1',
            ],
        ]);
    }

    public function updateSeo(Request $request): RedirectResponse
    {
        $request->merge(['indexable' => $request->boolean('indexable')]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'max:320'],
            'indexable' => ['required', 'boolean'],
        ]);
        Setting::set('blog_seo_title', $data['title']);
        Setting::set('blog_seo_description', $data['description']);
        Setting::set('blog_seo_indexable', $request->boolean('indexable') ? '1' : '0');

        return back()->with('success', 'Blog SEO settings saved.');
    }

    private function uniqueSlug(string $value, string $table, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'article';
        $slug = $base;
        $suffix = 2;

        while (DB::table($table)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
