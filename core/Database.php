<?php
declare(strict_types=1);

/**
 * Database - PDO Singleton Pattern
 *
 * Đảm bảo chỉ có duy nhất một kết nối DB trong toàn bộ vòng đời request.
 * Thread-safe, sử dụng lazy initialization.
 */
final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $cfg = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['dbname'],
            $cfg['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], $options);
        } catch (PDOException $e) {
            // Log lỗi, không lộ thông tin kết nối ra ngoài
            error_log('[DB ERROR] ' . $e->getMessage());
            throw new RuntimeException('Không thể kết nối cơ sở dữ liệu. Vui lòng thử lại sau.');
        }
    }

    /** Ngăn clone object */
    private function __clone() {}

    /** Ngăn unserialize (PHP 8 required) */
    public function __wakeup(): void
    {
        throw new \Exception('Cannot unserialize singleton.');
    }

    /**
     * Lấy instance duy nhất (Lazy initialization)
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Trả về PDO connection
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Shorthand: prepare + execute, trả về PDOStatement
     *
     * @param string $sql
     * @param array  $params
     * @return \PDOStatement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Lấy một hàng duy nhất
     */
    public function fetchOne(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Lấy tất cả hàng
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Lấy ID bản ghi vừa insert
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Bắt đầu transaction
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollBack(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    /**
     * Ghi log hoạt động vào bảng activity_logs
     *
     * @param int         $userId ID của người dùng thực hiện
     * @param string      $action Hành động (VD: 'Create user', 'Update program')
     * @param string|null $entity Đối tượng bị tác động (VD: 'user', 'program', null = 'System')
     * @param string|null $ip     Địa chỉ IP, mặc định lấy từ REMOTE_ADDR
     */
    public function logActivity(int $userId, string $action, ?string $entity = null, ?string $ip = null): void
    {
        $entity = $entity ?? 'System';
        $ip = $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        $this->query(
            "INSERT INTO activity_logs (user_id, action, entity, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())",
            [$userId, $action, $entity, $ip]
        );
    }
}
