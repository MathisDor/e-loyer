@extends('layouts.app')

@section('title', 'Mot de passe oublié')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <div class="w-12 h-12 bg-gradient-to-br from-gabon-green to-gabon-green/80 rounded-xl flex items-center justify-center shadow-lg shadow-gabon-green/20">
                    <i class="fas fa-home text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold bg-gradient-to-r from-gabon-green to-gabon-blue bg-clip-text text-transparent">E-Loyer</span>
            </a>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Mot de passe oublié ?</h1>
            <p class="text-gray-600 text-center mb-8">
                Entrez votre email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </p>
            
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />
            
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-gabon-green/20 focus:border-gabon-green transition-all"
                               placeholder="votre@email.com">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-gabon-green to-gabon-green/90 text-white rounded-xl font-semibold hover:shadow-lg hover:shadow-gabon-green/25 transition-all duration-200">
                    Envoyer le lien de réinitialisation
                </button>
            </form>
            
            <!-- Back to login -->
            <p class="text-center text-gray-600 mt-6">
                <a href="{{ route('login') }}" class="text-gabon-green font-semibold hover:underline">
                    <i class="fas fa-arrow-left mr-2"></i>Retour à la connexion
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
