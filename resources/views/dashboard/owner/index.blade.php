<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Espace propriétaire — E-Loyer</title>
    <link rel="icon" type="image/png" href="{{ asset('img/eloyer-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }
        body { background: #F1F5F9; }

        /* Sidebar */
        .sidebar { width: 260px; transform: translateX(-100%); transition: transform .25s ease; }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0); position: fixed; top: 0; left: 0; bottom: 0; }
            .main-wrap { margin-left: 260px; }
            .bottom-nav, .burger-btn { display: none !important; }
        }

        /* Nav active state */
        .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 14px; border-radius: 10px;
                    font-size: 14px; font-weight: 500; color: #64748b; transition: all .15s ease; cursor: pointer;
                    width: 100%; text-align: left; background: none; border: none; }
        .nav-link:hover { background: #F1F5F9; color: #1e293b; }
        .nav-link.active { background: #EEF2FF; color: #4F46E5; font-weight: 600; }
        .nav-link .icon { width: 18px; text-align: center; font-size: 14px; }

        /* KPI cards */
        .kpi-card { background: white; border-radius: 14px; padding: 20px 22px; border: 1px solid #E2E8F0;
                    transition: box-shadow .15s, transform .15s; cursor: pointer; }
        .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); }

        /* Property card */
        .prop-card { background: white; border-radius: 14px; border: 1px solid #E2E8F0; overflow: hidden;
                     transition: box-shadow .15s, transform .15s; }
        .prop-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }

        /* Section heading */
        .section-title { font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase;
                         letter-spacing: .08em; margin-bottom: 14px; }

        /* Bottom nav mobile */
        .bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body x-data="{ tab: 'home', sidebar: false }">

{{-- Overlay mobile --}}
<div x-show="sidebar" @click="sidebar=false" x-cloak
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

{{-- ═══════════════════════════════ SIDEBAR ═══════════════════════════════ --}}
<aside class="sidebar fixed z-50 bg-white border-r border-slate-200 flex flex-col h-full"
       :class="{ 'open': sidebar }">

    {{-- Logo --}}
    <div class="flex items-center justify-between h-[60px] px-5 border-b border-slate-100 flex-shrink-0">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-7 w-auto">
            <span class="text-[17px] font-bold text-indigo-600">E-Loyer</span>
        </a>
        <button @click="sidebar=false" class="lg:hidden w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    {{-- User --}}
    <div class="px-4 py-4 border-b border-slate-100 flex-shrink-0">
        <div class="flex items-center gap-3">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-10 h-10 rounded-xl object-cover ring-2 ring-white shadow-sm flex-shrink-0">
            <div class="min-w-0">
                <p class="font-semibold text-slate-800 text-sm truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                    <span class="text-xs text-slate-500">Propriétaire</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
        <p class="section-title px-2 mb-3">Navigation</p>

        @php
        $navItems = [
            ['tab' => 'home',       'icon' => 'fa-home',           'label' => 'Tableau de bord'],
            ['tab' => 'properties', 'icon' => 'fa-building',       'label' => 'Mes biens',      'badge' => $stats['total_properties']],
            ['tab' => 'visits',     'icon' => 'fa-calendar-check', 'label' => 'Visites',         'badge' => $upcomingVisits->count()],
            ['tab' => 'contracts',  'icon' => 'fa-file-contract',  'label' => 'Contrats',        'badge' => $stats['active_contracts']],
            ['tab' => 'tenants',    'icon' => 'fa-users',          'label' => 'Locataires',      'badge' => $activeContracts->count()],
            ['tab' => 'bookings',   'icon' => 'fa-calendar-alt',   'label' => 'Réservations',    'badge' => $pendingBookings->count()],
        ];
        @endphp

        @foreach($navItems as $item)
        <button @click="tab='{{ $item['tab'] }}'; sidebar=false"
                :class="tab==='{{ $item['tab'] }}' ? 'active' : ''"
                class="nav-link">
            <i class="fas {{ $item['icon'] }} icon"></i>
            <span class="flex-1">{{ $item['label'] }}</span>
            @if(!empty($item['badge']) && $item['badge'] > 0)
                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-600 text-[11px] font-bold rounded-full">{{ $item['badge'] }}</span>
            @endif
        </button>
        @endforeach

        <div class="pt-4 mt-4 border-t border-slate-100 space-y-0.5">
            <p class="section-title px-2 mb-3">Général</p>
            <a href="{{ route('properties.create') }}"
               class="nav-link font-semibold !text-indigo-600 !bg-indigo-50 hover:!bg-indigo-100">
                <i class="fas fa-plus-circle icon"></i>
                <span>Publier un bien</span>
            </a>
            <a href="{{ route('messages.index') }}" class="nav-link">
                <i class="fas fa-envelope icon"></i>
                <span class="flex-1">Messages</span>
                @if(auth()->user()->unread_messages_count > 0)
                    <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[11px] font-bold rounded-full">{{ auth()->user()->unread_messages_count }}</span>
                @endif
            </a>
            <a href="{{ route('notifications.index') }}" class="nav-link">
                <i class="fas fa-bell icon"></i>
                <span>Notifications</span>
            </a>
            <a href="{{ route('profile.show') }}" class="nav-link">
                <i class="fas fa-user-cog icon"></i>
                <span>Mon profil</span>
            </a>
        </div>
    </nav>

    {{-- Logout --}}
    <div class="p-3 border-t border-slate-100 flex-shrink-0">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="nav-link w-full !text-red-500 hover:!bg-red-50">
                <i class="fas fa-sign-out-alt icon"></i>
                <span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

