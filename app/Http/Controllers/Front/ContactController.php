<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\AdminEmailNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'subject' => 'nullable|string|max:150',
            'message' => 'required|string|max:3000',
        ]);

        $contactMessage = ContactMessage::create($data);

        $adminEmail = AdminEmailNotifier::recipientAddress();
        if (! $adminEmail) {
            report(new \RuntimeException('No valid admin email address is configured for contact inquiries.'));
            return redirect()
                ->route('contact')
                ->withInput()
                ->with('contact_error', 'Your message was saved, but the admin email address is not configured.');
        }

        try {
            Mail::to($adminEmail)->send(new ContactMessageReceived($contactMessage));
        } catch (\Throwable $e) {
            report($e);
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your message could not be sent. Please try again later.',
                ], 503);
            }

            return redirect()
                ->route('contact')
                ->withInput()
                ->with('contact_error', 'Message send nahi ho saka — SMTP Mail Settings check karein, ya direct email karein: '.$adminEmail);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Thank you! Your message has been sent — our team will reply within 24 hours.',
            ]);
        }

        return redirect()
            ->route('contact')
            ->with('contact_success', 'Thank you! Your message has been sent — our team will reply within 24 hours. ✨');
    }
}
