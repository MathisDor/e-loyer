@extends('layouts.app')

@section('title', $title ?? 'Visites')

@section('content')
<div class="bg-gray-50 min-h-screen py-8 lg:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-2 mb-6">
            <div class="flex items-center gap-3">
                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                    @switch($role)
                        @case('proprietaire') Visites propriétaire @break

                        @default Mes visites
                    @endswitch
                </span>
                <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">{{ $title ?? 'Visites' }}</h1>
            </div>
            @if(!empty($description))
                <p class="text-gray-600">{{ $description }}</p>
            @endif
        </div>

        <!-- Filtres -->
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-2 flex-wrap">
                @foreach($tabs as $tab)
                    <a href="{{ request()->fullUrlWithQuery(['status' => $tab['key']]) }}"
                       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold border transition-colors
                       {{ $activeStatus === $tab['key'] ? 'bg-green-100 text-green-800 border-green-200' : 'bg-gray-50 text-gray-700 border-gray-200 hover:border-green-200' }}">
                        {{ $tab['label'] }}
                        <span class="text-xs px-2 py-0.5 rounded-full bg-white border border-gray-200">{{ $tab['count'] }}</span>
                    </a>
                @endforeach
            </div>
            <form class="w-full md:w-auto" method="GET">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un bien, une ville..."
                           class="w-full md:w-72 pl-10 pr-3 py-2.5 rounded-xl border border-gray-200 bg-gray-50 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 text-sm">
                </div>
            </form>
        </div>

        @if($visits->count() === 0)
            <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-10 text-center">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-home text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucune visite</h3>
                <p class="text-gray-600 text-sm">Aucune visite trouvée pour ce filtre.</p>
            </div>
        @else
            <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($visits as $visit)
                    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-4 flex flex-col gap-4">
                        <!-- En-tête -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase">Visite</p>
                                <p class="font-bold text-gray-900 line-clamp-2">{{ $visit->property->title }}</p>
                                <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                                    <i class="fas fa-map-marker-alt text-green-600"></i>
                                    {{ $visit->property->full_address ?? ($visit->property->city ?? '') }}
                                </p>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                @if($visit->status === 'reservee') bg-blue-100 text-blue-700
                                @elseif($visit->status === 'en_cours') bg-yellow-100 text-yellow-700
                                @elseif($visit->status === 'acceptee') bg-green-100 text-green-700
                                @elseif($visit->status === 'refusee') bg-red-100 text-red-700
                                @elseif($visit->status === 'terminee') bg-emerald-100 text-emerald-700
                                @else bg-gray-100 text-gray-700
                                @endif">
                                {{ $visit->status_name }}
                            </span>
                        </div>

                        <!-- Infos -->
                        <div class="grid grid-cols-2 gap-3 text-sm text-gray-700">
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <p class="text-xs text-gray-500">Date & heure</p>
                                <p class="font-semibold">{{ $visit->scheduled_at->format('d/m/Y') }}</p>
                                <p class="text-gray-500 text-xs">{{ $visit->scheduled_at->format('H:i') }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100">
                                <p class="text-xs text-gray-500">Montant</p>
                                <p class="font-semibold">{{ $visit->formatted_total_amount }}</p>
                                <p class="text-gray-500 text-xs">{{ $visit->is_paid ? 'Payé' : 'En attente' }}</p>
                            </div>
                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-100 col-span-2">
                                <p class="text-xs text-gray-500">Parties</p>
                                <p class="text-gray-700">
                                    <i class="fas fa-user text-green-600 mr-1"></i>
                                    Locataire : {{ $visit->tenant->name ?? '—' }}
                                </p>
                                <p class="text-gray-700">
                                    <i class="fas fa-user-tie text-gray-500 mr-1"></i>
                                    Propriétaire : {{ $visit->owner->name ?? '—' }}
                                </p>
                                @if($visit->assignedUser)
                                    <p class="text-gray-700">
                                        <i class="fas fa-briefcase text-blue-600 mr-1"></i>
                                        Assigné : {{ $visit->assignedUser->name }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('visits.show', $visit) }}" class="px-3 py-2 rounded-xl border border-gray-200 text-sm font-semibold text-gray-700 hover:border-green-300 hover:text-green-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i> Détails
                            </a>
                        </div>

                        @if($user->user_type === 'proprietaire')
                            <form method="POST" action="{{ route('visits.status', $visit) }}" class="flex flex-col gap-2">
                                @csrf
                                <div class="grid grid-cols-2 gap-2">
                                    <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20">
                                        <option value="reservee" {{ $visit->status === 'reservee' ? 'selected' : '' }}>Réservée</option>
                                        <option value="en_cours" {{ $visit->status === 'en_cours' ? 'selected' : '' }}>En cours</option>
                                        <option value="terminee" {{ $visit->status === 'terminee' ? 'selected' : '' }}>Terminée</option>
                                        <option value="acceptee" {{ $visit->status === 'acceptee' ? 'selected' : '' }}>Acceptée</option>
                                        <option value="refusee" {{ $visit->status === 'refusee' ? 'selected' : '' }}>Refusée</option>
                                        <option value="annulee" {{ $visit->status === 'annulee' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                    <input type="datetime-local" name="scheduled_at"
                                           value="{{ $visit->scheduled_at?->format('Y-m-d\TH:i') }}"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                                           placeholder="Replanifier">
                                </div>
                                <textarea name="notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-500/20" placeholder="Notes / motif (optionnel)">{{ old('notes') }}</textarea>
                                <button type="submit" class="w-full px-3 py-2 bg-green-600 text-white rounded-xl text-sm font-semibold hover:bg-green-700 transition-colors">
                                    Mettre à jour
                                </button>
                            </form>

                            @if($visit->status === 'reservee' && $visit->is_paid)
                                <form method="POST" action="{{ route('visits.start', $visit) }}" class="mt-2">
                                    @csrf
                                    <button type="submit" class="w-full px-3 py-2 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700 transition-colors">
                                        Démarrer
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

