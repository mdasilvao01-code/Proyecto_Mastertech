<?php
require_once 'db_config.php';
verificarSesion();

try {
    $pdo = getDB();
    $total    = $pdo->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
    $abiertas = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='Abierto'")->fetchColumn();
    $proceso  = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='En Proceso'")->fetchColumn();
    $resueltas= $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='Resuelto'")->fetchColumn();
    $criticas = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE prioridad='Crítica'")->fetchColumn();
    $clientes_total = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();

    if ($_SESSION['rol']=='cliente') {
        $stmt = $pdo->prepare("SELECT i.*,u.nombre as tecnico_nombre FROM incidencias i LEFT JOIN usuarios u ON i.tecnico_id=u.id WHERE i.cliente_id=? ORDER BY i.fecha_creacion DESC LIMIT 8");
        $stmt->execute([$_SESSION['usuario_id']]);
    } elseif ($_SESSION['rol']=='tecnico') {
        $stmt = $pdo->prepare("SELECT i.*,u.nombre as tecnico_nombre FROM incidencias i LEFT JOIN usuarios u ON i.tecnico_id=u.id WHERE i.tecnico_id=? ORDER BY i.fecha_creacion DESC LIMIT 8");
        $stmt->execute([$_SESSION['usuario_id']]);
    } else {
        $stmt = $pdo->query("SELECT i.*,u.nombre as tecnico_nombre FROM incidencias i LEFT JOIN usuarios u ON i.tecnico_id=u.id ORDER BY i.fecha_creacion DESC LIMIT 8");
    }
    $recientes = $stmt->fetchAll();
} catch (Exception $e) {
    $total=$abiertas=$proceso=$resueltas=$criticas=$clientes_total=0; $recientes=[];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard — Mastertech</title>
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
      <div class="topbar-title">Dashboard</div>
    </div>
    <div class="topbar-actions">
      <a href="/crear_incidencia.php" class="btn btn-primary btn-sm">
        <i class="fa fa-plus"></i> Nueva incidencia
      </a>
    </div>
  </div>

  <div class="content">
    <!-- STATS -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-indicator si-blue"><i class="fa fa-ticket"></i></div>
        <div class="stat-label">Total incidencias</div>
        <div class="stat-value"><?= $total ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-indicator si-orange"><i class="fa fa-circle-dot"></i></div>
        <div class="stat-label">Abiertas</div>
        <div class="stat-value"><?= $abiertas ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-indicator si-cyan"><i class="fa fa-gear"></i></div>
        <div class="stat-label">En proceso</div>
        <div class="stat-value"><?= $proceso ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-indicator si-green"><i class="fa fa-check"></i></div>
        <div class="stat-label">Resueltas</div>
        <div class="stat-value"><?= $resueltas ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-indicator si-red"><i class="fa fa-triangle-exclamation"></i></div>
        <div class="stat-label">Críticas</div>
        <div class="stat-value"><?= $criticas ?></div>
      </div>
      <div class="stat-card">
        <div class="stat-indicator si-purple"><i class="fa fa-users"></i></div>
        <div class="stat-label">Clientes</div>
        <div class="stat-value"><?= $clientes_total ?></div>
      </div>
    </div>

    <!-- RECENT INCIDENTS -->
    <div class="card">
      <div class="card-header">
        <h3>Incidencias recientes</h3>
        <a href="/incidencias.php" class="btn btn-ghost btn-sm">Ver todas <i class="fa fa-arrow-right"></i></a>
      </div>
      <?php if (empty($recientes)): ?>
      <div class="empty-state">
        <i class="fa fa-inbox" style="display:block;margin-bottom:12px;"></i>
        <h3>Sin incidencias</h3>
        <p style="margin-bottom:20px;">Crea la primera para empezar a gestionar el soporte.</p>
        <a href="/crear_incidencia.php" class="btn btn-primary">Nueva incidencia</a>
      </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>ID</th><th>Título</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th>Fecha</th><th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recientes as $inc): ?>
            <tr>
              <td style="color:var(--text-3);font-size:.82rem;">#<?= $inc['id'] ?></td>
              <td style="font-weight:500;"><?= htmlspecialchars($inc['titulo']) ?></td>
              <td style="color:var(--text-2);font-size:.88rem;"><?= htmlspecialchars($inc['tecnico_nombre'] ?? '—') ?></td>
              <td><span class="badge badge-<?= strtolower(str_replace(' ','',$inc['estado'])) ?>"><?= $inc['estado'] ?></span></td>
              <td><span class="badge badge-<?= strtolower($inc['prioridad']) ?>"><?= $inc['prioridad'] ?></span></td>
              <td style="color:var(--text-2);font-size:.85rem;"><?= date('d/m/Y', strtotime($inc['fecha_creacion'])) ?></td>
              <td><a href="/ver_incidencia.php?id=<?= $inc['id'] ?>" class="btn btn-ghost btn-sm">Ver</a></td>
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