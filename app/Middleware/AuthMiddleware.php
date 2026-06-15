<?php
namespace App\Middleware;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        // Check if session is still valid
        if (isset($_SESSION['expires_at']) && time() > $_SESSION['expires_at']) {
            session_destroy();
            header('Location: /login?expired=1');
            exit;
        }

        // Update last activity
        $_SESSION['last_activity'] = time();
        $_SESSION['expires_at'] = time() + 7200; // 2 hours

        return true;
    }
}
