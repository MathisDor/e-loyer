@extends('layouts.app')

@section('title', 'Mon profil')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-8">
    <div class="max-w-5xl mx-auto px-4">
        
        <!-- Back Button -->
        <div class="mb-6">
            <a href="javascript:history.back()" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour</span>
            </a>
        </div>
        
        <!-- Profile Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <div class="relative">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-2xl object-cover border-4 border-white shadow-lg">
                    @if($user->is_verified)
                        <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center shadow">
                            <i class="fas fa-check text-white text-sm"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1 text-center md:text-left">
                    <div class="flex flex-col md:flex-row md:items-center gap-2 mb-1">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <!-- User Type Badge -->
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold
                            {{ $user->user_type === 'locataire' ? 'bg-blue-100 text-blue-700' : '' }}
                            {{ $user->user_type === 'proprietaire' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $user->user_type === 'admin' ? 'bg-red-100 text-red-700' : '' }}">
                            <i class="fas {{ $user->user_type === 'locataire' ? 'fa-user' : ($user->user_type === 'proprietaire' ? 'fa-key' : 'fa-shield-alt') }}"></i>
                            {{ ucfirst($user->user_type) }}
                        </span>
                    </div>
                    <div class="flex flex-wrap justify-center md:justify-start gap-4 mt-3 text-sm text-gray-500">
                        <span><i class="fas fa-calendar mr-1"></i>Membre depuis {{ $user->created_at->format('M Y') }}</span>
                        @if($user->city)
                            <span><i class="fas fa-map-marker-alt mr-1"></i>{{ $user->city }}</span>
                        @endif
                        @if($user->email_verified_at)
                            <span class="text-green-600"><i class="fas fa-envelope-circle-check mr-1"></i>Email vérifié</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('profile.edit') }}" class="px-5 py-2.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                    <i class="fas fa-edit mr-2"></i>Modifier
                </a>
            </div>
        </div>
        
        <!-- Verification Status Banner -->
        @if(!$user->is_verified)
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-yellow-800">Profil non vérifié</h3>
                        <p class="text-yellow-700 text-sm mt-1">Complétez votre vérification d'identité pour accéder à toutes les fonctionnalités et gagner la confiance des autres utilisateurs.</p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-shield-alt text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-green-800">Profil vérifié</h3>
                        <p class="text-green-700 text-sm">Votre identité a été vérifiée avec succès.</p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Personal Information -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-user-circle text-gray-400"></i>
                            Informations personnelles
                        </h2>
                    </div>
                    <dl class="grid sm:grid-cols-2 gap-4">
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Email</dt>
                            <dd class="font-medium text-gray-900 mt-1">{{ $user->email }}</dd>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Téléphone</dt>
                            <dd class="font-medium text-gray-900 mt-1">{{ $user->phone ?? 'Non renseigné' }}</dd>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">WhatsApp</dt>
                            <dd class="font-medium text-gray-900 mt-1">{{ $user->whatsapp ?? 'Non renseigné' }}</dd>
                        </div>
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <dt class="text-xs text-gray-500 uppercase tracking-wider">Adresse</dt>
                            <dd class="font-medium text-gray-900 mt-1">{{ $user->address ?? 'Non renseigné' }}</dd>
                        </div>
                    </dl>
                    
                    @if($user->bio)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">À propos</h3>
                            <p class="text-gray-600">{{ $user->bio }}</p>
                        </div>
                    @endif
                </div>
                
                <!-- Identity Verification Section -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2">
                            <i class="fas fa-id-card text-gray-400"></i>
                            Vérification d'identité
                        </h2>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $user->id_card ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $user->id_card ? 'Envoyé' : 'En attente' }}
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- ID Card -->
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->id_card ? 'bg-green-50 border-green-200' : '' }}">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->id_card ? 'bg-green-100' : 'bg-gray-100' }}">
                                    <i class="fas fa-id-card {{ $user->id_card ? 'text-green-600' : 'text-gray-400' }}"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900">Pièce d'identité</p>
                                    <p class="text-sm text-gray-500">CNI, Passeport ou Permis de conduire</p>
                                </div>
                            </div>
                            @if($user->id_card)
                                <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                    <i class="fas fa-check-circle"></i> Envoyé
                                </span>
                            @else
                                <form action="{{ route('profile.id-card') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <label class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium text-sm cursor-pointer hover:bg-green-700 transition-colors">
                                        <i class="fas fa-upload mr-1"></i> Téléverser
                                        <input type="file" name="id_card" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                    </label>
                                </form>
                            @endif
                        </div>
                        
                        @if($user->user_type === 'locataire')
                            <!-- Pay Slips - For Tenants Only -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->pay_slip ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->pay_slip ? 'bg-green-100' : 'bg-gray-100' }}">
                                        <i class="fas fa-file-invoice-dollar {{ $user->pay_slip ? 'text-green-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Bulletin de salaire</p>
                                        <p class="text-sm text-gray-500">3 derniers mois recommandés</p>
                                    </div>
                                </div>
                                @if($user->pay_slip)
                                    <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                        <i class="fas fa-check-circle"></i> Envoyé
                                    </span>
                                @else
                                    <form action="{{ route('profile.pay-slip') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm cursor-pointer hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-upload mr-1"></i> Téléverser
                                            <input type="file" name="pay_slip" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                            
                            <!-- Employment Contract -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->employment_contract ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->employment_contract ? 'bg-green-100' : 'bg-gray-100' }}">
                                        <i class="fas fa-file-contract {{ $user->employment_contract ? 'text-green-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Contrat de travail</p>
                                        <p class="text-sm text-gray-500">Attestation d'emploi ou contrat</p>
                                    </div>
                                </div>
                                @if($user->employment_contract)
                                    <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                        <i class="fas fa-check-circle"></i> Envoyé
                                    </span>
                                @else
                                    <form action="{{ route('profile.employment-contract') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm cursor-pointer hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-upload mr-1"></i> Téléverser
                                            <input type="file" name="employment_contract" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                            
                            <!-- Proof of Address -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->proof_of_address ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->proof_of_address ? 'bg-green-100' : 'bg-gray-100' }}">
                                        <i class="fas fa-home {{ $user->proof_of_address ? 'text-green-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Justificatif de domicile</p>
                                        <p class="text-sm text-gray-500">Facture ou quittance récente</p>
                                    </div>
                                </div>
                                @if($user->proof_of_address)
                                    <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                        <i class="fas fa-check-circle"></i> Envoyé
                                    </span>
                                @else
                                    <form action="{{ route('profile.proof-of-address') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium text-sm cursor-pointer hover:bg-blue-700 transition-colors">
                                            <i class="fas fa-upload mr-1"></i> Téléverser
                                            <input type="file" name="proof_of_address" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                            
                            <!-- Bank Statement -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->bank_statement ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->bank_statement ? 'bg-green-100' : 'bg-gray-100' }}">
                                        <i class="fas fa-university {{ $user->bank_statement ? 'text-green-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Relevé bancaire</p>
                                        <p class="text-sm text-gray-500">3 derniers mois (optionnel)</p>
                                    </div>
                                </div>
                                @if($user->bank_statement)
                                    <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                        <i class="fas fa-check-circle"></i> Envoyé
                                    </span>
                                @else
                                    <form action="{{ route('profile.bank-statement') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-medium text-sm cursor-pointer hover:bg-gray-300 transition-colors">
                                            <i class="fas fa-upload mr-1"></i> Téléverser
                                            <input type="file" name="bank_statement" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                        @endif
                        
                        @if($user->user_type === 'proprietaire')
                            <!-- Property Title -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-xl {{ $user->property_title ? 'bg-green-50 border-green-200' : '' }}">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $user->property_title ? 'bg-green-100' : 'bg-gray-100' }}">
                                        <i class="fas fa-file-alt {{ $user->property_title ? 'text-green-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">Titre de propriété</p>
                                        <p class="text-sm text-gray-500">Document prouvant la propriété</p>
                                    </div>
                                </div>
                                @if($user->property_title)
                                    <span class="flex items-center gap-1 text-green-600 text-sm font-medium">
                                        <i class="fas fa-check-circle"></i> Envoyé
                                    </span>
                                @else
                                    <form action="{{ route('profile.property-title') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <label class="px-4 py-2 bg-green-600 text-white rounded-lg font-medium text-sm cursor-pointer hover:bg-green-700 transition-colors">
                                            <i class="fas fa-upload mr-1"></i> Téléverser
                                            <input type="file" name="property_title" class="hidden" accept=".pdf,.jpg,.jpeg,.png" onchange="this.form.submit()">
                                        </label>
                                    </form>
                                @endif
                            </div>
                        @endif
                        

                    </div>
                    
                    <!-- Verification Progress -->
                    @php
                        $totalDocs = 1; // ID Card is mandatory for all
                        $uploadedDocs = $user->id_card ? 1 : 0;
                        
                        if($user->user_type === 'locataire') {
                            $totalDocs = 5;
                            $uploadedDocs += ($user->pay_slip ? 1 : 0) + ($user->employment_contract ? 1 : 0) + ($user->proof_of_address ? 1 : 0) + ($user->bank_statement ? 1 : 0);
                        } elseif($user->user_type === 'proprietaire') {
                            $totalDocs = 2;
                            $uploadedDocs += $user->property_title ? 1 : 0;
                        }
                        
                        $progress = $totalDocs > 0 ? round(($uploadedDocs / $totalDocs) * 100) : 0;
                    @endphp
                    
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Progression de la vérification</span>
                            <span class="text-sm font-bold {{ $progress == 100 ? 'text-green-600' : 'text-gray-600' }}">{{ $progress }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500 {{ $progress == 100 ? 'bg-green-500' : 'bg-blue-500' }}" style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ $uploadedDocs }}/{{ $totalDocs }} documents téléversés</p>
                    </div>
                </div>
                
                <!-- Reviews Received -->
                @if($user->reviewsReceived && $user->reviewsReceived->count() > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 flex items-center gap-2 mb-4">
                            <i class="fas fa-star text-yellow-400"></i>
                            Avis reçus ({{ $user->reviewsReceived->count() }})
                        </h2>
                        <div class="space-y-4">
                            @foreach($user->reviewsReceived->take(5) as $review)
                                <div class="flex gap-4 pb-4 border-b border-gray-100 last:border-0 last:pb-0">
                                    <img src="{{ $review->reviewer->avatar_url }}" alt="{{ $review->reviewer->name }}" class="w-10 h-10 rounded-full object-cover">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="font-medium text-gray-900">{{ $review->reviewer->name }}</span>
                                            <span class="text-gray-400 text-xs">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="flex gap-0.5 mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                        </div>
                                        <p class="text-gray-600 text-sm">{{ $review->comment }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Rating Card -->
                @if($user->reviews_count > 0)
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
                        <div class="text-4xl font-bold text-gray-900">{{ number_format($user->average_rating, 1) }}</div>
                        <div class="flex justify-center gap-0.5 my-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-lg {{ $i <= round($user->average_rating) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                        <p class="text-gray-500 text-sm">{{ $user->reviews_count }} avis</p>
                    </div>
                @endif
                
                <!-- Stats Card based on user type -->
                @if($user->user_type === 'proprietaire')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Statistiques</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Propriétés</span>
                                <span class="font-bold text-gray-900">{{ $user->properties()->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Locations actives</span>
                                <span class="font-bold text-green-600">{{ $user->properties()->where('status', 'loue')->count() }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                

                @if($user->user_type === 'locataire')
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                        <h3 class="font-bold text-gray-900 mb-4">Mon activité</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Réservations</span>
                                <span class="font-bold text-gray-900">{{ $user->bookings()->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Locations actives</span>
                                <span class="font-bold text-green-600">{{ $user->bookings()->whereIn('status', ['active', 'payee'])->count() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Favoris</span>
                                <span class="font-bold text-red-500">{{ $user->favoriteProperties()->count() }}</span>
                            </div>
                        </div>
                    </div>
                @endif
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Actions rapides</h3>
                    <div class="space-y-2">
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user-edit text-gray-400 w-5"></i>
                            <span class="text-gray-700">Modifier le profil</span>
                        </a>
                        <a href="{{ route('profile.password') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <i class="fas fa-lock text-gray-400 w-5"></i>
                            <span class="text-gray-700">Changer mot de passe</span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                            <i class="fas fa-tachometer-alt text-gray-400 w-5"></i>
                            <span class="text-gray-700">Tableau de bord</span>
                        </a>
                        @if($user->user_type === 'locataire')
                            <a href="{{ route('profile.favorites') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 transition-colors">
                                <i class="fas fa-heart text-red-400 w-5"></i>
                                <span class="text-gray-700">Mes favoris</span>
                            </a>
                        @endif
                        @if($user->user_type === 'proprietaire')
                            <a href="{{ route('properties.create') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-green-50 text-green-600 transition-colors">
                                <i class="fas fa-plus w-5"></i>
                                <span class="font-medium">Ajouter un bien</span>
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Security -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4">Sécurité</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-envelope text-gray-400"></i>
                                <span class="text-sm text-gray-600">Email vérifié</span>
                            </div>
                            @if($user->email_verified_at)
                                <i class="fas fa-check-circle text-green-500"></i>
                            @else
                                <span class="text-xs text-yellow-600 font-medium">En attente</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-phone text-gray-400"></i>
                                <span class="text-sm text-gray-600">Téléphone vérifié</span>
                            </div>
                            @if($user->phone_verified_at)
                                <i class="fas fa-check-circle text-green-500"></i>
                            @else
                                <span class="text-xs text-yellow-600 font-medium">En attente</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-id-card text-gray-400"></i>
                                <span class="text-sm text-gray-600">Identité vérifiée</span>
                            </div>
                            @if($user->is_verified)
                                <i class="fas fa-check-circle text-green-500"></i>
                            @else
                                <span class="text-xs text-yellow-600 font-medium">En attente</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
