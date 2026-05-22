<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Administration — E-Loyer</title>
    <link rel="icon" type="image/png" href="{{ asset('img/eloyer-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak]{display:none!important}
        .hide-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
        .hide-scrollbar::-webkit-scrollbar{display:none}
        body{background:#0f172a}
        .sidebar{transform:translateX(-100%);transition:transform .3s cubic-bezier(.4,0,.2,1)}
        .sidebar.open{transform:translateX(0)}
        @media(min-width:1024px){
            .sidebar{transform:translateX(0);position:fixed}
            .main-wrap{margin-left:268px}
            .bottom-nav{display:none!important}
            .burger-btn{display:none!important}
        }
        .tap-card{transition:transform .15s ease}
        .tap-card:active{transform:scale(.97)}
        .bottom-nav{padding-bottom:env(safe-area-inset-bottom,0)}
        .stat-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08)}
        @keyframes ping-slow{0%,100%{opacity:1}50%{opacity:.4}}
        .ping-slow{animation:ping-slow 2s ease-in-out infinite}
    </style>
</head>
<body class="font-sans antialiased" x-data="{ tab:'home', sidebar:false }">

{{-- Overlay --}}
<div x-show="sidebar" @click="sidebar=false" x-cloak
     class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 lg:hidden"
     x-transition:enter="transition duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

