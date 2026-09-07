<?php
header('Content-Type: text/plain');

echo "=== PHP VERSION ===\n";
echo phpversion() . "\n\n";

echo "=== SERVER VARIABLES ===\n";
$vars = [
    'HTTP_AUTHORIZATION',
    'REDIRECT_HTTP_AUTHORIZATION',
    'PHP_AUTH_USER',
    'PHP_AUTH_PW',
    'AUTH_TYPE',
    'REMOTE_USER',
    'REQUEST_URI',
    'REQUEST_METHOD'
];

foreach ($vars as $var) {
    $value = $_SERVER[$var] ?? 'NOT SET';
    if (in_array($var, ['PHP_AUTH_PW', 'HTTP_AUTHORIZATION']) && $value !== 'NOT SET') {
        $value = substr($value, 0, 20) . '... (hidden)';
    }
    echo $var . ": " . $value . "\n";
}

echo "\n=== HEADERS ===\n";
$headers = getallheaders();
foreach ($headers as $key => $value) {
    if (strtolower($key) === 'authorization') {
        echo $key . ": " . substr($value, 0, 20) . "... (hidden)\n";
    } else {
        echo $key . ": " . $value . "\n";
    }
}

echo "\n=== GET PARAMS ===\n";
print_r($_GET);

echo "\n=== POST PARAMS ===\n";
print_r($_POST);
