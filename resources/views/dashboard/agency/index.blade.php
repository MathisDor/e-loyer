<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Espace agence — E-Loyer</title>
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
        .nav-link.active { background: #F5F3FF; color: #7C3AED; font-weight: 600; }
        .nav-link .icon { width: 18px; text-align: center; font-size: 14px; }

        .kpi-card { background: white; border-radius: 14px; padding: 20px 22px; border: 1px solid #E2E8F0;
                    transition: box-shadow .15s, transform .15s; }
        .kpi-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.08); transform: translateY(-2px); }

        .prop-card { background: white; border-radius: 14px; border: 1px solid #E2E8F0; overflow: hidden;
                     transition: box-shadow .15s, transform .15s; }
        .prop-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,.1); transform: translateY(-2px); }

        .section-title { font-size: 11px; font-weight: 700; color: #94A3B8; text-transform: uppercase;
                         letter-spacing: .08em; margin-bottom: 14px; }
        .bottom-nav { padding-bottom: env(safe-area-inset-bottom, 0); }

        .plan-card { background: white; border-radius: 16px; border: 2px solid #E2E8F0; padding: 24px; transition: all .2s; }
        .plan-card.current { border-color: #7C3AED; }
        .plan-card:hover { box-shadow: 0 8px 30px rgba(0,0,0,.1); }
    </style>
</head>
<body x-data="{ tab: 'dashboard', sidebar: false }">

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
            <span class="text-[17px] font-bold text-violet-600">E-Loyer</span>
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
                <p class="font-semibold text-slate-800 text-sm truncate">{{ auth()->user()->agency_name ?? auth()->user()->name }}</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-violet-500 rounded-full"></span>
                    <span class="text-xs text-slate-500">Agence immobilière</span>
                </div>
            </div>
        </div>
        @if($subscription)
        <div class="flex items-center gap-2 px-3 py-2 bg-violet-50 rounded-xl border border-violet-100">
            <i class="fas fa-crown text-violet-500 text-xs"></i>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-violet-800">{{ $plan->name }}</p>
                <p class="text-[10px] text-violet-600">{{ $subscription->daysRemaining() }} jours restants</p>
            </div>
        </div>
        @else
        <div class="flex items-center gap-2 px-3 py-2 bg-amber-50 rounded-xl border border-amber-100">
            <i class="fas fa-exclamation-triangle text-amber-500 text-xs"></i>
            <p class="text-xs text-amber-700 font-medium">Aucun abonnement actif</p>
        </div>
        @endif
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
        <p class="section-title px-2 mb-3">Principal</p>

        <button @click="tab='dashboard'; sidebar=false" :class="tab==='dashboard' ? 'active' : ''" class="nav-link">
            <i class="fas fa-tachometer-alt icon"></i>
            <span>Tableau de bord</span>
        </button>

        <button @click="tab='properties'; sidebar=false" :class="tab==='properties' ? 'active' : ''" class="nav-link">
            <i class="fas fa-home icon"></i>
            <span class="flex-1">Mes biens</span>
            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 text-[11px] font-bold rounded-full">{{ $stats['total_properties'] }}/{{ $stats['max_properties'] }}</span>
        </button>

        <button @click="tab='bookings'; sidebar=false" :class="tab==='bookings' ? 'active' : ''" class="nav-link">
            <i class="fas fa-calendar-check icon"></i>
            <span class="flex-1">Réservations</span>
            @if($stats['pending_bookings'] > 0)
                <span class="px-2 py-0.5 bg-amber-100 text-amber-600 text-[11px] font-bold rounded-full">{{ $stats['pending_bookings'] }}</span>
            @endif
        </button>

        <div class="pt-4 mt-4 border-t border-slate-100 space-y-0.5">
            <p class="section-title px-2 mb-3">Finances</p>

            <button @click="tab='subscription'; sidebar=false" :class="tab==='subscription' ? 'active' : ''" class="nav-link">
                <i class="fas fa-crown icon"></i>
                <span>Abonnement</span>
            </button>

            <button @click="tab='sponsorship'; sidebar=false" :class="tab==='sponsorship' ? 'active' : ''" class="nav-link">
                <i class="fas fa-rocket icon"></i>
                <span class="flex-1">Sponsorisation</span>
                @if($activeSponsorships->count() > 0)
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-600 text-[11px] font-bold rounded-full">{{ $activeSponsorships->count() }}</span>
                @endif
            </button>

            <button @click="tab='transactions'; sidebar=false" :class="tab==='transactions' ? 'active' : ''" class="nav-link">
                <i class="fas fa-exchange-alt icon"></i>
                <span>Transactions</span>
            </button>

            <button @click="tab='withdrawal'; sidebar=false" :class="tab==='withdrawal' ? 'active' : ''" class="nav-link">
                <i class="fas fa-wallet icon"></i>
                <span>Retrait</span>
            </button>

            <button @click="tab='payment_methods'; sidebar=false" :class="tab==='payment_methods' ? 'active' : ''" class="nav-link">
                <i class="fas fa-credit-card icon"></i>
                <span>Moyens de paiement</span>
            </button>
        </div>

        <div class="pt-4 mt-4 border-t border-slate-100 space-y-0.5">
            <a href="{{ route('profile.show') }}" class="nav-link">
                <i class="fas fa-user-cog icon"></i>
                <span>Mon profil</span>
            </a>
            <a href="{{ route('home') }}" class="nav-link">
                <i class="fas fa-globe icon"></i>
                <span>Voir le site</span>
            </a>
            <a href="{{ route('messages.index') }}" class="nav-link">
                <i class="fas fa-envelope icon"></i>
                <span class="flex-1">Messages</span>
                @if(auth()->user()->unread_messages_count > 0)
                    <span class="px-2 py-0.5 bg-red-100 text-red-600 text-[11px] font-bold rounded-full">{{ auth()->user()->unread_messages_count }}</span>
                @endif
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
                       x-text="{
                           dashboard:'Tableau de bord', properties:'Mes biens', bookings:'Réservations',
                           subscription:'Abonnement', sponsorship:'Sponsorisation',
                           transactions:'Transactions', withdrawal:'Retrait', payment_methods:'Moyens de paiement'
                       }[tab]"></p>
                </div>
                <div class="flex items-center gap-2 lg:hidden">
                    <img src="{{ asset('img/eloyer-logo.png') }}" alt="" class="h-6 w-auto">
                    <span class="font-bold text-violet-600 text-sm">E-Loyer</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2 px-3 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                    <i class="fas fa-wallet text-emerald-600 text-sm"></i>
                    <span class="font-bold text-emerald-700 text-sm">{{ number_format($stats['balance'], 0, ',', ' ') }} FCFA</span>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="hidden sm:flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-xl text-[13px] font-semibold hover:bg-violet-700 transition-colors
                          {{ $stats['total_properties'] >= $stats['max_properties'] ? 'opacity-50 cursor-not-allowed' : '' }}">
                    <i class="fas fa-plus text-xs"></i> Nouveau bien
                </a>
                <a href="{{ route('messages.index') }}" class="relative w-9 h-9 flex items-center justify-center text-slate-500 hover:bg-slate-100 rounded-lg transition-colors">
                    <i class="fas fa-envelope text-sm"></i>
                    @if(auth()->user()->unread_messages_count > 0)
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    @endif
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

        {{-- ═══════════════════════ TAB: DASHBOARD ═══════════════════════ --}}
        <div x-show="tab==='dashboard'" x-cloak>

            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Bonjour, {{ auth()->user()->agency_name ?? explode(' ', auth()->user()->name)[0] }} 👋</h1>
                    <p class="text-slate-500 text-sm mt-1">Tableau de bord de votre agence</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="hidden lg:flex items-center gap-2 px-5 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-semibold hover:bg-violet-700 transition-colors shadow-sm shadow-violet-100
                          {{ $stats['total_properties'] >= $stats['max_properties'] ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <i class="fas fa-plus text-xs"></i> Nouveau bien
                </a>
            </div>

            {{-- KPI Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <button @click="tab='properties'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-violet-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-building text-violet-600 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['total_properties'] }}<span class="text-base text-slate-400 font-normal">/{{ $stats['max_properties'] }}</span></p>
                    <p class="text-sm text-slate-500 mt-1">Biens</p>
                    <div class="mt-2 h-1 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-violet-500 rounded-full transition-all"
                             style="width: {{ min(100, ($stats['total_properties'] / max(1, $stats['max_properties'])) * 100) }}%"></div>
                    </div>
                </button>

                <div class="kpi-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-wallet text-emerald-600 text-sm"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ number_format($stats['balance'], 0, ',', ' ') }}</p>
                    <p class="text-sm text-slate-500 mt-1">Solde (FCFA)</p>
                    <p class="text-xs text-slate-400 mt-1">Disponible au retrait</p>
                </div>

                <div class="kpi-card">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-sky-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-key text-sky-600 text-sm"></i>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-400">{{ $stats['occupancy_rate'] }}% occupé</span>
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['rented_properties'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Biens loués</p>
                    <p class="text-xs text-slate-400 mt-1">sur {{ $stats['total_properties'] }} publiés</p>
                </div>

                <button @click="tab='bookings'" class="kpi-card text-left">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-clock text-amber-600 text-sm"></i>
                        </div>
                        @if($stats['pending_bookings'] > 0)
                        <span class="w-5 h-5 bg-amber-500 text-white text-[11px] font-bold rounded-full flex items-center justify-center">{{ $stats['pending_bookings'] }}</span>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-slate-900">{{ $stats['pending_bookings'] }}</p>
                    <p class="text-sm text-slate-500 mt-1">Réservations en attente</p>
                    <p class="text-xs {{ $stats['pending_bookings'] > 0 ? 'text-amber-500 font-medium' : 'text-slate-400' }} mt-1">
                        {{ $stats['pending_bookings'] > 0 ? 'Action requise' : 'Aucune action' }}
                    </p>
                </button>
            </div>

            {{-- Alerte limite biens --}}
            @if($stats['total_properties'] >= $stats['max_properties'])
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-amber-500 flex-shrink-0"></i>
                <p class="text-amber-800 text-sm flex-1">Limite de biens atteinte.
                    <button @click="tab='subscription'" class="text-violet-600 font-semibold hover:underline">Upgradez votre abonnement</button>
                    pour publier plus.
                </p>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Colonne gauche --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Actions rapides --}}
                    <div class="grid grid-cols-3 gap-3">
                        <button @click="tab='subscription'"
                                class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-violet-300 hover:shadow-md transition-all text-left">
                            <div class="w-10 h-10 bg-violet-600 rounded-xl flex items-center justify-center mb-3">
                                <i class="fas fa-crown text-white text-sm"></i>
                            </div>
                            <p class="font-semibold text-slate-800 text-sm">Abonnement</p>
                            <p class="text-xs text-slate-400 mt-0.5">Gérer le plan</p>
                        </button>
                        <button @click="tab='sponsorship'"
                                class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-orange-300 hover:shadow-md transition-all text-left">
                            <div class="w-10 h-10 bg-orange-500 rounded-xl flex items-center justify-center mb-3">
                                <i class="fas fa-rocket text-white text-sm"></i>
                            </div>
                            <p class="font-semibold text-slate-800 text-sm">Sponsoriser</p>
                            <p class="text-xs text-slate-400 mt-0.5">Booster un bien</p>
                        </button>
                        <button @click="tab='withdrawal'"
                                class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-emerald-300 hover:shadow-md transition-all text-left">
                            <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center mb-3">
                                <i class="fas fa-money-bill-wave text-white text-sm"></i>
                            </div>
                            <p class="font-semibold text-slate-800 text-sm">Retirer</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ number_format($stats['balance'], 0, ',', ' ') }} F dispo</p>
                        </button>
                    </div>

                    {{-- Mes biens récents --}}
                    @if($properties->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="font-semibold text-slate-800">Mes biens</h2>
                            <button @click="tab='properties'" class="text-sm text-violet-600 font-medium hover:text-violet-700">
                                Voir tout <i class="fas fa-arrow-right text-xs ml-1"></i>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($properties->take(4) as $property)
                            @php
                                $image = $property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$property->id % count($imgIds)].'?w=400&h=240&fit=crop';
                                $sstyle = match($property->status) {
                                    'approuve'   => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Disponible'],
                                    'loue'       => ['pill' => 'bg-sky-100 text-sky-700',         'label' => 'Loué'],
                                    'en_attente' => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                                    default      => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $property->status_name],
                                };
                            @endphp
                            <div class="prop-card">
                                <div class="relative h-32">
                                    <img src="{{ $image }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                                    <span class="absolute top-2.5 right-2.5 px-2 py-1 {{ $sstyle['pill'] }} text-[11px] font-semibold rounded-lg">
                                        {{ $sstyle['label'] }}
                                    </span>
                                </div>
                                <div class="p-3">
                                    <p class="font-semibold text-slate-800 text-sm truncate">{{ $property->title }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="font-bold text-violet-600 text-sm">{{ $property->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                                        <a href="{{ route('properties.edit', $property) }}"
                                           class="w-7 h-7 bg-slate-100 rounded-lg flex items-center justify-center hover:bg-violet-100 hover:text-violet-600 transition-colors">
                                            <i class="fas fa-pen text-xs text-slate-500"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Transactions récentes --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800">Transactions récentes</h2>
                            <button @click="tab='transactions'" class="text-sm text-violet-600 font-medium">Tout voir</button>
                        </div>
                        @if($transactions->count() > 0)
                        <div class="divide-y divide-slate-50">
                            @foreach($transactions->take(5) as $transaction)
                            @php
                                $isCredit = in_array($transaction->type, ['deposit', 'commission']);
                                $ticon = $transaction->type === 'withdrawal' ? 'fa-arrow-up' : ($transaction->type === 'deposit' ? 'fa-arrow-down' : 'fa-exchange-alt');
                            @endphp
                            <div class="flex items-center gap-4 px-5 py-3.5">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                    {{ $isCredit ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                    <i class="fas {{ $ticon }} text-sm"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm">{{ $transaction->type_name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <p class="font-bold {{ $isCredit ? 'text-emerald-600' : 'text-red-600' }} flex-shrink-0">
                                    {{ $transaction->formatted_amount }}
                                </p>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="p-10 text-center">
                            <p class="text-slate-400 text-sm">Aucune transaction</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Colonne droite --}}
                <div class="space-y-5">

                    {{-- Abonnement actuel --}}
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Mon abonnement</h2>
                        </div>
                        <div class="p-5">
                            @if($subscription)
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-violet-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-crown text-white text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">{{ $plan->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $subscription->daysRemaining() }} jours restants</p>
                                </div>
                            </div>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                                    <span class="text-slate-500">Biens max</span>
                                    <span class="font-semibold text-slate-800">{{ $stats['max_properties'] }}</span>
                                </div>
                                <div class="flex items-center justify-between py-1.5 border-b border-slate-50">
                                    <span class="text-slate-500">Images par bien</span>
                                    <span class="font-semibold text-slate-800">{{ $stats['max_images'] }}</span>
                                </div>
                                <div class="flex items-center justify-between py-1.5">
                                    <span class="text-slate-500">Sponsorisation</span>
                                    <span class="font-semibold {{ $plan->can_sponsor ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $plan->can_sponsor ? 'Oui' : 'Non' }}
                                    </span>
                                </div>
                            </div>
                            @else
                            <div class="text-center py-3">
                                <p class="text-slate-500 text-sm mb-3">Aucun abonnement actif</p>
                            </div>
                            @endif
                            <button @click="tab='subscription'"
                                    class="mt-4 w-full py-2 text-sm font-semibold text-violet-600 bg-violet-50 rounded-xl hover:bg-violet-100 transition-colors">
                                {{ $subscription ? 'Gérer l\'abonnement' : 'Choisir un plan' }}
                            </button>
                        </div>
                    </div>

                    {{-- Réservations en attente --}}
                    @if($pendingBookings->count() > 0)
                    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-amber-100 bg-amber-50">
                            <div class="w-7 h-7 bg-amber-200 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-clock text-amber-700 text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-amber-900 text-sm">{{ $pendingBookings->count() }} en attente</p>
                                <p class="text-xs text-amber-600">Action requise</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-50">
                            @foreach($pendingBookings->take(3) as $booking)
                            @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$booking->property->id % count($imgIds)].'?w=80&fit=crop'; @endphp
                            <div class="flex items-start gap-3 px-5 py-3.5">
                                <img src="{{ $img }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0 mt-0.5">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $booking->property->title }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $booking->tenant->name ?? '—' }}</p>
                                    <p class="text-sm font-bold text-violet-600 mt-1">{{ $booking->formatted_monthly_amount }}/mois</p>
                                    <div class="flex gap-2 mt-2">
                                        <form action="{{ route('bookings.accept', $booking) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button class="w-full py-1.5 bg-violet-600 text-white text-xs font-semibold rounded-lg hover:bg-violet-700 transition-colors">
                                                <i class="fas fa-check mr-1"></i>Accepter
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($pendingBookings->count() > 3)
                        <div class="px-5 py-3 border-t border-slate-100">
                            <button @click="tab='bookings'" class="text-xs text-violet-600 font-medium">+ {{ $pendingBookings->count() - 3 }} autre(s)</button>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Sponsorisations actives --}}
                    @if($activeSponsorships->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h2 class="font-semibold text-slate-800 text-sm">Sponsorisations actives</h2>
                            <button @click="tab='sponsorship'" class="text-xs text-violet-600 font-medium">Voir</button>
                        </div>
                        <div class="px-5 py-4 space-y-3">
                            @foreach($activeSponsorships as $sp)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-rocket text-orange-500 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-slate-800 text-sm truncate">{{ $sp->property->title }}</p>
                                    <p class="text-xs text-slate-400">Expire {{ $sp->ends_at->format('d/m/Y') }}</p>
                                </div>
                                <span class="px-2 py-1 bg-emerald-50 text-emerald-700 text-[11px] font-semibold rounded-lg flex-shrink-0">Actif</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>{{-- /tab dashboard --}}


        {{-- ═══════════════════════ TAB: MES BIENS ═══════════════════════ --}}
        <div x-show="tab==='properties'" x-cloak>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Mes biens</h1>
                    <p class="text-slate-500 text-sm mt-1">{{ $stats['total_properties'] }}/{{ $stats['max_properties'] }} biens publiés</p>
                </div>
                <a href="{{ route('properties.create') }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-violet-600 text-white rounded-xl text-sm font-semibold hover:bg-violet-700 transition-colors
                          {{ $stats['total_properties'] >= $stats['max_properties'] ? 'opacity-50 cursor-not-allowed pointer-events-none' : '' }}">
                    <i class="fas fa-plus text-xs"></i> Ajouter
                </a>
            </div>

            @if($stats['total_properties'] >= $stats['max_properties'])
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-5 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-amber-500 flex-shrink-0"></i>
                <p class="text-amber-800 text-sm">Limite atteinte.
                    <button @click="tab='subscription'" class="text-violet-600 font-semibold hover:underline">Upgradez votre plan</button>
                    pour publier plus de biens.
                </p>
            </div>
            @endif

            @if($properties->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($properties as $property)
                @php
                    $image = $property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$property->id % count($imgIds)].'?w=400&h=240&fit=crop';
                    $sstyle = match($property->status) {
                        'approuve'   => ['pill' => 'bg-emerald-100 text-emerald-700', 'label' => 'Disponible'],
                        'loue'       => ['pill' => 'bg-sky-100 text-sky-700',         'label' => 'Loué'],
                        'en_attente' => ['pill' => 'bg-amber-100 text-amber-700',     'label' => 'En attente'],
                        default      => ['pill' => 'bg-slate-100 text-slate-600',     'label' => $property->status_name],
                    };
                @endphp
                <div class="prop-card">
                    <div class="relative h-40">
                        <img src="{{ $image }}" alt="{{ $property->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>
                        <span class="absolute top-3 right-3 px-2.5 py-1 {{ $sstyle['pill'] }} text-[11px] font-semibold rounded-lg">
                            {{ $sstyle['label'] }}
                        </span>
                    </div>
                    <div class="p-4">
                        <p class="font-semibold text-slate-800 truncate">{{ $property->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-map-marker-alt mr-1 text-violet-400"></i>{{ $property->city }}</p>
                        <div class="flex items-center justify-between mt-3 mb-3">
                            <p class="font-bold text-violet-600">{{ $property->formatted_price }}<span class="text-xs text-slate-400 font-normal">/mois</span></p>
                        </div>
                        <div class="flex gap-2 pt-3 border-t border-slate-100">
                            <a href="{{ route('properties.show', $property) }}"
                               class="flex-1 py-2 text-xs font-semibold text-center bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-colors">
                                <i class="fas fa-eye mr-1"></i>Voir
                            </a>
                            <a href="{{ route('properties.edit', $property) }}"
                               class="flex-1 py-2 text-xs font-semibold text-center bg-violet-50 text-violet-700 rounded-lg hover:bg-violet-100 transition-colors">
                                <i class="fas fa-pen mr-1"></i>Modifier
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-violet-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-home text-violet-600 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucun bien</h3>
                <p class="text-slate-500 text-sm mb-6">Commencez par ajouter votre premier bien immobilier.</p>
                <a href="{{ route('properties.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-2.5 bg-violet-600 text-white rounded-xl font-semibold hover:bg-violet-700 transition-colors">
                    <i class="fas fa-plus text-xs"></i> Ajouter un bien
                </a>
            </div>
            @endif
        </div>{{-- /tab properties --}}


        {{-- ═══════════════════════ TAB: RÉSERVATIONS ═══════════════════════ --}}
        <div x-show="tab==='bookings'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Réservations</h1>
                @if($stats['pending_bookings'] > 0)
                <p class="text-amber-600 font-medium text-sm mt-1">{{ $stats['pending_bookings'] }} en attente de votre réponse</p>
                @else
                <p class="text-slate-500 text-sm mt-1">Aucune demande en attente</p>
                @endif
            </div>

            @if($pendingBookings->count() > 0)
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></div>
                    <p class="section-title mb-0">En attente · {{ $pendingBookings->count() }}</p>
                </div>
                <div class="space-y-4">
                    @foreach($pendingBookings as $booking)
                    @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$booking->property->id % count($imgIds)].'?w=120&fit=crop'; @endphp
                    <div class="bg-white rounded-2xl border border-amber-200 p-5" x-data="{ refusing: false }">
                        <div class="flex items-start gap-4 mb-4">
                            <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-slate-800 truncate">{{ $booking->property->title }}</p>
                                <p class="text-sm text-slate-500 mt-0.5"><i class="fas fa-user mr-1.5 text-slate-300"></i>{{ $booking->tenant->name ?? '—' }}</p>
                                <p class="text-xs text-slate-400 mt-1"><i class="fas fa-calendar mr-1.5 text-slate-300"></i>{{ $booking->start_date->format('d/m/Y') }} → {{ $booking->end_date->format('d/m/Y') }}</p>
                                <p class="font-bold text-violet-600 mt-1.5">{{ $booking->formatted_monthly_amount }}/mois</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('bookings.accept', $booking) }}" method="POST" class="flex-1">
                                @csrf
                                <button class="w-full py-2.5 bg-violet-600 text-white text-sm font-semibold rounded-xl hover:bg-violet-700 transition-colors">
                                    <i class="fas fa-check mr-1.5"></i>Accepter
                                </button>
                            </form>
                            <button @click="refusing=!refusing"
                                    :class="refusing ? 'bg-red-600 text-white' : 'border border-red-200 text-red-600 hover:bg-red-50'"
                                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl transition-colors">
                                <i class="fas fa-times mr-1.5"></i>Refuser
                            </button>
                        </div>
                        <div x-show="refusing" x-cloak class="mt-3"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0">
                            <form action="{{ route('bookings.reject', $booking) }}" method="POST">
                                @csrf
                                <textarea name="rejection_reason" rows="2" required
                                          class="w-full px-3 py-2.5 text-sm border border-red-200 rounded-xl focus:outline-none focus:border-red-400 resize-none mb-2"
                                          placeholder="Raison du refus (obligatoire)..."></textarea>
                                <button class="w-full py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition-colors">
                                    <i class="fas fa-ban mr-1.5"></i>Confirmer le refus
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($activeBookings->count() > 0)
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <p class="section-title mb-0">Actives · {{ $activeBookings->count() }}</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @foreach($activeBookings as $booking)
                    @php $img = $booking->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$booking->property->id % count($imgIds)].'?w=80&fit=crop'; @endphp
                    <div class="flex items-center gap-4 px-5 py-4">
                        <img src="{{ $img }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">{{ $booking->property->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $booking->tenant->name ?? '—' }} · Depuis {{ $booking->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg block mb-1">Active</span>
                            <p class="font-bold text-violet-600 text-sm">{{ $booking->formatted_monthly_amount }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($pendingBookings->isEmpty() && $activeBookings->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-calendar-check text-slate-400 text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 text-lg mb-2">Aucune réservation</h3>
                <p class="text-slate-500 text-sm">Les demandes de location apparaîtront ici.</p>
            </div>
            @endif
        </div>{{-- /tab bookings --}}


        {{-- ═══════════════════════ TAB: ABONNEMENT ═══════════════════════ --}}
        <div x-show="tab==='subscription'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Abonnement</h1>
                <p class="text-slate-500 text-sm mt-1">Gérez votre plan et vos limites</p>
            </div>

            @if($subscription)
            <div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl p-6 text-white mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-violet-200 text-sm">Plan actuel</p>
                        <h2 class="text-3xl font-bold mt-1">{{ $plan->name }}</h2>
                        <p class="text-violet-200 mt-2 text-sm">{{ $subscription->daysRemaining() }} jours restants</p>
                    </div>
                    <div class="text-right">
                        <p class="text-4xl font-bold">{{ $plan->formatted_price }}</p>
                        <p class="text-violet-200 text-sm">/mois</p>
                    </div>
                </div>
            </div>
            @endif

            <h3 class="font-semibold text-slate-800 mb-4">Plans disponibles</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($plans as $planOption)
                <div class="plan-card {{ $plan && $plan->id === $planOption->id ? 'current' : '' }} relative">
                    @if($plan && $plan->id === $planOption->id)
                    <span class="absolute top-4 right-4 px-2 py-1 bg-violet-100 text-violet-700 text-[11px] font-bold rounded-full">Actuel</span>
                    @endif
                    <h4 class="text-xl font-bold text-slate-800">{{ $planOption->name }}</h4>
                    <p class="text-3xl font-bold text-violet-600 mt-2">{{ $planOption->formatted_price }}<span class="text-sm text-slate-400 font-normal">/mois</span></p>
                    <ul class="mt-5 space-y-2.5">
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas fa-check text-emerald-500 flex-shrink-0 w-4"></i>{{ $planOption->max_properties }} biens max
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas fa-check text-emerald-500 flex-shrink-0 w-4"></i>{{ $planOption->max_images_per_property }} images/bien
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas {{ $planOption->can_sponsor ? 'fa-check text-emerald-500' : 'fa-times text-red-400' }} flex-shrink-0 w-4"></i>Sponsorisation
                        </li>
                        <li class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="fas {{ $planOption->priority_support ? 'fa-check text-emerald-500' : 'fa-times text-red-400' }} flex-shrink-0 w-4"></i>Support prioritaire
                        </li>
                    </ul>
                    <button class="w-full mt-6 py-2.5 rounded-xl font-semibold text-sm transition-colors
                        {{ $plan && $plan->id === $planOption->id ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-violet-600 text-white hover:bg-violet-700' }}">
                        {{ $plan && $plan->id === $planOption->id ? 'Plan actuel' : 'Choisir ce plan' }}
                    </button>
                </div>
                @endforeach
            </div>
        </div>{{-- /tab subscription --}}


        {{-- ═══════════════════════ TAB: SPONSORISATION ═══════════════════════ --}}
        <div x-show="tab==='sponsorship'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Sponsorisation</h1>
                <p class="text-slate-500 text-sm mt-1">Boostez la visibilité de vos biens</p>
            </div>

            @if($activeSponsorships->count() > 0)
            <div class="mb-6">
                <h2 class="font-semibold text-slate-800 mb-4">Actives ({{ $activeSponsorships->count() }})</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($activeSponsorships as $sp)
                    @php $img = $sp->property->main_image ?? 'https://images.unsplash.com/photo-'.$imgIds[$sp->property->id % count($imgIds)].'?w=160&fit=crop'; @endphp
                    <div class="bg-white rounded-2xl border border-emerald-200 p-5">
                        <div class="flex items-start gap-4">
                            <img src="{{ $img }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg">{{ $sp->type_name }}</span>
                                <p class="font-semibold text-slate-800 mt-2 truncate">{{ $sp->property->title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5"><i class="fas fa-calendar mr-1"></i>Expire {{ $sp->ends_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <h2 class="font-semibold text-slate-800 mb-4">Options disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <div class="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-bolt text-sky-600 text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Boost</h3>
                    <p class="text-3xl font-bold text-sky-600 mt-2">5 000 <span class="text-base font-normal text-slate-400">FCFA</span></p>
                    <p class="text-sm text-slate-400 mt-1">7 jours de visibilité</p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>+50% de visibilité</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Badge "Boosté"</li>
                    </ul>
                    <button class="w-full mt-5 py-2.5 bg-sky-600 text-white rounded-xl font-semibold text-sm hover:bg-sky-700 transition-colors">Choisir</button>
                </div>

                <div class="bg-white rounded-2xl border-2 border-orange-300 p-6 relative">
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full">Populaire</span>
                    <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-star text-orange-600 text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">À la une</h3>
                    <p class="text-3xl font-bold text-orange-600 mt-2">15 000 <span class="text-base font-normal text-slate-400">FCFA</span></p>
                    <p class="text-sm text-slate-400 mt-1">14 jours de visibilité</p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Page d'accueil</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>+100% de visibilité</li>
                    </ul>
                    <button class="w-full mt-5 py-2.5 bg-orange-600 text-white rounded-xl font-semibold text-sm hover:bg-orange-700 transition-colors">Choisir</button>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <div class="w-11 h-11 bg-violet-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-gem text-violet-600 text-lg"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Premium</h3>
                    <p class="text-3xl font-bold text-violet-600 mt-2">30 000 <span class="text-base font-normal text-slate-400">FCFA</span></p>
                    <p class="text-sm text-slate-400 mt-1">30 jours de visibilité</p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600">
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>Tout "À la une"</li>
                        <li><i class="fas fa-check text-emerald-500 mr-2"></i>+200% de visibilité</li>
                    </ul>
                    <button class="w-full mt-5 py-2.5 bg-violet-600 text-white rounded-xl font-semibold text-sm hover:bg-violet-700 transition-colors">Choisir</button>
                </div>
            </div>
        </div>{{-- /tab sponsorship --}}


        {{-- ═══════════════════════ TAB: TRANSACTIONS ═══════════════════════ --}}
        <div x-show="tab==='transactions'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Transactions</h1>
                <p class="text-slate-500 text-sm mt-1">Historique de vos opérations financières</p>
            </div>
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                @if($transactions->count() > 0)
                <div class="divide-y divide-slate-50">
                    @foreach($transactions as $transaction)
                    @php
                        $isCredit = in_array($transaction->type, ['deposit', 'commission']);
                        $ticon = $transaction->type === 'withdrawal' ? 'fa-arrow-up' : ($transaction->type === 'deposit' ? 'fa-arrow-down' : 'fa-exchange-alt');
                        $tstyle = match($transaction->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700',
                            'pending'   => 'bg-amber-50 text-amber-700',
                            'failed'    => 'bg-red-50 text-red-700',
                            default     => 'bg-slate-100 text-slate-600',
                        };
                    @endphp
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0
                            {{ $isCredit ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                            <i class="fas {{ $ticon }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800">{{ $transaction->type_name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $transaction->description ?? $transaction->reference }}</p>
                            <p class="text-xs text-slate-400">{{ $transaction->created_at->format('d/m/Y à H:i') }}</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="font-bold text-lg {{ $isCredit ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transaction->formatted_amount }}
                            </p>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $tstyle }} font-medium">{{ $transaction->status_name }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-exchange-alt text-slate-400 text-2xl"></i>
                    </div>
                    <p class="text-slate-500 text-sm">Aucune transaction</p>
                </div>
                @endif
            </div>
        </div>{{-- /tab transactions --}}


        {{-- ═══════════════════════ TAB: RETRAIT ═══════════════════════ --}}
        <div x-show="tab==='withdrawal'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Retrait</h1>
                <p class="text-slate-500 text-sm mt-1">Solde disponible : {{ number_format($stats['balance'], 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Demande de retrait</h3>
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 mb-5">
                        <p class="text-xs text-emerald-600 font-medium mb-0.5">Solde disponible</p>
                        <p class="text-3xl font-bold text-emerald-700">{{ number_format($stats['balance'], 0, ',', ' ') }} <span class="text-lg font-normal">FCFA</span></p>
                    </div>
                    <form>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Montant à retirer</label>
                            <div class="relative">
                                <input type="number" placeholder="0"
                                       class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 text-lg font-bold">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm">FCFA</span>
                            </div>
                        </div>
                        <div class="mb-5">
                            <label class="block text-sm font-medium text-slate-700 mb-2">Moyen de paiement</label>
                            @if($paymentMethods->count() > 0)
                            <div class="space-y-2">
                                @foreach($paymentMethods as $method)
                                <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-xl cursor-pointer hover:border-violet-400 transition-colors">
                                    <input type="radio" name="payment_method" value="{{ $method->id }}" class="text-violet-600">
                                    <i class="fas {{ $method->type === 'bank_transfer' ? 'fa-university text-slate-500' : 'fa-mobile-alt text-violet-500' }}"></i>
                                    <span class="font-medium text-slate-700 text-sm">{{ $method->type_name }}</span>
                                    <span class="text-slate-400 text-sm">{{ $method->masked_number }}</span>
                                </label>
                                @endforeach
                            </div>
                            @else
                            <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl text-center">
                                <p class="text-amber-700 text-sm">Aucun moyen de paiement enregistré</p>
                                <button @click="tab='payment_methods'" type="button" class="text-violet-600 font-semibold text-sm mt-2 hover:underline">
                                    Ajouter un moyen de paiement
                                </button>
                            </div>
                            @endif
                        </div>
                        <button type="submit"
                                class="w-full py-3 bg-violet-600 text-white rounded-xl font-semibold hover:bg-violet-700 transition-colors {{ $paymentMethods->count() === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $paymentMethods->count() === 0 ? 'disabled' : '' }}>
                            Demander le retrait
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Historique des retraits</h3>
                    <div class="text-center py-12">
                        <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-wallet text-slate-400 text-xl"></i>
                        </div>
                        <p class="text-slate-400 text-sm">Aucun retrait effectué</p>
                    </div>
                </div>
            </div>
        </div>{{-- /tab withdrawal --}}


        {{-- ═══════════════════════ TAB: MOYENS DE PAIEMENT ═══════════════════════ --}}
        <div x-show="tab==='payment_methods'" x-cloak>
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-900">Moyens de paiement</h1>
                <p class="text-slate-500 text-sm mt-1">Gérez vos comptes de retrait</p>
            </div>
            <div class="grid lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-semibold text-slate-800 mb-4">Enregistrés</h3>
                    @if($paymentMethods->count() > 0)
                    <div class="space-y-3">
                        @foreach($paymentMethods as $method)
                        <div class="bg-white rounded-2xl border {{ $method->is_default ? 'border-violet-300' : 'border-slate-200' }} p-5 flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0
                                {{ $method->type === 'airtel_money' ? 'bg-red-100' : ($method->type === 'moov_money' ? 'bg-sky-100' : 'bg-slate-100') }}">
                                <i class="fas {{ $method->type === 'bank_transfer' ? 'fa-university' : 'fa-mobile-alt' }} text-lg
                                    {{ $method->type === 'airtel_money' ? 'text-red-600' : ($method->type === 'moov_money' ? 'text-sky-600' : 'text-slate-600') }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800">{{ $method->type_name }}</p>
                                <p class="text-sm text-slate-400">{{ $method->name }} · {{ $method->masked_number }}</p>
                            </div>
                            @if($method->is_default)
                            <span class="px-2 py-1 bg-violet-100 text-violet-700 text-xs font-semibold rounded-full flex-shrink-0">Par défaut</span>
                            @endif
                            <button class="w-8 h-8 text-slate-300 hover:text-red-500 hover:bg-red-50 rounded-lg flex items-center justify-center transition-colors flex-shrink-0">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="bg-slate-50 rounded-2xl border border-slate-200 p-8 text-center">
                        <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-credit-card text-slate-400 text-xl"></i>
                        </div>
                        <p class="text-slate-500 text-sm">Aucun moyen de paiement enregistré</p>
                    </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h3 class="font-semibold text-slate-800 mb-5">Ajouter un moyen de paiement</h3>
                    <form class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Type</label>
                            <select class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 text-sm bg-white">
                                <option value="airtel_money">Airtel Money</option>
                                <option value="moov_money">Moov Money</option>
                                <option value="bank_transfer">Virement bancaire</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Nom du titulaire</label>
                            <input type="text" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 text-sm" placeholder="Nom complet">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Numéro</label>
                            <input type="tel" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-violet-400 text-sm" placeholder="+241 XX XX XX XX">
                        </div>
                        <button type="submit" class="w-full py-3 bg-violet-600 text-white rounded-xl font-semibold text-sm hover:bg-violet-700 transition-colors">
                            Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>{{-- /tab payment_methods --}}

    </main>
</div>

{{-- ═══════════════════════ BOTTOM NAV MOBILE ═══════════════════════ --}}
<nav class="bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-40 lg:hidden">
    <div class="flex items-center justify-around h-16 px-2">
        <button @click="tab='dashboard'" :class="tab==='dashboard' ? 'text-violet-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-tachometer-alt text-xl"></i>
            <span class="text-[10px] font-semibold">Dashboard</span>
        </button>

        <button @click="tab='properties'" :class="tab==='properties' ? 'text-violet-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-home text-xl"></i>
            <span class="text-[10px] font-semibold">Biens</span>
        </button>

        <div class="flex-1 flex justify-center">
            <a href="{{ route('properties.create') }}"
               class="w-14 h-14 bg-violet-600 rounded-2xl flex items-center justify-center shadow-lg shadow-violet-200 hover:bg-violet-700 transition-colors -mt-5">
                <i class="fas fa-plus text-white text-xl"></i>
            </a>
        </div>

        <button @click="tab='bookings'" :class="tab==='bookings' ? 'text-amber-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors relative">
            <i class="fas fa-calendar-check text-xl"></i>
            <span class="text-[10px] font-semibold">Réservations</span>
            @if($stats['pending_bookings'] > 0)
            <span class="absolute top-1 right-2.5 w-4 h-4 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ $stats['pending_bookings'] }}</span>
            @endif
        </button>

        <button @click="tab='subscription'" :class="tab==='subscription' ? 'text-violet-600' : 'text-slate-400'"
                class="flex flex-col items-center gap-1 flex-1 py-2 transition-colors">
            <i class="fas fa-crown text-xl"></i>
            <span class="text-[10px] font-semibold">Abonnement</span>
        </button>
    </div>
</nav>

</body>
</html>
