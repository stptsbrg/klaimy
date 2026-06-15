<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Company;
use App\Models\User;

class SettingsController extends Controller
{
    public function company(): void
    {
        $companyModel = new Company();
        $company = $companyModel->find($this->companyId());

        $layout = 'app';
        $pageTitle = 'Paramètres entreprise';
        $currentPage = 'settings';
        $this->view('settings.company', compact('layout', 'pageTitle', 'currentPage', 'company'));
    }

    public function updateCompany(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/settings/company');
            return;
        }

        $companyModel = new Company();
        $data = [
            'name' => trim($this->input('name', '')),
            'legal_name' => trim($this->input('legal_name', '')),
            'type' => $this->input('type', 'tpe'),
            'email' => trim($this->input('email', '')),
            'phone' => trim($this->input('phone', '')),
            'whatsapp' => trim($this->input('whatsapp', '')),
            'address' => trim($this->input('address', '')),
            'city' => trim($this->input('city', '')),
            'country' => trim($this->input('country', 'Cameroun')),
            'tax_id' => trim($this->input('tax_id', '')),
            'rccm' => trim($this->input('rccm', '')),
            'website' => trim($this->input('website', '')),
            'default_currency' => $this->input('default_currency', 'XAF'),
            'invoice_prefix' => trim($this->input('invoice_prefix', 'FAC')),
            'quote_prefix' => trim($this->input('quote_prefix', 'DEV')),
            'payment_terms' => (int) $this->input('payment_terms', 30),
            'legal_mentions' => trim($this->input('legal_mentions', '')),
            'payment_conditions' => trim($this->input('payment_conditions', '')),
        ];

        // Handle logo upload
        if (!empty($_FILES['logo']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filename = 'logo_' . $this->companyId() . '.' . $ext;
                $path = ROOT_PATH . '/public/uploads/logos/';
                if (!is_dir($path)) mkdir($path, 0755, true);
                move_uploaded_file($_FILES['logo']['tmp_name'], $path . $filename);
                $data['logo'] = '/uploads/logos/' . $filename;
            }
        }

        $companyModel->update($this->companyId(), $data);

        // Update session
        $_SESSION['company'] = $companyModel->getWithPlan($this->companyId());

        $this->setFlash('success', 'Paramètres mis à jour.');
        $this->redirect('/settings/company');
    }

    public function users(): void
    {
        $userModel = new User();
        $users = $userModel->getCompanyUsers($this->companyId());

        $layout = 'app';
        $pageTitle = 'Utilisateurs';
        $currentPage = 'settings';
        $this->view('settings.users', compact('layout', 'pageTitle', 'currentPage', 'users'));
    }

    public function createUser(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token invalide.');
            $this->redirect('/settings/users');
            return;
        }

        $userModel = new User();

        if ($userModel->findByEmail(trim($this->input('email', '')))) {
            $this->setFlash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/settings/users');
            return;
        }

        $userModel->createUser([
            'company_id' => $this->companyId(),
            'first_name' => trim($this->input('first_name', '')),
            'last_name' => trim($this->input('last_name', '')),
            'email' => trim($this->input('email', '')),
            'phone' => trim($this->input('phone', '')),
            'password' => $this->input('password', ''),
            'role' => $this->input('role', 'collaborator'),
        ]);

        $this->setFlash('success', 'Utilisateur créé.');
        $this->redirect('/settings/users');
    }

    public function profile(): void
    {
        $userModel = new User();
        $user = $userModel->find($_SESSION['user_id']);

        $layout = 'app';
        $pageTitle = 'Mon profil';
        $currentPage = 'settings';
        $this->view('settings.profile', compact('layout', 'pageTitle', 'currentPage', 'user'));
    }

    public function updateProfile(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token invalide.');
            $this->redirect('/settings/profile');
            return;
        }

        $userModel = new User();
        $data = [
            'first_name' => trim($this->input('first_name', '')),
            'last_name' => trim($this->input('last_name', '')),
            'phone' => trim($this->input('phone', '')),
        ];

        $password = $this->input('password', '');
        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->setFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
                $this->redirect('/settings/profile');
                return;
            }
            $data['password_hash'] = password_hash($password, PASSWORD_ARGON2ID);
        }

        $userModel->update($_SESSION['user_id'], $data);

        $_SESSION['user']['first_name'] = $data['first_name'];
        $_SESSION['user']['last_name'] = $data['last_name'];

        $this->setFlash('success', 'Profil mis à jour.');
        $this->redirect('/settings/profile');
    }

    public function subscription(): void
    {
        $companyModel = new Company();
        $company = $companyModel->getWithPlan($this->companyId());

        $db = \App\Core\App::getInstance()->db();
        $plans = $db->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order");

        $layout = 'app';
        $pageTitle = 'Abonnement';
        $currentPage = 'settings';
        $this->view('settings.subscription', compact('layout', 'pageTitle', 'currentPage', 'company', 'plans'));
    }
}
