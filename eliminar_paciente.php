<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = :id");
$stmt->execute([':id' => $id]);

header("Location: index.php");
exit;
