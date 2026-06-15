<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;

class AdminController extends Controller
{
    public function showLogin(): void
    {
        $layout = 'auth';
        $pageTitle = 'Administration Klaimy';
        $this->view('admin.login', compact('layout', 'pageTitle'));
    }

    public function login(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token invalide.');
            $this->redirect('/admin/login');
            return;
        }

        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');

        $db = App::getInstance()->db();
        $admin = $db->fetch("SELECT * FROM super_admins WHERE email = ? AND is_active = 1", [$email]);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $this->setFlash('error', 'Identifiants incorrects.');
            $this->redirect('/admin/login');
            return;
        }

        $_SESSION['super_admin_id'] = $admin['id'];
        $_SESSION['super_admin'] = ['id' => $admin['id'], 'name' => $admin['name'], 'email' => $admin['email']];

        $db->query("UPDATE super_admins SET last_login_at = NOW() WHERE id = ?", [$admin['id']]);

        $this->redirect('/admin/dashboard');
    }

    public function dashboard(): void
    {
        $db = App::getInstance()->db();

        $stats = [
            'total_companies' => $db->count('companies'),
            'active_companies' => $db->count('companies', 'is_active = 1'),
            'total_users' => $db->count('users'),
            'total_invoices' => $db->count('invoices'),
            'total_payments' => $db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'completed'")['total'],
            'recent_companies' => $db->fetchAll("SELECT * FROM companies ORDER BY created_at DESC LIMIT 10"),
            'plan_distribution' => $db->fetchAll(
                "SELECT sp.name, COUNT(c.id) as count
                 FROM subscription_plans sp
                 LEFT JOIN companies c ON c.plan_id = sp.id
                 GROUP BY sp.id, sp.name ORDER BY sp.sort_order"
            ),
        ];

        $layout = 'admin';
        $pageTitle = 'Administration';
        $currentPage = 'admin_dashboard';
        $this->view('admin.dashboard', compact('layout', 'pageTitle', 'currentPage', 'stats'));
    }

    public function companies(): void
    {
        $db = App::getInstance()->db();
        $page = (int) ($this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $companies = $db->fetchAll(
            "SELECT c.*, sp.name as plan_name,
                    (SELECT COUNT(*) FROM users WHERE company_id = c.id) as user_count,
                    (SELECT COUNT(*) FROM invoices WHERE company_id = c.id) as invoice_count
             FROM companies c
             LEFT JOIN subscription_plans sp ON c.plan_id = sp.id
             ORDER BY c.created_at DESC LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );

        $total = $db->count('companies');

        $layout = 'admin';
        $pageTitle = 'Entreprises';
        $currentPage = 'admin_companies';
        $pagination = ['total' => $total, 'page' => $page, 'per_page' => $perPage, 'total_pages' => ceil($total / $perPage)];
        $this->view('admin.companies', compact('layout', 'pageTitle', 'currentPage', 'companies', 'pagination'));
    }

    public function logout(): void
    {
        unset($_SESSION['super_admin_id'], $_SESSION['super_admin']);
        $this->redirect('/admin/login');
    }
}
