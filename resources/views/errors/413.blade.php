@extends('layouts.app')

@section('title', 'Requête trop volumineuse')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-lg w-full p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-50 flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Requête trop volumineuse</h1>
        <p class="text-gray-600 mb-4">
            Vos fichiers sont trop lourds pour être envoyés. Réduisez la taille ou le nombre d’images, puis réessayez.
        </p>
        <ul class="text-left text-sm text-gray-600 space-y-2 mb-6">
            <li>• Limitez la taille des images (ex. 1-2 Mo chacune).</li>
            <li>• Compressez ou réduisez la résolution avant l’envoi.</li>
            <li>• Évitez d’envoyer plus de 10 images à la fois.</li>
        </ul>
        <div class="flex items-center justify-center gap-3">
            <button onclick="history.back()" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:border-gray-300">
                Retour
            </button>
            <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl bg-green-600 text-white font-semibold hover:bg-green-700">
                Accueil
            </a>
        </div>
    </div>
</div>
@endsection


