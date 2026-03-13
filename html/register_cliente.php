<?php
require_once 'db_config.php';
$mensaje=''; $tipo='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $nombre=trim($_POST['nombre']??'');
        $email=trim($_POST['email']??'');
        $telefono=trim($_POST['telefono']??'');
        $empresa=trim($_POST['empresa']??'');
        if(empty($nombre)||empty($email)) throw new Exception('Nombre y email son obligatorios');
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new Exception('Email no válido');
        $stmt=$conexion->prepare("INSERT INTO cliente (nombre,email,telefono,empresa) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss",$nombre,$email,$telefono,$empresa);
        if($stmt->execute()){
            logAccion('Registro cliente',"Cliente: $nombre");
            $mensaje="Cliente registrado correctamente"; $tipo='success'; $_POST=[];
        } else throw new Exception($conexion->error);
        $stmt->close();
    }catch(Exception $e){ $mensaje=$e->getMessage(); $tipo='danger'; }
}
$loggedIn=isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nuevo Cliente — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php if($loggedIn): include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div>
      <div class="breadcrumb"><a href="/clientes.php">Clientes</a><span class="sep">/</span><span>Nuevo</span></div>
      <div class="topbar-title">Registrar cliente</div>
    </div>
    <a href="/clientes.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
  </div>
  <div class="content" style="max-width:600px;">
<?php else: ?>
<nav style="background:var(--navy);padding:0 32px;height:64px;display:flex;align-items:center;">
  <span style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;">Mastertech</span>
</nav>
<div style="padding:40px;max-width:600px;margin:0 auto;">
<?php endif; ?>

    <?php if($mensaje): ?>
    <div class="alert alert-<?=$tipo?>">
      <i class="fa fa-<?=$tipo=='success'?'check':'exclamation'?>-circle"></i> <?=$mensaje?>
      <?php if($tipo=='success'): ?>
      <div style="margin-top:10px;">
        <a href="/clientes.php" class="btn btn-primary btn-sm">Ver clientes</a>
        <a href="/register_cliente.php" class="btn btn-ghost btn-sm">Registrar otro</a>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:18px;">
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
              <label class="form-label">Teléfono</label>
              <input type="tel" name="telefono" class="form-control" placeholder="666 123 456" value="<?=htmlspecialchars($_POST['telefono']??'')?>">
            </div>
            <div>
              <label class="form-label">Empresa</label>
              <input type="text" name="empresa" class="form-control" value="<?=htmlspecialchars($_POST['empresa']??'')?>">
            </div>
          </div>
          <div style="display:flex;gap:10px;padding-top:4px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Registrar cliente</button>
            <a href="<?=$loggedIn?'/clientes.php':'/index.php'?>" class="btn btn-ghost">Cancelar</a>
          </div>
        </form>
      </div>
    </div>

<?php if($loggedIn): ?>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<?php else: ?>
</div>
<?php endif; ?>
</body>
</html>