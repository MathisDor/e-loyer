@extends('layouts.app')

@section('title', 'Détails de la visite')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour</span>
            </a>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="flex items-start justify-between gap-4 flex-col md:flex-row mb-6">
                <div>
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 mb-3">
                        <i class="fas fa-route"></i> Suivi de votre visite
                    </p>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-1">Visite : {{ $visit->property->title }}</h1>
                    <p class="text-gray-600 flex items-center gap-2">
                        <i class="far fa-calendar text-green-600"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-4 py-2 rounded-xl font-semibold
                        @if($visit->status === 'reservee') bg-blue-100 text-blue-700
                        @elseif($visit->status === 'en_cours') bg-yellow-100 text-yellow-700
                        @elseif($visit->status === 'acceptee') bg-green-100 text-green-700
                        @elseif($visit->status === 'refusee') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $visit->status_name }}
                    </span>
                </div>
            </div>
            
            <!-- Info cards -->
            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Montant payé</p>
                    <p class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-money-bill-wave text-green-600"></i>{{ $visit->formatted_total_amount }}
                    </p>
                </div>
                <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Créneau</p>
                    <p class="text-sm text-gray-800">{{ $visit->scheduled_at->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-500">{{ $visit->scheduled_at->format('H:i') }}</p>
                </div>
                <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Visite assignée</p>
                    <p class="text-sm text-gray-800">
                        @if($visit->assignedUser)
                            <i class="fas fa-user-check text-blue-600 mr-1"></i>{{ $visit->assignedUser->name }}
                        @else
                            Non attribuée
                        @endif
                    </p>
                </div>
            </div>
            
            <!-- Actions + forms -->
            <div class="grid lg:grid-cols-2 gap-6 mb-8">
                <div class="space-y-6">
                    @if($visit->status === 'reservee' && $visit->is_paid && $visit->assigned_user_id === Auth::id())
                        <form action="{{ route('visits.start', $visit) }}" method="POST" class="p-4 bg-yellow-50 border border-yellow-200 rounded-2xl">
                            @csrf
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-play text-yellow-700"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Prêt à démarrer ?</p>
                                    <p class="text-sm text-gray-600">Commence la visite avec le locataire.</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full py-3 bg-yellow-600 text-white rounded-xl font-semibold hover:bg-yellow-700 transition-colors">
                                Démarrer la visite
                            </button>
                        </form>
                    @endif
                    
                    @if($visit->status === 'en_cours' && $visit->assigned_user_id === Auth::id())
                        <form action="{{ route('visits.validate', $visit) }}" method="POST" class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                            @csrf
                            <h3 class="font-semibold text-gray-900 mb-3">Valider l'état de la visite</h3>
                            <div class="space-y-2 mb-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="visit_status" value="reussie" required class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Visite réussie</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="visit_status" value="non_effectuee" required class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Visite non effectuée (absence du locataire)</span>
                                </label>
                            </div>
                            <div class="mb-4">
                                <label for="visit_status_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (optionnel)
                                </label>
                                <textarea id="visit_status_notes" name="visit_status_notes" rows="3"
                                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                Valider l'état
                            </button>
                        </form>
                    @endif
                    
                    @if($visit->status === 'en_cours' && $visit->tenant_id === Auth::id())
                        <form action="{{ route('visits.complete', $visit) }}" method="POST" class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm" x-data="{ propertyAccepted: null }">
                            @csrf
                            <h3 class="font-semibold text-gray-900 mb-3">Retour du locataire</h3>
                            <div class="space-y-2 mb-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="property_accepted" value="1" required 
                                           x-model="propertyAccepted" class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Oui, j'accepte</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="property_accepted" value="0" required 
                                           x-model="propertyAccepted" class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Non, je refuse</span>
                                </label>
                            </div>
                            <div class="mb-4" x-show="propertyAccepted === '0'" x-transition>
                                <label for="refusal_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Raison du refus <span class="text-red-500">*</span>
                                </label>
                                <textarea id="refusal_reason" name="refusal_reason" rows="3" required
                                          x-bind:required="propertyAccepted === '0'"
                                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                Terminer la visite
                            </button>
                        </form>
                    @endif
                    
                    @if($visit->status === 'acceptee' && $visit->tenant_id === Auth::id())
                        <div class="p-4 bg-green-50 rounded-2xl border border-green-200">
                            <p class="text-green-700 font-medium mb-2">
                                <i class="fas fa-check-circle mr-2"></i>Propriété acceptée !
                            </p>
                            <p class="text-sm text-green-600 mb-4">Veuillez procéder au paiement du premier versement (1/6 du loyer) pour finaliser la location.</p>
                            <a href="{{ route('visits.payment.first', $visit) }}" class="inline-block py-2 px-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors">
                                Payer le premier versement
                            </a>
                        </div>
                    @endif
                    
                    @if($visit->status === 'refusee')
                        <div class="p-4 bg-red-50 rounded-2xl border border-red-200">
                            <p class="text-red-700 font-medium mb-2">
                                <i class="fas fa-times-circle mr-2"></i>Propriété refusée
                            </p>
                            @if($visit->refusal_reason)
                                <p class="text-sm text-red-600">{{ $visit->refusal_reason }}</p>
                            @endif
                        </div>
                    @endif
                </div>
                
                <!-- Sidebar / property card -->
                <div class="space-y-4">
                    <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50">
                        <div class="flex gap-4">
                            <img src="{{ $visit->property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=500&h=360&fit=crop' }}"
                                 alt="{{ $visit->property->title }}"
                                 class="w-28 h-24 rounded-xl object-cover">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $visit->property->title }}</h3>
                                <p class="text-sm text-gray-600 line-clamp-2 flex items-start gap-2 mt-1">
                                    <i class="fas fa-map-marker-alt text-green-600 mt-0.5"></i>{{ $visit->property->full_address }}
                                </p>
                                <a href="{{ route('properties.show', $visit->property) }}" class="inline-flex items-center gap-1 text-sm text-green-600 font-semibold mt-2 hover:gap-2 transition-all">
                                    Voir la fiche
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-5 rounded-2xl border border-gray-100">
                        <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                            <i class="fas fa-stream text-green-600"></i> Timeline
                        </h3>
                        <ol class="space-y-3 text-sm text-gray-700">
                            <li class="flex gap-3">
                                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs"><i class="fas fa-check"></i></span>
                                <div>
                                    <p class="font-semibold text-gray-900">Demande envoyée</p>
                                    <p class="text-gray-500">{{ $visit->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="w-6 h-6 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs"><i class="fas fa-bell"></i></span>
                                <div>
                                    <p class="font-semibold text-gray-900">Prochaine étape</p>
                                    <p class="text-gray-500">Rappel automatique 24h avant</p>
                                </div>
                            </li>
                            <li class="flex gap-3">
                                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs"><i class="fas fa-home"></i></span>
                                <div>
                                    <p class="font-semibold text-gray-900">Visite sur place</p>
                                    <p class="text-gray-500">Confirmer sur place puis valider</p>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

