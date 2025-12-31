<?php
require_once __DIR__ . '/session_path.php'; // ✅ MISMA SESIÓN QUE INDEX

// Cookie de sesión temporal (expira al cerrar el navegador)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => false,   // ponlo en true si usas https
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
require_once __DIR__ . '/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = :u LIMIT 1");
    $stmt->execute([':u' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $ok = false;

    if ($user) {
        $hash = (string)($user['pass_hash'] ?? '');

        if ($hash !== '' && password_verify($pass, $hash)) {
            $ok = true;
        } else {
            $sha = hash('sha256', $pass);
            if ($hash !== '' && hash_equals($hash, $sha)) {
                $ok = true;

                // Migración automática
                $newHash = password_hash($pass, PASSWORD_BCRYPT);
                $up = $pdo->prepare("UPDATE usuarios SET pass_hash = :h WHERE id = :id");
                $up->execute([
                    ':h'  => $newHash,
                    ':id' => (int)$user['id']
                ]);
            }
        }
    }

    if ($ok) {
        $_SESSION['usuario_id'] = (int)$user['id'];
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['usuario']    = $user['usuario'];
        $_SESSION['servicio']   = $user['servicio'] ?? null;

        header("Location: index.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Iniciar sesión</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f3f5f7; }
    .login-box {
      max-width: 380px;
      margin: 80px auto;
      padding: 30px;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0px 4px 12px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="login-box">
  <h3 class="text-center mb-3">Acceso al Sistema</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger text-center"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Usuario</label>
      <input type="text" name="usuario" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button class="btn btn-primary w-100">Entrar</button>
  </form>
</div>

</body>
</html>
