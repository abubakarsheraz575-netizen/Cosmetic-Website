<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostView;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function createBlogTestPost(array $attributes = []): BlogPost
{
    return BlogPost::create(array_merge([
        'title' => 'A helpful skincare story',
        'slug' => 'helpful-skincare-story',
        'excerpt' => 'A short original summary.',
        'content' => "First paragraph.\n\nSecond paragraph.",
        'status' => 'published',
        'views_count' => 0,
        'is_featured' => false,
        'is_indexable' => true,
        'published_at' => now()->subDay(),
        'source_links' => [['label' => 'NHS acne guidance', 'url' => 'https://www.nhs.uk/conditions/acne/']],
    ], $attributes));
}

test('public journal filters published posts and records privacy-preserving article views', function () {
    $category = BlogCategory::create(['name' => 'Skincare', 'slug' => 'skincare']);
    $tag = BlogTag::create(['name' => 'Routine', 'slug' => 'routine']);
    $published = createBlogTestPost();
    $published->categories()->attach($category);
    $published->tags()->attach($tag);
    createBlogTestPost([
        'title' => 'An unpublished draft',
        'slug' => 'unpublished-draft',
        'status' => 'draft',
        'published_at' => null,
    ]);
    createBlogTestPost([
        'title' => 'A future story',
        'slug' => 'future-story',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('A helpful skincare story')
        ->assertDontSee('An unpublished draft')
        ->assertDontSee('A future story');

    $this->get(route('blog.category', $category->slug))
        ->assertOk()
        ->assertSee('A helpful skincare story');
    $this->get(route('blog.index', ['tag' => $tag->slug]))
        ->assertOk()
        ->assertSee('A helpful skincare story');
    $this->get(route('blog.index', ['q' => 'unmatched']))
        ->assertOk()
        ->assertSee('No published stories match this view yet.');

    $this->get(route('blog.show', $published->slug))
        ->assertOk()
        ->assertSee('Second paragraph.')
        ->assertSee('Helpful references')
        ->assertSee('Skincare');

    expect($published->fresh()->views_count)->toBe(1)
        ->and(BlogPostView::where('blog_post_id', $published->id)->count())->toBe(1)
        ->and(strlen(BlogPostView::where('blog_post_id', $published->id)->value('visitor_hash')))->toBe(64);
});

test('featured blog posts do not display the editor pick badge on the journal page', function () {
    createBlogTestPost(['is_featured' => true]);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertDontSee('blog-featured', false)
        ->assertDontSee("Editor's pick");
});

test('admin can manage blog categories, tags, posts, reports, and SEO defaults', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);
    BlogCategory::create(['name' => 'Existing Guide', 'slug' => 'existing-guide', 'is_active' => true]);

    $this->get(route('admin.blog.categories'))
        ->assertOk()
        ->assertSee('Add category')
        ->assertSee('id="blogCategoryDialog"', false)
        ->assertSee('class="blog-status-control', false)
        ->assertDontSee('Add category here');

    foreach ([
        route('admin.blog.posts'),
        route('admin.blog.categories'),
        route('admin.blog.tags'),
        route('admin.blog.reports'),
        route('admin.blog.seo'),
        route('admin.blog.posts.create'),
    ] as $url) {
        $this->get($url)->assertOk();
    }

    $this->post(route('admin.blog.categories.store'), [
        'name' => 'Ingredient Guides',
        'slug' => 'Ingredient Guides',
        'description' => 'Useful ingredient explainers.',
    ])->assertRedirect();
    $category = BlogCategory::where('slug', 'ingredient-guides')->firstOrFail();

    $this->post(route('admin.blog.tags.store'), ['name' => 'Daily routine', 'slug' => 'Daily routine'])
        ->assertRedirect();
    $tag = BlogTag::where('slug', 'daily-routine')->firstOrFail();

    $this->post(route('admin.blog.posts.store'), [
        'title' => 'A guide to gentle routines',
        'slug' => 'A guide to gentle routines',
        'excerpt' => 'Small, steady steps.',
        'content' => 'An original, practical skincare article.',
        'status' => 'published',
        'categories' => [$category->id],
        'tags' => [$tag->id],
        'source_links' => [['label' => 'NHS acne guidance', 'url' => 'https://www.nhs.uk/conditions/acne/']],
        'is_indexable' => '1',
        'is_featured' => '1',
        'save_action' => 'save-exit',
    ])->assertRedirect(route('admin.blog.posts'));

    $post = BlogPost::where('slug', 'a-guide-to-gentle-routines')->firstOrFail();
    expect($post->categories->modelKeys())->toContain($category->id)
        ->and($post->tags->modelKeys())->toContain($tag->id)
        ->and($post->source_links[0]['label'])->toBe('NHS acne guidance');

    $this->get(route('blog.show', $post->slug))->assertOk()->assertSee('A guide to gentle routines');
    $this->get(route('admin.blog.reports'))->assertOk()->assertSee('Top posts by views');
    $this->patch(route('admin.blog.seo.update'), [
        'title' => 'Cosmetics Beauty Notes',
        'description' => 'Original skincare and beauty stories.',
    ])->assertRedirect();
    $this->get(route('blog.index'))->assertOk()
        ->assertSee('<title>Cosmetics Beauty Notes</title>', false)
        ->assertSee('noindex,nofollow');

    $this->delete(route('admin.blog.posts.destroy', $post))->assertRedirect();
    expect(BlogPost::whereKey($post->id)->exists())->toBeFalse();
});

