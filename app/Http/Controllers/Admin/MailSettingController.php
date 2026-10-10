<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class MailSettingController extends Controller
{
    public function index()
    {
        // ✅ ONLY from database — no .env / config() fallbacks
        $mail = [
            'host'       => Setting::get('mail_host', ''),
            'port'       => Setting::get('mail_port', ''),
            'username'   => Setting::get('mail_username', ''),
            'encryption' => Setting::get('mail_encryption', 'none'),
            'from_addr'  => Setting::get('mail_from_address', ''),
            'from_name'  => Setting::get('mail_from_name', ''),
        ];

        $isActive = (bool) Setting::get('mail_host');

        return view('admin.pages.mail-settings', compact('mail', 'isActive'));
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'host'       => ['required', 'string', 'max:255'],
            'port'       => ['required', 'integer', 'min:1', 'max:65535'],
            'username'   => ['required', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'max:255'],
            'encryption' => ['nullable', 'in:none,tls,ssl'],
            'from_addr'  => ['required', 'email', 'max:255'],
            'from_name'  => ['required', 'string', 'max:100'],
        ]);

        // ✅ everything saved ONLY in the database
        Setting::set('mail_host',         $data['host']);
        Setting::set('mail_port',         $data['port']);
        Setting::set('mail_username',     $data['username']);
        Setting::set('mail_encryption',   $data['encryption'] ?? 'none');
        Setting::set('mail_from_address', $data['from_addr']);
        Setting::set('mail_from_name',    $data['from_name']);

        // password: encrypted in DB, only replaced when admin types a new one
        if ($request->filled('password')) {
            Setting::set('mail_password', Crypt::encryptString($data['password']));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Mail settings saved to database.',
        ]);
    }
}