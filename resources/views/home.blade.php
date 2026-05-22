@extends('layouts.app')

@section('title', 'Accueil')
@section('description', 'E-loyer - La première plateforme de location longue durée au Gabon. Trouvez appartements, maisons et villas à Libreville, Port-Gentil, Franceville.')

@section('content')
<!-- Hero Section -->
<section class="relative min-h-[600px] flex items-center overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-gradient-to-br from-green-50 via-white to-blue-50"></div>
    <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cg fill=&quot;none&quot; fill-rule=&quot;evenodd&quot;%3E%3Cg fill=&quot;%2316a34a&quot; fill-opacity=&quot;0.03&quot;%3E%3Cpath d=&quot;M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z&quot;/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left Content -->
            <div class="space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 rounded-full">
                    <span class="w-2 h-2 bg-green-600 rounded-full animate-pulse"></span>
                    <span class="text-green-700 font-medium text-sm">+{{ $stats['properties'] }} logements disponibles</span>
                </div>
                
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight">
                    Trouvez votre
                    <span class="text-green-600">logement idéal</span>
                    au Gabon
                </h1>
                
                <p class="text-lg text-gray-600 max-w-lg">
                    Location longue durée d'appartements, maisons et villas. 
                    Paiement sécurisé par <strong>Mobile Money</strong> (Airtel, Moov, Gabon Telecom).
                </p>
                
                <!-- Search Form -->
                <form action="{{ route('properties.index') }}" method="GET" class="bg-white p-2 rounded-2xl shadow-xl shadow-gray-200/50 border border-gray-100">
                    <div class="flex flex-col md:flex-row gap-2">
                        <div class="flex-1 relative">
                            <i class="fas fa-map-marker-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="city" class="w-full pl-11 pr-4 py-4 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500/20 transition-all">
                                <option value="">Toutes les villes</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city }}">{{ $city }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 relative">
                            <i class="fas fa-home absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="type" class="w-full pl-11 pr-4 py-4 bg-gray-50 border-0 rounded-xl text-gray-900 focus:bg-white focus:ring-2 focus:ring-green-500/20 transition-all">
                                <option value="">Type de bien</option>
                                <option value="appartement">Appartement</option>
                                <option value="maison">Maison</option>
                                <option value="studio">Studio</option>
                                <option value="villa">Villa</option>
                                <option value="chambre">Chambre</option>
                            </select>
                        </div>
                        <button type="submit" class="px-8 py-4 bg-white border-2 border-green-500 text-green-600 rounded-xl font-bold 
                                                        shadow-lg shadow-green-500/15
                                                        hover:bg-green-50 hover:border-green-600 hover:text-green-700 hover:shadow-xl hover:shadow-green-500/25 
                                                        transition-all duration-200 hover:-translate-y-0.5 
                                                        flex items-center justify-center gap-2">
                            <i class="fas fa-search"></i>
                            <span>Rechercher</span>
                        </button>
                    </div>
                </form>
                
                <!-- Trust Badges -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-3 sm:gap-5 text-xs sm:text-sm text-gray-500">
                    <div class="flex items-center gap-2 bg-white/80 sm:bg-transparent px-3 py-2 sm:p-0 rounded-lg">
                        <i class="fas fa-shield-alt text-green-600"></i>
                        <span>Paiement sécurisé</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/80 sm:bg-transparent px-3 py-2 sm:p-0 rounded-lg">
                        <i class="fas fa-check-circle text-green-600"></i>
                        <span>Annonces vérifiées</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/80 sm:bg-transparent px-3 py-2 sm:p-0 rounded-lg">
                        <i class="fas fa-headset text-green-600"></i>
                        <span>Support 7j/7</span>
                    </div>
                    <div class="flex items-center gap-2 bg-white/80 sm:bg-transparent px-3 py-2 sm:p-0 rounded-lg">
                        <i class="fas fa-bolt text-green-600"></i>
                        <span>Transactions rapides</span>
                    </div>
                </div>
            </div>
            
            <!-- Right Visual -->
            <div class="hidden lg:block relative">
                <div class="absolute -top-10 -right-10 w-72 h-72 bg-yellow-200/30 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-green-200/30 rounded-full blur-3xl"></div>
                
                <div class="relative grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="bg-white rounded-2xl shadow-xl p-6 card-hover">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-building text-green-600 text-xl"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">{{ $stats['properties'] }}+</h3>
                            <p class="text-gray-500 text-sm">Propriétés</p>
                        </div>
                        <div class="bg-green-600 rounded-2xl shadow-xl p-6 text-white card-hover">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-city text-xl"></i>
                            </div>
                            <h3 class="font-bold">{{ $stats['cities'] }}</h3>
                            <p class="text-white/80 text-sm">Villes</p>
                        </div>
                    </div>
                    <div class="space-y-4 mt-8">
                        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-hover">
                            <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop" alt="Appartement moderne" class="w-full h-32 object-cover">
                            <div class="p-4">
                                <p class="font-semibold text-gray-900">Appartement F3</p>
                                <p class="text-green-600 font-bold">350 000 FCFA/mois</p>
                            </div>
                        </div>
                        <div class="bg-blue-600 rounded-2xl shadow-xl p-6 text-white card-hover">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-mobile-alt"></i>
                                <span class="font-medium">Mobile Money</span>
                            </div>
                            <p class="text-white/80 text-sm">Airtel • Moov • GT Cash</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Key Features -->
