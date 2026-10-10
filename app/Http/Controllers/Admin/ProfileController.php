<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\MediaLibrary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * 👤 Admin profile page
     */
    public function index()
    {
        $admin = auth()->user();

        return view('admin.pages.profile', compact('admin'));
    }

    /**
     * 💾 Update profile (section-wise: info / password / photo)
     */
    public function update(Request $request)
    {
        $admin = auth()->user();

        /* 🔍 section value — case-insensitive + trim (bulletproof) */
        $section = strtolower(trim((string) $request->input('section')));

        switch ($section) {

            /* ---------- 📸 PHOTO ---------- */
            case 'photo':

                /* 🗑️ remove */
                if ($request->boolean('remove')) {
                    if ($admin->profile_photo) {
                        MediaLibrary::deleteIfUnused($admin->profile_photo);
                    }
                    $admin->profile_photo = null;
                    $admin->save();

                    return response()->json([
                        'status'    => 'success',
                        'message'   => 'Profile photo removed.',
                        'photo_url' => null,
                    ]);
                }

                /* 📤 upload */
                $request->validate([
                    'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
                ], [
                    'photo.required' => 'Please select a photo first.',
                    'photo.image'    => 'The file must be an image.',
                    'photo.mimes'    => 'Allowed types: JPG, JPEG, PNG, WEBP.',
                    'photo.max'      => 'Photo must be under 2MB.',
                ]);

                if ($admin->profile_photo) {
                    MediaLibrary::deleteIfUnused($admin->profile_photo);
                }

                $path = $request->file('photo')->store('avatars', 'public');
                $admin->profile_photo = $path;
                $admin->save();

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Profile photo updated.',
                    'photo_url' => asset('storage/' . $path),
                ]);

            /* ---------- 👤 INFO ---------- */
            case 'info':
                $request->validate([
                    'name'  => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
                    'phone' => 'nullable|string|max:30',
                ], [
                    'name.required'  => 'Name is required.',
                    'email.required' => 'Email is required.',
                    'email.email'    => 'Enter a valid email address.',
                    'email.unique'   => 'This email is already taken by another account.',
                ]);

                $admin->name  = $request->input('name');
                $admin->email = $request->input('email');
                $admin->phone = $request->input('phone');
                $admin->save();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Profile updated successfully.',
                    'user'    => [
                        'name'  => $admin->name,
                        'email' => $admin->email,
                        'phone' => $admin->phone,
                    ],
                ]);

            /* ---------- 🔐 PASSWORD ---------- */
            case 'password':
                $request->validate([
                    'current_password' => 'required',
                    'password'         => 'required|string|min:8|confirmed',
                ], [
                    'current_password.required' => 'Current password is required.',
                    'password.required'         => 'New password is required.',
                    'password.min'              => 'New password must be at least 8 characters.',
                    'password.confirmed'        => 'Password confirmation does not match.',
                ]);

                if (! Hash::check($request->input('current_password'), $admin->password)) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Current password is incorrect.',
                    ], 422);
                }

                $admin->password = Hash::make($request->input('password'));
                $admin->save();

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Password updated successfully.',
                ]);

            /* ---------- ❓ UNKNOWN (diagnostic — received value dikhata hai) ---------- */
            default:
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unknown section received: "' . $section . '" (empty = body not parsed).',
                ], 422);
        }
    }
}