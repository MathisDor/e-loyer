@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            @if($notifications->where('read_at', null)->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-gabon-green font-medium text-sm hover:underline">
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>
        
        @if($notifications->count() > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                @foreach($notifications as $notification)
                    <div class="p-4 flex items-start gap-4 {{ !$notification->is_read ? 'bg-gabon-green/5' : '' }}">
                        <div class="w-10 h-10 bg-{{ $notification->color }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-{{ $notification->icon }} text-{{ $notification->color }}-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-semibold text-gray-900 {{ !$notification->is_read ? 'font-bold' : '' }}">{{ $notification->title }}</h3>
                                    <p class="text-gray-600 text-sm mt-0.5">{{ $notification->message }}</p>
                                </div>
                                <span class="text-xs text-gray-400 whitespace-nowrap">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-4 mt-2">
                                @if($notification->action_url)
                                    <form action="{{ route('notifications.read', $notification) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-gabon-green font-medium text-sm hover:underline">
                                            Voir →
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('notifications.destroy', $notification) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 text-sm">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-bell-slash text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune notification</h3>
                <p class="text-gray-600">Vous n'avez pas de notifications pour le moment</p>
            </div>
        @endif
    </div>
</div>
@endsection


