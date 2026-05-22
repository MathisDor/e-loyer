<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Commission;
use App\Models\User;
use App\Models\Visit;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard principal - redirige selon le type d'utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        return match($user->user_type) {
            'admin' => $this->adminDashboard(),
            'proprietaire' => $this->ownerDashboard(),
            'demarcheur' => $this->prospectorDashboard(),
            'agence' => $this->agencyDashboard(),
            default => $this->tenantDashboard(),
        };
    }

    /**
     * Dashboard Locataire
     */
    protected function tenantDashboard()
    {
        $user = Auth::user();

        // Visites
        $upcomingVisits = Visit::with(['property', 'assignedUser'])
            ->where('tenant_id', $user->id)
            ->whereIn('status', ['reservee', 'en_cours'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $pastVisits = Visit::with(['property', 'assignedUser'])
            ->where('tenant_id', $user->id)
            ->whereIn('status', ['terminee', 'acceptee', 'refusee', 'annulee', 'non_effectuee'])
            ->latest()
            ->take(10)
            ->get();

        // Contrats actifs
        $activeContracts = Contract::with(['property', 'owner'])
            ->where('tenant_id', $user->id)
            ->where('status', 'actif')
            ->get();

        // Contrats terminés ou résiliés
        $completedContracts = Contract::with(['property', 'owner'])
            ->where('tenant_id', $user->id)
            ->whereIn('status', ['termine', 'annule', 'resilie'])
            ->latest()
            ->take(10)
            ->get();

        // Propriétés louées (via contrats)
        $rentedProperties = Property::whereHas('contracts', function($query) use ($user) {
            $query->where('tenant_id', $user->id)
                  ->whereIn('status', ['actif', 'termine']);
        })
        ->with(['contracts' => function($query) use ($user) {
            $query->where('tenant_id', $user->id)
                  ->whereIn('status', ['actif', 'termine'])
                  ->latest();
        }])
        ->latest()
        ->take(10)
        ->get();

        $favorites = $user->favoriteProperties()
            ->available()
            ->take(6)
            ->get();

        // Réservations actives du locataire (annulables)
        $activeBookings = Booking::with(['property', 'owner'])
            ->where('tenant_id', $user->id)
            ->whereIn('status', ['en_attente', 'acceptee', 'payee', 'active'])
            ->latest()
            ->get();

        // Paiements mensuels à venir (via contrats)
        $upcomingPayments = $activeContracts->map(function ($contract) {
            return [
                'contract' => $contract,
                'due_date' => $contract->next_payment_date,
                'amount' => $contract->monthly_amount,
            ];
        })->filter(fn($p) => $p['due_date'] !== null)
          ->sortBy('due_date')
          ->take(3);

        // Transactions récentes
        $recentPayments = Payment::with(['contract.property', 'booking.property'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        // Total loyer mensuel
        $totalMonthlyRent = $activeContracts->sum('monthly_amount');

        // Prochain paiement le plus urgent
        $nextPayment = $upcomingPayments->first();

        return view('dashboard.tenant.index', compact(
            'upcomingVisits',
            'pastVisits',
            'activeContracts',
            'completedContracts',
            'rentedProperties',
            'favorites',
            'upcomingPayments',
            'recentPayments',
            'totalMonthlyRent',
            'nextPayment',
            'activeBookings'
        ));
    }

    /**
     * Dashboard Propriétaire
     */
    protected function ownerDashboard()
    {
        $user = Auth::user();

        $properties = $user->properties()->withCount(['bookings', 'visits', 'contracts'])->get();

        // Visites
        $upcomingVisits = Visit::with(['property', 'tenant', 'assignedUser'])
            ->where('owner_id', $user->id)
            ->whereIn('status', ['reservee', 'en_cours'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $pastVisits = Visit::with(['property', 'tenant', 'assignedUser'])
            ->where('owner_id', $user->id)
            ->whereIn('status', ['terminee', 'acceptee', 'refusee'])
            ->latest()
            ->take(10)
            ->get();

        // Contrats actifs
        $activeContracts = Contract::with(['property', 'tenant'])
            ->where('owner_id', $user->id)
            ->where('status', 'actif')
            ->get();

        // Contrats terminés
        $completedContracts = Contract::with(['property', 'tenant'])
            ->where('owner_id', $user->id)
            ->whereIn('status', ['termine', 'annule'])
            ->latest()
            ->take(10)
            ->get();

        // Anciennes réservations (pour compatibilité)
        $pendingBookings = Booking::with(['property', 'tenant'])
            ->forOwner($user->id)
            ->pending()
            ->get();

        $activeBookings = Booking::with(['property', 'tenant'])
            ->forOwner($user->id)
            ->whereIn('status', ['active', 'payee'])
            ->get();

        // Propriétés ajoutées par démarcheurs à valider
        $propertiesToValidate = Property::with('prospector')
            ->where('owner_id', $user->id)
            ->whereNotNull('prospector_id')
            ->where('prospector_validated', false)
            ->get();

        // Statistiques
        $stats = [
            'total_properties' => $properties->count(),
            'approved_properties' => $properties->where('status', 'approuve')->count(),
            'rented_properties' => $properties->where('status', 'loue')->count(),
            'pending_visits' => $upcomingVisits->count(),
            'active_contracts' => $activeContracts->count(),
            'monthly_revenue' => $activeContracts->sum('monthly_amount') + $activeBookings->sum('monthly_amount'),
            'occupancy_rate' => $properties->count() > 0 
                ? round(($properties->where('status', 'loue')->count() / $properties->count()) * 100) 
                : 0,
        ];

        // Revenus des 6 derniers mois (via contrats et bookings)
        $contracts = Contract::where('owner_id', $user->id)
            ->whereIn('status', ['actif', 'termine'])
            ->where('start_date', '>=', now()->subMonths(6))
            ->get(['start_date', 'monthly_amount']);
        
        $bookings = Booking::where('owner_id', $user->id)
            ->whereIn('status', ['active', 'payee', 'terminee'])
            ->where('start_date', '>=', now()->subMonths(6))
            ->get(['start_date', 'monthly_amount']);
        
        $allRevenues = $contracts->concat($bookings);
        $revenueByMonth = $allRevenues->groupBy(function ($item) {
            return $item->start_date->format('n');
        })->map(function ($group) {
            return $group->sum('monthly_amount');
        })->toArray();

        return view('dashboard.owner.index', compact(
            'properties',
            'upcomingVisits',
            'pastVisits',
            'activeContracts',
            'completedContracts',
            'pendingBookings',
            'activeBookings',
            'propertiesToValidate',
            'stats',
            'revenueByMonth'
        ));
    }

    /**
     * Dashboard Démarcheur
     */
    protected function prospectorDashboard()
    {
        $user = Auth::user();

        // S'assurer que le démarcheur a un ref_code
        if (empty($user->ref_code)) {
            $user->update(['ref_code' => \App\Models\User::generateRefCode()]);
        }

        $properties = $user->prospectedProperties()
            ->with('owner')
            ->latest()
            ->get();

        $commissions = $user->commissions()
            ->with(['property', 'booking'])
            ->latest()
            ->get();

        // Visites assignées
        $assignedVisits = Visit::with(['property', 'tenant', 'owner'])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['reservee', 'en_cours'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->get();

        $pastVisits = Visit::with(['property', 'tenant', 'owner'])
            ->where('assigned_user_id', $user->id)
            ->whereIn('status', ['terminee', 'acceptee', 'refusee', 'non_effectuee'])
            ->latest()
            ->take(10)
            ->get();

        // Revenus des visites
        $visitEarnings = Visit::where('assigned_user_id', $user->id)
            ->where('is_paid', true)
            ->sum('commission');

        // Retraits
        $withdrawalRequests = $user->withdrawalRequests()->latest()->take(5)->get();
        $pendingWithdrawal  = $user->withdrawalRequests()->pending()->first();

        // Solde disponible = gains - retraits approuvés/payés
        $withdrawnAmount = $user->withdrawalRequests()
            ->whereIn('status', ['approuve', 'paye'])
            ->sum('amount');
        $availableBalance = max(0, (float) $user->total_earnings + $visitEarnings - $withdrawnAmount);

        // Classement top 5
        $topProspectors = \App\Models\User::where('user_type', 'demarcheur')
            ->where('is_suspended', false)
            ->orderByDesc('total_earnings')
            ->take(5)
            ->get();

        $myRank = \App\Models\User::where('user_type', 'demarcheur')
            ->where('total_earnings', '>', $user->total_earnings)
            ->count() + 1;

        // Statistiques
        $stats = [
            'total_properties'      => $properties->count(),
            'validated_properties'  => $properties->where('prospector_validated', true)->count(),
            'pending_properties'    => $properties->where('prospector_validated', false)->count(),
            'approved_properties'   => $properties->where('status', 'approuve')->count(),
            'pending_commissions'   => $commissions->where('status', 'en_attente')->sum('amount'),
            'validated_commissions' => $commissions->where('status', 'validee')->sum('amount'),
            'paid_commissions'      => $commissions->where('status', 'payee')->sum('amount'),
            'total_earnings'        => (float) $user->total_earnings + $visitEarnings,
            'assigned_visits'       => $assignedVisits->count(),
            'visit_earnings'        => $visitEarnings,
            'available_balance'     => $availableBalance,
            'clients_brought'       => (int) $user->clients_brought,
            'locations_concluded'   => (int) $user->locations_concluded,
            'badge_level'           => $user->badge_level ?? 'bronze',
            'badge_label'           => $user->badge_label,
            'my_rank'               => $myRank,
        ];

        // Top 5 propriétés rentables
        $topProperties = $user->prospectedProperties()
            ->withSum('commissions', 'amount')
            ->orderByDesc('commissions_sum_amount')
            ->take(5)
            ->get();

        return view('dashboard.prospector.index', compact(
            'properties',
            'commissions',
            'assignedVisits',
            'pastVisits',
            'stats',
            'topProperties',
            'withdrawalRequests',
            'pendingWithdrawal',
            'availableBalance',
            'topProspectors',
            'myRank'
        ));
    }

    /**
     * Dashboard Admin
     */
    protected function adminDashboard()
    {
        // Statistiques globales
        $stats = [
            'total_users' => User::count(),
            'total_tenants' => User::where('user_type', 'locataire')->count(),
            'total_owners' => User::where('user_type', 'proprietaire')->count(),
            'total_prospectors' => User::where('user_type', 'demarcheur')->count(),
            'total_agencies' => User::where('user_type', 'agence')->count(),
            'total_properties' => Property::count(),
            'approved_properties' => Property::where('status', 'approuve')->count(),
            'pending_properties' => Property::where('status', 'en_attente')->count(),
            'total_bookings' => Booking::count(),
            'active_bookings' => Booking::whereIn('status', ['active', 'payee'])->count(),
            'total_visits' => Visit::count(),
            'upcoming_visits' => Visit::whereIn('status', ['reservee', 'en_cours'])
                ->where('scheduled_at', '>=', now())
                ->count(),
            'total_contracts' => Contract::count(),
            'active_contracts' => Contract::where('status', 'actif')->count(),
            'platform_revenue' => Booking::whereIn('status', ['active', 'payee', 'terminee'])->sum('platform_commission') +
                Visit::where('is_paid', true)->sum('service_fee') +
                Contract::where('status', 'actif')->sum('monthly_amount') * 0.05, // 5% de commission sur les contrats
        ];

        // Propriétés en attente de validation
        $pendingProperties = Property::with(['owner', 'prospector'])
            ->pending()
            ->latest()
            ->take(10)
            ->get();

        // Derniers utilisateurs
        $recentUsers = User::latest()
            ->take(10)
            ->get();

        // Dernières réservations
        $recentBookings = Booking::with(['property', 'tenant', 'owner'])
            ->latest()
            ->take(10)
            ->get();

        // Dernières visites
        $recentVisits = Visit::with(['property', 'tenant', 'owner', 'assignedUser'])
            ->latest()
            ->take(10)
            ->get();

        // Derniers contrats
        $recentContracts = Contract::with(['property', 'tenant', 'owner'])
            ->latest()
            ->take(10)
            ->get();

        // Revenus par source
        $revenueBySource = [
            'bookings' => Booking::whereIn('status', ['active', 'payee', 'terminee'])->sum('platform_commission'),
            'visits' => Visit::where('is_paid', true)->sum('service_fee'),
            'contracts' => Contract::where('status', 'actif')->sum('monthly_amount') * 0.05,
        ];

        return view('dashboard.admin.index', compact(
            'stats',
            'pendingProperties',
            'recentUsers',
            'recentBookings',
            'recentVisits',
            'recentContracts',
            'revenueBySource'
        ));
    }

    /**
     * Dashboard Agence
     */
    protected function agencyDashboard()
    {
        $user = Auth::user();

        // Propriétés de l'agence
        $properties = $user->properties()->withCount('bookings')->get();

        // Réservations
        $pendingBookings = Booking::with(['property', 'tenant'])
            ->forOwner($user->id)
            ->pending()
            ->get();

        $activeBookings = Booking::with(['property', 'tenant'])
            ->forOwner($user->id)
            ->whereIn('status', ['active', 'payee'])
            ->get();

        // Abonnement actif
        $subscription = $user->subscription;
        $plan = $subscription?->plan;

        // Statistiques
        $stats = [
            'total_properties' => $properties->count(),
            'approved_properties' => $properties->where('status', 'approuve')->count(),
            'rented_properties' => $properties->where('status', 'loue')->count(),
            'pending_bookings' => $pendingBookings->count(),
            'active_bookings' => $activeBookings->count(),
            'monthly_revenue' => $activeBookings->sum('monthly_amount'),
            'balance' => $user->balance ?? 0,
            'total_images' => $properties->sum(fn($p) => count($p->images ?? [])),
            'max_properties' => $plan?->max_properties ?? 5,
            'max_images' => $plan?->max_images_per_property ?? 5,
            'occupancy_rate' => $properties->count() > 0 
                ? round(($properties->where('status', 'loue')->count() / $properties->count()) * 100) 
                : 0,
        ];

        // Transactions récentes
        $transactions = $user->transactions()->latest()->take(10)->get();

        // Sponsorisations actives
        $activeSponsorships = $user->sponsorships()
            ->where('status', 'active')
            ->where('ends_at', '>', now())
            ->with('property')
            ->get();

        // Méthodes de paiement
        $paymentMethods = $user->paymentMethods;

        // Plans d'abonnement disponibles
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();

        return view('dashboard.agency.index', compact(
            'properties',
            'pendingBookings',
            'activeBookings',
            'subscription',
            'plan',
            'stats',
            'transactions',
            'activeSponsorships',
            'paymentMethods',
            'plans'
        ));
    }
}

