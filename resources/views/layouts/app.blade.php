<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Loyer') - Location Longue Durée au Gabon</title>
    <meta name="description" content="@yield('description', 'Trouvez votre logement idéal au Gabon. Location longue durée d\'appartements, maisons, villas à Libreville, Port-Gentil, Franceville...')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('img/eloyer-logo.png') }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased" x-data="{ mobileMenuOpen: false }">
    @if(!isset($hideNav) || !$hideNav)
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-14 md:h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-8 md:h-10 w-auto">
                        <span class="text-lg md:text-xl font-bold text-green-600">E-Loyer</span>
                    </a>
                </div>
                
                <!-- Navigation Links (Desktop) -->
                <div class="hidden md:flex items-center gap-1">
                    <!-- Dropdown: Je cherche -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center gap-1.5 px-4 py-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-search text-sm"></i>
                            <span>Je cherche</span>
                            <i class="fas fa-chevron-down text-xs ml-0.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                            <a href="{{ route('properties.index') }}" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-700 transition-colors group">
                                <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-green-200 transition-colors">
                                    <i class="fas fa-home text-green-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Trouver une maison</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Parcourez toutes les annonces</p>
                                </div>
                            </a>
                            <a href="{{ url('/demarcheurs') }}" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-200 transition-colors">
                                    <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Trouver un démarcheur</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Visites guidées & accompagnées</p>
                                </div>
                            </a>
                            <a href="{{ url('/agences') }}" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                                <div class="w-9 h-9 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-200 transition-colors">
                                    <i class="fas fa-city text-purple-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Trouver une agence</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Agences immobilières agréées</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Dropdown: Je propose -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false"
                                class="flex items-center gap-1.5 px-4 py-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200 font-medium">
                            <i class="fas fa-plus-circle text-sm"></i>
                            <span>Je propose</span>
                            <i class="fas fa-chevron-down text-xs ml-0.5 transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                            @auth
                                @if(auth()->user()->isProprietaire())
                                    <a href="{{ route('properties.create') }}" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors group">
                                @else
                                    <a href="{{ route('register') }}?type=proprietaire" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors group">
                                @endif
                            @else
                                <a href="{{ route('register') }}?type=proprietaire" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 hover:text-yellow-700 transition-colors group">
                            @endauth
                                <div class="w-9 h-9 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-yellow-200 transition-colors">
                                    <i class="fas fa-key text-yellow-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Publier une maison</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Mettez votre bien en location</p>
                                </div>
                            </a>
                            <a href="{{ route('register') }}?type=demarcheur" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-colors group">
                                <div class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-orange-200 transition-colors">
                                    <i class="fas fa-handshake text-orange-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Devenir démarcheur</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Gagnez des commissions</p>
                                </div>
                            </a>
                            <a href="{{ route('register') }}?type=agence" class="flex items-start gap-3 px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors group">
                                <div class="w-9 h-9 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-200 transition-colors">
                                    <i class="fas fa-briefcase text-indigo-600 text-sm"></i>
                                </div>
                                <div>
                                    <p class="font-semibold text-sm">Je suis une agence</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Espace agences immobilières</p>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Aide -->
                    <a href="{{ url('/aide') }}" class="flex items-center gap-1.5 px-4 py-2 text-gray-600 hover:text-green-600 hover:bg-green-50 rounded-lg transition-all duration-200 font-medium">
                        <i class="fas fa-question-circle text-sm"></i>
                        <span>Aide</span>
                    </a>
                </div>
                
                <!-- Right Side (Desktop) -->
                <div class="hidden md:flex items-center gap-3">
                    @guest
                        <a href="{{ route('login') }}" class="px-4 py-2 text-gray-600 hover:text-green-600 font-medium transition-colors">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/25 transition-all duration-200 hover:-translate-y-0.5">
                            S'inscrire
                        </a>
                    @else
                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="relative p-2 text-gray-500 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="fas fa-bell text-lg"></i>
                                <span id="notification-badge" class="hidden absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center font-bold"></span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                                <div class="p-4 border-b border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-900">Notifications</h3>
                                        <a href="{{ route('notifications.index') }}" class="text-sm text-green-600 hover:underline">Voir tout</a>
                                    </div>
                                </div>
                                <div id="notifications-list" class="max-h-80 overflow-y-auto">
                                    <div class="p-4 text-center text-gray-500 text-sm">
                                        Aucune notification
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Messages -->
                        <a href="{{ route('messages.index') }}" class="relative p-2 text-gray-500 hover:text-green-600 hover:bg-gray-100 rounded-lg transition-colors">
                            <i class="fas fa-envelope text-lg"></i>
                            @if(auth()->user()->unread_messages_count > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-yellow-400 text-gray-900 text-xs rounded-full flex items-center justify-center font-bold">
                                    {{ auth()->user()->unread_messages_count }}
                                </span>
                            @endif
                        </a>
                        
                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 p-1.5 pr-3 rounded-xl hover:bg-gray-100 transition-colors">
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover">
                                <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden py-2">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->user_type }}</p>
                                </div>
                                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-tachometer-alt w-5 text-gray-400"></i>
                                    <span>Tableau de bord</span>
                                </a>
                                <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-user w-5 text-gray-400"></i>
                                    <span>Mon profil</span>
                                </a>
                                <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-heart w-5 text-gray-400"></i>
                                    <span>Mes favoris</span>
                                </a>
                                <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-envelope w-5 text-gray-400"></i>
                                    <span>Messages</span>
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <a href="{{ route('admin.properties.pending') }}" class="flex items-center gap-3 px-4 py-2.5 text-gray-700 hover:bg-gray-50 transition-colors">
                                        <i class="fas fa-shield-alt w-5 text-green-600"></i>
                                        <span>Administration</span>
                                    </a>
                                @endif
                                <div class="border-t border-gray-100 my-2"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-red-600 hover:bg-red-50 transition-colors w-full">
                                        <i class="fas fa-sign-out-alt w-5"></i>
                                        <span>Déconnexion</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
                
                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center gap-2">
                    @auth
                        <a href="{{ route('messages.index') }}" class="relative p-2 text-gray-500">
                            <i class="fas fa-envelope text-lg"></i>
                            @if(auth()->user()->unread_messages_count > 0)
                                <span class="absolute -top-1 -right-1 w-4 h-4 bg-yellow-400 text-gray-900 text-xs rounded-full flex items-center justify-center font-bold">
                                    {{ auth()->user()->unread_messages_count }}
                                </span>
                            @endif
                        </a>
                    @endauth
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-gray-500 hover:text-green-600 rounded-lg">
                        <i class="fas fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                        <i class="fas fa-times text-xl" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden border-t border-gray-100 bg-white shadow-lg">
            <div class="px-4 py-4 space-y-1">
                @auth
                    <div class="flex items-center gap-3 px-3 py-3 mb-3 bg-gray-50 rounded-xl">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-lg object-cover">
                        <div>
                            <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->user_type }}</p>
                        </div>
                    </div>
                @endauth
                
                @auth
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                        <i class="fas fa-tachometer-alt w-5 text-gray-400"></i>
                        <span class="font-medium">Tableau de bord</span>
                    </a>
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                        <i class="fas fa-user w-5 text-gray-400"></i>
                        <span class="font-medium">Mon profil</span>
                    </a>
                    <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                        <i class="fas fa-heart w-5 text-gray-400"></i>
                        <span class="font-medium">Mes favoris</span>
                    </a>
                    <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                        <i class="fas fa-bell w-5 text-gray-400"></i>
                        <span class="font-medium">Notifications</span>
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.properties.pending') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                            <i class="fas fa-shield-alt w-5 text-green-600"></i>
                            <span class="font-medium">Administration</span>
                        </a>
                    @endif
                @endauth

                <div class="border-t border-gray-100 my-3"></div>

                <!-- Je cherche -->
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Je cherche</p>
                <a href="{{ route('properties.index') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-home text-green-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Trouver une maison</span>
                </a>
                <a href="{{ url('/demarcheurs') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Trouver un démarcheur</span>
                </a>
                <a href="{{ url('/agences') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-purple-600 hover:bg-purple-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-city text-purple-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Trouver une agence</span>
                </a>

                <div class="border-t border-gray-100 my-3"></div>

                <!-- Je propose -->
                <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Je propose</p>
                @auth
                    @if(auth()->user()->isProprietaire())
                        <a href="{{ route('properties.create') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-yellow-700 hover:bg-yellow-50 rounded-xl transition-colors">
                    @else
                        <a href="{{ route('register') }}?type=proprietaire" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-yellow-700 hover:bg-yellow-50 rounded-xl transition-colors">
                    @endif
                @else
                    <a href="{{ route('register') }}?type=proprietaire" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-yellow-700 hover:bg-yellow-50 rounded-xl transition-colors">
                @endauth
                    <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-key text-yellow-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Publier une maison</span>
                </a>
                <a href="{{ route('register') }}?type=demarcheur" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-handshake text-orange-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Devenir démarcheur</span>
                </a>
                <a href="{{ route('register') }}?type=agence" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-briefcase text-indigo-600 text-sm"></i>
                    </div>
                    <span class="font-medium">Je suis une agence</span>
                </a>

                <div class="border-t border-gray-100 my-3"></div>

                <!-- Aide -->
                <a href="{{ url('/aide') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                    <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-question-circle text-gray-500 text-sm"></i>
                    </div>
                    <span class="font-medium">Aide</span>
                </a>

                <div class="border-t border-gray-100 my-3"></div>
                
                @guest
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-3 text-gray-700 hover:text-green-600 hover:bg-green-50 rounded-xl transition-colors">
                        <i class="fas fa-sign-in-alt w-5 text-gray-400"></i>
                        <span class="font-medium">Connexion</span>
                    </a>
                    
                    <a href="{{ route('register') }}" class="flex items-center justify-center gap-2 mx-3 mt-2 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                        <i class="fas fa-user-plus"></i>
                        <span>Créer un compte</span>
                    </a>
                @else
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="flex items-center gap-3 w-full px-3 py-3 text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span class="font-medium">Déconnexion</span>
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </nav>
    @endif
    
    <!-- Flash Messages -->
    @if(session('success') || session('error') || session('status') || session('info'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl flex items-center gap-3" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-check-circle text-green-500"></i>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button @click="show = false" class="text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center gap-3" role="alert" x-data="{ show: true }" x-show="show">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif
            @if(session('status'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center gap-3" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                    <i class="fas fa-info-circle text-blue-500"></i>
                    <span class="flex-1">{{ session('status') }}</span>
                    <button @click="show = false" class="text-blue-600 hover:text-blue-800"><i class="fas fa-times"></i></button>
                </div>
            @endif
            @if(session('info'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl flex items-center gap-3" role="alert" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)">
                    <i class="fas fa-route text-blue-500"></i>
                    <span class="flex-1">{{ session('info') }}</span>
                    <button @click="show = false" class="text-blue-600 hover:text-blue-800"><i class="fas fa-times"></i></button>
                </div>
            @endif
        </div>
    @endif
    
    <!-- Main Content -->
    <main>
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </main>
    
    <!-- Footer -->
    @if(!isset($hideFooter) || !$hideFooter)
    <footer class="bg-white border-t border-gray-200 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <!-- Brand -->
                <div class="col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-10 w-auto">
                        <span class="text-xl font-bold text-green-600">E-Loyer</span>
                    </div>
                    <p class="text-gray-600 max-w-md text-sm md:text-base">
                        La première plateforme de location longue durée au Gabon. Trouvez votre logement idéal à Libreville, Port-Gentil, Franceville et dans tout le pays.
                    </p>
                    <div class="flex gap-3 mt-6">
                        <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center text-gray-600 hover:bg-green-600 hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm md:text-base">Liens Rapides</h3>
                    <ul class="space-y-2 text-gray-600 text-sm">
                        <li><a href="{{ route('properties.index') }}" class="hover:text-green-600 transition-colors">Trouver une maison</a></li>
                        <li><a href="{{ url('/demarcheurs') }}" class="hover:text-green-600 transition-colors">Trouver un démarcheur</a></li>
                        <li><a href="{{ url('/agences') }}" class="hover:text-green-600 transition-colors">Trouver une agence</a></li>
                        <li><a href="{{ route('register') }}?type=proprietaire" class="hover:text-green-600 transition-colors">Publier une maison</a></li>
                        <li><a href="{{ route('register') }}?type=demarcheur" class="hover:text-green-600 transition-colors">Devenir démarcheur</a></li>
                        <li><a href="{{ route('register') }}?type=agence" class="hover:text-green-600 transition-colors">Je suis une agence</a></li>
                        <li><a href="{{ url('/aide') }}" class="hover:text-green-600 transition-colors">Aide</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h3 class="font-semibold text-gray-900 mb-4 text-sm md:text-base">Contact</h3>
                    <ul class="space-y-3 text-gray-600 text-sm">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-map-marker-alt text-green-600 mt-0.5"></i>
                            <span>Libreville, Gabon</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-phone text-green-600"></i>
                            <span>+241 XX XX XX XX</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-envelope text-green-600"></i>
                            <span>contact@e-loyer.ga</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fas fa-clock text-green-600"></i>
                            <span>Lun - Sam: 8h - 18h</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="border-t border-gray-200 mt-10 pt-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500 text-sm">Paiement sécurisé :</span>
                        <div class="flex items-center gap-4">
                            <!-- Airtel Money Logo -->
                            <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100">
                                <svg class="h-8 w-auto" viewBox="0 0 120 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="120" height="40" rx="6" fill="#ED1C24"/>
                                    <text x="12" y="26" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="white">airtel</text>
                                    <text x="55" y="26" font-family="Arial, sans-serif" font-size="10" fill="white">money</text>
                                </svg>
                            </div>
                            <!-- Moov Money Logo -->
                            <div class="bg-white rounded-lg p-2 shadow-sm border border-gray-100">
                                <svg class="h-8 w-auto" viewBox="0 0 120 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="120" height="40" rx="6" fill="#0066B3"/>
                                    <text x="12" y="26" font-family="Arial, sans-serif" font-size="14" font-weight="bold" fill="white">moov</text>
                                    <text x="55" y="26" font-family="Arial, sans-serif" font-size="10" fill="#FFD700">money</text>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-200 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">
                    © {{ date('Y') }} E-Loyer. Tous droits réservés.
                </p>
                <div class="flex flex-wrap justify-center gap-4 md:gap-6 text-sm text-gray-500">
                    <a href="#" class="hover:text-green-600 transition-colors">Conditions d'utilisation</a>
                    <a href="#" class="hover:text-green-600 transition-colors">Politique de confidentialité</a>
                    <a href="#" class="hover:text-green-600 transition-colors">Mentions légales</a>
                </div>
            </div>
        </div>
    </footer>
    @endif
    
    @if(!isset($hideFooter) || !$hideFooter)
    <!-- Back to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="fixed bottom-6 right-6 w-12 h-12 bg-green-600 text-white rounded-full shadow-lg hover:bg-green-700 transition-all duration-300 z-40 hidden md:flex items-center justify-center"
            x-data="{ show: false }"
            x-init="window.addEventListener('scroll', () => { show = window.scrollY > 500 })"
            x-show="show"
            x-transition>
        <i class="fas fa-chevron-up"></i>
    </button>
    @endif
    
    @stack('scripts')
    
    <script>
        @auth
        function updateNotifications() {
            fetch('{{ route("notifications.count") }}')
                .then(r => r.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count > 9 ? '9+' : data.count;
                            badge.classList.remove('hidden');
                            badge.classList.add('flex');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }
                })
                .catch(() => {});
        }
        updateNotifications();
        setInterval(updateNotifications, 30000);
        @endauth
    </script>
</body>
</html>
