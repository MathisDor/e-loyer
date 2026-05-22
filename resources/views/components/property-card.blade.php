@php
    $fallbacks = [
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=800&h=600&fit=crop',
        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=600&fit=crop',
    ];
    $propertyImage = $property->main_image ?? $fallbacks[$property->id % count($fallbacks)];
@endphp

<article class="property-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover group">
    <a href="{{ route('properties.show', $property) }}" class="block">
        <!-- Image -->
        <div class="relative aspect-[4/3] overflow-hidden">
            <img src="{{ $propertyImage }}" alt="{{ $property->title }}" class="property-image w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            
            <!-- Badges -->
            <div class="absolute top-3 left-3 flex gap-2">
                <span class="px-2.5 py-1 bg-white/90 backdrop-blur-sm rounded-lg text-xs font-semibold text-gray-700">
                    {{ $property->type_name }}
                </span>
                @if($property->created_at->diffInDays() < 7)
                    <span class="px-2.5 py-1 bg-yellow-400 rounded-lg text-xs font-semibold text-gray-900">
                        Nouveau
                    </span>
                @endif
            </div>
            
            <!-- Favorite Button -->
            @auth
                <button onclick="event.preventDefault(); toggleFavorite({{ $property->id }}, this)" 
                        class="absolute top-3 right-3 w-9 h-9 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center hover:bg-white transition-colors group/fav">
                    <i class="{{ $property->isFavoritedBy(auth()->user()) ? 'fas text-red-500' : 'far text-gray-400 group-hover/fav:text-red-500' }} fa-heart transition-colors"></i>
                </button>
            @endauth
            
            <!-- Price Badge -->
            <div class="absolute bottom-3 left-3">
                <span class="px-3 py-1.5 bg-green-600 text-white rounded-lg font-bold text-sm shadow-lg">
                    {{ $property->formatted_price_with_fees }}<span class="font-normal text-white/80">/mois</span>
                </span>
            </div>
        </div>
        
        <!-- Content -->
        <div class="p-4">
            <h3 class="font-bold text-gray-900 text-lg mb-1 line-clamp-1 group-hover:text-green-600 transition-colors">
                {{ $property->title }}
            </h3>
            
            <div class="flex items-center gap-1 text-gray-500 text-sm mb-3">
                <i class="fas fa-map-marker-alt text-green-600"></i>
                <span class="line-clamp-1">{{ $property->neighborhood ? $property->neighborhood . ', ' : '' }}{{ $property->city }}</span>
            </div>
            
            <!-- Features -->
            <div class="flex items-center gap-4 text-gray-600 text-sm">
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-bed text-gray-400"></i>
                    <span>{{ $property->bedrooms }} ch.</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fas fa-bath text-gray-400"></i>
                    <span>{{ $property->bathrooms }} sdb.</span>
                </div>
                @if($property->surface)
                    <div class="flex items-center gap-1.5">
                        <i class="fas fa-ruler-combined text-gray-400"></i>
                        <span>{{ number_format($property->surface, 0) }} m²</span>
                    </div>
                @endif
            </div>
            
            <!-- Rating -->
            @if($property->reviews_count > 0)
                <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
                    <div class="flex items-center gap-1">
                        <i class="fas fa-star text-yellow-400 text-sm"></i>
                        <span class="font-semibold text-gray-900">{{ number_format($property->average_rating, 1) }}</span>
                    </div>
                    <span class="text-gray-400 text-sm">({{ $property->reviews_count }} avis)</span>
                </div>
            @endif
        </div>
    </a>
</article>

@pushOnce('scripts')
<script>
function toggleFavorite(propertyId, button) {
    fetch(`/proprietes/${propertyId}/favori`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        const icon = button.querySelector('i');
        if (data.favorited) {
            icon.classList.remove('far', 'text-gray-400');
            icon.classList.add('fas', 'text-red-500');
        } else {
            icon.classList.remove('fas', 'text-red-500');
            icon.classList.add('far', 'text-gray-400');
        }
    });
}
</script>
@endpushOnce
