<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mon espace — E-Loyer</title>
    <link rel="icon" type="image/png" href="{{ asset('img/eloyer-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        body { background: #f0f4f8; }

        /* Sidebar desktop */
        .sidebar { transform: translateX(-100%); transition: transform 0.3s cubic-bezier(.4,0,.2,1); }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0); position: fixed; }
            .main-wrap { margin-left: 268px; }
            .bottom-nav { display: none !important; }
            .burger-btn { display: none !important; }
        }

        /* Hero card mesh */
        .hero-mesh {
            background-image:
                radial-gradient(at 20% 50%, rgba(16,185,129,.18) 0, transparent 50%),
                radial-gradient(at 80% 20%, rgba(6,78,59,.3) 0, transparent 50%),
                radial-gradient(at 60% 80%, rgba(52,211,153,.1) 0, transparent 50%);
        }

        /* Transaction icon pulse */
        @keyframes subtle-pulse { 0%,100%{opacity:1} 50%{opacity:.7} }
        .pulse-pending { animation: subtle-pulse 2s ease-in-out infinite; }

        /* Card tap feedback */
        .tap-card { transition: transform .15s ease, box-shadow .15s ease; }
        .tap-card:active { transform: scale(.97); }

        /* Bottom nav safe area */
        .bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body class="font-sans antialiased" x-data="{ tab: 'home', sidebar: false }">

{{-- ── OVERLAY SIDEBAR MOBILE ─────────────────────────────────────────── --}}
<div x-show="sidebar" @click="sidebar=false" x-cloak
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
</div>

{{-- ── SIDEBAR ─────────────────────────────────────────────────────────── --}}
<aside class="sidebar fixed top-0 left-0 bottom-0 w-[268px] bg-white border-r border-gray-100 z-50 flex flex-col"
       :class="{ 'open': sidebar }">
    <div class="flex items-center justify-between h-16 px-5 border-b border-gray-100">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-8 w-auto">
            <span class="text-lg font-bold text-emerald-600">E-Loyer</span>
        </a>
        <button @click="sidebar=false" class="lg:hidden p-2 text-gray-400 hover:text-gray-600 rounded-lg">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="p-4 border-b border-gray-100">
        <div class="flex items-center gap-3 p-3 bg-emerald-50 rounded-2xl">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-11 h-11 rounded-xl object-cover ring-2 ring-white shadow-sm">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-900 text-sm truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                    <p class="text-xs text-emerald-700 font-medium">Locataire</p>
                </div>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto hide-scrollbar">
        @php
        $navItems = [
            ['tab' => 'home',      'icon' => 'fa-home',          'label' => 'Accueil'],
            ['tab' => 'bookings',  'icon' => 'fa-calendar-alt',  'label' => 'Réservations',  'badge' => $activeBookings->whereIn('status',['en_attente','acceptee'])->count()],
            ['tab' => 'visits',    'icon' => 'fa-calendar-check','label' => 'Mes visites',   'badge' => $upcomingVisits->count()],
            ['tab' => 'contracts', 'icon' => 'fa-file-contract', 'label' => 'Mes contrats',  'badge' => $activeContracts->count()],
            ['tab' => 'payments',  'icon' => 'fa-credit-card',   'label' => 'Paiements'],
            ['tab' => 'favorites', 'icon' => 'fa-heart',         'label' => 'Favoris',       'badge' => $favorites->count()],
        ];
        @endphp
        @foreach($navItems as $item)
        <button @click="tab='{{ $item['tab'] }}'; sidebar=false"
                :class="tab === '{{ $item['tab'] }}' ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800'"
                class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all">
            <i class="fas {{ $item['icon'] }} w-5 text-center"></i>
            <span class="flex-1 text-left">{{ $item['label'] }}</span>
            @if(!empty($item['badge']) && $item['badge'] > 0)
                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">{{ $item['badge'] }}</span>
            @endif
        </button>
        @endforeach

        <div class="pt-3 mt-3 border-t border-gray-100 space-y-0.5">
            <a href="{{ route('properties.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-all">
                <i class="fas fa-search w-5 text-center"></i><span>Rechercher</span>
            </a>
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-all">
                <i class="fas fa-user-cog w-5 text-center"></i><span>Mon profil</span>
            </a>
            <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-500 hover:bg-gray-50 hover:text-gray-800 transition-all">
                <i class="fas fa-envelope w-5 text-center"></i><span>Messages</span>
                @if(auth()->user()->unread_messages_count > 0)
                    <span class="px-2 py-0.5 bg-red-100 text-red-600 text-xs font-bold rounded-full">{{ auth()->user()->unread_messages_count }}</span>
                @endif
            </a>
        </div>
    </nav>

    <div class="p-3 border-t border-gray-100">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-500 hover:bg-red-50 rounded-xl transition-colors font-medium">
                <i class="fas fa-sign-out-alt w-5 text-center"></i><span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

