<?php
namespace App\Core;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected Database $db;

    public function __construct()
    {
        $this->db = App::getInstance()->db();
    }

    public function find(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
            [$id]
        );
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return $this->db->fetch(
            "SELECT * FROM {$this->table} WHERE {$column} = ? LIMIT 1",
            [$value]
        );
    }

    public function findAllBy(string $column, mixed $value, string $orderBy = 'created_at DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$column} = ? ORDER BY {$orderBy}",
            [$value]
        );
    }

    public function all(string $orderBy = 'id ASC'): array
    {
        return $this->db->fetchAll("SELECT * FROM {$this->table} ORDER BY {$orderBy}");
    }

    public function create(array $data): int
    {
        return $this->db->insert($this->table, $data);
    }

    public function update(int $id, array $data): int
    {
        return $this->db->update($this->table, $data, "{$this->primaryKey} = ?", [$id]);
    }

    public function delete(int $id): int
    {
        return $this->db->delete($this->table, "{$this->primaryKey} = ?", [$id]);
    }

    public function count(string $where = '1=1', array $params = []): int
    {
        return $this->db->count($this->table, $where, $params);
    }

    public function paginate(int $page, int $perPage = 20, string $where = '1=1', array $params = [], string $orderBy = 'created_at DESC'): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($where, $params);
        $totalPages = (int) ceil($total / $perPage);

        $items = $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} LIMIT ? OFFSET ?",
            array_merge($params, [$perPage, $offset])
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    public function forCompany(int $companyId, string $orderBy = 'created_at DESC'): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table} WHERE company_id = ? ORDER BY {$orderBy}",
            [$companyId]
        );
    }

    protected function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
