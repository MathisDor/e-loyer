<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\ContractController;
use Illuminate\Support\Facades\Route;

// ============ PAGES PUBLIQUES ============

Route::get('/', [HomeController::class, 'index'])->name('home');

// Propriétés - Routes spécifiques AVANT les routes avec paramètres
Route::get('/proprietes', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/proprietes/carte/recherche', [PropertyController::class, 'mapSearch'])->name('properties.map');
Route::get('/proprietes/{property}', [PropertyController::class, 'show'])->name('properties.show')->where('property', '[0-9]+');

// ============ ROUTES AUTHENTIFIÉES ============

Route::middleware('auth')->group(function () {
    
    // Dashboard principal (redirige selon le type)
    Route::get('/tableau-de-bord', [DashboardController::class, 'index'])->name('dashboard');
    
    // ============ PROFIL ============
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profil/mot-de-passe', [ProfileController::class, 'password'])->name('profile.password');
    Route::put('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profil/piece-identite', [ProfileController::class, 'uploadIdCard'])->name('profile.id-card');
    Route::post('/profil/bulletin-salaire', [ProfileController::class, 'uploadPaySlip'])->name('profile.pay-slip');
    Route::post('/profil/contrat-travail', [ProfileController::class, 'uploadEmploymentContract'])->name('profile.employment-contract');
    Route::post('/profil/justificatif-domicile', [ProfileController::class, 'uploadProofOfAddress'])->name('profile.proof-of-address');
    Route::post('/profil/releve-bancaire', [ProfileController::class, 'uploadBankStatement'])->name('profile.bank-statement');
    Route::post('/profil/titre-propriete', [ProfileController::class, 'uploadPropertyTitle'])->name('profile.property-title');
    Route::post('/profil/registre-commerce', [ProfileController::class, 'uploadBusinessRegistration'])->name('profile.business-registration');
    Route::get('/profil/favoris', [ProfileController::class, 'favorites'])->name('profile.favorites');
    
    Route::get('/utilisateur/{user}', [ProfileController::class, 'public'])->name('user.profile');
    
    // ============ PROPRIÉTÉS ============
    Route::get('/proprietes/ajouter', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/proprietes', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/proprietes/{property}/modifier', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/proprietes/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/proprietes/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::post('/proprietes/{property}/favori', [PropertyController::class, 'toggleFavorite'])->name('properties.favorite');
    
    // ============ RÉSERVATIONS ============
    Route::get('/proprietes/{property}/reserver', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/proprietes/{property}/reserver', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/reservations/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/reservations/{booking}/accepter', [BookingController::class, 'accept'])->name('bookings.accept');
    Route::post('/reservations/{booking}/refuser', [BookingController::class, 'reject'])->name('bookings.reject');
    Route::post('/reservations/{booking}/annuler', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/reservations/{booking}/paiement', [BookingController::class, 'payment'])->name('bookings.payment');
    Route::post('/reservations/{booking}/payer', [BookingController::class, 'processPayment'])->name('bookings.pay');
    Route::post('/reservations/{booking}/activer', [BookingController::class, 'activate'])->name('bookings.activate');
    Route::post('/reservations/{booking}/terminer', [BookingController::class, 'complete'])->name('bookings.complete');
    
    // ============ VISITES ============
    Route::get('/mes-visites', [VisitController::class, 'tenantIndex'])->name('visits.tenant');
    Route::get('/proprietes/{property}/visite', [VisitController::class, 'create'])->name('visits.create');
    Route::post('/proprietes/{property}/visite', [VisitController::class, 'store'])->name('visits.store');
    Route::get('/visites/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::get('/visites/{visit}/paiement', [VisitController::class, 'payment'])->name('visits.payment');
    Route::post('/visites/{visit}/payer', [VisitController::class, 'processPayment'])->name('visits.pay');
    Route::post('/visites/{visit}/demarrer', [VisitController::class, 'start'])->name('visits.start');
    Route::post('/visites/{visit}/valider', [VisitController::class, 'validateVisit'])->name('visits.validate');
    Route::post('/visites/{visit}/terminer', [VisitController::class, 'complete'])->name('visits.complete');
    Route::get('/visites/{visit}/premier-versement', [VisitController::class, 'firstPayment'])->name('visits.payment.first');
    Route::post('/visites/{visit}/premier-versement', [VisitController::class, 'processFirstPayment'])->name('visits.pay.first');
    Route::post('/visites/{visit}/statut', [VisitController::class, 'updateStatus'])->name('visits.status');
    // Vues dédiées par rôle
    Route::get('/proprietaire/visites', [VisitController::class, 'ownerIndex'])->middleware('user.type:proprietaire')->name('visits.owner');
    
    // ============ CONTRATS ============
    Route::get('/contrats/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contrats/{contract}/paiement-mensuel', [ContractController::class, 'payMonthly'])->name('contracts.pay.monthly');
    Route::post('/contrats/{contract}/renouveler', [ContractController::class, 'renew'])->name('contracts.renew');
    Route::post('/contrats/{contract}/resilier', [ContractController::class, 'requestTermination'])->name('contracts.terminate.request');
    Route::post('/contrats/{contract}/resilier/accepter', [ContractController::class, 'acceptTermination'])->name('contracts.terminate.accept');
    Route::post('/contrats/{contract}/resilier/annuler', [ContractController::class, 'cancelTermination'])->name('contracts.terminate.cancel');
    
    // ============ ÉTATS DES LIEUX ============
    Route::get('/contrats/{contract}/etat-des-lieux/entree', [App\Http\Controllers\InventoryReportController::class, 'createEntry'])->name('inventory-reports.create.entry');
    Route::get('/contrats/{contract}/etat-des-lieux/sortie', [App\Http\Controllers\InventoryReportController::class, 'createExit'])->name('inventory-reports.create.exit');
    Route::post('/contrats/{contract}/etat-des-lieux', [App\Http\Controllers\InventoryReportController::class, 'store'])->name('inventory-reports.store');
    Route::get('/etat-des-lieux/{inventoryReport}', [App\Http\Controllers\InventoryReportController::class, 'show'])->name('inventory-reports.show');
    Route::post('/etat-des-lieux/{inventoryReport}/signer', [App\Http\Controllers\InventoryReportController::class, 'sign'])->name('inventory-reports.sign');
    
    // ============ MESSAGERIE ============
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::post('/nouvelle', [MessageController::class, 'create'])->name('create');
        Route::get('/{conversation}', [MessageController::class, 'show'])->name('show');
        Route::post('/{conversation}', [MessageController::class, 'store'])->name('store');
        Route::post('/propriete/{property}/contact', [MessageController::class, 'contactOwner'])->name('contact');
        Route::post('/marquer-lus', [MessageController::class, 'markAllAsRead'])->name('mark-read');
    });
    
    // ============ AVIS ============
    Route::get('/reservations/{booking}/avis', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reservations/{booking}/avis', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/proprietes/{property}/avis', [ReviewController::class, 'propertyReviews'])->name('reviews.property');
    
    // ============ NOTIFICATIONS ============
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/lue', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/tout-lire', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/nombre', [NotificationController::class, 'unreadCount'])->name('count');
        Route::get('/recentes', [NotificationController::class, 'latest'])->name('latest');
    });
    
    // ============ DASHBOARD LOCATAIRE ============
    Route::prefix('locataire')->name('dashboard.tenant.')->middleware('user.type:locataire')->group(function () {
        Route::get('/reservations', [App\Http\Controllers\Tenant\BookingController::class, 'index'])->name('bookings');
        Route::get('/reservations/{booking}', [App\Http\Controllers\Tenant\BookingController::class, 'show'])->name('bookings.show');
        Route::get('/paiements', [App\Http\Controllers\Tenant\BookingController::class, 'payments'])->name('payments');
        Route::post('/reservations/{booking}/loyer', [App\Http\Controllers\Tenant\BookingController::class, 'payRent'])->name('bookings.pay-rent');
    });
    
    // ============ DASHBOARD PROPRIÉTAIRE ============
    Route::prefix('proprietaire')->name('dashboard.owner.')->middleware('user.type:proprietaire')->group(function () {
        Route::get('/proprietes', [App\Http\Controllers\Owner\PropertyController::class, 'index'])->name('properties');
        Route::get('/proprietes/a-valider', [App\Http\Controllers\Owner\PropertyController::class, 'toValidate'])->name('properties.validate');
        Route::post('/proprietes/{property}/valider', [App\Http\Controllers\Owner\PropertyController::class, 'validateProperty'])->name('properties.validate.confirm');
        Route::post('/proprietes/{property}/rejeter', [App\Http\Controllers\Owner\PropertyController::class, 'rejectProperty'])->name('properties.validate.reject');
        Route::post('/proprietes/{property}/disponibilite', [App\Http\Controllers\Owner\PropertyController::class, 'toggleAvailability'])->name('properties.availability');
        
        Route::get('/reservations', [App\Http\Controllers\Owner\BookingController::class, 'index'])->name('bookings');
        Route::get('/reservations/{booking}', [App\Http\Controllers\Owner\BookingController::class, 'show'])->name('bookings.show');
        Route::get('/calendrier', [App\Http\Controllers\Owner\BookingController::class, 'calendar'])->name('calendar');
    });
    

    // ============ DASHBOARD DÉMARCHEUR ============
    Route::prefix('demarcheur')->name('dashboard.demarcheur.')->middleware('user.type:demarcheur')->group(function () {
        // Biens prospectés
        Route::get('/proprietes', [App\Http\Controllers\Prospector\PropertyController::class, 'index'])->name('properties');
        Route::get('/proprietes/{property}/lier', [App\Http\Controllers\Prospector\PropertyController::class, 'linkForm'])->name('properties.link');
        Route::post('/proprietes/{property}/lier', [App\Http\Controllers\Prospector\PropertyController::class, 'link'])->name('properties.link.store');
        Route::get('/proprietes/rechercher-proprietaires', [App\Http\Controllers\Prospector\PropertyController::class, 'searchOwners'])->name('properties.search-owners');

        // Commissions
        Route::get('/commissions', [App\Http\Controllers\Prospector\CommissionController::class, 'index'])->name('commissions');
        Route::get('/commissions/historique', [App\Http\Controllers\Prospector\CommissionController::class, 'history'])->name('commissions.history');

        // Retraits
        Route::get('/retraits', [App\Http\Controllers\Prospector\WithdrawalController::class, 'index'])->name('withdrawals');
        Route::post('/retraits', [App\Http\Controllers\Prospector\WithdrawalController::class, 'store'])->name('withdrawals.store');
        Route::delete('/retraits/{withdrawal}', [App\Http\Controllers\Prospector\WithdrawalController::class, 'cancel'])->name('withdrawals.cancel');

        // Classement
        Route::get('/classement', [App\Http\Controllers\Prospector\RankingController::class, 'index'])->name('ranking');
    });

    // ============ ADMIN — Retraits démarcheurs ============

    // ============ DASHBOARD ADMIN ============
    Route::prefix('admin')->name('admin.')->middleware('user.type:admin')->group(function () {
        // Propriétés
        Route::get('/proprietes', [App\Http\Controllers\Admin\PropertyController::class, 'index'])->name('properties.index');
        Route::get('/proprietes/en-attente', [App\Http\Controllers\Admin\PropertyController::class, 'pending'])->name('properties.pending');
        Route::get('/proprietes/{property}', [App\Http\Controllers\Admin\PropertyController::class, 'show'])->name('properties.show');
        Route::post('/proprietes/{property}/approuver', [App\Http\Controllers\Admin\PropertyController::class, 'approve'])->name('properties.approve');
        Route::post('/proprietes/{property}/rejeter', [App\Http\Controllers\Admin\PropertyController::class, 'reject'])->name('properties.reject');
        Route::delete('/proprietes/{property}', [App\Http\Controllers\Admin\PropertyController::class, 'destroy'])->name('properties.destroy');
        
        // Utilisateurs
        Route::get('/utilisateurs', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/utilisateurs/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
        Route::post('/utilisateurs/{user}/verifier', [App\Http\Controllers\Admin\UserController::class, 'verify'])->name('users.verify');
        Route::post('/utilisateurs/{user}/retirer-verification', [App\Http\Controllers\Admin\UserController::class, 'unverify'])->name('users.unverify');
        Route::put('/utilisateurs/{user}/type', [App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.type');
        Route::post('/utilisateurs/{user}/suspendre', [App\Http\Controllers\Admin\UserController::class, 'suspend'])->name('users.suspend');
        Route::post('/utilisateurs/{user}/reactiver', [App\Http\Controllers\Admin\UserController::class, 'unsuspend'])->name('users.unsuspend');
        Route::delete('/utilisateurs/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
        
        // Avis
        Route::get('/avis', [App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('/avis/en-attente', [App\Http\Controllers\Admin\ReviewController::class, 'pending'])->name('reviews.pending');
        Route::post('/avis/{review}/approuver', [App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/avis/{review}/rejeter', [App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reviews.reject');
        Route::delete('/avis/{review}', [App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
        
        // Commissions
        Route::get('/commissions', [App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('commissions.index');
        Route::post('/commissions/{commission}/payer', [App\Http\Controllers\Admin\CommissionController::class, 'pay'])->name('commissions.pay');
        Route::post('/commissions/payer-multiple', [App\Http\Controllers\Admin\CommissionController::class, 'payBulk'])->name('commissions.pay-bulk');

        // Retraits démarcheurs
        Route::get('/retraits-demarcheurs', [App\Http\Controllers\Admin\WithdrawalController::class, 'index'])->name('withdrawals.index');
        Route::post('/retraits-demarcheurs/{withdrawal}/approuver', [App\Http\Controllers\Admin\WithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/retraits-demarcheurs/{withdrawal}/rejeter', [App\Http\Controllers\Admin\WithdrawalController::class, 'reject'])->name('withdrawals.reject');

        // Démarcheurs
        Route::get('/demarcheurs', [App\Http\Controllers\Admin\ProspectorController::class, 'index'])->name('prospectors.index');
        Route::post('/demarcheurs/{user}/suspendre', [App\Http\Controllers\Admin\ProspectorController::class, 'suspend'])->name('prospectors.suspend');
        Route::post('/demarcheurs/{user}/reactiver', [App\Http\Controllers\Admin\ProspectorController::class, 'unsuspend'])->name('prospectors.unsuspend');
    });
});

require __DIR__.'/auth.php';
