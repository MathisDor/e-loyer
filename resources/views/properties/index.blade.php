<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Rechercher un logement - E-Loyer</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/eloyer-logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        .leaflet-popup-content-wrapper {
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        }
        .leaflet-popup-content {
            margin: 0;
            min-width: 220px;
        }
        .property-marker {
            background: #16a34a;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            white-space: nowrap;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.4);
            border: 2px solid white;
            cursor: pointer;
            transition: all 0.2s;
        }
        .property-marker:hover,
        .property-marker.active {
            background: #dc2626;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
            transform: scale(1.1);
        }
        
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* ===== ÉTATS PAR DÉFAUT (Mobile First) ===== */
        .desktop-layout {
            display: none;
        }
        .mobile-layout {
            display: block;
        }
        .map-container {
            position: relative;
            height: 100%;
        }
        #desktop-map {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
        }
        
        /* ===== DESKTOP (>=1024px) - Affiche la carte ===== */
        @media (min-width: 1024px) {
            .desktop-layout {
                display: flex !important;
                height: calc(100vh - 57px);
            }
            .mobile-layout {
                display: none !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased" x-data="propertiesApp()">
    
    <!-- ========== HEADER ========== -->
    <header class="bg-white border-b border-gray-100 sticky top-0 z-50">
        <div class="px-4 py-3">
            <div class="flex items-center gap-3">
                <!-- Logo / Retour -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 flex-shrink-0">
                    <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-8 w-auto">
                    <span class="text-lg font-bold text-green-600 hidden sm:inline">E-Loyer</span>
                </a>
                
                <!-- Barre de recherche -->
                <form action="{{ route('properties.index') }}" method="GET" class="flex-1 max-w-xl" id="search-form">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" 
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Rechercher un quartier, une ville..." 
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-100 border-0 rounded-full text-sm focus:bg-white focus:ring-2 focus:ring-green-500 transition-all">
                        <!-- Champs cachés pour garder les filtres -->
                        <input type="hidden" name="city" value="{{ request('city') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <input type="hidden" name="bedrooms" value="{{ request('bedrooms') }}">
                        <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                        <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                    </div>
                </form>
                
                <!-- Bouton Filtres -->
                <button @click="showFilters = true" 
                        class="relative flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-full text-sm font-medium hover:border-green-500 hover:text-green-600 transition-all flex-shrink-0">
                    <i class="fas fa-sliders-h"></i>
                    <span class="hidden sm:inline">Filtres</span>
                    @php
                        $activeFilters = collect(['city', 'type', 'bedrooms', 'min_price', 'max_price'])->filter(fn($f) => request($f))->count();
                    @endphp
                    @if($activeFilters > 0)
                        <span class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-green-500 text-white text-[10px] rounded-full flex items-center justify-center font-bold">
                            {{ $activeFilters }}
                        </span>
                    @endif
                </button>
                
                <!-- User Menu -->
                @auth
                    <a href="{{ route('dashboard') }}" class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 border-2 border-gray-200 hover:border-green-500 transition-colors">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                    </a>
                @else
                    <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-600 hover:text-green-600 transition-colors">
                        <i class="fas fa-user"></i>
                        <span>Connexion</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>
    
    <!-- ========== DESKTOP LAYOUT (PC uniquement - lg+) ========== -->
    <div class="desktop-layout">
        <!-- Properties List (Left side - scrollable) -->
        <div class="w-[55%] xl:w-[60%] overflow-y-auto hide-scrollbar bg-gray-50 border-r border-gray-200">
            <div class="p-4">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-sm font-bold text-gray-900">{{ $properties->total() }} logements disponibles</h1>
                    <select class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Plus récents</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix ↑</option>
                        <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix ↓</option>
                    </select>
                </div>
                
                @if($properties->count() > 0)
                    <!-- Grille responsive : 3 colonnes desktop / 1 mobile -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($properties as $property)
                            <div @mouseenter="highlightMarker({{ $property->id }})"
                                 @mouseleave="unhighlightMarker({{ $property->id }})">
                                @include('components.property-card', ['property' => $property])
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-xs">{{ $properties->links() }}</div>
                @else
                    <div class="bg-white rounded-2xl p-10 text-center">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <h3 class="font-semibold text-gray-900 mb-2">Aucun résultat</h3>
                        <p class="text-gray-500 text-sm mb-4">Modifiez vos critères de recherche</p>
                        <a href="{{ route('properties.index') }}" class="text-green-600 font-medium hover:underline">Réinitialiser les filtres</a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Map (Right side - FIXED) -->
        <div class="w-[45%] xl:w-[40%] map-container">
            <div id="desktop-map"></div>
        </div>
    </div>
    
    <!-- ========== MOBILE LAYOUT (Sans carte - <lg) ========== -->
    <div class="mobile-layout">
        <!-- Résumé des filtres actifs -->
        @if(request()->hasAny(['city', 'type', 'bedrooms', 'min_price', 'max_price']))
            <div class="bg-green-50 border-b border-green-100 px-4 py-2 flex items-center gap-2 overflow-x-auto hide-scrollbar">
                @if(request('city'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-green-200 rounded-full text-xs font-medium text-green-700">
                        <i class="fas fa-map-marker-alt text-[10px]"></i>{{ request('city') }}
                    </span>
                @endif
                @if(request('type'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-green-200 rounded-full text-xs font-medium text-green-700">
                        <i class="fas fa-home text-[10px]"></i>{{ $types[request('type')] ?? request('type') }}
                    </span>
                @endif
                @if(request('bedrooms'))
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-white border border-green-200 rounded-full text-xs font-medium text-green-700">
                        <i class="fas fa-bed text-[10px]"></i>{{ request('bedrooms') }}+ ch.
                    </span>
                @endif
                <a href="{{ route('properties.index') }}" class="text-green-600 text-xs font-medium whitespace-nowrap ml-auto">
                    <i class="fas fa-times mr-1"></i>Effacer
                </a>
            </div>
        @endif
        
        <!-- Liste des propriétés -->
        <div class="p-3">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-gray-900">{{ $properties->total() }} logements</h2>
                <select class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white" onchange="window.location.href=this.value">
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Récents</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Prix ↑</option>
                    <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Prix ↓</option>
                </select>
            </div>
            
            <!-- Cartes verticales sur mobile (1 colonne) -->
            <div class="grid grid-cols-1 gap-4">
                @forelse($properties as $property)
                    @include('components.property-card', ['property' => $property])
                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-home text-4xl text-gray-300 mb-4"></i>
                        <h3 class="font-semibold text-gray-900 mb-2">Aucun résultat</h3>
                        <p class="text-gray-500 text-sm mb-4">Modifiez vos critères</p>
                        <a href="{{ route('properties.index') }}" class="text-green-600 font-medium">Réinitialiser</a>
                    </div>
                @endforelse
            </div>
            
            @if($properties->hasPages())
                <div class="mt-4">{{ $properties->links() }}</div>
            @endif
        </div>
    </div>
    
    <!-- ========== MODAL FILTRES ========== -->
    <div x-show="showFilters" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         class="fixed inset-0 z-50 flex items-end lg:items-center justify-center"
         @click.self="showFilters = false">
        
        <div class="absolute inset-0 bg-black/50"></div>
        
        <div class="relative bg-white w-full lg:max-w-lg lg:rounded-2xl rounded-t-3xl max-h-[85vh] overflow-hidden"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
             x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 lg:scale-100 lg:opacity-100"
             x-transition:leave-end="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">Filtres</h2>
                <button @click="showFilters = false" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            
            <!-- Form -->
            <form id="filter-form" action="{{ route('properties.index') }}" method="GET" class="overflow-y-auto" style="max-height: calc(85vh - 140px);">
                <div class="p-4 space-y-5">
                    <!-- Ville -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-map-marker-alt text-green-500 mr-2"></i>Ville
                        </label>
                        <select name="city" class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            <option value="">Toutes les villes</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Type de bien -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-home text-green-500 mr-2"></i>Type de bien
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach($types as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="type" value="{{ $key }}" {{ request('type') === $key ? 'checked' : '' }} class="sr-only peer">
                                    <div class="px-3 py-2.5 border-2 border-gray-200 rounded-xl text-center text-sm font-medium peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 hover:border-green-300 transition-colors">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    <!-- Chambres -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-bed text-green-500 mr-2"></i>Chambres minimum
                        </label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++)
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="bedrooms" value="{{ $i }}" {{ request('bedrooms') == $i ? 'checked' : '' }} class="sr-only peer">
                                    <div class="py-3 border-2 border-gray-200 rounded-xl text-center font-medium peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 hover:border-green-300 transition-colors">
                                        {{ $i }}+
                                    </div>
                                </label>
                            @endfor
                        </div>
                    </div>
                    
                    <!-- Budget -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            <i class="fas fa-coins text-green-500 mr-2"></i>Budget (FCFA/mois)
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" 
                                   class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            <span class="text-gray-400">—</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max"
                                   class="flex-1 px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </div>
                    </div>
                    
                    <!-- Équipements -->
                    @if(isset($amenities) && count($amenities) > 0)
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-3">
                            <i class="fas fa-star text-green-500 mr-2"></i>Équipements
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($amenities as $key => $label)
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" name="amenities[]" value="{{ $key }}" 
                                           {{ in_array($key, (array) request('amenities', [])) ? 'checked' : '' }} 
                                           class="w-5 h-5 text-green-600 rounded border-gray-300 focus:ring-green-500">
                                    <span class="text-gray-700 text-sm">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </form>
            
            <!-- Footer -->
            <div class="p-4 border-t border-gray-100 flex gap-3 bg-white">
                <a href="{{ route('properties.index') }}" class="flex-1 py-3 text-center text-gray-600 font-semibold rounded-xl border-2 border-gray-200 hover:bg-gray-50 transition-colors">
                    Réinitialiser
                </a>
                <button type="submit" form="filter-form"
                        class="flex-1 py-3 text-center bg-green-600 text-white font-semibold rounded-xl hover:bg-green-700 transition-colors shadow-lg shadow-green-600/25">
                    Appliquer
                </button>
            </div>
        </div>
    </div>

@php
    $propertiesForMap = $properties->map(function($p) {
        $imageIds = ['1502672260266-1c1ef2d93688', '1560448204-e02f11c3d0e2', '1522708323590-d24dbb6b0267', '1493809842364-78817add7ffb', '1560185007-cde436f6a4d0'];
        return [
            'id' => $p->id,
            'title' => $p->title,
            'price' => $p->formatted_price,
            'type' => $p->type_name,
            'address' => $p->full_address,
            'bedrooms' => $p->bedrooms,
            'bathrooms' => $p->bathrooms,
            'lat' => $p->latitude ?? (0.4162 + (rand(-50, 50) / 1000)),
            'lng' => $p->longitude ?? (9.4673 + (rand(-50, 50) / 1000)),
            'url' => route('properties.show', $p->id),
            'image' => 'https://images.unsplash.com/photo-' . $imageIds[$p->id % count($imageIds)] . '?w=300&h=200&fit=crop'
        ];
    })->values()->toArray();
@endphp

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
const propertiesData = @json($propertiesForMap);
let desktopMap = null;
let markers = {};

// Initialisation de la carte (même méthode que page détails)
document.addEventListener('DOMContentLoaded', function() {
    const mapEl = document.getElementById('desktop-map');
    
    // Ne pas initialiser si on est sur mobile ou si l'élément n'existe pas
    if (!mapEl || window.innerWidth < 1024) return;
    
    // Centre par défaut (Libreville, Gabon)
    const defaultLat = 0.4162;
    const defaultLng = 9.4673;
    
    // Créer la carte (même config que page détails)
    desktopMap = L.map('desktop-map', { 
        scrollWheelZoom: false,
        zoomControl: true
    }).setView([defaultLat, defaultLng], 12);
    
    // Tile layer identique à la page détails
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(desktopMap);
    
    // Ajouter les marqueurs pour chaque propriété
    propertiesData.forEach(p => {
        if (!p.lat || !p.lng) return;
        
        const priceLabel = p.price.replace(' FCFA', '').replace('/mois', '');
        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div class="property-marker">${priceLabel}</div>`,
            iconSize: [100, 32],
            iconAnchor: [50, 16]
        });
        
        const marker = L.marker([p.lat, p.lng], { icon })
            .addTo(desktopMap)
            .bindPopup(`
                <a href="${p.url}" class="block" style="text-decoration: none; color: inherit;">
                    <img src="${p.image}" style="width: 100%; height: 100px; object-fit: cover; border-radius: 12px 12px 0 0;">
                    <div style="padding: 12px;">
                        <p style="font-size: 11px; color: #16a34a; font-weight: 600; margin: 0;">${p.type}</p>
                        <h3 style="font-size: 14px; font-weight: 700; color: #111; margin: 4px 0;">${p.title}</h3>
                        <p style="font-size: 11px; color: #6b7280; margin: 0;">${p.address}</p>
                        <p style="font-size: 14px; font-weight: 700; color: #16a34a; margin-top: 8px;">${p.price}</p>
                    </div>
                </a>
            `, { maxWidth: 260 });
        
        markers[p.id] = marker;
    });
    
    // Ajuster la vue pour montrer tous les marqueurs
    if (propertiesData.length > 0) {
        const validData = propertiesData.filter(p => p.lat && p.lng);
        if (validData.length > 0) {
            const bounds = L.latLngBounds(validData.map(p => [p.lat, p.lng]));
            desktopMap.fitBounds(bounds, { padding: [40, 40] });
        }
    }
    
    // Activer le scroll zoom au clic (comme page détails)
    desktopMap.on('click', () => desktopMap.scrollWheelZoom.enable());
    
    // Rafraîchir la taille après le rendu complet
    setTimeout(() => desktopMap.invalidateSize(), 200);
});

// Rafraîchir la carte lors du redimensionnement
window.addEventListener('resize', function() {
    if (desktopMap && window.innerWidth >= 1024) {
        setTimeout(() => desktopMap.invalidateSize(), 100);
    }
});

function propertiesApp() {
    return {
        showFilters: false,
        
        highlightMarker(id) {
            const m = markers[id];
            if (m) {
                const el = m.getElement();
                if (el) el.querySelector('.property-marker')?.classList.add('active');
                m.openPopup();
            }
        },
        
        unhighlightMarker(id) {
            const m = markers[id];
            if (m) {
                const el = m.getElement();
                if (el) el.querySelector('.property-marker')?.classList.remove('active');
                m.closePopup();
            }
        }
    }
}
</script>
</body>
</html>
