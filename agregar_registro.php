<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="registros_clinicos.csv"');

$output = fopen('php://output', 'w');

// Encabezados
fputcsv($output, [
  'registro_id',
  'paciente_id',
  'nombre',
  'apellido',
  'fecha',
  'motivo',
  'diagnostico',
  'tratamiento',
  'observaciones'
]);

$sql = "SELECT r.*, p.nombre, p.apellido
        FROM registros r
        JOIN pacientes p ON p.id = r.paciente_id
        ORDER BY r.fecha DESC";

$stmt = $pdo->query($sql);

while ($row = $stmt->fetch()) {
    fputcsv($output, [
      $row['id'],
      $row['paciente_id'],
      $row['nombre'],
      $row['apellido'],
      $row['fecha'],
      $row['motivo'],
      $row['diagnostico'],
      $row['tratamiento'],
      $row['observaciones']
    ]);
}

fclose($output);
exit;
