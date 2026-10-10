<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NewsletterAdminController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['subscribed', 'unsubscribed'])],
            'per_page' => ['nullable', Rule::in(['10', '25', '50'])],
        ]);
        $subscribers = NewsletterSubscriber::query()
            ->when(trim($filters['q'] ?? '') !== '', fn ($query) => $query->where('email', 'like', '%'.trim($filters['q']).'%'))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('consented_at')
            ->paginate((int) ($filters['per_page'] ?? 10))->withQueryString();

        return view('admin.pages.newsletters.index', compact('subscribers', 'filters'));
    }

    public function updateStatus(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        $request->validate(['status' => ['required', Rule::in(['unsubscribed'])]]);
        $subscriber->update(['status' => 'unsubscribed']);

        return back()->with('success', 'Subscriber status updated.');
    }

    public function destroy(NewsletterSubscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber deleted.');
    }
}
