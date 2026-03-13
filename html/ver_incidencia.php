<?php
require_once 'db_config.php';
verificarSesion();

$id = intval($_GET['id'] ?? 0);
$mensaje = ''; $tipo = '';
$pdo = getDB();

$stmt = $pdo->prepare("SELECT i.*, uc.nombre as cliente_nombre, uc.email as cliente_email, ut.nombre as tecnico_nombre
    FROM incidencias i
    LEFT JOIN usuarios uc ON i.cliente_id=uc.id
    LEFT JOIN usuarios ut ON i.tecnico_id=ut.id
    WHERE i.id=?");
$stmt->execute([$id]);
$inc = $stmt->fetch();
if (!$inc) { header('Location: incidencias.php'); exit(); }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (isset($_POST['comentario']) && trim($_POST['comentario'])) {
        $pdo->prepare("INSERT INTO comentarios (incidencia_id,usuario_id,comentario) VALUES (?,?,?)")
            ->execute([$id, $_SESSION['usuario_id'], trim($_POST['comentario'])]);
        $mensaje='Comentario añadido'; $tipo='success';
    }
    if (isset($_POST['nuevo_estado']) && in_array($_SESSION['rol'],['admin','tecnico'])) {
        $pdo->prepare("UPDATE incidencias SET estado=? WHERE id=?")->execute([$_POST['nuevo_estado'],$id]);
        $inc['estado'] = $_POST['nuevo_estado'];
        $mensaje='Estado actualizado'; $tipo='success';
    }
    if (isset($_POST['asignar_tecnico']) && $_SESSION['rol']=='admin') {
        $pdo->prepare("UPDATE incidencias SET tecnico_id=?,estado='En Proceso' WHERE id=?")->execute([$_POST['tecnico_id'],$id]);
        header("Location: ver_incidencia.php?id=$id"); exit();
    }
}

$stmt = $pdo->prepare("SELECT c.*,u.nombre as usuario_nombre,u.rol FROM comentarios c JOIN usuarios u ON c.usuario_id=u.id WHERE c.incidencia_id=? ORDER BY c.fecha DESC");
$stmt->execute([$id]);
$comentarios = $stmt->fetchAll();

