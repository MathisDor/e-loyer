@extends('layouts.app')

@section('title', 'Créer un état des lieux')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('contracts.show', $contract) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour au contrat</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8 mb-6">
            <div class="mb-6">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">
                    Créer un état des lieux {{ $type === 'entree' ? 'd\'entrée' : 'de sortie' }}
                </h1>
                <p class="text-gray-600">Propriété : <strong>{{ $contract->property->title }}</strong></p>
            </div>

            <form action="{{ route('inventory-reports.store', $contract) }}" method="POST" enctype="multipart/form-data" x-data="{ items: @js(old('items', [])), addItem() { this.items.push({name: '', condition: 'bon', notes: ''}) }, removeItem(index) { this.items.splice(index, 1) } }">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="space-y-6">
                    <!-- Date -->
                    <div>
                        <label for="report_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Date de l'état des lieux <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="report_date" name="report_date" 
                               value="{{ old('report_date', now()->format('Y-m-d')) }}" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        @error('report_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observations générales -->
                    <div>
                        <label for="observations" class="block text-sm font-medium text-gray-700 mb-2">
                            Observations générales
                        </label>
                        <textarea id="observations" name="observations" rows="4"
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                  placeholder="Notes générales sur l'état de la propriété...">{{ old('observations') }}</textarea>
                        @error('observations')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Liste des éléments -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Éléments vérifiés
                            </label>
                            <button type="button" @click="addItem()" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus mr-1"></i> Ajouter un élément
                            </button>
                        </div>
                        <div class="space-y-3" x-show="items.length > 0">
                            <template x-for="(item, index) in items" :key="index">
                                <div class="p-4 border border-gray-200 rounded-xl bg-gray-50">
                                    <div class="grid md:grid-cols-3 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Nom de l'élément</label>
                                            <input type="text" x-model="items[index].name" 
                                                   :name="`items[${index}][name]`" required
                                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                                   placeholder="Ex: Porte d'entrée">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">État</label>
                                            <select x-model="items[index].condition" 
                                                    :name="`items[${index}][condition]`" required
                                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                                                <option value="bon">Bon état</option>
                                                <option value="etat">État moyen</option>
                                                <option value="degrade">Dégradé</option>
                                                <option value="manquant">Manquant</option>
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <button type="button" @click="removeItem(index)" class="w-full px-3 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold hover:bg-red-200 transition-colors">
                                                <i class="fas fa-trash mr-1"></i> Supprimer
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                                        <textarea x-model="items[index].notes" 
                                                  :name="`items[${index}][notes]`"
                                                  rows="2"
                                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                                  placeholder="Détails supplémentaires..."></textarea>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div x-show="items.length === 0" class="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl">
                            <p class="text-gray-500 mb-3">Aucun élément ajouté</p>
                            <button type="button" @click="addItem()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200 transition-colors">
                                Ajouter le premier élément
                            </button>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div>
                        <label for="photos" class="block text-sm font-medium text-gray-700 mb-2">
                            Photos (optionnel)
                        </label>
                        <input type="file" id="photos" name="photos[]" multiple accept="image/*"
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        <p class="mt-1 text-xs text-gray-500">Vous pouvez sélectionner plusieurs photos (max 5MB par photo)</p>
                        @error('photos.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('contracts.show', $contract) }}" class="px-6 py-3 border border-gray-200 rounded-xl font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="flex-1 px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                            <i class="fas fa-save mr-2"></i>Créer l'état des lieux
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

