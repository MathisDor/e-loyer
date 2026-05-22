<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Espace démarcheur — E-Loyer</title>
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

        .sidebar { width: 260px; transform: translateX(-100%); transition: transform .25s ease; }
        .sidebar.open { transform: translateX(0); }
        @media (min-width: 1024px) {
            .sidebar { transform: translateX(0); position: fixed; top: 0; left: 0; bottom: 0; }
            .main-wrap { margin-left: 260px; }
            .bottom-nav, .burger-btn { display: none !important; }
        }

        .nav-link { display: flex; align-items: center; gap: 10px; padding: 9px 14px; border-radius: 10px;
                    font-size: 14px; font-weight: 500; color: #64748b; transition: all .15s ease; cursor: pointer;
                    width: 100%; text-align: left; background: none; border: none; }
        .nav-link:hover { background: #F1F5F9; color: #1e293b; }
        .nav-link.active { background: #FFF7ED; color: #EA580C; font-weight: 600; }
        .nav-link .icon { width: 18px; text-align: center; font-size: 14px; }

        .kpi-card { background: white; border-radius: 14px; padding: 20px 22px; border: 1px solid #E2E8F0;
                    transition: box-shadow .15s, transform .15s; cursor: pointer; }
        .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); }

        .prop-card { background: white; border-radius: 14px; border: 1px solid #E2E8F0; overflow: hidden;
                     transition: box-shadow .15s, transform .15s; }
        .prop-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }

        .section-title { font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase;
                         letter-spacing: .08em; margin-bottom: 14px; }
        .bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0); }
    </style>
</head>
<body x-data="{ tab: 'home', sidebar: false, copiedRef: false }">

{{-- Overlay mobile --}}
<div x-show="sidebar" @click="sidebar=false" x-cloak
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

