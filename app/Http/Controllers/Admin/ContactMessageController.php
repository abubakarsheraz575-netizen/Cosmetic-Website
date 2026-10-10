<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageReply;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactMessageController extends Controller
{
    public function index()
    {
        $messages = ContactMessage::query()
            ->latest()
            ->paginate(20);

        return view('admin.pages.contacts', compact('messages'));
    }

    public function show(ContactMessage $contactMessage)
    {
        if ($contactMessage->read_at === null) {
            $contactMessage->forceFill(['read_at' => now()])->save();
        }

        return view('admin.pages.contact-detail', compact('contactMessage'));
    }

    public function reply(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'reply' => ['required', 'string', 'max:5000'],
        ]);

        $contactMessage->forceFill([
            'admin_reply' => $data['reply'],
            'replied_at' => now(),
        ])->save();

        try {
            Mail::to($contactMessage->email)->send(new ContactMessageReply(
                $contactMessage->name,
                $data['reply'],
                $contactMessage->message,
                $contactMessage->subject,
            ));
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('contact_reply_error', 'Your reply was saved, but email delivery failed. Check Mail Settings, then send it again.');
        }

        return back()->with('contact_reply_success', 'Your reply was sent to '.$contactMessage->email.'.');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        try {
            if (! $contactMessage->delete()) {
                return back()->with('contact_delete_error', 'This message could not be deleted. Please try again.');
            }
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('contact_delete_error', 'This message could not be deleted. Please try again.');
        }

        return redirect()->route('admin.contacts')->with('contact_delete_success', 'Contact message deleted.');
    }
}
