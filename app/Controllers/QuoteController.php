<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\App;
use App\Models\Quote;
use App\Models\Company;
use App\Models\Client;
use App\Models\Product;

class QuoteController extends Controller
{
    private Quote $quoteModel;

    public function __construct()
    {
        $this->quoteModel = new Quote();
    }

    public function index(): void
    {
        $page = (int) ($this->input('page', 1));
        $status = $this->input('status');
        $result = $this->quoteModel->getForCompany($this->companyId(), $status, $page);

        $layout = 'app';
        $pageTitle = 'Devis';
        $currentPage = 'quotes';
        $quotes = $result['items'];
        $pagination = $result;
        $this->view('quotes.index', compact('layout', 'pageTitle', 'currentPage', 'quotes', 'pagination', 'status'));
    }

    public function create(): void
    {
        $clientModel = new Client();
        $productModel = new Product();
        $clients = $clientModel->forCompany($this->companyId());
        $products = $productModel->getWithCategory($this->companyId());

        $layout = 'app';
        $pageTitle = 'Nouveau devis';
        $currentPage = 'quotes';
        $this->view('quotes.form', compact('layout', 'pageTitle', 'currentPage', 'clients', 'products'));
    }

    public function store(): void
    {
        if (!$this->verifyCsrf()) {
            $this->setFlash('error', 'Token de sécurité invalide.');
            $this->redirect('/quotes/create');
            return;
        }

        $companyId = $this->companyId();
        $companyModel = new Company();
        $db = App::getInstance()->db();
        $db->beginTransaction();

        try {
            $quoteNumber = $companyModel->getNextQuoteNumber($companyId);

            $quoteId = $this->quoteModel->createQuote([
                'company_id' => $companyId,
                'client_id' => (int) $this->input('client_id'),
                'quote_number' => $quoteNumber,
                'status' => 'draft',
                'issue_date' => $this->input('issue_date', date('Y-m-d')),
                'expiry_date' => $this->input('expiry_date', date('Y-m-d', strtotime('+30 days'))),
                'currency' => $this->input('currency', 'XAF'),
                'notes' => trim($this->input('notes', '')),
                'terms' => trim($this->input('terms', '')),
                'discount_amount' => (float) $this->input('discount_amount', 0),
                'discount_type' => $this->input('discount_type', 'fixed'),
                'created_by' => $_SESSION['user_id'],
            ]);

            $descriptions = $_POST['item_description'] ?? [];
            $quantities = $_POST['item_quantity'] ?? [];
            $prices = $_POST['item_price'] ?? [];
            $taxRates = $_POST['item_tax_rate'] ?? [];
            $productIds = $_POST['item_product_id'] ?? [];
            $subtotal = 0;
            $totalTax = 0;

            for ($i = 0; $i < count($descriptions); $i++) {
                if (empty($descriptions[$i])) continue;

                $qty = (float) ($quantities[$i] ?? 1);
                $price = (float) ($prices[$i] ?? 0);
                $taxRate = (float) ($taxRates[$i] ?? 0);
                $lineTotal = $qty * $price;
                $taxAmount = $lineTotal * ($taxRate / 100);

                $db->insert('document_items', [
                    'document_type' => 'quote',
                    'document_id' => $quoteId,
                    'product_id' => !empty($productIds[$i]) ? (int) $productIds[$i] : null,
                    'description' => $descriptions[$i],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'total' => $lineTotal,
                    'sort_order' => $i,
                ]);

                $subtotal += $lineTotal;
                $totalTax += $taxAmount;
            }

            $discount = (float) $this->input('discount_amount', 0);
            $total = $subtotal + $totalTax - $discount;

            $this->quoteModel->update($quoteId, [
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total' => $total,
            ]);

            $db->commit();

            $this->setFlash('success', 'Devis créé avec succès.');
            $this->redirect('/quotes/' . $quoteId);
        } catch (\Exception $e) {
            $db->rollback();
            $this->setFlash('error', 'Erreur lors de la création du devis.');
            $this->redirect('/quotes/create');
        }
    }

    public function show(string $id): void
    {
        $quote = $this->quoteModel->getWithDetails((int) $id);
        if (!$quote || $quote['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Devis introuvable.');
            $this->redirect('/quotes');
            return;
        }

        $layout = 'app';
        $pageTitle = 'Devis ' . $quote['quote_number'];
        $currentPage = 'quotes';
        $this->view('quotes.show', compact('layout', 'pageTitle', 'currentPage', 'quote'));
    }

    public function convertToInvoice(string $id): void
    {
        try {
            $invoiceId = $this->quoteModel->convertToInvoice((int) $id, $this->companyId());
            $this->setFlash('success', 'Devis converti en facture avec succès.');
            $this->redirect('/invoices/' . $invoiceId);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Erreur lors de la conversion.');
            $this->redirect('/quotes/' . $id);
        }
    }

    public function send(string $id): void
    {
        $quote = $this->quoteModel->find((int) $id);
        if (!$quote || $quote['company_id'] !== $this->companyId()) {
            $this->setFlash('error', 'Devis introuvable.');
            $this->redirect('/quotes');
            return;
        }

        $this->quoteModel->update((int) $id, ['status' => 'sent']);
        $this->setFlash('success', 'Devis envoyé.');
        $this->redirect('/quotes/' . $id);
    }
}
