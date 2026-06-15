<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;

class ClientController extends Controller
{
    private Client $clientModel;

    public function __construct()
    {
        $this->clientModel = new Client();
    }

    public function index(): void
    {
        $page = (int) ($this->input('page', 1));
        $search = $this->input('search', '');
        $companyId = $this->companyId();

        if ($search) {
            $clients = $this->clientModel->search($companyId, $search);
            $pagination = null;
        } else {
            $result = $this->clientModel->paginate($page, 20, 'company_id = ? AND is_active = 1', [$companyId], 'name ASC');
            $clients = $result['items'];
            $pagination = $result;
        }

        $layout = 'app';
        $pageTitle = 'Clients';
        $currentPage = 'clients';
        $this->view('clients.index', compact('layout', 'pageTitle', 'currentPage', 'clients', 'pagination', 'search'));
    }

    public function create(): void
    {
        $layout = 'app';
        $pageTitle = 'Nouveau client';
        $currentPage = 'clients';
        $this->view('clients.form', compact('layout', 'pageTitle', 'currentPage'));
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/clients/create');
            return;
        }

        $data = [
            'company_id' => $this->companyId(),
            'type' => $this->input('type', 'individual'),
            'name' => trim($this->input('name', '')),
            'contact_name' => trim($this->input('contact_name', '')),
            'email' => trim($this->input('email', '')),
            'phone' => trim($this->input('phone', '')),
            'whatsapp' => trim($this->input('whatsapp', '')),
            'address' => trim($this->input('address', '')),
            'city' => trim($this->input('city', '')),
            'country' => trim($this->input('country', 'Cameroun')),
            'tax_id' => trim($this->input('tax_id', '')),
            'rccm' => trim($this->input('rccm', '')),
            'notes' => trim($this->input('notes', '')),
        ];

        $errors = $this->validate($data, ['name' => 'required|min:2|max:255']);

        if (!empty($errors)) {
            $this->setFlash('error', implode(' ', $errors));
            $this->redirect('/clients/create');
            return;
        }

        $this->clientModel->createClient($data);
        $this->setFlash('success', 'Client créé avec succès.');
        $this->redirect('/clients');
    }

    public function show(string $id): void
    {
        $client = $this->clientModel->getWithStats((int) $id);

        if (!$client || $client['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/clients');
            return;
        }

        $invoices = $this->clientModel->getClientInvoices((int) $id);
        $payments = $this->clientModel->getClientPayments((int) $id);

        $layout = 'app';
        $pageTitle = $client['name'];
        $currentPage = 'clients';
        $this->view('clients.show', compact('layout', 'pageTitle', 'currentPage', 'client', 'invoices', 'payments'));
    }

    public function edit(string $id): void
    {
        $client = $this->clientModel->find((int) $id);

        if (!$client || $client['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/clients');
            return;
        }

        $layout = 'app';
        $pageTitle = 'Modifier ' . $client['name'];
        $currentPage = 'clients';
        $this->view('clients.form', compact('layout', 'pageTitle', 'currentPage', 'client'));
    }

    public function update(string $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect("/clients/{$id}/edit");
            return;
        }

        $client = $this->clientModel->find((int) $id);
        if (!$client || $client['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/clients');
            return;
        }

        $data = [
            'type' => $this->input('type', 'individual'),
            'name' => trim($this->input('name', '')),
            'contact_name' => trim($this->input('contact_name', '')),
            'email' => trim($this->input('email', '')),
            'phone' => trim($this->input('phone', '')),
            'whatsapp' => trim($this->input('whatsapp', '')),
            'address' => trim($this->input('address', '')),
            'city' => trim($this->input('city', '')),
            'country' => trim($this->input('country', 'Cameroun')),
            'tax_id' => trim($this->input('tax_id', '')),
            'rccm' => trim($this->input('rccm', '')),
            'notes' => trim($this->input('notes', '')),
        ];

        $this->clientModel->update((int) $id, $data);
        $this->setFlash('success', 'Client modifié avec succès.');
        $this->redirect('/clients/' . $id);
    }

    public function delete(string $id): void
    {
        $client = $this->clientModel->find((int) $id);
        if (!$client || $client['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Client introuvable.');
            $this->redirect('/clients');
            return;
        }

        $this->clientModel->update((int) $id, ['is_active' => 0]);
        $this->setFlash('success', 'Client supprimé.');
        $this->redirect('/clients');
    }

    public function search(): void
    {
        $query = $this->input('q', '');
        $clients = $this->clientModel->search($this->companyId(), $query);
        $this->json(['clients' => $clients]);
    }
}
