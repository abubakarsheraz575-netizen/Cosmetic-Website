<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        $announcements = Announcement::forCustomer(auth()->id())->paginate(10);

        return view('user.pages.announcements', compact('announcements'));
    }
}
