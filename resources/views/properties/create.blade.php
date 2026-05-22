@extends('layouts.app')
@section('title', 'Publier un logement')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    [x-cloak] { display: none !important; }
    .step-line { height: 2px; flex: 1; transition: background .3s; }
    .type-card input:checked ~ div { border-color: #059669; background: #ecfdf5; }
    .type-card input:checked ~ div .type-icon { background: #059669; color: white; }
    .amenity-check:checked ~ div { border-color: #059669; background: #f0fdf4; }
    .amenity-check:checked ~ div i { color: #059669; }
    .img-item:hover .img-remove { opacity: 1; }
    .img-remove { opacity: 0; transition: opacity .2s; }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen" x-data="propertyForm()">

    {{-- Header sticky --}}
    <div class="sticky top-0 z-20 bg-white border-b border-gray-100 shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="javascript:history.back()" class="flex items-center gap-2 text-gray-500 hover:text-gray-800 text-sm font-medium transition-colors">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

            {{-- Stepper --}}
            <div class="flex items-center gap-0">
                @php $stepLabels = ['Type','Détails','Localisation','Équipements','Photos']; @endphp
                @foreach($stepLabels as $i => $label)
                    @php $n = $i + 1; @endphp
                    <div class="flex items-center">
                        <div class="flex flex-col items-center">
                            <button type="button" @click="goToStep({{ $n }})"
                                    :disabled="{{ $n }} > maxStep"
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                                    :class="{
                                        'bg-emerald-600 text-white shadow-sm shadow-emerald-500/40': step === {{ $n }},
                                        'bg-emerald-100 text-emerald-600': step > {{ $n }},
                                        'bg-gray-200 text-gray-400 cursor-not-allowed': step < {{ $n }}
                                    }">
                                <i x-show="step > {{ $n }}" class="fas fa-check text-[10px]"></i>
                                <span x-show="step <= {{ $n }}">{{ $n }}</span>
                            </button>
                            <span class="text-[9px] mt-0.5 hidden sm:block font-medium"
                                  :class="step === {{ $n }} ? 'text-emerald-600' : 'text-gray-400'">{{ $label }}</span>
                        </div>
                        @if($i < 4)
                        <div class="step-line w-6 sm:w-10 mx-1 mt-[-10px]"
                             :style="step > {{ $n }} ? 'background:#059669' : 'background:#e5e7eb'"></div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="text-xs text-gray-400 font-medium hidden sm:block">
                Étape <span x-text="step"></span>/5
            </div>
        </div>
    </div>

    {{-- Aperçu titre (barre sous le header) --}}
    <div class="bg-emerald-700 text-white py-2 px-4" x-show="formData.type && formData.city">
        <div class="max-w-3xl mx-auto flex items-center gap-2 text-sm">
            <i class="fas fa-eye text-emerald-300 text-xs"></i>
            <span class="text-emerald-200 text-xs">Votre annonce apparaîtra sous :</span>
            <span class="font-bold" x-text="previewTitle()"></span>
        </div>
    </div>

    <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data"
          @submit.prevent="handleSubmit($event)" class="max-w-3xl mx-auto px-4 py-6 lg:py-10 space-y-0">
        @csrf

        {{-- Erreurs globales --}}
        @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
            <p class="font-semibold text-red-800 mb-2 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> Veuillez corriger les erreurs suivantes :</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-sm text-red-700">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ═══════════════════════════════════════
             ÉTAPE 1 — TYPE DE LOGEMENT
        ═══════════════════════════════════════ --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <h2 class="text-xl font-extrabold text-gray-900 mb-1">Quel type de logement proposez-vous ?</h2>
                <p class="text-sm text-gray-500 mb-6">Sélectionnez la catégorie qui correspond le mieux à votre bien</p>

                @php
                $typeConfig = [
                    'appartement' => ['icon' => 'fa-building',   'color' => 'emerald', 'desc' => 'F2, F3, F4...'],
                    'maison'      => ['icon' => 'fa-home',        'color' => 'blue',    'desc' => 'Individuelle, mitoyenne'],
                    'studio'      => ['icon' => 'fa-door-open',   'color' => 'purple',  'desc' => 'Pièce unique'],
                    'villa'       => ['icon' => 'fa-hotel',       'color' => 'amber',   'desc' => 'Avec jardin, standing'],
                    'chambre'     => ['icon' => 'fa-bed',         'color' => 'rose',    'desc' => 'Dans une résidence'],
                ];
                @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
                    @foreach($types as $key => $label)
                    @php $cfg = $typeConfig[$key]; @endphp
                    <label class="type-card cursor-pointer">
                        <input type="radio" name="type" value="{{ $key }}" x-model="formData.type" class="sr-only" required>
                        <div class="flex flex-col items-center gap-3 p-5 border-2 border-gray-200 rounded-2xl transition-all duration-200 hover:border-{{ $cfg['color'] }}-300 hover:bg-{{ $cfg['color'] }}-50/50">
                            <div class="type-icon w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center transition-all">
                                <i class="fas {{ $cfg['icon'] }} text-2xl text-gray-400"></i>
                            </div>
                            <div class="text-center">
                                <p class="font-bold text-gray-800 text-sm">{{ $label }}</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $cfg['desc'] }}</p>
                            </div>
                        </div>
                    </label>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <label for="description" class="block text-sm font-bold text-gray-800 mb-2">
                        Décrivez votre logement <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3">
                        <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                        Mentionnez l'état général, l'exposition, les points forts, la proximité des services (marché, école, transport...)
                    </p>
                    <textarea id="description" name="description" rows="6" x-model="formData.description" required
                              class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none text-sm @error('description') border-red-400 @enderror"
                              placeholder="Ex: Bel appartement F3 situé à Nombakélé, au 2ème étage d'un immeuble sécurisé. Séjour spacieux, cuisine équipée, deux chambres avec placard. Groupe électrogène et citerne d'eau inclus. À 5 minutes du marché de Mont-Bouët..."></textarea>
                    <div class="flex justify-between mt-1.5">
                        <span class="text-xs text-gray-400">Minimum 30 caractères</span>
                        <span class="text-xs font-semibold transition-colors" :class="formData.description.length >= 30 ? 'text-emerald-600' : 'text-gray-400'" x-text="formData.description.length + ' caractères'"></span>
                    </div>
                    @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             ÉTAPE 2 — CARACTÉRISTIQUES & TARIF
        ═══════════════════════════════════════ --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0" class="space-y-4">

            {{-- Caractéristiques --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-ruler-combined text-blue-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Caractéristiques</h2>
                        <p class="text-xs text-gray-500">Surface, pièces et capacité</p>
                    </div>
                </div>

                {{-- Pièces --}}
                <div class="grid grid-cols-3 gap-4">
                    @foreach([['bedrooms','fa-bed','Chambres',0,20],['bathrooms','fa-shower','Douches',0,10],['beds','fa-couch','Lits',0,20]] as [$field,$icon,$label,$min,$max])
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-2 text-center">
                            <i class="fas {{ $icon }} mr-1"></i>{{ $label }}
                        </label>
                        <div class="flex items-center border border-gray-200 rounded-2xl overflow-hidden">
                            <button type="button" @click="formData.{{ $field }} = Math.max({{ $min }}, formData.{{ $field }} - 1)"
                                    class="px-3 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-gray-500">
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <input type="number" name="{{ $field }}" x-model="formData.{{ $field }}" min="{{ $min }}" max="{{ $max }}"
                                   class="w-full text-center py-3 border-0 focus:ring-0 font-bold text-base bg-white">
                            <button type="button" @click="formData.{{ $field }} = Math.min({{ $max }}, formData.{{ $field }} + 1)"
                                    class="px-3 py-3 bg-gray-50 hover:bg-gray-100 transition-colors text-gray-500">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tarification --}}
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-emerald-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Tarification</h2>
                        <p class="text-xs text-gray-500">Loyer mensuel et caution</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Loyer mensuel <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="monthly_price" x-model="formData.monthly_price" min="10000" required
                                   @input="autoFillDeposit()"
                                   class="w-full pl-4 pr-20 py-3.5 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-lg @error('monthly_price') border-red-400 @enderror"
                                   placeholder="150 000">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">FCFA</span>
                        </div>
                        @error('monthly_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            Caution
                            <span class="text-xs font-normal text-gray-400">(auto = 1 mois)</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="deposit" x-model="formData.deposit" min="0"
                                   class="w-full pl-4 pr-20 py-3.5 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all font-bold text-lg"
                                   :placeholder="formData.monthly_price || '150 000'">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">FCFA</span>
                        </div>
                    </div>
                </div>

                {{-- Simulateur de prix --}}
                <div class="rounded-2xl overflow-hidden border border-gray-100" x-show="formData.monthly_price >= 10000">
                    {{-- Ce que voit le locataire --}}
                    <div class="bg-gradient-to-r from-slate-800 to-slate-700 p-4 text-white">
                        <p class="text-xs text-slate-400 mb-1">Prix affiché aux locataires (frais inclus)</p>
                        <p class="text-2xl font-extrabold" x-text="formatPrice(priceWithFees()) + ' FCFA/mois'"></p>
                        <div class="flex gap-4 mt-2 text-xs text-slate-400">
                            <span x-text="'Loyer net : ' + formatPrice(parseInt(formData.monthly_price)||0) + ' FCFA'"></span>
                            <span>+ commission 8%</span>
                            <span>+ 400 FCFA frais</span>
                        </div>
                    </div>
                    {{-- Ce que reçoit le propriétaire --}}
                    <div class="grid grid-cols-2 divide-x divide-gray-100">
                        <div class="p-3 bg-emerald-50 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">Vous recevez / mois</p>
                            <p class="font-extrabold text-emerald-600 text-lg" x-text="formatPrice(parseInt(formData.monthly_price)||0) + ' F'"></p>
                        </div>
                        <div class="p-3 bg-amber-50 text-center">
                            <p class="text-xs text-gray-500 mb-0.5">Entrée locataire</p>
                            <p class="font-extrabold text-amber-600 text-lg" x-text="formatPrice(priceWithFees() + (parseInt(formData.deposit)||parseInt(formData.monthly_price)||0)) + ' F'"></p>
                        </div>
                    </div>
                </div>

                {{-- Configuration visite --}}
                <div class="mt-5 pt-5 border-t border-gray-100">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Prix de la visite <span class="text-red-500">*</span>
                        <span class="text-xs font-normal text-gray-400 ml-1">(prix de base demarcheur)</span>
                    </label>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="number" name="visit_price" x-model="formData.visit_price" min="0" required
                                   class="w-full pl-4 pr-20 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all font-semibold"
                                   placeholder="5 000">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-semibold">FCFA</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl text-xs text-gray-600" x-show="formData.visit_price">
                            <i class="fas fa-calculator text-gray-400"></i>
                            <span>Total visite locataire : <strong x-text="formatPrice(visitTotal()) + ' FCFA'"></strong></span>
                        </div>
                    </div>
                    @error('visit_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Caution requise --}}
                <label class="flex items-center gap-3 cursor-pointer mt-4 p-3 rounded-2xl hover:bg-gray-50 transition-colors">
                    <input type="checkbox" name="requires_deposit" value="1" x-model="formData.requires_deposit"
                           class="w-5 h-5 rounded text-emerald-600 border-gray-300 focus:ring-emerald-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Exiger une caution</p>
                        <p class="text-xs text-gray-500">Le locataire devra payer la caution en plus du premier loyer</p>
                    </div>
                </label>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             ÉTAPE 3 — LOCALISATION
        ═══════════════════════════════════════ --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8 space-y-5">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-map-marker-alt text-red-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Localisation</h2>
                        <p class="text-xs text-gray-500">Ville, quartier et repères</p>
                    </div>
                </div>

                {{-- Ville --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Ville <span class="text-red-500">*</span>
                    </label>
                    <select name="city" x-model="formData.city" required @change="formData.neighborhood = ''"
                            class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium appearance-none bg-white">
                        <option value="">-- Sélectionnez une ville --</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Quartier avec suggestions dynamiques --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Quartier</label>
                    <div class="relative">
                        <input type="text" name="neighborhood" x-model="formData.neighborhood"
                               list="neighborhood-list"
                               class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all"
                               placeholder="Ex: Nombakélé, Glass, Akébé...">
                        <datalist id="neighborhood-list">
                            <template x-for="q in neighborhoodSuggestions()" :key="q">
                                <option :value="q"></option>
                            </template>
                        </datalist>
                    </div>
                    <div class="flex flex-wrap gap-2 mt-2" x-show="formData.city && neighborhoodSuggestions().length > 0">
                        <template x-for="q in neighborhoodSuggestions().slice(0,8)" :key="q">
                            <button type="button" @click="formData.neighborhood = q"
                                    :class="formData.neighborhood === q ? 'bg-emerald-100 text-emerald-700 border-emerald-300' : 'bg-gray-50 text-gray-600 border-gray-200'"
                                    class="px-3 py-1 border text-xs rounded-xl font-medium hover:border-emerald-300 hover:bg-emerald-50 transition-colors"
                                    x-text="q"></button>
                        </template>
                    </div>
                </div>

                {{-- Adresse --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Adresse / Localisation <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="address" x-model="formData.address" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all @error('address') border-red-400 @enderror"
                           placeholder="Ex: Rue de la Sablière, face à l'école primaire...">
                    @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                {{-- Repère (important en Afrique) --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">
                        <i class="fas fa-map-pin text-amber-500 mr-1"></i> Repère / Indications d'accès
                        <span class="text-gray-400 font-normal">(recommandé)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-2">Aidez le locataire à trouver facilement votre logement</p>
                    <textarea name="landmark" x-model="formData.landmark" rows="2"
                              class="w-full px-4 py-3 border border-gray-200 rounded-2xl focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all resize-none text-sm"
                              placeholder="Ex: À 200m du marché de Mont-Bouët, après le carrefour Shell, portail bleu à gauche..."></textarea>
                </div>

                {{-- Carte --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fas fa-map text-gray-400 mr-1"></i> Position exacte sur la carte
                        <span class="text-gray-400 font-normal">(optionnel)</span>
                    </label>
                    <div id="location-map" class="h-56 rounded-2xl border border-gray-200 overflow-hidden"></div>
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-hand-pointer mr-1"></i>Cliquez sur la carte pour épingler l'emplacement exact</p>
                    <input type="hidden" name="latitude" x-model="formData.latitude">
                    <input type="hidden" name="longitude" x-model="formData.longitude">
                    <div class="flex items-center gap-2 mt-2 text-xs text-emerald-600 font-medium" x-show="formData.latitude">
                        <i class="fas fa-check-circle"></i>
                        <span x-text="'Position enregistrée : ' + parseFloat(formData.latitude).toFixed(4) + ', ' + parseFloat(formData.longitude).toFixed(4)"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             ÉTAPE 4 — ÉQUIPEMENTS
        ═══════════════════════════════════════ --}}
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-bolt text-purple-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Équipements & Services</h2>
                        <p class="text-xs text-gray-500">Cochez tous les équipements disponibles</p>
                    </div>
                    <div class="ml-auto px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full" x-show="formData.amenities.length > 0" x-text="formData.amenities.length + ' sélectionné(s)'"></div>
                </div>

                @php
                $amenityGroups = [
                    'Eau & Énergie'  => ['groupe_electrogene','citerne_eau','forage','eau_chaude','fibre_optique','wifi'],
                    'Confort'        => ['climatisation','meuble','cuisine_equipee','cuisine_exterieure','buanderie'],
                    'Sécurité'       => ['gardien','securite_24h','cloture','porte_blindee'],
                    'Extérieur'      => ['parking','jardin','balcon','terrasse'],
                    'Divers'         => ['piscine','douche_externe'],
                ];
                $amenityIcons = [
                    'groupe_electrogene' => ['icon' => 'fa-bolt',         'color' => 'text-amber-500'],
                    'citerne_eau'        => ['icon' => 'fa-tint',          'color' => 'text-blue-500'],
                    'forage'             => ['icon' => 'fa-water',         'color' => 'text-blue-600'],
                    'eau_chaude'         => ['icon' => 'fa-hot-tub',       'color' => 'text-orange-500'],
                    'fibre_optique'      => ['icon' => 'fa-network-wired', 'color' => 'text-indigo-500'],
                    'wifi'               => ['icon' => 'fa-wifi',          'color' => 'text-blue-500'],
                    'climatisation'      => ['icon' => 'fa-snowflake',     'color' => 'text-cyan-500'],
                    'meuble'             => ['icon' => 'fa-couch',         'color' => 'text-purple-500'],
                    'cuisine_equipee'    => ['icon' => 'fa-utensils',      'color' => 'text-orange-500'],
                    'cuisine_exterieure' => ['icon' => 'fa-fire',          'color' => 'text-red-500'],
                    'buanderie'          => ['icon' => 'fa-soap',          'color' => 'text-teal-500'],
                    'gardien'            => ['icon' => 'fa-user-shield',   'color' => 'text-gray-600'],
                    'securite_24h'       => ['icon' => 'fa-shield-alt',    'color' => 'text-emerald-600'],
                    'cloture'            => ['icon' => 'fa-fence',         'color' => 'text-stone-500'],
                    'porte_blindee'      => ['icon' => 'fa-lock',          'color' => 'text-gray-700'],
                    'parking'            => ['icon' => 'fa-parking',       'color' => 'text-blue-600'],
                    'jardin'             => ['icon' => 'fa-tree',          'color' => 'text-green-600'],
                    'balcon'             => ['icon' => 'fa-door-open',     'color' => 'text-amber-600'],
                    'terrasse'           => ['icon' => 'fa-umbrella-beach','color' => 'text-yellow-600'],
                    'piscine'            => ['icon' => 'fa-swimming-pool', 'color' => 'text-sky-600'],
                    'douche_externe'     => ['icon' => 'fa-shower',        'color' => 'text-blue-400'],
                ];
                @endphp

                <div class="space-y-6 mt-6">
                    @foreach($amenityGroups as $groupName => $keys)
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">{{ $groupName }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($keys as $key)
                            @if(isset($amenities[$key]))
                            @php $cfg = $amenityIcons[$key] ?? ['icon'=>'fa-check','color'=>'text-gray-500']; @endphp
                            <label class="cursor-pointer">
                                <input type="checkbox" name="amenities[]" value="{{ $key }}" class="amenity-check peer sr-only"
                                       :checked="formData.amenities.includes('{{ $key }}')"
                                       @change="toggleAmenity('{{ $key }}')">
                                <div class="flex items-center gap-2 px-3 py-2.5 border-2 border-gray-200 rounded-xl transition-all hover:border-gray-300 peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                    <i class="fas {{ $cfg['icon'] }} {{ $cfg['color'] }} text-sm w-4 text-center transition-colors"></i>
                                    <span class="text-xs font-medium text-gray-700">{{ $amenities[$key] }}</span>
                                </div>
                            </label>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════
             ÉTAPE 5 — PHOTOS
        ═══════════════════════════════════════ --}}
        <div x-show="step === 5" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-3" x-transition:enter-end="opacity-100 translate-x-0">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-camera text-amber-600"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900">Photos du logement</h2>
                        <p class="text-xs text-gray-500">Des photos de qualité multiplient vos chances de location</p>
                    </div>
                </div>

                {{-- Conseils photos contexte africain --}}
                <div class="mb-5 p-4 bg-amber-50 border border-amber-100 rounded-2xl">
                    <p class="text-xs font-bold text-amber-800 mb-2"><i class="fas fa-lightbulb mr-1"></i>Conseils photos</p>
                    <ul class="text-xs text-amber-700 space-y-1">
                        <li>• Prenez en journée avec une bonne luminosité naturelle</li>
                        <li>• Montrez le groupe électrogène, la citerne et les équipements clés</li>
                        <li>• Incluez une photo du portail/entrée et du quartier</li>
                        <li>• Évitez les photos floues ou trop sombres</li>
                    </ul>
                </div>

                {{-- Zone upload --}}
                <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center transition-all"
                     @dragover.prevent="isDragging = true"
                     @dragleave.prevent="isDragging = false"
                     @drop.prevent="handleDrop($event)"
                     :class="isDragging ? 'border-emerald-500 bg-emerald-50' : 'hover:border-emerald-400 hover:bg-gray-50'">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-600 mb-3 font-medium">Glissez vos photos ici ou</p>
                    <label class="inline-block px-6 py-2.5 bg-emerald-600 text-white rounded-xl font-bold cursor-pointer hover:bg-emerald-700 transition-colors">
                        <i class="fas fa-folder-open mr-2"></i>Choisir les photos
                        <input type="file" name="images[]" multiple accept="image/*" class="hidden" id="images" @change="handleFileSelect($event)" required>
                    </label>
                    <p class="text-xs text-gray-400 mt-3">Max. 10 photos · 10 MB chacune · JPEG, PNG, WebP</p>
                </div>

                {{-- Grille prévisualisation --}}
                <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3 mt-5" x-show="imagePreview.length > 0">
                    <template x-for="(image, index) in imagePreview" :key="index">
                        <div class="img-item relative aspect-square rounded-xl overflow-hidden border-2 transition-colors"
                             :class="index === 0 ? 'border-amber-400 ring-2 ring-amber-200' : 'border-gray-200'">
                            <img :src="image" class="w-full h-full object-cover">
                            <div x-show="index === 0"
                                 class="absolute bottom-0 left-0 right-0 bg-amber-500/90 text-white text-[9px] font-bold text-center py-1">
                                <i class="fas fa-star mr-0.5"></i> Principale
                            </div>
                            <button type="button" @click="removeImage(index)"
                                    class="img-remove absolute top-1.5 right-1.5 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="flex items-center justify-between mt-3" x-show="imagePreview.length > 0">
                    <p class="text-xs text-gray-500">
                        <span class="font-bold text-gray-700" x-text="imagePreview.length"></span> photo(s) — la 1ère sera la photo principale
                    </p>
                    <span class="text-xs" :class="imagePreview.length < 3 ? 'text-amber-600' : 'text-emerald-600'" x-text="imagePreview.length < 3 ? '+ de photos recommandées' : '✓ Bon nombre de photos'"></span>
                </div>

                @error('images')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Récapitulatif avant soumission --}}
            <div class="mt-4 bg-emerald-50 border border-emerald-100 rounded-2xl p-5" x-show="formData.type && formData.city">
                <p class="text-sm font-bold text-emerald-800 mb-3"><i class="fas fa-check-circle mr-1"></i> Récapitulatif de votre annonce</p>
                <div class="grid grid-cols-2 gap-2 text-xs text-emerald-700">
                    <div><span class="font-semibold">Titre :</span> <span x-text="previewTitle()"></span></div>
                    <div><span class="font-semibold">Loyer affiché :</span> <span x-text="formatPrice(priceWithFees()) + ' FCFA/mois'"></span></div>
                    <div><span class="font-semibold">Chambres :</span> <span x-text="formData.bedrooms"></span></div>
                    <div><span class="font-semibold">Photos :</span> <span x-text="imagePreview.length + ' photo(s)'"></span></div>
                    <div><span class="font-semibold">Équipements :</span> <span x-text="formData.amenities.length + ' sélectionné(s)'"></span></div>
                </div>
            </div>
        </div>

        {{-- Boutons navigation --}}
        <div class="flex gap-3 pt-6 pb-2">
            <button type="button" x-show="step > 1" @click="prevStep()"
                    class="px-6 py-3.5 border-2 border-gray-200 text-gray-700 rounded-2xl font-bold hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Précédent
            </button>
            <button type="button" x-show="step < 5" @click="nextStep()"
                    class="flex-1 py-3.5 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition-colors shadow-lg shadow-emerald-500/25">
                Continuer <i class="fas fa-arrow-right ml-2"></i>
            </button>
            <button type="button" x-show="step === 5" @click="handleSubmit()"
                    :disabled="isSubmitting"
                    class="flex-1 py-3.5 bg-gradient-to-r from-emerald-600 to-green-600 text-white rounded-2xl font-bold shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40 transition-all hover:-translate-y-0.5 disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                <span x-show="!isSubmitting"><i class="fas fa-paper-plane mr-2"></i>Publier l'annonce</span>
                <span x-show="isSubmitting"><i class="fas fa-spinner fa-spin mr-2"></i>Publication en cours...</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function propertyForm() {
    return {
        step: 1,
        maxStep: 1,
        isDragging: false,
        isSubmitting: false,
        imagePreview: [],
        imageFiles: [],
        map: null,
        marker: null,

        formData: {
            type:             '{{ old('type') }}',
            description:      `{{ old('description') }}`,
            bedrooms:         {{ old('bedrooms', 1) }},
            bathrooms:        {{ old('bathrooms', 1) }},
            beds:             {{ old('beds', 1) }},
            monthly_price:    '{{ old('monthly_price') }}',
            deposit:          '{{ old('deposit') }}',
            visit_price:      '{{ old('visit_price') }}',
            requires_deposit: {{ old('requires_deposit', 1) ? 'true' : 'false' }},
            city:             '{{ old('city') }}',
            neighborhood:     '{{ old('neighborhood') }}',
            address:          '{{ old('address') }}',
            landmark:         '{{ old('landmark') }}',
            latitude:         '{{ old('latitude') }}',
            longitude:        '{{ old('longitude') }}',
            amenities:        @json(old('amenities', [])),
        },

        // Quartiers par ville (Gabon)
        quartiers: {
            'Libreville':   ['Nombakélé','Glass','Akébé-Ville','Nkembo','Owendo','PK5','PK8','PK12','PK15','Angondjé','Île de Cocotiers','Gros Bouquet','Belle Vue','Sable','Louis','Batterie IV','Montagne Sainte','Plein-Ciel','Oloumi','Sotega','Alibandeng','Nzeng-Ayong','Okala'],
            'Port-Gentil':  ['Dixville','Balise','Grand Village','Ozouri','Sogara','Santa-Clara','Village Charbonnier','Awoungou','Bwanampoyo'],
            'Franceville':  ['Mikouyi','Mvengue','Potos','Léconi','Mounana','Moanda-centre'],
            'Oyem':         ['Centre-ville','Quartier Présidentiel','Nnem-Biyeng','Mendong'],
            'Moanda':       ['Centre','Comilog','Moulengui','Mboukou'],
            'Mouila':       ['Centre','Bongolo','Ngoumba'],
            'Lambaréné':    ['Centre','Adouma','Nkemé','Baka'],
            'Tchibanga':    ['Centre','Quartier Bas'],
            'Koulamoutou':  ['Centre','Haut Quartier'],
            'Makokou':      ['Centre','Booué'],
        },

        neighborhoodSuggestions() {
            return this.quartiers[this.formData.city] || [];
        },

        previewTitle() {
            const types = {
                'appartement': 'Appartement', 'maison': 'Maison', 'studio': 'Studio',
                'villa': 'Villa', 'chambre': 'Chambre'
            };
            const t = types[this.formData.type] || '';
            const q = this.formData.neighborhood ? this.formData.neighborhood + ', ' : '';
            const c = this.formData.city;
            if (!t || !c) return '';
            return `${t} à ${q}${c}`;
        },

        autoFillDeposit() {
            if (!this.formData.deposit && this.formData.monthly_price) {
                this.formData.deposit = this.formData.monthly_price;
            }
        },

        priceWithFees() {
            const base = parseInt(this.formData.monthly_price) || 0;
            return base + Math.round(base * 0.08) + 400;
        },

        visitTotal() {
            const base = parseInt(this.formData.visit_price) || 0;
            return base + Math.round(base * 0.08) + 400;
        },

        formatPrice(v) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(v));
        },

        goToStep(n) {
            if (n <= this.maxStep) { this.step = n; this.$nextTick(() => window.scrollTo({top:0,behavior:'smooth'})); }
        },

        nextStep() {
            if (!this.validateStep()) return;
            this.step++;
            this.maxStep = Math.max(this.maxStep, this.step);
            this.$nextTick(() => {
                window.scrollTo({top:0,behavior:'smooth'});
                if (this.step === 3) this.initMap();
            });
        },

        prevStep() {
            this.step--;
            this.$nextTick(() => window.scrollTo({top:0,behavior:'smooth'}));
        },

        validateStep() {
            if (this.step === 1) {
                if (!this.formData.type) { this.toast('Sélectionnez un type de logement.'); return false; }
                if (this.formData.description.length < 30) { this.toast('La description doit faire au moins 30 caractères.'); return false; }
            }
            if (this.step === 2) {
                if (!this.formData.monthly_price || parseInt(this.formData.monthly_price) < 10000) {
                    this.toast('Indiquez un loyer valide (minimum 10 000 FCFA).'); return false;
                }
                if (!this.formData.visit_price && this.formData.visit_price !== 0) {
                    this.toast('Indiquez un prix de visite (peut être 0).'); return false;
                }
            }
            if (this.step === 3) {
                if (!this.formData.city) { this.toast('Sélectionnez la ville.'); return false; }
                if (!this.formData.address) { this.toast("Indiquez l'adresse ou la localisation."); return false; }
            }
            return true;
        },

        toast(msg) {
            alert(msg);
        },

        toggleAmenity(key) {
            const idx = this.formData.amenities.indexOf(key);
            if (idx > -1) this.formData.amenities.splice(idx, 1);
            else this.formData.amenities.push(key);
        },

        handleFileSelect(e) { this.processFiles(e.target.files); },
        handleDrop(e) { this.isDragging = false; this.processFiles(e.dataTransfer.files); },

        processFiles(files) {
            Array.from(files).forEach(file => {
                if (this.imagePreview.length >= 10) { alert('Maximum 10 photos.'); return; }
                if (file.size > 10 * 1024 * 1024) { alert(`${file.name} dépasse 10 MB.`); return; }
                if (!file.type.startsWith('image/')) { alert(`${file.name} n'est pas une image.`); return; }
                const reader = new FileReader();
                reader.onload = e => this.imagePreview.push(e.target.result);
                reader.readAsDataURL(file);
                this.imageFiles.push(file);
            });
            this.syncFileInput();
        },

        removeImage(idx) {
            this.imagePreview.splice(idx, 1);
            this.imageFiles.splice(idx, 1);
            this.syncFileInput();
        },

        syncFileInput() {
            const dt = new DataTransfer();
            this.imageFiles.forEach(f => dt.items.add(f));
            document.getElementById('images').files = dt.files;
        },

        initMap() {
            if (this.map) { setTimeout(() => this.map.invalidateSize(), 100); return; }
            const el = document.getElementById('location-map');
            if (!el) return;
            const lat = parseFloat(this.formData.latitude) || 0.4162;
            const lng = parseFloat(this.formData.longitude) || 9.4673;
            this.map = L.map('location-map').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(this.map);
            if (this.formData.latitude && this.formData.longitude) {
                this.marker = L.marker([lat, lng]).addTo(this.map);
            }
            this.map.on('click', e => {
                this.formData.latitude  = e.latlng.lat.toFixed(6);
                this.formData.longitude = e.latlng.lng.toFixed(6);
                if (this.marker) this.marker.setLatLng(e.latlng);
                else this.marker = L.marker(e.latlng).addTo(this.map);
            });
            setTimeout(() => this.map.invalidateSize(), 200);
        },

        handleSubmit() {
            if (this.imagePreview.length === 0) { alert('Ajoutez au moins une photo.'); return; }
            if (!this.validateStep()) return;
            this.isSubmitting = true;

            // Inject landmark into address if filled
            if (this.formData.landmark) {
                const addrInput = document.querySelector('[name="address"]');
                if (addrInput && !addrInput.value.includes(this.formData.landmark)) {
                    addrInput.value = addrInput.value + ' — Repère : ' + this.formData.landmark;
                }
            }

            this.$nextTick(() => {
                document.querySelector('form').submit();
            });
        },

        init() {
            // Si retour arrière avec old(), re-initialiser l'étape
            @if($errors->any())
            this.step = 1;
            this.maxStep = 5;
            @endif
        }
    };
}
</script>
@endpush
