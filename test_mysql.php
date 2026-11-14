<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=auth_db', 'root', '');
    echo "Conexión exitosa a XAMPP (MariaDB 3307) - OK\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
