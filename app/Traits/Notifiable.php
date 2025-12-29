<?php

namespace App\Traits;

use App\Models\Notification;

trait Notifiable
{
    /**
     * Relation avec les notifications
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Créer une notification - 2 paramètres maximum
     */
    public function notify($type, $data = [])
    {
        return $this->notifications()->create([
            'type' => $type,
            'data' => array_merge([
                'timestamp' => now()->toISOString(),
            ], $data)
        ]);
    }

    /**
     * Récupérer les notifications non lues
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    /**
     * Récupérer les notifications lues
     */
    public function readNotifications()
    {
        return $this->notifications()->read();
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead()
    {
        $this->unreadNotifications()->update(['read_at' => now()]);
    }

    /**
     * Supprimer toutes les notifications
     */
    public function clearAllNotifications()
    {
        $this->notifications()->delete();
    }

    /**
     * Compter les notifications non lues
     */
    public function unreadNotificationsCount()
    {
        return $this->unreadNotifications()->count();
    }
}
