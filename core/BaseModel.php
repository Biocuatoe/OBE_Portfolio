<?php
declare(strict_types=1);

/**
 * BaseModel - Model cha
 *
 * Cung cấp CRUD cơ bản sử dụng PDO Prepared Statements.
 * Mọi model con kế thừa và override $table, $primaryKey nếu cần.
 */
abstract class BaseModel
{
    protected Database $db;
    protected string $table      = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int|string $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    public function findAll(string $orderBy = '', int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        if ($limit)   $sql .= " LIMIT {$limit}";
        if ($offset)  $sql .= " OFFSET {$offset}";
        return $this->db->fetchAll($sql);
    }

    public function findWhere(array $conditions, string $orderBy = ''): array
    {
        $where  = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        $sql    = "SELECT * FROM `{$this->table}` WHERE {$where}";
        if ($orderBy) $sql .= " ORDER BY {$orderBy}";
        return $this->db->fetchAll($sql, array_values($conditions));
    }

    public function findOneWhere(array $conditions): array|false
    {
        $where = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        return $this->db->fetchOne(
            "SELECT * FROM `{$this->table}` WHERE {$where} LIMIT 1",
            array_values($conditions)
        );
    }

    public function insert(array $data): string
    {
        $cols   = implode('`, `', array_keys($data));
        $places = implode(', ', array_fill(0, count($data), '?'));
        $this->db->query(
            "INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$places})",
            array_values($data)
        );
        return $this->db->lastInsertId();
    }

    public function update(int|string $id, array $data): int
    {
        $set  = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $stmt = $this->db->query(
            "UPDATE `{$this->table}` SET {$set} WHERE `{$this->primaryKey}` = ?",
            [...array_values($data), $id]
        );
        return $stmt->rowCount();
    }

    public function delete(int|string $id): int
    {
        $stmt = $this->db->query(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
        return $stmt->rowCount();
    }

    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            return (int) $this->db->fetchOne("SELECT COUNT(*) as c FROM `{$this->table}`")['c'];
        }
        $where = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        return (int) $this->db->fetchOne(
            "SELECT COUNT(*) as c FROM `{$this->table}` WHERE {$where}",
            array_values($conditions)
        )['c'];
    }
}
