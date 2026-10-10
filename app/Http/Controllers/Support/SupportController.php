<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Models\User;
use App\Support\AdminEmailNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupportController extends Controller
{
    private const ONLINE_WINDOW_SECONDS = 45;

    public function userPage(Request $request)
    {
        $conversation = SupportConversation::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        $orderContext = null;
        if ($request->filled('order') || $request->filled('item')) {
            $data = $request->validate([
                'order' => ['required', 'integer'],
                'item' => ['required', 'integer'],
            ]);
            $item = OrderItem::query()
                ->whereKey($data['item'])
                ->where('order_id', $data['order'])
                ->whereHas('order', fn (Builder $query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('payment_status', 'paid'))
                ->with(['order:id,order_number', 'order.items.product.images', 'product.images'])
                ->firstOrFail();

            $orderContext = $this->serializeOrderContext($item);
        }

        return view('user.pages.support', compact('conversation', 'orderContext'));
    }

    public function adminPage()
    {
        return view('admin.pages.support');
    }

    public function userMessages(Request $request): JsonResponse
    {
        $conversation = SupportConversation::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return $this->messagesFor($request, $conversation, false);
    }

    public function adminMessages(Request $request, SupportConversation $conversation): JsonResponse
    {
        return $this->messagesFor($request, $conversation, true);
    }

    public function userHeartbeat(Request $request): JsonResponse
    {
        $conversation = SupportConversation::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return $this->heartbeat($request, $conversation, false);
    }

    public function adminHeartbeat(Request $request, SupportConversation $conversation): JsonResponse
    {
        return $this->heartbeat($request, $conversation, true);
    }

    public function userSend(Request $request): JsonResponse
    {
        $conversation = SupportConversation::firstOrCreate([
            'user_id' => $request->user()->id,
        ]);

        return $this->send($request, $conversation);
    }

    public function adminSend(Request $request, SupportConversation $conversation): JsonResponse
    {
        return $this->send($request, $conversation);
    }

    public function userEditMessage(Request $request, SupportMessage $message): JsonResponse
    {
        return $this->editMessage($request, $message, false);
    }

    public function adminEditMessage(Request $request, SupportConversation $conversation, SupportMessage $message): JsonResponse
    {
        return $this->editMessage($request, $message, true, $conversation);
    }

    public function userDeleteMessage(Request $request, SupportMessage $message): JsonResponse
    {
        return $this->deleteMessage($request, $message, false);
    }

    public function adminDeleteMessage(Request $request, SupportConversation $conversation, SupportMessage $message): JsonResponse
    {
        return $this->deleteMessage($request, $message, true, $conversation);
    }

    public function userTogglePin(Request $request, SupportMessage $message): JsonResponse
    {
        return $this->togglePin($request, $message, false);
    }

    public function adminTogglePin(Request $request, SupportConversation $conversation, SupportMessage $message): JsonResponse
    {
        return $this->togglePin($request, $message, true, $conversation);
    }

    public function userToggleStar(Request $request, SupportMessage $message): JsonResponse
    {
        return $this->toggleStar($request, $message, false);
    }

    public function adminToggleStar(Request $request, SupportConversation $conversation, SupportMessage $message): JsonResponse
    {
        return $this->toggleStar($request, $message, true, $conversation);
    }

    public function userReportMessage(Request $request, SupportMessage $message): JsonResponse
    {
        return $this->reportMessage($request, $message, false);
    }

    public function adminReportMessage(Request $request, SupportConversation $conversation, SupportMessage $message): JsonResponse
    {
        return $this->reportMessage($request, $message, true, $conversation);
    }

    public function userDeleteConversation(Request $request): JsonResponse
    {
        $conversation = SupportConversation::where('user_id', $request->user()->id)->firstOrFail();

        return $this->deleteConversation($conversation);
    }

    public function adminDeleteConversation(SupportConversation $conversation): JsonResponse
    {
        return $this->deleteConversation($conversation);
    }

    public function inbox(Request $request): JsonResponse
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($data['search'] ?? '');

        $users = User::query()
            ->where('role', 'user')
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            }))
            ->with(['supportConversation' => fn ($query) => $query
                ->with('latestMessage.sender:id,name,role')
                ->with('latestMessage.orderItem.order', 'latestMessage.orderItem.product.images')
                ->withCount([
                    'messages as unread_count' => fn (Builder $messages) => $messages
                        ->whereNull('read_at')
                        ->whereHas('sender', fn (Builder $sender) => $sender->where('role', 'user')),
                ]),
            ])
            ->orderByDesc(
                SupportConversation::query()
                    ->select('last_message_at')
                    ->whereColumn('support_conversations.user_id', 'users.id')
                    ->limit(1)
            )
            ->orderByDesc('users.created_at')
            ->paginate(30);

        $conversations = $users->getCollection()->map(function (User $user) {
            $conversation = $user->supportConversation;

            return [
                'id' => $conversation?->id,
                'user_id' => $user->id,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile_photo' => $user->profile_photo
                        ? asset('storage/' . $user->profile_photo)
                        : null,
                ],
                'last_message' => $conversation?->latestMessage
                    ? $this->serializeMessage($conversation->latestMessage)
                    : null,
                'unread_count' => $conversation?->unread_count ?? 0,
                'user_online' => $this->isOnline($conversation?->user_last_seen_at),
                'updated_at' => $conversation?->last_message_at?->toIso8601String()
                    ?? $user->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'conversations' => $conversations,
            'unread_total' => SupportMessage::query()
                ->whereNull('read_at')
                ->whereHas('sender', fn (Builder $sender) => $sender->where('role', 'user'))
                ->count(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function startConversation(User $user): JsonResponse
    {
        abort_unless($user->role === 'user', 404);

        $conversation = SupportConversation::firstOrCreate(['user_id' => $user->id]);

        return response()->json(['id' => $conversation->id]);
    }

    public function downloadAttachment(Request $request, SupportMessage $message): StreamedResponse
    {
        $message->loadMissing('conversation');
        $this->authorizeAttachment($request, $message);
        abort_unless(Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->download(
            $message->attachment_path,
            basename($message->attachment_name ?: 'support-attachment')
        );
    }

    public function previewAttachment(Request $request, SupportMessage $message): StreamedResponse
    {
        $message->loadMissing('conversation');
        $this->authorizeAttachment($request, $message);
        abort_unless(
            in_array($message->attachment_mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true),
            404
        );
        abort_unless(Storage::disk('local')->exists($message->attachment_path), 404);

        return Storage::disk('local')->response(
            $message->attachment_path,
            basename($message->attachment_name ?: 'support-image'),
            ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=300']
        );
    }

    private function authorizeAttachment(Request $request, SupportMessage $message): void
    {
        $user = $request->user();
        $canAccess = $user->isAdmin() || $message->conversation->user_id === $user->id;

        abort_unless($canAccess && $message->attachment_path, 404);
    }

    private function editMessage(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): JsonResponse {
        $this->authorizeMessage($request, $message, $admin, $conversation);
        abort_unless($message->sender_id === $request->user()->id, 403, 'You can only edit your own messages.');
        abort_if($message->trashed(), 404);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);
        $message->update([
            'body' => trim($data['body']),
            'edited_at' => now(),
        ]);
        $message->conversation->forceFill(['last_message_at' => now()])->save();

        return response()->json(['message' => $this->serializeMessage($message->fresh(['sender:id,name,role', 'replyTo']))]);
    }

    private function deleteMessage(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): JsonResponse {
        $this->authorizeMessage($request, $message, $admin, $conversation);
        $data = $request->validate(['scope' => ['sometimes', 'in:me,everyone']]);
        $scope = $data['scope'] ?? 'everyone';

        if ($scope === 'me') {
            DB::table('support_message_user_hides')->insertOrIgnore([
                'support_message_id' => $message->id,
                'user_id' => $request->user()->id,
                'created_at' => now(),
            ]);

            return response()->json(['id' => $message->id, 'deleted' => true, 'scope' => 'me']);
        }

        abort_unless($admin || $message->sender_id === $request->user()->id, 403, 'You can only delete your own messages for everyone.');
        abort_if($message->trashed(), 404);

        if ($message->attachment_path && ! Storage::disk('local')->delete($message->attachment_path)) {
            throw new \RuntimeException('The message attachment could not be deleted.');
        }

        DB::table('support_message_user_hides')->where('support_message_id', $message->id)->delete();
        $message->delete();
        $latestMessage = $message->conversation->messages()->latest('id')->first();
        $message->conversation->forceFill(['last_message_at' => $latestMessage?->created_at])->save();

        return response()->json(['id' => $message->id, 'deleted' => true, 'scope' => 'everyone']);
    }

    private function togglePin(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): JsonResponse {
        $this->authorizeMessage($request, $message, $admin, $conversation);
        abort_if($message->trashed(), 404);
        $message->update(['is_pinned' => ! $message->is_pinned]);

        return response()->json(['id' => $message->id, 'is_pinned' => $message->is_pinned]);
    }

    private function toggleStar(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): JsonResponse {
        $this->authorizeMessage($request, $message, $admin, $conversation);
        abort_if($message->trashed(), 404);

        $star = DB::table('support_message_stars')
            ->where('support_message_id', $message->id)
            ->where('user_id', $request->user()->id);

        if ($star->exists()) {
            $star->delete();
            $starred = false;
        } else {
            DB::table('support_message_stars')->insertOrIgnore([
                'support_message_id' => $message->id,
                'user_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $starred = true;
        }

        return response()->json(['id' => $message->id, 'is_starred' => $starred]);
    }

    private function reportMessage(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): JsonResponse {
        $this->authorizeMessage($request, $message, $admin, $conversation);
        abort_if($message->trashed(), 404);
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        DB::table('support_message_reports')->updateOrInsert(
            ['support_message_id' => $message->id, 'reporter_id' => $request->user()->id],
            ['reason' => trim($data['reason']), 'created_at' => now(), 'updated_at' => now()]
        );
        $message->touch();

        return response()->json(['id' => $message->id, 'reported' => true]);
    }

    private function deleteConversation(SupportConversation $conversation): JsonResponse
    {
        $attachmentPaths = $conversation->messages()
            ->whereNotNull('attachment_path')
            ->pluck('attachment_path')
            ->all();

        if ($attachmentPaths && ! Storage::disk('local')->delete($attachmentPaths)) {
            throw new \RuntimeException('One or more chat attachments could not be deleted.');
        }

        DB::transaction(fn () => $conversation->delete());

        return response()->json(['deleted' => true]);
    }

    private function authorizeMessage(
        Request $request,
        SupportMessage $message,
        bool $admin,
        ?SupportConversation $conversation = null
    ): void {
        $message->loadMissing('conversation');
        abort_if($message->trashed(), 404);
        abort_unless($admin || $message->conversation->user_id === $request->user()->id, 404);
        if ($conversation) {
            abort_unless($message->conversation_id === $conversation->id, 404);
        }
    }

    private function messagesFor(
        Request $request,
        SupportConversation $conversation,
        bool $admin
    ): JsonResponse {
        $request->validate([
            'after' => ['sometimes', 'integer', 'min:0'],
            'changed_after' => ['nullable', 'date'],
        ]);
        $after = $request->integer('after', 0);

        $visibleToUser = function ($query) use ($request) {
            return $query->whereNotExists(function ($hidden) use ($request) {
                $hidden->selectRaw('1')
                    ->from('support_message_user_hides')
                    ->whereColumn('support_message_user_hides.support_message_id', 'support_messages.id')
                    ->where('support_message_user_hides.user_id', $request->user()->id);
            });
        };

        $incoming = $visibleToUser($conversation->messages())->where('sender_id', '!=', $request->user()->id);
        if ($admin) {
            $incoming->whereHas('sender', fn (Builder $sender) => $sender->where('role', 'user'));
        } else {
            $incoming->whereHas('sender', fn (Builder $sender) => $sender->where('role', 'admin'));
        }
        $incoming->whereNull('read_at')->update(['read_at' => now()]);

        $withCounts = [
            'stars as is_starred' => fn (Builder $query) => $query->where('users.id', $request->user()->id),
            'reports',
        ];
        $relations = [
            'sender:id,name,role',
            'replyTo.sender:id,name,role',
            'orderItem.order:id,order_number',
            'orderItem.product.images',
        ];
        $newMessages = $visibleToUser($conversation->messages())
            ->with($relations)
            ->withCount($withCounts)
            ->where('id', '>', $after)
            ->orderBy('id')
            ->limit(100)
            ->get();

        $changedMessages = collect();
        if ($request->filled('changed_after') && $after > 0) {
            $changedAfter = \Illuminate\Support\Carbon::parse($request->input('changed_after'))->subSeconds(5);
            $changedMessages = $visibleToUser($conversation->messages())
                ->withTrashed()
                ->with($relations)
                ->withCount($withCounts)
                ->where('id', '<=', $after)
                ->where('updated_at', '>=', $changedAfter)
                ->get();
        }

        $messages = $newMessages
            ->concat($changedMessages)
            ->unique('id')
            ->sortBy('id')
            ->values()
            ->map(fn (SupportMessage $message) => $this->serializeMessage($message));

        $now = now();
        $conversation->refresh();

        return response()->json([
            'messages' => $messages,
            'last_id' => max($after, (int) ($newMessages->last()?->id ?? $after)),
            'change_cursor' => now()->toIso8601String(),
            'presence' => [
                'user_online' => $this->isOnline($conversation->user_last_seen_at),
                'admin_online' => $this->isOnline($conversation->admin_last_seen_at),
                'user_typing' => $this->isTyping($conversation->user_typing_until, $now),
                'admin_typing' => $this->isTyping($conversation->admin_typing_until, $now),
            ],
        ]);
    }

    private function heartbeat(
        Request $request,
        SupportConversation $conversation,
        bool $admin
    ): JsonResponse {
        $data = $request->validate(['typing' => ['sometimes', 'boolean']]);
        $typing = (bool) ($data['typing'] ?? false);
        $prefix = $admin ? 'admin' : 'user';

        $conversation->forceFill([
            $prefix . '_last_seen_at' => now(),
            $prefix . '_typing_until' => $typing ? now()->addSeconds(5) : null,
        ])->save();

        return response()->json(['status' => 'ok']);
    }

    private function send(Request $request, SupportConversation $conversation): JsonResponse
    {
        $data = $request->validate([
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:5000'],
            'order_item_id' => ['nullable', 'integer'],
            'reply_to_message_id' => ['nullable', 'integer', 'exists:support_messages,id'],
            'attachment' => [
                'nullable',
                'required_without:body',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx',
            ],
        ]);

        $orderItem = null;
        if (! $request->user()->isAdmin() && ! empty($data['order_item_id'])) {
            $orderItem = OrderItem::query()
                ->whereKey($data['order_item_id'])
                ->whereHas('order', fn (Builder $query) => $query
                    ->where('user_id', $request->user()->id)
                    ->where('payment_status', 'paid'))
                ->with(['order:id,order_number', 'product.images'])
                ->first();

            if (! $orderItem) {
                return response()->json([
                    'message' => 'The selected order item is not available for this account.',
                    'errors' => ['order_item_id' => ['Choose an item from one of your paid orders.']],
                ], 422);
            }

        }

        $replyTo = null;
        if (! empty($data['reply_to_message_id'])) {
            $replyTo = $conversation->messages()
                ->whereKey($data['reply_to_message_id'])
                ->first();
            if (! $replyTo) {
                return response()->json([
                    'message' => 'The message you are replying to is no longer available.',
                    'errors' => ['reply_to_message_id' => ['Choose an active message in this conversation.']],
                ], 422);
            }
        }

        $storedPath = null;
        $attachment = $request->file('attachment');
        if ($attachment) {
            $storedPath = $attachment->store('support/' . $conversation->id, 'local');
            if (! $storedPath) {
                throw new \RuntimeException('The attachment could not be stored.');
            }
        }

        try {
            $message = $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'body' => isset($data['body']) ? trim($data['body']) : null,
                'attachment_path' => $storedPath,
                'attachment_name' => $attachment ? basename($attachment->getClientOriginalName()) : null,
                'attachment_mime' => $attachment?->getMimeType(),
                'attachment_size' => $attachment?->getSize(),
                'order_item_id' => $orderItem?->id,
                'reply_to_message_id' => $replyTo?->id,
            ]);

            $conversation->forceFill([
                'last_message_at' => $message->created_at,
                $request->user()->isAdmin() ? 'admin_typing_until' : 'user_typing_until' => null,
            ])->save();
        } catch (\Throwable $exception) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }

            throw $exception;
        }

        $adminNotified = null;
        if (! $request->user()->isAdmin()) {
            $adminNotified = AdminEmailNotifier::send('New customer support message', [
                'Customer' => $request->user()->name.' <'.$request->user()->email.'>',
                'Message' => $message->body ?: 'Customer attached a file.',
                'Support inbox' => route('admin.support'),
            ]);
        }

        return response()->json([
            'message' => $this->serializeMessage($message->load(['sender:id,name,role', 'replyTo'])),
            'admin_notified' => $adminNotified,
            'notification_warning' => $adminNotified === false
                ? 'Your message was saved, but its admin email alert could not be delivered.'
                : null,
        ], 201);
    }

    private function serializeMessage(SupportMessage $message): array
    {
        if ($message->order_item_id) {
            $message->loadMissing(['orderItem.order:id,order_number', 'orderItem.product.images']);
        }

        $message->loadMissing(['sender:id,name,role', 'replyTo.sender:id,name,role']);
        if (! array_key_exists('is_starred', $message->getAttributes())) {
            $message->setAttribute('is_starred', $message->stars()->where('users.id', auth()->id())->exists());
        }
        if (! array_key_exists('reports_count', $message->getAttributes())) {
            $message->loadCount('reports');
        }

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->name,
            'sender_role' => $message->sender?->role,
            'body' => $message->trashed() ? null : $message->body,
            'deleted' => $message->trashed(),
            'edited_at' => $message->edited_at?->toIso8601String(),
            'is_pinned' => (bool) $message->is_pinned,
            'is_starred' => (bool) $message->is_starred,
            'report_count' => (int) $message->reports_count,
            'reply_to_message_id' => $message->reply_to_message_id,
            'reply' => $message->replyTo ? [
                'id' => $message->replyTo->id,
                'sender_name' => $message->replyTo->sender?->name,
                'body' => $message->replyTo->trashed() ? null : $message->replyTo->body,
                'deleted' => $message->replyTo->trashed(),
                'attachment' => $message->replyTo->trashed() ? null : $this->serializeAttachment($message->replyTo),
            ] : null,
            'context' => $message->orderItem
                ? $this->serializeOrderContext($message->orderItem)
                : null,
            'attachment' => $message->trashed() ? null : $this->serializeAttachment($message),
            'created_at' => $message->created_at?->toIso8601String(),
            'updated_at' => $message->updated_at?->toIso8601String(),
            'read_at' => $message->read_at?->toIso8601String(),
        ];
    }

    private function serializeAttachment(SupportMessage $message): ?array
    {
        if (! $message->attachment_path) {
            return null;
        }

        return [
            'name' => $message->attachment_name,
            'mime' => $message->attachment_mime,
            'size' => $message->attachment_size,
            'url' => route('support.attachments.download', $message),
            'is_image' => str_starts_with((string) $message->attachment_mime, 'image/'),
            'preview_url' => in_array($message->attachment_mime, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'], true)
                ? route('support.attachments.preview', $message)
                : null,
        ];
    }

    private function serializeOrderContext(OrderItem $item): array
    {
        $item->loadMissing(['order:id,order_number', 'order.items.product.images', 'product.images']);
        $imagePath = $item->product?->images->first()?->path;
        $orderItems = $item->order?->items ?? collect([$item]);

        return [
            'item_id' => $item->id,
            'order_number' => $item->order?->order_number,
            'product_name' => $item->product_name,
            'quantity' => $item->qty,
            'line_total' => $item->line_total,
            'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
            'product_url' => $item->product?->slug
                ? route('product.show', $item->product->slug)
                : null,
            'items' => $orderItems->map(function (OrderItem $orderItem): array {
                $imagePath = $orderItem->product?->images->first()?->path;

                return [
                    'item_id' => $orderItem->id,
                    'product_name' => $orderItem->product_name,
                    'image_url' => $imagePath ? asset('storage/' . $imagePath) : null,
                    'product_url' => $orderItem->product?->slug
                        ? route('product.show', $orderItem->product->slug)
                        : null,
                ];
            })->values()->all(),
        ];
    }

    private function isOnline(?\Illuminate\Support\Carbon $lastSeen): bool
    {
        return $lastSeen !== null && $lastSeen->greaterThan(now()->subSeconds(self::ONLINE_WINDOW_SECONDS));
    }

    private function isTyping(?\Illuminate\Support\Carbon $typingUntil, \Illuminate\Support\Carbon $now): bool
    {
        return $typingUntil !== null && $typingUntil->greaterThan($now);
    }
}
