<?php

namespace App\Http\Controllers;

use App\Models\InventoryReport;
use App\Models\Contract;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InventoryReportController extends Controller
{
    /**
     * Afficher un état des lieux
     */
    public function show(InventoryReport $inventoryReport)
    {
        // Vérifier les permissions
        if ($inventoryReport->contract->tenant_id !== Auth::id() && 
            $inventoryReport->contract->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        $inventoryReport->load(['contract', 'property', 'contract.tenant', 'contract.owner']);

        $role = $this->resolveRole($inventoryReport);

        return view('inventory-reports.show', compact('inventoryReport', 'role'));
    }

    /**
     * Créer un état des lieux d'entrée
     */
    public function createEntry(Contract $contract)
    {
        // Vérifier les permissions (propriétaire ou locataire)
        if ($contract->tenant_id !== Auth::id() && 
            $contract->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Vérifier qu'il n'existe pas déjà un état des lieux d'entrée
        if ($contract->entryInventoryReport) {
            return redirect()->route('inventory-reports.show', $contract->entryInventoryReport)
                ->with('info', 'Un état des lieux d\'entrée existe déjà pour ce contrat.');
        }

        return view('inventory-reports.create', [
            'contract' => $contract,
            'type' => 'entree',
        ]);
    }

    /**
     * Créer un état des lieux de sortie
     */
    public function createExit(Contract $contract)
    {
        // Vérifier les permissions
        if ($contract->tenant_id !== Auth::id() && 
            $contract->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Vérifier qu'il existe un état des lieux d'entrée
        if (!$contract->entryInventoryReport) {
            return back()->with('error', 'Vous devez d\'abord créer un état des lieux d\'entrée.');
        }

        // Vérifier qu'il n'existe pas déjà un état des lieux de sortie
        if ($contract->exitInventoryReport) {
            return redirect()->route('inventory-reports.show', $contract->exitInventoryReport)
                ->with('info', 'Un état des lieux de sortie existe déjà pour ce contrat.');
        }

        return view('inventory-reports.create', [
            'contract' => $contract,
            'type' => 'sortie',
        ]);
    }

    /**
     * Enregistrer un état des lieux
     */
    public function store(Request $request, Contract $contract)
    {
        // Vérifier les permissions
        if ($contract->tenant_id !== Auth::id() && 
            $contract->owner_id !== Auth::id() && 
            !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:entree,sortie'],
            'report_date' => ['required', 'date'],
            'observations' => ['nullable', 'string', 'max:2000'],
            'items' => ['nullable', 'array'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.condition' => ['required', 'in:bon,etat,degrade,manquant'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'max:5120'], // 5MB max
        ]);

        // Vérifier qu'il n'existe pas déjà un état des lieux de ce type
        $existing = InventoryReport::where('contract_id', $contract->id)
            ->where('type', $validated['type'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Un état des lieux de ce type existe déjà.');
        }

        // Traiter les photos
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inventory-reports', 'public');
                $photoPaths[] = $path;
            }
        }

        // Créer l'état des lieux
        $inventoryReport = InventoryReport::create([
            'contract_id' => $contract->id,
            'property_id' => $contract->property_id,
            'type' => $validated['type'],
            'report_date' => $validated['report_date'],
            'observations' => $validated['observations'] ?? null,
            'items' => $validated['items'] ?? [],
            'photos' => $photoPaths,
        ]);

        // Notifier l'autre partie
        $otherParty = Auth::id() === $contract->tenant_id ? $contract->owner : $contract->tenant;
        Notification::send(
            $otherParty,
            'inventory_report_created',
            'État des lieux créé',
            "Un état des lieux {$inventoryReport->type_name} a été créé pour le contrat de {$contract->property->title}.",
            route('inventory-reports.show', $inventoryReport)
        );

        return redirect()->route('inventory-reports.show', $inventoryReport)
            ->with('success', 'État des lieux créé avec succès. En attente de signature.');
    }

    /**
     * Signer un état des lieux
     */
    public function sign(Request $request, InventoryReport $inventoryReport)
    {
        $user = Auth::user();

        // Vérifier les permissions
        if ($inventoryReport->contract->tenant_id !== $user->id && 
            $inventoryReport->contract->owner_id !== $user->id && 
            !$user->isAdmin()) {
            abort(403);
        }

        if ($inventoryReport->isSigned()) {
            return back()->with('error', 'Cet état des lieux est déjà signé par les deux parties.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Signer selon le rôle
        if ($inventoryReport->contract->tenant_id === $user->id) {
            if ($inventoryReport->tenant_signed) {
                return back()->with('error', 'Vous avez déjà signé cet état des lieux.');
            }
            $inventoryReport->signByTenant($validated['notes'] ?? null);
        } elseif ($inventoryReport->contract->owner_id === $user->id) {
            if ($inventoryReport->owner_signed) {
                return back()->with('error', 'Vous avez déjà signé cet état des lieux.');
            }
            $inventoryReport->signByOwner($validated['notes'] ?? null);
        }

        // Notifier l'autre partie si les deux ont signé
        if ($inventoryReport->isSigned()) {
            $otherParty = $user->id === $inventoryReport->contract->tenant_id 
                ? $inventoryReport->contract->owner 
                : $inventoryReport->contract->tenant;

            Notification::send(
                $otherParty,
                'inventory_report_signed',
                'État des lieux signé',
                "L'état des lieux {$inventoryReport->type_name} pour {$inventoryReport->contract->property->title} a été signé par les deux parties.",
                route('inventory-reports.show', $inventoryReport)
            );
        } else {
            // Notifier l'autre partie qu'une signature est en attente
            $otherParty = $user->id === $inventoryReport->contract->tenant_id 
                ? $inventoryReport->contract->owner 
                : $inventoryReport->contract->tenant;

            Notification::send(
                $otherParty,
                'inventory_report_pending_signature',
                'Signature en attente',
                "L'état des lieux {$inventoryReport->type_name} pour {$inventoryReport->contract->property->title} attend votre signature.",
                route('inventory-reports.show', $inventoryReport)
            );
        }

        return back()->with('success', 'État des lieux signé avec succès.');
    }

    /**
     * Détermine le rôle de l'utilisateur
     */
    protected function resolveRole(InventoryReport $inventoryReport): string
    {
        $user = Auth::user();

        if ($user->id === $inventoryReport->contract->tenant_id) {
            return 'locataire';
        }

        if ($user->id === $inventoryReport->contract->owner_id) {
            return 'proprietaire';
        }

        if ($user->isAdmin()) {
            return 'admin';
        }

        return 'invite';
    }
}

