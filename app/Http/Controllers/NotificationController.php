<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    /**
     * Afficher toutes les notifications de l'utilisateur connecté
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $notifications = Auth::user()->customNotifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Marquer une notification comme lue
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead($id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit d\'accéder à cette notification.');
        }
        
        $notification->markAsRead();
        
        return redirect()->back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Marquer toutes les notifications comme lues
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAllAsRead(): RedirectResponse
    {
        Auth::user()->customNotifications()->unread()->update(['is_read' => true]);
        
        return redirect()->back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        
        // Vérifier que la notification appartient à l'utilisateur connecté
        if ($notification->user_id !== Auth::id()) {
            abort(403, 'Vous n\'avez pas le droit de supprimer cette notification.');
        }
        
        $notification->delete();
        
        return redirect()->back()->with('success', 'Notification supprimée avec succès.');
    }

    /**
     * Supprimer toutes les notifications lues
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroyAllRead(): RedirectResponse
    {
        Auth::user()->customNotifications()->read()->delete();
        
        return redirect()->back()->with('success', 'Toutes les notifications lues ont été supprimées.');
    }
}
