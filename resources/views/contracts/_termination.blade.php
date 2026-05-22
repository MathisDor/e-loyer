{{--
    Composant résiliation de contrat
    Variables attendues : $contract, $role ('locataire' | 'proprietaire')
--}}
@php
    $isLocataire   = $role === 'locataire';
    $isProprietaire = $role === 'proprietaire';
    $otherRole     = $isLocataire ? 'propriétaire' : 'locataire';
    $noticeMois    = $isLocataire ? \App\Models\Contract::NOTICE_TENANT_MONTHS : \App\Models\Contract::NOTICE_OWNER_MONTHS;
    $hasRequest    = $contract->hasTerminationRequest();
    $myRequest     = $hasRequest && $contract->termination_requested_by === $role;
    $theirRequest  = $hasRequest && $contract->termination_requested_by !== $role;
    $reasons       = \App\Models\Contract::TERMINATION_REASONS[$role];
    $effectiveDate = $contract->termination_effective_date;
    $isResilie     = $contract->status === 'resilie';
@endphp

<div class="mt-8" x-data="{ showForm: false, showRules: false }">

    {{-- ── Titre section ─────────────────────────────────────── --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 bg-red-100 rounded-2xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-file-contract text-red-600"></i>
        </div>
        <div>
            <h3 class="font-bold text-gray-900">Résiliation du contrat</h3>
            <p class="text-xs text-gray-500">Droits et procédures de résiliation</p>
        </div>
    </div>

    {{-- ── Contrat déjà résilié ─────────────────────────────── --}}
    @if($isResilie)
    <div class="p-5 bg-red-50 border border-red-200 rounded-2xl">
        <div class="flex items-center gap-3 mb-3">
            <i class="fas fa-ban text-red-600 text-xl"></i>
            <div>
                <p class="font-bold text-red-800">Contrat résilié</p>
                @if($effectiveDate)
                <p class="text-xs text-red-600">Date effective : {{ $effectiveDate->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>
        @if($contract->termination_reason)
        <p class="text-sm text-red-700">
            <span class="font-semibold">Motif :</span> {{ $contract->termination_reason_label }}
        </p>
        @endif
        @if($contract->deposit_amount > 0)
        <div class="mt-3 p-3 bg-white rounded-xl border border-red-100 text-xs text-gray-700">
            <p class="font-bold text-gray-800 mb-1"><i class="fas fa-shield-alt text-amber-500 mr-1"></i>Caution</p>
            @if($contract->deposit_refundable)
            <p class="text-emerald-700"><i class="fas fa-check-circle mr-1"></i>Préavis respecté — caution restituable sous 30 jours.</p>
            @else
            <p class="text-red-700"><i class="fas fa-exclamation-triangle mr-1"></i>Préavis non respecté — caution potentiellement retenue.</p>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Demande en attente (de moi) ──────────────────────── --}}
    @elseif($myRequest)
    <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="flex items-center justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-amber-600"></i>
                </span>
                <div>
                    <p class="font-bold text-amber-800">Demande de résiliation en cours</p>
                    <p class="text-xs text-amber-600">En attente de l'accord du {{ $otherRole }}</p>
                </div>
            </div>
            <form action="{{ route('contracts.terminate.cancel', $contract) }}" method="POST">
                @csrf
                <button type="submit" class="px-3 py-2 bg-white border border-amber-300 text-amber-700 text-xs font-bold rounded-xl hover:bg-amber-100 transition-colors">
                    <i class="fas fa-times mr-1"></i>Retirer
                </button>
            </form>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-2.5 bg-white rounded-xl border border-amber-100">
                <p class="text-gray-500">Motif</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $contract->termination_reason_label }}</p>
            </div>
            <div class="p-2.5 bg-white rounded-xl border border-amber-100">
                <p class="text-gray-500">Date effective</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $effectiveDate?->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>
        @if($contract->termination_details)
        <p class="mt-3 text-xs text-amber-700 bg-amber-100 rounded-xl p-2.5 italic">"{{ $contract->termination_details }}"</p>
        @endif
    </div>

    {{-- ── Demande de l'autre partie (à accepter/refuser) ─────── --}}
    @elseif($theirRequest)
    <div class="p-5 bg-orange-50 border border-orange-200 rounded-2xl">
        <div class="flex items-center gap-3 mb-3">
            <span class="w-9 h-9 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-orange-600"></i>
            </span>
            <div>
                <p class="font-bold text-orange-800">
                    Demande de résiliation reçue du {{ $otherRole }}
                </p>
                <p class="text-xs text-orange-600">
                    Déposée le {{ $contract->termination_requested_at?->format('d/m/Y') }} ·
                    Préavis : {{ $noticeMois }} mois
                </p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs mb-4">
            <div class="p-2.5 bg-white rounded-xl border border-orange-100">
                <p class="text-gray-500">Motif</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $contract->termination_reason_label }}</p>
            </div>
            <div class="p-2.5 bg-white rounded-xl border border-orange-100">
                <p class="text-gray-500">Date effective demandée</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $effectiveDate?->format('d/m/Y') ?? '—' }}</p>
            </div>
        </div>
        @if($contract->termination_details)
        <p class="text-xs text-gray-600 italic bg-white rounded-xl p-2.5 border border-orange-100 mb-4">"{{ $contract->termination_details }}"</p>
        @endif
        <div class="flex gap-2">
            <form action="{{ route('contracts.terminate.accept', $contract) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700 transition-colors">
                    <i class="fas fa-check mr-1"></i>Accepter la résiliation
                </button>
            </form>
            <form action="{{ route('contracts.terminate.cancel', $contract) }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full py-2.5 bg-gray-100 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-200 transition-colors">
                    <i class="fas fa-times mr-1"></i>Refuser
                </button>
            </form>
        </div>
    </div>

    {{-- ── Pas de demande — contrat actif ───────────────────── --}}
    @elseif($contract->status === 'actif')

    {{-- Règles légales (accordéon) --}}
    <div class="mb-4 border border-gray-200 rounded-2xl overflow-hidden">
        <button @click="showRules = !showRules"
                class="w-full flex items-center justify-between px-4 py-3.5 bg-gray-50 hover:bg-gray-100 transition-colors text-left">
            <div class="flex items-center gap-2">
                <i class="fas fa-balance-scale text-blue-600 text-sm"></i>
                <span class="font-semibold text-gray-800 text-sm">Conditions légales de résiliation</span>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200" :class="showRules ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="showRules" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="p-4 bg-white space-y-4 border-t border-gray-100">

            {{-- Locataire --}}
            <div class="rounded-2xl overflow-hidden border border-blue-100">
                <div class="px-4 py-3 bg-blue-50 flex items-center gap-2">
                    <i class="fas fa-user text-blue-600 text-sm"></i>
                    <p class="font-bold text-blue-800 text-sm">Droits du locataire</p>
                </div>
                <div class="p-4 space-y-2.5 text-xs text-gray-700">
                    <div class="flex gap-2">
                        <i class="fas fa-clock text-blue-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Préavis :</span> <span class="text-blue-700 font-bold">1 mois</span> minimum avant le départ effectif.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-file-alt text-blue-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Forme :</span> La demande doit être faite par écrit (notification sur la plateforme = preuve légale).</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-money-bill-wave text-blue-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Loyer pendant le préavis :</span> Le locataire reste redevable du loyer pendant toute la durée du préavis.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-shield-alt text-blue-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Caution :</span> Restituée <span class="font-bold">sous 30 jours</span> après la remise des clés si le préavis est respecté et sans dommages.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Préavis non respecté :</span> La caution peut être retenue totalement ou partiellement.</p>
                    </div>
                </div>
            </div>

            {{-- Propriétaire --}}
            <div class="rounded-2xl overflow-hidden border border-emerald-100">
                <div class="px-4 py-3 bg-emerald-50 flex items-center gap-2">
                    <i class="fas fa-key text-emerald-600 text-sm"></i>
                    <p class="font-bold text-emerald-800 text-sm">Droits du propriétaire</p>
                </div>
                <div class="p-4 space-y-2.5 text-xs text-gray-700">
                    <div class="flex gap-2">
                        <i class="fas fa-clock text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Préavis :</span> <span class="text-emerald-700 font-bold">3 mois</span> minimum pour protéger le locataire.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-list-ul text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Motifs légitimes :</span> Fin de bail, non-paiement répété des loyers, nuisances graves.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-home text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Obligation :</span> Le locataire a le droit d'occuper le logement jusqu'à la date effective.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-hand-holding-usd text-emerald-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Caution :</span> Doit être restituée sous 30 jours après départ si aucun dommage constaté.</p>
                    </div>
                    <div class="flex gap-2">
                        <i class="fas fa-ban text-red-500 mt-0.5 flex-shrink-0"></i>
                        <p><span class="font-semibold">Interdit :</span> Résiliation sans motif valable, coupure des services, harcèlement locatif.</p>
                    </div>
                </div>
            </div>

            {{-- Infos communes --}}
            <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs text-gray-600">
                <p class="font-bold text-gray-800 mb-1.5"><i class="fas fa-info-circle text-gray-500 mr-1"></i>État des lieux</p>
                <p>Un état des lieux de sortie est obligatoire avant la remise des clés. Il sert de base pour l'évaluation des éventuels dommages.</p>
            </div>
        </div>
    </div>

    {{-- Bouton demander résiliation --}}
    <button @click="showForm = !showForm"
            class="w-full py-3 border-2 border-red-200 text-red-600 rounded-2xl font-bold text-sm hover:bg-red-50 hover:border-red-400 transition-all flex items-center justify-center gap-2">
        <i class="fas fa-file-contract"></i>
        <span x-text="showForm ? 'Annuler' : '{{ $isLocataire ? 'Demander à quitter le logement' : 'Donner congé au locataire' }}'"></span>
    </button>

    {{-- Formulaire résiliation --}}
    <div x-show="showForm" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-4 p-5 bg-red-50 border border-red-200 rounded-2xl">

        <div class="flex items-center gap-2 mb-4">
            <i class="fas fa-exclamation-triangle text-red-500"></i>
            <p class="font-bold text-red-800 text-sm">
                {{ $isLocataire ? 'Demande de départ — préavis 1 mois' : 'Donner congé — préavis 3 mois' }}
            </p>
        </div>

        {{-- Alerte préavis --}}
        <div class="mb-4 p-3 bg-white border border-red-100 rounded-xl text-xs text-gray-700">
            @if($isLocataire)
            <p><i class="fas fa-calendar-check text-blue-500 mr-1"></i>
                Date de départ effective : <span class="font-bold text-blue-700">{{ now()->addMonth()->format('d/m/Y') }}</span>
            </p>
            <p class="mt-1 text-gray-500">Vous resterez redevable du loyer jusqu'au {{ now()->addMonth()->format('d/m/Y') }}.</p>
            @else
            <p><i class="fas fa-calendar-check text-emerald-500 mr-1"></i>
                Date de départ effective : <span class="font-bold text-emerald-700">{{ now()->addMonths(3)->format('d/m/Y') }}</span>
            </p>
            <p class="mt-1 text-gray-500">Le locataire reste dans le logement jusqu'au {{ now()->addMonths(3)->format('d/m/Y') }}.</p>
            @endif
        </div>

        <form action="{{ route('contracts.terminate.request', $contract) }}" method="POST" class="space-y-4">
            @csrf

            {{-- Motif --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">Motif de résiliation <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($reasons as $key => $label)
                    <label class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl cursor-pointer hover:border-red-400 transition-colors">
                        <input type="radio" name="termination_reason" value="{{ $key }}" required
                               class="text-red-600 focus:ring-red-500">
                        <span class="text-sm text-gray-700 font-medium">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Détails --}}
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-2">
                    Explication détaillée <span class="text-red-500">*</span>
                    <span class="text-gray-400 font-normal">(min. 20 caractères)</span>
                </label>
                <textarea name="termination_details" rows="4" required minlength="20"
                          class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl focus:border-red-400 focus:ring-2 focus:ring-red-400/20 transition-all resize-none"
                          placeholder="{{ $isLocataire
                              ? 'Expliquez votre situation : déménagement, changement de travail, acquisition d\'un bien propre...'
                              : 'Expliquez le motif : vente du bien, travaux majeurs, non-paiement répété...' }}"></textarea>
                @error('termination_details')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            {{-- Confirmation --}}
            <div class="p-3 bg-white border border-red-200 rounded-xl">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="mt-0.5 text-red-600 focus:ring-red-500 flex-shrink-0">
                    <span class="text-xs text-gray-700">
                        Je confirme avoir pris connaissance des conditions légales de résiliation et m'engage à respecter le préavis de
                        <span class="font-bold text-red-600">{{ $noticeMois }} mois</span>.
                    </span>
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3.5 bg-red-600 text-white font-bold rounded-xl hover:bg-red-700 transition-colors text-sm">
                <i class="fas fa-paper-plane mr-2"></i>
                {{ $isLocataire ? 'Envoyer la demande de départ' : 'Envoyer le congé' }}
            </button>
        </form>
    </div>

    @endif
</div>
