<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Message;

class MessageController extends Controller
{
    private Message $messageModel;

    public function __construct()
    {
        $this->messageModel = new Message();
    }

    public function index(): void
    {
        $conversations = $this->messageModel->getConversations(
            $this->companyId(),
            $_SESSION['user_id']
        );

        $layout = 'app';
        $pageTitle = 'Messagerie';
        $currentPage = 'messages';
        $this->view('messages.index', compact('layout', 'pageTitle', 'currentPage', 'conversations'));
    }

    public function show(string $id): void
    {
        $conversations = $this->messageModel->getConversations(
            $this->companyId(),
            $_SESSION['user_id']
        );

        $messages = $this->messageModel->getConversationMessages((int) $id);
        $this->messageModel->markAsRead((int) $id, $_SESSION['user_id']);

        $activeConversation = (int) $id;

        $layout = 'app';
        $pageTitle = 'Messagerie';
        $currentPage = 'messages';
        $this->view('messages.index', compact('layout', 'pageTitle', 'currentPage', 'conversations', 'messages', 'activeConversation'));
    }

    public function send(string $id): void
    {
        $body = trim($this->input('body', ''));

        if (empty($body)) {
            $this->redirect('/messages/' . $id);
            return;
        }

        $this->messageModel->sendMessage((int) $id, $_SESSION['user_id'], $body);
        $this->redirect('/messages/' . $id);
    }

    public function create(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token invalide.');
            $this->redirect('/messages');
            return;
        }

        $type = $this->input('type', 'internal');
        $subject = trim($this->input('subject', ''));
        $clientId = $this->input('client_id') ? (int) $this->input('client_id') : null;

        $convId = $this->messageModel->createConversation(
            $this->companyId(),
            $type,
            $_SESSION['user_id'],
            $clientId,
            $subject
        );

        $this->redirect('/messages/' . $convId);
    }

    public function getMessages(string $id): void
    {
        $messages = $this->messageModel->getConversationMessages((int) $id);
        $this->messageModel->markAsRead((int) $id, $_SESSION['user_id']);
        $this->json(['messages' => $messages]);
    }

    public function sendAjax(string $id): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $body = trim($input['body'] ?? '');

        if (empty($body)) {
            $this->json(['error' => 'Message vide.'], 400);
            return;
        }

        $messageId = $this->messageModel->sendMessage((int) $id, $_SESSION['user_id'], $body);
        $this->json(['success' => true, 'message_id' => $messageId]);
    }
}
