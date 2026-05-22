<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MobileMoneyService
{
    /**
     * Initier un paiement Airtel Money
     */
    public function initiateAirtelPayment(array $data): array
    {
        try {
            // En production, remplacer par l'API réelle Airtel Money
            // Documentation: https://developers.airtel.africa
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->getAirtelToken(),
                'X-Country' => 'GA',
                'X-Currency' => 'XAF',
            ])->post(config('services.airtel.api_url') . '/merchant/v1/payments/', [
                'reference' => $data['reference'],
                'subscriber' => [
                    'country' => 'GA',
                    'currency' => 'XAF',
                    'msisdn' => $this->formatPhoneNumber($data['phone']),
                ],
                'transaction' => [
                    'amount' => $data['amount'],
                    'country' => 'GA',
                    'currency' => 'XAF',
                    'id' => $data['reference'],
                ],
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'transaction_id' => $response->json('data.transaction.id'),
                    'status' => $response->json('data.transaction.status'),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message', 'Erreur de paiement'),
            ];
        } catch (\Exception $e) {
            Log::error('Airtel Money Error: ' . $e->getMessage());
            
            // Simulation pour développement
            return $this->simulatePayment($data);
        }
    }

    /**
     * Initier un paiement Moov Money
     */
    public function initiateMoovPayment(array $data): array
    {
        try {
            // En production, implémenter l'API Moov Money
            // Contacter Moov Africa Gabon pour la documentation
            
            return $this->simulatePayment($data);
        } catch (\Exception $e) {
            Log::error('Moov Money Error: ' . $e->getMessage());
            return $this->simulatePayment($data);
        }
    }

    /**
     * Initier un paiement Gabon Telecom Cash
     */
    public function initiateGTCashPayment(array $data): array
    {
        try {
            // En production, implémenter l'API Gabon Telecom
            // Contacter Gabon Telecom pour la documentation
            
            return $this->simulatePayment($data);
        } catch (\Exception $e) {
            Log::error('GT Cash Error: ' . $e->getMessage());
            return $this->simulatePayment($data);
        }
    }

    /**
     * Vérifier le statut d'une transaction
     */
    public function checkTransactionStatus(string $transactionId, string $provider): array
    {
        return match($provider) {
            'airtel_money' => $this->checkAirtelStatus($transactionId),
            'moov_money' => $this->checkMoovStatus($transactionId),
            'gabon_telecom_cash' => $this->checkGTCashStatus($transactionId),
            default => ['status' => 'unknown'],
        };
    }

    /**
     * Initier un paiement selon le fournisseur
     */
    public function initiatePayment(string $provider, array $data): array
    {
        return match($provider) {
            'airtel_money' => $this->initiateAirtelPayment($data),
            'moov_money' => $this->initiateMoovPayment($data),
            'gabon_telecom_cash' => $this->initiateGTCashPayment($data),
            default => ['success' => false, 'error' => 'Fournisseur inconnu'],
        };
    }

    /**
     * Simulation de paiement pour développement
     */
    protected function simulatePayment(array $data): array
    {
        // Simuler un délai de traitement
        usleep(500000); // 0.5 seconde

        // 95% de réussite en simulation
        $success = rand(1, 100) <= 95;

        if ($success) {
            return [
                'success' => true,
                'transaction_id' => 'SIM_' . strtoupper(uniqid()) . '_' . time(),
                'status' => 'completed',
                'message' => 'Paiement simulé réussi',
            ];
        }

        return [
            'success' => false,
            'error' => 'Paiement refusé (simulation)',
        ];
    }

    /**
     * Obtenir le token Airtel
     */
    protected function getAirtelToken(): string
    {
        $cacheKey = 'airtel_money_token';
        
        if ($token = cache($cacheKey)) {
            return $token;
        }

        try {
            $response = Http::asForm()->post(config('services.airtel.api_url') . '/auth/oauth2/token', [
                'client_id' => config('services.airtel.client_id'),
                'client_secret' => config('services.airtel.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

            if ($response->successful()) {
                $token = $response->json('access_token');
                $expiresIn = $response->json('expires_in', 3600) - 60;
                cache([$cacheKey => $token], $expiresIn);
                return $token;
            }
        } catch (\Exception $e) {
            Log::error('Airtel Token Error: ' . $e->getMessage());
        }

        return '';
    }

    /**
     * Formater le numéro de téléphone
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Retirer les espaces et caractères spéciaux
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Retirer le préfixe +241 si présent
        if (str_starts_with($phone, '241')) {
            $phone = substr($phone, 3);
        }
        
        return $phone;
    }

    protected function checkAirtelStatus(string $transactionId): array
    {
        // Implémenter la vérification réelle
        return ['status' => 'completed'];
    }

    protected function checkMoovStatus(string $transactionId): array
    {
        return ['status' => 'completed'];
    }

    protected function checkGTCashStatus(string $transactionId): array
    {
        return ['status' => 'completed'];
    }
}