{{-- ═══════════════════════ SIDEBAR ═══════════════════════ --}}
<aside class="sidebar fixed z-50 bg-white border-r border-slate-200 flex flex-col h-full"
       :class="{ 'open': sidebar }">

    <div class="flex items-center justify-between h-[60px] px-5 border-b border-slate-100 flex-shrink-0">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-7 w-auto">
            <span class="text-[17px] font-bold text-orange-600">E-Loyer</span>
        </a>
        <button @click="sidebar=false" class="lg:hidden w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-600 rounded-lg">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>

    <div class="px-4 py-4 border-b border-slate-100 flex-shrink-0">
        <div class="flex items-center gap-3 mb-3">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-10 h-10 rounded-xl object-cover ring-2 ring-white shadow-sm flex-shrink-0">
            <div class="min-w-0">
                <p class="font-semibold text-slate-800 text-sm truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-orange-500 rounded-full"></span>
                    <span class="text-xs text-slate-500">Démarcheur · {{ auth()->user()->commission_rate }}%</span>
                </div>
            </div>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-amber-500 rounded-xl p-3 text-white text-center">
            <p class="text-[11px] text-white/70 mb-0.5">Gains totaux</p>
            <p class="text-xl font-bold">{{ number_format($stats['total_earnings'], 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span></p>
        </div>
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
        <p class="section-title px-2 mb-3">Navigation</p>
        @php
        $navItems = [
            ['tab' => 'home',        'icon' => 'fa-home',           'label' => 'Tableau de bord'],
            ['tab' => 'visits',      'icon' => 'fa-calendar-check', 'label' => 'Mes visites',      'badge' => $assignedVisits->count()],
            ['tab' => 'properties',  'icon' => 'fa-building',       'label' => 'Biens prospectés', 'badge' => $stats['total_properties']],
            ['tab' => 'commissions', 'icon' => 'fa-coins',          'label' => 'Commissions'],
            ['tab' => 'referral',    'icon' => 'fa-link',           'label' => 'Mon lien de parrainage'],
            ['tab' => 'withdrawal',  'icon' => 'fa-wallet',         'label' => 'Retrait'],
            ['tab' => 'ranking',     'icon' => 'fa-trophy',         'label' => 'Classement'],
        ];
        @endphp
        @foreach($navItems as $item)
        <button @click="tab='{{ $item['tab'] }}'; sidebar=false"
                :class="tab==='{{ $item['tab'] }}' ? 'active' : ''"
                class="nav-link">
            <i class="fas {{ $item['icon'] }} icon"></i>
            <span class="flex-1">{{ $item['label'] }}</span>
            @if(!empty($item['badge']) && $item['badge'] > 0)
                <span class="px-2 py-0.5 bg-orange-100 text-orange-600 text-[11px] font-bold rounded-full">{{ $item['badge'] }}</span>
            @endif
        </button>
        @endforeach

        <div class="pt-4 mt-4 border-t border-slate-100 space-y-0.5">
            <p class="section-title px-2 mb-3">Général</p>
            <a href="{{ route('properties.create') }}"
               class="nav-link font-semibold !text-orange-600 !bg-orange-50 hover:!bg-orange-100">
                <i class="fas fa-plus-circle icon"></i>
                <span>Prospecter un bien</span>
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

{{-- ═══════════════════════ MAIN WRAP ═══════════════════════ --}}
<div class="main-wrap min-h-screen pb-20 lg:pb-0">

    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-slate-200">
        <div class="flex items-center justify-between h-[60px] px-5">
            <div class="flex items-center gap-4">
                <button @click="sidebar=!sidebar" class="burger-btn w-9 h-9 flex items-center justify-center text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-bars text-sm"></i>
                </button>
                <div class="hidden lg:block">
                    <p class="font-semibold text-slate-800 text-sm"
                       x-text="{ home:'Tableau de bord', visits:'Mes visites', properties:'Biens prospectés', commissions:'Commissions', referral:'Lien de parrainage', withdrawal:'Retrait', ranking:'Classement' }[tab]"></p>
                </div>
                <div class="flex items-center gap-2 lg:hidden">
                    <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-6 w-auto">
                    <span class="font-bold text-orange-600 text-sm">E-Loyer</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('properties.create') }}"
                   class="hidden sm:flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-xl text-[13px] font-semibold hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Prospecter
                </a>
                <a href="{{ route('messages.index') }}" class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-envelope text-sm"></i>
                    @if(auth()->user()->unread_messages_count > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
                </a>
                <a href="{{ route('notifications.index') }}" class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-bell text-sm"></i>
                </a>
            </div>
        </div>
    </header>

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

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Bonjour, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
                    <p class="text-slate-500 text-sm mt-1">Voici vos performances de démarcheur</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="hidden lg:flex items-center gap-2 px-5 py-2.5 bg-orange-600 text-white rounded-xl text-sm font-semibold hover:bg-orange-700 transition-colors shadow-sm shadow-orange-100">
                    <i class="fas fa-plus text-xs"></i> Prospecter un bien
                </a>
            </div>

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button @click="tab='properties'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-orange-600 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['total_properties'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Biens prospectés</p>
                    <p class="text-xs mt-1">
                        <span class="text-emerald-600 font-medium">{{ $stats['approved_properties'] }} approuvé(s)</span>
                        @if($stats['pending_properties'] > 0)
                        · <span class="text-amber-500">{{ $stats['pending_properties'] }} en attente</span>
                        @endif
                    </p>
                </button>

                <button @click="tab='visits'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-sky-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-calendar-check text-sky-600 text-sm"></i>
                        </div>
                        @if($assignedVisits->count() > 0)
                        <span class="w-5 h-5 bg-sky-600 text-white text-[11px] font-bold rounded-full flex items-center justify-center">{{ $assignedVisits->count() }}</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['assigned_visits'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Visites assignées</p>
                    <p class="text-xs text-slate-400 mt-1">{{ $stats['assigned_visits'] > 0 ? 'Voir le planning' : 'Aucune en cours' }}</p>
                </button>

                <button @click="tab='commissions'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-coins text-emerald-600 text-sm"></i>
                        </div>
                        @if($stats['pending_commissions'] > 0)
                        <span class="text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full">En attente</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['paid_commissions'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">FCFA perçus</p>
                    @if($stats['pending_commissions'] > 0)
                    <p class="text-xs text-amber-600 font-medium mt-1">+{{ number_format($stats['pending_commissions'], 0, ',', ' ') }} en attente</p>
                    @endif
                </button>

                <div class="kpi-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-walking text-violet-600 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['visit_earnings'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">FCFA via visites</p>
                    <p class="text-xs text-violet-600 font-medium mt-1">Commissions visites</p>
                </div>
            </div>

            {{-- Guide nouveau démarcheur --}}
            @if($stats['total_properties'] === 0 && $assignedVisits->isEmpty())
            <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5 mb-6">
                <p class="font-semibold text-orange-800 mb-4"><i class="fas fa-info-circle mr-2"></i>Comment gagner de l'argent ?</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0">1</div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">Prospectez un bien</p>
                            <p class="text-xs text-slate-500 mt-0.5">Ajoutez un bien disponible à louer</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-orange-500 text-white rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0">2</div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">Guidez les visites</p>
                            <p class="text-xs text-slate-500 mt-0.5">Accompagnez les locataires intéressés</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 bg-emerald-500 text-white rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0">3</div>
                        <div>
                            <p class="font-semibold text-slate-800 text-sm">Percevez vos commissions</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ auth()->user()->commission_rate }}% du loyer à chaque location réussie</p>
                        </div>
                    </div>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="mt-4 w-full py-2.5 bg-orange-600 text-white text-sm font-semibold rounded-xl flex items-center justify-center gap-2 hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Commencer à prospecter
                </a>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Colonne gauche (2/3) --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Prochaines visites --}}
                    @if($assignedVisits->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800">Prochaines visites</h2>
                            <button @click="tab='visits'" class="text-sm text-orange-600 font-medium hover:text-orange-700">Tout voir</button>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @foreach($assignedVisits->take(4) as $visit)
                            @php $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=120&fit=crop'; @endphp
                            <a href="{{ route('visits.show', $visit) }}"
                               class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                                <img src="{{ $img }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $visit->property->title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <i class="fas fa-user mr-1 text-slate-300"></i>{{ $visit->tenant->name ?? 'Locataire' }}
                                        &nbsp;·&nbsp;
                                        <i class="fas fa-calendar mr-1 text-slate-300"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1 flex-shrink-0">
                                    <span class="text-sm font-bold text-emerald-600">+{{ number_format($visit->commission, 0, ',', ' ') }}</span>
                                    <span class="text-[10px] text-slate-400">FCFA</span>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Biens prospectés --}}
                    @if($properties->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-800">Mes biens prospectés</h2>
                            <button @click="tab='properties'" class="text-sm text-orange-600 font-medium hover:text-orange-700">
                                Voir tout <i class="fas fa-arrow-right text-xs ml-1"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($properties->take(4) as $prop)
                            @php
                                $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=400&h=240&fit=crop';
                                $statusStyle = match($prop->status) {
                                    'approuve'   => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Approuvé'],
                                    'loue'       => ['pill' => 'bg-sky-100 text-sky-700',         'label' => 'Loué'],
                                    'en_attente' => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                                    'rejete'     => ['pill' => 'bg-red-100 text-red-700',         'label' => 'Rejeté'],
                                    default      => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $prop->status],
                                };
                            @endphp
                            <a href="{{ route('properties.show', $prop) }}" class="prop-card block">
                                <div class="relative h-32">
                                    <img src="{{ $img }}" alt="{{ $prop->title }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                    <span class="absolute top-2.5 right-2.5 px-2 py-1 {{ $statusStyle['pill'] }} text-[11px] font-semibold rounded-lg">
                                        {{ $statusStyle['label'] }}
                                    </span>
                                </div>
                                <div class="p-3">
                                    <p class="font-semibold text-slate-800 text-sm truncate">{{ $prop->title }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="font-bold text-orange-600 text-sm">{{ $prop->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                                        <p class="text-xs text-slate-400"><i class="fas fa-map-marker-alt mr-1"></i>{{ $prop->city }}</p>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Empty state --}}
                    @if($properties->isEmpty() && $assignedVisits->isEmpty())
                    <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                        <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-building text-orange-600 text-2xl"></i>
                        </div>
                        <h3 class="font-bold text-slate-800 text-lg mb-2">Prospectez votre premier bien</h3>
                        <p class="text-slate-500 text-sm mb-6 max-w-sm mx-auto">Ajoutez un bien et commencez à percevoir des commissions dès aujourd'hui.</p>
                        <a href="{{ route('properties.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-2.5 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition-colors">
                            <i class="fas fa-plus text-xs"></i> Prospecter un bien
                        </a>
                    </div>
                    @endif
                </div>

                {{-- Colonne droite (1/3) --}}
                <div class="space-y-5">

                    {{-- Résumé commissions --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Mes gains</h2>
                        </div>
                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between py-2.5 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-check text-emerald-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-slate-600">Perçus</span>
                                </div>
                                <span class="font-bold text-emerald-600">{{ number_format($stats['paid_commissions'], 0, ',', ' ') }} F</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5 border-b border-slate-50">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-clock text-amber-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-slate-600">En attente</span>
                                </div>
                                <span class="font-bold text-amber-600">{{ number_format($stats['pending_commissions'], 0, ',', ' ') }} F</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-walking text-violet-600 text-xs"></i>
                                    </div>
                                    <span class="text-sm text-slate-600">Via visites</span>
                                </div>
                                <span class="font-bold text-violet-600">{{ number_format($stats['visit_earnings'], 0, ',', ' ') }} F</span>
                            </div>
                        </div>
                        <div class="px-5 pb-4">
                            <button @click="tab='commissions'"
                                    class="w-full py-2 text-sm font-semibold text-orange-600 bg-orange-50 rounded-xl hover:bg-orange-100 transition-colors">
                                Voir l'historique complet
                            </button>
                        </div>
                    </div>

                    {{-- Top biens rentables --}}
                    @if($topProperties->count() > 0 && $topProperties->where('commissions_sum_amount', '>', 0)->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Biens les plus rentables</h2>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            @foreach($topProperties->take(5) as $i => $prop)
                            @if($prop->commissions_sum_amount > 0)
                            @php $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=80&fit=crop'; @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-lg text-[11px] font-bold flex items-center justify-center flex-shrink-0
                                    {{ $i === 0 ? 'bg-amber-400 text-amber-900' : ($i === 1 ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-500') }}">
                                    {{ $i + 1 }}
                                </span>
                                <img src="{{ $img }}" class="w-9 h-9 rounded-xl object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $prop->title }}</p>
                                    <p class="text-xs text-slate-400">{{ $prop->city }}</p>
                                </div>
                                <span class="font-bold text-emerald-600 text-sm flex-shrink-0">{{ number_format($prop->commissions_sum_amount, 0, ',', ' ') }} F</span>
                            </div>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Badge & Classement --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h2 class="font-semibold text-slate-800 text-sm">Mon niveau</h2>
                            <button @click="tab='ranking'" class="text-xs text-orange-600 font-medium">Classement</button>
                        </div>
                        <div class="p-5">
                            @php
                                $badgeIcons = ['bronze'=>'fa-medal','silver'=>'fa-medal','gold'=>'fa-trophy','platinum'=>'fa-crown'];
                                $badgeBg    = ['bronze'=>'bg-amber-700','silver'=>'bg-slate-400','gold'=>'bg-yellow-500','platinum'=>'bg-purple-600'];
                                $bl = $stats['badge_level'];
                            @endphp
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 {{ $badgeBg[$bl] ?? 'bg-amber-700' }} rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <i class="fas {{ $badgeIcons[$bl] ?? 'fa-medal' }} text-white text-2xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800 text-lg">{{ $stats['badge_label'] }}</p>
                                    <p class="text-xs text-slate-500">Rang #{{ $stats['my_rank'] }} sur la plateforme</p>
                                </div>
                            </div>
                            {{-- Barre de progression vers le niveau suivant --}}
                            @php
                                $thresholds = \App\Models\User::BADGE_THRESHOLDS;
                                $levels = array_keys($thresholds);
                                $currentIdx = array_search($bl, $levels);
                                $nextLevel = $levels[($currentIdx + 1)] ?? null;
                                $currentMin = $thresholds[$bl] ?? 0;
                                $nextMin = $nextLevel ? $thresholds[$nextLevel] : null;
                                $progress = $nextMin ? min(100, (($stats['total_earnings'] - $currentMin) / ($nextMin - $currentMin)) * 100) : 100;
                            @endphp
                            @if($nextLevel)
                            <div class="mt-3">
                                <div class="flex items-center justify-between text-xs text-slate-400 mb-1.5">
                                    <span>{{ $stats['badge_label'] }}</span>
                                    <span>{{ \App\Models\User::BADGE_LABELS[$nextLevel] }}</span>
                                </div>
                                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="{{ $badgeBg[$bl] ?? 'bg-amber-700' }} h-full rounded-full transition-all"
                                         style="width: {{ round($progress) }}%"></div>
                                </div>
                                <p class="text-xs text-slate-400 mt-1.5 text-center">
                                    {{ number_format($nextMin - $stats['total_earnings'], 0, ',', ' ') }} FCFA pour atteindre le niveau suivant
                                </p>
                            </div>
                            @else
                            <p class="text-xs text-center text-purple-600 font-semibold mt-2">🏆 Niveau maximum atteint !</p>
                            @endif
                        </div>
                    </div>

                    {{-- Lien de parrainage rapide --}}
                    <div class="bg-orange-50 border border-orange-200 rounded-2xl p-5">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 bg-orange-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-link text-orange-700 text-sm"></i>
                            </div>
                            <p class="font-semibold text-orange-900 text-sm">Mon code de parrainage</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <code class="flex-1 px-3 py-2 bg-white border border-orange-200 rounded-xl text-sm font-bold text-orange-700 truncate">
                                {{ auth()->user()->ref_code ?? 'DM------' }}
                            </code>
                            <button @click="navigator.clipboard.writeText('{{ auth()->user()->ref_code }}'); copiedRef=true; setTimeout(()=>copiedRef=false,2000)"
                                    class="px-3 py-2 bg-orange-600 text-white text-xs font-semibold rounded-xl hover:bg-orange-700 transition-colors flex-shrink-0">
                                <span x-show="!copiedRef"><i class="fas fa-copy mr-1"></i>Copier</span>
                                <span x-show="copiedRef" x-cloak><i class="fas fa-check mr-1"></i>Copié !</span>
                            </button>
                        </div>
                        <button @click="tab='referral'" class="mt-3 w-full text-xs text-orange-600 font-medium text-center hover:text-orange-700">
                            Voir tous mes liens de parrainage →
                        </button>
                    </div>
                </div>
            </div>
        </div>{{-- /tab home --}}


        {{-- ═══════════════════════ TAB: VISITES ═══════════════════════ --}}
        <div x-show="tab==='visits'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Mes visites</h1>
                <p class="text-slate-500 text-sm mt-1">{{ $assignedVisits->count() }} visite(s) assignée(s)</p>
            </div>

            @if($assignedVisits->count() > 0)
            <div class="space-y-4 mb-6">
                <p class="section-title">À effectuer · {{ $assignedVisits->count() }}</p>
                @foreach($assignedVisits as $visit)
                @php
                    $img = $visit->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$visit->property->id % count($imgIds)].'?w=160&fit=crop';
                    $canStart    = $visit->status === 'reservee' && $visit->is_paid;
                    $canComplete = $visit->status === 'en_cours';
                    $spill = match($visit->status) {
                        'reservee' => 'bg-amber-50 text-amber-700',
                        'en_cours' => 'bg-sky-50 text-sky-700',
                        default    => 'bg-slate-100 text-slate-600',
                    };
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="flex gap-4 p-5">
                        <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <p class="font-semibold text-slate-800 truncate flex-1">{{ $visit->property->title }}</p>
                                <span class="px-2.5 py-1 {{ $spill }} text-xs font-semibold rounded-lg flex-shrink-0">{{ $visit->status_name }}</span>
                            </div>
                            <p class="text-xs text-slate-500"><i class="fas fa-map-marker-alt mr-1.5 text-slate-300"></i>{{ $visit->property->city }}</p>
                            <p class="text-xs text-sky-600 font-medium mt-0.5"><i class="fas fa-user mr-1.5 text-slate-300"></i>{{ $visit->tenant->name ?? '—' }}</p>
                            <p class="text-xs text-slate-500 mt-0.5"><i class="fas fa-calendar mr-1.5 text-slate-300"></i>{{ $visit->scheduled_at->format('d/m/Y à H:i') }}</p>
                            <p class="text-sm font-bold text-emerald-600 mt-1.5">Commission : +{{ number_format($visit->commission, 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>

                    @if(!$visit->is_paid)
                    <div class="px-5 pb-4">
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5">
                            <i class="fas fa-clock mr-1.5"></i>En attente du paiement du locataire
                        </p>
                    </div>
                    @endif

                    <div class="flex gap-2 px-5 pb-5" x-data="{ confirming: false }">
                        @if($canStart)
                        <form action="{{ route('visits.start', $visit) }}" method="POST" class="flex-1">
                            @csrf
                            <button class="w-full py-2.5 bg-sky-600 text-white text-sm font-semibold rounded-xl hover:bg-sky-700 transition-colors">
                                <i class="fas fa-walking mr-1.5"></i>Démarrer la visite
                            </button>
                        </form>
                        @endif

                        @if($canComplete)
                        <div class="flex-1" x-data="{ open: false }">
                            <button @click="open=!open"
                                    class="w-full py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-flag-checkered mr-1.5"></i>Terminer
                            </button>
                            <div x-show="open" x-cloak class="mt-3 p-4 bg-slate-50 border border-slate-200 rounded-xl"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0">
                                <p class="text-xs font-semibold text-slate-700 mb-3">Résultat de la visite :</p>
                                <form action="{{ route('visits.validate', $visit) }}" method="POST" class="space-y-2">
                                    @csrf
                                    <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-emerald-400 transition-colors">
                                        <input type="radio" name="visit_status" value="reussie" required class="text-emerald-600">
                                        <span class="text-sm font-medium text-emerald-700"><i class="fas fa-check-circle mr-1"></i>Visite réussie</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 p-2.5 bg-white rounded-xl border border-slate-200 cursor-pointer hover:border-red-400 transition-colors">
                                        <input type="radio" name="visit_status" value="non_effectuee" required class="text-red-600">
                                        <span class="text-sm font-medium text-red-600"><i class="fas fa-times-circle mr-1"></i>Non effectuée</span>
                                    </label>
                                    <textarea name="visit_status_notes" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-slate-200 rounded-xl resize-none focus:outline-none focus:border-orange-400"
                                              placeholder="Notes (optionnel)..."></textarea>
                                    <button class="w-full py-2 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-900 transition-colors">
                                        Confirmer
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif

                        @if($visit->tenant)
                        <div class="flex gap-1.5 flex-shrink-0">
                            @if($visit->tenant->phone)
                            <a href="tel:{{ $visit->tenant->phone }}"
                               class="w-10 h-10 bg-sky-100 text-sky-700 rounded-xl flex items-center justify-center hover:bg-sky-200 transition-colors">
                                <i class="fas fa-phone text-sm"></i>
                            </a>
                            @endif
                            @if($visit->tenant->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $visit->tenant->whatsapp) }}" target="_blank"
                               class="w-10 h-10 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center hover:bg-emerald-200 transition-colors">
                                <i class="fab fa-whatsapp text-sm"></i>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
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
                            'acceptee'      => 'bg-emerald-50 text-emerald-700',
                            'refusee'       => 'bg-red-50 text-red-700',
                            'non_effectuee' => 'bg-slate-100 text-slate-600',
                            default         => 'bg-slate-100 text-slate-500',
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
                            @if($visit->is_paid)
                            <span class="text-sm font-bold text-emerald-600">+{{ number_format($visit->commission, 0, ',', ' ') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($assignedVisits->isEmpty() && $pastVisits->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-sky-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-times text-sky-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucune visite</h3>
                <p class="text-slate-500 text-sm">Les visites qui vous seront assignées apparaîtront ici.</p>
            </div>
            @endif
        </div>{{-- /tab visits --}}


        {{-- ═══════════════════════ TAB: BIENS PROSPECTÉS ═══════════════════════ --}}
        <div x-show="tab==='properties'" x-cloak>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Biens prospectés</h1>
                    <p class="text-slate-500 text-sm mt-1">{{ $stats['total_properties'] }} bien(s) · {{ $stats['approved_properties'] }} approuvé(s)</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-orange-600 text-white rounded-xl text-sm font-semibold hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Ajouter
                </a>
            </div>

            @if($properties->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($properties as $prop)
                @php
                    $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=400&h=240&fit=crop';
                    $statusStyle = match($prop->status) {
                        'approuve'   => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Approuvé'],
                        'loue'       => ['pill' => 'bg-sky-100 text-sky-700',         'label' => 'Loué'],
                        'en_attente' => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                        'rejete'     => ['pill' => 'bg-red-100 text-red-700',         'label' => 'Rejeté'],
                        default      => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $prop->status],
                    };
                    $validStyle = $prop->prospector_validated
                        ? ['icon' => 'fa-check-circle', 'color' => 'text-emerald-600', 'label' => 'Validé par propriétaire']
                        : ['icon' => 'fa-clock',        'color' => 'text-amber-500',   'label' => 'En attente propriétaire'];
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
                        <div class="flex items-center justify-between mt-2.5 mb-1">
                            <p class="font-bold text-orange-600">{{ $prop->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                            <p class="text-xs {{ $validStyle['color'] }} font-medium">
                                <i class="fas {{ $validStyle['icon'] }} mr-1"></i>{{ $validStyle['label'] }}
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 mt-3">
                            <a href="{{ route('properties.show', $prop) }}"
                               class="flex items-center justify-center gap-1.5 py-2 text-xs font-semibold bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fas fa-eye"></i> Voir le bien
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach

                <a href="{{ route('properties.create') }}"
                   class="border-2 border-dashed border-slate-300 rounded-2xl flex flex-col items-center justify-center gap-3 p-8 min-h-[220px] hover:border-orange-400 hover:bg-orange-50 transition-all group">
                    <div class="w-12 h-12 bg-slate-200 group-hover:bg-orange-200 rounded-xl flex items-center justify-center transition-colors">
                        <i class="fas fa-plus text-slate-500 group-hover:text-orange-600 transition-colors"></i>
                    </div>
                    <p class="text-sm font-semibold text-slate-500 group-hover:text-orange-600 text-center transition-colors">Prospecter un bien</p>
                </a>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-building text-orange-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucun bien prospecté</h3>
                <p class="text-slate-500 text-sm mb-6">Ajoutez un premier bien pour commencer à percevoir des commissions.</p>
                <a href="{{ route('properties.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Prospecter un bien
                </a>
            </div>
            @endif
        </div>{{-- /tab properties --}}


        {{-- ═══════════════════════ TAB: COMMISSIONS ═══════════════════════ --}}
        <div x-show="tab==='commissions'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Mes commissions</h1>
                <p class="text-slate-500 text-sm mt-1">Total perçu : {{ number_format($stats['total_earnings'], 0, ',', ' ') }} FCFA</p>
            </div>

            {{-- Résumé 3 colonnes --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-check text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['paid_commissions'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">FCFA perçus</p>
                </div>
                <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5 text-center">
                    <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-clock text-amber-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['pending_commissions'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-amber-600 mt-1">FCFA en attente</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                    <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-walking text-violet-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-violet-600">{{ number_format($stats['visit_earnings'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">FCFA visites</p>
                </div>
            </div>

            {{-- Historique --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Historique des commissions</p>
                </div>
                @if($commissions->count() > 0)
                <div class="divide-y divide-slate-50">
                    @foreach($commissions as $commission)
                    @php
                        $cpill = match($commission->status) {
                            'payee'      => ['pill' => 'bg-emerald-50 text-emerald-700', 'icon' => 'fa-check-circle', 'label' => 'Perçue'],
                            'validee'    => ['pill' => 'bg-sky-50 text-sky-700',         'icon' => 'fa-thumbs-up',   'label' => 'Validée'],
                            'en_attente' => ['pill' => 'bg-amber-50 text-amber-700',     'icon' => 'fa-clock',       'label' => 'En attente'],
                            default      => ['pill' => 'bg-slate-100 text-slate-600',    'icon' => 'fa-circle',      'label' => $commission->status],
                        };
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="w-10 h-10 {{ $cpill['pill'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $cpill['icon'] }} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate">{{ $commission->property->title ?? $commission->booking->property->title ?? 'Bien' }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $commission->created_at->format('d/m/Y') }} · {{ $commission->commission_rate }}%</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-slate-800">+{{ number_format($commission->commission_amount ?? $commission->amount ?? 0, 0, ',', ' ') }} F</p>
                            <span class="text-xs {{ $cpill['pill'] }} px-2 py-0.5 rounded-full font-semibold">{{ $cpill['label'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-coins text-slate-400 text-xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm">Aucune commission pour le moment</p>
                    <p class="text-xs text-slate-400 mt-1">Prospectez des biens pour commencer</p>
                </div>
                @endif
            </div>
        </div>{{-- /tab commissions --}}

        {{-- ═══════════════════════ TAB: PARRAINAGE ═══════════════════════ --}}
        <div x-show="tab==='referral'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Mon lien de parrainage</h1>
                <p class="text-slate-500 text-sm mt-1">Partagez ce lien pour ramener des locataires et gagner des commissions</p>
            </div>

            {{-- Code et lien --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="section-title">Mon code unique</p>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex-1 px-4 py-4 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <p class="text-3xl font-bold text-orange-600 tracking-widest">{{ auth()->user()->ref_code ?? '—' }}</p>
                        </div>
                        <button @click="navigator.clipboard.writeText('{{ auth()->user()->ref_code }}'); copiedRef=true; setTimeout(()=>copiedRef=false,2000)"
                                class="w-12 h-12 bg-orange-600 text-white rounded-xl flex items-center justify-center hover:bg-orange-700 transition-colors flex-shrink-0">
                            <i x-show="!copiedRef" class="fas fa-copy"></i>
                            <i x-show="copiedRef" x-cloak class="fas fa-check"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 text-center">Partagez ce code à vos clients. Ils l'utilisent via le lien ci-contre.</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <p class="section-title">Lien général</p>
                    @php $refLink = route('properties.index') . '?ref=' . auth()->user()->ref_code; @endphp
                    <div class="flex items-center gap-2 mb-4">
                        <input type="text" readonly value="{{ $refLink }}"
                               class="flex-1 px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 font-mono">
                        <button @click="navigator.clipboard.writeText('{{ $refLink }}'); copiedRef=true; setTimeout(()=>copiedRef=false,2000)"
                                class="px-3 py-2.5 bg-orange-600 text-white text-xs font-semibold rounded-xl hover:bg-orange-700 transition-colors flex-shrink-0">
                            <span x-show="!copiedRef">Copier</span>
                            <span x-show="copiedRef" x-cloak>✓ Copié</span>
                        </button>
                    </div>
                    {{-- Partage rapide --}}
                    <div class="flex gap-2">
                        <a href="https://wa.me/?text={{ urlencode('Trouvez votre logement sur E-Loyer ! ' . $refLink) }}" target="_blank"
                           class="flex-1 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl text-center hover:bg-emerald-700 transition-colors">
                            <i class="fab fa-whatsapp mr-1"></i>WhatsApp
                        </a>
                        <a href="sms:?body={{ urlencode('Trouvez votre logement sur E-Loyer : ' . $refLink) }}"
                           class="flex-1 py-2 bg-sky-600 text-white text-xs font-semibold rounded-xl text-center hover:bg-sky-700 transition-colors">
                            <i class="fas fa-sms mr-1"></i>SMS
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($refLink) }}" target="_blank"
                           class="flex-1 py-2 bg-blue-700 text-white text-xs font-semibold rounded-xl text-center hover:bg-blue-800 transition-colors">
                            <i class="fab fa-facebook mr-1"></i>Facebook
                        </a>
                    </div>
                </div>
            </div>

            {{-- Liens par bien --}}
            @if($properties->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Liens par bien prospecté</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($properties->take(10) as $prop)
                    @php
                        $propLink = route('properties.show', $prop->id) . '?ref=' . auth()->user()->ref_code;
                        $img = $prop->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$prop->id % count($imgIds)].'?w=80&fit=crop';
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <img src="{{ $img }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm truncate">{{ $prop->title }}</p>
                            <p class="text-xs text-slate-400 font-mono truncate mt-0.5">…?ref={{ auth()->user()->ref_code }}</p>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button onclick="navigator.clipboard.writeText('{{ $propLink }}')"
                                    class="px-3 py-1.5 bg-orange-50 text-orange-600 text-xs font-semibold rounded-lg hover:bg-orange-100 transition-colors">
                                <i class="fas fa-copy mr-1"></i>Copier
                            </button>
                            <a href="https://wa.me/?text={{ urlencode($prop->title . ' - ' . $propLink) }}" target="_blank"
                               class="w-8 h-8 bg-emerald-100 text-emerald-700 rounded-lg flex items-center justify-center hover:bg-emerald-200 transition-colors">
                                <i class="fab fa-whatsapp text-sm"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Statistiques parrainage --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                    <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user-plus text-orange-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['clients_brought'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Clients apportés</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-handshake text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['locations_concluded'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Locations conclues</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-5 text-center">
                    <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-percentage text-violet-600"></i>
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ auth()->user()->commission_rate }}%</p>
                    <p class="text-sm text-slate-500 mt-1">Taux de commission</p>
                </div>
            </div>
        </div>{{-- /tab referral --}}


        {{-- ═══════════════════════ TAB: RETRAIT ═══════════════════════ --}}
        <div x-show="tab==='withdrawal'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Retrait</h1>
                <p class="text-slate-500 text-sm mt-1">Solde disponible : <span class="font-bold text-emerald-600">{{ number_format($availableBalance, 0, ',', ' ') }} FCFA</span></p>
            </div>

            @if($pendingWithdrawal)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                <i class="fas fa-clock text-amber-500 flex-shrink-0"></i>
                <p class="text-amber-800 text-sm flex-1">
                    Une demande de <strong>{{ number_format($pendingWithdrawal->amount, 0, ',', ' ') }} FCFA</strong> est en cours de traitement.
                </p>
            </div>
            @endif

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Formulaire --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Nouvelle demande</h3>

                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 mb-5">
                        <p class="text-xs text-emerald-600 font-medium mb-0.5">Solde disponible</p>
                        <p class="text-3xl font-bold text-emerald-700">{{ number_format($availableBalance, 0, ',', ' ') }} <span class="text-lg font-normal">FCFA</span></p>
                    </div>

                    @if($availableBalance >= 5000 && !$pendingWithdrawal)
                    <form action="{{ route('dashboard.demarcheur.withdrawals.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Montant (min. 5 000 FCFA)</label>
                            <div class="relative">
                                <input type="number" name="amount" min="5000" max="{{ floor($availableBalance) }}"
                                       value="{{ old('amount') }}"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-orange-400 text-lg font-bold"
                                       placeholder="0" required>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">FCFA</span>
                            </div>
                            @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Moyen de paiement</label>
                            <select name="payment_method" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 bg-white text-sm" required>
                                @foreach(\App\Models\WithdrawalRequest::METHODS as $key => $label)
                                <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Numéro de téléphone</label>
                            <input type="tel" name="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm"
                                   placeholder="+241 XX XX XX XX">
                            @error('phone_number')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nom du titulaire</label>
                            <input type="text" name="account_name" value="{{ old('account_name', auth()->user()->name) }}"
                                   class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-400 text-sm"
                                   placeholder="Nom complet" required>
                            @error('account_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>

                        <button type="submit" class="w-full py-3 bg-orange-600 text-white font-semibold rounded-xl hover:bg-orange-700 transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Soumettre la demande
                        </button>
                    </form>
                    @elseif($pendingWithdrawal)
                    <p class="text-slate-400 text-sm text-center py-4">En attente du traitement de votre demande en cours.</p>
                    @else
                    <div class="text-center py-6">
                        <p class="text-slate-400 text-sm">Solde insuffisant (minimum 5 000 FCFA).</p>
                        <p class="text-xs text-slate-400 mt-1">Continuez à prospecter pour augmenter vos gains !</p>
                    </div>
                    @endif
                </div>

                {{-- Historique des retraits --}}
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <p class="section-title mb-0">Historique des retraits</p>
                    </div>
                    @if($withdrawalRequests->count() > 0)
                    <div class="divide-y divide-slate-50">
                        @foreach($withdrawalRequests as $wr)
                        @php
                            $wrpill = match($wr->status) {
                                'en_attente' => 'bg-amber-50 text-amber-700',
                                'approuve'   => 'bg-sky-50 text-sky-700',
                                'paye'       => 'bg-emerald-50 text-emerald-700',
                                'rejete'     => 'bg-red-50 text-red-700',
                                default      => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="w-10 h-10 {{ $wrpill }} rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-arrow-up text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-slate-800 text-sm">{{ $wr->method_name }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $wr->created_at->format('d/m/Y') }}</p>
                                @if($wr->isRejected() && $wr->rejection_reason)
                                <p class="text-xs text-red-500 mt-0.5">{{ $wr->rejection_reason }}</p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-slate-800">{{ $wr->formatted_amount }}</p>
                                <span class="text-xs {{ $wrpill }} px-2 py-0.5 rounded-full font-medium">{{ $wr->status_name }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-10 text-center">
                        <p class="text-slate-400 text-sm">Aucun retrait effectué</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>{{-- /tab withdrawal --}}


        {{-- ═══════════════════════ TAB: CLASSEMENT ═══════════════════════ --}}
        <div x-show="tab==='ranking'" x-cloak>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Classement</h1>
                    <p class="text-slate-500 text-sm mt-1">Votre rang actuel : <span class="font-bold text-orange-600">#{{ $myRank }}</span></p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2 bg-orange-50 border border-orange-200 rounded-xl">
                    @php $bl = $stats['badge_level']; $badgeBg = ['bronze'=>'text-amber-700','silver'=>'text-slate-500','gold'=>'text-yellow-500','platinum'=>'text-purple-600']; @endphp
                    <i class="fas fa-trophy {{ $badgeBg[$bl] ?? 'text-amber-700' }}"></i>
                    <span class="font-semibold text-slate-800 text-sm">{{ $stats['badge_label'] }}</span>
                </div>
            </div>

            {{-- Podium Top 3 --}}
            @if($topProspectors->count() >= 3)
            <div class="grid grid-cols-3 gap-4 mb-8">
                {{-- 2e place --}}
                <div class="flex flex-col items-center pt-6">
                    <img src="{{ $topProspectors[1]->avatar_url }}" class="w-14 h-14 rounded-2xl object-cover mb-2 ring-4 ring-slate-300">
                    <p class="font-semibold text-slate-800 text-sm text-center truncate w-full px-2">{{ explode(' ', $topProspectors[1]->name)[0] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ number_format($topProspectors[1]->total_earnings, 0, ',', ' ') }} F</p>
                    <div class="mt-2 w-full bg-slate-300 rounded-t-xl h-16 flex items-center justify-center">
                        <span class="text-2xl font-bold text-slate-600">2</span>
                    </div>
                </div>
                {{-- 1re place --}}
                <div class="flex flex-col items-center">
                    <div class="w-6 h-6 bg-yellow-400 rounded-full flex items-center justify-center mb-1">
                        <i class="fas fa-crown text-yellow-900 text-xs"></i>
                    </div>
                    <img src="{{ $topProspectors[0]->avatar_url }}" class="w-16 h-16 rounded-2xl object-cover mb-2 ring-4 ring-yellow-400">
                    <p class="font-bold text-slate-800 text-sm text-center truncate w-full px-2">{{ explode(' ', $topProspectors[0]->name)[0] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ number_format($topProspectors[0]->total_earnings, 0, ',', ' ') }} F</p>
                    <div class="mt-2 w-full bg-yellow-400 rounded-t-xl h-24 flex items-center justify-center">
                        <span class="text-3xl font-bold text-yellow-900">1</span>
                    </div>
                </div>
                {{-- 3e place --}}
                <div class="flex flex-col items-center pt-10">
                    <img src="{{ $topProspectors[2]->avatar_url }}" class="w-12 h-12 rounded-2xl object-cover mb-2 ring-4 ring-amber-600">
                    <p class="font-semibold text-slate-800 text-sm text-center truncate w-full px-2">{{ explode(' ', $topProspectors[2]->name)[0] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ number_format($topProspectors[2]->total_earnings, 0, ',', ' ') }} F</p>
                    <div class="mt-2 w-full bg-amber-600 rounded-t-xl h-10 flex items-center justify-center">
                        <span class="text-xl font-bold text-amber-100">3</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Tableau complet --}}
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Top 20 démarcheurs</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($topProspectors as $rank => $prospector)
                    @php
                        $isMe = $prospector->id === auth()->id();
                        $bl2 = $prospector->badge_level ?? 'bronze';
                        $badgeIcon = ['bronze'=>'fa-medal','silver'=>'fa-medal','gold'=>'fa-trophy','platinum'=>'fa-crown'];
                        $badgeColor = ['bronze'=>'text-amber-700','silver'=>'text-slate-400','gold'=>'text-yellow-500','platinum'=>'text-purple-600'];
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4 {{ $isMe ? 'bg-orange-50 border-l-4 border-orange-500' : '' }}">
                        <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0
                            {{ $rank === 0 ? 'bg-yellow-400 text-yellow-900' : ($rank === 1 ? 'bg-slate-200 text-slate-700' : ($rank === 2 ? 'bg-amber-200 text-amber-800' : 'bg-slate-100 text-slate-500')) }}">
                            {{ $rank + 1 }}
                        </span>
                        <img src="{{ $prospector->avatar_url }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="font-medium text-slate-800 text-sm truncate">{{ $prospector->name }}</p>
                                @if($isMe)<span class="text-[10px] bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded-full font-semibold">Vous</span>@endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $prospector->locations_concluded ?? 0 }} location(s) · {{ $prospector->clients_brought ?? 0 }} client(s)
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-slate-800 text-sm">{{ number_format($prospector->total_earnings, 0, ',', ' ') }}</p>
                            <p class="text-[10px] text-slate-400">FCFA</p>
                        </div>
                        <i class="fas {{ $badgeIcon[$bl2] ?? 'fa-medal' }} {{ $badgeColor[$bl2] ?? 'text-amber-700' }} flex-shrink-0"></i>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>{{-- /tab ranking --}}

    </main>
</div>

{{-- ═══════════════════════ BOTTOM NAV MOBILE ═══════════════════════ --}}
<nav class="bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 lg:hidden">
    <div class="flex items-center justify-around h-16 px-2">
        <button @click="tab='home'" :class="tab==='home' ? 'text-orange-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-home text-xl"></i>
            <span class="text-[10px] font-semibold">Accueil</span>
        </button>

        <button @click="tab='visits'" :class="tab==='visits' ? 'text-sky-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-check text-xl"></i>
            <span class="text-[10px] font-semibold">Visites</span>
            @if($assignedVisits->count() > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-sky-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $assignedVisits->count() }}</span>
            @endif
        </button>

        <div class="flex-1 flex justify-center">
            <a href="{{ route('properties.create') }}"
               class="w-14 h-14 bg-orange-600 rounded-2xl flex items-center justify-center shadow-lg shadow-orange-200 hover:bg-orange-700 transition-colors -mt-5">
                <i class="fas fa-plus text-white text-xl"></i>
            </a>
        </div>

        <button @click="tab='properties'" :class="tab==='properties' ? 'text-orange-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-building text-xl"></i>
            <span class="text-[10px] font-semibold">Biens</span>
            @if($stats['total_properties'] > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-orange-600 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $stats['total_properties'] }}</span>
            @endif
        </button>

        <button @click="tab='commissions'" :class="tab==='commissions' ? 'text-emerald-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-coins text-xl"></i>
            <span class="text-[10px] font-semibold">Gains</span>
        </button>

        <button @click="tab='withdrawal'" :class="tab==='withdrawal' ? 'text-orange-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-wallet text-xl"></i>
            <span class="text-[10px] font-semibold">Retrait</span>
        </button>
    </div>
</nav>

</body>
</html>
