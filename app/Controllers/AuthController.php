<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Models\User;
use App\Models\Company;

class AuthController extends Controller
{
    private User $userModel;
    private Company $companyModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->companyModel = new Company();
    }

    public function showLogin(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $layout = 'auth';
        $pageTitle = 'Connexion';
        $this->view('auth.login', compact('layout', 'pageTitle'));
    }

    public function login(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/login');
            return;
        }

        $email = trim($this->input('email', ''));
        $password = $this->input('password', '');

        $errors = $this->validate(
            ['email' => $email, 'password' => $password],
            ['email' => 'required|email', 'password' => 'required']
        );

        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
            return;
        }

        if ($this->userModel->isLocked($user)) {
            $this->setFlash('error', 'Compte temporairement bloqué. Réessayez plus tard.');
            $this->redirect('/login');
            return;
        }

        if (!$user['is_active']) {
            $this->setFlash('error', 'Ce compte est désactivé.');
            $this->redirect('/login');
            return;
        }

        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->userModel->incrementLoginAttempts($user['id']);
            $this->setFlash('error', 'Email ou mot de passe incorrect.');
            $this->redirect('/login');
            return;
        }

        // Check if 2FA is required
        if ($this->userModel->needsTwoFactor($user)) {
            $code = $this->userModel->generateTwoFactorCode($user['id']);
            $_SESSION['pending_2fa_user_id'] = $user['id'];
            // In production: send email with $code
            $_SESSION['debug_2fa_code'] = $code; // For development only
            $this->redirect('/verify-2fa');
            return;
        }

        $this->completeLogin($user);
    }

    public function showVerify2FA(): void
    {
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            $this->redirect('/login');
            return;
        }
        $layout = 'auth';
        $pageTitle = 'Vérification en deux étapes';
        $debugCode = $_SESSION['debug_2fa_code'] ?? '';
        $this->view('auth.verify_2fa', compact('layout', 'pageTitle', 'debugCode'));
    }

    public function verify2FA(): void
    {
        if (!isset($_SESSION['pending_2fa_user_id'])) {
            $this->redirect('/login');
            return;
        }

        $code = trim($this->input('code', ''));
        $userId = $_SESSION['pending_2fa_user_id'];

        if ($this->userModel->verifyTwoFactorCode($userId, $code)) {
            $user = $this->userModel->find($userId);
            unset($_SESSION['pending_2fa_user_id'], $_SESSION['debug_2fa_code']);
            $this->completeLogin($user);
        } else {
            $this->setFlash('error', 'Code invalide ou expiré.');
            $this->redirect('/verify-2fa');
        }
    }

    private function completeLogin(array $user): void
    {
        $this->userModel->resetLoginAttempts($user['id']);

        $company = $this->companyModel->getWithPlan($user['company_id']);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'uuid' => $user['uuid'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar' => $user['avatar'],
        ];
        $_SESSION['company'] = $company;
        $_SESSION['last_activity'] = time();
        $_SESSION['expires_at'] = time() + 7200;

        session_regenerate_id(true);
        $this->redirect('/dashboard');
    }

    public function showRegister(): void
    {
        if ($this->isAuthenticated()) {
            $this->redirect('/dashboard');
        }
        $layout = 'auth';
        $pageTitle = 'Inscription';
        $this->view('auth.register', compact('layout', 'pageTitle'));
    }

    public function register(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/register');
            return;
        }

        $data = [
            'company_name' => trim($this->input('company_name', '')),
            'company_type' => $this->input('company_type', 'tpe'),
            'first_name' => trim($this->input('first_name', '')),
            'last_name' => trim($this->input('last_name', '')),
            'email' => trim($this->input('email', '')),
            'phone' => trim($this->input('phone', '')),
            'password' => $this->input('password', ''),
            'password_confirmation' => $this->input('password_confirmation', ''),
        ];

        $errors = $this->validate($data, [
            'company_name' => 'required|min:2|max:255',
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/register');
            return;
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($data['email'])) {
            $this->setFlash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/register');
            return;
        }

        $db = App::getInstance()->db();
        $db->beginTransaction();

        try {
            // Get free plan
            $freePlan = $db->fetch("SELECT id FROM subscription_plans WHERE slug = 'free'");

            // Create company
            $companyId = $this->companyModel->createCompany([
                'name' => $data['company_name'],
                'type' => $data['company_type'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'plan_id' => $freePlan['id'] ?? null,
                'subscription_status' => 'trial',
                'subscription_start' => date('Y-m-d'),
                'subscription_end' => date('Y-m-d', strtotime('+30 days')),
                'invoices_month_reset' => date('Y-m-01'),
            ]);

            // Create user (owner)
            $userId = $this->userModel->createUser([
                'company_id' => $companyId,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'role' => 'owner',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            $db->commit();

            $this->setFlash('success', 'Compte créé avec succès ! Connectez-vous.');
            $this->redirect('/login');
        } catch (\Exception $e) {
            $db->rollback();
            $this->setFlash('error', 'Une erreur est survenue. Veuillez réessayer.');
            $this->redirect('/register');
        }
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }

    public function showForgotPassword(): void
    {
        $layout = 'auth';
        $pageTitle = 'Mot de passe oublié';
        $this->view('auth.forgot_password', compact('layout', 'pageTitle'));
    }

    public function forgotPassword(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($this->input('email', ''));
        $user = $this->userModel->findByEmail($email);

        // Always show success to prevent email enumeration
        $this->setFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation a été envoyé.');

        if ($user) {
            $token = $this->userModel->generatePasswordResetToken($user['id']);
            // In production: send email with reset link
            $_SESSION['debug_reset_token'] = $token;
        }

        $this->redirect('/forgot-password');
    }

    public function showResetPassword(string $token): void
    {
        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            $this->setFlash('error', 'Lien invalide ou expiré.');
            $this->redirect('/forgot-password');
            return;
        }
        $layout = 'auth';
        $pageTitle = 'Réinitialiser le mot de passe';
        $this->view('auth.reset_password', compact('layout', 'pageTitle', 'token'));
    }

    public function resetPassword(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/login');
            return;
        }

        $token = $this->input('token', '');
        $password = $this->input('password', '');
        $confirmation = $this->input('password_confirmation', '');

        if ($password !== $confirmation || strlen($password) < 8) {
            $this->setFlash('error', 'Les mots de passe ne correspondent pas ou sont trop courts.');
            $this->redirect("/reset-password/{$token}");
            return;
        }

        $user = $this->userModel->findByResetToken($token);
        if (!$user) {
            $this->setFlash('error', 'Lien invalide ou expiré.');
            $this->redirect('/forgot-password');
            return;
        }

        $this->userModel->resetPassword($user['id'], $password);
        $this->setFlash('success', 'Mot de passe réinitialisé avec succès.');
        $this->redirect('/login');
    }
}
