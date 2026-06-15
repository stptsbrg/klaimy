<?php
namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';

    public function createInvoice(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function getWithDetails(int $invoiceId): ?array
    {
        $invoice = $this->db->fetch(
            "SELECT i.*, c.name as client_name, c.email as client_email, c.phone as client_phone,
                    c.address as client_address, c.city as client_city, c.country as client_country,
                    c.tax_id as client_tax_id, c.rccm as client_rccm,
                    co.name as company_name, co.legal_name as company_legal_name,
                    co.email as company_email, co.phone as company_phone,
                    co.address as company_address, co.city as company_city,
                    co.country as company_country, co.tax_id as company_tax_id,
                    co.rccm as company_rccm, co.logo as company_logo,
                    co.signature as company_signature, co.legal_mentions as company_legal_mentions,
                    co.payment_conditions as company_payment_conditions
             FROM invoices i
             JOIN clients c ON i.client_id = c.id
             JOIN companies co ON i.company_id = co.id
             WHERE i.id = ?",
            [$invoiceId]
        );

        if ($invoice) {
            $invoice['items'] = $this->db->fetchAll(
                "SELECT di.*, p.name as product_name, p.sku as product_sku
                 FROM document_items di
                 LEFT JOIN products p ON di.product_id = p.id
                 WHERE di.document_type = 'invoice' AND di.document_id = ?
                 ORDER BY di.sort_order ASC",
                [$invoiceId]
            );

            $invoice['payments'] = $this->db->fetchAll(
                "SELECT * FROM payments WHERE invoice_id = ? ORDER BY created_at DESC",
                [$invoiceId]
            );
        }

        return $invoice;
    }

    public function getForCompany(int $companyId, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $where = 'i.company_id = ?';
        $params = [$companyId];

        if ($status) {
            $where .= ' AND i.status = ?';
            $params[] = $status;
        }

        $total = $this->db->fetch(
            "SELECT COUNT(*) as total FROM invoices i WHERE {$where}",
            $params
        )['total'];

        $offset = ($page - 1) * $perPage;
        $items = $this->db->fetchAll(
            "SELECT i.*, c.name as client_name
             FROM invoices i
             JOIN clients c ON i.client_id = c.id
             WHERE {$where}
             ORDER BY i.created_at DESC LIMIT ? OFFSET ?",
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

    public function updateAmounts(int $invoiceId): void
    {
        // Recalculate from items
        $totals = $this->db->fetch(
            "SELECT COALESCE(SUM(total), 0) as subtotal,
                    COALESCE(SUM(tax_amount), 0) as tax_amount
             FROM document_items
             WHERE document_type = 'invoice' AND document_id = ?",
            [$invoiceId]
        );

        $invoice = $this->find($invoiceId);
        $subtotal = (float) $totals['subtotal'];
        $taxAmount = (float) $totals['tax_amount'];
        $discount = (float) ($invoice['discount_amount'] ?? 0);
        $total = $subtotal + $taxAmount - $discount;

        $paid = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as paid FROM payments WHERE invoice_id = ? AND status = 'completed'",
            [$invoiceId]
        )['paid'];

        $amountDue = $total - (float) $paid;

        $this->update($invoiceId, [
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'amount_paid' => (float) $paid,
            'amount_due' => max(0, $amountDue),
        ]);

        // Update status
        if ($amountDue <= 0) {
            $this->update($invoiceId, ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        } elseif ((float) $paid > 0) {
            $this->update($invoiceId, ['status' => 'partial']);
        }
    }

    public function getOverdueInvoices(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, c.name as client_name, c.email as client_email
             FROM invoices i
             JOIN clients c ON i.client_id = c.id
             WHERE i.company_id = ? AND i.due_date < CURDATE()
               AND i.status IN ('sent','viewed','partial')
             ORDER BY i.due_date ASC",
            [$companyId]
        );
    }

    public function getInvoicesForRecovery(): array
    {
        return $this->db->fetchAll(
            "SELECT i.*, c.name as client_name, c.email as client_email,
                    c.phone as client_phone, c.whatsapp as client_whatsapp,
                    co.name as company_name
             FROM invoices i
             JOIN clients c ON i.client_id = c.id
             JOIN companies co ON i.company_id = co.id
             WHERE i.status IN ('sent','viewed','partial','overdue')
             ORDER BY i.due_date ASC"
        );
    }
}
