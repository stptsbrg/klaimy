<?php
namespace App\Middleware;

class AdminMiddleware
{
    public function handle(): bool
    {
        if (!isset($_SESSION['super_admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
        return true;
    }
}
