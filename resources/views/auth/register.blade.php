@extends('layouts.app')

@section('title', 'Inscription')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-6 sm:py-12 px-3 sm:px-4">
    <div class="w-full max-w-xl">
        <!-- Card -->
        <div class="bg-white rounded-2xl sm:rounded-3xl shadow-xl border border-gray-100 p-5 sm:p-8">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 text-center mb-1 sm:mb-2">Créer un compte</h1>
            <p class="text-gray-500 text-center text-sm sm:text-base mb-5 sm:mb-8">Rejoignez E-Loyer — la plateforme de location au Gabon</p>
            
            <form method="POST" action="{{ route('register') }}" class="space-y-4 sm:space-y-5" x-data="{ userType: '{{ old('user_type', request('type', 'locataire')) }}' }">
                @csrf

                <!-- User Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Je suis...</label>
                    <div class="grid grid-cols-2 gap-3">

                        <!-- Locataire -->
                        <label class="relative cursor-pointer" @click="userType = 'locataire'">
                            <input type="radio" name="user_type" value="locataire" class="sr-only" :checked="userType === 'locataire'">
                            <div class="p-3 sm:p-4 border-2 rounded-xl text-center transition-all duration-200 hover:border-green-300 hover:bg-green-50/50 hover:shadow-md"
                                 :class="userType === 'locataire' ? 'border-green-500 bg-gradient-to-br from-green-50 to-emerald-100 shadow-lg shadow-green-500/20 scale-[1.02]' : 'border-gray-200'">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition-all"
                                     :class="userType === 'locataire' ? 'bg-green-500' : 'bg-gray-100'">
                                    <i class="fas fa-user text-base sm:text-lg transition-colors"
                                       :class="userType === 'locataire' ? 'text-white' : 'text-gray-400'"></i>
                                </div>
                                <p class="font-semibold text-sm transition-colors"
                                   :class="userType === 'locataire' ? 'text-green-700' : 'text-gray-700'">Locataire</p>
                                <p class="text-[10px] text-gray-500 mt-0.5 hidden sm:block">Je cherche un logement</p>
                            </div>
                        </label>

                        <!-- Propriétaire -->
                        <label class="relative cursor-pointer" @click="userType = 'proprietaire'">
                            <input type="radio" name="user_type" value="proprietaire" class="sr-only" :checked="userType === 'proprietaire'">
                            <div class="p-3 sm:p-4 border-2 rounded-xl text-center transition-all duration-200 hover:border-yellow-300 hover:bg-yellow-50/50 hover:shadow-md"
                                 :class="userType === 'proprietaire' ? 'border-yellow-500 bg-gradient-to-br from-yellow-50 to-amber-100 shadow-lg shadow-yellow-500/20 scale-[1.02]' : 'border-gray-200'">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition-all"
                                     :class="userType === 'proprietaire' ? 'bg-yellow-500' : 'bg-gray-100'">
                                    <i class="fas fa-key text-base sm:text-lg transition-colors"
                                       :class="userType === 'proprietaire' ? 'text-white' : 'text-gray-400'"></i>
                                </div>
                                <p class="font-semibold text-sm transition-colors"
                                   :class="userType === 'proprietaire' ? 'text-yellow-700' : 'text-gray-700'">Propriétaire</p>
                                <p class="text-[10px] text-gray-500 mt-0.5 hidden sm:block">Je loue mes biens</p>
                            </div>
                        </label>

                        <!-- Démarcheur -->
                        <label class="relative cursor-pointer" @click="userType = 'demarcheur'">
                            <input type="radio" name="user_type" value="demarcheur" class="sr-only" :checked="userType === 'demarcheur'">
                            <div class="p-3 sm:p-4 border-2 rounded-xl text-center transition-all duration-200 hover:border-orange-300 hover:bg-orange-50/50 hover:shadow-md"
                                 :class="userType === 'demarcheur' ? 'border-orange-500 bg-gradient-to-br from-orange-50 to-amber-100 shadow-lg shadow-orange-500/20 scale-[1.02]' : 'border-gray-200'">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition-all"
                                     :class="userType === 'demarcheur' ? 'bg-orange-500' : 'bg-gray-100'">
                                    <i class="fas fa-handshake text-base sm:text-lg transition-colors"
                                       :class="userType === 'demarcheur' ? 'text-white' : 'text-gray-400'"></i>
                                </div>
                                <p class="font-semibold text-sm transition-colors"
                                   :class="userType === 'demarcheur' ? 'text-orange-700' : 'text-gray-700'">Démarcheur</p>
                                <p class="text-[10px] text-gray-500 mt-0.5 hidden sm:block">Je guide les visites</p>
                            </div>
                        </label>

                        <!-- Agence -->
                        <label class="relative cursor-pointer" @click="userType = 'agence'">
                            <input type="radio" name="user_type" value="agence" class="sr-only" :checked="userType === 'agence'">
                            <div class="p-3 sm:p-4 border-2 rounded-xl text-center transition-all duration-200 hover:border-indigo-300 hover:bg-indigo-50/50 hover:shadow-md"
                                 :class="userType === 'agence' ? 'border-indigo-500 bg-gradient-to-br from-indigo-50 to-blue-100 shadow-lg shadow-indigo-500/20 scale-[1.02]' : 'border-gray-200'">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition-all"
                                     :class="userType === 'agence' ? 'bg-indigo-500' : 'bg-gray-100'">
                                    <i class="fas fa-briefcase text-base sm:text-lg transition-colors"
                                       :class="userType === 'agence' ? 'text-white' : 'text-gray-400'"></i>
                                </div>
                                <p class="font-semibold text-sm transition-colors"
                                   :class="userType === 'agence' ? 'text-indigo-700' : 'text-gray-700'">Agence</p>
                                <p class="text-[10px] text-gray-500 mt-0.5 hidden sm:block">Agence immobilière</p>
                            </div>
                        </label>

                    </div>
                    @error('user_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nom de l'agence (affiché uniquement pour agence) -->
                <div x-show="userType === 'agence'" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    <label for="agency_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'agence <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400">
                            <i class="fas fa-briefcase"></i>
                        </span>
                        <input type="text" id="agency_name" name="agency_name" value="{{ old('agency_name') }}"
                               :required="userType === 'agence'"
                               class="w-full pl-11 pr-4 py-3 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all bg-indigo-50/30"
                               placeholder="Ex: Immobilier Gabon SARL">
                    </div>
                    @error('agency_name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nom complet</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="Jean Dupont">
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="votre@email.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Phone -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Téléphone</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-phone"></i>
                            </span>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                                   class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                   placeholder="+241 XX XX XX XX">
                        </div>
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="whatsapp" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp (optionnel)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}"
                                   class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                                   placeholder="+241 XX XX XX XX">
                        </div>
                    </div>
                </div>
                
                <!-- City -->
                <div>
                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Ville</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-city"></i>
                        </span>
                        <select id="city" name="city" class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all appearance-none">
                            <option value="">Sélectionnez votre ville</option>
                            @foreach(['Libreville', 'Port-Gentil', 'Franceville', 'Oyem', 'Moanda', 'Mouila', 'Lambaréné', 'Tchibanga', 'Koulamoutou', 'Makokou'] as $city)
                                <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Mot de passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="new-password"
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="Min. 8 caractères">
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password Confirmation -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmer le mot de passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password"
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="Confirmez votre mot de passe">
                    </div>
                    @error('password_confirmation')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit -->
                <button type="submit"
                        class="w-full py-4 border-2 rounded-xl font-bold text-base flex items-center justify-center gap-2 transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98]"
                        :class="{
                            'border-green-500 text-green-600 bg-white hover:bg-green-50 shadow-lg shadow-green-500/20': userType === 'locataire',
                            'border-yellow-500 text-yellow-700 bg-white hover:bg-yellow-50 shadow-lg shadow-yellow-500/20': userType === 'proprietaire',
                            'border-orange-500 text-orange-600 bg-white hover:bg-orange-50 shadow-lg shadow-orange-500/20': userType === 'demarcheur',
                            'border-indigo-500 text-indigo-600 bg-white hover:bg-indigo-50 shadow-lg shadow-indigo-500/20': userType === 'agence',
                        }">
                    <i class="fas fa-user-plus"></i>
                    <span x-text="{
                        locataire: 'Créer mon compte locataire',
                        proprietaire: 'Créer mon compte propriétaire',
                        demarcheur: 'Créer mon compte démarcheur',
                        agence: 'Créer le compte agence',
                    }[userType] || 'S\'inscrire'"></span>
                </button>
            </form>
            
            <!-- Login Link -->
            <p class="text-center text-gray-600 mt-6">
                Déjà un compte ?
                <a href="{{ route('login') }}" class="text-green-600 font-semibold hover:underline">Se connecter</a>
            </p>
        </div>
    </div>
</div>
@endsection
