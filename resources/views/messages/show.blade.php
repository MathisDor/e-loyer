@extends('layouts.app')

@section('title', 'Conversation avec ' . $otherUser->name)

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white border-b border-gray-100 sticky top-16 z-10">
            <div class="px-4 py-4 flex items-center gap-4">
                <a href="{{ route('messages.index') }}" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-arrow-left text-lg"></i>
                </a>
                <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" class="w-10 h-10 rounded-full object-cover">
                <div class="flex-1">
                    <h1 class="font-semibold text-gray-900">{{ $otherUser->name }}</h1>
                    @if($conversation->property)
                        <a href="{{ route('properties.show', $conversation->property) }}" class="text-xs text-gabon-green hover:underline">
                            {{ $conversation->property->title }}
                        </a>
                    @endif
                </div>
                <a href="{{ route('user.profile', $otherUser) }}" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="p-4 space-y-4 min-h-[calc(100vh-16rem)]" id="messages-container">
            @foreach($messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-xs lg:max-w-md">
                        @if($message->sender_id !== auth()->id())
                            <div class="flex items-center gap-2 mb-1">
                                <img src="{{ $message->sender->avatar_url }}" alt="{{ $message->sender->name }}" class="w-6 h-6 rounded-full">
                                <span class="text-xs text-gray-500">{{ $message->sender->name }}</span>
                            </div>
                        @endif
                        <div class="chat-bubble {{ $message->sender_id === auth()->id() ? 'sent' : 'received' }}">
                            <p>{{ $message->message }}</p>
                            @if($message->attachment)
                                <a href="{{ $message->attachment_url }}" target="_blank" class="flex items-center gap-2 mt-2 text-sm opacity-80 hover:opacity-100">
                                    <i class="fas fa-paperclip"></i>
                                    Pièce jointe
                                </a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1 {{ $message->sender_id === auth()->id() ? 'text-right' : '' }}">
                            {{ $message->created_at->format('H:i') }}
                            @if($message->sender_id === auth()->id())
                                · {{ $message->is_read ? 'Lu' : 'Envoyé' }}
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Input -->
        <div class="bg-white border-t border-gray-100 sticky bottom-0 p-4">
            <form action="{{ route('messages.store', $conversation) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-3">
                @csrf
                <label class="text-gray-400 hover:text-gray-600 cursor-pointer p-2">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" name="attachment" class="hidden" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                </label>
                <div class="flex-1">
                    <textarea name="message" rows="1" required
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl resize-none focus:border-gabon-green"
                              placeholder="Écrivez votre message..."></textarea>
                </div>
                <button type="submit" class="p-3 bg-gabon-green text-white rounded-xl hover:bg-gabon-green/90">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
// Scroll to bottom on load
document.getElementById('messages-container').scrollIntoView({ block: 'end' });
</script>
@endsection


