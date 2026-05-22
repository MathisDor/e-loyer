@extends('layouts.app')

@section('title', $property->title)
@section('description', Str::limit($property->description, 160))

@php
    // Images réelles — fallback Unsplash si aucune image uploadée
    $fallbacks = [
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=800&h=600&fit=crop',
    ];

    $realImages = $property->images_urls ?? [];
    if (empty($realImages)) {
        $base = $property->id % count($fallbacks);
        $realImages = array_map(fn($i) => $fallbacks[($base + $i) % count($fallbacks)], range(0, 4));
    }

    $mainImage      = $realImages[0];
    $gridImages     = array_slice($realImages, 1, 4);
    $totalImages    = count($realImages);

    // Prix
    $basePrice    = (float) $property->monthly_price;
    $commission   = round($basePrice * 0.08);
    $serviceFee   = 400;
    $displayPrice = $basePrice + $commission + $serviceFee;
    $entryTotal   = $displayPrice + (float) ($property->deposit ?? $basePrice);

    // Icons équipements
    $amenityIcons = [
        'groupe_electrogene' => ['icon'=>'fa-bolt',          'color'=>'text-amber-500'],
        'citerne_eau'        => ['icon'=>'fa-tint',           'color'=>'text-blue-500'],
        'forage'             => ['icon'=>'fa-water',          'color'=>'text-blue-600'],
        'eau_chaude'         => ['icon'=>'fa-hot-tub',        'color'=>'text-orange-500'],
        'fibre_optique'      => ['icon'=>'fa-network-wired',  'color'=>'text-indigo-500'],
        'wifi'               => ['icon'=>'fa-wifi',           'color'=>'text-blue-500'],
        'climatisation'      => ['icon'=>'fa-snowflake',      'color'=>'text-cyan-500'],
        'meuble'             => ['icon'=>'fa-couch',          'color'=>'text-purple-500'],
        'cuisine_equipee'    => ['icon'=>'fa-utensils',       'color'=>'text-orange-500'],
        'cuisine_exterieure' => ['icon'=>'fa-fire',           'color'=>'text-red-500'],
        'buanderie'          => ['icon'=>'fa-soap',           'color'=>'text-teal-500'],
        'gardien'            => ['icon'=>'fa-user-shield',    'color'=>'text-gray-600'],
        'securite_24h'       => ['icon'=>'fa-shield-alt',     'color'=>'text-emerald-600'],
        'cloture'            => ['icon'=>'fa-fence',          'color'=>'text-stone-500'],
        'porte_blindee'      => ['icon'=>'fa-lock',           'color'=>'text-gray-700'],
        'parking'            => ['icon'=>'fa-parking',        'color'=>'text-blue-600'],
        'jardin'             => ['icon'=>'fa-tree',           'color'=>'text-green-600'],
        'balcon'             => ['icon'=>'fa-door-open',      'color'=>'text-amber-600'],
        'terrasse'           => ['icon'=>'fa-umbrella-beach', 'color'=>'text-yellow-600'],
        'piscine'            => ['icon'=>'fa-swimming-pool',  'color'=>'text-sky-600'],
        'douche_externe'     => ['icon'=>'fa-shower',         'color'=>'text-blue-400'],
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    [x-cloak] { display:none !important; }
    .hide-scrollbar { -ms-overflow-style:none; scrollbar-width:none; }
    .hide-scrollbar::-webkit-scrollbar { display:none; }
    .property-marker {
        background:#16a34a; color:white; padding:6px 14px;
        border-radius:20px; font-weight:700; font-size:13px;
        box-shadow:0 4px 12px rgba(22,163,74,.4); border:2px solid white;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen pb-16" x-data="galleryModal()">

    {{-- Fil d'ariane --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-green-600 transition-colors">Accueil</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="{{ route('properties.index') }}" class="hover:text-green-600 transition-colors">Annonces</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium truncate max-w-xs">{{ $property->title }}</span>
        </div>
    </div>

    {{-- ── GALERIE ──────────────────────────────────────────────── --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="grid grid-cols-1 md:grid-cols-4 md:grid-rows-2 gap-2 rounded-2xl overflow-hidden" style="height:420px">
                {{-- Image principale --}}
                <div class="md:col-span-2 md:row-span-2 relative cursor-pointer group" @click="openGallery(0)">
                    <img src="{{ $mainImage }}" alt="{{ $property->title }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-300"></div>
                    @if($totalImages > 1)
                    <button @click.stop="openGallery(0)"
                            class="absolute bottom-3 left-3 px-3 py-1.5 bg-white/90 backdrop-blur-sm rounded-xl text-xs font-bold text-gray-800 hover:bg-white shadow-lg transition-colors">
                        <i class="fas fa-images mr-1.5"></i>{{ $totalImages }} photo{{ $totalImages > 1 ? 's' : '' }}
                    </button>
                    @endif
                </div>

                {{-- Images secondaires (grille 2×2) --}}
                @foreach($gridImages as $i => $img)
                <div class="relative cursor-pointer group hidden md:block overflow-hidden" @click="openGallery({{ $i + 1 }})">
                    <img src="{{ $img }}" alt="Photo {{ $i + 2 }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors duration-300"></div>
                    @if($i === 3 && $totalImages > 5)
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                        <span class="text-white font-bold text-lg">+{{ $totalImages - 5 }}</span>
                    </div>
                    @endif
                </div>
                @endforeach

                {{-- Compléter la grille si moins de 4 images secondaires --}}
                @for($i = count($gridImages); $i < 4; $i++)
                <div class="hidden md:block bg-gray-100"></div>
                @endfor
            </div>
        </div>
    </div>

    {{-- ── MODAL GALERIE ──────────────────────────────────────── --}}
    <div x-show="isOpen" x-cloak class="fixed inset-0 z-50"
         @keydown.escape.window="closeGallery()"
         @keydown.arrow-left.window="prevImage()"
         @keydown.arrow-right.window="nextImage()">
        <div class="absolute inset-0 bg-black/95 backdrop-blur-md" @click="closeGallery()"></div>
        <button @click="closeGallery()" class="absolute top-4 right-4 z-50 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-colors">
            <i class="fas fa-times text-lg"></i>
        </button>
        <div class="absolute top-4 left-4 z-50 px-3 py-1.5 bg-black/40 rounded-full text-white text-sm font-medium border border-white/20">
            <span x-text="`${currentIndex + 1} / ${images.length}`"></span>
        </div>
        <div class="relative w-full h-full flex flex-col justify-center">
            <div class="flex-1 flex items-center justify-center px-14 py-16 relative">
                <button @click.stop="prevImage()" class="absolute left-3 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all z-20">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <img :src="images[currentIndex]" :alt="`Photo ${currentIndex + 1}`"
                     class="max-w-full max-h-[72vh] object-contain rounded-xl shadow-2xl">
                <button @click.stop="nextImage()" class="absolute right-3 top-1/2 -translate-y-1/2 w-11 h-11 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white border border-white/20 transition-all z-20">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/80 to-transparent">
                <div class="flex items-center justify-center gap-2 overflow-x-auto pb-1 hide-scrollbar">
                    <template x-for="(img, idx) in images" :key="idx">
                        <button @click="goToImage(idx)"
                                class="flex-shrink-0 w-14 h-10 rounded-lg overflow-hidden border-2 transition-all"
                                :class="idx === currentIndex ? 'border-green-500 ring-2 ring-green-500/40 scale-110' : 'border-white/20 opacity-50 hover:opacity-80'">
                            <img :src="img" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ── CONTENU PRINCIPAL ──────────────────────────────────── --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid lg:grid-cols-3 gap-8">

            {{-- ── COLONNE GAUCHE (infos) ──────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- En-tête --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">{{ $property->type_name }}</span>
                                @if($property->is_available && $property->status === 'approuve')
                                    <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>Disponible
                                    </span>
                                @elseif($property->status === 'loue')
                                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold">Loué</span>
                                @elseif($property->status === 'en_attente')
                                    <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">En attente de validation</span>
                                @endif
                                @if($property->created_at->diffInDays() < 14)
                                    <span class="px-3 py-1 bg-yellow-400 text-yellow-900 rounded-full text-xs font-bold">Nouveau</span>
                                @endif
                            </div>
                            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 leading-tight">{{ $property->title }}</h1>
                            <p class="text-gray-500 flex items-center gap-2 mt-2 text-sm">
                                <i class="fas fa-map-marker-alt text-green-600"></i>
                                {{ $property->neighborhood ? $property->neighborhood.', ' : '' }}{{ $property->city }}
                                @if($property->address)
                                    · {{ $property->address }}
                                @endif
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-3xl font-extrabold text-green-600">{{ number_format($displayPrice, 0, ',', ' ') }}</p>
                            <p class="text-gray-400 text-sm font-medium">FCFA / mois (frais inclus)</p>
                            <p class="text-xs text-gray-400 mt-0.5">Loyer net : {{ number_format($basePrice, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-gray-100">
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-bed text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg leading-none">{{ $property->bedrooms }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Chambre{{ $property->bedrooms > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-9 h-9 bg-cyan-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shower text-cyan-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg leading-none">{{ $property->bathrooms }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Douche{{ $property->bathrooms > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        @if($property->beds && $property->beds > 0)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-couch text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg leading-none">{{ $property->beds }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Lit{{ $property->beds > 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        @endif
                        @if($property->deposit)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-shield-alt text-amber-600"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm leading-none">{{ number_format($property->deposit, 0, ',', ' ') }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Caution (FCFA)</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Description --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" x-data="{ expanded: false }">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">À propos de ce logement</h2>
                    <div class="text-gray-600 leading-relaxed text-sm"
                         :class="expanded ? '' : 'line-clamp-5'">
                        {!! nl2br(e($property->description)) !!}
                    </div>
                    @if(strlen($property->description) > 300)
                    <button @click="expanded = !expanded"
                            class="mt-3 text-green-600 font-semibold text-sm hover:underline flex items-center gap-1">
                        <span x-text="expanded ? 'Voir moins' : 'Lire la suite'"></span>
                        <i class="fas transition-transform duration-200" :class="expanded ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                    @endif
                </div>

                {{-- Équipements --}}
                @if($property->amenities && count($property->amenities) > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Équipements & Services</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($property->amenities as $key)
                        @php
                            $label = \App\Models\Property::AMENITIES[$key] ?? ucfirst(str_replace('_', ' ', $key));
                            $icon  = $amenityIcons[$key] ?? ['icon'=>'fa-check-circle','color'=>'text-green-500'];
                        @endphp
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="w-8 h-8 bg-white rounded-lg shadow-sm flex items-center justify-center flex-shrink-0">
                                <i class="fas {{ $icon['icon'] }} {{ $icon['color'] }} text-sm"></i>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Localisation --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <i class="fas fa-map-marker-alt text-green-600"></i>Localisation
                    </h2>

                    <div class="flex flex-wrap gap-3 mb-4 text-sm">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-xl text-green-700 font-medium">
                            <i class="fas fa-city text-xs"></i>{{ $property->city }}
                        </div>
                        @if($property->neighborhood)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-xl text-gray-700">
                            <i class="fas fa-map-pin text-xs text-gray-500"></i>{{ $property->neighborhood }}
                        </div>
                        @endif
                        @if($property->address)
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 rounded-xl text-gray-600 text-xs">
                            <i class="fas fa-road text-xs text-gray-400"></i>{{ $property->address }}
                        </div>
                        @endif
                    </div>

                    <div id="property-map" class="w-full h-64 rounded-xl bg-gray-100 overflow-hidden border border-gray-200"></div>

                    @if($property->latitude && $property->longitude)
                    <p class="text-xs text-gray-400 mt-2 flex items-center gap-1">
                        <i class="fas fa-crosshairs"></i>
                        Position exacte épinglée · {{ number_format($property->latitude, 4) }}, {{ number_format($property->longitude, 4) }}
                    </p>
                    @else
                    <p class="text-xs text-gray-400 mt-2"><i class="fas fa-info-circle mr-1"></i>Carte centrée sur {{ $property->city }}</p>
                    @endif
                </div>

                {{-- Avis --}}
                @if($property->reviews->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-xl font-bold text-gray-900">Avis locataires</h2>
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-yellow-50 rounded-xl">
                            <i class="fas fa-star text-yellow-400"></i>
                            <span class="font-bold text-gray-900">{{ number_format($property->average_rating, 1) }}</span>
                            <span class="text-gray-500 text-sm">({{ $property->reviews_count }})</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        @foreach($property->reviews->take(5) as $review)
                        <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-0">
                            <img src="{{ $review->reviewer->avatar_url }}" alt="{{ $review->reviewer->name }}"
                                 class="w-11 h-11 rounded-2xl object-cover flex-shrink-0">
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $review->reviewer->name }}</span>
                                    <span class="text-gray-400 text-xs">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center gap-0.5 mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-200' }}"></i>
                                    @endfor
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">{{ $review->comment }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ── COLONNE DROITE (sidebar) ──────────────── --}}
            <div class="space-y-5">

                {{-- Card Action (sticky) --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:sticky lg:top-20">
                    {{-- Prix --}}
                    <div class="text-center mb-5">
                        <p class="text-4xl font-extrabold text-gray-900">{{ number_format($displayPrice, 0, ',', ' ') }}</p>
                        <p class="text-gray-400 text-sm font-medium">FCFA / mois</p>
                        <div class="mt-3 space-y-1.5 text-xs text-gray-400">
                            <div class="flex justify-between px-2">
                                <span>Loyer de base</span>
                                <span class="font-medium text-gray-600">{{ number_format($basePrice, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between px-2">
                                <span>Commission plateforme (8%)</span>
                                <span class="font-medium text-gray-600">+ {{ number_format($commission, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="flex justify-between px-2">
                                <span>Frais de service</span>
                                <span class="font-medium text-gray-600">+ {{ number_format($serviceFee, 0, ',', ' ') }} FCFA</span>
                            </div>
                        </div>
                    </div>

                    {{-- Montant entrée --}}
                    @if($property->deposit)
                    <div class="p-3 bg-amber-50 border border-amber-100 rounded-xl mb-4 text-center">
                        <p class="text-xs text-amber-700 font-medium">Montant total à l'entrée</p>
                        <p class="text-xl font-extrabold text-amber-700 mt-0.5">{{ number_format($entryTotal, 0, ',', ' ') }} FCFA</p>
                        <p class="text-xs text-amber-600 mt-0.5">1er mois + caution ({{ number_format($property->deposit, 0, ',', ' ') }} FCFA)</p>
                    </div>
                    @endif

                    {{-- Boutons --}}
                    @auth
                        @if(auth()->id() === $property->owner_id)
                            {{-- ─── PROPRIÉTAIRE ─── --}}
                            <a href="{{ route('properties.edit', $property) }}"
                               class="block w-full py-3.5 bg-green-600 text-white text-center rounded-xl font-bold hover:bg-green-700 transition-colors mb-3 text-sm">
                                <i class="fas fa-edit mr-2"></i>Modifier l'annonce
                            </a>

                        @elseif(auth()->user()->isLocataire())

                            {{-- ─── CAS 1 : LOCATAIRE ACTUEL de ce bien ─── --}}
                            @if($tenantActiveContract)
                            <div class="mb-4">
                                <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl mb-3">
                                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-home text-emerald-600 text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-emerald-800 text-sm">Vous louez actuellement ce logement</p>
                                        <p class="text-xs text-emerald-600 mt-0.5">
                                            Contrat actif jusqu'au {{ $tenantActiveContract->end_date->format('d/m/Y') }}
                                        </p>
                                        <p class="text-xs text-emerald-600">
                                            Mois {{ $tenantActiveContract->months_paid }}/{{ $tenantActiveContract->duration_months }}
                                        </p>
                                    </div>
                                </div>
                                <a href="{{ route('contracts.show', $tenantActiveContract) }}"
                                   class="block w-full py-3 bg-emerald-600 text-white text-center rounded-xl font-bold hover:bg-emerald-700 transition-colors text-sm mb-3">
                                    <i class="fas fa-file-contract mr-2"></i>Voir mon contrat
                                </a>
                            </div>

                            {{-- ─── CAS 2 : VISITE EN COURS / EN ATTENTE ─── --}}
                            @elseif($tenantActiveVisit && in_array($tenantActiveVisit->status, ['reservee', 'en_cours']))
                            @php
                                $vd = match($tenantActiveVisit->status) {
                                    'reservee' => ['label'=>'Visite en attente',  'sub'=>'En attente de confirmation du démarcheur', 'bg'=>'bg-amber-500',  'icon'=>'fa-clock',   'pulse'=>true],
                                    'en_cours' => ['label'=>'Visite en cours',    'sub'=>'Le démarcheur est sur place',              'bg'=>'bg-blue-600',   'icon'=>'fa-walking', 'pulse'=>true],
                                    default    => ['label'=>'Visite active',      'sub'=>'',                                         'bg'=>'bg-gray-600',   'icon'=>'fa-eye',     'pulse'=>false],
                                };
                            @endphp
                            <div class="mb-4">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl mb-3">
                                    <div class="w-10 h-10 {{ $vd['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas {{ $vd['icon'] }} text-white {{ $vd['pulse'] ? 'animate-pulse' : '' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">{{ $vd['label'] }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $vd['sub'] }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('visits.show', $tenantActiveVisit) }}"
                                   class="block w-full py-3.5 {{ $vd['bg'] }} text-white text-center rounded-xl font-bold hover:opacity-90 transition-all mb-3 text-sm">
                                    <i class="fas fa-route mr-2"></i>Suivre ma visite
                                </a>
                            </div>

                            {{-- ─── CAS 3 : VISITE TERMINÉE — AVIS EN ATTENTE ─── --}}
                            @elseif($tenantActiveVisit && $tenantActiveVisit->status === 'terminee' && is_null($tenantActiveVisit->property_accepted))
                            <div class="mb-4">
                                <div class="flex items-center gap-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl mb-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-star text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 text-sm">Visite effectuée</p>
                                        <p class="text-xs text-indigo-700 mt-0.5">Donnez votre avis sur ce logement</p>
                                    </div>
                                </div>
                                <a href="{{ route('visits.show', $tenantActiveVisit) }}"
                                   class="block w-full py-3.5 bg-indigo-600 text-white text-center rounded-xl font-bold hover:bg-indigo-700 transition-all mb-3 text-sm">
                                    <i class="fas fa-thumbs-up mr-2"></i>Accepter ou refuser ce logement
                                </a>
                            </div>

                            {{-- ─── CAS 4 : AUCUN LIEN ACTIF — boutons normaux ─── --}}
                            @elseif($property->is_available && $property->status === 'approuve')
                            @if($property->visit_price)
                            <a href="{{ route('visits.create', $property) }}"
                               class="block w-full py-3.5 bg-blue-600 text-white text-center rounded-xl font-bold hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-600/25 transition-all mb-3 text-sm">
                                <i class="fas fa-eye mr-2"></i>Réserver une visite
                                @if(isset($property->formatted_visit_total_amount))
                                <span class="text-blue-200 font-normal text-xs block">{{ $property->formatted_visit_total_amount }}</span>
                                @endif
                            </a>
                            @endif
                            <a href="{{ route('bookings.create', $property) }}"
                               class="block w-full py-3.5 bg-green-600 text-white text-center rounded-xl font-bold hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/25 transition-all mb-3 text-sm">
                                <i class="fas fa-calendar-check mr-2"></i>Réserver directement
                            </a>

                            @elseif($property->status === 'loue')
                            <div class="w-full py-3 bg-gray-100 text-gray-500 text-center rounded-xl font-medium mb-3 text-sm">
                                <i class="fas fa-lock mr-2"></i>Ce logement est actuellement loué
                            </div>
                            @endif

                            {{-- Contacter (toujours visible sauf si c'est son propre bien) --}}
                            <form action="{{ route('messages.contact', $property) }}" method="POST">
                                @csrf
                                <input type="hidden" name="message" value="Bonjour, je suis intéressé(e) par votre logement « {{ $property->title }} ». Pouvez-vous me donner plus d'informations ?">
                                <button type="submit"
                                        class="w-full py-3 border-2 border-green-600 text-green-600 rounded-xl font-bold hover:bg-green-50 transition-colors text-sm">
                                    <i class="fas fa-comment-alt mr-2"></i>Contacter le propriétaire
                                </button>
                            </form>
                        @endif

                    @else
                        {{-- ─── NON CONNECTÉ ─── --}}
                        <a href="{{ route('register') }}"
                           class="block w-full py-3.5 bg-green-600 text-white text-center rounded-xl font-bold hover:bg-green-700 transition-colors mb-3 text-sm">
                            <i class="fas fa-user-plus mr-2"></i>Créer un compte pour réserver
                        </a>
                        <a href="{{ route('login') }}"
                           class="block w-full py-3 border-2 border-gray-200 text-gray-600 text-center rounded-xl font-semibold hover:bg-gray-50 transition-colors text-sm">
                            J'ai déjà un compte
                        </a>
                    @endguest

                    @auth
                    <button onclick="toggleFavorite({{ $property->id }}, this)"
                            class="w-full py-3 mt-3 text-gray-500 hover:text-red-500 font-medium transition-colors text-sm flex items-center justify-center gap-2">
                        <i class="{{ $isFavorite ? 'fas text-red-500' : 'far' }} fa-heart"></i>
                        <span>{{ $isFavorite ? 'Retirer des favoris' : 'Ajouter aux favoris' }}</span>
                    </button>
                    @endauth

                    <div class="border-t border-gray-100 mt-4 pt-3 text-center text-xs text-gray-400">
                        <i class="fas fa-shield-alt text-green-600 mr-1"></i>Paiement sécurisé · Mobile Money
                    </div>
                </div>

                {{-- Card Propriétaire --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-900 mb-4 text-sm uppercase tracking-wide text-gray-500">Propriétaire</h3>
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ $property->owner->avatar_url }}" alt="{{ $property->owner->name }}"
                             class="w-14 h-14 rounded-2xl object-cover ring-2 ring-gray-100">
                        <div>
                            <p class="font-bold text-gray-900">{{ $property->owner->name }}</p>
                            @if($property->owner->is_verified)
                                <p class="text-xs text-emerald-600 font-medium flex items-center gap-1 mt-0.5">
                                    <i class="fas fa-check-circle"></i>Vérifié
                                </p>
                            @endif
                            <p class="text-xs text-gray-400 mt-0.5">Membre depuis {{ $property->owner->created_at->format('M Y') }}</p>
                        </div>
                    </div>
                    @if($property->owner->city)
                    <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                        <i class="fas fa-map-marker-alt text-gray-400"></i>{{ $property->owner->city }}
                    </div>
                    @endif
                    <a href="{{ route('user.profile', $property->owner) }}"
                       class="block w-full py-2.5 text-center border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 hover:border-green-300 transition-colors">
                        Voir le profil complet
                    </a>
                </div>

                {{-- Propriétés similaires (sidebar) --}}
                @if($similarProperties->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-900 text-sm">Dans la même ville</h3>
                        <a href="{{ route('properties.index', ['city' => $property->city]) }}"
                           class="text-xs text-green-600 font-semibold hover:underline">Voir tout</a>
                    </div>
                    <div class="space-y-3">
                        @foreach($similarProperties as $similar)
                        @php
                            $simImg = $similar->main_image ?? $fallbacks[$similar->id % count($fallbacks)];
                        @endphp
                        <a href="{{ route('properties.show', $similar) }}"
                           class="flex gap-3 p-2.5 hover:bg-gray-50 rounded-xl transition-colors group">
                            <div class="w-20 h-16 rounded-xl overflow-hidden flex-shrink-0">
                                <img src="{{ $simImg }}" alt="{{ $similar->title }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-xs truncate group-hover:text-green-600 transition-colors">{{ $similar->title }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    <i class="fas fa-map-marker-alt mr-0.5 text-green-500"></i>{{ $similar->neighborhood ?: $similar->city }}
                                </p>
                                <p class="font-bold text-green-600 text-xs mt-1">{{ $similar->formatted_price_with_fees }}<span class="text-gray-400 font-normal">/mois</span></p>
                                <div class="flex items-center gap-2 text-[10px] text-gray-400 mt-0.5">
                                    <span><i class="fas fa-bed mr-0.5"></i>{{ $similar->bedrooms }}</span>
                                    <span><i class="fas fa-bath mr-0.5"></i>{{ $similar->bathrooms }}</span>
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>{{-- /sidebar --}}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
function galleryModal() {
    return {
        isOpen: false,
        currentIndex: 0,
        images: @json($realImages),

        openGallery(index = 0) {
            this.currentIndex = index;
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },
        closeGallery() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },
        nextImage() {
            if (!this.isOpen) return;
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        },
        prevImage() {
            if (!this.isOpen) return;
            this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        },
        goToImage(index) { this.currentIndex = index; }
    };
}

function toggleFavorite(propertyId, button) {
    fetch(`/proprietes/${propertyId}/favori`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        const icon = button.querySelector('i');
        const span = button.querySelector('span');
        if (data.favorited) {
            icon.className = 'fas fa-heart text-red-500';
            if (span) span.textContent = 'Retirer des favoris';
        } else {
            icon.className = 'far fa-heart';
            if (span) span.textContent = 'Ajouter aux favoris';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const mapEl = document.getElementById('property-map');
    if (!mapEl) return;

    const lat = {{ $property->latitude ?? 'null' }};
    const lng = {{ $property->longitude ?? 'null' }};
    const hasCoords = lat !== null && lng !== null;

    const cityCoords = {
        'Libreville': [0.4162, 9.4673],
        'Port-Gentil': [-0.7193, 8.7815],
        'Franceville': [-1.6333, 13.5833],
        'Oyem': [1.6147, 11.5794],
        'Moanda': [-1.5667, 13.2],
        'Mouila': [-1.8667, 11.05],
        'Lambaréné': [-0.7, 10.2167],
    };

    const defaultCoords = cityCoords['{{ $property->city }}'] ?? [0.4162, 9.4673];
    const center = hasCoords ? [lat, lng] : defaultCoords;
    const zoom = hasCoords ? 16 : 13;

    const map = L.map('property-map', { scrollWheelZoom: false }).setView(center, zoom);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap', maxZoom: 19
    }).addTo(map);

    const icon = L.divIcon({
        className: '',
        html: `<div class="property-marker">{{ number_format($displayPrice, 0, ',', ' ') }} FCFA</div>`,
        iconSize: [160, 36],
        iconAnchor: [80, 18]
    });

    if (hasCoords) {
        L.marker([lat, lng], { icon }).addTo(map);
        // Cercle de zone si pas de coords exactes
    } else {
        L.circle(center, { radius: 800, color: '#16a34a', fillColor: '#16a34a', fillOpacity: 0.08, weight: 2 }).addTo(map);
        L.marker(center, { icon }).addTo(map);
    }

    map.on('click', () => map.scrollWheelZoom.enable());
});
</script>
@endpush
