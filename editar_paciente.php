<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$id = $_GET['id'] ?? 0;

// Obtener datos del paciente
$stmt = $pdo->prepare("SELECT * FROM pacientes WHERE id = :id");
$stmt->execute([':id' => $id]);
$pac = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pac) {
    die("Paciente no encontrado.");
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $sql = "UPDATE pacientes SET
        nombre = :nombre,
        apellido = :apellido,
        fecha_nacimiento = :fecha_nacimiento,
        genero = :genero,
        telefono = :telefono,
        email = :email,
        curp = :curp,
        estado_civil = :estado_civil,
        calle = :calle,
        no_ext = :no_ext,
        no_int = :no_int,
        colonia = :colonia,
        cp = :cp,
        municipio = :municipio,
        estado = :estado
        WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nombre' => $_POST['nombre'],
        ':apellido' => $_POST['apellido'],
        ':fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
        ':genero' => $_POST['genero'],
        ':telefono' => $_POST['telefono'],
        ':email' => $_POST['email'],
        ':curp' => $_POST['curp'],
        ':estado_civil' => $_POST['estado_civil'],
        ':calle' => $_POST['calle'],
        ':no_ext' => $_POST['no_ext'],
        ':no_int' => $_POST['no_int'],
        ':colonia' => $_POST['colonia'],
        ':cp' => $_POST['cp'],
        ':municipio' => $_POST['municipio'],
        ':estado' => $_POST['estado'],
        ':id' => $id
    ]);

    header("Location: panel_paciente.php?id=" . $id);
    exit;
}

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Editar Paciente</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<div class="container mt-4">

<a href="panel_paciente.php?id=<?= $id ?>" class="btn btn-link">&larr; Volver</a>
<h2 class="fw-bold">Editar Paciente</h2>

<form method="post" class="row g-3 mt-3">

    <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input name="nombre" value="<?= htmlspecialchars($pac['nombre']) ?>" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Apellido</label>
        <input name="apellido" value="<?= htmlspecialchars($pac['apellido']) ?>" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" value="<?= $pac['fecha_nacimiento'] ?>" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Género</label>
        <input name="genero" value="<?= htmlspecialchars($pac['genero']) ?>" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="telefono" value="<?= htmlspecialchars($pac['telefono']) ?>" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($pac['email']) ?>" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">CURP</label>
        <input name="curp" value="<?= htmlspecialchars($pac['curp']) ?>" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Estado civil</label>
        <input name="estado_civil" value="<?= htmlspecialchars($pac['estado_civil']) ?>" class="form-control">
    </div>

    <h4 class="mt-4 fw-bold">Dirección</h4>

    <div class="col-md-6">
        <label class="form-label">Calle</label>
        <input name="calle" value="<?= htmlspecialchars($pac['calle']) ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">No. Ext.</label>
        <input name="no_ext" value="<?= htmlspecialchars($pac['no_ext']) ?>" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">No. Int.</label>
        <input name="no_int" value="<?= htmlspecialchars($pac['no_int']) ?>" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Colonia</label>
        <input name="colonia" value="<?= htmlspecialchars($pac['colonia']) ?>" class="form-control">
    </div>

    <div class="col-md-2">
        <label class="form-label">CP</label>
        <input name="cp" value="<?= htmlspecialchars($pac['cp']) ?>" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Municipio</label>
        <input name="municipio" value="<?= htmlspecialchars($pac['municipio']) ?>" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Estado</label>
        <input name="estado" value="<?= htmlspecialchars($pac['estado']) ?>" class="form-control">
    </div>

    <div class="mt-4">
        <button class="btn btn-primary">Guardar Cambios</button>
    </div>

</form>


</div>
</body>
</html>
