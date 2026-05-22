<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Commission;
use App\Models\Favorite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============ CRÉER LES UTILISATEURS ============

        // Admin
        $admin = User::create([
            'name' => 'Admin E-Loyer',
            'email' => 'admin@e-loyer.ga',
            'password' => Hash::make('password'),
            'user_type' => 'admin',
            'phone' => '+241 77 00 00 00',
            'whatsapp' => '+241 77 00 00 00',
            'city' => 'Libreville',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Propriétaires
        $proprietaires = collect();
        $proprietairesData = [
            ['name' => 'Jean-Pierre Moussavou', 'email' => 'jean.moussavou@email.com', 'city' => 'Libreville'],
            ['name' => 'Marie-Claire Obame', 'email' => 'marie.obame@email.com', 'city' => 'Libreville'],
            ['name' => 'Patrick Ndong', 'email' => 'patrick.ndong@email.com', 'city' => 'Port-Gentil'],
            ['name' => 'Sylvie Mba', 'email' => 'sylvie.mba@email.com', 'city' => 'Franceville'],
        ];

        foreach ($proprietairesData as $data) {
            $proprietaires->push(User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'user_type' => 'proprietaire',
                'phone' => '+241 ' . rand(60, 79) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'whatsapp' => '+241 ' . rand(60, 79) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'city' => $data['city'],
                'is_verified' => true,
                'email_verified_at' => now(),
            ]));
        }

        // Locataires
        $locataires = collect();
        $locatairesData = [
            ['name' => 'François Ella', 'email' => 'francois.ella@email.com', 'city' => 'Libreville'],
            ['name' => 'Aurélien Nzeng', 'email' => 'aurelien.nzeng@email.com', 'city' => 'Libreville'],
            ['name' => 'Christine Ovono', 'email' => 'christine.ovono@email.com', 'city' => 'Port-Gentil'],
            ['name' => 'David Ondo', 'email' => 'david.ondo@email.com', 'city' => 'Libreville'],
            ['name' => 'Emma Nguema', 'email' => 'emma.nguema@email.com', 'city' => 'Franceville'],
        ];

        foreach ($locatairesData as $data) {
            $locataires->push(User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'user_type' => 'locataire',
                'phone' => '+241 ' . rand(60, 79) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'city' => $data['city'],
                'email_verified_at' => now(),
            ]));
        }

        // Démarcheurs
        $demarcheurs = collect();
        $demarcheursData = [
            ['name' => 'Albert Bibang', 'email' => 'albert.bibang@email.com', 'city' => 'Libreville'],
            ['name' => 'Rose Minko', 'email' => 'rose.minko@email.com', 'city' => 'Port-Gentil'],
        ];

        foreach ($demarcheursData as $data) {
            $demarcheurs->push(User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'user_type' => 'demarcheur',
                'phone' => '+241 ' . rand(60, 79) . ' ' . rand(10, 99) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                'city' => $data['city'],
                'is_verified' => true,
                'email_verified_at' => now(),
            ]));
        }

        // ============ CRÉER LES PROPRIÉTÉS ============

        $propertiesData = [
            [
                'owner' => 0,
                'title' => 'Magnifique appartement F3 à Nombakélé',
                'description' => 'Superbe appartement de 3 pièces entièrement rénové, situé dans le quartier résidentiel de Nombakélé. Lumineux avec une vue dégagée, il comprend un grand salon, 2 chambres climatisées, une cuisine américaine équipée et une salle de bain moderne. Parking sécurisé inclus. Idéal pour couple ou jeune famille.',
                'type' => 'appartement',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'surface' => 75,
                'monthly_price' => 350000,
                'deposit' => 350000,
                'city' => 'Libreville',
                'neighborhood' => 'Nombakélé',
                'address' => 'Rue des Palmiers, Immeuble Rose',
                'amenities' => ['wifi', 'climatisation', 'parking', 'cuisine_equipee', 'securite_24h'],
            ],
            [
                'owner' => 0,
                'title' => 'Studio meublé centre-ville Libreville',
                'description' => 'Charmant studio entièrement meublé au cœur de Libreville, proche de tous commerces et transports. Idéal pour étudiant ou jeune professionnel. Comprend une kitchenette équipée, coin nuit avec lit double, salle d\'eau avec douche. Internet haut débit inclus.',
                'type' => 'studio',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'surface' => 28,
                'monthly_price' => 180000,
                'deposit' => 180000,
                'city' => 'Libreville',
                'neighborhood' => 'Centre-ville',
                'address' => 'Boulevard Triomphal',
                'amenities' => ['wifi', 'climatisation', 'meuble', 'eau_chaude'],
            ],
            [
                'owner' => 1,
                'title' => 'Villa de luxe avec piscine à Glass',
                'description' => 'Exceptionnelle villa de standing dans le quartier prisé de Glass. 4 chambres en suite, vaste salon double, cuisine américaine haut de gamme, piscine privée avec terrasse, jardin tropical de 500m². Garage 2 voitures, personnel de maison possible. Une adresse d\'exception.',
                'type' => 'villa',
                'bedrooms' => 4,
                'bathrooms' => 4,
                'surface' => 280,
                'monthly_price' => 1500000,
                'deposit' => 3000000,
                'city' => 'Libreville',
                'neighborhood' => 'Glass',
                'address' => 'Avenue du Littoral',
                'amenities' => ['wifi', 'climatisation', 'parking', 'cuisine_equipee', 'piscine', 'jardin', 'securite_24h', 'gardien'],
            ],
            [
                'owner' => 2,
                'title' => 'Appartement F2 vue mer à Port-Gentil',
                'description' => 'Bel appartement avec vue imprenable sur l\'océan Atlantique. 1 chambre spacieuse, salon lumineux, balcon face à la mer. Résidence calme et sécurisée. Parfait pour professionnel ou couple en mission à Port-Gentil.',
                'type' => 'appartement',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'surface' => 55,
                'monthly_price' => 400000,
                'deposit' => 400000,
                'city' => 'Port-Gentil',
                'neighborhood' => 'Front de mer',
                'address' => 'Résidence Les Cocotiers',
                'amenities' => ['wifi', 'climatisation', 'balcon', 'securite_24h', 'groupe_electrogene'],
            ],
            [
                'owner' => 3,
                'title' => 'Maison familiale à Franceville',
                'description' => 'Grande maison familiale dans un quartier calme de Franceville. 3 chambres, 2 salles de bain, grand jardin clôturé idéal pour les enfants. Garage et dépendances. Proche des écoles et commerces.',
                'type' => 'maison',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'surface' => 120,
                'monthly_price' => 280000,
                'deposit' => 280000,
                'city' => 'Franceville',
                'neighborhood' => 'Centre',
                'address' => 'Quartier Potos',
                'amenities' => ['wifi', 'parking', 'jardin', 'cuisine_equipee'],
            ],
            [
                'owner' => 0,
                'prospector' => 0,
                'title' => 'Chambre meublée à Akébé',
                'description' => 'Chambre confortable dans une colocation, idéale pour étudiant. Salle de bain partagée, cuisine équipée commune, WiFi inclus. Ambiance conviviale et quartier dynamique.',
                'type' => 'chambre',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'surface' => 15,
                'monthly_price' => 75000,
                'deposit' => 75000,
                'city' => 'Libreville',
                'neighborhood' => 'Akébé',
                'address' => 'Carrefour SNI',
                'amenities' => ['wifi', 'meuble'],
            ],
            [
                'owner' => 1,
                'title' => 'Duplex moderne à Batterie IV',
                'description' => 'Superbe duplex contemporain dans le quartier huppé de Batterie IV. Rez-de-chaussée: salon cathédrale, cuisine ouverte, WC invités. Étage: 3 chambres dont suite parentale, 2 salles de bain. Terrasse avec vue, 2 places de parking.',
                'type' => 'appartement',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'surface' => 140,
                'monthly_price' => 850000,
                'deposit' => 850000,
                'city' => 'Libreville',
                'neighborhood' => 'Batterie IV',
                'address' => 'Résidence Le Palmier',
                'amenities' => ['wifi', 'climatisation', 'parking', 'cuisine_equipee', 'terrasse', 'securite_24h', 'groupe_electrogene'],
            ],
            [
                'owner' => 2,
                'prospector' => 1,
                'title' => 'Appartement économique Ntchengué',
                'description' => 'Appartement simple mais fonctionnel, idéal premier logement. 2 chambres, salon, cuisine, salle d\'eau. Quartier populaire et animé, proche transports.',
                'type' => 'appartement',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'surface' => 50,
                'monthly_price' => 120000,
                'deposit' => 120000,
                'city' => 'Port-Gentil',
                'neighborhood' => 'Ntchengué',
                'address' => 'Près du marché',
                'amenities' => ['eau_chaude'],
            ],
        ];

        $properties = collect();
        foreach ($propertiesData as $index => $data) {
            $property = Property::create([
                'owner_id' => $proprietaires[$data['owner']]->id,
                'prospector_id' => isset($data['prospector']) ? $demarcheurs[$data['prospector']]->id : null,
                'title' => $data['title'],
                'description' => $data['description'],
                'type' => $data['type'],
                'bedrooms' => $data['bedrooms'],
                'bathrooms' => $data['bathrooms'],
                'surface' => $data['surface'],
                'monthly_price' => $data['monthly_price'],
                'deposit' => $data['deposit'],
                'address' => $data['address'],
                'city' => $data['city'],
                'neighborhood' => $data['neighborhood'],
                'latitude' => $data['city'] === 'Libreville' ? (0.3 + (rand(-100, 100) / 10000)) : null,
                'longitude' => $data['city'] === 'Libreville' ? (9.4 + (rand(-100, 100) / 10000)) : null,
                'amenities' => $data['amenities'],
                'images' => ['properties/placeholder-' . ($index + 1) . '.jpg'],
                'status' => 'approuve',
                'is_available' => true,
                'prospector_validated' => true,
            ]);
            $properties->push($property);
        }

        // ============ CRÉER DES RÉSERVATIONS ============

        // Réservation active
        $booking1 = Booking::create([
            'property_id' => $properties[0]->id,
            'tenant_id' => $locataires[0]->id,
            'owner_id' => $proprietaires[0]->id,
            'start_date' => now()->subMonths(2),
            'end_date' => now()->addMonths(10),
            'duration_months' => 12,
            'monthly_amount' => 350000,
            'total_amount' => 4200000,
            'deposit_amount' => 350000,
            'platform_commission' => 42000,
            'prospector_commission' => 0,
            'status' => 'active',
        ]);

        // Réservation terminée avec avis
        $booking2 = Booking::create([
            'property_id' => $properties[1]->id,
            'tenant_id' => $locataires[1]->id,
            'owner_id' => $proprietaires[0]->id,
            'start_date' => now()->subMonths(8),
            'end_date' => now()->subMonths(2),
            'duration_months' => 6,
            'monthly_amount' => 180000,
            'total_amount' => 1080000,
            'deposit_amount' => 180000,
            'platform_commission' => 21600,
            'prospector_commission' => 0,
            'status' => 'terminee',
        ]);

        Review::create([
            'booking_id' => $booking2->id,
            'reviewer_id' => $locataires[1]->id,
            'property_id' => $properties[1]->id,
            'rating' => 5,
            'comment' => 'Excellent studio, très bien situé et parfaitement équipé. Le propriétaire est très réactif. Je recommande vivement !',
            'type' => 'property',
            'is_approved' => true,
        ]);

        // Réservation avec commission démarcheur
        $booking3 = Booking::create([
            'property_id' => $properties[5]->id,
            'tenant_id' => $locataires[3]->id,
            'owner_id' => $proprietaires[0]->id,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'duration_months' => 6,
            'monthly_amount' => 75000,
            'total_amount' => 450000,
            'deposit_amount' => 75000,
            'platform_commission' => 9000,
            'prospector_commission' => 3750,
            'status' => 'active',
        ]);

        Commission::create([
            'prospector_id' => $demarcheurs[0]->id,
            'booking_id' => $booking3->id,
            'property_id' => $properties[5]->id,
            'amount' => 3750,
            'percentage' => 5,
            'status' => 'payee',
            'paid_at' => now()->subWeek(),
            'payment_method' => 'airtel_money',
        ]);

        $demarcheurs[0]->increment('total_earnings', 3750);

        // Demande en attente
        Booking::create([
            'property_id' => $properties[3]->id,
            'tenant_id' => $locataires[2]->id,
            'owner_id' => $proprietaires[2]->id,
            'start_date' => now()->addWeeks(2),
            'end_date' => now()->addMonths(6)->addWeeks(2),
            'duration_months' => 6,
            'monthly_amount' => 400000,
            'total_amount' => 2400000,
            'deposit_amount' => 400000,
            'platform_commission' => 48000,
            'prospector_commission' => 0,
            'status' => 'en_attente',
            'tenant_message' => 'Bonjour, je suis très intéressé par votre appartement. Je travaille chez Total et je cherche un logement calme. Merci de considérer ma demande.',
        ]);

        // ============ CRÉER DES FAVORIS ============

        Favorite::create(['user_id' => $locataires[0]->id, 'property_id' => $properties[2]->id]);
        Favorite::create(['user_id' => $locataires[0]->id, 'property_id' => $properties[6]->id]);
        Favorite::create(['user_id' => $locataires[1]->id, 'property_id' => $properties[3]->id]);

        // ============ CRÉER DES AVIS SUPPLÉMENTAIRES ============

        Review::create([
            'booking_id' => $booking1->id,
            'reviewer_id' => $proprietaires[0]->id,
            'reviewed_id' => $locataires[0]->id,
            'rating' => 5,
            'comment' => 'Locataire exemplaire, toujours ponctuel dans ses paiements et respectueux du logement.',
            'type' => 'tenant',
            'is_approved' => true,
        ]);

        $this->command->info('✅ Base de données peuplée avec succès !');
        $this->command->info('');
        $this->command->info('📧 Comptes de test (mot de passe: password) :');
        $this->command->info('   Admin:        admin@e-loyer.ga');
        $this->command->info('   Propriétaire: jean.moussavou@email.com');
        $this->command->info('   Locataire:    francois.ella@email.com');
        $this->command->info('   Démarcheur:   albert.bibang@email.com');
    }
}
