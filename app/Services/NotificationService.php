<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Créer une nouvelle notification
     *
     * @param User $user Utilisateur qui recevra la notification
     * @param string $type Type de notification (reservation, order, review, etc.)
     * @param string $message Message de la notification
     * @param mixed $notifiable Élément associé à la notification (reservation, order, review, etc.)
     * @return Notification
     */
    public function create(User $user, string $type, string $message, $notifiable = null): Notification
    {
        $notification = new Notification([
            'user_id' => $user->id,
            'type' => $type,
            'message' => $message,
            'is_read' => false,
        ]);
        
        if ($notifiable) {
            $notification->notifiable()->associate($notifiable);
        }
        
        $notification->save();
        
        return $notification;
    }
    
    /**
     * Envoyer une notification pour une nouvelle réservation
     *
     * @param \App\Models\Reservation $reservation
     * @return Notification
     */
    public function sendReservationNotification($reservation): Notification
    {
        // Notification au client
        $message = "Votre réservation au restaurant {$reservation->restaurant->name} pour le {$reservation->date_time->format('d/m/Y à H:i')} a été confirmée.";
        
        return $this->create(
            $reservation->user,
            'reservation',
            $message,
            $reservation
        );
    }
    
    /**
     * Envoyer une notification pour une commande
     *
     * @param \App\Models\Order $order
     * @param string $status Statut de la commande
     * @return Notification
     */
    public function sendOrderNotification($order, $status): Notification
    {
        $statusMessages = [
            'pending' => "Votre commande #{$order->id} est en attente de confirmation.",
            'confirmed' => "Votre commande #{$order->id} a été confirmée et est en cours de préparation.",
            'ready' => "Votre commande #{$order->id} est prête ! Vous pouvez venir la récupérer.",
            'delivered' => "Votre commande #{$order->id} a été livrée. Bon appétit !",
            'cancelled' => "Votre commande #{$order->id} a été annulée.",
        ];
        
        $message = $statusMessages[$status] ?? "Le statut de votre commande #{$order->id} a été mis à jour.";
        
        return $this->create(
            $order->user,
            'order',
            $message,
            $order
        );
    }
    
    /**
     * Envoyer une notification pour un nouvel avis
     *
     * @param \App\Models\Review $review
     * @return Notification
     */
    public function sendReviewNotification($review): Notification
    {
        // Notification au restaurateur
        $restaurateur = $review->restaurant->user;
        $message = "Un nouvel avis a été publié pour votre restaurant {$review->restaurant->name}.";
        
        return $this->create(
            $restaurateur,
            'review',
            $message,
            $review
        );
    }
    
    /**
     * Envoyer une notification pour un avis approuvé
     *
     * @param \App\Models\Review $review
     * @return Notification
     */
    public function sendReviewApprovedNotification($review): Notification
    {
        // Notification à l'utilisateur qui a laissé l'avis
        $message = "Votre avis pour le restaurant {$review->restaurant->name} a été approuvé et est maintenant visible.";
        
        return $this->create(
            $review->user,
            'review_approved',
            $message,
            $review
        );
    }
}