{{-- ── SIDEBAR ──────────────────────────────────────────────── --}}
<aside class="sidebar fixed top-0 left-0 bottom-0 w-[268px] bg-slate-900 border-r border-white/10 z-50 flex flex-col"
       :class="{'open':sidebar}">
    <div class="flex items-center justify-between h-16 px-5 border-b border-white/10">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-8 w-auto opacity-90">
            <span class="text-lg font-bold text-white">E-Loyer</span>
            <span class="px-1.5 py-0.5 bg-red-500 text-white text-[9px] font-extrabold rounded uppercase tracking-wider">Admin</span>
        </a>
        <button @click="sidebar=false" class="lg:hidden p-2 text-white/40 hover:text-white rounded-lg">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3 p-3 bg-white/5 rounded-2xl">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-11 h-11 rounded-xl object-cover ring-2 ring-white/20">
            <div class="flex-1 min-w-0">
                <p class="font-bold text-white text-sm truncate">{{ auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <i class="fas fa-shield-alt text-red-400 text-[10px]"></i>
                    <p class="text-xs text-red-400 font-semibold">Super administrateur</p>
                </div>
            </div>
        </div>
    </div>

    <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto hide-scrollbar">
        @php
        $navItems = [
            ['tab'=>'home',        'icon'=>'fa-tachometer-alt','label'=>'Tableau de bord'],
            ['tab'=>'users',       'icon'=>'fa-users',         'label'=>'Utilisateurs',    'badge'=>$stats['total_users']],
            ['tab'=>'properties',  'icon'=>'fa-building',      'label'=>'Propriétés',       'badge'=>$stats['pending_properties'], 'urgent'=>true],
            ['tab'=>'visits',      'icon'=>'fa-calendar-check','label'=>'Visites',          'badge'=>$stats['upcoming_visits']],
            ['tab'=>'contracts',   'icon'=>'fa-file-contract', 'label'=>'Contrats',         'badge'=>$stats['active_contracts']],
            ['tab'=>'commissions', 'icon'=>'fa-coins',         'label'=>'Commissions'],
            ['tab'=>'revenue',     'icon'=>'fa-chart-line',    'label'=>'Revenus'],
        ];
        @endphp
        @foreach($navItems as $item)
        <button @click="tab='{{ $item['tab'] }}'; sidebar=false"
                :class="tab==='{{ $item['tab'] }}' ? 'bg-red-600 text-white font-semibold' : 'text-white/60 hover:bg-white/5 hover:text-white'"
                class="flex items-center gap-3 w-full px-4 py-3 rounded-xl text-sm transition-all">
            <i class="fas {{ $item['icon'] }} w-5 text-center"></i>
            <span class="flex-1 text-left">{{ $item['label'] }}</span>
            @if(!empty($item['badge']) && $item['badge'] > 0)
                <span class="px-2 py-0.5 {{ !empty($item['urgent']) ? 'bg-amber-400 text-amber-900' : 'bg-white/10 text-white' }} text-xs font-bold rounded-full {{ !empty($item['urgent']) ? 'ping-slow' : '' }}">{{ $item['badge'] }}</span>
            @endif
        </button>
        @endforeach

        <div class="pt-3 mt-3 border-t border-white/10 space-y-0.5">
            <a href="{{ route('home') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-white/40 hover:bg-white/5 hover:text-white transition-all">
                <i class="fas fa-globe w-5 text-center"></i><span>Voir le site</span>
            </a>
        </div>
    </nav>

    <div class="p-3 border-t border-white/10">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-400 hover:bg-red-900/30 rounded-xl transition-colors font-medium">
                <i class="fas fa-sign-out-alt w-5 text-center"></i><span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

{{-- ── MAIN WRAP ────────────────────────────────────────────── --}}
<div class="main-wrap min-h-screen pb-24 lg:pb-0 bg-gray-50">

    {{-- Top Bar --}}
    <header class="sticky top-0 z-30 bg-slate-900/95 backdrop-blur-xl border-b border-white/10">
        <div class="flex items-center justify-between h-14 px-4 lg:px-6">
            <button @click="sidebar=!sidebar" class="burger-btn w-10 h-10 flex items-center justify-center text-white/70 hover:bg-white/10 rounded-xl transition-colors">
                <i class="fas fa-bars"></i>
            </button>
            <div class="flex items-center gap-2 lg:hidden">
                <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-7 w-auto opacity-80">
                <span class="font-bold text-white text-base">Admin</span>
            </div>
            <div class="hidden lg:flex items-center gap-3">
                <p class="text-sm font-semibold text-white"
                   x-text="{ home:'Tableau de bord', users:'Utilisateurs', properties:'Propriétés', visits:'Visites', contracts:'Contrats', commissions:'Commissions', revenue:'Revenus' }[tab]"></p>
            </div>
            <div class="flex items-center gap-2">
                @if($stats['pending_properties'] > 0)
                <button @click="tab='properties'"
                        class="relative flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 text-amber-900 rounded-xl text-xs font-bold hover:bg-amber-400 transition-colors">
                    <i class="fas fa-clock"></i>
                    <span>{{ $stats['pending_properties'] }} en attente</span>
                </button>
                @endif
                <div class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center">
                    <span class="text-white text-xs font-bold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>
    </header>

    @if(session('success') || session('error'))
    <div class="max-w-3xl mx-auto px-4 pt-3">
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

    <main class="max-w-2xl mx-auto lg:max-w-6xl px-4 lg:px-8 py-4 lg:py-8">

        {{-- ══════════════════════ TAB: ACCUEIL ══════════════════════ --}}
        <div x-show="tab==='home'" x-cloak>

            {{-- Hero Card --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 mb-5 text-white border border-white/10">
                <div class="absolute -top-12 -right-12 w-56 h-56 bg-red-500/10 rounded-full"></div>
                <div class="absolute -bottom-8 -left-8 w-40 h-40 bg-white/5 rounded-full"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="text-white/50 text-xs font-medium uppercase tracking-wider">Revenus plateforme</p>
                            <p class="text-3xl font-extrabold mt-1">{{ number_format($stats['platform_revenue'], 0, ',', ' ') }}<span class="text-lg font-normal text-white/60 ml-1">FCFA</span></p>
                        </div>
                        <div class="text-right">
                            <div class="px-3 py-1.5 bg-red-500/20 border border-red-400/30 text-red-300 text-xs font-bold rounded-full flex items-center gap-1.5">
                                <i class="fas fa-shield-alt text-[9px]"></i>ADMIN
                            </div>
                        </div>
                    </div>

                    {{-- KPI rapides --}}
                    <div class="grid grid-cols-3 gap-3">
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-3 text-center">
                            <p class="text-xl font-extrabold text-white">{{ $stats['total_users'] }}</p>
                            <p class="text-[10px] text-white/50 mt-0.5">Utilisateurs</p>
                        </div>
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-3 text-center">
                            <p class="text-xl font-extrabold text-white">{{ $stats['approved_properties'] }}</p>
                            <p class="text-[10px] text-white/50 mt-0.5">Biens publiés</p>
                        </div>
                        <div class="bg-amber-500/20 border border-amber-400/30 rounded-2xl p-3 text-center">
                            <p class="text-xl font-extrabold text-amber-300 ping-slow">{{ $stats['pending_properties'] }}</p>
                            <p class="text-[10px] text-amber-400/80 mt-0.5">À valider</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alerte validation urgente --}}
            @if($stats['pending_properties'] > 0)
            <div class="mb-5 bg-amber-50 border border-amber-200 rounded-3xl overflow-hidden">
                <div class="p-4 flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-clock text-amber-600 ping-slow"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm">{{ $stats['pending_properties'] }} bien(s) en attente de validation</p>
                        <p class="text-xs text-gray-500">À approuver ou rejeter pour mise en ligne</p>
                    </div>
                    <button @click="tab='properties'" class="px-4 py-2 bg-amber-500 text-white text-xs font-bold rounded-xl hover:bg-amber-600 transition-colors flex-shrink-0">
                        Valider
                    </button>
                </div>
            </div>
            @endif

            {{-- Stats 2×2 --}}
            <div class="grid grid-cols-2 gap-3 mb-5">
                <button @click="tab='users'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow border border-gray-100">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-users text-blue-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Utilisateurs</p>
                    <div class="flex items-center gap-3 mt-2 text-[10px] text-gray-400">
                        <span class="text-blue-600 font-semibold">{{ $stats['total_tenants'] }} loc.</span>
                        <span class="text-emerald-600 font-semibold">{{ $stats['total_owners'] }} prop.</span>
                    </div>
                </button>

                <button @click="tab='properties'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow border border-gray-100">
                    <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-building text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_properties'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Propriétés</p>
                    <div class="flex items-center gap-3 mt-2 text-[10px]">
                        <span class="text-emerald-600 font-semibold">{{ $stats['approved_properties'] }} approuvées</span>
                        @if($stats['pending_properties'] > 0)
                        <span class="text-amber-600 font-bold">{{ $stats['pending_properties'] }} ⚠</span>
                        @endif
                    </div>
                </button>

                <button @click="tab='visits'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow border border-gray-100">
                    <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-calendar-check text-purple-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_visits'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Visites total</p>
                    @if($stats['upcoming_visits'] > 0)
                    <div class="mt-2 text-[10px] text-purple-600 font-semibold">{{ $stats['upcoming_visits'] }} à venir</div>
                    @endif
                </button>

                <button @click="tab='contracts'" class="tap-card bg-white rounded-2xl p-4 text-left hover:shadow-md transition-shadow border border-gray-100">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-file-contract text-indigo-600"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_contracts'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 font-medium">Contrats total</p>
                    <div class="mt-2 text-[10px] text-indigo-600 font-semibold">{{ $stats['active_contracts'] }} actifs</div>
                </button>
            </div>

            {{-- Répartition revenus --}}
            <div class="bg-white rounded-3xl p-5 mb-5 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Répartition des revenus</p>
                <div class="space-y-3">
                    @php
                        $totalRev = array_sum($revenueBySource);
                    @endphp
                    @foreach([
                        ['label'=>'Commissions réservations', 'key'=>'bookings', 'color'=>'bg-emerald-500', 'icon'=>'fa-calendar-alt', 'text'=>'text-emerald-600'],
                        ['label'=>'Frais de service visites', 'key'=>'visits',   'color'=>'bg-blue-500',    'icon'=>'fa-eye',          'text'=>'text-blue-600'],
                        ['label'=>'Commissions contrats',     'key'=>'contracts','color'=>'bg-indigo-500',  'icon'=>'fa-file-contract','text'=>'text-indigo-600'],
                    ] as $src)
                    @php $amount = $revenueBySource[$src['key']] ?? 0; $pct = $totalRev > 0 ? round(($amount/$totalRev)*100) : 0; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <i class="fas {{ $src['icon'] }} {{ $src['text'] }} text-xs"></i>
                                <span class="text-xs text-gray-600 font-medium">{{ $src['label'] }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-bold text-gray-900">{{ number_format($amount, 0, ',', ' ') }} F</span>
                                <span class="text-[10px] text-gray-400 ml-1">{{ $pct }}%</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="{{ $src['color'] }} h-1.5 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Répartition utilisateurs --}}
            <div class="bg-white rounded-3xl p-5 mb-5 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Utilisateurs par rôle</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['label'=>'Locataires',   'count'=>$stats['total_tenants'],     'icon'=>'fa-user',         'bg'=>'bg-blue-100',   'text'=>'text-blue-700'],
                        ['label'=>'Propriétaires','count'=>$stats['total_owners'],      'icon'=>'fa-key',          'bg'=>'bg-emerald-100','text'=>'text-emerald-700'],
                        ['label'=>'Démarcheurs',  'count'=>$stats['total_prospectors'], 'icon'=>'fa-handshake',    'bg'=>'bg-orange-100', 'text'=>'text-orange-700'],
                        ['label'=>'Agences',      'count'=>$stats['total_agencies'],    'icon'=>'fa-briefcase',    'bg'=>'bg-purple-100', 'text'=>'text-purple-700'],
                    ] as $role)
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-2xl border border-gray-100">
                        <div class="w-9 h-9 {{ $role['bg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $role['icon'] }} {{ $role['text'] }} text-sm"></i>
                        </div>
                        <div>
                            <p class="font-extrabold text-gray-900 text-lg leading-none">{{ $role['count'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ $role['label'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Derniers utilisateurs --}}
            <div class="bg-white rounded-3xl p-5 mb-5 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <p class="font-bold text-gray-900 text-sm">Nouveaux membres</p>
                    <button @click="tab='users'" class="text-xs text-red-600 font-semibold">Voir tout</button>
                </div>
                <div class="space-y-1">
                    @foreach($recentUsers->take(5) as $user)
                    @php
                        $typeStyle = match($user->user_type) {
                            'admin'        => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Admin'],
                            'locataire'    => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'label'=>'Locataire'],
                            'proprietaire' => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Propriétaire'],
                            'demarcheur'   => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'label'=>'Démarcheur'],
                            'agence'       => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'label'=>'Agence'],
                            default        => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'label'=>ucfirst($user->user_type)],
                        };
                    @endphp
                    <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                        <div class="relative flex-shrink-0">
                            <img src="{{ $user->avatar_url }}" class="w-10 h-10 rounded-xl object-cover">
                            @if($user->is_suspended)
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-ban text-white text-[7px]"></i>
                            </div>
                            @elseif($user->is_verified)
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-[7px]"></i>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <span class="px-2 py-0.5 {{ $typeStyle['bg'] }} {{ $typeStyle['text'] }} text-[10px] font-bold rounded-lg">{{ $typeStyle['label'] }}</span>
                            <span class="text-[10px] text-gray-400">{{ $user->created_at->format('d/m') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>{{-- /tab home --}}


        {{-- ══════════════════════ TAB: UTILISATEURS ══════════════════════ --}}
        <div x-show="tab==='users'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Utilisateurs ({{ $stats['total_users'] }})</h2>

            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Membre</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider hidden md:table-cell">Type</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Inscrit</th>
                                <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Statut</th>
                                <th class="text-right py-3 px-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($recentUsers as $user)
                            @php
                                $typeStyle = match($user->user_type) {
                                    'admin'        => ['bg'=>'bg-red-100',    'text'=>'text-red-700',    'label'=>'Admin'],
                                    'locataire'    => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',   'label'=>'Locataire'],
                                    'proprietaire' => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Propriétaire'],
                                    'demarcheur'   => ['bg'=>'bg-orange-100', 'text'=>'text-orange-700', 'label'=>'Démarcheur'],
                                    'agence'       => ['bg'=>'bg-purple-100', 'text'=>'text-purple-700', 'label'=>'Agence'],
                                    default        => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',   'label'=>ucfirst($user->user_type)],
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 {{ $user->is_suspended ? 'opacity-50' : '' }}">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-shrink-0">
                                            <img src="{{ $user->avatar_url }}" class="w-9 h-9 rounded-xl object-cover">
                                            @if($user->is_suspended)
                                            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full flex items-center justify-center">
                                                <i class="fas fa-ban text-white text-[6px]"></i>
                                            </div>
                                            @elseif($user->is_verified)
                                            <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-emerald-500 rounded-full flex items-center justify-center">
                                                <i class="fas fa-check text-white text-[6px]"></i>
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 text-sm">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-400 hidden sm:block">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 hidden md:table-cell">
                                    <span class="px-2 py-0.5 {{ $typeStyle['bg'] }} {{ $typeStyle['text'] }} text-[10px] font-bold rounded-lg">{{ $typeStyle['label'] }}</span>
                                </td>
                                <td class="py-3 px-4 text-xs text-gray-400 hidden lg:table-cell">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="py-3 px-4">
                                    @if($user->is_suspended)
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-bold rounded-lg">Suspendu</span>
                                    @elseif($user->is_verified)
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[10px] font-semibold rounded-lg">Vérifié</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[10px] rounded-lg">Actif</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center justify-end gap-1 relative" x-data="{ suspending:false }">
                                        <a href="{{ route('admin.users.show', $user) }}"
                                           class="w-7 h-7 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg flex items-center justify-center transition-colors">
                                            <i class="fas fa-eye text-[10px]"></i>
                                        </a>
                                        @if(!$user->isAdmin())
                                            @if($user->is_suspended)
                                            <form action="{{ route('admin.users.unsuspend', $user) }}" method="POST">
                                                @csrf
                                                <button class="w-7 h-7 bg-emerald-100 hover:bg-emerald-200 text-emerald-700 rounded-lg flex items-center justify-center transition-colors" title="Réactiver">
                                                    <i class="fas fa-undo text-[10px]"></i>
                                                </button>
                                            </form>
                                            @else
                                            <button @click="suspending=!suspending"
                                                    class="w-7 h-7 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-lg flex items-center justify-center transition-colors" title="Suspendre">
                                                <i class="fas fa-ban text-[10px]"></i>
                                            </button>
                                            <div x-show="suspending" x-cloak @click.away="suspending=false"
                                                 class="absolute right-0 top-8 w-56 bg-white border border-gray-200 rounded-2xl shadow-xl p-3 z-50"
                                                 x-transition:enter="transition ease-out duration-150"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100">
                                                <p class="text-xs font-bold text-gray-800 mb-2">Suspendre {{ $user->name }}</p>
                                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                                    @csrf
                                                    <textarea name="suspension_reason" rows="2" required
                                                              class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-xl resize-none mb-2"
                                                              placeholder="Raison..."></textarea>
                                                    <button class="w-full py-1.5 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700">Confirmer</button>
                                                </form>
                                            </div>
                                            @endif
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                                  onsubmit="return confirm('Supprimer définitivement ?')">
                                                @csrf @method('DELETE')
                                                <button class="w-7 h-7 bg-red-100 hover:bg-red-200 text-red-600 rounded-lg flex items-center justify-center transition-colors" title="Supprimer">
                                                    <i class="fas fa-trash text-[10px]"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-100 text-center">
                    <a href="{{ route('admin.users.index') }}" class="text-sm text-red-600 font-bold hover:underline">
                        Voir tous les utilisateurs <i class="fas fa-arrow-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>{{-- /tab users --}}


        {{-- ══════════════════════ TAB: PROPRIÉTÉS ══════════════════════ --}}
        <div x-show="tab==='properties'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Propriétés</h2>

            {{-- En attente --}}
            @if($pendingProperties->count() > 0)
            <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 mb-5">
                <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-4">
                    <i class="fas fa-clock mr-1"></i>En attente de validation ({{ $pendingProperties->count() }})
                </p>
                <div class="space-y-4">
                    @foreach($pendingProperties as $prop)
                    @php $img = $prop->main_image ?? 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=120&h=120&fit=crop'; @endphp
                    <div class="bg-white rounded-2xl p-4 border border-amber-100">
                        <div class="flex gap-3 mb-3">
                            <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-gray-900 truncate">{{ $prop->title }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <i class="fas fa-user mr-1 text-gray-400"></i>{{ $prop->owner->name }}
                                    @if($prop->prospector)· <span class="text-orange-600">{{ $prop->prospector->name }}</span>@endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ $prop->city }}</p>
                                <p class="font-extrabold text-emerald-600 text-sm mt-1">{{ $prop->formatted_price }}/mois</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('admin.properties.approve', $prop) }}" method="POST" class="flex-1">
                                @csrf
                                <button class="w-full py-2.5 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-colors">
                                    <i class="fas fa-check mr-1"></i>Approuver
                                </button>
                            </form>
                            <div class="flex-1" x-data="{ open:false }">
                                <button @click="open=!open" class="w-full py-2.5 bg-red-100 text-red-600 text-xs font-bold rounded-xl hover:bg-red-200 transition-colors">
                                    <i class="fas fa-times mr-1"></i>Rejeter
                                </button>
                                <div x-show="open" x-cloak class="mt-2"
                                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                                    <form action="{{ route('admin.properties.reject', $prop) }}" method="POST">
                                        @csrf
                                        <textarea name="rejection_reason" rows="2" required
                                                  class="w-full px-3 py-2 text-xs border border-red-200 rounded-xl resize-none mb-2"
                                                  placeholder="Raison du rejet..."></textarea>
                                        <button class="w-full py-2 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700">Confirmer le rejet</button>
                                    </form>
                                </div>
                            </div>
                            <a href="{{ route('admin.properties.show', $prop) }}"
                               class="w-10 h-10 bg-gray-100 text-gray-600 rounded-xl flex items-center justify-center hover:bg-gray-200 transition-colors flex-shrink-0">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Liste complète --}}
            <div class="bg-white rounded-3xl border border-gray-100">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <p class="font-bold text-gray-900 text-sm">Toutes les propriétés</p>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-1 bg-emerald-100 text-emerald-700 font-bold rounded-lg">{{ $stats['approved_properties'] }} approuvées</span>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 font-bold rounded-lg">{{ $stats['active_bookings'] ?? 0 }} louées</span>
                    </div>
                </div>
                <a href="{{ route('admin.properties.index') }}" class="block p-4 text-center text-sm text-red-600 font-bold hover:underline">
                    Gérer toutes les propriétés <i class="fas fa-arrow-right ml-1 text-xs"></i>
                </a>
            </div>
        </div>{{-- /tab properties --}}


        {{-- ══════════════════════ TAB: VISITES ══════════════════════ --}}
        <div x-show="tab==='visits'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Visites</h2>

            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_visits'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Total</p>
                </div>
                <div class="bg-purple-50 rounded-2xl p-4 text-center border border-purple-100">
                    <p class="text-2xl font-extrabold text-purple-700">{{ $stats['upcoming_visits'] }}</p>
                    <p class="text-xs text-purple-600 mt-0.5 font-medium">À venir</p>
                </div>
                <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_visits'] - $stats['upcoming_visits'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Passées</p>
                </div>
            </div>

            @if($recentVisits->count() > 0)
            <div class="bg-white rounded-3xl p-5 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Visites récentes</p>
                <div class="space-y-1">
                    @foreach($recentVisits->take(8) as $visit)
                    @php
                        $vstyle = match($visit->status) {
                            'reservee'  => ['bg'=>'bg-amber-100', 'text'=>'text-amber-700', 'label'=>'En attente'],
                            'en_cours'  => ['bg'=>'bg-blue-100',  'text'=>'text-blue-700',  'label'=>'En cours'],
                            'terminee'  => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Terminée'],
                            'acceptee'  => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Acceptée'],
                            'refusee'   => ['bg'=>'bg-red-100',   'text'=>'text-red-700',   'label'=>'Refusée'],
                            'annulee'   => ['bg'=>'bg-gray-100',  'text'=>'text-gray-600',  'label'=>'Annulée'],
                            default     => ['bg'=>'bg-gray-100',  'text'=>'text-gray-600',  'label'=>$visit->status],
                        };
                    @endphp
                    <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $visit->property->title ?? '—' }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $visit->tenant->name ?? '—' }} · {{ $visit->scheduled_at->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="px-2 py-0.5 {{ $vstyle['bg'] }} {{ $vstyle['text'] }} text-[10px] font-bold rounded-lg flex-shrink-0">{{ $vstyle['label'] }}</span>
                        <a href="{{ route('visits.show', $visit) }}" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-eye text-[10px]"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>{{-- /tab visits --}}


        {{-- ══════════════════════ TAB: CONTRATS ══════════════════════ --}}
        <div x-show="tab==='contracts'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Contrats</h2>

            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_contracts'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Total</p>
                </div>
                <div class="bg-indigo-50 rounded-2xl p-4 text-center border border-indigo-100">
                    <p class="text-2xl font-extrabold text-indigo-700">{{ $stats['active_contracts'] }}</p>
                    <p class="text-xs text-indigo-600 mt-0.5 font-medium">Actifs</p>
                </div>
                <div class="bg-white rounded-2xl p-4 text-center border border-gray-100">
                    <p class="text-2xl font-extrabold text-gray-900">{{ $stats['total_contracts'] - $stats['active_contracts'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Terminés</p>
                </div>
            </div>

            @if($recentContracts->count() > 0)
            <div class="bg-white rounded-3xl p-5 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Contrats récents</p>
                <div class="space-y-1">
                    @foreach($recentContracts->take(8) as $contract)
                    @php
                        $cstyle = match($contract->status) {
                            'actif'     => ['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Actif'],
                            'termine'   => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',  'label'=>'Terminé'],
                            'resilie'   => ['bg'=>'bg-red-100',    'text'=>'text-red-700',   'label'=>'Résilié'],
                            'renouvele' => ['bg'=>'bg-blue-100',   'text'=>'text-blue-700',  'label'=>'Renouvelé'],
                            default     => ['bg'=>'bg-gray-100',   'text'=>'text-gray-600',  'label'=>$contract->status],
                        };
                    @endphp
                    <div class="flex items-center gap-3 py-2.5 border-b border-gray-50 last:border-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-gray-900 text-sm truncate">{{ $contract->property->title ?? '—' }}</p>
                            <p class="text-xs text-gray-400">
                                {{ $contract->tenant->name ?? '—' }} · {{ number_format($contract->monthly_amount, 0, ',', ' ') }} FCFA/mois
                            </p>
                        </div>
                        <span class="px-2 py-0.5 {{ $cstyle['bg'] }} {{ $cstyle['text'] }} text-[10px] font-bold rounded-lg flex-shrink-0">{{ $cstyle['label'] }}</span>
                        <a href="{{ route('contracts.show', $contract) }}" class="w-7 h-7 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-eye text-[10px]"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>{{-- /tab contracts --}}


        {{-- ══════════════════════ TAB: COMMISSIONS ══════════════════════ --}}
        <div x-show="tab==='commissions'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Commissions</h2>
            <div class="bg-white rounded-3xl p-8 text-center border border-gray-100">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-coins text-amber-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900 mb-2">Gestion des commissions</h3>
                <p class="text-gray-500 text-sm mb-4">Gérez les commissions des démarcheurs depuis l'interface dédiée.</p>
                <a href="{{ route('admin.commissions.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-amber-500 text-white rounded-2xl font-bold hover:bg-amber-600 transition-colors">
                    <i class="fas fa-coins"></i> Gérer les commissions
                </a>
            </div>
        </div>{{-- /tab commissions --}}


        {{-- ══════════════════════ TAB: REVENUS ══════════════════════ --}}
        <div x-show="tab==='revenue'" x-cloak>
            <h2 class="text-xl font-extrabold text-gray-900 mb-5">Revenus de la plateforme</h2>

            {{-- Total --}}
            <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-3xl p-6 mb-5 text-white border border-white/10">
                <p class="text-white/50 text-xs font-medium uppercase tracking-wider mb-1">Revenus totaux</p>
                <p class="text-4xl font-extrabold">{{ number_format($stats['platform_revenue'], 0, ',', ' ') }}<span class="text-lg font-normal text-white/60 ml-2">FCFA</span></p>
                <p class="text-white/40 text-xs mt-2">Commissions + frais de service</p>
            </div>

            {{-- Détail par source --}}
            <div class="bg-white rounded-3xl p-5 border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">Répartition par source</p>
                <div class="space-y-4">
                    @foreach([
                        ['label'=>'Commissions réservations (12%)', 'key'=>'bookings', 'color'=>'bg-emerald-500', 'icon'=>'fa-calendar-alt', 'iconbg'=>'bg-emerald-100', 'icontxt'=>'text-emerald-600'],
                        ['label'=>'Frais visites (400 FCFA/visite)', 'key'=>'visits',   'color'=>'bg-blue-500',    'icon'=>'fa-eye',          'iconbg'=>'bg-blue-100',    'icontxt'=>'text-blue-600'],
                        ['label'=>'Commissions contrats (5%)',       'key'=>'contracts','color'=>'bg-indigo-500',  'icon'=>'fa-file-contract','iconbg'=>'bg-indigo-100',  'icontxt'=>'text-indigo-600'],
                    ] as $src)
                    @php
                        $amount = $revenueBySource[$src['key']] ?? 0;
                        $total  = array_sum($revenueBySource);
                        $pct    = $total > 0 ? round(($amount/$total)*100) : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 {{ $src['iconbg'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas {{ $src['icon'] }} {{ $src['icontxt'] }} text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between mb-1.5">
                                <p class="text-sm font-semibold text-gray-800">{{ $src['label'] }}</p>
                                <p class="text-sm font-extrabold text-gray-900">{{ number_format($amount, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="{{ $src['color'] }} h-2 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 w-8 text-right flex-shrink-0">{{ $pct }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>{{-- /tab revenue --}}

    </main>
</div>

{{-- ── BOTTOM NAV ───────────────────────────────────────── --}}
<nav class="bottom-nav fixed bottom-0 left-0 right-0 bg-slate-900 border-t border-white/10 z-40 lg:hidden">
    <div class="flex items-center justify-around h-16 px-2">
        <button @click="tab='home'"
                :class="tab==='home' ? 'text-white' : 'text-white/40'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-tachometer-alt text-xl"></i>
            <span class="text-[9px] font-semibold">Accueil</span>
        </button>

        <button @click="tab='users'"
                :class="tab==='users' ? 'text-white' : 'text-white/40'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-users text-xl"></i>
            <span class="text-[9px] font-semibold">Membres</span>
        </button>

        {{-- FAB : validation propriétés --}}
        <div class="flex-1 flex justify-center">
            <button @click="tab='properties'"
                    class="relative w-14 h-14 rounded-2xl flex items-center justify-center shadow-xl -mt-5 transition-colors"
                    :class="tab==='properties' ? 'bg-red-500' : 'bg-amber-500 hover:bg-amber-400'">
                <i class="fas fa-building text-white text-xl"></i>
                @if($stats['pending_properties'] > 0)
                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-600 text-white text-[9px] font-extrabold rounded-full flex items-center justify-center border-2 border-slate-900">{{ $stats['pending_properties'] }}</span>
                @endif
            </button>
        </div>

        <button @click="tab='contracts'"
                :class="tab==='contracts' ? 'text-white' : 'text-white/40'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-file-contract text-xl"></i>
            <span class="text-[9px] font-semibold">Contrats</span>
        </button>

        <button @click="tab='revenue'"
                :class="tab==='revenue' ? 'text-white' : 'text-white/40'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-chart-line text-xl"></i>
            <span class="text-[9px] font-semibold">Revenus</span>
        </button>
    </div>
</nav>

</body>
</html>
