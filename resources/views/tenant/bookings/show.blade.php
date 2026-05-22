@extends('layouts.app')

@section('title', 'Détails de la réservation')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-10">
    <div class="max-w-5xl mx-auto px-4">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('dashboard.tenant.bookings') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour aux réservations</span>
            </a>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Booking Status Card -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h1 class="text-2xl font-bold text-gray-900">Détails de la réservation</h1>
                        <span class="px-4 py-2 rounded-lg text-sm font-bold
                            {{ $booking->status === 'en_attente' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $booking->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $booking->status === 'payee' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $booking->status === 'terminee' ? 'bg-gray-100 text-gray-700' : '' }}
                            {{ $booking->status === 'refusee' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $booking->status_name }}
                        </span>
                    </div>
                    
                    <!-- Dates -->
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Date de début</p>
                            <p class="font-bold text-gray-900">{{ $booking->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Date de fin</p>
                            <p class="font-bold text-gray-900">{{ $booking->end_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Durée</p>
                            <p class="font-bold text-gray-900">{{ $booking->duration_months }} mois</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-sm text-gray-500 mb-1">Date de création</p>
                            <p class="font-bold text-gray-900">{{ $booking->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    @if($booking->tenant_message)
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <p class="text-sm font-semibold text-blue-800 mb-2">Votre message au propriétaire :</p>
                            <p class="text-blue-900">{{ $booking->tenant_message }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Property Details -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Informations sur le bien</h2>
                    <a href="{{ route('properties.show', $booking->property) }}" class="block">
                        @php
                            $imageIds = ['1502672260266-1c1ef2d93688', '1560448204-e02f11c3d0e2', '1522708323590-d24dbb6b0267', '1493809842364-78817add7ffb', '1560185007-cde436f6a4d0', '1484154218962-a197022b5858', '1512917774080-9991f1c4c750', '1600596542815-ffad4c1539a9'];
                            $image = 'https://images.unsplash.com/photo-' . $imageIds[$booking->property->id % count($imageIds)] . '?w=800&h=400&fit=crop';
                        @endphp
                        <img src="{{ $image }}" alt="{{ $booking->property->title }}" class="w-full h-48 object-cover rounded-xl mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $booking->property->title }}</h3>
                        <p class="text-gray-600">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            {{ $booking->property->city }}
                            @if($booking->property->neighborhood)
                                · {{ $booking->property->neighborhood }}
                            @endif
                        </p>
                    </a>
                </div>
                
                <!-- Payments -->
                @if($booking->payments->count() > 0)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Historique des paiements</h2>
                        <div class="space-y-3">
                            @foreach($booking->payments as $payment)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $payment->description }}</p>
                                        <p class="text-sm text-gray-500">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                        <span class="text-xs px-2 py-1 rounded
                                            {{ $payment->status === 'confirme' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $payment->status === 'confirme' ? 'Confirmé' : 'En attente' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Price Summary -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sticky top-24">
                    <h3 class="font-bold text-gray-900 mb-4">Résumé financier</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Loyer mensuel</span>
                            <span class="font-semibold">{{ number_format($booking->monthly_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Caution</span>
                            <span class="font-semibold">{{ number_format($booking->deposit_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between mb-1">
                                <span class="font-semibold text-gray-900">Total</span>
                                <span class="text-xl font-bold text-green-600">{{ number_format($booking->total_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <p class="text-xs text-gray-500">pour {{ $booking->duration_months }} mois</p>
                        </div>
                    </div>
                </div>
                
                <!-- Owner Info -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Propriétaire</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $booking->owner->name }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->owner->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('messages.index') }}?user={{ $booking->owner_id }}" 
                       class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                        <i class="fas fa-comment mr-2"></i>Contacter
                    </a>
                </div>
                
                <!-- Actions -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('properties.show', $booking->property) }}" 
                           class="block w-full text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-colors">
                            <i class="fas fa-home mr-2"></i>Voir le bien
                        </a>
                        @if($booking->status === 'active' || $booking->status === 'payee')
                            <a href="{{ route('dashboard.tenant.bookings.pay-rent', $booking) }}" 
                               class="block w-full text-center px-4 py-2 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-money-bill-wave mr-2"></i>Payer le loyer
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