test('admin blog posts can select a featured image from the media library', function () {
    Storage::fake('public');
    Storage::disk('public')->put('media/blog-cover.png', 'image-data');
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->post(route('admin.blog.posts.store'), [
        'title' => 'Media library blog image',
        'slug' => 'media-library-blog-image',
        'excerpt' => 'A media-backed blog image.',
        'content' => 'Original editorial content.',
        'status' => 'draft',
        'featured_image_path' => 'media/blog-cover.png',
        'save_action' => 'save-exit',
    ])->assertRedirect(route('admin.blog.posts'));

    expect(BlogPost::where('slug', 'media-library-blog-image')->value('featured_image'))
        ->toBe('media/blog-cover.png');
});

test('blog post editor can create and immediately select categories and tags with ajax', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin);

    $this->get(route('admin.blog.posts.create'))
        ->assertOk()
        ->assertSee('data-create-taxonomy="category"', false)
        ->assertSee('data-create-taxonomy="tag"', false)
        ->assertSee('id="blogCategoryChoices"', false)
        ->assertSee('id="blogTagChoices"', false);

    $categoryResponse = $this->postJson(route('admin.blog.categories.store'), [
        'name' => 'Texture Guides',
    ])->assertCreated()->assertJsonPath('category.name', 'Texture Guides');
    $tagResponse = $this->postJson(route('admin.blog.tags.store'), [
        'name' => 'Sensitive skin',
    ])->assertCreated()->assertJsonPath('tag.name', 'Sensitive skin');

    $category = BlogCategory::findOrFail($categoryResponse->json('category.id'));
    $tag = BlogTag::findOrFail($tagResponse->json('tag.id'));
    $this->post(route('admin.blog.posts.store'), [
        'title' => 'Freshly categorized article',
        'content' => 'Original content for the new category and tag.',
        'status' => 'draft',
        'categories' => [$category->id],
        'tags' => [$tag->id],
        'save_action' => 'save-exit',
    ])->assertRedirect(route('admin.blog.posts'));

    $post = BlogPost::where('slug', 'freshly-categorized-article')->firstOrFail();
    expect($post->categories->modelKeys())->toContain($category->id)
        ->and($post->tags->modelKeys())->toContain($tag->id);
});

test('blog reports display live recorded views and distinct visitors per post', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $post = createBlogTestPost(['views_count' => 2]);
    BlogPostView::create([
        'blog_post_id' => $post->id,
        'visitor_hash' => str_repeat('a', 64),
        'viewed_at' => now(),
    ]);
    BlogPostView::create([
        'blog_post_id' => $post->id,
        'visitor_hash' => str_repeat('b', 64),
        'viewed_at' => now(),
    ]);
    BlogPostView::create([
        'blog_post_id' => $post->id,
        'visitor_hash' => str_repeat('a', 64),
        'viewed_at' => now(),
    ]);

    $this->actingAs($admin)->get(route('admin.blog.reports'))
        ->assertOk()
        ->assertSee('Article page views')
        ->assertSee('data-post-id="'.$post->id.'" data-views="3" data-visitors="2"', false)
        ->assertSee('>3</td><td>2</td>', false);
});