$tecnicos = [];
if ($_SESSION['rol']=='admin')
    $tecnicos = $pdo->query("SELECT id,nombre FROM usuarios WHERE rol='tecnico'")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Incidencia #<?=$id?> — Mastertech</title>
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
      <div class="breadcrumb">
        <a href="/incidencias.php">Incidencias</a>
        <span class="sep">/</span>
        <span>#<?=$id?></span>
      </div>
      <div class="topbar-title"><?=htmlspecialchars($inc['titulo'])?></div>
    </div>
    <div class="topbar-actions">
      <a href="/generar_informe.php?id=<?=$id?>&tipo=pdf" class="btn btn-ghost btn-sm" target="_blank"><i class="fa fa-file-pdf"></i> PDF</a>
      <a href="/generar_informe.php?id=<?=$id?>&tipo=txt" class="btn btn-ghost btn-sm"><i class="fa fa-file-lines"></i> TXT</a>
      <a href="/incidencias.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
    </div>
  </div>

  <div class="content">
    <?php if($mensaje): ?>
    <div class="alert alert-<?=$tipo?>"><i class="fa fa-<?=$tipo=='success'?'check':'exclamation'?>-circle"></i> <?=$mensaje?></div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">
      <!-- LEFT -->
      <div style="display:flex;flex-direction:column;gap:20px;">
        <!-- Detail card -->
        <div class="card">
          <div class="card-header">
            <h3>Detalles de la incidencia</h3>
            <div style="display:flex;gap:8px;">
              <span class="badge badge-<?=strtolower(str_replace(' ','',$inc['estado']))?>"><?=$inc['estado']?></span>
              <span class="badge badge-<?=strtolower($inc['prioridad'])?>"><?=$inc['prioridad']?></span>
            </div>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
              <div>
                <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;margin-bottom:4px;">Cliente</div>
                <div style="font-weight:500;"><?=htmlspecialchars($inc['cliente_nombre'])?></div>
                <div style="font-size:.85rem;color:var(--text-2);"><?=htmlspecialchars($inc['cliente_email'])?></div>
              </div>
              <div>
                <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;margin-bottom:4px;">Técnico</div>
                <div style="font-weight:500;"><?=htmlspecialchars($inc['tecnico_nombre']??'Sin asignar')?></div>
              </div>
              <div>
                <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;margin-bottom:4px;">Categoría</div>
                <div><?=$inc['categoria']?></div>
              </div>
              <div>
                <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;margin-bottom:4px;">Fecha</div>
                <div><?=date('d/m/Y H:i',strtotime($inc['fecha_creacion']))?></div>
              </div>
            </div>
            <hr>
            <div style="margin-top:16px;">
              <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;margin-bottom:10px;">Descripción</div>
              <p style="line-height:1.7;color:var(--text);"><?=nl2br(htmlspecialchars($inc['descripcion']))?></p>
            </div>
          </div>
        </div>

        <!-- Comments -->
        <div class="card">
          <div class="card-header"><h3>Comentarios (<?=count($comentarios)?>)</h3></div>
          <div class="card-body">
            <form method="POST" style="margin-bottom:24px;">
              <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe un comentario o actualización..."></textarea>
              <button type="submit" class="btn btn-primary btn-sm" style="margin-top:10px;"><i class="fa fa-paper-plane"></i> Enviar</button>
            </form>
            <?php if(empty($comentarios)): ?>
            <p style="color:var(--text-3);font-size:.9rem;text-align:center;padding:20px 0;">Sin comentarios aún.</p>
            <?php else: ?>
            <div class="comment-thread">
              <?php foreach($comentarios as $com):
                $ini = strtoupper(substr($com['usuario_nombre'],0,1));
              ?>
              <div class="comment-item">
                <div class="comment-avatar"><?=$ini?></div>
                <div class="comment-bubble">
                  <div class="comment-meta">
                    <strong><?=htmlspecialchars($com['usuario_nombre'])?></strong>
                    <span class="badge badge-<?=$com['rol']?>" style="font-size:.7rem;"><?=ucfirst($com['rol'])?></span>
                    <span><?=date('d/m/Y H:i',strtotime($com['fecha']))?></span>
                  </div>
                  <div class="comment-text"><?=nl2br(htmlspecialchars($com['comentario']))?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT SIDEBAR -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <?php if(in_array($_SESSION['rol'],['admin','tecnico'])): ?>
        <div class="card">
          <div class="card-header"><h3>Actualizar estado</h3></div>
          <div class="card-body">
            <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
              <select name="nuevo_estado" class="form-select">
                <?php foreach(['Abierto','En Proceso','Pendiente Cliente','Resuelto','Cerrado'] as $e): ?>
                <option value="<?=$e?>" <?=$inc['estado']==$e?'selected':''?>><?=$e?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Actualizar</button>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <?php if($_SESSION['rol']=='admin' && empty($inc['tecnico_id'])): ?>
        <div class="card">
          <div class="card-header"><h3>Asignar técnico</h3></div>
          <div class="card-body">
            <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
              <select name="tecnico_id" class="form-select" required>
                <option value="">Seleccionar...</option>
                <?php foreach($tecnicos as $t): ?>
                <option value="<?=$t['id']?>"><?=htmlspecialchars($t['nombre'])?></option>
                <?php endforeach; ?>
              </select>
              <button type="submit" name="asignar_tecnico" class="btn btn-success"><i class="fa fa-user-check"></i> Asignar</button>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-header"><h3>Exportar</h3></div>
          <div class="card-body" style="display:flex;flex-direction:column;gap:8px;">
            <a href="/generar_informe.php?id=<?=$id?>&tipo=pdf" target="_blank" class="btn btn-ghost"><i class="fa fa-file-pdf"></i> Descargar PDF</a>
            <a href="/generar_informe.php?id=<?=$id?>&tipo=txt" class="btn btn-ghost"><i class="fa fa-file-lines"></i> Descargar TXT</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>