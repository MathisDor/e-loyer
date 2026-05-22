<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Property;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Booking;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisitController extends Controller
{
    /**
     * Formulaire de réservation d'une visite
     */
    public function create(Property $property)
    {
        if (!$property->is_available || $property->status !== 'approuve') {
            return back()->with('error', 'Cette propriété n\'est pas disponible pour les visites.');
        }

        if (!$property->visit_price) {
            return back()->with('error', 'Cette propriété n\'a pas de prix de visite défini.');
        }

        if (Auth::id() === $property->owner_id) {
            return back()->with('error', 'Vous ne pouvez pas réserver une visite pour votre propre propriété.');
        }

        if (!Auth::user()->isLocataire()) {
            return back()->with('error', 'Seuls les locataires peuvent réserver une visite.');
        }

        // Bloquer si contrat actif pour ce bien
        $activeContract = \App\Models\Contract::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'actif')
            ->first();

        if ($activeContract) {
            return redirect()->route('properties.show', $property)
                ->with('info', 'Vous louez actuellement ce logement.');
        }

        // Bloquer si visite déjà en attente ou en cours (non terminée)
        $existing = Visit::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->whereIn('status', ['reservee', 'en_cours'])
            ->first();

        if ($existing) {
            return redirect()->route('visits.show', $existing)
                ->with('info', 'Vous avez déjà une visite active pour ce logement. Suivez-la ici.');
        }

        // Bloquer si visite terminée sans décision du locataire
        $pendingDecision = Visit::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'terminee')
            ->whereNull('property_accepted')
            ->first();

        if ($pendingDecision) {
            return redirect()->route('visits.show', $pendingDecision)
                ->with('info', 'Votre visite est terminée. Donnez votre avis sur le logement avant de revisiter.');
        }

        $amounts = Visit::calculateTotalAmount($property->visit_price);

        return view('visits.create', compact('property', 'amounts'));
    }

    /**
     * Enregistrer une réservation de visite
     */
    public function store(Request $request, Property $property)
    {
        if (!$property->is_available || $property->status !== 'approuve') {
            return back()->with('error', 'Cette propriété n\'est pas disponible pour les visites.');
        }

        if (!$property->visit_price) {
            return back()->with('error', 'Cette propriété n\'a pas de prix de visite défini.');
        }

        if (Auth::id() === $property->owner_id) {
            return back()->with('error', 'Vous ne pouvez pas réserver une visite pour votre propre propriété.');
        }

        // Bloquer si contrat actif
        $activeContract = \App\Models\Contract::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->where('status', 'actif')
            ->first();

        if ($activeContract) {
            return redirect()->route('properties.show', $property)
                ->with('info', 'Vous louez actuellement ce logement.');
        }

        // Bloquer si visite déjà en attente ou en cours
        $existing = Visit::where('property_id', $property->id)
            ->where('tenant_id', Auth::id())
            ->whereIn('status', ['reservee', 'en_cours'])
            ->first();

        if ($existing) {
            return redirect()->route('visits.show', $existing)
                ->with('info', 'Vous avez déjà une visite active pour ce logement.');
        }

        $validated = $request->validate([
            'scheduled_at' => ['required', 'date', 'after_or_equal:today'],
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'Vous devez accepter les conditions de visite.',
            'accept_terms.accepted' => 'Vous devez accepter les conditions de visite.',
        ]);

        $amounts = Visit::calculateTotalAmount($property->visit_price);
        $assignedUserId = null;



        $visit = Visit::create([
            'property_id' => $property->id,
            'tenant_id' => Auth::id(),
            'owner_id' => $property->owner_id,
            'owner_id' => $property->owner_id,
            'scheduled_at' => Carbon::parse($validated['scheduled_at']),
            'base_price' => $amounts['base_price'],
            'commission' => $amounts['commission'],
            'service_fee' => $amounts['service_fee'],
            'total_amount' => $amounts['total_amount'],
            'status' => 'reservee',
            'is_paid' => false,
        ]);

        return redirect()->route('visits.payment', $visit)
            ->with('success', 'Visite réservée. Veuillez procéder au paiement.');
    }

    /**
     * Page de paiement de la visite
     */
    public function payment(Visit $visit)
    {
        if ($visit->tenant_id !== Auth::id()) {
            abort(403);
        }

        if ($visit->is_paid) {
            return redirect()->route('visits.show', $visit)
                ->with('info', 'Cette visite a déjà été payée.');
        }

        return view('visits.payment', compact('visit'));
    }

    /**
     * Traiter le paiement de la visite
     */
    public function processPayment(Request $request, Visit $visit)
    {
        if ($visit->tenant_id !== Auth::id()) {
            abort(403);
        }

        if ($visit->is_paid) {
            return back()->with('error', 'Cette visite a déjà été payée.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        // Créer le paiement
        $payment = Payment::create([
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
            'amount' => $visit->total_amount,
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'payment_type' => 'visite',
            'status' => 'traitement',
            'description' => "Paiement de la visite - {$visit->property->title}",
        ]);

        // Simuler une réponse de paiement réussie
        // En production, ici vous appelleriez l'API du fournisseur Mobile Money
        $payment->update([
            'status' => 'confirme',
            'transaction_id' => 'TXN' . time() . rand(1000, 9999),
            'paid_at' => now(),
        ]);

        // Mettre à jour la visite
        $visit->update([
            'payment_id' => $payment->id,
            'is_paid' => true,
            'paid_at' => now(),
        ]);



        // Notifier le propriétaire
        Notification::send(
            $visit->owner,
            'visit_payment_received',
            'Paiement de visite reçu',
            "Un locataire a payé pour une visite de votre propriété : {$visit->property->title}",
            route('visits.show', $visit)
        );



        return redirect()->route('visits.show', $visit)
            ->with('success', 'Paiement effectué avec succès ! Votre visite est confirmée.');
    }

    /**
     * Afficher une visite
     */
    public function show(Visit $visit)
    {
        // Vérifier les permissions
        if ($visit->tenant_id !== Auth::id() && 
            $visit->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        $visit->load(['property', 'tenant', 'owner', 'assignedUser', 'payment']);

        $role = $this->resolveVisitRole($visit);
        $tabs = $this->buildShowTabs($visit, $role);
        $statusSteps = $this->buildStatusSteps($visit);

        $view = match ($role) {
            'locataire' => 'visits.show-tenant',
            'proprietaire' => 'visits.show-owner',
            default => 'visits.show',
        };

        return view($view, compact('visit', 'role', 'tabs', 'statusSteps'));
    }

    /**
    * Liste des visites par rôle : locataire
    */
    public function tenantIndex(Request $request)
    {
        $query = Visit::with(['property', 'owner', 'assignedUser'])
            ->where('tenant_id', Auth::id())
            ->latest('scheduled_at');

        return $this->renderIndex($request, $query, 'locataire', [
            'title' => 'Mes visites',
            'description' => 'Suivi de vos visites planifiées et passées.',
        ]);
    }

    /**
     * Liste des visites par rôle : propriétaire
     */
    public function ownerIndex(Request $request)
    {
        $query = Visit::with(['property', 'tenant', 'assignedUser'])
            ->where('owner_id', Auth::id())
            ->latest('scheduled_at');

        return $this->renderIndex($request, $query, 'proprietaire', [
            'title' => 'Visites de mes biens',
            'description' => 'Acceptez, refusez ou démarrez les visites de vos biens.',
        ]);
    }



    /**
     * Rendu commun des index visites (avec filtres/tabs)
     */
    protected function renderIndex(Request $request, $baseQuery, string $role, array $meta = [])
    {
        $status = $request->get('status');
        $search = $request->get('q');

        if ($status) {
            $baseQuery->where('status', $status);
        }

        if ($search) {
            $baseQuery->whereHas('property', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('quartier', 'like', "%{$search}%");
            });
        }

        $tabs = $this->buildStatusTabs($baseQuery);
        $visits = $baseQuery->paginate(9)->withQueryString();

        return view('visits.index', [
            'title' => $meta['title'] ?? 'Visites',
            'description' => $meta['description'] ?? '',
            'visits' => $visits,
            'tabs' => $tabs,
            'activeStatus' => $status,
            'role' => $role,
            'search' => $search,
        ]);
    }

    /**
     * Comptage des statuts pour les onglets
     */
    protected function buildStatusTabs($query): array
    {
        $allCount = (clone $query)->count();
        $statusCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $map = [
            ['key' => null, 'label' => 'Toutes', 'count' => $allCount],
            ['key' => 'reservee', 'label' => 'Réservées', 'count' => $statusCounts['reservee'] ?? 0],
            ['key' => 'en_cours', 'label' => 'En cours', 'count' => $statusCounts['en_cours'] ?? 0],
            ['key' => 'terminee', 'label' => 'Terminées', 'count' => $statusCounts['terminee'] ?? 0],
            ['key' => 'acceptee', 'label' => 'Acceptées', 'count' => $statusCounts['acceptee'] ?? 0],
            ['key' => 'refusee', 'label' => 'Refusées', 'count' => $statusCounts['refusee'] ?? 0],
            ['key' => 'annulee', 'label' => 'Annulées', 'count' => $statusCounts['annulee'] ?? 0],
        ];

        return $map;
    }

    /**
     * Détermine le contexte d'affichage de la visite pour l'utilisateur courant
     */
    protected function resolveVisitRole(Visit $visit): string
    {
        $user = Auth::user();

        if ($user->id === $visit->tenant_id) {
            return 'locataire';
        }

        if ($user->id === $visit->owner_id) {
            return 'proprietaire';
        }



        if ($user->isAdmin()) {
            return 'admin';
        }

        return 'invite';
    }

    /**
     * Onglets à afficher sur les écrans "visit-show" selon le rôle
     */
    protected function buildShowTabs(Visit $visit, string $role): array
    {
        $tabs = [
            ['id' => 'overview', 'label' => 'Résumé'],
            ['id' => 'participants', 'label' => 'Participants'],
            ['id' => 'payments', 'label' => 'Paiements', 'badge' => $visit->is_paid ? 'Payé' : 'À payer'],
            ['id' => 'actions', 'label' => 'Actions'],
            ['id' => 'history', 'label' => 'Statuts'],
        ];

        if ($role === 'locataire' && $visit->status === 'acceptee') {
            $tabs[] = ['id' => 'first-payment', 'label' => 'Premier versement'];
        }

        return $tabs;
    }

    /**
     * Timeline simple des statuts d'une visite
     */
    protected function buildStatusSteps(Visit $visit): array
    {
        $labels = [
            'reservee' => 'Réservée',
            'en_cours' => 'En cours',
            'terminee' => 'Terminée',
            'non_effectuee' => 'Non effectuée',
            'acceptee' => 'Acceptée',
            'refusee' => 'Refusée',
            'annulee' => 'Annulée',
        ];

        $ordered = ['reservee', 'en_cours', 'terminee', 'non_effectuee', 'acceptee', 'refusee', 'annulee'];
        $currentIndex = array_search($visit->status, $ordered, true);

        return collect($ordered)->map(function ($key, $index) use ($currentIndex, $labels) {
            return [
                'key' => $key,
                'label' => $labels[$key],
                'done' => $currentIndex !== false && $index <= $currentIndex,
                'active' => $currentIndex !== false && $index === $currentIndex,
            ];
        })->toArray();
    }

    /**
     * Mettre à jour le statut de la visite à "en cours" (par le démarcheur)
     */
    public function start(Visit $visit)
    {
        if (
            $visit->assigned_user_id !== Auth::id() &&
            $visit->owner_id !== Auth::id() &&
            !Auth::user()->isAdmin()
        ) {
            abort(403);
        }

        if (!$visit->canStart()) {
            return back()->with('error', 'Cette visite ne peut pas être démarrée actuellement.');
        }

        $visit->update(['status' => 'en_cours']);

        // Notifier le locataire
        Notification::send(
            $visit->tenant,
            'visit_started',
            'Visite démarrée',
            "La visite de la propriété {$visit->property->title} a commencé.",
            route('visits.show', $visit)
        );

        return back()->with('success', 'Visite démarrée avec succès.');
    }

    /**
     * Valider l'état de la visite (par le démarcheur)
     */
    public function validateVisit(Request $request, Visit $visit)
    {
        if (
            $visit->owner_id !== Auth::id() &&
            !Auth::user()->isAdmin()
        ) {
            abort(403);
        }

        if ($visit->status !== 'en_cours') {
            return back()->with('error', 'Cette visite n\'est pas en cours.');
        }

        $validated = $request->validate([
            'visit_status' => ['required', 'in:reussie,non_effectuee'],
            'visit_status_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $visit->update([
            'visit_status' => $validated['visit_status'],
            'visit_status_notes' => $validated['visit_status_notes'] ?? null,
            'status' => $validated['visit_status'] === 'reussie' ? 'terminee' : 'non_effectuee',
        ]);

        // Notifier le locataire
        if ($validated['visit_status'] === 'reussie') {
            Notification::send(
                $visit->tenant,
                'visit_completed',
                'Visite terminée',
                "La visite de la propriété {$visit->property->title} a été terminée. Vous pouvez maintenant donner votre avis.",
                route('visits.show', $visit)
            );
        } else {
            Notification::send(
                $visit->tenant,
                'visit_not_completed',
                'Visite non effectuée',
                "La visite de la propriété {$visit->property->title} n'a pas pu être effectuée.",
                route('visits.show', $visit)
            );
        }

        return back()->with('success', 'État de la visite mis à jour avec succès.');
    }

    /**
     * Mise à jour générique du statut (propriétaire / démarcheur / agence)
     */
    public function updateStatus(Request $request, Visit $visit)
    {
        if (
            $visit->owner_id !== Auth::id() &&
            !Auth::user()->isAdmin()
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:reservee,en_cours,terminee,acceptee,refusee,annulee'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = [
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if (!empty($validated['scheduled_at'])) {
            $data['scheduled_at'] = Carbon::parse($validated['scheduled_at']);
        }

        $visit->update($data);

        return back()->with('success', 'Statut de la visite mis à jour.');
    }

    /**
     * Mettre à jour le statut de la visite à "terminée" (par le locataire)
     */
    public function complete(Request $request, Visit $visit)
    {
        if ($visit->tenant_id !== Auth::id()) {
            abort(403);
        }

        // Vérifier que la visite a été validée comme réussie par le démarcheur/propriétaire
        if ($visit->status === 'terminee' && $visit->visit_status !== 'reussie') {
            return back()->with('error', 'La visite doit être validée comme réussie par le propriétaire/démarcheur avant de pouvoir donner votre avis.');
        }

        // Si la visite est déjà acceptée ou refusée, ne pas permettre de modifier
        if (!is_null($visit->property_accepted)) {
            return back()->with('error', 'Vous avez déjà donné votre avis sur cette visite.');
        }

        if (!$visit->canComplete()) {
            return back()->with('error', 'Cette visite ne peut pas être complétée actuellement.');
        }

        $validated = $request->validate([
            'property_accepted' => ['required', 'boolean'],
            'refusal_reason' => ['nullable', 'string', 'max:1000', 'required_if:property_accepted,false'],
        ]);

        $visit->update([
            'status' => 'terminee',
            'property_accepted' => $validated['property_accepted'],
            'refusal_reason' => $validated['refusal_reason'] ?? null,
        ]);

        // Si la propriété est acceptée, mettre à jour le statut
        if ($validated['property_accepted']) {
            // Mettre à jour le statut
            $visit->update(['status' => 'acceptee']);

            // Notifier le propriétaire
            Notification::send(
                $visit->owner,
                'visit_accepted',
                'Propriété acceptée',
                "Le locataire a accepté votre propriété : {$visit->property->title}. Veuillez attendre le paiement du premier versement pour finaliser le contrat.",
                route('visits.show', $visit)
            );

            return redirect()->route('visits.payment.first', $visit)
                ->with('success', 'Propriété acceptée ! Veuillez procéder au paiement du premier versement pour finaliser le contrat.');
        } else {
            // Propriété refusée, elle reste disponible
            Notification::send(
                $visit->owner,
                'visit_refused',
                'Propriété refusée',
                "Le locataire a refusé votre propriété : {$visit->property->title}.",
                route('visits.show', $visit)
            );

            return back()->with('info', 'Propriété refusée. La propriété reste disponible.');
        }
    }

    /**
     * Page de paiement du premier versement
     * Montant = loyer mensuel + commission (8%) + frais de service (400) + caution (si requis)
     */
    public function firstPayment(Visit $visit)
    {
        if ($visit->tenant_id !== Auth::id()) {
            abort(403);
        }

        if ($visit->status !== 'acceptee') {
            return back()->with('error', 'Cette visite n\'a pas été acceptée.');
        }

        // Calculer le montant du premier versement
        $monthlyRent = $visit->property->monthly_price;
        $commission = round($monthlyRent * 0.08); // 8% de commission
        $serviceFee = 400; // Frais de service fixes
        $deposit = $visit->property->requires_deposit ? ($visit->property->deposit ?? 0) : 0;
        
        $firstPaymentAmount = $monthlyRent + $commission + $serviceFee + $deposit;

        return view('visits.first-payment', compact('visit', 'firstPaymentAmount', 'monthlyRent', 'commission', 'serviceFee', 'deposit'));
    }

    /**
     * Traiter le paiement du premier versement
     * Créer le contrat APRÈS le paiement réussi
     */
    public function processFirstPayment(Request $request, Visit $visit)
    {
        if ($visit->tenant_id !== Auth::id()) {
            abort(403);
        }

        if ($visit->status !== 'acceptee') {
            return back()->with('error', 'Cette visite n\'a pas été acceptée.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:airtel_money,moov_money,gabon_telecom_cash'],
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        // Calculer le montant du premier versement
        $monthlyRent = $visit->property->monthly_price;
        $commission = round($monthlyRent * 0.08); // 8% de commission
        $serviceFee = 400; // Frais de service fixes
        $depositAmount = $visit->property->requires_deposit ? ($visit->property->deposit ?? 0) : 0;
        $totalAmount = $monthlyRent + $commission + $serviceFee + $depositAmount;

        // Créer le paiement pour le premier versement
        $payment = Payment::create([
            'visit_id' => $visit->id,
            'user_id' => Auth::id(),
            'amount' => $totalAmount,
            'payment_method' => $validated['payment_method'],
            'phone_number' => $validated['phone_number'],
            'payment_type' => 'premier_versement',
            'status' => 'traitement',
            'description' => "Premier versement (Loyer + Commission + Frais de service" . ($depositAmount > 0 ? " + Dépôt de garantie" : "") . ") - {$visit->property->title}",
        ]);

        // Simuler le paiement
        $payment->update([
            'status' => 'confirme',
            'transaction_id' => 'TXN' . time() . rand(1000, 9999),
            'paid_at' => now(),
        ]);

        // CRÉER LE CONTRAT APRÈS LE PAIEMENT RÉUSSI
        $startDate = now();
        $endDate = $startDate->copy()->addMonths(6);
        
        $contract = Contract::create([
            'visit_id' => $visit->id,
            'property_id' => $visit->property_id,
            'tenant_id' => $visit->tenant_id,
            'owner_id' => $visit->owner_id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_months' => 6,
            'monthly_amount' => $monthlyRent,
            'total_amount' => $monthlyRent * 6,
            'deposit_amount' => $depositAmount,
            'status' => 'actif',
            'tenant_signed_at' => now(),
            'owner_signed_at' => now(),
            'months_paid' => 1, // Premier mois payé
            'next_payment_date' => $startDate->copy()->addMonth(),
        ]);

        // Mettre à jour le paiement avec le contract_id
        $payment->update(['contract_id' => $contract->id]);

        // Si un dépôt est requis, créer un paiement séparé pour le dépôt (pour le suivi)
        if ($depositAmount > 0) {
            Payment::create([
                'contract_id' => $contract->id,
                'visit_id' => $visit->id,
                'user_id' => Auth::id(),
                'amount' => $depositAmount,
                'payment_method' => $validated['payment_method'],
                'phone_number' => $validated['phone_number'],
                'payment_type' => 'caution',
                'status' => 'confirme',
                'transaction_id' => 'DEP' . time() . rand(1000, 9999),
                'paid_at' => now(),
                'description' => "Dépôt de garantie - {$visit->property->title}",
            ]);
        }

        // Mettre à jour la propriété
        $visit->property->update([
            'status' => 'loue',
            'is_available' => false,
        ]);

        // Notifier le propriétaire
        Notification::send(
            $visit->owner,
            'first_payment_received',
            'Premier versement reçu',
            "Le premier versement pour {$visit->property->title} a été reçu. Le contrat de 6 mois est maintenant actif.",
            route('contracts.show', $contract)
        );

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Premier versement effectué avec succès ! Votre contrat de 6 mois est maintenant actif.');
    }
}

