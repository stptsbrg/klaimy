<?php
namespace App\Models;

use App\Core\Model;

class Client extends Model
{
    protected string $table = 'clients';

    public function createClient(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function search(int $companyId, string $query): array
    {
        $like = '%' . $query . '%';
        return $this->db->fetchAll(
            "SELECT * FROM clients WHERE company_id = ? AND (name LIKE ? OR email LIKE ? OR phone LIKE ?) ORDER BY name ASC",
            [$companyId, $like, $like, $like]
        );
    }

    public function getWithStats(int $clientId): ?array
    {
        return $this->db->fetch(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM invoices WHERE client_id = c.id) as invoice_count,
                    (SELECT COALESCE(SUM(total), 0) FROM invoices WHERE client_id = c.id) as total_invoiced,
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE client_id = c.id AND status = 'completed') as total_paid
             FROM clients c WHERE c.id = ?",
            [$clientId]
        );
    }

    public function getClientInvoices(int $clientId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM invoices WHERE client_id = ? ORDER BY created_at DESC",
            [$clientId]
        );
    }

    public function getClientPayments(int $clientId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, i.invoice_number FROM payments p
             LEFT JOIN invoices i ON p.invoice_id = i.id
             WHERE p.client_id = ? ORDER BY p.created_at DESC",
            [$clientId]
        );
    }

    public function updateTotals(int $clientId): void
    {
        $this->db->query(
            "UPDATE clients SET
                total_invoiced = (SELECT COALESCE(SUM(total), 0) FROM invoices WHERE client_id = ?),
                total_paid = (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE client_id = ? AND status = 'completed'),
                total_outstanding = (SELECT COALESCE(SUM(amount_due), 0) FROM invoices WHERE client_id = ? AND status IN ('sent','viewed','partial','overdue'))
             WHERE id = ?",
            [$clientId, $clientId, $clientId, $clientId]
        );
    }
}
