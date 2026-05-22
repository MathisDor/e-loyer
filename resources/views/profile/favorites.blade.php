@extends('layouts.app')

@section('title', 'Mes favoris')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Mes favoris</h1>
        
        @if($favorites->count() > 0)
            <div class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($favorites as $property)
                    @include('components.property-card', ['property' => $property])
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $favorites->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-red-50 rounded-full flex items-center justify-center">
                    <i class="fas fa-heart text-3xl text-red-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucun favori</h3>
                <p class="text-gray-600 mb-6">Ajoutez des propriétés à vos favoris pour les retrouver facilement</p>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gabon-green text-white rounded-xl font-semibold">
                    <i class="fas fa-search"></i>Explorer les propriétés
                </a>
            </div>
        @endif
    </div>
</div>
@endsection


