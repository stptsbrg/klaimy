<?php
namespace App\Middleware;

class CsrfMiddleware
{
    public function handle(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            return true;
        }

        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || !hash_equals($sessionToken, $token)) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF invalide.']);
            exit;
        }

        return true;
    }
}