{{-- ── MAIN WRAP ─────────────────────────────────────────────────────────── --}}
<div class="main-wrap min-h-screen pb-24 lg:pb-0">

    {{-- Top Bar --}}
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-100">
        <div class="flex items-center justify-between h-14 px-4 lg:px-6 max-w-4xl mx-auto lg:max-w-none">
            <button @click="sidebar=!sidebar" class="burger-btn w-10 h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 rounded-xl transition-colors">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex items-center gap-2 lg:hidden">
                <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-7 w-auto">
                <span class="font-bold text-emerald-600 text-base">E-Loyer</span>
            </div>
            <div class="hidden lg:block">
                <p class="text-sm font-semibold text-gray-900"
                   x-text="{ home:'Accueil', visits:'Mes visites', contracts:'Mes contrats', payments:'Paiements', favorites:'Favoris' }[tab]"></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('messages.index') }}" class="relative w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">
                    <i class="fas fa-envelope"></i>
                    @if(auth()->user()->unread_messages_count > 0)
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    @endif
                </a>
                <a href="{{ route('notifications.index') }}" class="relative w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-100 rounded-xl transition-colors">
                    <i class="fas fa-bell"></i>
                    <span id="notif-dot" class="hidden absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                </a>
            </div>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
    <div class="max-w-2xl mx-auto px-4 pt-4">
        @if(session('success'))
        <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
             class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl text-sm">
            <i class="fas fa-check-circle text-emerald-500 flex-shrink-0"></i>
            <span class="flex-1">{{ session('success') }}</span>
            <button @click="show=false" class="text-emerald-500"><i class="fas fa-times"></i></button>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{show:true}" x-show="show"
             class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl text-sm">
            <i class="fas fa-exclamation-circle text-red-500 flex-shrink-0"></i>
            <span class="flex-1">{{ session('error') }}</span>
            <button @click="show=false" class="text-red-500"><i class="fas fa-times"></i></button>
        </div>
        @endif
    </div>
    @endif

    <main class="max-w-2xl mx-auto lg:max-w-5xl px-4 lg:px-8 py-4 lg:py-8">
    @php
        $imgIds = ['1502672260266-1c1ef2d93688','1560448204-e02f11c3d0e2','1522708323590-d24dbb6b0267',
                   '1493809842364-78817add7ffb','1560185007-cde436f6a4d0','1484154218962-a197022b5858',
                   '1512917774080-9991f1c4c750','1600596542815-ffad4c1539a9'];
    @endphp

        {{-- ══════════════════════════════════════════════════════════
             TAB: ACCUEIL
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'home'" x-cloak>

            {{-- Hero Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-700 via-emerald-600 to-teal-500 rounded-3xl p-6 mb-5 text-white">
                <div class="absolute -top-8 -right-8 w-40 h-40 bg-white/10 rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-28 h-28 bg-black/10 rounded-full"></div>

                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                                 class="w-11 h-11 rounded-2xl object-cover ring-2 ring-white/40">
                            <div>
                                <p class="text-white/70 text-xs">Bonjour 👋</p>
                                <p class="font-bold text-base">{{ explode(' ', auth()->user()->name)[0] }}</p>
                            </div>
                        </div>
                        @if($activeContracts->count() > 0)
                        <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-xs font-bold rounded-full">
                            <i class="fas fa-circle text-[6px] mr-1 animate-pulse"></i>Locataire actif
                        </span>
                        @endif
                    </div>

                    @if($activeContracts->count() > 0)
                    <div class="mb-5">
                        <p class="text-white/70 text-xs font-medium uppercase tracking-wider mb-1">Loyer mensuel</p>
                        <p class="text-3xl font-extrabold tracking-tight">
                            {{ number_format($totalMonthlyRent ?: $activeContracts->first()?->monthly_amount ?? 0, 0, ',', ' ') }}
                            <span class="text-base font-semibold text-white/80">FCFA</span>
                        </p>
                        @if($nextPayment)
                        @php $daysLeft = now()->diffInDays($nextPayment['due_date'], false); @endphp
                        <p class="text-sm mt-1 font-medium {{ $daysLeft < 0 ? 'text-red-200' : ($daysLeft <= 7 ? 'text-yellow-200' : 'text-white/70') }}">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            @if($daysLeft < 0)
                                Retard de {{ abs($daysLeft) }} jour{{ abs($daysLeft) > 1 ? 's' : '' }}
                            @elseif($daysLeft === 0)
                                Dû aujourd'hui !
                            @elseif($daysLeft <= 7)
                                Dû dans {{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}
                            @else
                                Prochain loyer le {{ $nextPayment['due_date']->format('d/m/Y') }}
                            @endif
                        </p>
                        @else
                        <p class="text-sm mt-1 text-white/60"><i class="fas fa-check-circle mr-1"></i>Aucun loyer dû pour le moment</p>
                        @endif
                    </div>

                    {{-- Bouton toujours visible si contrat actif --}}
                    @if($nextPayment)
                    <a href="{{ route('contracts.show', $nextPayment['contract']) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-700 text-sm font-extrabold rounded-2xl hover:bg-emerald-50 transition-colors shadow-lg">
                        <i class="fas fa-credit-card"></i> Payer mon loyer
                    </a>
                    @else
                    <a href="{{ route('contracts.show', $activeContracts->first()) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 border border-white/40 text-white text-sm font-bold rounded-2xl hover:bg-white/30 transition-colors">
                        <i class="fas fa-file-contract"></i> Voir mon contrat
                    </a>
                    @endif

                    @else
                    <div class="mb-4">
                        <p class="text-white/70 text-xs font-medium uppercase tracking-wider mb-2">Votre parcours</p>
                        <p class="text-xl font-bold">Trouvez votre logement idéal</p>
                        <p class="text-sm text-white/70 mt-1">Parcourez les annonces au Gabon</p>
                    </div>
                    <a href="{{ route('properties.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-emerald-700 text-sm font-extrabold rounded-2xl hover:bg-emerald-50 transition-colors shadow-lg">
                        <i class="fas fa-search"></i> Rechercher un logement
                    </a>
                    @endif
                </div>
            </div>

            {{-- Stats — 2 colonnes verticales --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
                <button @click="tab='visits'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-calendar-check text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $upcomingVisits->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Visites à venir</p>
                    @if($upcomingVisits->count() > 0)
                        <div class="mt-2 flex items-center gap-1 text-xs text-blue-600 font-medium">
                            <i class="fas fa-arrow-right text-[10px]"></i> Voir
                        </div>
                    @endif
                </button>

                <button @click="tab='contracts'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-file-contract text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $activeContracts->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Contrats actifs</p>
                    @if($activeContracts->count() > 0)
                        <div class="mt-2 flex items-center gap-1 text-xs text-emerald-600 font-medium">
                            <i class="fas fa-arrow-right text-[10px]"></i> Voir
                        </div>
                    @endif
                </button>

                <button @click="tab='favorites'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-heart text-rose-500"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $favorites->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Favoris</p>
                    @if($favorites->count() > 0)
                        <div class="mt-2 flex items-center gap-1 text-xs text-rose-500 font-medium">
                            <i class="fas fa-arrow-right text-[10px]"></i> Voir
                        </div>
                    @endif
                </button>

                <button @click="tab='payments'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-credit-card text-amber-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $upcomingPayments->count() }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Paiements à venir</p>
                    @if($upcomingPayments->count() > 0)
                        <div class="mt-2 flex items-center gap-1 text-xs text-amber-600 font-medium">
                            <i class="fas fa-arrow-right text-[10px]"></i> Voir
                        </div>
                    @endif
                </button>
            </div>

            {{-- Actions Rapides (horizontale) --}}
            <div class="mb-5">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-1">Actions rapides</p>
                <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-1 -mx-1 px-1">
                    <a href="{{ route('properties.index') }}"
                       class="tap-card flex-shrink-0 flex items-center gap-3 px-4 py-3 bg-emerald-600 text-white rounded-2xl shadow-lg shadow-emerald-500/30 hover:bg-emerald-700 transition-colors">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-search text-white text-base"></i>
                        </div>
                        <span class="text-sm font-bold whitespace-nowrap">Chercher un logement</span>
                    </a>
                    <button @click="tab='visits'"
                            class="tap-card flex-shrink-0 flex items-center gap-3 px-4 py-3 bg-blue-600 text-white rounded-2xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition-colors">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar-check text-white text-base"></i>
                        </div>
                        <div class="text-left">
                            <span class="text-sm font-bold whitespace-nowrap block">Mes visites</span>
                            @if($upcomingVisits->count() > 0)
                            <span class="text-[10px] text-blue-200">{{ $upcomingVisits->count() }} à venir</span>
                            @endif
                        </div>
                    </button>
                    <button @click="tab='contracts'"
                            class="tap-card flex-shrink-0 flex items-center gap-3 px-4 py-3 bg-indigo-600 text-white rounded-2xl shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 transition-colors">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-contract text-white text-base"></i>
                        </div>
                        <div class="text-left">
                            <span class="text-sm font-bold whitespace-nowrap block">Mes contrats</span>
                            @if($activeContracts->count() > 0)
                            <span class="text-[10px] text-indigo-200">{{ $activeContracts->count() }} actif(s)</span>
                            @endif
                        </div>
                    </button>
                    <button @click="tab='bookings'"
                            class="tap-card flex-shrink-0 flex items-center gap-3 px-4 py-3 bg-amber-500 text-white rounded-2xl shadow-lg shadow-amber-500/30 hover:bg-amber-600 transition-colors">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar-alt text-white text-base"></i>
                        </div>
                        <div class="text-left">
                            <span class="text-sm font-bold whitespace-nowrap block">Réservations</span>
                            @if($activeBookings->whereIn('status',['en_attente','acceptee'])->count() > 0)
                            <span class="text-[10px] text-amber-200">{{ $activeBookings->whereIn('status',['en_attente','acceptee'])->count() }} en attente</span>
                            @endif
                        </div>
                    </button>
                    <button @click="tab='favorites'"
                            class="tap-card flex-shrink-0 flex items-center gap-3 px-4 py-3 bg-rose-500 text-white rounded-2xl shadow-lg shadow-rose-500/30 hover:bg-rose-600 transition-colors">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-heart text-white text-base"></i>
                        </div>
                        <div class="text-left">
                            <span class="text-sm font-bold whitespace-nowrap block">Favoris</span>
                            @if($favorites->count() > 0)
                            <span class="text-[10px] text-rose-200">{{ $favorites->count() }} bien(s)</span>
                            @endif
                        </div>
                    </button>
                </div>
            </div>

            {{-- Alerte paiement urgent --}}
            @if($nextPayment && now()->diffInDays($nextPayment['due_date'], false) <= 7)
            @php $daysLeft = now()->diffInDays($nextPayment['due_date'], false); @endphp
            <div class="mb-5 rounded-3xl overflow-hidden {{ $daysLeft < 0 ? 'bg-red-50 border border-red-200' : 'bg-amber-50 border border-amber-200' }}">
                <div class="p-5 flex items-center gap-4">
                    <div class="w-12 h-12 {{ $daysLeft < 0 ? 'bg-red-100' : 'bg-amber-100' }} rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle {{ $daysLeft < 0 ? 'text-red-600' : 'text-amber-600' }} text-lg"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm">{{ $daysLeft < 0 ? 'Paiement en retard !' : 'Paiement bientôt dû' }}</p>
                        <p class="text-xs text-gray-600 mt-0.5 truncate">{{ $nextPayment['contract']->property->title }}</p>
                        <p class="font-extrabold {{ $daysLeft < 0 ? 'text-red-600' : 'text-amber-600' }} mt-1">{{ number_format($nextPayment['amount'], 0, ',', ' ') }} FCFA</p>
                    </div>
                    <a href="{{ route('contracts.show', $nextPayment['contract']) }}"
                       class="px-4 py-2 {{ $daysLeft < 0 ? 'bg-red-600 hover:bg-red-700' : 'bg-amber-500 hover:bg-amber-600' }} text-white text-xs font-bold rounded-xl transition-colors flex-shrink-0">
                        Payer
                    </a>
                </div>
            </div>
            @endif

            {{-- Contrats actifs (horizontal scroll) --}}
            @if($activeContracts->count() > 0)
            <div class="mb-5">
                <div class="flex items-center justify-between mb-3 px-1">
                    <p class="font-bold text-gray-900 text-sm">Mes logements</p>
                    <button @click="tab='contracts'" class="text-xs text-emerald-600 font-semibold">Voir tout</button>
                </div>
                <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-2 -mx-1 px-1">
                    @foreach($activeContracts as $contract)
                    @php
                        $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=400&h=240&fit=crop';
                        $progress = $contract->duration_months > 0 ? min(100, ($contract->months_paid / $contract->duration_months) * 100) : 0;
                        $remainingMonths = max(0, $contract->duration_months - $contract->months_paid);
                    @endphp
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="tap-card flex-shrink-0 w-60 bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-gray-100">
                        {{-- Image --}}
                        <div class="relative h-36">
                            <img src="{{ $img }}" alt="{{ $contract->property->title }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-1 bg-emerald-500 text-white text-[10px] font-extrabold rounded-lg">
                                    <i class="fas fa-circle text-[6px] mr-0.5 animate-pulse"></i>Actif
                                </span>
                            </div>
                            <div class="absolute bottom-2.5 left-3 right-3">
                                <p class="text-white text-sm font-bold truncate">{{ $contract->property->title }}</p>
                                <p class="text-white/70 text-[11px] mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $contract->property->neighborhood ? $contract->property->neighborhood.', ' : '' }}{{ $contract->property->city }}</p>
                            </div>
                        </div>
                        {{-- Détails --}}
                        <div class="p-3.5">
                            {{-- Loyer en vert --}}
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <p class="text-[10px] text-gray-400 font-medium">Loyer mensuel</p>
                                    <p class="text-lg font-extrabold text-emerald-600">{{ number_format($contract->monthly_amount, 0, ',', ' ') }} <span class="text-xs font-normal text-gray-400">FCFA</span></p>
                                </div>
                                @if($contract->next_payment_date)
                                <div class="text-right">
                                    <p class="text-[10px] text-gray-400">Prochain loyer</p>
                                    <p class="text-xs font-bold {{ $contract->next_payment_date->isPast() ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ $contract->next_payment_date->format('d/m/Y') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                            {{-- Progression --}}
                            <div class="mb-1.5">
                                <div class="flex justify-between text-[10px] text-gray-400 mb-1">
                                    <span>{{ $contract->months_paid }} mois payés</span>
                                    <span>{{ $remainingMonths }} restant{{ $remainingMonths > 1 ? 's' : '' }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full transition-all" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                            {{-- Caution --}}
                            @if($contract->deposit_amount > 0)
                            <div class="flex items-center gap-1.5 mt-2 text-[10px] text-gray-400">
                                <i class="fas fa-shield-alt text-amber-400"></i>
                                <span>Caution : {{ number_format($contract->deposit_amount, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Transactions récentes --}}
            <div class="bg-white rounded-3xl p-5 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-bold text-gray-900 text-sm">Transactions récentes</p>
                    <button @click="tab='payments'" class="text-xs text-emerald-600 font-semibold">Voir tout</button>
                </div>

                @if($recentPayments->count() > 0)
                <div class="space-y-0.5">
                    @foreach($recentPayments->take(6) as $payment)
                    @php
                        $propName = $payment->contract?->property?->title
                            ?? $payment->booking?->property?->title
                            ?? 'Paiement';

                        // Entrée d'argent (remboursement ou caution restituée) = +vert
                        $isCredit = in_array($payment->payment_type, ['remboursement'])
                            || $payment->status === 'rembourse';

                        $typeConfig = [
                            'initial'          => ['icon'=>'fa-home',        'bg'=>'bg-blue-100',   'text'=>'text-blue-600',   'label'=>'1er versement'],
                            'mensuel'          => ['icon'=>'fa-calendar',    'bg'=>'bg-emerald-100','text'=>'text-emerald-600','label'=>'Loyer mensuel'],
                            'caution'          => ['icon'=>'fa-shield-alt',  'bg'=>'bg-indigo-100', 'text'=>'text-indigo-600', 'label'=>'Caution'],
                            'remboursement'    => ['icon'=>'fa-arrow-down',  'bg'=>'bg-emerald-100','text'=>'text-emerald-600','label'=>'Remboursement'],
                            'visite'           => ['icon'=>'fa-eye',         'bg'=>'bg-purple-100', 'text'=>'text-purple-600', 'label'=>'Visite'],
                            'premier_versement'=> ['icon'=>'fa-key',         'bg'=>'bg-blue-100',   'text'=>'text-blue-600',   'label'=>'1er versement'],
                        ];
                        $cfg = $typeConfig[$payment->payment_type] ?? ['icon'=>'fa-money-bill','bg'=>'bg-gray-100','text'=>'text-gray-500','label'=>'Paiement'];

                        $amountColor = $isCredit ? 'text-emerald-600' : ($payment->status === 'echoue' ? 'text-red-500' : 'text-gray-900');
                        $amountPrefix = $isCredit ? '+' : '-';

                        $statusLabel = match($payment->status) {
                            'confirme'   => 'Confirmé',
                            'en_attente' => 'En attente',
                            'traitement' => 'En cours',
                            'echoue'     => 'Échoué',
                            'rembourse'  => 'Remboursé',
                            default      => $payment->status,
                        };
                        $statusColor = match($payment->status) {
                            'confirme','rembourse' => 'text-emerald-600 bg-emerald-50',
                            'en_attente','traitement' => 'text-amber-600 bg-amber-50',
                            'echoue'   => 'text-red-600 bg-red-50',
                            default    => 'text-gray-500 bg-gray-50',
                        };
                    @endphp
                    <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                        <div class="w-11 h-11 {{ $cfg['bg'] }} rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $cfg['icon'] }} {{ $cfg['text'] }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ $cfg['label'] }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $propName }} · {{ $payment->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-base font-extrabold {{ $amountColor }}">
                                {{ $amountPrefix }}{{ number_format($payment->amount, 0, ',', ' ') }}
                                <span class="text-[10px] font-normal {{ $isCredit ? 'text-emerald-500' : 'text-gray-400' }}">F</span>
                            </p>
                            <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded-lg {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-8 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-receipt text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500">Aucune transaction</p>
                </div>
                @endif
            </div>

            {{-- Visites à venir --}}
            @if($upcomingVisits->count() > 0)
            <div class="bg-white rounded-3xl p-5 mb-5">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-bold text-gray-900 text-sm">Visites à venir</p>
                    <button @click="tab='visits'" class="text-xs text-emerald-600 font-semibold">Voir tout</button>
                </div>
                <div class="space-y-2">
                    @foreach($upcomingVisits->take(3) as $visit)
                    @php $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=120&h=120&fit=crop'; @endphp
                    <a href="{{ route('visits.show', $visit) }}"
                       class="tap-card flex items-center gap-4 p-3 hover:bg-gray-50 rounded-2xl transition-colors">
                        <img src="{{ $img }}" alt="{{ $visit->property->title }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $visit->property->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $visit->scheduled_at->format('d/m/Y à H:i') }}</p>
                            <span class="inline-flex items-center mt-1.5 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold">
                                {{ $visit->status_name }}
                            </span>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-xs flex-shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Empty state --}}
            @if($upcomingVisits->isEmpty() && $activeContracts->isEmpty() && $recentPayments->isEmpty())
            <div class="bg-white rounded-3xl p-10 text-center">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-home text-emerald-600 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Démarrez votre recherche</h3>
                <p class="text-gray-500 text-sm mb-6">Trouvez votre logement idéal au Gabon et réservez une visite.</p>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-search"></i> Rechercher un logement
                </a>
            </div>
            @endif

        </div>{{-- /tab home --}}


        {{-- ══════════════════════════════════════════════════════════
             TAB: VISITES
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'visits'" x-cloak>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-extrabold text-gray-900">Mes visites</h2>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search text-xs"></i> Rechercher
                </a>
            </div>

            @if($upcomingVisits->count() > 0 || $pastVisits->count() > 0)
                @if($upcomingVisits->count() > 0)
                <div class="bg-white rounded-3xl p-5 mb-4">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">À venir</p>
                    <div class="space-y-2">
                        @foreach($upcomingVisits as $visit)
                        @php $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=160&h=160&fit=crop'; @endphp
                        <a href="{{ route('visits.show', $visit) }}"
                           class="tap-card flex items-center gap-4 p-3 hover:bg-gray-50 rounded-2xl transition-colors">
                            <img src="{{ $img }}" alt="{{ $visit->property->title }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 truncate">{{ $visit->property->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $visit->property->city }}</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="text-xs font-semibold text-blue-600"><i class="fas fa-calendar mr-1"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold rounded-xl">{{ $visit->status_name }}</span>
                                <i class="fas fa-chevron-right text-gray-300 text-xs"></i>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($pastVisits->count() > 0)
                <div class="bg-white rounded-3xl p-5">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Historique</p>
                    <div class="space-y-1">
                        @foreach($pastVisits as $visit)
                        @php
                            $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=120&h=120&fit=crop';
                            $statusStyle = match($visit->status) {
                                'acceptee'  => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                                'refusee'   => ['bg' => 'bg-red-100',     'text' => 'text-red-700'],
                                'annulee'   => ['bg' => 'bg-gray-100',    'text' => 'text-gray-600'],
                                default     => ['bg' => 'bg-slate-100',   'text' => 'text-slate-600'],
                            };
                        @endphp
                        <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                            <img src="{{ $img }}" alt="{{ $visit->property->title }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $visit->property->title }}</p>
                                <p class="text-xs text-gray-400">{{ $visit->scheduled_at->format('d/m/Y') }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="px-2 py-1 {{ $statusStyle['bg'] }} {{ $statusStyle['text'] }} text-[10px] font-bold rounded-xl">{{ $visit->status_name }}</span>
                                <a href="{{ route('visits.show', $visit) }}" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                                    <i class="fas fa-eye text-gray-600 text-xs"></i>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
            <div class="bg-white rounded-3xl p-10 text-center">
                <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-blue-600 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Aucune visite</h3>
                <p class="text-gray-500 text-sm mb-6">Réservez une visite pour un bien qui vous intéresse.</p>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-2xl font-bold hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search"></i> Rechercher
                </a>
            </div>
            @endif
        </div>{{-- /tab visits --}}


        {{-- ══════════════════════════════════════════════════════════
             TAB: CONTRATS
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'contracts'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Mes contrats</h2>

            @if($activeContracts->count() > 0)
            <div class="bg-white rounded-3xl p-5 mb-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Actifs</p>
                <div class="space-y-2">
                    @foreach($activeContracts as $contract)
                    @php $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=160&h=160&fit=crop'; @endphp
                    <a href="{{ route('contracts.show', $contract) }}"
                       class="tap-card flex items-center gap-4 p-3 hover:bg-gray-50 rounded-2xl transition-colors">
                        <img src="{{ $img }}" alt="{{ $contract->property->title }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-gray-900 truncate">{{ $contract->property->title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $contract->property->city }}</p>
                            <p class="text-sm font-bold text-emerald-600 mt-1">{{ number_format($contract->monthly_amount, 0, ',', ' ') }} FCFA<span class="text-xs font-normal text-gray-400">/mois</span></p>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="flex-1 bg-gray-100 rounded-full h-1">
                                    <div class="bg-emerald-500 h-1 rounded-full" style="width:{{ min(100,($contract->months_paid/$contract->duration_months)*100) }}%"></div>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">{{ $contract->months_paid }}/{{ $contract->duration_months }} mois</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 text-xs flex-shrink-0"></i>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            @if($completedContracts->count() > 0)
            <div class="bg-white rounded-3xl p-5">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Terminés</p>
                <div class="space-y-1">
                    @foreach($completedContracts as $contract)
                    @php $img = $contract->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$contract->property->id % count($imgIds)].'?w=120&h=120&fit=crop'; @endphp
                    <div class="flex items-center gap-3 py-3 border-b border-gray-50 last:border-0">
                        <img src="{{ $img }}" alt="{{ $contract->property->title }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $contract->property->title }}</p>
                            <p class="text-xs text-gray-400">{{ $contract->start_date->format('M Y') }} — {{ $contract->end_date->format('M Y') }}</p>
                        </div>
                        <a href="{{ route('contracts.show', $contract) }}" class="w-8 h-8 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors flex-shrink-0">
                            <i class="fas fa-eye text-gray-600 text-xs"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($activeContracts->isEmpty() && $completedContracts->isEmpty())
            <div class="bg-white rounded-3xl p-10 text-center">
                <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-file-contract text-emerald-600 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Aucun contrat</h3>
                <p class="text-gray-500 text-sm mb-6">Réservez une visite pour commencer.</p>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-search"></i> Rechercher
                </a>
            </div>
            @endif
        </div>{{-- /tab contracts --}}


        {{-- ══════════════════════════════════════════════════════════
             TAB: RÉSERVATIONS
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'bookings'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Mes réservations</h2>

            @if($activeBookings->count() > 0)
            <div class="space-y-3">
                @foreach($activeBookings as $booking)
                @php
                    $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$booking->property->id % count($imgIds)].'?w=160&h=160&fit=crop';
                    $bStyle = match($booking->status) {
                        'en_attente' => ['bg'=>'bg-amber-100',  'text'=>'text-amber-700',  'label'=>'En attente',    'icon'=>'fa-clock'],
                        'acceptee'   => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'label'=>'Acceptée',      'icon'=>'fa-thumbs-up'],
                        'payee'      => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Payée',         'icon'=>'fa-check'],
                        'active'     => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Active',        'icon'=>'fa-home'],
                        default      => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'label'=>$booking->status,'icon'=>'fa-circle'],
                    };
                    $canCancel = in_array($booking->status, ['en_attente', 'acceptee']);
                @endphp
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                    <div class="flex gap-3 mb-3">
                        <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-bold text-gray-900 text-sm truncate flex-1">{{ $booking->property->title }}</p>
                                <span class="px-2 py-0.5 {{ $bStyle['bg'] }} {{ $bStyle['text'] }} text-[10px] font-bold rounded-lg flex-shrink-0 flex items-center gap-1">
                                    <i class="fas {{ $bStyle['icon'] }} text-[9px]"></i>{{ $bStyle['label'] }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $booking->property->city }}</p>
                            <p class="font-bold text-emerald-600 text-sm mt-1">{{ number_format($booking->monthly_amount, 0, ',', ' ') }} FCFA<span class="text-xs text-gray-400 font-normal">/mois</span></p>
                            <p class="text-xs text-gray-400 mt-0.5">Du {{ $booking->start_date->format('d/m/Y') }} au {{ $booking->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('bookings.show', $booking) }}"
                           class="flex-1 py-2 text-xs font-bold text-center bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-colors">
                            <i class="fas fa-eye mr-1"></i>Détails
                        </a>
                        @if($booking->status === 'acceptee')
                        <a href="{{ route('bookings.payment', $booking) }}"
                           class="flex-1 py-2 text-xs font-bold text-center bg-emerald-100 text-emerald-700 rounded-xl hover:bg-emerald-200 transition-colors">
                            <i class="fas fa-credit-card mr-1"></i>Payer
                        </a>
                        @endif
                        @if($canCancel)
                        <button onclick="confirmCancel('{{ route('bookings.cancel', $booking) }}')"
                                class="flex-1 py-2 text-xs font-bold text-center bg-red-100 text-red-600 rounded-xl hover:bg-red-200 transition-colors">
                            <i class="fas fa-times mr-1"></i>Annuler
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-3xl p-10 text-center">
                <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-alt text-gray-400 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Aucune réservation</h3>
                <p class="text-gray-500 text-sm mb-6">Réservez une visite ou un logement pour commencer.</p>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-search"></i> Rechercher un logement
                </a>
            </div>
            @endif
        </div>{{-- /tab bookings --}}


        {{-- ══════════════════════════════════════════════════════════
             TAB: PAIEMENTS
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'payments'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Paiements</h2>

            {{-- Prochains loyers --}}
            @if($upcomingPayments->count() > 0)
            <div class="bg-white rounded-3xl p-5 mb-4">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Prochains loyers</p>
                <div class="space-y-3">
                    @foreach($upcomingPayments as $payment)
                    @php $days = now()->diffInDays($payment['due_date'], false); @endphp
                    <div class="flex items-center justify-between p-4 {{ $days < 0 ? 'bg-red-50 border border-red-100' : ($days <= 7 ? 'bg-amber-50 border border-amber-100' : 'bg-gray-50') }} rounded-2xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 {{ $days < 0 ? 'bg-red-100' : ($days <= 7 ? 'bg-amber-100' : 'bg-emerald-100') }} rounded-xl flex items-center justify-center">
                                <i class="fas fa-calendar-alt {{ $days < 0 ? 'text-red-600' : ($days <= 7 ? 'text-amber-600' : 'text-emerald-600') }} text-sm"></i>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ $payment['contract']->property->title }}</p>
                                <p class="text-xs text-gray-500">Dû le {{ $payment['due_date']->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-extrabold text-gray-900 text-sm">{{ number_format($payment['amount'], 0, ',', ' ') }}</p>
                            <p class="text-[10px] text-gray-400">FCFA</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Historique transactions --}}
            <div class="bg-white rounded-3xl p-5">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Historique</p>

                @if($recentPayments->count() > 0)
                <div class="space-y-1">
                    @foreach($recentPayments as $payment)
                    @php
                        $propName = $payment->contract?->property?->title ?? $payment->booking?->property?->title ?? 'Paiement';
                        $typeConfig = [
                            'initial'       => ['icon'=>'fa-house-user',   'bg'=>'bg-blue-100',   'text'=>'text-blue-600',   'label'=>'Premier versement'],
                            'mensuel'       => ['icon'=>'fa-calendar',     'bg'=>'bg-emerald-100','text'=>'text-emerald-600','label'=>'Loyer mensuel'],
                            'caution'       => ['icon'=>'fa-shield-alt',   'bg'=>'bg-indigo-100', 'text'=>'text-indigo-600', 'label'=>'Caution'],
                            'remboursement' => ['icon'=>'fa-undo',         'bg'=>'bg-amber-100',  'text'=>'text-amber-600',  'label'=>'Remboursement'],
                        ];
                        $cfg = $typeConfig[$payment->payment_type] ?? ['icon'=>'fa-money-bill','bg'=>'bg-gray-100','text'=>'text-gray-600','label'=>'Paiement'];
                        $statusColor = match($payment->status) {
                            'confirme'   => 'text-emerald-600',
                            'en_attente','traitement' => 'text-amber-600',
                            'echoue'     => 'text-red-500',
                            'rembourse'  => 'text-blue-600',
                            default      => 'text-gray-500',
                        };
                    @endphp
                    <div class="flex items-center gap-3 py-3.5 border-b border-gray-50 last:border-0">
                        <div class="w-11 h-11 {{ $cfg['bg'] }} rounded-2xl flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $cfg['icon'] }} {{ $cfg['text'] }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900">{{ $cfg['label'] }}</p>
                            <p class="text-xs text-gray-400 truncate mt-0.5">{{ $propName }} · {{ $payment->created_at->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-extrabold text-sm {{ $payment->payment_type === 'remboursement' ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $payment->payment_type === 'remboursement' ? '+' : '-' }}{{ number_format($payment->amount, 0, ',', ' ') }} F
                            </p>
                            <p class="text-[10px] {{ $statusColor }} font-semibold mt-0.5">
                                {{ match($payment->status) {
                                    'confirme'   => 'Confirmé',
                                    'en_attente' => 'En attente',
                                    'traitement' => 'En cours',
                                    'echoue'     => 'Échoué',
                                    'rembourse'  => 'Remboursé',
                                    default      => $payment->status,
                                } }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="py-10 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-receipt text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500">Aucune transaction pour le moment</p>
                </div>
                @endif
            </div>
        </div>{{-- /tab payments --}}


        {{-- ══════════════════════════════════════════════════════════
             TAB: FAVORIS
        ══════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'favorites'" x-cloak>
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-extrabold text-gray-900">Mes favoris</h2>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-500 text-white rounded-xl text-sm font-semibold hover:bg-rose-600 transition-colors">
                    <i class="fas fa-search text-xs"></i> Parcourir
                </a>
            </div>

            @if($favorites->count() > 0)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                @foreach($favorites as $property)
                @php $img = $property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$property->id % count($imgIds)].'?w=400&h=280&fit=crop'; @endphp
                <a href="{{ route('properties.show', $property) }}"
                   class="tap-card bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-shadow group">
                    <div class="relative h-36 overflow-hidden">
                        <img src="{{ $img }}" alt="{{ $property->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                        <button onclick="event.preventDefault(); toggleFavorite({{ $property->id }}, this)"
                                class="absolute top-2 right-2 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors shadow-sm">
                            <i class="fas fa-heart text-rose-500 text-sm"></i>
                        </button>
                    </div>
                    <div class="p-3">
                        <p class="font-bold text-gray-900 text-sm truncate">{{ $property->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $property->city }}</p>
                        <p class="font-extrabold text-emerald-600 text-sm mt-1.5">{{ $property->formatted_price }}<span class="text-[10px] text-gray-400 font-normal">/mois</span></p>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-3xl p-10 text-center">
                <div class="w-20 h-20 bg-rose-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-heart text-rose-500 text-3xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 text-lg mb-2">Aucun favori</h3>
                <p class="text-gray-500 text-sm mb-6">Ajoutez des propriétés à vos favoris pour les retrouver facilement.</p>
                <a href="{{ route('properties.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-2xl font-bold hover:bg-rose-600 transition-colors">
                    <i class="fas fa-search"></i> Parcourir les annonces
                </a>
            </div>
            @endif
        </div>{{-- /tab favorites --}}

    </main>
</div>

{{-- ── BOTTOM NAV (mobile only) ────────────────────────────────────────── --}}
<nav class="bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-40 lg:hidden">
    <div class="flex items-center justify-around h-16 px-2">

        <button @click="tab='home'"
                :class="tab === 'home' ? 'text-emerald-600' : 'text-gray-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-home text-xl"></i>
            <span class="text-[10px] font-semibold">Accueil</span>
        </button>

        <button @click="tab='visits'"
                :class="tab === 'visits' ? 'text-blue-600' : 'text-gray-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-check text-xl"></i>
            <span class="text-[10px] font-semibold">Visites</span>
            @if($upcomingVisits->count() > 0)
            <span class="absolute top-1 right-4 w-4 h-4 bg-blue-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $upcomingVisits->count() }}</span>
            @endif
        </button>

        {{-- FAB center --}}
        <div class="flex-1 flex justify-center">
            <a href="{{ route('properties.index') }}"
               class="w-14 h-14 bg-emerald-600 rounded-2xl flex items-center justify-center shadow-xl shadow-emerald-500/40 hover:bg-emerald-700 transition-colors -mt-5">
                <i class="fas fa-search text-white text-xl"></i>
            </a>
        </div>

        <button @click="tab='bookings'"
                :class="tab === 'bookings' ? 'text-amber-600' : 'text-gray-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-alt text-xl"></i>
            <span class="text-[10px] font-semibold">Réservations</span>
            @if($activeBookings->whereIn('status',['en_attente','acceptee'])->count() > 0)
            <span class="absolute top-1 right-3 w-4 h-4 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $activeBookings->whereIn('status',['en_attente','acceptee'])->count() }}</span>
            @endif
        </button>

        <button @click="tab='contracts'"
                :class="tab === 'contracts' ? 'text-indigo-600' : 'text-gray-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-file-contract text-xl"></i>
            <span class="text-[10px] font-semibold">Contrats</span>
            @if($activeContracts->count() > 0)
            <span class="absolute top-1 right-3 w-4 h-4 bg-indigo-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $activeContracts->count() }}</span>
            @endif
        </button>

    </div>
</nav>

<script>
function confirmCancel(url) {
    if (!confirm('Confirmer l\'annulation de cette réservation ?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    const csrf = document.createElement('input');
    csrf.type = 'hidden'; csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrf);
    document.body.appendChild(form);
    form.submit();
}

function toggleFavorite(propertyId, button) {
    fetch(`/proprietes/${propertyId}/favori`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        const icon = button.querySelector('i');
        if (data.favorited) { icon.className = 'fas fa-heart text-rose-500 text-sm'; }
        else { icon.className = 'far fa-heart text-gray-400 text-sm'; }
    });
}

// Notifications badge
@auth
fetch('{{ route("notifications.count") }}')
    .then(r => r.json())
    .then(data => {
        if (data.count > 0) {
            const dot = document.getElementById('notif-dot');
            if (dot) { dot.classList.remove('hidden'); }
        }
    }).catch(()=>{});
@endauth
</script>
</body>
</html>
