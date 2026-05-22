@extends('layouts.app')

@section('title', 'Mes paiements')

@section('content')
<div class="bg-gray-50 min-h-screen py-6 lg:py-10">
    <div class="max-w-7xl mx-auto px-4">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 mb-2">Mes paiements</h1>
            <p class="text-gray-600">Historique de tous vos paiements</p>
        </div>
        
        <!-- Stats -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total payé</p>
                        <p class="text-2xl font-bold text-green-600">{{ number_format($stats['total_paid'], 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">En attente</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending'], 0, ',', ' ') }} FCFA</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payments List -->
        @if($payments->count() > 0)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-900">Historique des paiements</h2>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @foreach($payments as $payment)
                        @php
                            $property = $payment->booking?->property ?? $payment->visit?->property ?? $payment->contract?->property;
                            $paymentType = match($payment->payment_type) {
                                'visite' => 'Visite',
                                'premier_versement' => 'Premier versement',
                                'mensuel' => 'Loyer mensuel',
                                'initial' => 'Paiement initial',
                                'caution' => 'Caution',
                                default => 'Paiement'
                            };
                        @endphp
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h3 class="font-bold text-gray-900">{{ $property?->title ?? 'N/A' }}</h3>
                                        <span class="px-2 py-1 rounded text-xs font-semibold
                                            {{ $payment->status === 'confirme' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $payment->status === 'confirme' ? 'Confirmé' : 'En attente' }}
                                        </span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs font-semibold">
                                            {{ $paymentType }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-1">{{ $payment->description }}</p>
                                    <p class="text-xs text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        {{ $payment->created_at->format('d/m/Y à H:i') }}
                                        @if($payment->payment_method)
                                            · <i class="fas fa-mobile-alt mr-1"></i>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        @endif
                                    </p>
                                </div>
                                
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-green-600">{{ number_format($payment->amount, 0, ',', ' ') }} FCFA</p>
                                    @if($payment->reference)
                                        <p class="text-xs text-gray-500 mt-1">Ref: {{ $payment->reference }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $payments->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Aucun paiement</h3>
                <p class="text-gray-600 mb-6">Vous n'avez pas encore effectué de paiement.</p>
                <a href="{{ route('properties.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-xl font-semibold hover:bg-green-700 transition-colors">
                    <i class="fas fa-search"></i>
                    Rechercher un logement
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

