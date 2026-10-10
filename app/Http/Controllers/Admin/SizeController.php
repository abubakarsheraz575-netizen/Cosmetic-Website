<?php
// app/Http/Controllers/Admin/SizeController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index()
    {
        $sizes = Size::orderBy('ml')->get();
        $maxMl = $sizes->max('ml') ?: 100;

        $stats = [
            'total'  => Size::count(),
            'active' => Size::where('is_active', true)->count(),
            'hidden' => Size::where('is_active', false)->count(),
            'new'    => Size::whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.pages.sizes', compact('sizes', 'stats', 'maxMl'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $size = Size::create([
            'name'      => $data['name'],
            'ml'        => (int) $data['ml'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json(['message' => "{$size->name} added successfully.", 'size' => $size]);
    }

    public function update(Request $request, Size $size): JsonResponse
    {
        $data = $this->validated($request, $size->id);

        $size->update([
            'name'      => $data['name'],
            'ml'        => (int) $data['ml'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json(['message' => "{$size->name} updated.", 'size' => $size->fresh()]);
    }

    public function updateStatus(Request $request, Size $size): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $size->update(['is_active' => $data['is_active']]);
        $size->refresh();

        return response()->json([
            'message' => "{$size->name} is now " . ($size->is_active ? 'active.' : 'hidden.'),
            'size' => $size,
        ]);
    }

    public function destroy(Size $size): JsonResponse
    {
        $name = $size->name;
        $size->delete();

        return response()->json(['message' => "{$name} deleted."]);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'ml'   => ['required', 'integer', 'min:1', 'max:100000'],
        ]);
    }
}