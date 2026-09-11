<?php
echo "<h1>Тест Apache + GSSAPI</h1>";
echo "<p>REMOTE_USER: " . ($_SERVER['REMOTE_USER'] ?? 'НЕ УСТАНОВЛЕН') . "</p>";
echo "<p>AUTH_TYPE: " . ($_SERVER['AUTH_TYPE'] ?? 'НЕ УСТАНОВЛЕН') . "</p>";
echo "<p>PHP_AUTH_USER: " . ($_SERVER['PHP_AUTH_USER'] ?? 'НЕ УСТАНОВЛЕН') . "</p>";
echo "<p>SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'НЕ УСТАНОВЛЕН') . "</p>";
echo "<hr>";
echo "<h2>Все переменные сервера:</h2>";
echo "<pre>";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'AUTH') !== false || 
        strpos($key, 'USER') !== false || 
        strpos($key, 'GSS') !== false ||
        $key === 'REMOTE_USER' ||
        $key === 'PHP_AUTH_USER') {
        echo "$key = " . htmlspecialchars($value ?? 'NULL') . "\n";
    }
}
echo "</pre>";
?>
