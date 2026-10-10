<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class BlogContentSeeder extends Seeder
{
    public function run(): void
    {
        $skincare = BlogCategory::updateOrCreate(
            ['slug' => 'skincare'],
            ['name' => 'Skincare', 'description' => 'Practical notes for building a thoughtful skincare routine.', 'is_active' => true],
        );
        $guides = BlogCategory::updateOrCreate(
            ['slug' => 'ingredient-guides'],
            ['name' => 'Ingredient Guides', 'description' => 'Clear, careful explainers about common skincare ingredients.', 'is_active' => true],
        );
        $sunCare = BlogCategory::updateOrCreate(
            ['slug' => 'sun-care'],
            ['name' => 'Sun Care', 'description' => 'Everyday guidance for protecting skin from the sun.', 'is_active' => true],
        );

        $routineTag = BlogTag::updateOrCreate(['slug' => 'daily-routine'], ['name' => 'Daily routine']);
        $acneTag = BlogTag::updateOrCreate(['slug' => 'blemish-care'], ['name' => 'Blemish care']);
        $spfTag = BlogTag::updateOrCreate(['slug' => 'sun-protection'], ['name' => 'Sun protection']);
        $hydrationTag = BlogTag::updateOrCreate(['slug' => 'skin-basics'], ['name' => 'Skin basics']);

        $adminId = User::where('role', 'admin')->value('id');
        $imagePaths = ProductImage::whereNotNull('path')
            ->whereHas('product', function ($query) {
                $query->where(function ($products) {
                    $products->where('name', 'like', '%skin%')
                        ->orWhere('name', 'like', '%serum%')
                        ->orWhere('name', 'like', '%cream%')
                        ->orWhere('name', 'like', '%sun%')
                        ->orWhere('name', 'like', '%moistur%');
                });
            })
            ->pluck('path')
            ->filter(fn ($path) => Storage::disk('public')->exists($path))
            ->unique()
            ->values();
        if ($imagePaths->isEmpty()) {
            $imagePaths = ProductImage::whereNotNull('path')
                ->pluck('path')
                ->filter(fn ($path) => Storage::disk('public')->exists($path))
                ->unique()
                ->values();
        }

        $articles = [
            [
                'slug' => 'building-a-simple-skincare-routine',
                'title' => 'A simple skincare routine is a good place to start',
                'excerpt' => 'A gentle cleanse, a suitable moisturiser, and daytime sun protection can make a routine easier to follow.',
                'content' => "Good skincare does not need to begin with a crowded shelf. Start by noticing what your skin needs, then introduce products slowly so it is easier to tell what works for you.\n\nA gentle cleanser can remove everyday dirt and makeup without leaving skin feeling uncomfortable. Follow with a moisturiser that suits your skin. In the morning, use a broad-spectrum sunscreen and follow its label directions; reapply as directed, especially after swimming or sweating.\n\nGive a new routine time and avoid adding several unfamiliar products at once. If a product causes persistent irritation, stop using it. For ongoing skin concerns or symptoms that worry you, a qualified healthcare professional can offer advice tailored to you.",
                'category' => $skincare,
                'tags' => [$routineTag, $hydrationTag],
                'featured' => true,
                'sources' => [
                    ['label' => 'NHS: Acne', 'url' => 'https://www.nhs.uk/conditions/acne/'],
                    ['label' => 'NHS: Sunscreen and sun safety', 'url' => 'https://www.nhs.uk/live-well/seasonal-health/sunscreen-and-sun-safety/'],
                ],
            ],
            [
                'slug' => 'understanding-acne-and-blemish-care',
                'title' => 'Understanding acne: gentle habits and when to seek help',
                'excerpt' => 'Acne is common and can affect people in different ways. A kind, consistent approach is more useful than harsh scrubbing.',
                'content' => "Acne can show up as blackheads, whiteheads, spots, or deeper painful bumps. It is common, and it is not a sign that someone is unclean. Scrubbing hard or repeatedly changing products may irritate skin rather than help.\n\nTry washing affected areas gently and choose products that feel comfortable on your skin. Follow product instructions, introduce changes one at a time, and give a routine a chance to work. Avoid picking spots, which can make irritation and marks worse.\n\nIf acne is severe, painful, leaving scars, or affecting how you feel, speak with a healthcare professional. There are treatment options, and getting advice is a sensible next step—not a personal failing.",
                'category' => $guides,
                'tags' => [$acneTag, $routineTag],
                'featured' => false,
                'sources' => [
                    ['label' => 'NHS: Acne symptoms and treatment', 'url' => 'https://www.nhs.uk/conditions/acne/'],
                ],
            ],
            [
                'slug' => 'everyday-sun-protection-basics',
                'title' => 'Everyday sun protection: small habits that matter',
                'excerpt' => 'Shade, clothing, and sunscreen work together as practical ways to reduce sun exposure.',
                'content' => "Sun protection is useful beyond holidays and hot weather. UV exposure can happen on bright and cloudy days, so make protection part of the routine when you will be outdoors.\n\nSeek shade when the sun is strongest, cover skin with suitable clothing, and use sunscreen on exposed areas. Follow the product label for how much to apply and how often to reapply; reapply after swimming or sweating as directed. Sunscreen works alongside shade and clothing, not as a reason to stay out longer.\n\nTake extra care with children and anyone who burns easily. If you notice a new or changing skin mark, seek medical advice. These general tips do not replace advice from a healthcare professional.",
                'category' => $sunCare,
                'tags' => [$spfTag, $routineTag],
                'featured' => false,
                'sources' => [
                    ['label' => 'NHS: Sunscreen and sun safety', 'url' => 'https://www.nhs.uk/live-well/seasonal-health/sunscreen-and-sun-safety/'],
                ],
            ],
        ];

        foreach ($articles as $index => $article) {
            $imagePath = $imagePaths->isEmpty()
                ? null
                : $imagePaths->get($index % $imagePaths->count());
            $post = BlogPost::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'author_id' => $adminId,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'featured_image' => $imagePath,
                    'status' => 'published',
                    'is_featured' => $article['featured'],
                    'meta_title' => $article['title'].' | Cosmetics Beauty Journal',
                    'meta_description' => $article['excerpt'],
                    'source_links' => $article['sources'],
                    'is_indexable' => true,
                    'published_at' => now()->subDays(4),
                ],
            );
            if ($post->featured_image !== $imagePath) {
                $post->update(['featured_image' => $imagePath]);
            }
            $post->categories()->syncWithoutDetaching([$article['category']->id]);
            $post->tags()->syncWithoutDetaching(collect($article['tags'])->pluck('id')->all());
        }
    }
}
