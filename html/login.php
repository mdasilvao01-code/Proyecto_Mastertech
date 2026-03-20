<?php
require_once 'db_config.php';
$error = '';
if (isset($_SESSION['usuario_id'])) { header('Location: /dashboard.php'); exit(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    try {
        $pdo  = getDB();
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
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Acceso — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
  <style>
    body { background: var(--bg); }

    .login-page {
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
    }

    /* ── LEFT ── */
    .login-left {
      background: var(--navy);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 52px;
      position: relative;
      overflow: hidden;
      background-image:
        linear-gradient(rgba(59,130,246,.05) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59,130,246,.05) 1px, transparent 1px);
      background-size: 40px 40px;
    }

    .login-left .orb1 {
      position: absolute; top: -140px; right: -100px;
      width: 500px; height: 500px; border-radius: 50%;
      background: radial-gradient(circle, rgba(59,130,246,.18) 0%, transparent 65%);
      pointer-events: none;
    }
    .login-left .orb2 {
      position: absolute; bottom: -120px; left: -80px;
      width: 380px; height: 380px; border-radius: 50%;
      background: radial-gradient(circle, rgba(6,182,212,.1) 0%, transparent 65%);
      pointer-events: none;
    }

    .ll-logo {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      color: #fff;
      letter-spacing: -.5px;
      display: flex; align-items: center; gap: 10px;
      position: relative; z-index: 1;
    }

    .ll-main {
      position: relative; z-index: 1;
    }
    .ll-main h1 {
      font-size: 2.6rem;
      color: #fff;
      line-height: 1.1;
      letter-spacing: -.5px;
      margin-bottom: 18px;
    }
    .ll-main h1 .hl {
      background: linear-gradient(135deg, #60a5fa, #22d3ee);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .ll-main p {
      color: var(--text-3);
      font-size: .975rem;
      line-height: 1.65;
      max-width: 360px;
    }

    .ll-badges {
      position: relative; z-index: 1;
      display: flex; gap: 12px; flex-wrap: wrap;
    }
    .ll-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.04);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 10px 16px;
    }
    .ll-badge-val {
      font-family: 'Syne', sans-serif;
      font-size: 1.3rem;
      font-weight: 800;
      color: #fff;
    }
    .ll-badge-label {
      font-size: .68rem;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    /* ── RIGHT ── */
    .login-right {
      background: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 52px 44px;
    }

    .lf-wrap {
      width: 100%;
      max-width: 400px;
    }

    .lf-wrap h2 {
      font-size: 1.7rem;
      letter-spacing: -.4px;
      margin-bottom: 6px;
    }

    .lf-sub {
      color: var(--text-2);
      font-size: .9rem;
      margin-bottom: 32px;
    }

    .demo-box {
      background: rgba(59,130,246,.06);
      border: 1px solid rgba(59,130,246,.15);
      border-radius: var(--radius-sm);
      padding: 14px 16px;
      margin-top: 20px;
    }
    .demo-box-title {
      font-size: .7rem;
      font-weight: 700;
      color: var(--text-3);
      text-transform: uppercase;
      letter-spacing: 1.2px;
      margin-bottom: 8px;
      display: flex; align-items: center; gap: 6px;
    }
    .demo-box-title::before {
      content: '';
      width: 5px; height: 5px;
      border-radius: 50%;
      background: #60a5fa;
    }
    .demo-row {
      display: flex; justify-content: space-between; align-items: center;
      padding: 4px 0;
      border-bottom: 1px solid var(--border);
      font-size: .82rem;
      color: var(--text-2);
    }
    .demo-row:last-child { border-bottom: none; }
    .demo-role {
      font-size: .68rem;
      padding: 2px 8px;
      border-radius: 100px;
      font-weight: 600;
    }
    .demo-role.admin   { background: rgba(139,92,246,.15); color: #c4b5fd; }
    .demo-role.tecnico { background: rgba(59,130,246,.15);  color: #60a5fa; }
    .demo-role.cliente { background: rgba(16,185,129,.15);  color: #34d399; }

    @media (max-width:768px) {
      .login-page { grid-template-columns: 1fr; }
      .login-left  { display: none; }
      .login-right { padding: 32px 24px; }
    }
  </style>
</head>
<body>
<div class="login-page">

  <!-- LEFT -->
  <div class="login-left">
    <div class="orb1"></div>
    <div class="orb2"></div>

    <div class="ll-logo">
      <span class="brand-dot"></span>
      Mastertech
    </div>

    <div class="ll-main">
      <h1>Gestión sin <span class="hl">fricciones.</span></h1>
      <p>
        Incidencias, clientes y monitorización desde un único panel. 
        Diseñado para equipos IT que no se pueden permitir el tiempo de inactividad.
      </p>
    </div>

    <div class="ll-badges">
      <div class="ll-badge">
        <div>
          <div class="ll-badge-val">99.9%</div>
          <div class="ll-badge-label">Disponibilidad</div>
        </div>
      </div>
      <div class="ll-badge">
        <div>
          <div class="ll-badge-val">HA</div>
          <div class="ll-badge-label">Alta disponibilidad</div>
        </div>
      </div>
      <div class="ll-badge">
        <div>
          <div class="ll-badge-val">7</div>
          <div class="ll-badge-label">Servidores</div>
        </div>
      </div>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="login-right">
    <div class="lf-wrap">
      <h2>Bienvenido de nuevo</h2>
      <p class="lf-sub">Introduce tus credenciales para acceder al panel</p>

      <?php if ($error): ?>
      <div class="alert alert-danger">
        <i class="fa fa-circle-exclamation"></i> <?=htmlspecialchars($error)?>
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
          <div class="input-group" style="position:relative;">
            <i class="fa fa-lock input-icon"></i>
            <input type="password" name="password" id="pwd" class="form-control" placeholder="••••••••" required style="padding-right:44px;">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;background:none;border:none;cursor:pointer;color:var(--text-3);font-size:.85rem;" id="eye-btn">
              <i class="fa fa-eye" id="eye-icon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" style="width:100%;justify-content:center;margin-top:4px;">
          Iniciar sesión <i class="fa fa-arrow-right"></i>
        </button>
      </form>

      <div style="text-align:center;margin-top:20px;">
        <span style="font-size:.875rem;color:var(--text-2);">¿Sin cuenta? </span>
        <a href="/registro.php" style="color:var(--blue);font-weight:600;font-size:.875rem;text-decoration:none;">Crear cuenta</a>
      </div>

      <div class="demo-box">
        <div class="demo-box-title">Acceso demo</div>
        <div class="demo-row">
          <span>admin@mastertech.com</span>
          <span class="demo-role admin">Admin</span>
        </div>
        <div class="demo-row">
          <span>tecnico@mastertech.com</span>
          <span class="demo-role tecnico">Técnico</span>
        </div>
        <div class="demo-row">
          <span>cliente@mastertech.com</span>
          <span class="demo-role cliente">Cliente</span>
        </div>
        <div style="margin-top:8px;font-size:.78rem;color:var(--text-3);">
          <i class="fa fa-key" style="margin-right:4px;"></i> Contraseña: password123
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function togglePwd(){
  const i=document.getElementById('pwd');
  const ic=document.getElementById('eye-icon');
  if(i.type==='password'){i.type='text';ic.className='fa fa-eye-slash';}
  else{i.type='password';ic.className='fa fa-eye';}
}
</script>
</body>
</html>
