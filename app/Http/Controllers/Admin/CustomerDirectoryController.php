<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['customer', 'vendor'])],
            'status' => ['nullable', Rule::in(['active', 'blocked'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
            'sort' => ['nullable', Rule::in(['name', 'created_at', 'is_blocked', 'submitted_products_count'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sortColumns = [
            'name' => 'users.name',
            'created_at' => 'users.created_at',
            'is_blocked' => 'users.is_blocked',
        ];
        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';

        $users = $this->directoryUsers()
            ->when(trim($filters['q'] ?? '') !== '', function ($query) use ($filters) {
                $search = trim($filters['q']);
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', '%' . $search . '%')
                        ->orWhere('users.email', 'like', '%' . $search . '%');
                });
            })
            ->when(($filters['type'] ?? '') === 'vendor', fn ($query) => $this->vendorsOnly($query))
            ->when(($filters['type'] ?? '') === 'customer', fn ($query) => $this->customersOnly($query))
            ->when(isset($filters['status']), fn ($query) => $query->where('users.is_blocked', $filters['status'] === 'blocked'))
            ->when($sort === 'submitted_products_count', fn ($query) => $query->orderByRaw(
                '(select count(*) from products where products.submitted_by = users.id) ' . $direction
            ), fn ($query) => $query->orderBy($sortColumns[$sort], $direction))
            ->orderBy('users.id', $direction)
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();
        $submittedCounts = Product::query()
            ->selectRaw('submitted_by, count(*) as total')
            ->whereIn('submitted_by', $users->getCollection()->pluck('id'))
            ->groupBy('submitted_by')
            ->pluck('total', 'submitted_by');
        $users->getCollection()->each(
            fn (User $person) => $person->setAttribute('submitted_products_count', (int) ($submittedCounts[$person->id] ?? 0))
        );

        $directory = $this->directoryUsers();
        $stats = [
            'total' => (clone $directory)->count(),
            'vendors' => $this->vendorsOnly(clone $directory)->count(),
            'blocked' => (clone $directory)->where('users.is_blocked', true)->count(),
            'new' => (clone $directory)->whereMonth('users.created_at', now()->month)
                ->whereYear('users.created_at', now()->year)->count(),
        ];

        return view('admin.pages.ecommerce.customers.index', compact('users', 'filters', 'stats', 'sort', 'direction'));
    }

    public function show(User $user): View
    {
        abort_if($user->isAdmin(), 404);

        $vendor = $this->directoryUsers()->where('users.id', $user->id)->firstOrFail();
        abort_unless(Product::where('submitted_by', $vendor->id)->exists(), 404);

        $productQuery = Product::query()->where('submitted_by', $vendor->id);
        $counts = [
            'submitted' => (clone $productQuery)->count(),
            'published' => (clone $productQuery)->where('review_status', 'approved')->where('is_active', true)->count(),
            'pending' => (clone $productQuery)->where('review_status', 'pending')->count(),
            'rejected' => (clone $productQuery)->where('review_status', 'rejected')->count(),
        ];

        $products = $productQuery
            ->with([
                'category',
                'images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id'),
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('admin.pages.ecommerce.customers.vendor-profile', compact('vendor', 'products', 'counts'));
    }

    private function directoryUsers()
    {
        return User::query()->where(function ($query) {
            $query->whereNull('users.role')->orWhere('users.role', '!=', 'admin');
        });
    }

    private function vendorsOnly($query)
    {
        return $query->whereExists(function ($subquery) {
            $subquery->selectRaw('1')
                ->from('products')
                ->whereColumn('products.submitted_by', 'users.id');
        });
    }

    private function customersOnly($query)
    {
        return $query->whereNotExists(function ($subquery) {
            $subquery->selectRaw('1')
                ->from('products')
                ->whereColumn('products.submitted_by', 'users.id');
        });
    }
}
