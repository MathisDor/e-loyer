<?php

namespace App\Http\Controllers\Prospector;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RankingController extends Controller
{
    /**
     * Classement des meilleurs démarcheurs.
     */
    public function index()
    {
        $topProspectors = User::where('user_type', 'demarcheur')
            ->where('is_suspended', false)
            ->orderByDesc('total_earnings')
            ->take(20)
            ->get();

        $currentRank = $topProspectors->search(fn ($u) => $u->id === Auth::id());
        $currentRank = $currentRank !== false ? $currentRank + 1 : null;

        // Si le démarcheur n'est pas dans le top 20, calculer son rang global
        if ($currentRank === null) {
            $currentRank = User::where('user_type', 'demarcheur')
                ->where('total_earnings', '>', Auth::user()->total_earnings)
                ->count() + 1;
        }

        return view('prospector.ranking', compact('topProspectors', 'currentRank'));
    }
}
