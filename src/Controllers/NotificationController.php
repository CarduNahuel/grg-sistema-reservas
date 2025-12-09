<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Models\Notification as NotificationModel;

class NotificationController extends Controller
{
    private $authService;
    private $notificationModel;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->notificationModel = new NotificationModel();
    }

    public function index()
    {
        $userId = $this->authService->userId();
        $notifications = $this->notificationModel->getByUser($userId);

        $this->view('notifications.index', [
            'title' => 'Notificaciones - GRG',
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $notification = $this->notificationModel->find($id);
        
        if (!$notification || $notification['user_id'] != $this->authService->userId()) {
            return $this->json(['success' => false, 'message' => 'No autorizado.'], 403);
        }

        $this->notificationModel->markAsRead($id);
        
        return $this->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $userId = $this->authService->userId();
        $this->notificationModel->markAllAsRead($userId);
        
        $this->setFlash('success', 'Todas las notificaciones marcadas como leídas.');
        return $this->back();
    }
}
