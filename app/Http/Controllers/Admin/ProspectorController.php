<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ProspectorController extends Controller
{
    public function index()
    {
        $prospectors = User::where('user_type', 'demarcheur')
            ->withCount(['prospectedProperties', 'commissions'])
            ->withSum('commissions', 'amount')
            ->orderByDesc('total_earnings')
            ->paginate(20);

        $stats = [
            'total'     => User::where('user_type', 'demarcheur')->count(),
            'active'    => User::where('user_type', 'demarcheur')->where('is_suspended', false)->count(),
            'suspended' => User::where('user_type', 'demarcheur')->where('is_suspended', true)->count(),
            'total_commissions_paid' => \App\Models\Commission::where('status', 'payee')->sum('amount'),
        ];

        return view('admin.prospectors.index', compact('prospectors', 'stats'));
    }

    public function suspend(Request $request, User $user)
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $user->update(['is_suspended' => true, 'suspension_reason' => $request->reason]);

        return back()->with('success', "Démarcheur {$user->name} suspendu.");
    }

    public function unsuspend(User $user)
    {
        $user->update(['is_suspended' => false, 'suspension_reason' => null]);

        return back()->with('success', "Démarcheur {$user->name} réactivé.");
    }
}
