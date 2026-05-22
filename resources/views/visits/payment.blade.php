@extends('layouts.app')

@section('title', 'Paiement de la visite')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-8 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-100">
                <i class="fas fa-shield-alt"></i> Paiement sécurisé Mobile Money
            </div>
            <div class="w-16 h-16 mx-auto bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-credit-card text-green-600 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Paiement de la visite</h1>
            <p class="text-gray-600">Finalisez votre réservation de visite</p>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex gap-4">
                <img src="{{ $visit->property->main_image ?? 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&h=400&fit=crop' }}" 
                     alt="{{ $visit->property->title }}" class="w-24 h-24 rounded-xl object-cover">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900">{{ $visit->property->title }}</h3>
                    <p class="text-gray-500 text-sm">{{ $visit->property->full_address }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="far fa-calendar mr-1"></i>
                        {{ $visit->scheduled_at->format('d/m/Y à H:i') }}
                    </p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Détail du paiement</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Prix de base</span>
                    <span class="font-medium text-gray-900">{{ number_format($visit->base_price, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Commission (8%)</span>
                    <span class="font-medium text-gray-900">+ {{ number_format($visit->commission, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Frais de service</span>
                    <span class="font-medium text-gray-900">+ {{ number_format($visit->service_fee, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="border-t border-gray-100 pt-3">
                    <div class="flex justify-between">
                        <span class="font-bold text-gray-900">Total à payer</span>
                        <span class="font-bold text-2xl text-green-600">{{ $visit->formatted_total_amount }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <form action="{{ route('visits.pay', $visit) }}" method="POST" class="space-y-6">
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
                <p class="text-xs text-gray-500 mt-2 flex items-center gap-1">
                    <i class="fas fa-lock text-green-500"></i> Numéro vérifié uniquement pour le paiement Mobile Money.
                </p>
            </div>
            
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-green-500/25 transition-all">
                <i class="fas fa-lock mr-2"></i>Payer {{ $visit->formatted_total_amount }}
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

