@extends('layouts.app')

@section('title', 'Connexion')

@section('content')
<div class="min-h-[calc(100vh-4rem)] flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2">
                <img src="{{ asset('img/eloyer-logo.png') }}" alt="E-Loyer" class="h-14 w-auto">
                <span class="text-2xl font-bold text-green-600">E-Loyer</span>
            </a>
        </div>
        
        <!-- Card -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8">
            <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Bon retour !</h1>
            <p class="text-gray-600 text-center mb-8">Connectez-vous à votre compte</p>
            
            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                    {{ session('status') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Adresse email</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                               class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="votre@email.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password -->
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-green-600 hover:underline">Mot de passe oublié ?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required autocomplete="current-password"
                               class="w-full pl-11 pr-12 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-all"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i id="password-toggle-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Remember -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Se souvenir de moi</label>
                </div>
                
                <!-- Submit -->
                <button type="submit" class="w-full py-3.5 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 hover:shadow-lg hover:shadow-green-600/25 transition-all duration-200 hover:-translate-y-0.5">
                    Se connecter
                </button>
            </form>
            
            <!-- Divider -->
            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500">ou</span>
                </div>
            </div>
            
            <!-- Register Link -->
            <p class="text-center text-gray-600">
                Pas encore de compte ?
                <a href="{{ route('register') }}" class="text-green-600 font-semibold hover:underline">S'inscrire</a>
            </p>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    const icon = document.getElementById('password-toggle-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection
