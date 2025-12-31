<?php 
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre     = trim($_POST['nombre'] ?? '');
    $apellido   = trim($_POST['apellido'] ?? '');
    $fecha_nac  = $_POST['fecha_nacimiento'] ?? '';
    $genero     = trim($_POST['genero'] ?? '');   // ← AHORA SÍ ES "genero"
    $telefono   = trim($_POST['telefono'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $curp       = trim($_POST['curp'] ?? '');
    $estado_civil = trim($_POST['estado_civil'] ?? '');

    $calle      = trim($_POST['calle'] ?? '');
    $no_ext     = trim($_POST['no_ext'] ?? '');
    $no_int     = trim($_POST['no_int'] ?? '');
    $colonia    = trim($_POST['colonia'] ?? '');
    $cp         = trim($_POST['cp'] ?? '');
    $municipio  = trim($_POST['municipio'] ?? '');
    $estado     = trim($_POST['estado'] ?? '');

    if ($nombre === "") {
        $errores[] = "El nombre es obligatorio.";
    }

    if (!$errores) {
        $sql = "INSERT INTO pacientes
                (nombre, apellido, fecha_nacimiento, genero, telefono, email,
                 curp, estado_civil,
                 calle, no_ext, no_int, colonia, cp, municipio, estado)
                VALUES
                (:nombre, :apellido, :fecha_nacimiento, :genero, :telefono, :email,
                 :curp, :estado_civil,
                 :calle, :no_ext, :no_int, :colonia, :cp, :municipio, :estado)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':fecha_nacimiento' => $fecha_nac ?: null,
            ':genero' => $genero,
            ':telefono' => $telefono,
            ':email' => $email,
            ':curp' => $curp,
            ':estado_civil' => $estado_civil,
            ':calle' => $calle,
            ':no_ext' => $no_ext,
            ':no_int' => $no_int,
            ':colonia' => $colonia,
            ':cp' => $cp,
            ':municipio' => $municipio,
            ':estado' => $estado
        ]);

        header("Location: index.php");
        exit;
    }

}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Nuevo Paciente</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link
  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  rel="stylesheet">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">Clínica San Agustín</a>
    <div>
      <span class="text-white me-3">Usuario: <?= htmlspecialchars($_SESSION['usuario']) ?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container">

  <a href="index.php" class="btn btn-link">&larr; Volver</a>
  <h1 class="fw-bold">Registrar Nuevo Paciente</h1>

  <?php if ($errores): ?>
  <div class="alert alert-danger">
    <?php foreach ($errores as $e): ?>
      <?= htmlspecialchars($e) ?><br>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <form method="post" class="mt-4">
    <div class="row g-3">

      <div class="col-md-6">
        <label class="form-label">Nombre *</label>
        <input name="nombre" class="form-control" required>
      </div>

      <div class="col-md-6">
        <label class="form-label">Apellido</label>
        <input name="apellido" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento</label>
        <input type="date" name="fecha_nacimiento" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Género</label>
        <input name="genero" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Teléfono</label>
        <input name="telefono" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">CURP</label>
        <input name="curp" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">Estado civil</label>
        <input name="estado_civil" class="form-control">
      </div>

      <h4 class="mt-4">Dirección</h4>

      <div class="col-md-6">
        <label class="form-label">Calle</label>
        <input name="calle" class="form-control">
      </div>

      <div class="col-md-3">
        <label class="form-label">No. Ext.</label>
        <input name="no_ext" class="form-control">
      </div>

      <div class="col-md-3">
        <label class="form-label">No. Int.</label>
        <input name="no_int" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">Colonia</label>
        <input name="colonia" class="form-control">
      </div>

      <div class="col-md-2">
        <label class="form-label">CP</label>
        <input name="cp" class="form-control">
      </div>

      <div class="col-md-4">
        <label class="form-label">Municipio</label>
        <input name="municipio" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">Estado</label>
        <input name="estado" class="form-control">
      </div>

      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control">
      </div>

    </div>

    <div class="mt-4">
      <button class="btn btn-primary">Guardar Paciente</button>
    </div>
  </form>

</div>
</body>
</html>
