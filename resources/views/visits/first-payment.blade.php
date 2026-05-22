@extends('layouts.app')

@section('title', 'Premier versement')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="mb-6">
            <a href="{{ route('visits.show', $visit) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Retour à la visite</span>
            </a>
        </div>
        
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Premier versement</h1>
            <p class="text-gray-600">Finalisez votre location en payant le premier versement</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex gap-4">
                <img src="{{ $visit->property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop' }}" 
                     alt="{{ $visit->property->title }}" class="w-24 h-24 rounded-xl object-cover">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900">{{ $visit->property->title }}</h3>
                    <p class="text-gray-500 text-sm">{{ $visit->property->full_address }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                <i class="fas fa-receipt text-green-600"></i> Détail du paiement
            </h2>
            <div class="space-y-4">
                <!-- Informations du contrat -->
                <div class="p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3">Contrat de location (6 mois)</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Loyer mensuel</span>
                            <span class="font-medium text-gray-900">{{ number_format($monthlyRent, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Durée du contrat</span>
                            <span class="font-medium text-gray-900">6 mois</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Montant total du contrat</span>
                            <span class="font-medium text-gray-900">{{ number_format($monthlyRent * 6, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Détail du premier versement -->
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-gray-700">Premier versement</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center py-2">
                            <div>
                                <span class="text-gray-600 block">Loyer mensuel</span>
                                <span class="text-xs text-gray-500">Premier mois</span>
                            </div>
                            <span class="font-semibold text-gray-900">{{ number_format($monthlyRent, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-t border-gray-100">
                            <div>
                                <span class="text-gray-600 block">Commission (8%)</span>
                                <span class="text-xs text-gray-500">Commission sur le loyer</span>
                            </div>
                            <span class="font-semibold text-gray-900">+ {{ number_format($commission, 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-t border-gray-100">
                            <div>
                                <span class="text-gray-600 block">Frais de service</span>
                                <span class="text-xs text-gray-500">Frais de gestion</span>
                            </div>
                            <span class="font-semibold text-gray-900">+ {{ number_format($serviceFee, 0, ',', ' ') }} FCFA</span>
                        </div>
                        @if($deposit > 0)
                            <div class="flex justify-between items-center py-2 border-t border-gray-100">
                                <div>
                                    <span class="text-gray-600 block">Dépôt de garantie</span>
                                    <span class="text-xs text-gray-500">Remboursable à la fin du contrat</span>
                                </div>
                                <span class="font-semibold text-gray-900">+ {{ number_format($deposit, 0, ',', ' ') }} FCFA</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Total -->
                <div class="border-t-2 border-gray-200 pt-4 mt-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-bold text-lg text-gray-900 block">Total à payer maintenant</span>
                            <span class="text-xs text-gray-500">
                                Loyer + Commission + Frais de service{{ $deposit > 0 ? ' + Dépôt de garantie' : '' }}
                            </span>
                        </div>
                        <span class="font-bold text-2xl text-green-600">
                            {{ number_format($firstPaymentAmount, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>

                <!-- Note sur les paiements futurs -->
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100 mt-4">
                    <p class="text-xs text-blue-700">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Note :</strong> Les 6 versements restants (648 000 FCFA/mois) seront dus mensuellement à partir du mois prochain. Le contrat de 6 mois sera créé après validation de ce paiement.
                    </p>
                </div>
            </div>
        </div>
        
        <form action="{{ route('visits.pay.first', $visit) }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-4">Mode de paiement</h2>
                <div class="space-y-3">
                    <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="airtel_money">
                        <input type="radio" name="payment_method" value="airtel_money" class="sr-only" required>
                        <div class="w-14 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                            <span class="text-red-600 font-bold text-xs">Airtel</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Airtel Money</p>
                        </div>
                        <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                    </label>
                    
                    <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="moov_money">
                        <input type="radio" name="payment_method" value="moov_money" class="sr-only">
                        <div class="w-14 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-xs">Moov</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Moov Money</p>
                        </div>
                        <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                    </label>
                    
                    <label class="payment-method flex items-center gap-4 cursor-pointer p-4 border-2 border-gray-200 rounded-xl hover:border-green-500 transition-colors" data-method="gabon_telecom_cash">
                        <input type="radio" name="payment_method" value="gabon_telecom_cash" class="sr-only">
                        <div class="w-14 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 font-bold text-xs">GT</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Gabon Telecom Cash</p>
                        </div>
                        <i class="fas fa-check-circle text-green-600 text-xl opacity-0 check-icon"></i>
                    </label>
                </div>
            </div>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Numéro de téléphone <span class="text-red-500">*</span>
                </label>
                <input type="tel" id="phone_number" name="phone_number" 
                       value="{{ old('phone_number', auth()->user()->phone) }}" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-500 focus:ring-2 focus:ring-green-500/20"
                       placeholder="+241 XX XX XX XX">
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-green-500/25 transition-all">
                <i class="fas fa-lock mr-2"></i>Payer {{ number_format($firstPaymentAmount, 0, ',', ' ') }} FCFA
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        document.querySelectorAll('.payment-method').forEach(m => {
            m.classList.remove('border-green-500', 'bg-green-50');
            m.querySelector('.check-icon').classList.add('opacity-0');
            m.querySelector('input[type="radio"]').checked = false;
        });
        this.classList.add('border-green-500', 'bg-green-50');
        this.querySelector('.check-icon').classList.remove('opacity-0');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>
@endpush
@endsection

