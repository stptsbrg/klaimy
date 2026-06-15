<?php
namespace App\Models;

use App\Core\Model;

class Company extends Model
{
    protected string $table = 'companies';

    public function createCompany(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function getWithPlan(int $companyId): ?array
    {
        return $this->db->fetch(
            "SELECT c.*, sp.name as plan_name, sp.slug as plan_slug,
                    sp.max_invoices_per_month, sp.max_users, sp.max_warehouses,
                    sp.has_auto_recovery, sp.has_watermark
             FROM companies c
             LEFT JOIN subscription_plans sp ON c.plan_id = sp.id
             WHERE c.id = ?",
            [$companyId]
        );
    }

    public function canCreateInvoice(int $companyId): bool
    {
        $company = $this->getWithPlan($companyId);
        if (!$company) return false;

        $maxInvoices = $company['max_invoices_per_month'];
        if ($maxInvoices === -1) return true; // Unlimited

        // Reset monthly counter if needed
        $currentMonth = date('Y-m-01');
        if ($company['invoices_month_reset'] !== $currentMonth) {
            $this->update($companyId, [
                'invoices_this_month' => 0,
                'invoices_month_reset' => $currentMonth,
            ]);
            return true;
        }

        return $company['invoices_this_month'] < $maxInvoices;
    }

    public function incrementInvoiceCount(int $companyId): void
    {
        $this->db->query(
            "UPDATE companies SET invoices_this_month = invoices_this_month + 1 WHERE id = ?",
            [$companyId]
        );
    }

    public function getNextInvoiceNumber(int $companyId): string
    {
        $company = $this->find($companyId);
        $number = str_pad((string) $company['next_invoice_number'], 5, '0', STR_PAD_LEFT);
        $prefix = $company['invoice_prefix'] ?: 'FAC';
        $year = date('Y');
        $this->db->query(
            "UPDATE companies SET next_invoice_number = next_invoice_number + 1 WHERE id = ?",
            [$companyId]
        );
        return "{$prefix}-{$year}-{$number}";
    }

    public function getNextQuoteNumber(int $companyId): string
    {
        $company = $this->find($companyId);
        $number = str_pad((string) $company['next_quote_number'], 5, '0', STR_PAD_LEFT);
        $prefix = $company['quote_prefix'] ?: 'DEV';
        $year = date('Y');
        $this->db->query(
            "UPDATE companies SET next_quote_number = next_quote_number + 1 WHERE id = ?",
            [$companyId]
        );
        return "{$prefix}-{$year}-{$number}";
    }

    public function getNextDeliveryNumber(int $companyId): string
    {
        $company = $this->find($companyId);
        $number = str_pad((string) $company['next_delivery_number'], 5, '0', STR_PAD_LEFT);
        $prefix = $company['delivery_prefix'] ?: 'BL';
        $year = date('Y');
        $this->db->query(
            "UPDATE companies SET next_delivery_number = next_delivery_number + 1 WHERE id = ?",
            [$companyId]
        );
        return "{$prefix}-{$year}-{$number}";
    }

    public function getDashboardStats(int $companyId): array
    {
        $stats = [];

        // Chiffre d'affaires (factures payées)
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM(total), 0) as revenue FROM invoices WHERE company_id = ? AND status = 'paid'",
            [$companyId]
        );
        $stats['revenue'] = (float) $result['revenue'];

        // Montants encaissés
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM(amount), 0) as collected FROM payments WHERE company_id = ? AND status = 'completed'",
            [$companyId]
        );
        $stats['collected'] = (float) $result['collected'];

        // Créances en attente
        $result = $this->db->fetch(
            "SELECT COALESCE(SUM(amount_due), 0) as outstanding FROM invoices WHERE company_id = ? AND status IN ('sent','viewed','partial','overdue')",
            [$companyId]
        );
        $stats['outstanding'] = (float) $result['outstanding'];

        // Compteurs factures
        $stats['invoices_total'] = $this->db->count('invoices', 'company_id = ?', [$companyId]);
        $stats['invoices_paid'] = $this->db->count('invoices', "company_id = ? AND status = 'paid'", [$companyId]);
        $stats['invoices_unpaid'] = $this->db->count('invoices', "company_id = ? AND status IN ('sent','viewed','overdue')", [$companyId]);
        $stats['quotes_pending'] = $this->db->count('quotes', "company_id = ? AND status IN ('draft','sent','viewed')", [$companyId]);

        // Paiements récents
        $stats['recent_payments'] = $this->db->fetchAll(
            "SELECT p.*, c.name as client_name, i.invoice_number
             FROM payments p
             JOIN clients c ON p.client_id = c.id
             JOIN invoices i ON p.invoice_id = i.id
             WHERE p.company_id = ? AND p.status = 'completed'
             ORDER BY p.paid_at DESC LIMIT 5",
            [$companyId]
        );

        // CA mensuel (12 derniers mois)
        $stats['monthly_revenue'] = $this->db->fetchAll(
            "SELECT DATE_FORMAT(paid_at, '%Y-%m') as month,
                    COALESCE(SUM(total), 0) as amount
             FROM invoices
             WHERE company_id = ? AND status = 'paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY DATE_FORMAT(paid_at, '%Y-%m')
             ORDER BY month ASC",
            [$companyId]
        );

        return $stats;
    }
}
