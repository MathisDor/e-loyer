@extends('layouts.app')

@section('title', 'Réserver une visite - ' . $property->title)

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-12">
    <div class="max-w-5xl mx-auto px-4">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('properties.show', $property) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour à la propriété</span>
            </a>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Form -->
            <div class="lg:col-span-2 space-y-6">
                <div class="space-y-2">
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                        <i class="fas fa-calendar-check"></i> Étapes : choix du créneau → paiement → rappel auto
                    </p>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Réserver une visite</h1>
                    <p class="text-gray-600">Choisissez un créneau, payez les frais de visite, puis nous rappelons 24h avant.</p>
                </div>
                
                <form action="{{ route('visits.store', $property) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <!-- Date and Time -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="font-bold text-gray-900 leading-tight">Date et heure de la visite</h2>
                                <p class="text-sm text-gray-500">Planifiez au moins 24h à l'avance.</p>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date et heure <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" id="scheduled_at" name="scheduled_at" 
                                       value="{{ old('scheduled_at', now()->addDay()->format('Y-m-d\T09:00')) }}" 
                                       min="{{ now()->format('Y-m-d\TH:i') }}" required
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                @error('scheduled_at')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="text-sm text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Vous recevrez un rappel 24h avant le créneau choisi.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-contract text-yellow-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 mb-2">Conditions de visite</h3>
                                <ul class="text-sm text-gray-700 space-y-2">
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                        <span>Paiement requis pour confirmer la réservation</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                        <span>Tarif = prix de base + 8% de commission + 400 FCFA de frais de service</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                        <span>Si vous acceptez la propriété après visite : 1er versement (1/6 du loyer)</span>
                                    </li>
                                    <li class="flex items-start gap-2">
                                        <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                        <span>En cas de refus, la propriété reste disponible</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="accept_terms" value="1" required
                                   class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <span class="text-sm text-gray-700">
                                J'accepte les <a href="#" class="text-green-600 hover:underline">conditions de visite</a> et les <a href="#" class="text-green-600 hover:underline">règles de location</a>
                            </span>
                        </label>
                        @error('accept_terms')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-green-500/25 transition-all">
                        <i class="fas fa-calendar-check mr-2"></i>Réserver la visite
                    </button>
                </form>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Property Summary -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <div class="mb-4">
                        <img src="{{ $property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop' }}" 
                             alt="{{ $property->title }}" 
                             class="w-full h-48 object-cover rounded-xl mb-4">
                        <h3 class="font-bold text-gray-900 mb-2">{{ $property->title }}</h3>
                        <p class="text-sm text-gray-600 flex items-center gap-2 mb-4">
                            <i class="fas fa-map-marker-alt text-green-600"></i>
                            {{ $property->full_address }}
                        </p>
                    </div>
                    
                    <!-- Visit Price Breakdown -->
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Prix de base</span>
                            <span class="font-semibold text-gray-900">{{ number_format($amounts['base_price'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Commission (8%)</span>
                            <span class="font-semibold text-gray-900">+ {{ number_format($amounts['commission'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Frais de service</span>
                            <span class="font-semibold text-gray-900">+ {{ number_format($amounts['service_fee'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 flex items-center justify-between">
                            <span class="text-lg font-bold text-gray-900">Total à payer</span>
                            <span class="text-2xl font-bold text-green-600">{{ number_format($amounts['total_amount'], 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    
                    @if($property->visit_assigned_to !== 'self' && $property->visitAssignedUser)
                        <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-200">
                            <p class="text-xs text-blue-700 font-medium mb-1">Visite assignée à :</p>
                            <p class="text-sm font-semibold text-blue-900">{{ $property->visitAssignedUser->name }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

