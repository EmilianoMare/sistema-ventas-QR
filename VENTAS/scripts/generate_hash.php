<?php
// Usage:
// CLI: php scripts/generate_hash.php Administrador
// Browser: http://localhost/VENTAS/scripts/generate_hash.php?pass=Administrador
$pass = '';
if (PHP_SAPI === 'cli') {
    $pass = $argv[1] ?? '';
} else {
    $pass = $_GET['pass'] ?? '';
}
if ($pass === '') {
    echo "Provide a password as first CLI arg or ?pass= in URL\n";
    exit(1);
}
echo password_hash($pass, PASSWORD_BCRYPT)."\n";
