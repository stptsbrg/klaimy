<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Models\Invoice;
use App\Models\Company;
use App\Models\Client;
use App\Models\Product;

class InvoiceController extends Controller
{
    private Invoice $invoiceModel;

    public function __construct()
    {
        $this->invoiceModel = new Invoice();
    }

    public function index(): void
    {
        $page = (int) ($this->input('page', 1));
        $status = $this->input('status');
        $result = $this->invoiceModel->getForCompany($this->companyId(), $status, $page);

        $layout = 'app';
        $pageTitle = 'Factures';
        $currentPage = 'invoices';
        $invoices = $result['items'];
        $pagination = $result;
        $this->view('invoices.index', compact('layout', 'pageTitle', 'currentPage', 'invoices', 'pagination', 'status'));
    }

    public function create(): void
    {
        $companyId = $this->companyId();
        $clientModel = new Client();
        $productModel = new Product();

        $clients = $clientModel->forCompany($companyId);
        $products = $productModel->getWithCategory($companyId);

        $companyModel = new Company();
        $company = $companyModel->find($companyId);

        $layout = 'app';
        $pageTitle = 'Nouvelle facture';
        $currentPage = 'invoices';
        $this->view('invoices.form', compact('layout', 'pageTitle', 'currentPage', 'clients', 'products', 'company'));
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/invoices/create');
            return;
        }

        $companyId = $this->companyId();
        $companyModel = new Company();

        if (!$companyModel->canCreateInvoice($companyId)) {
            $this->setFlash('error', 'Vous avez atteint votre limite de factures ce mois-ci. Passez à un plan supérieur.');
            $this->redirect('/invoices');
            return;
        }

        $db = App::getInstance()->db();
        $db->beginTransaction();

