<?php
namespace App\Models;

use App\Core\Model;

class Quote extends Model
{
    protected string $table = 'quotes';

    public function createQuote(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function getWithDetails(int $quoteId): ?array
    {
        $quote = $this->db->fetch(
            "SELECT q.*, c.name as client_name, c.email as client_email, c.phone as client_phone,
                    c.address as client_address, c.city as client_city, c.country as client_country,
                    c.tax_id as client_tax_id, c.rccm as client_rccm,
                    co.name as company_name, co.legal_name as company_legal_name,
                    co.email as company_email, co.phone as company_phone,
                    co.address as company_address, co.city as company_city,
                    co.logo as company_logo, co.signature as company_signature
             FROM quotes q
             JOIN clients c ON q.client_id = c.id
             JOIN companies co ON q.company_id = co.id
             WHERE q.id = ?",
            [$quoteId]
        );

        if ($quote) {
            $quote['items'] = $this->db->fetchAll(
                "SELECT di.*, p.name as product_name
                 FROM document_items di
                 LEFT JOIN products p ON di.product_id = p.id
                 WHERE di.document_type = 'quote' AND di.document_id = ?
                 ORDER BY di.sort_order ASC",
                [$quoteId]
            );
        }

        return $quote;
    }

    public function getForCompany(int $companyId, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $where = 'q.company_id = ?';
        $params = [$companyId];

        if ($status) {
            $where .= ' AND q.status = ?';
            $params[] = $status;
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM quotes q WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT q.*, c.name as client_name
             FROM quotes q
             JOIN clients c ON q.client_id = c.id
             WHERE {$where}
             ORDER BY q.created_at DESC LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'items' => $items,
            'total' => (int) $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function convertToInvoice(int $quoteId, int $companyId): int
    {
        $quote = $this->getWithDetails($quoteId);
        if (!$quote) {
            throw new \RuntimeException('Devis introuvable.');
        }

        $companyModel = new Company();
        $invoiceNumber = $companyModel->getNextInvoiceNumber($companyId);

        $invoiceModel = new Invoice();
        $invoiceId = $invoiceModel->createInvoice([
            'company_id' => $companyId,
            'client_id' => $quote['client_id'],
            'quote_id' => $quoteId,
            'invoice_number' => $invoiceNumber,
            'status' => 'draft',
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'subtotal' => $quote['subtotal'],
            'tax_amount' => $quote['tax_amount'],
            'discount_amount' => $quote['discount_amount'],
            'discount_type' => $quote['discount_type'],
            'total' => $quote['total'],
            'amount_due' => $quote['total'],
            'currency' => $quote['currency'],
            'notes' => $quote['notes'],
            'terms' => $quote['terms'],
            'created_by' => $_SESSION['user_id'] ?? null,
        ]);

        // Copy items
        foreach ($quote['items'] as $item) {
            $this->db->insert('document_items', [
                'document_type' => 'invoice',
                'document_id' => $invoiceId,
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'],
                'tax_amount' => $item['tax_amount'],
                'discount' => $item['discount'],
                'total' => $item['total'],
                'sort_order' => $item['sort_order'],
            ]);
        }

        // Update quote status
        $this->update($quoteId, [
            'status' => 'converted',
            'converted_invoice_id' => $invoiceId,
        ]);

        return $invoiceId;
    }
}
