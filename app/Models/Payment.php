<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected string $table = 'payments';

    public function createPayment(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function getForCompany(int $companyId, int $page = 1, int $perPage = 20): array
    {
        $total = $this->db->count('payments', 'company_id = ?', [$companyId]);
        $offset = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            "SELECT p.*, c.name as client_name, i.invoice_number
             FROM payments p
             JOIN clients c ON p.client_id = c.id
             JOIN invoices i ON p.invoice_id = i.id
             WHERE p.company_id = ?
             ORDER BY p.created_at DESC LIMIT ? OFFSET ?",
            [$companyId, $perPage, $offset]
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    public function recordPayment(int $invoiceId, float $amount, string $method, string $transactionId = ''): int
    {
        $invoiceModel = new Invoice();
        $invoice = $invoiceModel->find($invoiceId);

        if (!$invoice) {
            throw new \RuntimeException('Facture introuvable.');
        }

        $paymentId = $this->createPayment([
            'company_id' => $invoice['company_id'],
            'invoice_id' => $invoiceId,
            'client_id' => $invoice['client_id'],
            'amount' => $amount,
            'currency' => $invoice['currency'],
            'payment_method' => $method,
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Update invoice amounts
        $invoiceModel->updateAmounts($invoiceId);

        // Update client totals
        $clientModel = new Client();
        $clientModel->updateTotals($invoice['client_id']);

        return $paymentId;
    }

    public function getMonthlyStats(int $companyId, int $months = 12): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(paid_at, '%Y-%m') as month,
                    COUNT(*) as count,
                    COALESCE(SUM(amount), 0) as total
             FROM payments
             WHERE company_id = ? AND status = 'completed'
               AND paid_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
             ORDER BY month ASC",
            [$companyId, $months]
        );
    }
}
