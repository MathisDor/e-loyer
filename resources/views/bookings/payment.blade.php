@extends('layouts.app')

@section('title', 'Paiement')

@section('content')
<div class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-2xl mx-auto px-4">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 bg-gabon-green/10 rounded-full flex items-center justify-center">
                <i class="fas fa-credit-card text-gabon-green text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Paiement sécurisé</h1>
            <p class="text-gray-600">Finalisez votre réservation</p>
        </div>
        
        <!-- Property Summary -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex gap-4">
                <img src="{{ $booking->property->main_image }}" alt="{{ $booking->property->title }}" class="w-24 h-24 rounded-xl object-cover">
                <div class="flex-1">
                    <h3 class="font-bold text-gray-900">{{ $booking->property->title }}</h3>
                    <p class="text-gray-500 text-sm">{{ $booking->property->city }}</p>
                    <p class="text-sm text-gray-600 mt-2">
                        <i class="far fa-calendar mr-1"></i>
                        {{ $booking->start_date->format('d/m/Y') }} - {{ $booking->end_date->format('d/m/Y') }}
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Payment Details -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-bold text-gray-900 mb-4">Détail du paiement</h2>
            
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">1er mois de loyer</span>
                    <span class="font-medium text-gray-900">{{ $booking->formatted_monthly_amount }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Caution (remboursable)</span>
                    <span class="font-medium text-gray-900">{{ $booking->formatted_deposit }}</span>
                </div>
                <div class="border-t border-gray-100 pt-3">
                    <div class="flex justify-between">
                        <span class="font-bold text-gray-900">Total à payer</span>
                        <span class="font-bold text-2xl text-gabon-green">{{ number_format($initialPayment, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Form -->
        <form action="{{ route('bookings.pay', $booking) }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Payment Method -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-4">Mode de paiement</h2>
                
                <div class="space-y-3">
                    <!-- Airtel Money -->
                    <label class="payment-method flex items-center gap-4 cursor-pointer" data-method="airtel_money">
                        <input type="radio" name="payment_method" value="airtel_money" class="sr-only" required>
                        <div class="w-14 h-10 bg-red-50 rounded-lg flex items-center justify-center">
                            <span class="text-red-600 font-bold text-xs">Airtel</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Airtel Money</p>
                            <p class="text-sm text-gray-500">Paiement via votre compte Airtel</p>
                        </div>
                        <i class="fas fa-check-circle text-gabon-green text-xl opacity-0 check-icon"></i>
                    </label>
                    
                    <!-- Moov Money -->
                    <label class="payment-method flex items-center gap-4 cursor-pointer" data-method="moov_money">
                        <input type="radio" name="payment_method" value="moov_money" class="sr-only">
                        <div class="w-14 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 font-bold text-xs">Moov</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Moov Money</p>
                            <p class="text-sm text-gray-500">Paiement via votre compte Moov</p>
                        </div>
                        <i class="fas fa-check-circle text-gabon-green text-xl opacity-0 check-icon"></i>
                    </label>
                    
                    <!-- Gabon Telecom Cash -->
                    <label class="payment-method flex items-center gap-4 cursor-pointer" data-method="gabon_telecom_cash">
                        <input type="radio" name="payment_method" value="gabon_telecom_cash" class="sr-only">
                        <div class="w-14 h-10 bg-green-50 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 font-bold text-xs">GT</span>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Gabon Telecom Cash</p>
                            <p class="text-sm text-gray-500">Paiement via votre compte GT Cash</p>
                        </div>
                        <i class="fas fa-check-circle text-gabon-green text-xl opacity-0 check-icon"></i>
                    </label>
                </div>
                
                @error('payment_method')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Phone Number -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <label for="phone_number" class="block font-bold text-gray-900 mb-2">Numéro de téléphone</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">+241</span>
                    <input type="tel" id="phone_number" name="phone_number" 
                           value="{{ old('phone_number', auth()->user()->phone) }}" required
                           class="w-full pl-16 pr-4 py-3 border border-gray-200 rounded-xl focus:border-gabon-green focus:ring-2 focus:ring-gabon-green/20"
                           placeholder="XX XX XX XX">
                </div>
                <p class="text-sm text-gray-500 mt-2">Vous recevrez une demande de confirmation sur ce numéro</p>
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Security Notice -->
            <div class="bg-gabon-green/5 border border-gabon-green/20 rounded-2xl p-4 flex items-start gap-3">
                <i class="fas fa-shield-alt text-gabon-green mt-0.5"></i>
                <div class="text-sm">
                    <p class="font-medium text-gray-900">Paiement sécurisé</p>
                    <p class="text-gray-600">Vos informations sont protégées et votre paiement est sécurisé par les opérateurs Mobile Money.</p>
                </div>
            </div>
            
            <!-- Submit -->
            <button type="submit" class="w-full py-4 bg-gradient-to-r from-gabon-green to-gabon-green/90 text-white rounded-xl font-bold text-lg hover:shadow-lg hover:shadow-gabon-green/25 transition-all duration-200">
                <i class="fas fa-lock mr-2"></i>Payer {{ number_format($initialPayment, 0, ',', ' ') }} FCFA
            </button>
            
            <p class="text-center text-sm text-gray-500">
                En payant, vous acceptez nos <a href="#" class="text-gabon-green hover:underline">conditions de location</a>
            </p>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.payment-method').forEach(method => {
    method.addEventListener('click', function() {
        // Remove selected state from all
        document.querySelectorAll('.payment-method').forEach(m => {
            m.classList.remove('selected');
            m.querySelector('.check-icon').classList.add('opacity-0');
        });
        
        // Add selected state to clicked
        this.classList.add('selected');
        this.querySelector('.check-icon').classList.remove('opacity-0');
        this.querySelector('input[type="radio"]').checked = true;
    });
});
</script>
@endpush
@endsection


