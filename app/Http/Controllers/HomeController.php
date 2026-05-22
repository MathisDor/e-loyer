<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $featuredProperties = Property::with('owner')
            ->available()
            ->latest()
            ->take(6)
            ->get();

        $cities = Property::available()
            ->select('city')
            ->distinct()
            ->pluck('city');

        $stats = [
            'properties' => Property::approved()->count(),
            'cities' => $cities->count(),
        ];

        return view('home', compact('featuredProperties', 'cities', 'stats'));
    }
}


