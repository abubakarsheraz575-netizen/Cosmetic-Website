<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Support\HomeCategoryStrip;
use App\Support\PageHeaders;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->withCount(['products' => function ($q) {
                $q->where('is_active', true)->where('review_status', 'approved');
            }])
            ->orderBy('name')
            ->get();
        $categoryOverrides = HomeCategoryStrip::overrides();
        $brands = Brand::query()
            ->where('is_active', true)
            ->whereHas('products', fn ($products) => $products
                ->where('is_active', true)
                ->where('review_status', 'approved'))
            ->withCount(['products' => fn ($products) => $products
                ->where('is_active', true)
                ->where('review_status', 'approved')])
            ->orderBy('name')
            ->get();
        $selectedBrand = Brand::query()
            ->where('is_active', true)
            ->where('slug', $request->query('brand'))
            ->first();

        $query = Product::where('is_active', true)
            ->where('review_status', 'approved')
            ->with([
                'category',
                'brand',
                'images',
                'labels',
                'skuVariants',
                'collections' => fn ($collections) => $collections->active()->orderBy('ends_at'),
            ])
            ->withCount('skuVariants')
            ->withCount([
                'reviews as approved_reviews_count' => fn ($reviews) => $reviews->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn ($reviews) => $reviews->where('status', 'approved'),
            ], 'rating')
            ->withSum([
                'orderItems as sold_units' => fn ($items) => $items->whereHas(
                    'order',
                    fn ($orders) => $orders->where('payment_status', 'paid')
                ),
            ], 'qty');

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn ($brand) => $brand
                ->where('slug', $request->query('brand'))
                ->where('is_active', true));
        }

        /* ---- live search ---- */
        if ($request->filled('search')) {
            $s = trim($request->get('search'));
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('brand', fn ($brand) => $brand->where('name', 'like', "%{$s}%"))
                  ->orWhereHas('category', function ($c) use ($s) {
                      $c->where('name', 'like', "%{$s}%");
                  });
            });
        }

        /* ---- category filter ---- */
        if ($request->filled('category')) {
            $query->whereHas('category', function ($c) use ($request) {
                $c->where('slug', $request->get('category'))->where('is_active', true);
            });
        }

        /* ---- sorting ---- */
        switch ($request->get('sort', 'latest')) {
            case 'price_low':
                $query->orderByRaw("CASE WHEN sale_price IS NOT NULL AND (sale_starts_at IS NULL OR sale_starts_at <= CURRENT_TIMESTAMP) AND (sale_ends_at IS NULL OR sale_ends_at > CURRENT_TIMESTAMP) THEN sale_price WHEN discount_type = 'fixed' THEN price - discount ELSE price * (1 - discount/100) END ASC");
                break;
            case 'price_high':
                $query->orderByRaw("CASE WHEN sale_price IS NOT NULL AND (sale_starts_at IS NULL OR sale_starts_at <= CURRENT_TIMESTAMP) AND (sale_ends_at IS NULL OR sale_ends_at > CURRENT_TIMESTAMP) THEN sale_price WHEN discount_type = 'fixed' THEN price - discount ELSE price * (1 - discount/100) END DESC");
                break;
            case 'name':
                $query->orderBy('name');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12)->withQueryString();

        /* ---- AJAX response for live search / filters ---- */
        if ($request->wantsJson()) {
            return response()->json([
                'status'       => 'success',
                'count'        => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'products'     => $products->getCollection()->map(function ($p) {
                    return [
                        'id'          => $p->id,
                        'name'        => $p->name,
                        'slug'        => $p->slug,
                        'url'         => route('product.show', $p->slug),
                        'category'    => $p->category?->name,
                        'brand'       => $p->brand ? [
                            'name' => $p->brand->name,
                            'slug' => $p->brand->slug,
                            'logo' => $p->brand->logo_path
                                ? asset('storage/' . $p->brand->logo_path)
                                : null,
                        ] : null,
                        'icon'        => $p->emoji,
                        'price'       => (float) $p->price,
                        'final_price' => (float) $p->final_price,
                        'is_on_sale'  => (bool) $p->is_on_sale,
                        'sale_price'  => $p->sale_price,
                        'sale_ends_at' => $p->sale_ends_at?->toIso8601String(),
                        'discount'    => (float) $p->discount,
                        'discount_type' => $p->discount_type,
                        'stock'       => (int) $p->stock,
                        'has_variants' => $p->sku_variants_count > 0,
                        'review_count' => (int) $p->approved_reviews_count,
                        'average_rating' => (float) ($p->approved_reviews_avg_rating ?? 0),
                        'sold_units'  => (int) ($p->sold_units ?? 0),
                        'collection_ends_at' => $p->collections->first()?->ends_at?->toIso8601String(),
                        'img'         => $p->images->first()
                                        ? asset('storage/' . $p->images->first()->path)
                                        : null,
                        'labels'      => $p->labels->map(fn ($label) => [
                            'name' => $label->name,
                            'type' => $label->type,
                        ])->values(),
                    ];
                }),
            ]);
        }

        return view('front-end.pages.shop', [
            'categories'  => $categories,
            'categoryOverrides' => $categoryOverrides,
            'brands'      => $brands,
            'selectedBrand' => $selectedBrand,
            'products'    => $products,
            'totalActive' => $categories->sum('products_count'),
            'pageHeader'  => PageHeaders::for('shop'),
        ]);
    }

    public function quickView(string $slug)
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->where('review_status', 'approved')
            ->with([
                'category',
                'brand',
                'images',
                'variants.attribute',
                'skuVariants.options.attribute',
            ])
            ->withCount([
                'reviews as approved_reviews_count' => fn ($reviews) => $reviews->where('status', 'approved'),
            ])
            ->withAvg([
                'reviews as approved_reviews_avg_rating' => fn ($reviews) => $reviews->where('status', 'approved'),
            ], 'rating')
            ->firstOrFail();

        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'url' => route('product.show', $product->slug),
            'category' => $product->category?->name,
            'brand' => $product->brand?->name,
            'icon' => $product->emoji,
            'description' => \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 420),
            'image' => $product->images->first()
                ? asset('storage/'.$product->images->first()->path)
                : null,
            'price' => (float) $product->price,
            'final_price' => (float) $product->final_price,
            'is_on_sale' => (bool) $product->is_on_sale,
            'stock' => (int) $product->stock,
            'has_variants' => $product->skuVariants->isNotEmpty(),
            'review_count' => (int) $product->approved_reviews_count,
            'average_rating' => (float) ($product->approved_reviews_avg_rating ?? 0),
            'options' => $product->skuVariants->map(fn ($skuVariant) => [
                'name' => $skuVariant->options->map(fn ($option) => $option->attribute->name.': '.$option->value)->implode(', '),
                'stock' => (int) $skuVariant->stock,
            ])->values(),
        ]);
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)
            ->where('review_status', 'approved')
            ->with(['category', 'brand', 'size', 'variants.attribute', 'skuVariants.options.attribute', 'labels', 'specificationValues.attribute', 'reviews' => function ($q) { $q->where('status', '!=', 'blocked')->latest(); }])
            ->firstOrFail();

        /* every photo of the product, ordered */
        $gallery = $product->images
            ->values()
            ->map(fn ($img) => asset('storage/' . $img->path))
            ->all();

        /* ---- related products ---- */
        $related = Product::where('is_active', true)
            ->where('review_status', 'approved')
            ->where('id', '!=', $product->id)
            ->when($product->category_id, function ($q) use ($product) {
                $q->where('category_id', $product->category_id);
            })
            ->with(['images', 'labels', 'brand'])
            ->withCount('skuVariants')
            ->inRandomOrder()
            ->take(4)
            ->get();

        if ($related->count() < 4) {
            $exclude = $related->pluck('id')->push($product->id)->all();
            $extra   = Product::where('is_active', true)
                ->where('review_status', 'approved')
                ->whereNotIn('id', $exclude)
                ->with(['images', 'labels', 'brand'])
                ->withCount('skuVariants')
                ->latest()
                ->take(4 - $related->count())
                ->get();

            $related = $related->merge($extra);
        }

        /* ---- review stats ---- */
        $reviews     = $product->reviews;
        $reviewTotal = $reviews->count();
        $reviewAvg   = $reviewTotal ? round($reviews->avg('rating'), 1) : 0;

        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($reviews as $r) {
            if (isset($dist[$r->rating])) $dist[$r->rating]++;
        }

        return view('front-end.pages.product-detail', [
            'product' => $product,
            'gallery' => $gallery,
            'related' => $related,
            'reviews' => $reviews,
            'reviewTotal' => $reviewTotal,
            'reviewAvg' => $reviewAvg,
            'dist' => $dist,
            'pageHeader' => PageHeaders::for('product_detail'),
        ]);
    }
}