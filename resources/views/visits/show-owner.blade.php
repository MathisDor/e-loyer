@extends('layouts.app')

@section('title', 'Visite - Propriétaire')

@section('content')
@php
    $activeTab = $tabs[0]['id'] ?? 'overview';
    $isOwner = Auth::id() === $visit->owner_id;
    $isAssignee = Auth::id() === $visit->assigned_user_id;
@endphp

<div class="bg-gray-50 min-h-screen py-6 lg:py-12" x-data="{ tab: '{{ $activeTab }}' }">
    <div class="max-w-6xl mx-auto px-4">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour</span>
            </a>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                Vue propriétaire
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        <i class="fas fa-home"></i> Gestion de la visite
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
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($tabs as $tabItem)
                    <button type="button"
                        @click="tab = '{{ $tabItem['id'] }}'"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold border transition-colors"
                        :class="tab === '{{ $tabItem['id'] }}' ? 'bg-blue-100 text-blue-800 border-blue-200' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-blue-200'">
                        {{ $tabItem['label'] }}
                        @if(!empty($tabItem['badge']))
                            <span class="text-xs px-2 py-0.5 rounded-full bg-white border border-gray-200">{{ $tabItem['badge'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div x-show="tab === 'overview'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i> Résumé
                    </h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Locataire</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $visit->tenant->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $visit->tenant->email ?? '' }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Créneau</p>
                            <p class="text-sm text-gray-800">{{ $visit->scheduled_at->format('d/m/Y') }}</p>
                            <p class="text-sm text-gray-500">{{ $visit->scheduled_at->format('H:i') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Paiement</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $visit->formatted_total_amount }}</p>
                            <p class="text-xs text-gray-600">{{ $visit->is_paid ? 'Payé' : 'En attente' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <i class="fas fa-map-marker-alt text-green-600"></i>
                        <span>{{ $visit->property->full_address }}</span>
                    </div>
                </div>

                <div x-show="tab === 'participants'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-users text-blue-600"></i> Participants
                    </h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Locataire</p>
                            <p class="font-semibold text-gray-900">{{ $visit->tenant->name ?? '—' }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Assigné (démarcheur / agence)</p>
                            <p class="font-semibold text-gray-900">{{ $visit->assignedUser->name ?? 'Non attribué' }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100">
                            <p class="text-xs text-gray-500">Propriétaire</p>
                            <p class="font-semibold text-gray-900">{{ $visit->owner->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'actions'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-tools text-blue-600"></i> Actions
                    </h2>

                    @if(($isOwner || $isAssignee) && $visit->status === 'reservee' && $visit->is_paid)
                        <form action="{{ route('visits.start', $visit) }}" method="POST" class="p-4 bg-yellow-50 border border-yellow-200 rounded-2xl">
                            @csrf
                            <div class="flex items-start gap-3 mb-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-play text-yellow-700"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900">Démarrer la visite</p>
                                    <p class="text-sm text-gray-600">Lancer la visite sur place avec le locataire.</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full py-3 bg-yellow-600 text-white rounded-xl font-semibold hover:bg-yellow-700 transition-colors">
                                Démarrer
                            </button>
                        </form>
                    @endif

                    @if(($isOwner || $isAssignee) && $visit->status === 'en_cours')
                        <form action="{{ route('visits.validate', $visit) }}" method="POST" class="p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                            @csrf
                            <h3 class="font-semibold text-gray-900 mb-3">Valider l'état</h3>
                            <div class="space-y-2 mb-4">
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="visit_status" value="reussie" required class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Visite réussie</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="radio" name="visit_status" value="non_effectuee" required class="w-5 h-5 text-green-600">
                                    <span class="text-gray-700">Visite non effectuée</span>
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
                                Valider
                            </button>
                        </form>
                    @endif

                    @if($isOwner || $isAssignee)
                        <form method="POST" action="{{ route('visits.status', $visit) }}" class="p-4 bg-gray-50 border border-gray-100 rounded-2xl space-y-3">
                            @csrf
                            <div class="grid md:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Statut</label>
                                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20">
                                        <option value="reservee" {{ $visit->status === 'reservee' ? 'selected' : '' }}>Réservée</option>
                                        <option value="en_cours" {{ $visit->status === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                        <option value="terminee" {{ $visit->status === 'terminee' ? 'selected' : '' }}>Terminée</option>
                                        <option value="acceptee" {{ $visit->status === 'acceptee' ? 'selected' : '' }}>Acceptée</option>
                                        <option value="refusee" {{ $visit->status === 'refusee' ? 'selected' : '' }}>Refusée</option>
                                        <option value="annulee" {{ $visit->status === 'annulee' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-700">Replanifier</label>
                                    <input type="datetime-local" name="scheduled_at"
                                           value="{{ $visit->scheduled_at?->format('Y-m-d\TH:i') }}"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                                           placeholder="Nouvelle date">
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-700">Notes</label>
                                <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" placeholder="Motif ou note interne">{{ old('notes', $visit->notes) }}</textarea>
                            </div>
                            <button type="submit" class="w-full px-3 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                                Mettre à jour
                            </button>
                        </form>
                    @endif
                </div>

                <div x-show="tab === 'history'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-stream text-blue-600"></i> Statuts
                    </h2>
                    <ol class="space-y-3 text-sm text-gray-700">
                        @foreach($statusSteps as $step)
                            <li class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold
                                    {{ $step['done'] ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500' }}">
                                    @if($step['active']) <i class="fas fa-circle-notch fa-spin"></i>
                                    @elseif($step['done']) <i class="fas fa-check"></i>
                                    @else <i class="fas fa-circle"></i> @endif
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $step['label'] }}</p>
                                    @if($step['active'])
                                        <p class="text-xs text-blue-700">Statut actuel</p>
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
                            <a href="{{ route('properties.show', $visit->property) }}" class="inline-flex items-center gap-1 text-sm text-blue-600 font-semibold mt-2 hover:gap-2 transition-all">
                                Voir la fiche
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-clipboard-list text-blue-600"></i> Rappels
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-700 list-disc pl-5">
                        <li>Assurez-vous que le locataire a payé la visite avant de démarrer.</li>
                        <li>Mettez à jour le statut dès que la visite est terminée.</li>
                        <li>Ajoutez des notes pour tracer les échanges.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



