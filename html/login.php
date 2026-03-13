<?php
require_once 'db_config.php';
$error = '';
if (isset($_SESSION['usuario_id'])) { header('Location: /dashboard.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['rol']        = $usuario['rol'];
            logAccion('Login exitoso', "Usuario: $email");
            header('Location: /dashboard.php'); exit();
        } else { $error = 'Email o contraseña incorrectos'; }
    } catch (Exception $e) { $error = 'Error al iniciar sesión'; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<div class="login-page">
  <!-- LEFT PANEL -->
  <div class="login-left">
    <div class="login-logo">Mastertech</div>

    <div class="login-tagline">
      <h1>Gestión de infraestructura sin fricciones.</h1>
      <p>Incidencias, clientes y monitorización desde un único panel de control diseñado para equipos de IT.</p>
    </div>

    <div style="display:flex; gap:32px;">
      <div>
        <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:#fff;">99.9%</div>
        <div style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">Disponibilidad</div>
      </div>
      <div>
        <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:#fff;">HA</div>
        <div style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">Alta disponibilidad</div>
      </div>
      <div>
        <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:#fff;">7</div>
        <div style="font-size:0.78rem;color:#6b7280;text-transform:uppercase;letter-spacing:1px;">Servidores</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="login-right">
    <div class="login-form-wrapper">
      <h2>Bienvenido de nuevo</h2>
      <p class="subtitle">Introduce tus credenciales para acceder al panel</p>

      <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fa fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="POST" style="display:flex;flex-direction:column;gap:16px;">
        <div>
          <label class="form-label">Correo electrónico</label>
          <div class="input-group">
            <i class="fa fa-envelope input-icon"></i>
            <input type="email" name="email" class="form-control" placeholder="usuario@mastertech.com" required autofocus>
          </div>
        </div>

        <div>
          <label class="form-label">Contraseña</label>
          <div class="input-group">
            <i class="fa fa-lock input-icon"></i>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:4px;">
          Iniciar sesión <i class="fa fa-arrow-right"></i>
        </button>
      </form>

      <div style="text-align:center;margin-top:24px;">
        <span style="font-size:0.875rem;color:var(--text-2);">¿Sin cuenta? </span>
        <a href="/registro.php" style="color:var(--blue);font-weight:600;font-size:0.875rem;text-decoration:none;">Crear cuenta</a>
      </div>

      <hr style="margin:24px 0;">
      <div style="background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:14px 16px;">
        <div style="font-size:0.75rem;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;">Acceso demo</div>
        <div style="font-size:0.82rem;color:var(--text-2);line-height:1.8;">
          admin@mastertech.com &bull; tecnico@mastertech.com &bull; cliente@mastertech.com<br>
          <span style="color:var(--text-3);">Contraseña: password123</span>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>