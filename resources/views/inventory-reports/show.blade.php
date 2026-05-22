@extends('layouts.app')

@section('title', 'État des lieux')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-12">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('contracts.show', $inventoryReport->contract) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour au contrat</span>
            </a>
            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                {{ $role === 'locataire' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-blue-50 text-blue-700 border border-blue-100' }}">
                Vue {{ $role === 'locataire' ? 'locataire' : 'propriétaire' }}
            </span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4 mb-6">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                        {{ $inventoryReport->type_name }}
                    </h1>
                    <p class="text-gray-600">Propriété : <strong>{{ $inventoryReport->property->title }}</strong></p>
                    <p class="text-sm text-gray-500">Date : {{ $inventoryReport->report_date->format('d/m/Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($inventoryReport->isSigned())
                        <span class="px-4 py-2 rounded-xl font-semibold bg-green-100 text-green-700">
                            <i class="fas fa-check-circle mr-2"></i>Signé par les deux parties
                        </span>
                    @else
                        <span class="px-4 py-2 rounded-xl font-semibold bg-yellow-100 text-yellow-700">
                            <i class="fas fa-clock mr-2"></i>En attente de signature
                        </span>
                    @endif
                </div>
            </div>

            <!-- Signatures -->
            <div class="grid md:grid-cols-2 gap-4 mb-6 p-4 bg-gray-50 rounded-xl border border-gray-100">
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">Locataire</p>
                    @if($inventoryReport->tenant_signed)
                        <div class="flex items-center gap-2 text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-semibold">Signé le {{ $inventoryReport->tenant_signed_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-gray-500">
                            <i class="fas fa-clock"></i>
                            <span>En attente</span>
                        </div>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-2">Propriétaire</p>
                    @if($inventoryReport->owner_signed)
                        <div class="flex items-center gap-2 text-green-600">
                            <i class="fas fa-check-circle"></i>
                            <span class="font-semibold">Signé le {{ $inventoryReport->owner_signed_at->format('d/m/Y à H:i') }}</span>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-gray-500">
                            <i class="fas fa-clock"></i>
                            <span>En attente</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Observations -->
            @if($inventoryReport->observations)
                <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <h3 class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        <i class="fas fa-comment text-blue-600"></i> Observations générales
                    </h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $inventoryReport->observations }}</p>
                </div>
            @endif

            <!-- Liste des éléments -->
            @if($inventoryReport->items && count($inventoryReport->items) > 0)
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-list text-green-600"></i> Éléments vérifiés
                    </h3>
                    <div class="space-y-3">
                        @foreach($inventoryReport->items as $item)
                            <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 mb-1">{{ $item['name'] ?? 'Élément' }}</p>
                                        @if(!empty($item['notes']))
                                            <p class="text-sm text-gray-600">{{ $item['notes'] }}</p>
                                        @endif
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        @if(($item['condition'] ?? 'bon') === 'bon') bg-green-100 text-green-700
                                        @elseif(($item['condition'] ?? '') === 'etat') bg-yellow-100 text-yellow-700
                                        @elseif(($item['condition'] ?? '') === 'degrade') bg-orange-100 text-orange-700
                                        @else bg-red-100 text-red-700
                                        @endif">
                                        @if(($item['condition'] ?? 'bon') === 'bon') Bon état
                                        @elseif(($item['condition'] ?? '') === 'etat') État moyen
                                        @elseif(($item['condition'] ?? '') === 'degrade') Dégradé
                                        @else Manquant
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Photos -->
            @if($inventoryReport->photos && count($inventoryReport->photos) > 0)
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-images text-green-600"></i> Photos
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($inventoryReport->photos as $photo)
                            <div class="relative group">
                                <img src="{{ asset('storage/' . $photo) }}" 
                                     alt="Photo état des lieux"
                                     class="w-full h-32 object-cover rounded-xl border border-gray-200">
                                <a href="{{ asset('storage/' . $photo) }}" target="_blank" 
                                   class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                                    <i class="fas fa-expand text-white text-xl"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Formulaire de signature -->
            @if(!$inventoryReport->isSigned() && $inventoryReport->canBeSignedBy(Auth::id()))
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-xl">
                    <h3 class="font-semibold text-gray-900 mb-3">Signer cet état des lieux</h3>
                    <form action="{{ route('inventory-reports.sign', $inventoryReport) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes (optionnel)
                            </label>
                            <textarea id="notes" name="notes" rows="3"
                                      class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                      placeholder="Ajoutez des commentaires si nécessaire...">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                            <i class="fas fa-signature mr-2"></i>Signer l'état des lieux
                        </button>
                    </form>
                </div>
            @endif

            <!-- Notes des parties -->
            @if($inventoryReport->tenant_notes || $inventoryReport->owner_notes)
                <div class="mt-6 space-y-3">
                    @if($inventoryReport->tenant_notes)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <p class="text-sm font-semibold text-gray-700 mb-1">Note du locataire</p>
                            <p class="text-gray-700">{{ $inventoryReport->tenant_notes }}</p>
                        </div>
                    @endif
                    @if($inventoryReport->owner_notes)
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                            <p class="text-sm font-semibold text-gray-700 mb-1">Note du propriétaire</p>
                            <p class="text-gray-700">{{ $inventoryReport->owner_notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

