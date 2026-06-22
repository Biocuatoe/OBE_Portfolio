<?php
/**
 * One-time migration: update stale password hashes in the users table.
 * Run ONCE via browser (e.g. http://localhost/reset_pass.php) then DELETE this file.
 * Updated hashes correspond to plaintext: "password" (cost=12 bcrypt).
 */

require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance();

    $hash1 = '$2y$12$F2oj/xkVO0VFbyhZ/nl8Jemk39.RpBpkSuypGvedi2iFiZbKhPGe6';
    $hash2 = '$2y$12$Jx1Hp9seXGMythjv84907uYkF5YYRV2XtcdOwVMbBYXwD2dIxSCa6';

    $stmt = $db->query(
        "UPDATE users SET password = CASE username
            WHEN 'admin01'    THEN ?
            WHEN 'student01'  THEN ?
            WHEN 'lecturer01'  THEN ?
            WHEN 'student02'  THEN ?
            ELSE password
         END",
        [$hash1, $hash1, $hash2, $hash2]
    );

    $affected = $stmt->rowCount();
    echo "<p style='color:green'>Done. {$affected} row(s) updated.</p>";
    echo "<p>Demo credentials (password for all: <code>password</code>):</p>";
    echo "<ul>";
    echo "<li>admin01 / password</li>";
    echo "<li>lecturer01 / password</li>";
    echo "<li>student01 / password</li>";
    echo "<li>student02 / password</li>";
    echo "</ul>";
    echo "<p><strong>Delete this file after running!</strong></p>";

} catch (Throwable $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
