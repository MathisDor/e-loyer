@extends('layouts.app')

@section('title', 'Contrat de location')

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
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                Vue propriétaire
            </span>
        </div>

        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div class="space-y-2">
                    <p class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                        <i class="fas fa-file-contract"></i> Contrat de location
                    </p>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $contract->property->title }}</h1>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="far fa-calendar text-blue-600"></i>{{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="fas fa-money-bill-wave text-blue-600"></i>{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA/mois
                        </span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="px-4 py-2 rounded-xl font-semibold
                        @if($contract->status === 'actif') bg-green-100 text-green-700
                        @elseif($contract->status === 'en_attente') bg-yellow-100 text-yellow-700
                        @elseif($contract->status === 'termine') bg-gray-100 text-gray-700
                        @elseif($contract->status === 'resilie') bg-red-100 text-red-700
                        @else bg-gray-100 text-gray-700
                        @endif">
                        {{ $contract->status_name }}
                    </span>
                </div>
            </div>

            <!-- Tabs -->
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

        <!-- Content -->
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <!-- Overview -->
                <div x-show="tab === 'overview'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-600"></i> Résumé du contrat
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Durée</p>
                            <p class="text-xl font-bold text-gray-900">{{ $contract->duration_months }} mois</p>
                            <p class="text-xs text-gray-600">{{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Loyer mensuel (brut)</p>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-gray-600">Total : {{ number_format($displayTotalAmount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-blue-600 mt-1">Sans commission ni frais de service</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Mois payés</p>
                            <p class="text-xl font-bold text-gray-900">{{ $contract->months_paid }}/{{ $contract->duration_months }}</p>
                            <p class="text-xs text-gray-600">{{ $contract->remaining_months }} mois restants</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Revenus perçus</p>
                            <p class="text-xl font-bold text-green-600">{{ number_format($contract->months_paid * $displayMonthlyAmount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-gray-600">Sur {{ number_format($displayTotalAmount, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-2xl">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user text-blue-700"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 mb-1">Locataire</p>
                                <p class="text-sm text-gray-700">{{ $contract->tenant->name }}</p>
                                <p class="text-sm text-gray-600">{{ $contract->tenant->email }}</p>
                                @if($contract->tenant->phone)
                                    <p class="text-sm text-gray-600">{{ $contract->tenant->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments -->
                <div x-show="tab === 'payments'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-credit-card text-blue-600"></i> Historique des paiements
                    </h2>
                    @if($contract->payments->count() === 0)
                        <div class="text-center py-8">
                            <p class="text-gray-500">Aucun paiement enregistré</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($contract->payments->sortByDesc('paid_at') as $payment)
                                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900">{{ $payment->description }}</p>
                                        <p class="text-sm text-gray-600">{{ $payment->paid_at?->format('d/m/Y à H:i') ?? 'En attente' }}</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} - {{ $payment->phone_number }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-gray-900">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold
                                            @if($payment->status === 'confirme') bg-green-100 text-green-700
                                            @elseif($payment->status === 'traitement') bg-yellow-100 text-yellow-700
                                            @else bg-red-100 text-red-700
                                            @endif">
                                            @if($payment->status === 'confirme')
                                                <i class="fas fa-check-circle"></i> Payé
                                            @elseif($payment->status === 'traitement')
                                                <i class="fas fa-clock"></i> En traitement
                                            @else
                                                <i class="fas fa-times-circle"></i> Échoué
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-gray-900">Total perçu</span>
                                <span class="text-xl font-bold text-green-600">
                                    {{ number_format($contract->payments->where('status', 'confirme')->sum('amount'), 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Details -->
                <div x-show="tab === 'details'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-alt text-blue-600"></i> Détails du contrat
                    </h2>
                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Propriété</p>
                                <p class="font-semibold text-gray-900">{{ $contract->property->title }}</p>
                                <p class="text-sm text-gray-600">{{ $contract->property->full_address }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Locataire</p>
                                <p class="font-semibold text-gray-900">{{ $contract->tenant->name }}</p>
                                <p class="text-sm text-gray-600">{{ $contract->tenant->email }}</p>
                                @if($contract->tenant->phone)
                                    <p class="text-sm text-gray-600">{{ $contract->tenant->phone }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-sm text-gray-500 mb-2">Dates importantes</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date de début</span>
                                    <span class="font-semibold">{{ $contract->start_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Date de fin</span>
                                    <span class="font-semibold">{{ $contract->end_date->format('d/m/Y') }}</span>
                                </div>
                                @if($contract->next_payment_date)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Prochain paiement attendu</span>
                                        <span class="font-semibold">{{ $contract->next_payment_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                                @if($contract->tenant_signed_at)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Signé par le locataire</span>
                                        <span class="font-semibold">{{ $contract->tenant_signed_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                @endif
                                @if($contract->owner_signed_at)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Signé par vous</span>
                                        <span class="font-semibold">{{ $contract->owner_signed_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-sm text-gray-500 mb-2">Montants</p>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Loyer mensuel (brut)</span>
                                    <span class="font-semibold">{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Montant total du contrat</span>
                                    <span class="font-semibold">{{ number_format($displayTotalAmount, 0, ',', ' ') }} FCFA</span>
                                </div>
                                <div class="pt-2 border-t border-gray-100 mt-2">
                                    <p class="text-xs text-gray-500 mb-1">Note : Le locataire paie un montant majoré incluant la commission (8%) et les frais de service (400 FCFA)</p>
                                </div>
                                @if($contract->deposit_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Dépôt de garantie</span>
                                        <span class="font-semibold">{{ number_format($contract->deposit_amount, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($contract->visit)
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm text-gray-500 mb-2">Visite associée</p>
                                <a href="{{ route('visits.show', $contract->visit) }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                                    Voir la visite <i class="fas fa-arrow-right text-xs"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-4">
                <div class="p-5 rounded-2xl border border-gray-100 bg-white">
                    <div class="flex gap-4">
                        <img src="{{ $contract->property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=500&h=360&fit=crop' }}"
                             alt="{{ $contract->property->title }}"
                             class="w-28 h-24 rounded-xl object-cover">
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $contract->property->title }}</h3>
                            <p class="text-sm text-gray-600 line-clamp-2 flex items-start gap-2 mt-1">
                                <i class="fas fa-map-marker-alt text-blue-600 mt-0.5"></i>{{ $contract->property->full_address }}
                            </p>
                            <a href="{{ route('properties.show', $contract->property) }}" class="inline-flex items-center gap-1 text-sm text-blue-600 font-semibold mt-2 hover:gap-2 transition-all">
                                Voir la fiche
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-chart-line text-blue-600"></i> Statistiques
                    </h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Taux de paiement</p>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($contract->months_paid / $contract->duration_months) * 100 }}%"></div>
                            </div>
                            <p class="text-xs text-gray-600 mt-1">{{ round(($contract->months_paid / $contract->duration_months) * 100) }}% complété</p>
                        </div>
                        <div class="pt-3 border-t border-gray-200">
                            <p class="text-xs text-gray-500 mb-1">Revenus attendus (brut)</p>
                            <p class="text-lg font-bold text-gray-900">{{ number_format($displayTotalAmount, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Revenus perçus</p>
                            <p class="text-lg font-bold text-green-600">{{ number_format($contract->months_paid * $displayMonthlyAmount, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── SECTION RÉSILIATION ─────────────────────────────── --}}
        @if(in_array($contract->status, ['actif', 'resilie']) || $contract->hasTerminationRequest())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
            @include('contracts._termination', ['role' => 'proprietaire'])
        </div>
        @endif

    </div>
</div>
@endsection

