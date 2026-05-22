<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'price' => 15000,
                'max_properties' => 5,
                'max_images_per_property' => 5,
                'can_sponsor' => false,
                'sponsor_discount' => 0,
                'priority_support' => false,
                'analytics_advanced' => false,
                'features' => json_encode([
                    '5 biens maximum',
                    '5 images par bien',
                    'Support standard',
                    'Statistiques basiques',
                ]),
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'price' => 35000,
                'max_properties' => 20,
                'max_images_per_property' => 10,
                'can_sponsor' => true,
                'sponsor_discount' => 10,
                'priority_support' => true,
                'analytics_advanced' => false,
                'features' => json_encode([
                    '20 biens maximum',
                    '10 images par bien',
                    'Sponsorisation (-10%)',
                    'Support prioritaire',
                    'Badge "Pro" sur le profil',
                ]),
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'price' => 75000,
                'max_properties' => 100,
                'max_images_per_property' => 20,
                'can_sponsor' => true,
                'sponsor_discount' => 25,
                'priority_support' => true,
                'analytics_advanced' => true,
                'features' => json_encode([
                    'Biens illimités',
                    '20 images par bien',
                    'Sponsorisation (-25%)',
                    'Support VIP 24/7',
                    'Statistiques avancées',
                    'API accès',
                    'Badge "Enterprise"',
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}


