<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\TaxRate;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponTaxController extends Controller
{
    public function coupons(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', Rule::in([10, 25, 50])],
        ]);
        $query = Coupon::query()->orderByDesc('created_at');
        if (! empty($filters['search'])) {
            $query->where('code', 'like', '%'.$filters['search'].'%');
        }

        return view('admin.pages.ecommerce.coupons', [
            'coupons' => $query->paginate((int) ($filters['per_page'] ?? 10))->withQueryString(),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $data = $this->validateCoupon($request);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                Coupon::create($data + ['code' => 'CPN-'.Str::upper(Str::random(12))]);

                return redirect()->route('admin.ecommerce.coupons')->with('success', 'Coupon created with a unique generated code.');
            } catch (QueryException $exception) {
                if (!str_contains(strtolower($exception->getMessage()), 'unique')) {
                    throw $exception;
                }
            }
        }

        return back()->withInput()->withErrors(['code' => 'A unique coupon code could not be generated. Please try again.']);
    }

    public function updateCoupon(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validateCoupon($request, $coupon));

        return redirect()->route('admin.ecommerce.coupons')->with('success', 'Coupon updated.');
    }

    public function destroyCoupon(Coupon $coupon)
    {
        $coupon->delete();

        return redirect()->route('admin.ecommerce.coupons')->with('success', 'Coupon deleted.');
    }

    public function taxes(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', Rule::in([10, 25, 50])],
        ]);
        $query = TaxRate::query()->orderBy('name');
        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return view('admin.pages.ecommerce.taxes', [
            'taxes' => $query->paginate((int) ($filters['per_page'] ?? 10))->withQueryString(),
        ]);
    }

    public function storeTax(Request $request)
    {
        TaxRate::create($this->validateTax($request));

        return redirect()->route('admin.ecommerce.taxes')->with('success', 'Tax created.');
    }

    public function updateTax(Request $request, TaxRate $taxRate)
    {
        $taxRate->update($this->validateTax($request));

        return redirect()->route('admin.ecommerce.taxes')->with('success', 'Tax updated.');
    }

    public function destroyTax(TaxRate $taxRate)
    {
        $taxRate->delete();

        return redirect()->route('admin.ecommerce.taxes')->with('success', 'Tax deleted.');
    }

    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        return $request->validate([
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'gt:0', 'max:'.($request->input('discount_type') === 'percent' ? '100' : '99999999.99')],
            'usage_limit' => ['required', 'integer', 'min:'.max(1, ($coupon?->uses_count ?? 0) + ($coupon?->reserved_count ?? 0))],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function validateTax(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'percentage' => ['required', 'numeric', 'gt:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
