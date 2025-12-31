<?php
require_once __DIR__ . '/session_path.php';
session_start();

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

// BUSCADOR
$q = $_GET['q'] ?? '';
$date_from = $_GET['from'] ?? '';
$date_to = $_GET['to'] ?? '';

// Pacientes
$sql = "SELECT * FROM pacientes WHERE 1";
$params = [];

if ($q !== "") {
    $sql .= " AND (nombre LIKE :q OR apellido LIKE :q OR diagnostico_principal LIKE :q OR id LIKE :id)";
    $params[':q'] = "%$q%";
    $params[':id'] = $q;
}

$sql .= " ORDER BY creado_en DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pacientes = $stmt->fetchAll();

// Registros clínicos
$sql2 = "SELECT r.*, p.nombre, p.apellido 
         FROM registros r 
         JOIN pacientes p ON r.paciente_id = p.id 
         ORDER BY r.fecha DESC 
         LIMIT 10";
$stmt2 = $pdo->query($sql2);
$registros = $stmt2->fetchAll();

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// PERMISO
$servicio_sesion = trim((string)($_SESSION['servicio'] ?? ''));

// normalizar: minusculas + sin acentos
$serv_norm = mb_strtolower($servicio_sesion, 'UTF-8');
$serv_norm = str_replace(['á','é','í','ó','ú'], ['a','e','i','o','u'], $serv_norm);

// acepta "Administración" / "Administracion"
$puede_admin_usuarios = ($serv_norm === 'administracion');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clínica San Agustín</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .patient-card:hover { background: #eef6ff; cursor: pointer; }
        .diag-text { color: #444; }
        .debug-box {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container py-4">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="fw-bold">Clínica San Agustín</h1>

        <div class="d-flex gap-2">
            <?php if ($puede_admin_usuarios): ?>
                <a href="admin/usuarios.php" class="btn btn-secondary">Administrar usuarios</a>
            <?php endif; ?>

            <a href="logout.php" class="btn btn-outline-danger">Cerrar sesión</a>
            <a href="export_registros.php" class="btn btn-success">Exportar todo (Excel)</a>
            <a href="crear_paciente.php" class="btn btn-primary">Nuevo paciente</a>
        </div>
    </div>

    <!-- BUSCADOR -->
    <form class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" class="form-control" name="q" placeholder="Buscar por nombre, apellido, diagnóstico o folio"
                   value="<?= h($q) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" class="form-control" name="from" value="<?= h($date_from) ?>">
        </div>
        <div class="col-md-3">
            <input type="date" class="form-control" name="to" value="<?= h($date_to) ?>">
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary w-100">Buscar</button>
        </div>
    </form>

    <div class="row">
        <!-- PACIENTES -->
        <div class="col-md-5">
            <h4 class="fw-bold mb-3">Pacientes</h4>

            <?php if (empty($pacientes)): ?>
                <div class="alert alert-info">No hay pacientes aún.</div>
            <?php endif; ?>

            <?php foreach ($pacientes as $p): ?>
                <div class="card mb-3 patient-card p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1">
                                <?= h($p['nombre'] . " " . $p['apellido']) ?>
                            </h5>
                            <p class="diag-text">
                                <?= h($p['diagnostico_principal'] ?: "Sin diagnóstico") ?>
                            </p>
                            <p class="text-muted small">
                                <?= h($p['calle'] . " " . $p['colonia'] . ", " . $p['municipio']) ?>
                            </p>
                        </div>

                        <div class="text-end">
                            <a href="panel_paciente.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-primary mb-2">
                                Expediente clínico
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- REGISTROS RECIENTES -->
        <div class="col-md-7">
            <h4 class="fw-bold mb-3">Registros recientes</h4>

            <table class="table table-striped table-sm">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Paciente</th>
                    <th>Fecha</th>
                    <th>Motivo</th>
                    <th>Diagnóstico</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay registros.</td></tr>
                <?php endif; ?>

                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= (int)$r['id'] ?></td>
                        <td><?= h($r['nombre'] . " " . $r['apellido']) ?></td>
                        <td><?= date("Y-m-d", strtotime($r['fecha'])) ?></td>
                        <td><?= h($r['motivo']) ?></td>
                        <td><?= h($r['diagnostico']) ?></td>
                        <td>
                            <a href="panel_paciente.php?id=<?= (int)$r['paciente_id'] ?>&sec=historia"
                               class="btn btn-primary btn-sm">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

</body>
</html>
