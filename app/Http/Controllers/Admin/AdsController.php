<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Setting;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdsController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'published'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);
        $ads = Ad::query()
            ->when(trim($filters['q'] ?? '') !== '', fn ($query) => $query
                ->where(fn ($search) => $search->where('name', 'like', '%'.trim($filters['q']).'%')
                    ->orWhere('title', 'like', '%'.trim($filters['q']).'%')
                    ->orWhere('shortcode', 'like', '%'.trim($filters['q']).'%')))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderBy('sort_order')->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? 10))
            ->withQueryString();

        return view('admin.pages.ads.index', compact('ads', 'filters'));
    }

    public function create(): View
    {
        return view('admin.pages.ads.form', ['ad' => new Ad(), 'title' => 'Create ad']);
    }

    public function edit(Ad $ad): View
    {
        return view('admin.pages.ads.form', ['ad' => $ad, 'title' => 'Edit ad']);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;
        $data['shortcode'] = $this->uniqueShortcode($data['shortcode'] ?? null);
        $data['opens_new_tab'] = $request->boolean('opens_new_tab');
        Ad::create($data);

        return redirect()->route('admin.ads.index')->with('success', 'Ad created.');
    }

    public function update(Request $request, Ad $ad): RedirectResponse
    {
        $data = $this->validatedData($request, $ad);
        $data['shortcode'] = $this->uniqueShortcode($data['shortcode'] ?? $ad->shortcode, $ad->id);
        $data['opens_new_tab'] = $request->boolean('opens_new_tab');
        $ad->update($data);

        return redirect()->route('admin.ads.index')->with('success', 'Ad updated.');
    }

    private function validatedData(Request $request, ?Ad $ad = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'shortcode' => ['nullable', 'string', 'max:32', Rule::unique('ads', 'shortcode')->ignore($ad?->id)],
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:180'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'target_url' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                $isLocalPath = is_string($value) && str_starts_with($value, '/')
                    && ! str_starts_with($value, '//')
                    && ! str_contains($value, '\\')
                    && ! preg_match('/[\r\n]/', $value)
                    && parse_url($value, PHP_URL_SCHEME) === null
                    && parse_url($value, PHP_URL_HOST) === null;
                $isSecureUrl = is_string($value) && filter_var($value, FILTER_VALIDATE_URL)
                    && parse_url($value, PHP_URL_SCHEME) === 'https';
                if (! $isLocalPath && ! $isSecureUrl) {
                    $fail('The destination must be a same-site path or a valid HTTPS URL.');
                }
            }],
            'image_url' => ['required', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! $this->isValidImageSource($value)) {
                    $fail('Choose an image from the Media library or provide a valid HTTPS image URL.');
                }
            }],
            'tablet_image_url' => ['nullable', 'string', 'max:2048', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value !== null && $value !== '' && ! $this->isValidImageSource($value)) {
                    $fail('Choose an image from the Media library or provide a valid HTTPS image URL.');
                }
            }],
            'opens_new_tab' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
        ]);
    }

    private function isValidImageSource(mixed $value): bool
    {
        return is_string($value)
            && (MediaLibrary::isSelectableImage($value)
                || (filter_var($value, FILTER_VALIDATE_URL) && parse_url($value, PHP_URL_SCHEME) === 'https'));
    }

    private function uniqueShortcode(?string $requested, ?int $ignoreId = null): string
    {
        $base = Str::upper(Str::slug($requested ?: 'AD-'.Str::random(8), '-'));
        $candidate = Str::limit($base, 32, '');
        $suffix = 2;
        while (Ad::where('shortcode', $candidate)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $candidate = Str::limit($base, 27, '').'-'.$suffix++;
        }

        return $candidate;
    }

    public function destroy(Ad $ad): RedirectResponse
    {
        $ad->delete();

        return back()->with('success', 'Ad deleted.');
    }

    public function settings(): View
    {
        return view('admin.pages.ads.settings', [
            'settings' => [
                'enabled' => Setting::get('ads_enabled', '0') === '1',
                'rotation_seconds' => (int) Setting::get('ads_rotation_seconds', '6'),
                'ads_txt' => Setting::get('ads_txt_content', ''),
            ],
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $request->merge(['enabled' => $request->boolean('enabled')]);
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'rotation_seconds' => ['required', 'integer', 'min:3', 'max:30'],
            'ads_txt' => ['nullable', 'string', 'max:10000'],
        ]);

        Setting::set('ads_enabled', $data['enabled'] ? '1' : '0');
        Setting::set('ads_rotation_seconds', (string) $data['rotation_seconds']);
        Setting::set('ads_txt_content', trim($data['ads_txt'] ?? ''));

        return back()->with('success', 'Ad settings saved.');
    }

    public function click(Ad $ad): RedirectResponse
    {
        abort_unless($ad->status === 'published' && (! $ad->starts_at || $ad->starts_at->lte(now()))
            && (! $ad->expires_at || $ad->expires_at->gt(now())), 404);
        $isLocalPath = str_starts_with($ad->target_url, '/') && ! str_starts_with($ad->target_url, '//')
            && ! str_contains($ad->target_url, '\\')
            && ! preg_match('/[\r\n]/', $ad->target_url)
            && parse_url($ad->target_url, PHP_URL_SCHEME) === null
            && parse_url($ad->target_url, PHP_URL_HOST) === null;
        $isSecureUrl = filter_var($ad->target_url, FILTER_VALIDATE_URL)
            && parse_url($ad->target_url, PHP_URL_SCHEME) === 'https';
        abort_unless($isLocalPath || $isSecureUrl, 404);
        $ad->increment('clicks_count');

        return $isLocalPath
            ? redirect()->to($ad->target_url)
            : redirect()->away($ad->target_url);
    }
}
