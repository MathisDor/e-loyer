@extends('layouts.app')

@section('title', 'Mes réservations')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-10">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Mes réservations</h1>
                    <p class="text-gray-600 mt-1">Gérez toutes vos demandes de location</p>
                </div>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                    <i class="fas fa-search"></i>
                    <span class="hidden sm:inline">Rechercher un logement</span>
                </a>
            </div>
            
            <!-- Filters -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('dashboard.tenant.bookings') }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ !request('status') ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Toutes
                </a>
                <a href="{{ route('dashboard.tenant.bookings', ['status' => 'en_attente']) }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'en_attente' ? 'bg-yellow-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    En attente
                </a>
                <a href="{{ route('dashboard.tenant.bookings', ['status' => 'active']) }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'active' ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Actives
                </a>
                <a href="{{ route('dashboard.tenant.bookings', ['status' => 'payee']) }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'payee' ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Payées
                </a>
                <a href="{{ route('dashboard.tenant.bookings', ['status' => 'terminee']) }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'terminee' ? 'bg-gray-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Terminées
                </a>
                <a href="{{ route('dashboard.tenant.bookings', ['status' => 'refusee']) }}" 
                   class="px-4 py-2 rounded-xl font-medium transition-colors {{ request('status') === 'refusee' ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Refusées
                </a>
            </div>
        </div>
        
        <!-- Bookings List -->
        @if($bookings->count() > 0)
            <div class="grid gap-6">
                @foreach($bookings as $booking)
                    @php
                        $imageIds = ['1502672260266-1c1ef2d93688', '1560448204-e02f11c3d0e2', '1522708323590-d24dbb6b0267', '1493809842364-78817add7ffb', '1560185007-cde436f6a4d0', '1484154218962-a197022b5858', '1512917774080-9991f1c4c750', '1600596542815-ffad4c1539a9'];
                        $image = 'https://images.unsplash.com/photo-' . $imageIds[$booking->property->id % count($imageIds)] . '?w=400&h=300&fit=crop';
                    @endphp
                    
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row gap-6">
                                <!-- Image -->
                                <div class="lg:w-48 flex-shrink-0">
                                    <img src="{{ $image }}" alt="{{ $booking->property->title }}" class="w-full h-40 lg:h-full object-cover rounded-xl">
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $booking->property->title }}</h3>
                                            <p class="text-gray-500">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                {{ $booking->property->city }}
                                                @if($booking->property->neighborhood)
                                                    · {{ $booking->property->neighborhood }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold
                                            {{ $booking->status === 'en_attente' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                            {{ $booking->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                                            {{ $booking->status === 'payee' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $booking->status === 'terminee' ? 'bg-gray-100 text-gray-700' : '' }}
                                            {{ $booking->status === 'refusee' ? 'bg-red-100 text-red-700' : '' }}">
                                            {{ $booking->status_name }}
                                        </span>
                                    </div>
                                    
                                    <!-- Details -->
                                    <div class="grid sm:grid-cols-2 gap-4 mb-4">
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fas fa-calendar-alt text-gray-400 w-5"></i>
                                            <span><strong>Début :</strong> {{ $booking->start_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fas fa-calendar-check text-gray-400 w-5"></i>
                                            <span><strong>Fin :</strong> {{ $booking->end_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fas fa-clock text-gray-400 w-5"></i>
                                            <span><strong>Durée :</strong> {{ $booking->duration_months }} mois</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <i class="fas fa-user text-gray-400 w-5"></i>
                                            <span><strong>Propriétaire :</strong> {{ $booking->owner->name }}</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Price -->
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-4">
                                        <div>
                                            <p class="text-sm text-gray-500">Loyer mensuel</p>
                                            <p class="text-2xl font-bold text-green-600">{{ number_format($booking->monthly_amount, 0, ',', ' ') }} FCFA</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500">Total</p>
                                            <p class="text-xl font-bold text-gray-900">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('dashboard.tenant.bookings.show', $booking) }}" 
                                           class="px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                            <i class="fas fa-eye mr-2"></i>Voir les détails
                                        </a>
                                        <a href="{{ route('properties.show', $booking->property) }}" 
                                           class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-home mr-2"></i>Voir le bien
                                        </a>
                                        @if($booking->status === 'active' || $booking->status === 'payee')
                                            <a href="{{ route('messages.index') }}?user={{ $booking->owner_id }}" 
                                               class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-comment mr-2"></i>Contacter
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <!-- Message -->
                                    @if($booking->tenant_message)
                                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-xl">
                                            <p class="text-xs text-blue-700 font-semibold mb-1">Votre message :</p>
                                            <p class="text-sm text-blue-800">{{ $booking->tenant_message }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $bookings->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-calendar-times text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucune réservation</h3>
                <p class="text-gray-600 mb-6">
                    @if(request('status'))
                        Aucune réservation avec ce statut.
                    @else
                        Vous n'avez pas encore de réservation.
                    @endif
                </p>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                    <i class="fas fa-search"></i>
                    <span class="hidden sm:inline">Rechercher un logement</span>
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

