<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Company;

class DashboardController extends Controller
{
    public function index(): void
    {
        $companyModel = new Company();
        $stats = $companyModel->getDashboardStats($this->companyId());
        $company = $this->currentCompany();

        $layout = 'app';
        $pageTitle = 'Tableau de bord';
        $currentPage = 'dashboard';
        $this->view('dashboard.index', compact('layout', 'pageTitle', 'currentPage', 'stats', 'company'));
    }
}
