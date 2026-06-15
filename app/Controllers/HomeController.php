<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;

class HomeController extends Controller
{
    public function index(): void
    {
        $db = App::getInstance()->db();
        $plans = $db->fetchAll("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order");

        $pageTitle = 'Klaimy - Facturation Intelligente pour l\'Afrique';
        $this->view('home.index', compact('pageTitle', 'plans'));
    }
}