<section class="py-20 bg-white overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="inline-block px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold mb-4">Pourquoi E-Loyer ?</span>
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Simplifiez votre location immobilière</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Découvrez les fonctionnalités qui font d'E-Loyer la plateforme de référence au Gabon</p>
        </div>
        
        <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
            <!-- Feature 1: Mobile Money -->
            <div class="relative group">
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-3xl p-8 border border-green-100 hover:shadow-2xl hover:shadow-green-500/10 transition-all duration-500">
                    <!-- Illustration -->
                    <div class="relative h-48 mb-6 flex items-center justify-center">
                        <div class="absolute w-32 h-32 bg-green-200/50 rounded-full blur-2xl"></div>
                        <svg class="relative w-40 h-40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Phone -->
                            <rect x="60" y="20" width="80" height="160" rx="12" fill="#16a34a" />
                            <rect x="65" y="35" width="70" height="120" rx="4" fill="white" />
                            <circle cx="100" cy="165" r="8" fill="#15803d" />
                            <!-- Money waves -->
                            <path d="M85 75 C90 65, 110 65, 115 75" stroke="#16a34a" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <path d="M80 90 C88 77, 112 77, 120 90" stroke="#22c55e" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <path d="M75 105 C85 88, 115 88, 125 105" stroke="#4ade80" stroke-width="3" fill="none" stroke-linecap="round"/>
                            <!-- FCFA Symbol -->
                            <text x="100" y="135" text-anchor="middle" font-size="20" font-weight="bold" fill="#16a34a">FCFA</text>
                            <!-- Floating coins -->
                            <circle cx="45" cy="60" r="15" fill="#fbbf24" stroke="#f59e0b" stroke-width="2"/>
                            <text x="45" y="65" text-anchor="middle" font-size="12" fill="#92400e">₣</text>
                            <circle cx="155" cy="80" r="12" fill="#fbbf24" stroke="#f59e0b" stroke-width="2"/>
                            <text x="155" y="84" text-anchor="middle" font-size="10" fill="#92400e">₣</text>
                            <circle cx="40" cy="120" r="10" fill="#fde047" stroke="#facc15" stroke-width="2"/>
                        </svg>
                    </div>
                    
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-green-100 rounded-full text-green-700 text-sm font-medium mb-4">
                            <i class="fas fa-bolt"></i>
                            <span>Instantané</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Paiement Mobile Money</h3>
                        <p class="text-gray-600 mb-4">Payez votre loyer en quelques clics via Airtel Money, Moov Money ou GT Cash. Transactions sécurisées et instantanées.</p>
                        <div class="flex items-center justify-center gap-4 pt-4 border-t border-green-100">
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-shield-alt text-green-500"></i>
                                <span>100% sécurisé</span>
                            </div>
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-clock text-green-500"></i>
                                <span>24h/24</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature 2: Verified Listings -->
            <div class="relative group lg:-mt-8">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8 border border-blue-100 hover:shadow-2xl hover:shadow-blue-500/10 transition-all duration-500">
                    <!-- Illustration -->
                    <div class="relative h-48 mb-6 flex items-center justify-center">
                        <div class="absolute w-32 h-32 bg-blue-200/50 rounded-full blur-2xl"></div>
                        <svg class="relative w-40 h-40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- House -->
                            <path d="M100 30 L160 80 L160 170 L40 170 L40 80 Z" fill="#dbeafe" stroke="#3b82f6" stroke-width="3"/>
                            <path d="M100 30 L40 80 L160 80 Z" fill="#93c5fd"/>
                            <!-- Door -->
                            <rect x="85" y="120" width="30" height="50" rx="2" fill="#3b82f6"/>
                            <circle cx="108" cy="145" r="3" fill="#fbbf24"/>
                            <!-- Windows -->
                            <rect x="55" y="95" width="25" height="25" rx="2" fill="#bfdbfe" stroke="#3b82f6" stroke-width="2"/>
                            <rect x="120" y="95" width="25" height="25" rx="2" fill="#bfdbfe" stroke="#3b82f6" stroke-width="2"/>
                            <line x1="67.5" y1="95" x2="67.5" y2="120" stroke="#3b82f6" stroke-width="1"/>
                            <line x1="55" y1="107.5" x2="80" y2="107.5" stroke="#3b82f6" stroke-width="1"/>
                            <line x1="132.5" y1="95" x2="132.5" y2="120" stroke="#3b82f6" stroke-width="1"/>
                            <line x1="120" y1="107.5" x2="145" y2="107.5" stroke="#3b82f6" stroke-width="1"/>
                            <!-- Verified Badge -->
                            <circle cx="155" cy="55" r="25" fill="#22c55e"/>
                            <path d="M145 55 L152 62 L167 47" stroke="white" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                            <!-- Magnifying glass -->
                            <circle cx="50" cy="50" r="18" fill="none" stroke="#6366f1" stroke-width="3"/>
                            <line x1="62" y1="62" x2="75" y2="75" stroke="#6366f1" stroke-width="4" stroke-linecap="round"/>
                        </svg>
                    </div>
                    
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 rounded-full text-blue-700 text-sm font-medium mb-4">
                            <i class="fas fa-check-circle"></i>
                            <span>Vérifié</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Annonces Vérifiées</h3>
                        <p class="text-gray-600 mb-4">Toutes nos annonces sont contrôlées par notre équipe. Photos réelles, propriétaires identifiés, informations exactes.</p>
                        <div class="flex items-center justify-center gap-4 pt-4 border-t border-blue-100">
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-camera text-blue-500"></i>
                                <span>Photos réelles</span>
                            </div>
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-user-check text-blue-500"></i>
                                <span>Propriétaires vérifiés</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Feature 3: Prospector Network -->
            <div class="relative group">
                <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-3xl p-8 border border-orange-100 hover:shadow-2xl hover:shadow-orange-500/10 transition-all duration-500">
                    <!-- Illustration -->
                    <div class="relative h-48 mb-6 flex items-center justify-center">
                        <div class="absolute w-32 h-32 bg-orange-200/50 rounded-full blur-2xl"></div>
                        <svg class="relative w-40 h-40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Central person -->
                            <circle cx="100" cy="70" r="25" fill="#fed7aa" stroke="#f97316" stroke-width="3"/>
                            <circle cx="92" cy="65" r="3" fill="#1f2937"/>
                            <circle cx="108" cy="65" r="3" fill="#1f2937"/>
                            <path d="M93 78 Q100 84, 107 78" stroke="#1f2937" stroke-width="2" fill="none" stroke-linecap="round"/>
                            <path d="M70 100 Q100 85, 130 100 L140 160 L60 160 Z" fill="#f97316"/>
                            
                            <!-- Connection lines -->
                            <line x1="55" y1="90" x2="35" y2="70" stroke="#fdba74" stroke-width="2" stroke-dasharray="4"/>
                            <line x1="145" y1="90" x2="165" y2="70" stroke="#fdba74" stroke-width="2" stroke-dasharray="4"/>
                            <line x1="60" y1="140" x2="30" y2="150" stroke="#fdba74" stroke-width="2" stroke-dasharray="4"/>
                            <line x1="140" y1="140" x2="170" y2="150" stroke="#fdba74" stroke-width="2" stroke-dasharray="4"/>
                            
                            <!-- Small houses -->
                            <g transform="translate(20, 50)">
                                <path d="M15 20 L0 35 L30 35 Z" fill="#fed7aa"/>
                                <rect x="5" y="35" width="20" height="15" fill="#fdba74"/>
                            </g>
                            <g transform="translate(150, 50)">
                                <path d="M15 20 L0 35 L30 35 Z" fill="#fed7aa"/>
                                <rect x="5" y="35" width="20" height="15" fill="#fdba74"/>
                            </g>
                            <g transform="translate(10, 130)">
                                <path d="M12 15 L0 25 L24 25 Z" fill="#fed7aa"/>
                                <rect x="4" y="25" width="16" height="12" fill="#fdba74"/>
                            </g>
                            <g transform="translate(160, 130)">
                                <path d="M12 15 L0 25 L24 25 Z" fill="#fed7aa"/>
                                <rect x="4" y="25" width="16" height="12" fill="#fdba74"/>
                            </g>
                            
                            <!-- Percentage badge -->
                            <circle cx="130" cy="45" r="18" fill="#22c55e"/>
                            <text x="130" y="50" text-anchor="middle" font-size="12" font-weight="bold" fill="white">5%</text>
                        </svg>
                    </div>
                    
                    <div class="text-center">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-100 rounded-full text-orange-700 text-sm font-medium mb-4">
                            <i class="fas fa-users"></i>
                            <span>Réseau</span>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Réseau de Démarcheurs</h3>
                        <p class="text-gray-600 mb-4">Des agents locaux prospectent les meilleurs biens pour vous. Rejoignez le réseau et gagnez 5% sur chaque location.</p>
                        <div class="flex items-center justify-center gap-4 pt-4 border-t border-orange-100">
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-map-marker-alt text-orange-500"></i>
                                <span>Biens locaux</span>
                            </div>
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <i class="fas fa-coins text-orange-500"></i>
                                <span>Commissions 5%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Dernières annonces</h2>
                <p class="text-gray-600">Découvrez nos propriétés récemment ajoutées</p>
            </div>
            <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 text-green-600 font-semibold hover:gap-3 transition-all">
                Voir toutes les annonces
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($featuredProperties as $property)
                @include('components.property-card', ['property' => $property])
            @empty
                <div class="col-span-3 text-center py-12">
                    <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-home text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Aucune propriété disponible</h3>
                    <p class="text-gray-600 mb-6">Soyez le premier à publier une annonce !</p>
                    @auth
                        @if(auth()->user()->isProprietaire() || auth()->user()->isDemarcheur())
                            <a href="{{ route('properties.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                                <i class="fas fa-plus"></i>
                                Ajouter une propriété
                            </a>
                        @endif
                    @endauth
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- CTA Sections -->
<section class="py-16 lg:py-20 bg-gradient-to-br from-green-600 via-green-700 to-emerald-800">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="text-center mb-10 lg:mb-12">
            <h2 class="text-2xl md:text-3xl font-bold text-white mb-3">Rejoignez E-Loyer</h2>
            <p class="text-green-100 max-w-xl mx-auto text-sm md:text-base">Choisissez votre profil et commencez à utiliser la plateforme dès aujourd'hui</p>
        </div>
        
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
            <!-- Proprietaire CTA -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-5 lg:p-6 shadow-xl hover:shadow-2xl transition-shadow duration-300 group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-green-100/40 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-key text-xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold mb-2 text-gray-900">Vous êtes propriétaire ?</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">Publiez vos biens gratuitement et trouvez des locataires fiables.</p>
                    <a href="{{ route('register') }}?type=proprietaire" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 transition-colors">
                        Devenir propriétaire
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            
            <!-- Agence CTA -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-5 lg:p-6 shadow-xl hover:shadow-2xl transition-shadow duration-300 group">
                <div class="absolute top-0 right-0 w-32 h-32 bg-gray-100/40 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-building text-xl text-gray-900"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold mb-2 text-gray-900">Vous êtes une agence ?</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">Gérez votre portefeuille immobilier avec des outils professionnels.</p>
                    <a href="{{ route('register') }}?type=agence" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-900 text-white rounded-lg font-semibold text-sm hover:bg-gray-800 transition-colors">
                        Créer mon agence
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            
            <!-- Demarcheur CTA -->
            <div class="relative overflow-hidden bg-white rounded-2xl p-5 lg:p-6 shadow-xl hover:shadow-2xl transition-shadow duration-300 group sm:col-span-2 lg:col-span-1">
                <div class="absolute top-0 right-0 w-32 h-32 bg-blue-100/40 rounded-full -translate-y-1/2 translate-x-1/2 group-hover:scale-110 transition-transform"></div>
                <div class="relative">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-4">
                        <i class="fas fa-handshake text-xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg lg:text-xl font-bold mb-2 text-gray-900">Devenez démarcheur</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">Prospectez des biens et gagnez 5% sur chaque location réussie.</p>
                    <a href="{{ route('register') }}?type=demarcheur" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition-colors">
                        Rejoindre le réseau
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cities Section -->
<section class="py-16 lg:py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-3">Explorez nos villes</h2>
            <p class="text-gray-600 text-sm md:text-base">Trouvez votre logement dans les principales villes du Gabon</p>
        </div>
        
        <div class="grid grid-cols-3 md:grid-cols-6 gap-3 lg:gap-4">
            @php
                $cityImages = [
                    'Libreville' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=300&h=200&fit=crop',
                    'Port-Gentil' => 'https://images.unsplash.com/photo-1480714378408-67cf0d13bc1b?w=300&h=200&fit=crop',
                    'Franceville' => 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df?w=300&h=200&fit=crop',
                    'Oyem' => 'https://images.unsplash.com/photo-1514565131-fce0801e5785?w=300&h=200&fit=crop',
                    'Moanda' => 'https://images.unsplash.com/photo-1519501025264-65ba15a82390?w=300&h=200&fit=crop',
                    'Mimongo' => 'https://images.unsplash.com/photo-1518391846015-55a9cc003b25?w=300&h=200&fit=crop',
                ];
            @endphp
            
            @foreach(['Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Moanda', 'Mimongo'] as $city)
                <a href="{{ route('properties.index', ['city' => $city]) }}" class="group relative overflow-hidden rounded-xl aspect-[4/3] lg:aspect-square shadow-sm hover:shadow-lg transition-shadow">
                    <img src="{{ $cityImages[$city] ?? 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=300&h=200&fit=crop' }}" alt="{{ $city }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-3">
                        <h3 class="text-white font-bold text-sm sm:text-base text-center drop-shadow-lg">{{ $city }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
