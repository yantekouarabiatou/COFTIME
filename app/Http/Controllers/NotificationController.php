<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\LogActivite;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('pages.notifications.index', compact('notifications'));
    }

    // Marquer une notification comme lue (AJAX)
    public function markAsRead(DatabaseNotification $notification)
    {
        $this->authorizeNotification($notification);

        $notification->markAsRead();

        $this->logActivity('update', $notification, 'Notification marquée comme lue');

        return response()->json(['success' => true]);
    }

    // Tout marquer comme lu
    public function markAllAsRead(Request $request)
    {
        $count = auth()->user()->unreadNotifications->count();
        auth()->user()->unreadNotifications->markAsRead();

        $this->logActivity('update', null, "Marquage de {$count} notification(s) comme lues", $count);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'count' => $count]);
        }

        return back()->with('status', 'Toutes les notifications sont marquées comme lues.');
    }

    // Supprimer une notification
    public function destroy(DatabaseNotification $notification)
    {
        $this->authorizeNotification($notification);

        $data = $notification->data;
        $notification->delete();

        $this->logActivity('delete', $notification->id, 'Notification supprimée', $data);

        return response()->json(['success' => true]);
    }

    // Supprimer toutes les notifications
    public function destroyAll()
    {
        $count = auth()->user()->notifications()->count();
        auth()->user()->notifications()->delete();

        $this->logActivity('delete', null, "Suppression totale des notifications ({$count})", $count);

        return back()->with('success', 'Toutes vos notifications ont été supprimées.');
    }

    // Retourne le nombre de non lues (pour AJAX realtime)
    public function unreadCount()
    {
        return response()->json([
            'count' => auth()->user()->unreadNotifications->count()
        ]);
    }

    // Retourne les dernières notifications non lues (pour dropdown realtime)
    public function recent()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(7)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread' => auth()->user()->unreadNotifications->count()
        ]);
    }

    // === MÉTHODES PRIVÉES ===

    private function authorizeNotification(DatabaseNotification $notification)
    {
        if ($notification->notifiable_id !== auth()->id()) {
            abort(403);
        }
    }

    private function logActivity(string $action, $id = null, string $description, $extra = null)
    {
        try {
            LogActivite::create([
                'user_id'           => auth()->id(),
                'action'            => $action,
                'loggable_type'     => 'notifications', // ou DatabaseNotification::class si tu veux polymorphe
                'loggable_id'       => $id,
                'description'       => $description,
                'old_values'        => $action === 'delete' ? ($extra ?? null) : null,
                'new_values'        => is_int($extra) ? ['count' => $extra] : null,
                'ip_address'        => request()->ip(),
                'user_agent'        => request()->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('Échec du log de notification : ' . $e->getMessage());
        }
    }
}