<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('role', 'user')->latest()->get();

        $stats = [
            'total'   => User::where('role', 'user')->count(),
            'active'  => User::where('role', 'user')->where('is_blocked', false)->count(),
            'blocked' => User::where('role', 'user')->where('is_blocked', true)->count(),
            'new'     => User::where('role', 'user')->whereMonth('created_at', now()->month)
                                      ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.pages.users', compact('users', 'stats'));
    }

    public function block(User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Admins cannot be blocked.'], 403);
        }

        $user->forceFill(['is_blocked' => true, 'remember_token' => null])->save();

        return response()->json(['status' => 'blocked', 'message' => "{$user->name} has been blocked."]);
    }

    public function unblock(User $user): JsonResponse
    {
        $user->update(['is_blocked' => false]);

        return response()->json(['status' => 'active', 'message' => "{$user->name} is now active."]);
    }
}