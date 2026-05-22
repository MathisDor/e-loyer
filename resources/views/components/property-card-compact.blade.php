@php
    $fallbacks = [
        'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=200&fit=crop',
        'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=200&fit=crop',
        'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop',
        'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=300&h=200&fit=crop',
        'https://images.unsplash.com/photo-1560185007-cde436f6a4d0?w=300&h=200&fit=crop',
    ];
    $propertyImage = $property->main_image ?? $fallbacks[$property->id % count($fallbacks)];
@endphp

<div class="flex">
    <!-- Image -->
    <div class="relative w-24 h-24 flex-shrink-0">
        <img src="{{ $propertyImage }}" alt="{{ $property->title }}" class="w-full h-full object-cover rounded-l-xl">
        @if($property->created_at->diffInDays() < 7)
            <span class="absolute top-1 left-1 px-1.5 py-0.5 bg-yellow-400 rounded text-[10px] font-bold text-gray-900">NEW</span>
        @endif
    </div>
    
    <!-- Content -->
    <div class="flex-1 p-3 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded text-xs font-semibold">
                {{ $property->type_name }}
            </span>
            @auth
                <button onclick="event.preventDefault(); event.stopPropagation(); toggleFavorite({{ $property->id }}, this)">
                    <i class="{{ $property->isFavoritedBy(auth()->user()) ? 'fas text-red-500' : 'far text-gray-300' }} fa-heart text-sm"></i>
                </button>
            @endauth
        </div>
        
        <h3 class="font-bold text-gray-900 text-sm mt-1 line-clamp-1">{{ $property->title }}</h3>
        
        <p class="text-gray-500 text-xs flex items-center gap-1 mt-0.5">
            <i class="fas fa-map-marker-alt text-green-600 text-[10px]"></i>
            <span class="truncate">{{ $property->city }}</span>
        </p>
        
        <div class="flex items-center justify-between mt-2">
            <div class="flex items-center gap-2 text-gray-500 text-xs">
                <span><i class="fas fa-bed mr-0.5"></i>{{ $property->bedrooms }}</span>
                <span><i class="fas fa-bath mr-0.5"></i>{{ $property->bathrooms }}</span>
            </div>
            <p class="font-bold text-green-600 text-sm">{{ $property->formatted_price_with_fees }}</p>
        </div>
    </div>
</div>


