<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\PageHeaders;
use App\Support\MediaLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PageHeaderSettingController extends Controller
{
    public function index()
    {
        $headers = [];
        foreach (array_keys(config('page_headers.pages')) as $page) {
            $headers[$page] = PageHeaders::for($page);
        }

        return view('admin.pages.page-headers', compact('headers'));
    }

    public function update(Request $request): JsonResponse
    {
        $rules = [];
        foreach (array_keys(config('page_headers.pages')) as $page) {
            $rules["page_header_{$page}_title"] = ['required', 'string', 'max:255'];
            $rules["page_header_{$page}_image"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'];
            if (config("page_headers.pages.{$page}.description") !== null) {
                $rules["page_header_{$page}_description"] = ['required', 'string', 'max:500'];
            }
        }
        $data = $request->validate($rules);

        $uploaded = [];
        $previousPaths = [];
        try {
            foreach (array_keys(config('page_headers.pages')) as $page) {
                $key = "page_header_{$page}_image";
                if ($request->hasFile($key)) {
                    $path = $request->file($key)->store('page-headers', 'public');
                    if (! $path) {
                        throw new \RuntimeException('A page header image could not be stored.');
                    }
                    $uploaded[$key] = $path;
                    $previous = Setting::get($key);
                    if ($previous) {
                        $previousPaths[] = $previous;
                    }
                }
            }

            DB::transaction(function () use ($data, $uploaded): void {
                foreach (array_keys(config('page_headers.pages')) as $page) {
                    $titleKey = "page_header_{$page}_title";
                    Setting::set($titleKey, trim($data[$titleKey]));
                    $descriptionKey = "page_header_{$page}_description";
                    if (array_key_exists($descriptionKey, $data)) {
                        Setting::set($descriptionKey, trim($data[$descriptionKey]));
                    }
                }
                foreach ($uploaded as $key => $path) {
                    Setting::set($key, $path);
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

        $images = [];
        foreach (array_keys(config('page_headers.pages')) as $page) {
            $images[$page] = PageHeaders::for($page)['image'];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Page header settings saved successfully.',
            'images' => $images,
        ]);
    }
}
