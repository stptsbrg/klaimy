<?php
namespace App\Models;

use App\Core\Model;

class Product extends Model
{
    protected string $table = 'products';

    public function createProduct(array $data): int
    {
        $data['uuid'] = $this->generateUuid();
        return $this->create($data);
    }

    public function search(int $companyId, string $query): array
    {
        $like = '%' . $query . '%';
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.company_id = ? AND (p.name LIKE ? OR p.sku LIKE ? OR p.description LIKE ?)
             ORDER BY p.name ASC",
            [$companyId, $like, $like, $like]
        );
    }

    public function getWithCategory(int $companyId, string $orderBy = 'p.name ASC'): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as category_name
             FROM products p
             LEFT JOIN categories c ON p.category_id = c.id
             WHERE p.company_id = ? ORDER BY {$orderBy}",
            [$companyId]
        );
    }

    public function getLowStockProducts(int $companyId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM products
             WHERE company_id = ? AND type = 'product' AND stock_quantity <= stock_alert_threshold AND is_active = 1
             ORDER BY stock_quantity ASC",
            [$companyId]
        );
    }

    public function adjustStock(int $productId, int $quantity, string $type = 'out'): void
    {
        $operator = $type === 'in' ? '+' : '-';
        $this->db->query(
            "UPDATE products SET stock_quantity = stock_quantity {$operator} ? WHERE id = ?",
            [$quantity, $productId]
        );
    }
}
