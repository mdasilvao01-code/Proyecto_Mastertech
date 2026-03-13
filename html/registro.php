<?php
require_once 'db_config.php';
$mensaje=''; $tipo='';
if(isset($_SESSION['usuario_id'])){ header('Location:/dashboard.php'); exit(); }

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $nombre=$_POST['nombre']; $email=$_POST['email'];
        $password=$_POST['password']; $confirm=$_POST['password_confirm'];
        $empresa=$_POST['empresa']??''; $telefono=$_POST['telefono']??'';
        if(empty($nombre)||empty($email)||empty($password)) throw new Exception('Nombre, email y contraseña son obligatorios');
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Email no válido');
        if(strlen($password)<6) throw new Exception('La contraseña debe tener al menos 6 caracteres');
        if($password!==$confirm) throw new Exception('Las contraseñas no coinciden');
        $pdo=getDB();
        $stmt=$pdo->prepare("SELECT id FROM usuarios WHERE email=?"); $stmt->execute([$email]);
        if($stmt->fetch()) throw new Exception('Este email ya está registrado');
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol,empresa,telefono) VALUES (?,?,?,'cliente',?,?)")
            ->execute([$nombre,$email,$hash,$empresa,$telefono]);
        $mensaje='Cuenta creada correctamente. Ya puedes iniciar sesión.'; $tipo='success';
    } catch(Exception $e){ $mensaje=$e->getMessage(); $tipo='danger'; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Crear cuenta — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body style="background:var(--bg);min-height:100vh;display:flex;flex-direction:column;">
<nav style="background:var(--navy);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;">
  <span style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:1.2rem;">Mastertech</span>
  <a href="/login.php" class="btn btn-ghost btn-sm" style="color:#9ca3af;border-color:#374151;">Iniciar sesión</a>
</nav>

<div style="flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div style="width:100%;max-width:520px;">
    <div style="text-align:center;margin-bottom:32px;">
      <h1 style="font-size:1.8rem;margin-bottom:6px;">Crear nueva cuenta</h1>
      <p style="color:var(--text-2);">Regístrate para gestionar tus incidencias de soporte</p>
    </div>

    <?php if($mensaje): ?>
    <div class="alert alert-<?=$tipo?>">
      <i class="fa fa-<?=$tipo=='success'?'check':'exclamation'?>-circle"></i> <?=$mensaje?>
      <?php if($tipo=='success'): ?>
      <div style="margin-top:12px;"><a href="/login.php" class="btn btn-primary btn-sm">Iniciar sesión</a></div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:16px;">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div>
              <label class="form-label">Nombre completo *</label>
              <input type="text" name="nombre" class="form-control" required value="<?=htmlspecialchars($_POST['nombre']??'')?>">
            </div>
            <div>
              <label class="form-label">Email *</label>
              <input type="email" name="email" class="form-control" required value="<?=htmlspecialchars($_POST['email']??'')?>">
            </div>
            <div>
              <label class="form-label">Empresa</label>
              <input type="text" name="empresa" class="form-control" value="<?=htmlspecialchars($_POST['empresa']??'')?>">
            </div>
            <div>
              <label class="form-label">Teléfono</label>
              <input type="tel" name="telefono" class="form-control" placeholder="666 123 456" value="<?=htmlspecialchars($_POST['telefono']??'')?>">
            </div>
          </div>
          <div>
            <label class="form-label">Contraseña * <span style="color:var(--text-3);font-weight:400;">(mínimo 6 caracteres)</span></label>
            <input type="password" name="password" id="pass1" class="form-control" minlength="6" required>
          </div>
          <div>
            <label class="form-label">Confirmar contraseña *</label>
            <input type="password" name="password_confirm" id="pass2" class="form-control" minlength="6" required>
            <div id="matchMsg" style="font-size:.8rem;margin-top:4px;"></div>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Crear cuenta</button>
        </form>
      </div>
    </div>
    <p style="text-align:center;margin-top:20px;font-size:.875rem;color:var(--text-2);">
      ¿Ya tienes cuenta? <a href="/login.php" style="color:var(--blue);font-weight:600;text-decoration:none;">Iniciar sesión</a>
    </p>
  </div>
</div>
<script>
const p1=document.getElementById('pass1'),p2=document.getElementById('pass2'),m=document.getElementById('matchMsg');
function check(){ if(!p2.value){m.textContent='';return;} if(p1.value===p2.value){m.textContent='Las contraseñas coinciden';m.style.color='var(--green)';}else{m.textContent='Las contraseñas no coinciden';m.style.color='var(--red)';} }
p1.addEventListener('input',check); p2.addEventListener('input',check);
</script>
</body>
</html>