<?php
require_once 'db_config.php';
verificarSesion();

try {
    $pdo      = getDB();
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

// Calc resolution rate
$resRate = $total > 0 ? round(($resueltas / $total) * 100) : 0;
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
  <style>
    /* Priority bar */
    .prio-bar { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
    .prio-bar-label { font-size:.8rem; color:var(--text-2); width:80px; flex-shrink:0; }
    .prio-bar-track { flex:1; height:6px; background:rgba(255,255,255,.06); border-radius:3px; overflow:hidden; }
    .prio-bar-fill { height:100%; border-radius:3px; transition: width .6s ease; }
    .prio-bar-count { font-size:.8rem; color:var(--text-3); width:30px; text-align:right; flex-shrink:0; }

    /* Ring chart */
    .ring-wrap { position:relative; width:100px; height:100px; margin:0 auto; }
    .ring-wrap svg { transform: rotate(-90deg); }
    .ring-center {
      position:absolute; top:50%; left:50%;
      transform:translate(-50%,-50%);
      text-align:center;
    }
    .ring-val { font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800; color:var(--text); line-height:1; }
    .ring-lbl { font-size:.65rem; color:var(--text-3); text-transform:uppercase; letter-spacing:1px; }

    /* Quick actions */
    .quick-actions {
      display:grid; grid-template-columns:1fr 1fr; gap:10px;
    }
    .qa-btn {
      display:flex; flex-direction:column; align-items:flex-start; gap:4px;
      background:var(--glass); border:1px solid var(--border);
      border-radius:var(--radius-sm); padding:14px 16px;
      text-decoration:none; color:var(--text-2);
      transition:all .15s;
      cursor:pointer;
    }
    .qa-btn:hover { background:var(--glass-2); border-color:var(--border-2); color:var(--text); }
    .qa-btn i { font-size:1rem; margin-bottom:4px; }
    .qa-btn span { font-size:.82rem; font-weight:500; }

    .welcome-banner {
      background: linear-gradient(135deg, rgba(59,130,246,.12) 0%, rgba(6,182,212,.08) 100%);
      border: 1px solid rgba(59,130,246,.2);
      border-radius: var(--radius);
      padding: 20px 24px;
      margin-bottom: 20px;
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px;
    }
    .welcome-text h3 { font-size:1rem; margin-bottom:2px; }
    .welcome-text p  { font-size:.875rem; color:var(--text-2); }

    .two-col { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }

    @media(max-width:1024px){ .two-col { grid-template-columns:1fr; } }
  </style>
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

    <!-- Welcome Banner -->
    <div class="welcome-banner">
      <div class="welcome-text">
        <h3>Hola, <?=htmlspecialchars($_SESSION['nombre'])?>! 👋</h3>
        <p>Tienes <strong style="color:var(--text)"><?=$abiertas?> incidencias abiertas</strong><?=$criticas > 0 ? " y <strong style='color:var(--red)'>{$criticas} críticas</strong>" : ''?> pendientes de atención.</p>
      </div>
      <a href="/incidencias.php?estado=Abierto" class="btn btn-primary btn-sm">Ver abiertas <i class="fa fa-arrow-right"></i></a>
    </div>

    <!-- STATS -->
    <div class="stats-row">
      <?php
      $stats=[
        ['si-blue',   'fa-ticket',             $total,     'Total'],
        ['si-orange', 'fa-circle-dot',          $abiertas,  'Abiertas'],
        ['si-cyan',   'fa-gear',                $proceso,   'En proceso'],
        ['si-green',  'fa-check-circle',        $resueltas, 'Resueltas'],
        ['si-red',    'fa-triangle-exclamation',$criticas,  'Críticas'],
        ['si-purple', 'fa-users',               $clientes_total,'Clientes'],
      ];
      foreach($stats as [$si,$icon,$val,$label]):?>
      <div class="stat-card">
        <div class="stat-indicator <?=$si?>"><i class="fa <?=$icon?>"></i></div>
        <div class="stat-label"><?=$label?></div>
        <div class="stat-value"><?=$val?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- TWO COL LAYOUT -->
    <div class="two-col">
      <!-- LEFT: Recent incidents -->
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
              <tr><th>ID</th><th>Título</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th>Fecha</th><th></th></tr>
            </thead>
            <tbody>
              <?php foreach ($recientes as $inc): ?>
              <tr>
                <td style="color:var(--text-3);font-size:.8rem;">#<?=$inc['id']?></td>
                <td style="font-weight:500;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                  <?=htmlspecialchars($inc['titulo'])?>
                </td>
                <td style="color:var(--text-2);font-size:.85rem;"><?=htmlspecialchars($inc['tecnico_nombre']??'—')?></td>
                <td><span class="badge badge-<?=strtolower(str_replace(' ','',$inc['estado']))?>"><?=$inc['estado']?></span></td>
                <td><span class="badge badge-<?=strtolower($inc['prioridad'])?>"><?=$inc['prioridad']?></span></td>
                <td style="color:var(--text-2);font-size:.82rem;white-space:nowrap;"><?=date('d/m/Y',strtotime($inc['fecha_creacion']))?></td>
                <td><a href="/ver_incidencia.php?id=<?=$inc['id']?>" class="btn btn-ghost btn-sm">Ver</a></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- RIGHT: Widgets -->
      <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Resolution rate ring -->
        <div class="card">
          <div class="card-header"><h3>Tasa de resolución</h3></div>
          <div class="card-body" style="text-align:center;">
            <?php
            $circ = 2 * M_PI * 38; // r=38
            $dash = ($resRate / 100) * $circ;
            ?>
            <div class="ring-wrap">
              <svg width="100" height="100" viewBox="0 0 100 100">
                <circle cx="50" cy="50" r="38" fill="none" stroke="rgba(255,255,255,.06)" stroke-width="8"/>
                <circle cx="50" cy="50" r="38" fill="none"
                  stroke="url(#gr)" stroke-width="8"
                  stroke-linecap="round"
                  stroke-dasharray="<?=round($dash,1)?> <?=round($circ,1)?>"
                />
                <defs>
                  <linearGradient id="gr" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#3b82f6"/>
                    <stop offset="100%" stop-color="#06b6d4"/>
                  </linearGradient>
                </defs>
              </svg>
              <div class="ring-center">
                <div class="ring-val"><?=$resRate?>%</div>
                <div class="ring-lbl">resuelto</div>
              </div>
            </div>
            <div style="margin-top:16px;display:flex;flex-direction:column;gap:6px;">
              <?php
              try {
                $pdo2 = getDB();
                $prioData = [
                  ['Crítica','var(--red)', $pdo2->query("SELECT COUNT(*) FROM incidencias WHERE prioridad='Crítica'")->fetchColumn()],
                  ['Alta','var(--orange)', $pdo2->query("SELECT COUNT(*) FROM incidencias WHERE prioridad='Alta'")->fetchColumn()],
                  ['Media','var(--blue)',  $pdo2->query("SELECT COUNT(*) FROM incidencias WHERE prioridad='Media'")->fetchColumn()],
                  ['Baja','var(--green)',  $pdo2->query("SELECT COUNT(*) FROM incidencias WHERE prioridad='Baja'")->fetchColumn()],
                ];
                $maxP = max(array_column($prioData, 2)) ?: 1;
                foreach($prioData as [$p,$c,$n]):
              ?>
              <div class="prio-bar">
                <div class="prio-bar-label"><?=$p?></div>
                <div class="prio-bar-track">
                  <div class="prio-bar-fill" style="width:<?=round(($n/$maxP)*100)?>%;background:<?=$c?>;"></div>
                </div>
                <div class="prio-bar-count"><?=$n?></div>
              </div>
              <?php endforeach; }catch(Exception $e){} ?>
            </div>
          </div>
        </div>

        <!-- Quick actions -->
        <div class="card">
          <div class="card-header"><h3>Acciones rápidas</h3></div>
          <div class="card-body">
            <div class="quick-actions">
              <a href="/crear_incidencia.php" class="qa-btn">
                <i class="fa fa-circle-plus" style="color:var(--blue);"></i>
                <span>Nueva incidencia</span>
              </a>
              <a href="/incidencias.php?estado=Abierto" class="qa-btn">
                <i class="fa fa-inbox" style="color:var(--orange);"></i>
                <span>Ver abiertas</span>
              </a>
              <?php if(in_array($_SESSION['rol'],['admin','tecnico'])): ?>
              <a href="/clientes.php" class="qa-btn">
                <i class="fa fa-users" style="color:var(--purple);"></i>
                <span>Clientes</span>
              </a>
              <?php endif; ?>
              <a href="/tienda.php" class="qa-btn">
                <i class="fa fa-store" style="color:var(--green);"></i>
                <span>Tienda</span>
              </a>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
  <?php include 'includes/footer.php'; ?>
</div>
</body>
</html>
