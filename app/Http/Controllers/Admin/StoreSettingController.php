<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreSettingController extends Controller
{
    private array $footerKeys = [
        'footer_contact_title', 'footer_links_title', 'footer_tagline', 'footer_copyright',
        'footer_link_1_label', 'footer_link_1_url', 'footer_link_2_label', 'footer_link_2_url',
        'footer_link_3_label', 'footer_link_3_url', 'footer_link_4_label', 'footer_link_4_url',
        'footer_link_5_label', 'footer_link_5_url',
    ];

    private array $footerDefaults = [
        'footer_contact_title' => 'Contact Details',
        'footer_links_title' => 'Quick Links',
        'footer_tagline' => 'Thoughtful beauty, made for your everyday glow.',
        'footer_copyright' => 'Copyright © {year} Cosmetics Store | Powered by Cosmetics Store',
        'footer_link_1_label' => 'Shop', 'footer_link_1_url' => '/shop',
        'footer_link_2_label' => 'Beauty Journal', 'footer_link_2_url' => '/blog',
        'footer_link_3_label' => 'FAQs', 'footer_link_3_url' => '/faqs',
        'footer_link_4_label' => 'About Us', 'footer_link_4_url' => '/about',
        'footer_link_5_label' => 'Testimonials', 'footer_link_5_url' => '/testimonials',
    ];

    private array $keys = [
        'site_email', 'site_phone', 'site_address', 'site_map_query',
        'hours_weekdays', 'hours_saturday', 'hours_sunday',
        'social_instagram', 'social_facebook', 'social_twitter', 'social_youtube',
        'footer_contact_title', 'footer_links_title', 'footer_tagline', 'footer_copyright',
        'footer_link_1_label', 'footer_link_1_url', 'footer_link_2_label', 'footer_link_2_url',
        'footer_link_3_label', 'footer_link_3_url', 'footer_link_4_label', 'footer_link_4_url',
        'footer_link_5_label', 'footer_link_5_url',
        'stat_years', 'stat_clients', 'stat_products', 'stat_satisfaction',
    ];

    public function index()
    {
        $defaults = [
            'stat_years' => '10', 'stat_clients' => '25',
            'stat_products' => '120', 'stat_satisfaction' => '98',
        ] + $this->footerDefaults;

        $s = [];
        foreach ($this->keys as $k) {
            $s[$k] = Setting::get($k, $defaults[$k] ?? '');
        }

        $contactSettings = [];
        foreach (config('contact_page.content') as $key => $default) {
            $contactSettings[$key] = Setting::get($key, $default);
        }
        $contactImages = [];
        foreach (config('contact_page.images') as $key => $default) {
            $path = Setting::get($key);
            $contactImages[$key] = $path ? asset('storage/' . $path) : $default;
        }

        return view('admin.pages.store-settings', compact('s', 'contactSettings', 'contactImages'));
    }

    public function footer()
    {
        $settings = [];
        foreach ($this->footerKeys as $key) {
            $settings[$key] = Setting::get($key, $this->footerDefaults[$key] ?? '');
        }

        return view('admin.pages.footer-settings', compact('settings'));
    }

    public function updateFooter(Request $request): RedirectResponse
    {
        $rules = [
            'footer_contact_title' => ['nullable', 'string', 'max:80'],
            'footer_links_title' => ['nullable', 'string', 'max:80'],
            'footer_tagline' => ['nullable', 'string', 'max:255'],
            'footer_copyright' => ['nullable', 'string', 'max:255'],
        ];
        foreach (range(1, 5) as $linkNumber) {
            $rules["footer_link_{$linkNumber}_label"] = ['nullable', 'string', 'max:80'];
            $rules["footer_link_{$linkNumber}_url"] = [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/|\/(?!\/))[^\s]*$/i',
            ];
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($data): void {
            foreach ($this->footerKeys as $key) {
                Setting::set($key, trim((string) ($data[$key] ?? '')));
            }
        });

        return redirect()->route('admin.footer-settings')->with('success', 'Footer settings saved.');
    }

    public function update(Request $request)
    {
        $rules = [
            'site_email'        => 'nullable|email|max:150',
            'site_phone'        => 'nullable|string|max:40',
            'site_address'      => 'nullable|string|max:255',
            'site_map_query'    => 'nullable|string|max:255',
            'hours_weekdays'    => 'nullable|string|max:60',
            'hours_saturday'    => 'nullable|string|max:60',
            'hours_sunday'      => 'nullable|string|max:60',
            'social_instagram'  => 'nullable|url|max:255',
            'social_facebook'   => 'nullable|url|max:255',
            'social_twitter'    => 'nullable|url|max:255',
            'social_youtube'    => 'nullable|url|max:255',
            'footer_contact_title' => 'nullable|string|max:80',
            'footer_links_title' => 'nullable|string|max:80',
            'footer_tagline' => 'nullable|string|max:255',
            'footer_copyright' => 'nullable|string|max:255',
            'footer_link_1_label' => 'nullable|string|max:80',
            'footer_link_2_label' => 'nullable|string|max:80',
            'footer_link_3_label' => 'nullable|string|max:80',
            'footer_link_4_label' => 'nullable|string|max:80',
            'footer_link_5_label' => 'nullable|string|max:80',
            'stat_years'        => 'nullable|numeric|max:999',
            'stat_clients'      => 'nullable|numeric|max:9999',
            'stat_products'     => 'nullable|numeric|max:9999',
            'stat_satisfaction' => 'nullable|numeric|max:100',
        ];
        foreach (range(1, 5) as $linkNumber) {
            $rules["footer_link_{$linkNumber}_url"] = [
                'nullable',
                'string',
                'max:255',
                'regex:/^(https?:\/\/|\/(?!\/))[^\s]*$/i',
            ];
        }
        foreach (array_keys(config('contact_page.content')) as $key) {
            $rules[$key] = ['nullable', 'string', 'max:2000'];
        }
        foreach (array_keys(config('contact_page.images')) as $key) {
            $rules[$key] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            $rules["clear_{$key}"] = ['sometimes', 'boolean'];
        }
        $data = $request->validate($rules);

        $uploaded = [];
        $previousPaths = [];
        try {
            foreach (array_keys(config('contact_page.images')) as $key) {
                if ($request->hasFile($key)) {
                    $path = $request->file($key)->store('contact', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A contact page image could not be stored.');
                    }
                    $uploaded[$key] = $path;
                }
                if ($request->boolean("clear_{$key}") || isset($uploaded[$key])) {
                    $previous = Setting::get($key);
                    if ($previous) {
                        $previousPaths[] = $previous;
                    }
                }
            }

            DB::transaction(function () use ($request, $data, $uploaded): void {
                foreach ($this->keys as $key) {
                    Setting::set($key, trim((string) ($data[$key] ?? '')));
                }
                foreach (array_keys(config('contact_page.content')) as $key) {
                    Setting::set($key, trim((string) ($data[$key] ?? '')));
                }
                foreach (array_keys(config('contact_page.images')) as $key) {
                    if (isset($uploaded[$key])) {
                        Setting::set($key, $uploaded[$key]);
                    } elseif ($request->boolean("clear_{$key}")) {
                        Setting::set($key, '');
                    }
                }
            });
        } catch (\Throwable $exception) {
            if ($uploaded) {
                MediaLibrary::deleteIfUnused(array_values($uploaded));
            }
            throw $exception;
        }

        foreach ($previousPaths as $path) {
            MediaLibrary::deleteIfUnused($path);
        }

        $message = 'Store settings saved — contact & about pages updated!';

        if ($request->expectsJson()) {
            $images = [];
            foreach (array_keys(config('contact_page.images')) as $key) {
                $path = Setting::get($key);
                $images[$key] = $path ? asset('storage/' . $path) : config("contact_page.images.{$key}");
            }

            return response()->json(['status' => 'success', 'message' => $message, 'images' => $images]);
        }

        return redirect()->route('admin.store-settings')->with('success', $message . ' ✅');
    }
}