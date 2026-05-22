@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Messages</h1>
            @if($conversations->count() > 0)
                <form action="{{ route('messages.mark-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gabon-green font-medium text-sm hover:underline">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>
        
        @if($conversations->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                @foreach($conversations as $conversation)
                    @php
                        $otherUser = $conversation->getOtherUser(auth()->user());
                        $lastMessage = $conversation->last_message;
                        $unreadCount = $conversation->getUnreadCountFor(auth()->user());
                    @endphp
                    <a href="{{ route('messages.show', $conversation) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors {{ $unreadCount > 0 ? 'bg-gabon-green/5' : '' }}">
                        <div class="relative">
                            <img src="{{ $otherUser->avatar_url }}" alt="{{ $otherUser->name }}" class="w-14 h-14 rounded-full object-cover">
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-gabon-green text-white text-xs rounded-full flex items-center justify-center font-bold">
                                    {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                                </span>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="font-semibold text-gray-900 {{ $unreadCount > 0 ? 'font-bold' : '' }}">{{ $otherUser->name }}</h3>
                                @if($lastMessage)
                                    <span class="text-xs text-gray-400">{{ $lastMessage->created_at->diffForHumans() }}</span>
                                @endif
                            </div>
                            @if($conversation->property)
                                <p class="text-xs text-gabon-green font-medium truncate">{{ $conversation->property->title }}</p>
                            @endif
                            @if($lastMessage)
                                <p class="text-sm text-gray-500 truncate {{ $unreadCount > 0 ? 'font-medium text-gray-700' : '' }}">
                                    @if($lastMessage->sender_id === auth()->id())
                                        <span class="text-gray-400">Vous: </span>
                                    @endif
                                    {{ $lastMessage->message }}
                                </p>
                            @endif
                        </div>
                        <i class="fas fa-chevron-right text-gray-300"></i>
                    </a>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $conversations->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-comments text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune conversation</h3>
                <p class="text-gray-600 mb-6">Contactez un propriétaire pour démarrer une conversation</p>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gabon-green text-white rounded-xl font-semibold">
                    <i class="fas fa-search"></i>Rechercher des propriétés
                </a>
            </div>
        @endif
    </div>
</div>
@endsection


