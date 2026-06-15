<?php
namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected string $table = 'messages';

    public function getConversations(int $companyId, int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT conv.*, 
                    (SELECT body FROM messages WHERE conversation_id = conv.id ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = conv.id 
                     AND m.created_at > COALESCE((SELECT last_read_at FROM conversation_participants WHERE conversation_id = conv.id AND user_id = ?), '1970-01-01')
                     AND m.sender_id != ?) as unread_count,
                    cl.name as client_name
             FROM conversations conv
             LEFT JOIN clients cl ON conv.client_id = cl.id
             JOIN conversation_participants cp ON conv.id = cp.conversation_id
             WHERE conv.company_id = ? AND cp.user_id = ? AND conv.is_archived = 0
             ORDER BY conv.last_message_at DESC",
            [$userId, $userId, $companyId, $userId]
        );
    }

    public function getConversationMessages(int $conversationId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            "SELECT m.*, u.first_name, u.last_name, u.avatar
             FROM messages m
             LEFT JOIN users u ON m.sender_id = u.id
             WHERE m.conversation_id = ?
             ORDER BY m.created_at ASC
             LIMIT ? OFFSET ?",
            [$conversationId, $limit, $offset]
        );
    }

    public function sendMessage(int $conversationId, int $senderId, string $body, ?string $attachment = null): int
    {
        $messageId = $this->create([
            'uuid' => $this->generateUuid(),
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $body,
            'attachment' => $attachment,
        ]);

        $this->db->query(
            "UPDATE conversations SET last_message_at = NOW() WHERE id = ?",
            [$conversationId]
        );

        return $messageId;
    }

    public function createConversation(int $companyId, string $type, int $createdBy, ?int $clientId = null, string $subject = ''): int
    {
        $convId = $this->db->insert('conversations', [
            'uuid' => $this->generateUuid(),
            'company_id' => $companyId,
            'type' => $type,
            'subject' => $subject,
            'client_id' => $clientId,
            'created_by' => $createdBy,
            'last_message_at' => date('Y-m-d H:i:s'),
        ]);

        // Add creator as participant
        $this->db->insert('conversation_participants', [
            'conversation_id' => $convId,
            'user_id' => $createdBy,
        ]);

        return $convId;
    }

    public function addParticipant(int $conversationId, int $userId): void
    {
        $exists = $this->db->fetch(
            "SELECT id FROM conversation_participants WHERE conversation_id = ? AND user_id = ?",
            [$conversationId, $userId]
        );

        if (!$exists) {
            $this->db->insert('conversation_participants', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
            ]);
        }
    }

    public function markAsRead(int $conversationId, int $userId): void
    {
        $this->db->query(
            "UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ?",
            [$conversationId, $userId]
        );
    }
}