        try {
            $invoiceNumber = $companyModel->getNextInvoiceNumber($companyId);

            $invoiceId = $this->invoiceModel->createInvoice([
                'company_id' => $companyId,
                'client_id' => (int) $this->input('client_id'),
                'invoice_number' => $invoiceNumber,
                'status' => $this->input('status', 'draft'),
                'issue_date' => $this->input('issue_date', date('Y-m-d')),
                'due_date' => $this->input('due_date', date('Y-m-d', strtotime('+30 days'))),
                'currency' => $this->input('currency', 'XAF'),
                'notes' => trim($this->input('notes', '')),
                'terms' => trim($this->input('terms', '')),
                'discount_amount' => (float) $this->input('discount_amount', 0),
                'discount_type' => $this->input('discount_type', 'fixed'),
                'created_by' => $_SESSION['user_id'],
            ]);

            // Add items
            $descriptions = $_POST['item_description'] ?? [];
            $quantities = $_POST['item_quantity'] ?? [];
            $prices = $_POST['item_price'] ?? [];
            $taxRates = $_POST['item_tax_rate'] ?? [];
            $productIds = $_POST['item_product_id'] ?? [];

            for ($i = 0; $i < count($descriptions); $i++) {
                if (empty($descriptions[$i])) continue;

                $qty = (float) ($quantities[$i] ?? 1);
                $price = (float) ($prices[$i] ?? 0);
                $taxRate = (float) ($taxRates[$i] ?? 0);
                $lineTotal = $qty * $price;
                $taxAmount = $lineTotal * ($taxRate / 100);

                $db->insert('document_items', [
                    'document_type' => 'invoice',
                    'document_id' => $invoiceId,
                    'product_id' => !empty($productIds[$i]) ? (int) $productIds[$i] : null,
                    'description' => $descriptions[$i],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $lineTotal,
                    'sort_order' => $i,
                ]);
            }

            $this->invoiceModel->updateAmounts($invoiceId);
            $companyModel->incrementInvoiceCount($companyId);

            // Generate QR code payment URL
            $appUrl = App::getInstance()->config('app.url');
            $invoice = $this->invoiceModel->find($invoiceId);
            $paymentLink = "{$appUrl}/pay/{$invoice['uuid']}";
            $this->invoiceModel->update($invoiceId, ['payment_link' => $paymentLink]);

            $db->commit();

            $this->setFlash('success', 'Facture créée avec succès.');
            $this->redirect('/invoices/' . $invoiceId);
        } catch (\Exception $e) {
            $db->rollback();
            $this->setFlash('error', 'Erreur lors de la création de la facture.');
            $this->redirect('/invoices/create');
        }
    }

    public function show(string $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/invoices');
            return;
        }

        $layout = 'app';
        $pageTitle = 'Facture ' . $invoice['invoice_number'];
        $currentPage = 'invoices';
        $this->view('invoices.show', compact('layout', 'pageTitle', 'currentPage', 'invoice'));
    }

    public function edit(string $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/invoices');
            return;
        }

        if ($invoice['status'] !== 'draft') {
            $this->setFlash('error', 'Seules les factures en brouillon peuvent être modifiées.');
            $this->redirect('/invoices/' . $id);
            return;
        }

        $clientModel = new Client();
        $productModel = new Product();
        $clients = $clientModel->forCompany($this->companyId());
        $products = $productModel->getWithCategory($this->companyId());

        $layout = 'app';
        $pageTitle = 'Modifier facture ' . $invoice['invoice_number'];
        $currentPage = 'invoices';
        $this->view('invoices.form', compact('layout', 'pageTitle', 'currentPage', 'invoice', 'clients', 'products'));
    }

    public function update(string $id): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect("/invoices/{$id}/edit");
            return;
        }

        $invoice = $this->invoiceModel->find((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId() || $invoice['status'] !== 'draft') {
            $this->setFlash('error', 'Facture introuvable ou non modifiable.');
            $this->redirect('/invoices');
            return;
        }

        $db = App::getInstance()->db();
        $db->beginTransaction();

        try {
            $this->invoiceModel->update((int) $id, [
                'client_id' => (int) $this->input('client_id'),
                'issue_date' => $this->input('issue_date'),
                'due_date' => $this->input('due_date'),
                'currency' => $this->input('currency', 'XAF'),
                'notes' => trim($this->input('notes', '')),
                'terms' => trim($this->input('terms', '')),
                'discount_amount' => (float) $this->input('discount_amount', 0),
                'discount_type' => $this->input('discount_type', 'fixed'),
            ]);

            // Delete old items and add new ones
            $db->delete('document_items', "document_type = 'invoice' AND document_id = ?", [(int) $id]);

            $descriptions = $_POST['item_description'] ?? [];
            $quantities = $_POST['item_quantity'] ?? [];
            $prices = $_POST['item_price'] ?? [];
            $taxRates = $_POST['item_tax_rate'] ?? [];
            $productIds = $_POST['item_product_id'] ?? [];

            for ($i = 0; $i < count($descriptions); $i++) {
                if (empty($descriptions[$i])) continue;

                $qty = (float) ($quantities[$i] ?? 1);
                $price = (float) ($prices[$i] ?? 0);
                $taxRate = (float) ($taxRates[$i] ?? 0);
                $lineTotal = $qty * $price;
                $taxAmount = $lineTotal * ($taxRate / 100);

                $db->insert('document_items', [
                    'document_type' => 'invoice',
                    'document_id' => (int) $id,
                    'product_id' => !empty($productIds[$i]) ? (int) $productIds[$i] : null,
                    'description' => $descriptions[$i],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $lineTotal,
                    'sort_order' => $i,
                ]);
            }

            $this->invoiceModel->updateAmounts((int) $id);
            $db->commit();

            $this->setFlash('success', 'Facture modifiée avec succès.');
            $this->redirect('/invoices/' . $id);
        } catch (\Exception $e) {
            $db->rollback();
            $this->setFlash('error', 'Erreur lors de la modification.');
            $this->redirect("/invoices/{$id}/edit");
        }
    }

    public function send(string $id): void
    {
        $invoice = $this->invoiceModel->find((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->json(['error' => 'Facture introuvable.'], 404);
            return;
        }

        $this->invoiceModel->update((int) $id, [
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s'),
        ]);

        // Create recovery schedules
        $this->createRecoverySchedules((int) $id);

        $this->setFlash('success', 'Facture envoyée.');
        $this->redirect('/invoices/' . $id);
    }

    public function cancel(string $id): void
    {
        $invoice = $this->invoiceModel->find((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/invoices');
            return;
        }

        $this->invoiceModel->update((int) $id, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
        ]);

        $this->setFlash('success', 'Facture annulée.');
        $this->redirect('/invoices/' . $id);
    }

    private function createRecoverySchedules(int $invoiceId): void
    {
        $invoice = $this->invoiceModel->find($invoiceId);
        if (!$invoice) return;

        $db = App::getInstance()->db();
        $dueDate = $invoice['due_date'];
        $offsets = [-7, -3, -1, 3, 7, 15, 30];

        foreach ($offsets as $offset) {
            $scheduleDate = date('Y-m-d', strtotime($dueDate . " {$offset} days"));
            if (strtotime($scheduleDate) < time()) continue;

            $db->insert('recovery_schedules', [
                'company_id' => $invoice['company_id'],
                'invoice_id' => $invoiceId,
                'client_id' => $invoice['client_id'],
                'schedule_type' => $offset < 0 ? 'before_due' : 'after_due',
                'days_offset' => $offset,
                'channel' => 'email',
                'status' => 'pending',
                'scheduled_date' => $scheduleDate,
            ]);
        }
    }

    public function pdf(string $id): void
    {
        $invoice = $this->invoiceModel->getWithDetails((int) $id);
        if (!$invoice || $invoice['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Facture introuvable.');
            $this->redirect('/invoices');
            return;
        }

        $pageTitle = 'Facture ' . $invoice['invoice_number'];
        $this->view('invoices.pdf', compact('pageTitle', 'invoice'));
    }
}
