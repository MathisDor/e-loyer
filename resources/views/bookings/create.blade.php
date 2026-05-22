@extends('layouts.app')

@section('title', 'Réserver - ' . $property->title)

@php
    // Calcul du prix affiché (loyer + 8% commission + 400 FCFA frais)
    $basePrice = $property->monthly_price;
    $commission = round($basePrice * 0.08);
    $serviceFee = 400;
    $displayedPrice = $basePrice + $commission + $serviceFee;
    
    // Image Unsplash
    $imageIds = ['1502672260266-1c1ef2d93688', '1560448204-e02f11c3d0e2', '1522708323590-d24dbb6b0267', '1493809842364-78817add7ffb', '1560185007-cde436f6a4d0', '1484154218962-a197022b5858', '1512917774080-9991f1c4c750', '1600596542815-ffad4c1539a9'];
    $image = 'https://images.unsplash.com/photo-' . $imageIds[$property->id % count($imageIds)] . '?w=600&h=400&fit=crop';
@endphp

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
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Demande de réservation</h1>
                    <p class="text-gray-600">Remplissez le formulaire ci-dessous pour faire une demande de réservation</p>
                </div>
                
                <form action="{{ route('bookings.store', $property) }}" method="POST" class="space-y-6" id="bookingForm">
                    @csrf
                    
                    <!-- Dates -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-600"></i>
                            </div>
                            <h2 class="font-bold text-gray-900">Période de location</h2>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Date de début <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date" 
                                       value="{{ old('start_date', now()->addDays(7)->format('Y-m-d')) }}" 
                                       min="{{ now()->format('Y-m-d') }}" required
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="duration_months" class="block text-sm font-medium text-gray-700 mb-2">
                                    Durée (mois) <span class="text-red-500">*</span>
                                </label>
                                <select id="duration_months" name="duration_months" required
                                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all">
                                    @for($i = 1; $i <= 24; $i++)
                                        <option value="{{ $i }}" {{ old('duration_months', 6) == $i ? 'selected' : '' }}>{{ $i }} mois</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message -->
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-comment-dots text-purple-600"></i>
                            </div>
                            <h2 class="font-bold text-gray-900">Message au propriétaire</h2>
                        </div>
                        <textarea name="message" rows="5" 
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition-all"
                                  placeholder="Présentez-vous et expliquez pourquoi vous êtes intéressé par ce logement...">{{ old('message') }}</textarea>
                        <p class="text-sm text-gray-500 mt-2">
                            <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                            Un message personnalisé augmente vos chances d'acceptation
                        </p>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="bg-yellow-50 border-2 border-yellow-200 rounded-2xl p-6">
                        <div class="flex items-start gap-3 mb-4">
                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-contract text-yellow-600"></i>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 mb-2">Règles et conditions de location</h3>
                                <p class="text-sm text-gray-700 mb-4">En soumettant cette demande, vous acceptez les conditions suivantes :</p>
                            </div>
                        </div>
                        
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                <p><strong>Paiement :</strong> Le premier paiement (loyer + caution) doit être effectué dans les 48h suivant l'acceptation de votre demande.</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                <p><strong>Caution :</strong> La caution sera restituée à la fin du bail, déduction faite des éventuels dommages ou dettes.</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                <p><strong>Respect du bien :</strong> Vous vous engagez à maintenir le logement en bon état et à respecter les règles de copropriété.</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                <p><strong>Paiement mensuel :</strong> Le loyer doit être payé avant le 5 de chaque mois, sous peine de pénalités de retard.</p>
                            </div>
                            <div class="flex items-start gap-2">
                                <i class="fas fa-check-circle text-green-600 mt-0.5"></i>
                                <p><strong>Résiliation :</strong> Un préavis d'un mois est requis pour toute résiliation anticipée du bail.</p>
                            </div>
                        </div>
                        
                        <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                            <div class="flex items-start gap-2">
                                <i class="fas fa-gavel text-red-600 mt-0.5"></i>
                                <div>
                                    <p class="font-bold text-red-800 mb-1">⚠️ Poursuites judiciaires</p>
                                    <p class="text-sm text-red-700">
                                        En cas de non-respect des termes du contrat de location, notamment le non-paiement du loyer, les dommages causés au bien, ou la violation des règles de vie, le propriétaire se réserve le droit d'engager des poursuites judiciaires conformément à la législation gabonaise en vigueur. Les frais de justice et les intérêts de retard seront à votre charge.
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <label class="flex items-start gap-3 mt-4 cursor-pointer">
                            <input type="checkbox" name="accept_terms" required
                                   class="mt-1 w-5 h-5 text-green-600 border-gray-300 rounded focus:ring-green-500">
                            <span class="text-sm text-gray-700">
                                J'accepte les <strong>règles et conditions de location</strong> et j'ai pris connaissance des <strong>risques de poursuites judiciaires</strong> en cas de non-respect du contrat.
                                <span class="text-red-500">*</span>
                            </span>
                        </label>
                    </div>
                    
                    <!-- Submit -->
                    <button type="submit" class="w-full py-4 bg-green-600 text-white rounded-xl font-bold text-lg hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/25 transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer la demande
                    </button>
                    
                    <p class="text-sm text-gray-500 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Vous ne serez pas débité maintenant. Le paiement n'intervient qu'après acceptation de votre demande par le propriétaire.
                    </p>
                </form>
            </div>
            
            <!-- Property Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-24">
                    <img src="{{ $image }}" alt="{{ $property->title }}" class="w-full h-48 object-cover rounded-xl mb-4">
                    
                    <h3 class="font-bold text-gray-900 text-lg mb-1">{{ $property->title }}</h3>
                    <p class="text-gray-500 text-sm mb-4">
                        <i class="fas fa-map-marker-alt mr-1"></i>{{ $property->city }}
                        @if($property->neighborhood)
                            · {{ $property->neighborhood }}
                        @endif
                    </p>
                    
                    <!-- Price Breakdown -->
                    <div class="border-t border-gray-100 pt-4 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Loyer mensuel</span>
                            <span class="font-semibold text-gray-900">{{ number_format($basePrice, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Commission (8%)</span>
                            <span class="text-gray-600">+ {{ number_format($commission, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Frais de service</span>
                            <span class="text-gray-600">+ {{ number_format($serviceFee, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="border-t border-gray-200 pt-3 mt-3">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900">Prix affiché</span>
                                <span class="text-xl font-bold text-green-600">{{ number_format($displayedPrice, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">par mois</p>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-gray-600">Caution</span>
                            <span class="font-semibold">{{ $property->formatted_deposit }}</span>
                        </div>
                    </div>
                    
                    <!-- First Payment Summary -->
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mt-4">
                        <p class="text-sm font-semibold text-green-800 mb-2">
                            <i class="fas fa-calculator mr-1"></i>Premier paiement
                        </p>
                        <div class="space-y-1 text-sm text-green-700">
                            <div class="flex justify-between">
                                <span>1er mois (avec commission)</span>
                                <span>{{ number_format($displayedPrice, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Caution</span>
                                <span>{{ number_format($property->deposit ?? $basePrice, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="border-t border-green-200 pt-2 mt-2 flex justify-between font-bold">
                                <span>Total</span>
                                <span class="text-lg">{{ number_format($displayedPrice + ($property->deposit ?? $basePrice), 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Property Details -->
                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            @if($property->bedrooms)
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-bed text-gray-400"></i>
                                    <span>{{ $property->bedrooms }} ch.</span>
                                </div>
                            @endif
                            @if($property->bathrooms)
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-bath text-gray-400"></i>
                                    <span>{{ $property->bathrooms }} sdb</span>
                                </div>
                            @endif
                            @if($property->beds)
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-bed text-gray-400"></i>
                                    <span>{{ $property->beds }} lits</span>
                                </div>
                            @endif
                            @if($property->surface)
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-vector-square text-gray-400"></i>
                                    <span>{{ $property->surface }} m²</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
