<?php
require_once 'db_config.php';
verificarSesion();

$where = []; $params = [];
if ($_SESSION['rol']=='cliente') { $where[]="i.cliente_id=?"; $params[]=$_SESSION['usuario_id']; }
elseif ($_SESSION['rol']=='tecnico' && !isset($_GET['todas'])) { $where[]="i.tecnico_id=?"; $params[]=$_SESSION['usuario_id']; }


$filtro_estado    = $_GET['estado'] ?? '';
$filtro_prioridad = $_GET['prioridad'] ?? '';
$buscar           = trim($_GET['q'] ?? '');

if ($filtro_estado)    { $where[]="i.estado=?";    $params[]=$filtro_estado; }
if ($filtro_prioridad) { $where[]="i.prioridad=?"; $params[]=$filtro_prioridad; }
if ($buscar)           { $where[]="i.titulo LIKE ?"; $params[]="%$buscar%"; }

try {
    $pdo = getDB();
    $sql = "SELECT i.*, uc.nombre as cliente_nombre, ut.nombre as tecnico_nombre
            FROM incidencias i
            LEFT JOIN usuarios uc ON i.cliente_id=uc.id
            LEFT JOIN usuarios ut ON i.tecnico_id=ut.id";
    if ($where) $sql .= " WHERE ".implode(" AND ",$where);
    $sql .= " ORDER BY i.fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $incidencias = $stmt->fetchAll();
} catch (Exception $e) { $incidencias = []; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Incidencias — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div class="topbar-title">Incidencias</div>
    <div class="topbar-actions">
      <a href="/crear_incidencia.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nueva</a>
    </div>
  </div>
  <div class="content">
    <form method="GET" class="filter-bar">
      <div class="input-group" style="flex:1;min-width:200px;">
        <i class="fa fa-search input-icon"></i>
        <input type="text" name="q" class="form-control" placeholder="Buscar por título..." value="<?= htmlspecialchars($buscar) ?>">
      </div>
      <select name="estado" class="form-select" style="width:160px;">
        <option value="">Todos los estados</option>
        <?php foreach(['Abierto','En Proceso','Pendiente Cliente','Resuelto','Cerrado'] as $e): ?>
        <option value="<?=$e?>" <?= $filtro_estado==$e?'selected':'' ?>><?=$e?></option>
        <?php endforeach; ?>
      </select>
      <select name="prioridad" class="form-select" style="width:150px;">
        <option value="">Toda prioridad</option>
        <?php foreach(['Baja','Media','Alta','Crítica'] as $p): ?>
        <option value="<?=$p?>" <?= $filtro_prioridad==$p?'selected':'' ?>><?=$p?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Filtrar</button>
      <a href="/incidencias.php" class="btn btn-ghost btn-sm">Limpiar</a>
    </form>

    <div class="card">
      <div class="card-header">
        <h3>Total: <?= count($incidencias) ?> incidencias</h3>
      </div>
      <?php if (empty($incidencias)): ?>
      <div class="empty-state">
        <i class="fa fa-inbox" style="display:block;margin-bottom:12px;"></i>
        <h3>Sin resultados</h3>
        <p>No se encontraron incidencias con los filtros aplicados.</p>
      </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr><th>ID</th><th>Título</th><th>Cliente</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th>Fecha</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach($incidencias as $inc): ?>
            <tr>
              <td style="color:var(--text-3);font-size:.82rem;">#<?=$inc['id']?></td>
              <td style="font-weight:500;max-width:260px;"><?=htmlspecialchars($inc['titulo'])?></td>
              <td style="font-size:.88rem;"><?=htmlspecialchars($inc['cliente_nombre']??'—')?></td>
              <td style="font-size:.88rem;color:var(--text-2);"><?=htmlspecialchars($inc['tecnico_nombre']??'Sin asignar')?></td>
              <td><span class="badge badge-<?=strtolower(str_replace(' ','',$inc['estado']))?>"><?=$inc['estado']?></span></td>
              <td><span class="badge badge-<?=strtolower($inc['prioridad'])?>"><?=$inc['prioridad']?></span></td>
              <td style="font-size:.85rem;color:var(--text-2);"><?=date('d/m/Y',strtotime($inc['fecha_creacion']))?></td>
              <td><a href="/ver_incidencia.php?id=<?=$inc['id']?>" class="btn btn-ghost btn-sm">Ver</a></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>