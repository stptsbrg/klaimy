<?php
namespace App\Models;

use App\Core\Model;

class Notification extends Model
{
    protected string $table = 'notifications';

    public function createNotification(int $userId, int $companyId, string $type, string $title, string $body = '', array $data = []): int
    {
        return $this->create([
            'uuid' => $this->generateUuid(),
            'user_id' => $userId,
            'company_id' => $companyId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => !empty($data) ? json_encode($data) : null,
            'channel' => 'system',
        ]);
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->db->count('notifications', 'user_id = ? AND is_read = 0', [$userId]);
    }

    public function getForUser(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function markAsRead(int $notificationId): void
    {
        $this->update($notificationId, ['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
    }

    public function markAllAsRead(int $userId): void
    {
        $this->db->query(
            "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }
}
