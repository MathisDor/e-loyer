<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste des notifications
     */
    public function index()
    {
        $notifications = Notification::forUser(Auth::id())
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        Notification::markAllAsRead(Auth::user());

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     */
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification supprimée.');
    }

    /**
     * Récupérer le nombre de notifications non lues (API)
     */
    public function unreadCount()
    {
        $count = Notification::forUser(Auth::id())->unread()->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Dernières notifications (API pour header)
     */
    public function latest()
    {
        $notifications = Notification::forUser(Auth::id())
            ->latest()
            ->take(5)
            ->get();

        return response()->json($notifications);
    }
}


