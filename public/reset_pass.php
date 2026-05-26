<?php
$hash = password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]);
echo "Hash mới: " . $hash . "<br><br>";
echo "Chạy SQL này trong phpMyAdmin:<br>";
echo "<code>UPDATE users SET password = '$hash' WHERE username IN ('admin01','lecturer01','student01','student02');</code>";
?>