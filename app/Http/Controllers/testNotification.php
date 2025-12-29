<?php

namespace App\Http\Controllers;

// Exemple d'utilisation dans un contrôleur
class OrderController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function store(Request $request)
    {
        $order = Order::create($request->all());

        // Notification à l'utilisateur
        $this->notificationService->sendNewOrderNotification($order);

        // Notification aux administrateurs
        $this->notificationService->notifyAdmins(
            NotificationService::TYPE_ORDER_CREATED,
            [
                'message' => "Nouvelle commande #{$order->id} créée par {$order->user->name}",
                'action_url' => "/admin/orders/{$order->id}",
                'icon' => 'shopping-cart'
            ],
            $order
        );

        return redirect()->back()->with('success', 'Commande créée !');
    }
}
