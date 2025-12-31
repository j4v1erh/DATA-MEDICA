<?php
require_once __DIR__ . '/../session_path.php';
session_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db.php';

// SOLO "Administración" puede ver esta página
if (!isset($_SESSION['servicio']) || $_SESSION['servicio'] !== 'Administración') {
    header("Location: ../index.php");
    exit;
}

// Obtener usuarios
$stmt = $pdo->query("
    SELECT id, usuario, nombre_completo, servicio, creado_en
    FROM usuarios
    ORDER BY id ASC
");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Administración de Usuarios</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body { background-color: #f4f6f9; }
table td, table th { vertical-align: middle !important; }
</style>

</head>
<body>

<div class="container py-4">

    <h2 class="fw-bold mb-4">Administración de Usuarios</h2>

    <a href="crear_usuario.php" class="btn btn-primary mb-3">Crear nuevo usuario</a>

    <?php if (isset($_GET['ok']) && $_GET['ok'] == '1'): ?>
        <div class="alert alert-success">✅ Usuario creado correctamente.</div>
    <?php endif; ?>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th style="width:80px;">ID</th>
                <th>Nombre completo</th>
                <th>Usuario</th>
                <th>Servicio</th>
                <th style="width:180px;">Creado en</th>
                <th style="width: 200px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= h($u['nombre_completo'] ?? '') ?></td>
                    <td><?= h($u['usuario'] ?? '') ?></td>
                    <td><?= h($u['servicio'] ?? '') ?></td>
                    <td><?= h($u['creado_en'] ?? '') ?></td>

                    <td>
                        <a href="editar_usuario.php?id=<?= (int)$u['id'] ?>"
                           class="btn btn-warning btn-sm">
                            Editar
                        </a>

                        <a href="eliminar_usuario.php?id=<?= (int)$u['id'] ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">
                            Eliminar
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($usuarios)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No hay usuarios registrados.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="../index.php" class="btn btn-secondary mt-3">← Volver</a>

</div>

</body>
</html>
