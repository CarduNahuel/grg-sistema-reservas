<?php

/**
 * Cron script for sending reservation reminders and checking no-shows
 * 
 * Run this script every 15 minutes via cron:
 * */15 * * * * php c:/xampp/htdocs/grg/cron/send_reminders.php
 */

// Load bootstrap
require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Reservation;
use App\Models\Notification;
use App\Models\User;
use App\Models\Restaurant;
use App\Services\EmailService;

echo "[" . date('Y-m-d H:i:s') . "] Starting cron job: send_reminders\n";

$reservationModel = new Reservation();
$notificationModel = new Notification();
$userModel = new User();
$restaurantModel = new Restaurant();
$emailService = new EmailService();

// 1. Send reminders for reservations starting in 1 hour
$reminders = $reservationModel->getReminders(60);

echo "Found " . count($reminders) . " reservations needing reminders\n";

foreach ($reminders as $reservation) {
    try {
        $user = $userModel->find($reservation['user_id']);
        $restaurant = $restaurantModel->find($reservation['restaurant_id']);

        // Create in-app notification
        $notificationId = $notificationModel->createReservationNotification(
            'reservation_reminder',
            $user['id'],
            $reservation['id'],
            'Recordatorio de Reserva',
            "Tu reserva en {$restaurant['name']} es en 1 hora ({$reservation['start_time']})."
        );

        // Send email
        $emailSent = $emailService->sendReservationReminder($reservation, $user, $restaurant);

        if ($emailSent) {
            $notificationModel->markEmailSent($notificationId);
            echo "  ✓ Reminder sent to {$user['email']} for reservation #{$reservation['id']}\n";
        } else {
            echo "  ✗ Failed to send email to {$user['email']}\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error processing reservation #{$reservation['id']}: " . $e->getMessage() . "\n";
    }
}

// 2. Check for no-shows
$noShowCount = $reservationModel->checkForNoShows();
echo "Marked {$noShowCount} reservations as no-show\n";

// 3. Send pending notification emails
$pendingEmails = $notificationModel->getPendingEmails();

echo "Found " . count($pendingEmails) . " pending notification emails\n";

foreach ($pendingEmails as $notification) {
    try {
        $user = $userModel->find($notification['user_id']);
        
        if (!$user) {
            continue;
        }

        // Simple email for generic notifications
        $subject = $notification['title'];
        $body = "<p>Hola {$user['first_name']},</p>";
        $body .= "<p>" . nl2br($notification['message']) . "</p>";
        $body .= "<p>Saludos,<br>Equipo GRG</p>";

        $emailSent = $emailService->send($user['email'], $subject, $body);

        if ($emailSent) {
            $notificationModel->markEmailSent($notification['id']);
            echo "  ✓ Email sent to {$user['email']}\n";
        } else {
            echo "  ✗ Failed to send email to {$user['email']}\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error sending notification #{$notification['id']}: " . $e->getMessage() . "\n";
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Cron job completed\n";
echo str_repeat("-", 50) . "\n";
