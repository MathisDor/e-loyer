<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Property;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * Liste des conversations
     */
    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::forUser($user->id)
            ->with(['userOne', 'userTwo', 'property', 'messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('messages.index', compact('conversations'));
    }

    /**
     * Afficher une conversation
     */
    public function show(Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        // Marquer les messages comme lus
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();

        $otherUser = $conversation->getOtherUser(Auth::user());

        return view('messages.show', compact('conversation', 'messages', 'otherUser'));
    }

    /**
     * Démarrer une conversation
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'property_id' => ['nullable', 'exists:properties,id'],
        ]);

        $otherUser = User::findOrFail($validated['user_id']);
        $property = isset($validated['property_id']) 
            ? Property::find($validated['property_id']) 
            : null;

        $conversation = Conversation::findOrCreateBetween(
            Auth::user(),
            $otherUser,
            $property
        );

        return redirect()->route('messages.show', $conversation);
    }

    /**
     * Envoyer un message
     */
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorize('view', $conversation);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'attachment' => $attachmentPath,
        ]);

        $conversation->updateLastMessageTime();

        // Notifier l'autre utilisateur
        $otherUser = $conversation->getOtherUser(Auth::user());
        Notification::send(
            $otherUser,
            'new_message',
            'Nouveau message',
            Auth::user()->name . ' vous a envoyé un message',
            route('messages.show', $conversation)
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message->load('sender'),
            ]);
        }

        return back();
    }

    /**
     * Contacter le propriétaire d'une propriété
     */
    public function contactOwner(Request $request, Property $property)
    {
        if (Auth::id() === $property->owner_id) {
            return back()->with('error', 'Vous ne pouvez pas vous contacter vous-même.');
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $conversation = Conversation::findOrCreateBetween(
            Auth::user(),
            $property->owner,
            $property
        );

        $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $conversation->updateLastMessageTime();

        // Notifier le propriétaire
        Notification::send(
            $property->owner,
            'new_message',
            'Nouveau message',
            Auth::user()->name . " vous a contacté à propos de \"{$property->title}\"",
            route('messages.show', $conversation)
        );

        return redirect()->route('messages.show', $conversation)
            ->with('success', 'Message envoyé au propriétaire.');
    }

    /**
     * Marquer tous les messages comme lus
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $conversationIds = Conversation::forUser($user->id)->pluck('id');

        Message::whereIn('conversation_id', $conversationIds)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return back()->with('success', 'Tous les messages ont été marqués comme lus.');
    }
}


