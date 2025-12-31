<?php
// db.php  - conexión general a MySQL

$DB_HOST = 'localhost';
$DB_NAME = 'u635913051_clinica';
$DB_USER = 'u635913051_admin';
$DB_PASS = 'Cerebro1942,';

$DB_DSN = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