{{-- ═══════════════════════════════ MAIN WRAP ═══════════════════════════════ --}}
<div class="main-wrap min-h-screen pb-20 lg:pb-0">

    {{-- Top bar --}}
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200">
        <div class="flex items-center justify-between h-[60px] px-5">
            <div class="flex items-center gap-4">
                <button @click="sidebar=!sidebar" class="burger-btn w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <div class="hidden lg:block">
                    <p class="font-semibold text-slate-800 text-sm"
                       x-text="{ home:'Tableau de bord', properties:'Mes biens', visits:'Visites', contracts:'Contrats', tenants:'Locataires', bookings:'Réservations' }[tab]"></p>
                </div>
                <div class="flex items-center gap-2 lg:hidden">
                    <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-6 w-auto">
                    <span class="font-bold text-indigo-600 text-sm">E-Loyer</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('properties.create') }}"
                   class="hidden sm:flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-[13px] font-semibold hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Publier un bien
                </a>
                <a href="{{ route('messages.index') }}" class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-envelope text-sm"></i>
                    @if(auth()->user()->unread_messages_count > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
                </a>
                <a href="{{ route('notifications.index') }}" class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-bell text-sm"></i>
                    @if($pendingBookings->count() > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
    <div class="max-w-6xl mx-auto px-5 pt-4">
        @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4500)"
             class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm mb-3">
            <i class="fas fa-check-circle text-emerald-500 flex-shrink-0"></i>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show=false" class="text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{show:true}" x-show="show"
             class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm mb-3">
            <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="show=false" class="text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
        </div>
        @endif
    </div>
    @endif

    <main class="max-w-6xl mx-auto px-5 py-6">
        @php $imgIds = ['1502672260266-1c1ef2d93688','1560448204-e02f11c3d0e2','1522708323590-d24dbb6b0267','1493809842364-78817add7ffb','1560185007-cde436f6a4d0','1484154218962-a197022b5858','1512917774080-9991f1c4c750','1600596542815-ffad4c1539a9']; @endphp

        {{-- ═══════════════════════ TAB: TABLEAU DE BORD ═══════════════════════ --}}
        <div x-show="tab==='home'" x-cloak>

            {{-- Header accueil --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Bonjour, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
                    <p class="text-slate-500 text-sm mt-1">Voici un résumé de votre activité</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="hidden lg:flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                    <i class="fas fa-plus text-xs"></i> Publier un bien
                </a>
            </div>

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button @click="tab='properties'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-indigo-600 text-sm"></i>
                        </div>
                        @if($stats['total_properties'] > 0)
                        <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            {{ $stats['occupancy_rate'] }}% occupé
                        </span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['total_properties'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Biens publiés</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['rented_properties'] }} loué(s)</p>
                </button>

                <div class="kpi-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-coins text-emerald-600 text-sm"></i>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400">/ mois</span>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">Revenus (FCFA)</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['active_contracts'] }} contrat(s) actif(s)</p>
                </div>

                <button @click="tab='visits'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-check text-violet-600 text-sm"></i>
                        </div>
                        @if($stats['pending_visits'] > 0)
                        <span class="w-5 h-5 bg-violet-600 text-white text-[11px] font-bold rounded-full flex items-center justify-center">{{ $stats['pending_visits'] }}</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['pending_visits'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Visites à venir</p>
                    <p class="text-xs text-slate-400 mt-1">Voir le calendrier</p>
                </button>

                <button @click="tab='bookings'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-amber-600 text-sm"></i>
                        </div>
                        @if($pendingBookings->count() > 0)
                        <span class="w-5 h-5 bg-amber-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center">{{ $pendingBookings->count() }}</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $pendingBookings->count() }}</p>
                    <p class="text-sm text-slate-500 mt-1">Réservations en attente</p>
                    <p class="text-xs {{ $pendingBookings->count() > 0 ? 'text-amber-500 font-medium' : 'text-slate-400' }} mt-1">
                        {{ $pendingBookings->count() > 0 ? 'Action requise' : 'Aucune action' }}
                    </p>
                </button>
            </div>

            {{-- Alerte biens à valider --}}
            @if($propertiesToValidate->count() > 0)
            <div class="mb-6 bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-amber-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-amber-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-exclamation text-amber-700 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-amber-900 text-sm">{{ $propertiesToValidate->count() }} bien(s) à valider</p>
                            <p class="text-xs text-amber-700">Ajoutés par des démarcheurs, en attente de votre confirmation</p>
                        </div>
                    </div>
                    <button @click="tab='properties'" class="px-3 py-1.5 bg-amber-500 text-white text-xs font-semibold rounded-lg hover:bg-amber-600 transition-colors">
                        Voir tout
                    </button>
                </div>
                <div class="divide-y divide-amber-100">
                    @foreach($propertiesToValidate->take(3) as $prop)
                    @php $img = $prop->main_image ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=80&h=80&fit=crop'; @endphp
                    <div class="flex items-center gap-4 px-5 py-3">
                        <img src="{{ $img }}" class="w-11 h-11 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 text-sm truncate">{{ $prop->title }}</p>
                            <p class="text-xs text-slate-500">Par {{ $prop->prospector->name ?? 'Démarcheur' }} · {{ $prop->formatted_price }}/mois</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form action="{{ route('dashboard.owner.properties.validate.confirm', $prop) }}" method="POST">
                                @csrf
                                <button class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-check text-[10px]"></i> Valider
                                </button>
                            </form>
                            <form action="{{ route('dashboard.owner.properties.validate.reject', $prop) }}" method="POST">
                                @csrf
                                <button class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-red-200 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="fas fa-times text-[10px]"></i> Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Contenu principal desktop : 2 colonnes --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Colonne gauche (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Mes biens --}}
                    @if($properties->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-800">Mes biens</h2>
                            <button @click="tab='properties'" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">
                                Voir tout <i class="fas fa-arrow-right text-xs ml-1"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($properties->take(4) as $prop)
                            @php
                                $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=400&h=240&fit=crop';
                                $statusStyle = match($prop->status) {
                                    'approuve'    => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Disponible'],
                                    'loue'        => ['pill' => 'bg-blue-100 text-blue-700',       'label' => 'Loué'],
                                    'en_attente'  => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                                    'rejete'      => ['pill' => 'bg-red-100 text-red-700',         'label' => 'Rejeté'],
                                    default       => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $prop->status],
                                };
                            @endphp
                            <a href="{{ route('properties.show', $prop) }}" class="prop-card block">
                                <div class="relative h-36">
                                    <img src="{{ $img }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                    <span class="absolute top-2.5 right-2.5 px-2 py-1 {{ $statusStyle['pill'] }} text-[11px] font-semibold rounded-lg">
                                        {{ $statusStyle['label'] }}
                                    </span>
                                </div>
                                <div class="p-4">
                                    <p class="font-semibold text-slate-800 text-sm truncate">{{ $prop->title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $prop->city }}{{ $prop->neighborhood ? ', '.$prop->neighborhood : '' }}</p>
                                    <div class="flex items-center justify-between mt-3">
                                        <p class="font-bold text-indigo-600">{{ $prop->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                                        <div class="flex items-center gap-2.5 text-[11px] text-slate-400">
                                            <span><i class="fas fa-bed mr-0.5"></i>{{ $prop->bedrooms }}</span>
                                            <span><i class="fas fa-bath mr-0.5"></i>{{ $prop->bathrooms }}</span>
                                            @if($prop->surface)<span><i class="fas fa-ruler-combined mr-0.5"></i>{{ $prop->surface }}m²</span>@endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Visites à venir --}}
                    @if($upcomingVisits->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800">Visites à venir</h2>
                            <button @click="tab='visits'" class="text-sm text-indigo-600 font-medium hover:text-indigo-700">Tout voir</button>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @foreach($upcomingVisits->take(4) as $visit)
                            @php $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=120&h=120&fit=crop'; @endphp
                            <a href="{{ route('visits.show', $visit) }}"
                               class="flex items-center gap-4 px-5 py-3.5 hover:bg-slate-50 transition-colors">
                                <img src="{{ $img }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $visit->property->title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <i class="fas fa-user mr-1 text-slate-300"></i>{{ $visit->tenant->name ?? 'Locataire' }}
                                        &nbsp;·&nbsp;
                                        <i class="fas fa-calendar mr-1 text-slate-300"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                                <span class="px-2.5 py-1 bg-violet-50 text-violet-700 text-[11px] font-semibold rounded-lg flex-shrink-0">{{ $visit->status_name }}</span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Empty state --}}
                    @if($properties->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-building text-indigo-600 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-2">Publiez votre premier bien</h3>
                        <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">Commencez à recevoir des demandes de locataires dès aujourd'hui.</p>
                        <a href="{{ route('properties.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-plus text-xs"></i> Publier un bien
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Colonne droite (1/3) --}}
                <div class="space-y-5">

                    {{-- Contrats actifs --}}
                    @if($activeContracts->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Contrats actifs</h2>
                            <button @click="tab='contracts'" class="text-xs text-indigo-600 font-medium">Tout voir</button>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @foreach($activeContracts->take(4) as $contract)
                            @php $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=80&h=80&fit=crop'; @endphp
                            <a href="{{ route('contracts.show', $contract) }}"
                               class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
                                <img src="{{ $img }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $contract->property->title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $contract->tenant->name ?? '—' }}</p>
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <div class="flex-1 bg-slate-100 rounded-full h-1">
                                            <div class="bg-indigo-500 h-1 rounded-full transition-all"
                                                 style="width:{{ min(100,($contract->months_paid/$contract->duration_months)*100) }}%"></div>
                                        </div>
                                        <span class="text-[10px] text-slate-400 flex-shrink-0">{{ $contract->months_paid }}/{{ $contract->duration_months }}m</span>
                                    </div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-bold text-slate-800 text-sm">{{ number_format($contract->monthly_amount, 0, ',', ' ') }}</p>
                                    <p class="text-[10px] text-slate-400">FCFA</p>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Réservations en attente --}}
                    @if($pendingBookings->count() > 0)
                    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-amber-100 bg-amber-50">
                            <div class="w-8 h-8 bg-amber-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-amber-700 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-amber-900 text-sm">{{ $pendingBookings->count() }} réservation(s) en attente</p>
                                <p class="text-xs text-amber-600">Action requise</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @foreach($pendingBookings->take(3) as $booking)
                            @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=80&fit=crop'; @endphp
                            <div class="px-5 py-3.5">
                                <div class="flex items-start gap-3 mb-3">
                                    <img src="{{ $img }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 mt-0.5">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-slate-800 text-sm truncate">{{ $booking->property->title }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $booking->tenant->name ?? '—' }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $booking->start_date->format('d/m/Y') }} → {{ $booking->end_date->format('d/m/Y') }}</p>
                                        <p class="text-sm font-bold text-indigo-600 mt-1">{{ number_format($booking->monthly_amount, 0, ',', ' ') }} FCFA/mois</p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <form action="{{ route('bookings.accept', $booking) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button class="w-full py-1.5 bg-indigo-600 text-white text-xs font-semibold rounded-lg hover:bg-indigo-700 transition-colors">
                                            <i class="fas fa-check mr-1"></i>Accepter
                                        </button>
                                    </form>
                                    <a href="{{ route('dashboard.owner.bookings.show', $booking) }}"
                                       class="px-3 py-1.5 bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-200 transition-colors flex-shrink-0">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($pendingBookings->count() > 3)
                        <div class="px-5 py-3 border-t border-slate-100">
                            <button @click="tab='bookings'" class="text-xs text-indigo-600 font-medium hover:text-indigo-700">
                                + {{ $pendingBookings->count() - 3 }} autre(s) → Voir tout
                            </button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Locataires actifs --}}
                    @if($activeContracts->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Locataires</h2>
                            <button @click="tab='tenants'" class="text-xs text-indigo-600 font-medium">Voir tout</button>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            @foreach($activeContracts->take(3) as $contract)
                            <div class="flex items-center gap-3">
                                <img src="{{ $contract->tenant->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($contract->tenant->name ?? 'L').'&background=4F46E5&color=fff&size=80' }}"
                                     class="w-9 h-9 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $contract->tenant->name ?? 'Locataire' }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $contract->property->title }}</p>
                                </div>
                                @if($contract->tenant->phone)
                                <a href="tel:{{ $contract->tenant->phone }}"
                                   class="w-8 h-8 bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center hover:bg-indigo-100 hover:text-indigo-600 transition-colors flex-shrink-0">
                                    <i class="fas fa-phone text-xs"></i>
                                </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>{{-- /tab home --}}


        {{-- ═══════════════════════ TAB: MES BIENS ═══════════════════════ --}}
        <div x-show="tab==='properties'" x-cloak>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Mes biens</h1>
                    <p class="text-slate-500 text-sm mt-1">{{ $stats['total_properties'] }} bien(s) publié(s)</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Publier
                </a>
            </div>

            {{-- Biens à valider --}}
            @if($propertiesToValidate->count() > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-amber-100">
                    <p class="text-sm font-semibold text-amber-800"><i class="fas fa-exclamation-triangle mr-2"></i>Biens à valider ({{ $propertiesToValidate->count() }})</p>
                </div>
                <div class="divide-y divide-amber-100">
                    @foreach($propertiesToValidate as $prop)
                    @php $img = $prop->main_image ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=80&fit=crop'; @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <img src="{{ $img }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">{{ $prop->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $prop->city }} · Par {{ $prop->prospector->name ?? '?' }}</p>
                            <p class="text-sm font-bold text-indigo-600 mt-0.5">{{ $prop->formatted_price }}/mois</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <form action="{{ route('dashboard.owner.properties.validate.confirm', $prop) }}" method="POST">
                                @csrf
                                <button class="flex items-center gap-1.5 px-3 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-check text-[10px]"></i> Valider
                                </button>
                            </form>
                            <form action="{{ route('dashboard.owner.properties.validate.reject', $prop) }}" method="POST">
                                @csrf
                                <button class="flex items-center gap-1.5 px-3 py-2 border border-red-200 text-red-600 text-xs font-semibold rounded-lg hover:bg-red-50 transition-colors">
                                    <i class="fas fa-times text-[10px]"></i> Refuser
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Grille des biens --}}
            @if($properties->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($properties as $prop)
                @php
                    $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=400&h=240&fit=crop';
                    $statusStyle = match($prop->status) {
                        'approuve'    => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Disponible'],
                        'loue'        => ['pill' => 'bg-blue-100 text-blue-700',       'label' => 'Loué'],
                        'en_attente'  => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                        'rejete'      => ['pill' => 'bg-red-100 text-red-700',         'label' => 'Rejeté'],
                        default       => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $prop->status],
                    };
                @endphp
                <div class="prop-card">
                    <div class="relative h-40">
                        <img src="{{ $img }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                        <span class="absolute top-3 right-3 px-2.5 py-1 {{ $statusStyle['pill'] }} text-[11px] font-semibold rounded-lg">
                            {{ $statusStyle['label'] }}
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="font-semibold text-slate-800 truncate">{{ $prop->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $prop->city }}{{ $prop->neighborhood ? ', '.$prop->neighborhood : '' }}</p>
                        <div class="flex items-center justify-between mt-3 mb-4">
                            <p class="font-bold text-indigo-600">{{ $prop->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                            <div class="flex items-center gap-2.5 text-[11px] text-slate-400">
                                <span><i class="fas fa-bed mr-0.5"></i>{{ $prop->bedrooms }}</span>
                                <span><i class="fas fa-bath mr-0.5"></i>{{ $prop->bathrooms }}</span>
                                @if($prop->surface)<span><i class="fas fa-ruler-combined mr-0.5"></i>{{ $prop->surface }}m²</span>@endif
                            </div>
                        </div>
                        <div class="flex gap-2 pt-3 border-t border-slate-100">
                            <a href="{{ route('properties.show', $prop) }}"
                               class="flex-1 py-2 text-xs font-semibold text-center bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                            <a href="{{ route('properties.edit', $prop) }}"
                               class="flex-1 py-2 text-xs font-semibold text-center bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                                <i class="fas fa-pen mr-1"></i>Modifier
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

                {{-- Carte ajout --}}
                <a href="{{ route('properties.create') }}"
                   class="border-2 border-dashed border-slate-300 rounded-2xl flex flex-col items-center justify-center gap-3 p-8 min-h-[220px] hover:border-indigo-400 hover:bg-indigo-50 transition-all group">
                    <div class="w-12 h-12 bg-slate-200 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center transition-colors">
                        <i class="fas fa-plus text-slate-500 group-hover:text-indigo-600 transition-colors"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-500 group-hover:text-indigo-600 text-center transition-colors">Publier un nouveau bien</p>
                </a>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-indigo-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-indigo-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucun bien publié</h3>
                <a href="{{ route('properties.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors mt-4">
                    <i class="fas fa-plus text-xs"></i> Publier un bien
                </a>
            </div>
            @endif
        </div>{{-- /tab properties --}}


        {{-- ═══════════════════════ TAB: VISITES ═══════════════════════ --}}
        <div x-show="tab==='visits'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Visites</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $upcomingVisits->count() }} visite(s) à venir</p>
            </div>

            @if($upcomingVisits->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">À venir · {{ $upcomingVisits->count() }}</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($upcomingVisits as $visit)
                    @php $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=120&fit=crop'; @endphp
                    <a href="{{ route('visits.show', $visit) }}"
                       class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                        <img src="{{ $img }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">{{ $visit->property->title }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $visit->tenant->name ?? 'Locataire' }}</p>
                            <p class="text-xs text-slate-400 mt-1"><i class="fas fa-calendar mr-1.5"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <span class="px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-lg">{{ $visit->status_name }}</span>
                            <i class="fas fa-chevron-right text-slate-300 text-xs"></i>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($pastVisits->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Historique</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($pastVisits as $visit)
                    @php
                        $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=80&fit=crop';
                        $vpill = match($visit->status) {
                            'acceptee' => 'bg-emerald-50 text-emerald-700',
                            'refusee'  => 'bg-red-50 text-red-700',
                            'annulee'  => 'bg-slate-100 text-slate-600',
                            default    => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <img src="{{ $img }}" class="w-11 h-11 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate">{{ $visit->property->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $visit->tenant->name ?? '—' }} · {{ $visit->scheduled_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2.5 py-1 {{ $vpill }} text-xs font-semibold rounded-lg">{{ $visit->status_name }}</span>
                            <a href="{{ route('visits.show', $visit) }}"
                               class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-eye text-slate-500 text-xs"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($upcomingVisits->isEmpty() && $pastVisits->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-violet-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-violet-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucune visite</h3>
                <p class="text-slate-500 text-sm">Les visites de vos biens apparaîtront ici.</p>
            </div>
            @endif
        </div>{{-- /tab visits --}}


        {{-- ═══════════════════════ TAB: CONTRATS ═══════════════════════ --}}
        <div x-show="tab==='contracts'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Contrats</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $stats['active_contracts'] }} contrat(s) actif(s)</p>
            </div>

            @if($activeContracts->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-5">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Actifs · {{ $activeContracts->count() }}</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($activeContracts as $contract)
                    @php $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=120&fit=crop'; @endphp
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                        <img src="{{ $img }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">{{ $contract->property->title }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $contract->tenant->name ?? '—' }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-1.5 max-w-[160px]">
                                    <div class="bg-indigo-500 h-1.5 rounded-full"
                                         style="width:{{ min(100,($contract->months_paid/$contract->duration_months)*100) }}%"></div>
                                </div>
                                <span class="text-xs text-slate-400">{{ $contract->months_paid }}/{{ $contract->duration_months }} mois</span>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-indigo-600">{{ number_format($contract->monthly_amount, 0, ',', ' ') }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">FCFA/mois</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($completedContracts->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Terminés</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($completedContracts as $contract)
                    @php $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=80&fit=crop'; @endphp
                    <div class="flex items-center gap-4 px-5 py-3.5">
                        <img src="{{ $img }}" class="w-11 h-11 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate">{{ $contract->property->title }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $contract->tenant->name ?? '—' }} · {{ $contract->start_date->format('M Y') }} – {{ $contract->end_date->format('M Y') }}</p>
                        </div>
                        <a href="{{ route('contracts.show', $contract) }}"
                           class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center transition-colors flex-shrink-0">
                            <i class="fas fa-eye text-slate-500 text-xs"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($activeContracts->isEmpty() && $completedContracts->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-contract text-amber-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucun contrat</h3>
                <p class="text-slate-500 text-sm">Les contrats de vos locataires apparaîtront ici.</p>
            </div>
            @endif
        </div>{{-- /tab contracts --}}


        {{-- ═══════════════════════ TAB: LOCATAIRES ═══════════════════════ --}}
        <div x-show="tab==='tenants'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Locataires actifs</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $activeContracts->count() }} locataire(s) en cours de bail</p>
            </div>

            @if($activeContracts->count() > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($activeContracts as $contract)
                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <div class="flex items-center gap-4 mb-4">
                        <img src="{{ $contract->tenant->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($contract->tenant->name ?? 'L').'&background=4F46E5&color=fff&size=80' }}"
                             class="w-12 h-12 rounded-2xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-slate-800 truncate">{{ $contract->tenant->name ?? 'Locataire' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $contract->tenant->phone ?? 'Pas de téléphone' }}</p>
                            <p class="text-xs font-semibold text-indigo-600 mt-0.5 truncate">{{ $contract->property->title }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-slate-800">{{ number_format($contract->monthly_amount, 0, ',', ' ') }}</p>
                            <p class="text-xs text-slate-400">FCFA/mois</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mb-4">
                        <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                            <div class="bg-indigo-500 h-1.5 rounded-full"
                                 style="width:{{ min(100,($contract->months_paid/$contract->duration_months)*100) }}%"></div>
                        </div>
                        <span class="text-xs text-slate-500 font-medium flex-shrink-0">{{ $contract->months_paid }}/{{ $contract->duration_months }} mois</span>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('contracts.show', $contract) }}"
                           class="flex-1 py-2 text-xs font-semibold text-center bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                            <i class="fas fa-file-contract mr-1"></i>Contrat
                        </a>
                        @if($contract->tenant->phone)
                        <a href="tel:{{ $contract->tenant->phone }}"
                           class="flex-1 py-2 text-xs font-semibold text-center bg-sky-50 text-sky-700 rounded-lg hover:bg-sky-100 transition-colors">
                            <i class="fas fa-phone mr-1"></i>Appeler
                        </a>
                        @endif
                        <a href="{{ route('messages.index') }}"
                           class="flex-1 py-2 text-xs font-semibold text-center bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-envelope mr-1"></i>Message
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-sky-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-users text-sky-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucun locataire actif</h3>
                <p class="text-slate-500 text-sm">Vos locataires apparaîtront ici une fois un contrat signé.</p>
            </div>
            @endif
        </div>{{-- /tab tenants --}}


        {{-- ═══════════════════════ TAB: RÉSERVATIONS ═══════════════════════ --}}
        <div x-show="tab==='bookings'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Réservations</h1>
                <p class="text-slate-500 text-sm mt-1">
                    @if($pendingBookings->count() > 0)
                        <span class="text-amber-600 font-medium">{{ $pendingBookings->count() }} en attente de votre réponse</span>
                    @else
                        Aucune demande en attente
                    @endif
                </p>
            </div>

            {{-- En attente --}}
            @if($pendingBookings->count() > 0)
            <div class="mb-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                    <p class="section-title mb-0">En attente · {{ $pendingBookings->count() }}</p>
                </div>
                <div class="space-y-4">
                    @foreach($pendingBookings as $booking)
                    @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$booking->property->id % count($imgIds)].'?w=120&fit=crop'; @endphp
                    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden" x-data="{ refusing: false }">
                        <div class="p-5">
                            <div class="flex items-start gap-4 mb-4">
                                <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-800 truncate">{{ $booking->property->title }}</p>
                                    <p class="text-sm text-indigo-600 font-semibold mt-1">
                                        <i class="fas fa-user mr-1.5 text-slate-300"></i>{{ $booking->tenant->name ?? '—' }}
                                        @if($booking->tenant->phone)
                                            · <a href="tel:{{ $booking->tenant->phone }}" class="hover:underline text-slate-500 font-normal">{{ $booking->tenant->phone }}</a>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500 mt-1">
                                        <i class="fas fa-calendar mr-1.5 text-slate-300"></i>
                                        {{ $booking->start_date->format('d/m/Y') }} → {{ $booking->end_date->format('d/m/Y') }}
                                        ({{ $booking->duration_months }} mois)
                                    </p>
                                    <p class="font-bold text-indigo-600 mt-2">{{ number_format($booking->monthly_amount, 0, ',', ' ') }} FCFA/mois</p>
                                </div>
                            </div>

                            @if($booking->tenant_message)
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 mb-4">
                                <p class="text-xs text-slate-400 font-semibold mb-1">Message du locataire</p>
                                <p class="text-sm text-slate-600 italic">"{{ $booking->tenant_message }}"</p>
                            </div>
                            @endif

                            <div class="flex gap-2">
                                <form action="{{ route('bookings.accept', $booking) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button class="w-full py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">
                                        <i class="fas fa-check mr-1.5"></i>Accepter
                                    </button>
                                </form>
                                <button @click="refusing=!refusing"
                                        :class="refusing ? 'bg-red-600 text-white' : 'border border-red-200 text-red-600 hover:bg-red-50'"
                                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl transition-colors">
                                    <i class="fas fa-times mr-1.5"></i>Refuser
                                </button>
                                <a href="{{ route('dashboard.owner.bookings.show', $booking) }}"
                                   class="w-11 h-11 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center hover:bg-slate-200 transition-colors flex-shrink-0">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                            </div>

                            <div x-show="refusing" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="mt-3">
                                <form action="{{ route('bookings.reject', $booking) }}" method="POST">
                                    @csrf
                                    <textarea name="rejection_reason" rows="2" required
                                              class="w-full px-3 py-2.5 text-sm border border-red-200 rounded-xl focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400/20 resize-none mb-2"
                                              placeholder="Raison du refus (obligatoire)..."></textarea>
                                    <button class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
                                        <i class="fas fa-ban mr-1.5"></i>Confirmer le refus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Actives --}}
            @if($activeBookings->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Réservations actives · {{ $activeBookings->count() }}</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($activeBookings as $booking)
                    @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=80&fit=crop'; @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <img src="{{ $img }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">{{ $booking->property->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $booking->tenant->name ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">Active</span>
                            <a href="{{ route('dashboard.owner.bookings.show', $booking) }}"
                               class="w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-eye text-slate-500 text-xs"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($pendingBookings->isEmpty() && $activeBookings->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-alt text-slate-400 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucune réservation</h3>
                <p class="text-slate-500 text-sm">Les demandes de location apparaîtront ici.</p>
            </div>
            @endif
        </div>{{-- /tab bookings --}}

    </main>
</div>

{{-- ═══════════════════════ BOTTOM NAV MOBILE ═══════════════════════ --}}
<nav class="bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 lg:hidden">
    <div class="flex items-center justify-around h-16 px-2">
        <button @click="tab='home'"
                :class="tab==='home' ? 'text-indigo-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-home text-xl"></i>
            <span class="text-[10px] font-semibold">Accueil</span>
        </button>

        <button @click="tab='properties'"
                :class="tab==='properties' ? 'text-indigo-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-building text-xl"></i>
            <span class="text-[10px] font-semibold">Biens</span>
            @if($stats['total_properties'] > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-indigo-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $stats['total_properties'] }}</span>
            @endif
        </button>

        <div class="flex-1 flex justify-center">
            <a href="{{ route('properties.create') }}"
               class="w-13 h-13 w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-colors -mt-5">
                <i class="fas fa-plus text-white text-xl"></i>
            </a>
        </div>

        <button @click="tab='visits'"
                :class="tab==='visits' ? 'text-violet-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-check text-xl"></i>
            <span class="text-[10px] font-semibold">Visites</span>
            @if($upcomingVisits->count() > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-violet-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $upcomingVisits->count() }}</span>
            @endif
        </button>

        <button @click="tab='bookings'"
                :class="tab==='bookings' ? 'text-amber-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-alt text-xl"></i>
            <span class="text-[10px] font-semibold">Réservations</span>
            @if($pendingBookings->count() > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $pendingBookings->count() }}</span>
            @endif
        </button>
    </div>
</nav>

<script>
@auth
fetch('{{ route("notifications.count") }}')
    .then(r => r.json())
    .then(data => {
        if (data.count > 0) {
            document.querySelectorAll('[data-notif-dot]').forEach(el => el.classList.remove('hidden'));
        }
    }).catch(() => {});
@endauth
</script>
</body>
</html>
