<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\User;
use App\Models\Favorite;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    /**
     * Afficher la liste des propriétés avec filtres
     */
    public function index(Request $request)
    {
        $query = Property::with('owner')->available();

        // Filtres
        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceBetween($request->min_price, $request->max_price);
        }

        if ($request->filled('bedrooms')) {
            $query->withBedrooms((int) $request->bedrooms);
        }

        if ($request->filled('amenities')) {
            $amenities = is_array($request->amenities) ? $request->amenities : [$request->amenities];
            $query->withAmenities($amenities);
        }

        if ($request->filled('neighborhood')) {
            $query->where('neighborhood', 'like', '%' . $request->neighborhood . '%');
        }

        // Tri
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('monthly_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('monthly_price', 'desc');
                break;
            case 'rating':
                $query->withAvg('reviews', 'rating')->orderByDesc('reviews_avg_rating');
                break;
            default:
                $query->latest();
        }

        $properties = $query->paginate(12)->withQueryString();

        $cities = Property::CITIES;
        $types = Property::TYPES;
        $amenities = Property::AMENITIES;

        return view('properties.index', compact('properties', 'cities', 'types', 'amenities'));
    }

    /**
     * Afficher une propriété
     */
    public function show(Property $property)
    {
        // Vérifier que la propriété est visible
        if ($property->status !== 'approuve' && 
            (!Auth::check() || 
             (Auth::id() !== $property->owner_id && 
              !Auth::user()->isAdmin()))) {
            abort(404);
        }

        $property->load(['owner', 'reviews.reviewer']);

        $isFavorite = Auth::check() ? $property->isFavoritedBy(Auth::user()) : false;

        $similarProperties = Property::with('owner')
            ->available()
            ->where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->take(3)
            ->get();

        $tenantActiveContract = null;
        $tenantActiveVisit    = null;

        if (Auth::check() && Auth::user()->isLocataire()) {
            // Contrat actif pour ce bien (le locataire est actuellement locataire)
            $tenantActiveContract = \App\Models\Contract::where('property_id', $property->id)
                ->where('tenant_id', Auth::id())
                ->where('status', 'actif')
                ->first();

            // Visite en cours uniquement si pas de contrat actif
            if (!$tenantActiveContract) {
                $tenantActiveVisit = \App\Models\Visit::where('property_id', $property->id)
                    ->where('tenant_id', Auth::id())
                    ->whereIn('status', ['reservee', 'en_cours', 'terminee'])
                    ->latest()
                    ->first();

                // Si la visite terminée a déjà une décision → on ne la considère plus active
                if ($tenantActiveVisit &&
                    $tenantActiveVisit->status === 'terminee' &&
                    !is_null($tenantActiveVisit->property_accepted)) {
                    $tenantActiveVisit = null;
                }
            }
        }

        return view('properties.show', compact(
            'property', 'isFavorite', 'similarProperties',
            'tenantActiveContract', 'tenantActiveVisit'
        ));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $this->authorize('create', Property::class);

        $cities = Property::CITIES;
        $types = Property::TYPES;
        $amenities = Property::AMENITIES;
        
        return view('properties.create', compact('cities', 'types', 'amenities'));
    }

    /**
     * Enregistrer une nouvelle propriété
     */
    public function store(Request $request)
    {
        $this->authorize('create', Property::class);

        $validated = $request->validate([
            'description'     => ['required', 'string', 'min:30'],
            'type'            => ['required', 'in:' . implode(',', array_keys(Property::TYPES))],
            'bedrooms'        => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms'       => ['required', 'integer', 'min:0', 'max:10'],
            'beds'            => ['nullable', 'integer', 'min:0', 'max:20'],
            'surface'         => ['nullable', 'numeric', 'min:5', 'max:5000'],
            'monthly_price'   => ['required', 'numeric', 'min:10000'],
            'deposit'         => ['nullable', 'numeric', 'min:0'],
            'visit_price'     => ['nullable', 'numeric', 'min:0'],
            'requires_deposit'=> ['nullable', 'boolean'],
            'address'         => ['required', 'string', 'max:500'],
            'city'            => ['required', 'string', 'max:100'],
            'neighborhood'    => ['nullable', 'string', 'max:100'],
            'latitude'        => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'       => ['nullable', 'numeric', 'between:-180,180'],
            'amenities'       => ['nullable', 'array'],
            'images'          => ['required', 'array', 'min:1', 'max:10'],
            'images.*'        => ['image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
        ]);

        // Upload des images
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('properties', 'public');
                $imagePaths[] = $path;
            }
        }

        $user = Auth::user();
        $ownerId = $user->id;
        $prospectorId = null;
        $prospectorValidated = true;



        $autoTitle = Property::generateTitle(
            $validated['type'],
            $validated['city'],
            $validated['neighborhood'] ?? null
        );

        $property = Property::create([
            'owner_id'            => $ownerId,
            'prospector_id'       => $prospectorId,
            'title'               => $autoTitle,
            'description'         => $validated['description'],
            'type'                => $validated['type'],
            'bedrooms'            => $validated['bedrooms'],
            'bathrooms'           => $validated['bathrooms'],
            'beds'                => $validated['beds'] ?? 0,
            'surface'             => $validated['surface'] ?? null,
            'monthly_price'       => $validated['monthly_price'],
            'deposit'             => $validated['deposit'] ?? $validated['monthly_price'],
            'visit_price'         => $validated['visit_price'] ?? null,
            'requires_deposit'    => $request->has('requires_deposit') ? (bool) $request->requires_deposit : true,
            'address'             => $validated['address'],
            'city'                => $validated['city'],
            'neighborhood'        => $validated['neighborhood'] ?? null,
            'latitude'            => $validated['latitude'] ?? null,
            'longitude'           => $validated['longitude'] ?? null,
            'amenities'           => $validated['amenities'] ?? [],
            'images'              => $imagePaths,
            'status'              => 'en_attente',
            'prospector_validated'=> $prospectorValidated,
        ]);

        return redirect()->route('dashboard')
            ->with('success', '✅ Annonce publiée ! Elle est en attente de validation par notre équipe. Elle apparaîtra dans les annonces publiques dès son approbation.');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(Property $property)
    {
        $this->authorize('update', $property);

        $cities = Property::CITIES;
        $types = Property::TYPES;
        $amenities = Property::AMENITIES;

        return view('properties.edit', compact('property', 'cities', 'types', 'amenities'));
    }

    /**
     * Mettre à jour une propriété
     */
    public function update(Request $request, Property $property)
    {
        $this->authorize('update', $property);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:50'],
            'type' => ['required', 'in:' . implode(',', array_keys(Property::TYPES))],
            'bedrooms' => ['required', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['required', 'integer', 'min:0', 'max:10'],
            'surface' => ['nullable', 'numeric', 'min:1'],
            'monthly_price' => ['required', 'numeric', 'min:10000'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'neighborhood' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'amenities' => ['nullable', 'array'],
            'new_images' => ['nullable', 'array', 'max:10'],
            'new_images.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
        ]);

        // Gérer les images
        $currentImages = $property->images ?? [];
        
        // Supprimer les images sélectionnées
        if ($request->filled('remove_images')) {
            foreach ($request->remove_images as $imagePath) {
                Storage::disk('public')->delete($imagePath);
                $currentImages = array_diff($currentImages, [$imagePath]);
            }
        }

        // Ajouter les nouvelles images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image->store('properties', 'public');
                $currentImages[] = $path;
            }
        }

        $property->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'bedrooms' => $validated['bedrooms'],
            'bathrooms' => $validated['bathrooms'],
            'surface' => $validated['surface'],
            'monthly_price' => $validated['monthly_price'],
            'deposit' => $validated['deposit'] ?? $validated['monthly_price'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'neighborhood' => $validated['neighborhood'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'amenities' => $validated['amenities'] ?? [],
            'images' => array_values($currentImages),
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Propriété mise à jour avec succès.');
    }

    /**
     * Supprimer une propriété
     */
    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        // Supprimer les images
        foreach ($property->images ?? [] as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $property->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Propriété supprimée avec succès.');
    }

    /**
     * Ajouter/Retirer des favoris
     */
    public function toggleFavorite(Property $property)
    {
        $added = Favorite::toggle(Auth::user(), $property);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'favorited' => $added,
                'message' => $added ? 'Ajouté aux favoris' : 'Retiré des favoris',
            ]);
        }

        return back()->with('success', $added ? 'Ajouté aux favoris' : 'Retiré des favoris');
    }

    /**
     * Recherche sur carte (API)
     */
    public function mapSearch(Request $request)
    {
        $query = Property::available()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($request->filled('bounds')) {
            $bounds = $request->bounds;
            $query->whereBetween('latitude', [$bounds['south'], $bounds['north']])
                  ->whereBetween('longitude', [$bounds['west'], $bounds['east']]);
        }

        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        $properties = $query->get(['id', 'title', 'monthly_price', 'latitude', 'longitude', 'images', 'type']);

        return response()->json($properties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'price' => $property->formatted_price,
                'lat' => (float) $property->latitude,
                'lng' => (float) $property->longitude,
                'image' => $property->main_image,
                'type' => $property->type_name,
                'url' => route('properties.show', $property),
            ];
        }));
    }
}

