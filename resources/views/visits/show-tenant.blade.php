@extends('layouts.app')

@section('title', 'Détails de ma visite')

@section('content')
@php
    $activeTab = $tabs[0]['id'] ?? 'overview';
@endphp

<div class="bg-gray-50 min-h-screen py-6 lg:py-12" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour</span>
            </a>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                Vue locataire
            </span>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        <i class="fas fa-route"></i> Suivi de votre visite
                    </p>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $visit->property->title }}</h1>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="far fa-calendar text-green-600"></i>{{ $visit->scheduled_at->format('d/m/Y') }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="far fa-clock text-green-600"></i>{{ $visit->scheduled_at->format('H:i') }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="fas fa-money-bill-wave text-green-600"></i>{{ $visit->formatted_total_amount }}
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-xl font-semibold
                        @if($visit->status === 'reservee') bg-blue-100 text-blue-700
                        @elseif($visit->status === 'en_cours') bg-yellow-100 text-yellow-700
                        @elseif($visit->status === 'acceptee') bg-green-100 text-green-700
                        @elseif($visit->status === 'refusee') bg-red-100 text-red-700
                        @elseif($visit->status === 'terminee') bg-emerald-100 text-emerald-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $visit->status_name }}
                    </span>
                    @if(!$visit->is_paid)
                        <a href="{{ route('visits.payment', $visit) }}" class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
                            Payer la visite
                        </a>
                    @endif
                </div>
            </div>

            <!-- Tabs -->
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($tabs as $tabItem)
                    <button type="button"
                        @click="tab = '{{ $tabItem['id'] }}'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold border transition-colors"
                        :class="tab === '{{ $tabItem['id'] }}' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-green-200'">
                        {{ $tabItem['label'] }}
                        @if(!empty($tabItem['badge']))
                            <span class="text-xs px-2 py-0.5 rounded-full bg-white border border-gray-200">{{ $tabItem['badge'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Content -->
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div x-show="tab === 'overview'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-green-600"></i> Résumé
                    </h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Montant payé</p>
                            <p class="text-xl font-bold text-gray-900">{{ $visit->is_paid ? $visit->formatted_total_amount : 'En attente' }}</p>
                            <p class="text-xs text-gray-600">Paiement requis pour confirmer</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Créneau</p>
                            <p class="text-sm text-gray-800">{{ $visit->scheduled_at->format('d/m/Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $visit->scheduled_at->format('H:i') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Interlocuteur</p>
                            <p class="text-sm text-gray-800">
                                @if($visit->assignedUser)
                                    <i class="fas fa-user-check text-blue-600 mr-1"></i>{{ $visit->assignedUser->name }}
                                @else
                                    Propriétaire : {{ $visit->owner->name ?? '—' }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fas fa-map-marker-alt text-green-600"></i>
                        <span>{{ $visit->property->full_address }}</span>
                    </div>
                </div>

                <div x-show="tab === 'participants'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-users text-green-600"></i> Participants
                    </h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Locataire</p>
                            <p class="font-semibold text-gray-900">{{ $visit->tenant->name ?? '—' }}</p>
                            <p class="text-sm text-gray-500">{{ $visit->tenant->email ?? '' }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Propriétaire</p>
                            <p class="font-semibold text-gray-900">{{ $visit->owner->name ?? '—' }}</p>
                            <p class="text-sm text-gray-500">{{ $visit->owner->email ?? '' }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Assigné</p>
                            <p class="font-semibold text-gray-900">{{ $visit->assignedUser->name ?? 'Non attribué' }}</p>
                            <p class="text-sm text-gray-500">{{ $visit->assignedUser->email ?? '' }}</p>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'payments'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-credit-card text-green-600"></i> Paiements
                    </h2>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gray-50 border border-gray-100">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Paiement de la visite</p>
                                <p class="text-sm text-gray-600">{{ $visit->formatted_total_amount }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $visit->is_paid ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $visit->is_paid ? 'Payé' : 'En attente' }}
                                </span>
                                @unless($visit->is_paid)
                                    <a href="{{ route('visits.payment', $visit) }}" class="px-3 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors">
                                        Régler maintenant
                                    </a>
                                @endunless
                            </div>
                        </div>
                        @if($visit->status === 'acceptee')
                            <div class="flex items-center justify-between p-4 rounded-xl bg-green-50 border border-green-100">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">Premier versement (1/6)</p>
                                    <p class="text-sm text-gray-600">À régler pour activer le contrat</p>
                                </div>
                                <a href="{{ route('visits.payment.first', $visit) }}" class="px-3 py-2 rounded-xl bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors">
                                    Payer
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div x-show="tab === 'actions'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6" x-data="{ propertyAccepted: null }">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-hand-pointer text-green-600"></i> Actions
                    </h2>

                    @if($visit->status === 'terminee' && $visit->visit_status === 'reussie' && is_null($visit->property_accepted))
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl mb-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-700"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 mb-1">Visite terminée avec succès</p>
                                    <p class="text-sm text-gray-600">Le propriétaire/démarcheur a confirmé que la visite s'est bien déroulée. Veuillez maintenant indiquer si vous acceptez ou refusez ce bien.</p>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('visits.complete', $visit) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-3">
                                <label class="flex items-center gap-3 cursor-pointer p-4 border-2 border-green-200 rounded-xl hover:border-green-400 transition-colors">
                                    <input type="radio" name="property_accepted" value="1" required 
                                           x-model="propertyAccepted" class="w-5 h-5 text-green-600">
                                    <div class="flex-1">
                                        <span class="font-semibold text-gray-900 block">Oui, j'accepte le bien</span>
                                        <span class="text-sm text-gray-600">Un contrat de 6 mois sera créé après le paiement du premier versement</span>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-red-200 transition-colors">
                                    <input type="radio" name="property_accepted" value="0" required 
                                           x-model="propertyAccepted" class="w-5 h-5 text-red-600">
                                    <div class="flex-1">
                                        <span class="font-semibold text-gray-900 block">Non, je refuse</span>
                                        <span class="text-sm text-gray-600">Le bien restera disponible pour d'autres locataires</span>
                                    </div>
                                </label>
                            </div>
                            <div x-show="propertyAccepted === '0'" x-transition class="mt-4">
                                <label for="refusal_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                    Raison du refus <span class="text-red-500">*</span>
                                </label>
                                <textarea id="refusal_reason" name="refusal_reason" rows="4"
                                          x-bind:required="propertyAccepted === '0'"
                                          placeholder="Expliquez pourquoi vous refusez ce bien (optionnel mais recommandé)"
                                          class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-red-500 focus:ring-2 focus:ring-red-500/20"></textarea>
                            </div>
                            <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-check-circle mr-2"></i>Confirmer mon choix
                            </button>
                        </form>
                    @elseif($visit->status === 'en_cours')
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-2xl">
                            <p class="font-semibold text-gray-900 mb-1">Visite en cours</p>
                            <p class="text-sm text-gray-600">La visite est actuellement en cours. Vous pourrez donner votre avis une fois qu'elle sera terminée.</p>
                        </div>
                    @elseif($visit->status === 'reservee' && !$visit->is_paid)
                        <div class="p-4 rounded-xl bg-yellow-50 border border-yellow-100 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center">
                                <i class="fas fa-info text-yellow-700"></i>
                            </div>
                            <div class="space-y-2">
                                <p class="font-semibold text-gray-900">Paiement requis</p>
                                <p class="text-sm text-gray-600">Réglez la visite pour confirmer le créneau.</p>
                                <a href="{{ route('visits.payment', $visit) }}" class="inline-flex px-4 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
                                    Procéder au paiement
                                </a>
                            </div>
                        </div>
                    @elseif($visit->status === 'acceptee')
                        <div class="p-4 rounded-xl bg-green-50 border border-green-100 flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-check text-green-700"></i>
                            </div>
                            <div class="space-y-2">
                                <p class="font-semibold text-gray-900">Propriété acceptée</p>
                                <p class="text-sm text-gray-600">Réalisez le premier versement pour activer le contrat de 6 mois.</p>
                                <a href="{{ route('visits.payment.first', $visit) }}" class="inline-flex px-4 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700 transition-colors">
                                    Payer le premier versement
                                </a>
                            </div>
                        </div>
                    @elseif($visit->status === 'refusee' && $visit->refusal_reason)
                        <div class="p-4 rounded-xl bg-red-50 border border-red-100">
                            <p class="font-semibold text-red-700 mb-1">Propriété refusée</p>
                            <p class="text-sm text-red-600">{{ $visit->refusal_reason }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucune action requise pour l'instant.</p>
                    @endif
                </div>

                <div x-show="tab === 'history'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-stream text-green-600"></i> Statuts
                    </h2>
                    <ol class="space-y-3 text-sm text-gray-700">
                        @foreach($statusSteps as $step)
                            <li class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                                    {{ $step['done'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    @if($step['active']) <i class="fas fa-circle-notch fa-spin"></i>
                                    @elseif($step['done']) <i class="fas fa-check"></i>
                                    @else <i class="fas fa-circle"></i> @endif
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $step['label'] }}</p>
                                    @if($step['active'])
                                        <p class="text-xs text-green-700">Statut actuel</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>

            <div class="space-y-4">
                <div class="p-5 rounded-2xl border border-gray-100 bg-white">
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

                <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-lightbulb text-green-600"></i> Conseils
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-700 list-disc pl-5">
                        <li>Arrivez 10 minutes en avance pour valider votre présence.</li>
                        <li>Préparez vos questions sur le bien et le quartier.</li>
                        <li>En cas d'empêchement, contactez l'assigné ou le propriétaire.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


