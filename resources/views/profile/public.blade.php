@extends('layouts.app')

@section('title', $user->name . ' - Profil')

@section('content')
<div class="bg-gray-50 min-h-screen py-10">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-4">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-semibold">Retour</span>
            </a>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 lg:p-8">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-2xl object-cover border-2 border-gray-100">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        @if($user->is_verified)
                            <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">Vérifié</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ ucfirst($user->user_type) }}</p>
                    @if($user->city)
                        <p class="text-sm text-gray-600 mt-1"><i class="fas fa-map-marker-alt text-green-600 mr-1"></i>{{ $user->city }} @if($user->neighborhood) · {{ $user->neighborhood }} @endif</p>
                    @endif
                    @if($user->bio)
                        <p class="text-gray-700 mt-3">{{ $user->bio }}</p>
                    @endif
                </div>
            </div>

            @if($properties->count() > 0)
                <div class="mt-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Propriétés de {{ $user->name }}</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        @foreach($properties as $property)
                            @php
                                $image = $property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop';
                            @endphp
                            <a href="{{ route('properties.show', $property) }}" class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden hover:shadow-lg transition">
                                <div class="relative h-40">
                                    <img src="{{ $image }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-green-600 text-white text-xs font-semibold rounded-lg">{{ $property->type_name }}</span>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-bold text-gray-900 truncate">{{ $property->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1"><i class="fas fa-map-marker-alt text-green-500 mr-1"></i>{{ $property->city }}</p>
                                    <p class="text-lg font-bold text-green-600 mt-2">{{ $property->formatted_price }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($user->reviewsReceived->count() > 0)
                <div class="mt-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Avis</h2>
                    <div class="space-y-3">
                        @foreach($user->reviewsReceived as $review)
                            <div class="p-4 border border-gray-100 rounded-xl bg-gray-50">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <span class="font-semibold text-gray-900">{{ number_format($review->rating, 1) }}/5</span>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>
                                <p class="text-gray-700">{{ $review->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

