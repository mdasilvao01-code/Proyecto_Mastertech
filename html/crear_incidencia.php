<?php
require_once 'db_config.php';
verificarSesion();
$mensaje = ''; $tipo = '';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        $pdo = getDB();
        $titulo      = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $prioridad   = $_POST['prioridad'];
        $categoria   = $_POST['categoria'];
        $cliente_id  = $_SESSION['rol']=='cliente' ? $_SESSION['usuario_id'] : ($_POST['cliente_id'] ?? $_SESSION['usuario_id']);
        $stmt = $pdo->prepare("INSERT INTO incidencias (titulo,descripcion,prioridad,categoria,cliente_id) VALUES (?,?,?,?,?)");
        $stmt->execute([$titulo,$descripcion,$prioridad,$categoria,$cliente_id]);
        $new_id = $pdo->lastInsertId();
        logAccion('Nueva incidencia',"ID:$new_id");
        header("Location: /ver_incidencia.php?id=$new_id"); exit();
    } catch (Exception $e) { $mensaje="Error: ".$e->getMessage(); $tipo='danger'; }
}

$clientes=[];
if ($_SESSION['rol']!='cliente') {
    try {
        $pdo=getDB();
        $clientes=$pdo->query("SELECT id,nombre FROM usuarios WHERE rol='cliente'")->fetchAll();
    } catch(Exception $e){}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Nueva Incidencia — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div>
      <div class="breadcrumb"><a href="/incidencias.php">Incidencias</a><span class="sep">/</span><span>Nueva</span></div>
      <div class="topbar-title">Crear incidencia</div>
    </div>
    <a href="/incidencias.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
  </div>
  <div class="content" style="max-width:720px;">
    <?php if($mensaje): ?>
    <div class="alert alert-<?=$tipo?>"><?=$mensaje?></div>
    <?php endif; ?>
    <div class="card">
      <div class="card-body">
        <form method="POST" style="display:flex;flex-direction:column;gap:20px;">
          <?php if($_SESSION['rol']!='cliente' && !empty($clientes)): ?>
          <div>
            <label class="form-label">Cliente</label>
            <select name="cliente_id" class="form-select">
              <option value="">Seleccionar cliente...</option>
              <?php foreach($clientes as $c): ?>
              <option value="<?=$c['id']?>"><?=htmlspecialchars($c['nombre'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>

          <div>
            <label class="form-label">Título <span style="color:var(--red)">*</span></label>
            <input type="text" name="titulo" class="form-control" placeholder="Describe brevemente el problema..." required>
          </div>

          <div>
            <label class="form-label">Descripción <span style="color:var(--red)">*</span></label>
            <textarea name="descripcion" class="form-control" rows="6" placeholder="Describe el problema en detalle, pasos para reproducirlo, etc." required></textarea>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
              <label class="form-label">Prioridad</label>
              <select name="prioridad" class="form-select">
                <option value="Baja">Baja</option>
                <option value="Media" selected>Media</option>
                <option value="Alta">Alta</option>
                <option value="Crítica">Crítica</option>
              </select>
            </div>
            <div>
              <label class="form-label">Categoría</label>
              <select name="categoria" class="form-select">
                <?php foreach(['Hardware','Software','Red','Seguridad','Base de Datos','Correo Electrónico','Otros'] as $cat): ?>
                <option value="<?=$cat?>"><?=$cat?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display:flex;gap:12px;padding-top:4px;">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Crear incidencia</button>
            <a href="/incidencias.php" class="btn btn-ghost">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>