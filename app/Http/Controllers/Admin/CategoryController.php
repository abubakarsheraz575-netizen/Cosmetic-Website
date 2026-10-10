<?php
// app/Http/Controllers/Admin/CategoryController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        $stats = [
            'total'  => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'hidden' => Category::where('is_active', false)->count(),
            'new'    => Category::whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->count(),
        ];

        $categoryIconChoices = config('category_icons.choices');
        $categoryIconClasses = collect($categoryIconChoices)->pluck('value', 'value')
            ->union(collect(config('product_icons.choices'))->pluck('class', 'value'))
            ->all();

        return view('admin.pages.categories', compact('categories', 'stats', 'categoryIconChoices', 'categoryIconClasses'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'icon'        => ['required', 'string', Rule::in(collect(config('category_icons.choices'))->pluck('value')->all())],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category = Category::create([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'icon'        => $data['icon'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return response()->json(['message' => "{$category->name} added successfully.", 'category' => $category]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'icon'        => ['required', 'string', Rule::in(collect(config('category_icons.choices'))->pluck('value')->all())],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $category->update([
            'name'        => $data['name'],
            'slug'        => Str::slug($data['name']),
            'icon'        => $data['icon'],
            'description' => $data['description'] ?? null,
            'is_active'   => $request->boolean('is_active'),
        ]);

        return response()->json(['message' => "{$category->name} updated.", 'category' => $category->fresh()]);
    }

    public function updateStatus(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $category->update(['is_active' => $data['is_active']]);
        $category->refresh();

        return response()->json([
            'message' => "{$category->name} is now " . ($category->is_active ? 'active.' : 'hidden.'),
            'category' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $name = $category->name;
        $category->delete();

        return response()->json(['message' => "{$name} deleted."]);
    }
}