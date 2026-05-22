@extends('layouts.app')

@section('title', 'Modifier la propriété')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-5xl mx-auto px-4">
        <div class="mb-6 flex items-center gap-2">
            <a href="{{ route('properties.show', $property) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 lg:p-8">
            <div class="mb-6">
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Modifier la propriété</h1>
                <p class="text-gray-600">Mettez à jour les informations de votre annonce.</p>
            </div>

            <form action="{{ route('properties.update', $property) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Titre *</span>
                            <input type="text" name="title" value="{{ old('title', $property->title) }}" required
                                   class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Type *</span>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                @foreach($types as $key => $label)
                                    <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer hover:border-green-300">
                                        <input type="radio" name="type" value="{{ $key }}" class="text-green-600"
                                               {{ old('type', $property->type) === $key ? 'checked' : '' }} required>
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Description *</span>
                            <textarea name="description" rows="4" required
                                      class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">{{ old('description', $property->description) }}</textarea>
                        </label>

                        <div class="grid grid-cols-3 gap-3">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Chambres *</span>
                                <input type="number" name="bedrooms" min="0" max="20" required
                                       value="{{ old('bedrooms', $property->bedrooms) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Salles de bain *</span>
                                <input type="number" name="bathrooms" min="0" max="10" required
                                       value="{{ old('bathrooms', $property->bathrooms) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Surface (m²)</span>
                                <input type="number" name="surface" min="1"
                                       value="{{ old('surface', $property->surface) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                        </div>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Loyer mensuel (FCFA) *</span>
                            <input type="number" name="monthly_price" min="10000" required
                                   value="{{ old('monthly_price', $property->monthly_price) }}"
                                   class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </label>

                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Caution (FCFA)</span>
                            <input type="number" name="deposit" min="0"
                                   value="{{ old('deposit', $property->deposit) }}"
                                   class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </label>
                    </div>

                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-sm font-semibold text-gray-700">Adresse *</span>
                            <input type="text" name="address" value="{{ old('address', $property->address) }}" required
                                   class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                        </label>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Ville *</span>
                                <select name="city" class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                                    @foreach($cities as $city)
                                        <option value="{{ $city }}" {{ old('city', $property->city) === $city ? 'selected' : '' }}>{{ $city }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Quartier</span>
                                <input type="text" name="neighborhood" value="{{ old('neighborhood', $property->neighborhood) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Latitude</span>
                                <input type="number" step="0.000001" name="latitude" value="{{ old('latitude', $property->latitude) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                            <label class="block">
                                <span class="text-sm font-semibold text-gray-700">Longitude</span>
                                <input type="number" step="0.000001" name="longitude" value="{{ old('longitude', $property->longitude) }}"
                                       class="mt-1 w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                            </label>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Équipements</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($amenities as $key => $label)
                                    <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-xl cursor-pointer hover:border-green-300">
                                        <input type="checkbox" name="amenities[]" value="{{ $key }}" class="text-green-600"
                                               {{ in_array($key, old('amenities', $property->amenities ?? [])) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-sm font-semibold text-gray-700">Photos existantes</p>
                            @if($property->images && count($property->images) > 0)
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    @foreach($property->images as $img)
                                        <label class="relative block">
                                            <img src="{{ asset('storage/'.$img) }}" alt="Image" class="w-full h-32 object-cover rounded-xl border border-gray-100">
                                            <span class="absolute top-2 right-2 bg-white rounded-lg px-2 py-1 text-xs font-semibold text-gray-700 border border-gray-200">Garder</span>
                                            <input type="checkbox" name="remove_images[]" value="{{ $img }}" class="absolute inset-0 opacity-0 peer">
                                            <span class="absolute top-2 right-2 bg-red-600 text-white text-xs px-2 py-1 rounded-lg opacity-0 peer-checked:opacity-100 transition">Supprimer</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">Aucune image disponible.</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Ajouter de nouvelles photos (max 10)</label>
                            <input type="file" name="new_images[]" accept="image/*" multiple
                                   class="w-full border border-dashed border-gray-300 rounded-xl p-4 text-sm text-gray-600 hover:border-green-400">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('properties.show', $property) }}" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:border-gray-300">Annuler</a>
                    <button type="submit" class="px-5 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


