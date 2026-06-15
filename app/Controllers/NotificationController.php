<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private Notification $notifModel;

    public function __construct()
    {
        $this->notifModel = new Notification();
    }

    public function index(): void
    {
        $notifications = $this->notifModel->getForUser($_SESSION['user_id'], 50);

        $layout = 'app';
        $pageTitle = 'Notifications';
        $currentPage = 'notifications';
        $this->view('notifications.index', compact('layout', 'pageTitle', 'currentPage', 'notifications'));
    }

    public function markRead(string $id): void
    {
        $this->notifModel->markAsRead((int) $id);
        $this->json(['success' => true]);
    }

    public function markAllRead(): void
    {
        $this->notifModel->markAllAsRead($_SESSION['user_id']);
        $this->redirect('/notifications');
    }

    public function getUnreadCount(): void
    {
        $count = $this->notifModel->getUnreadCount($_SESSION['user_id']);
        $this->json(['count' => $count]);
    }
}
