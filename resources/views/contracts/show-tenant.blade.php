@extends('layouts.app')

@section('title', 'Mon contrat de location')

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
                        <i class="fas fa-file-contract"></i> Contrat de location
                    </p>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $contract->property->title }}</h1>
                    <div class="flex items-center gap-3 text-sm text-gray-600">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="far fa-calendar text-green-600"></i>{{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}
                        </span>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                            <i class="fas fa-money-bill-wave text-green-600"></i>{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA/mois
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
                <!-- Overview -->
                <div x-show="tab === 'overview'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-info-circle text-green-600"></i> Résumé du contrat
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Durée</p>
                            <p class="text-xl font-bold text-gray-900">{{ $contract->duration_months }} mois</p>
                            <p class="text-xs text-gray-600">{{ $contract->start_date->format('d/m/Y') }} - {{ $contract->end_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Loyer mensuel</p>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-gray-600">Total : {{ number_format($displayTotalAmount, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-green-600 mt-1">Inclut commission et frais de service</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Mois payés</p>
                            <p class="text-xl font-bold text-gray-900">{{ $contract->months_paid }}/{{ $contract->duration_months }}</p>
                            <p class="text-xs text-gray-600">{{ $contract->remaining_months }} mois restants</p>
                        </div>
                        <div class="p-4 rounded-2xl border border-gray-100 bg-gray-50">
                            <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Dépôt de garantie</p>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($contract->deposit_amount ?? 0, 0, ',', ' ') }} FCFA</p>
                            <p class="text-xs text-gray-600">Remboursable à la fin</p>
                        </div>
                    </div>
                    @if($contract->needsPayment())
                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-2xl">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-yellow-700"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900 mb-1">Paiement requis</p>
                                    <p class="text-sm text-gray-600 mb-3">Le prochain paiement est dû le {{ $contract->next_payment_date->format('d/m/Y') }}.</p>
                                    <a href="#pay-rent" @click="tab = 'pay-rent'" class="inline-flex px-4 py-2 rounded-xl bg-yellow-600 text-white font-semibold hover:bg-yellow-700 transition-colors">
                                        Payer maintenant
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Payments -->
                <div x-show="tab === 'payments'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-credit-card text-green-600"></i> Historique des paiements
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
                    @endif
                </div>

                <!-- Pay Rent -->
                @if($contract->needsPayment())
                    <div x-show="tab === 'pay-rent'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-money-bill-wave text-green-600"></i> Payer le loyer mensuel
                        </h2>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">Montant à payer</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA</p>
                                    <p class="text-xs text-gray-500 mt-1">Loyer + Commission + Frais de service</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">Mois</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $contract->months_paid + 1 }}/{{ $contract->duration_months }}</p>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600 mb-4">Date d'échéance : <strong>{{ $contract->next_payment_date->format('d/m/Y') }}</strong></p>
                        </div>
                        <form action="{{ route('contracts.pay.monthly', $contract) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-3">
                                <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="airtel_money">
                                    <input type="radio" name="payment_method" value="airtel_money" class="sr-only" required>
                                    <div class="w-14 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                                        <span class="text-red-600 font-bold text-xs">Airtel</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Airtel Money</p>
                                    </div>
                                    <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                                </label>
                                <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="moov_money">
                                    <input type="radio" name="payment_method" value="moov_money" class="sr-only">
                                    <div class="w-14 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                                        <span class="text-blue-600 font-bold text-xs">Moov</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Moov Money</p>
                                    </div>
                                    <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                                </label>
                                <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="gabon_telecom_cash">
                                    <input type="radio" name="payment_method" value="gabon_telecom_cash" class="sr-only">
                                    <div class="w-14 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                                        <span class="text-green-600 font-bold text-xs">GT</span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">Gabon Telecom Cash</p>
                                    </div>
                                    <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                                </label>
                            </div>
                            <div>
                                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Numéro de téléphone <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="phone_number" name="phone_number" 
                                       value="{{ old('phone_number', auth()->user()->phone) }}" required
                                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                       placeholder="+241 XX XX XX XX">
                            </div>
                            <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-lock mr-2"></i>Payer {{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Inventory Reports -->
                <div x-show="tab === 'inventory'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-clipboard-check text-green-600"></i> États des lieux
                        </h2>
                        @if(!$contract->entryInventoryReport)
                            <a href="{{ route('inventory-reports.create.entry', $contract) }}" class="px-4 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>Créer état d'entrée
                            </a>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <!-- État des lieux d'entrée -->
                        @if($contract->entryInventoryReport)
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">État des lieux d'entrée</h3>
                                        <p class="text-sm text-gray-600">Date : {{ $contract->entryInventoryReport->report_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($contract->entryInventoryReport->isSigned())
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle mr-1"></i>Signé
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock mr-1"></i>En attente
                                            </span>
                                        @endif
                                        <a href="{{ route('inventory-reports.show', $contract->entryInventoryReport) }}" class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                                            Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-4 border-2 border-dashed border-gray-200 rounded-xl text-center">
                                <p class="text-gray-500 mb-3">Aucun état des lieux d'entrée créé</p>
                                <a href="{{ route('inventory-reports.create.entry', $contract) }}" class="inline-flex px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Créer l'état d'entrée
                                </a>
                            </div>
                        @endif

                        <!-- État des lieux de sortie -->
                        @if($contract->exitInventoryReport)
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900">État des lieux de sortie</h3>
                                        <p class="text-sm text-gray-600">Date : {{ $contract->exitInventoryReport->report_date->format('d/m/Y') }}</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        @if($contract->exitInventoryReport->isSigned())
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <i class="fas fa-check-circle mr-1"></i>Signé
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                <i class="fas fa-clock mr-1"></i>En attente
                                            </span>
                                        @endif
                                        <a href="{{ route('inventory-reports.show', $contract->exitInventoryReport) }}" class="px-3 py-1 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                                            Voir
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @elseif($contract->entryInventoryReport && $contract->status === 'termine')
                            <div class="p-4 border-2 border-dashed border-gray-200 rounded-xl text-center">
                                <p class="text-gray-500 mb-3">Aucun état des lieux de sortie créé</p>
                                <a href="{{ route('inventory-reports.create.exit', $contract) }}" class="inline-flex px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Créer l'état de sortie
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Details -->
                <div x-show="tab === 'details'" x-cloak class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-alt text-green-600"></i> Détails du contrat
                    </h2>
                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Propriété</p>
                                <p class="font-semibold text-gray-900">{{ $contract->property->title }}</p>
                                <p class="text-sm text-gray-600">{{ $contract->property->full_address }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 mb-1">Propriétaire</p>
                                <p class="font-semibold text-gray-900">{{ $contract->owner->name }}</p>
                                <p class="text-sm text-gray-600">{{ $contract->owner->email }}</p>
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
                                        <span class="text-gray-600">Prochain paiement</span>
                                        <span class="font-semibold">{{ $contract->next_payment_date->format('d/m/Y') }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @if($contract->visit)
                            <div class="border-t border-gray-100 pt-4">
                                <p class="text-sm text-gray-500 mb-2">Visite associée</p>
                                <a href="{{ route('visits.show', $contract->visit) }}" class="text-green-600 hover:text-green-700 font-semibold">
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
                                <i class="fas fa-map-marker-alt text-green-600 mt-0.5"></i>{{ $contract->property->full_address }}
                            </p>
                            <a href="{{ route('properties.show', $contract->property) }}" class="inline-flex items-center gap-1 text-sm text-green-600 font-semibold mt-2 hover:gap-2 transition-all">
                                Voir la fiche
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-5 rounded-2xl border border-gray-100 bg-gray-50">
                    <h3 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle text-green-600"></i> Informations
                    </h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span>Contrat de {{ $contract->duration_months }} mois renouvelable</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check text-green-600 mt-1"></i>
                            <span>Paiement mensuel de {{ number_format($displayMonthlyAmount, 0, ',', ' ') }} FCFA</span>
                        </li>
                        @if($contract->deposit_amount > 0)
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-green-600 mt-1"></i>
                                <span>Dépôt de garantie : {{ number_format($contract->deposit_amount, 0, ',', ' ') }} FCFA</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        {{-- ── SECTION RÉSILIATION ─────────────────────────────── --}}
        @if(in_array($contract->status, ['actif', 'resilie']) || $contract->hasTerminationRequest())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-6">
            @include('contracts._termination', ['role' => 'locataire'])
        </div>
        @endif

    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => {
            m.classList.remove('border-green-500', 'bg-green-50');
            m.querySelector('.check-icon').classList.add('opacity-0');
            m.querySelector('input[type="radio"]').checked = false;
        });
        this.classList.add('border-green-500', 'bg-green-50');
        this.querySelector('.check-icon').classList.remove('opacity-0');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>
@endpush
@endsection

