<?php
$current = basename($_SERVER['PHP_SELF']);
$rol     = $_SESSION['rol']    ?? 'guest';
$nombre  = $_SESSION['nombre'] ?? '';
$inicial = strtoupper(substr($nombre, 0, 1));

$navItems = [
  ['href'=>'/dashboard.php',        'icon'=>'fa-gauge',       'label'=>'Dashboard',       'file'=>'dashboard.php',       'roles'=>['admin','tecnico','cliente']],
  ['href'=>'/incidencias.php',      'icon'=>'fa-ticket',      'label'=>'Incidencias',     'file'=>'incidencias.php',     'roles'=>['admin','tecnico','cliente']],
  ['href'=>'/crear_incidencia.php', 'icon'=>'fa-circle-plus', 'label'=>'Nueva incidencia','file'=>'crear_incidencia.php','roles'=>['admin','tecnico','cliente']],
  ['href'=>'/clientes.php',         'icon'=>'fa-users',       'label'=>'Clientes',        'file'=>'clientes.php',        'roles'=>['admin']],
  ['href'=>'/tienda.php',           'icon'=>'fa-store',       'label'=>'Tienda',          'file'=>'tienda.php',          'roles'=>['admin','tecnico','cliente']],
];
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-name">
      <span class="brand-dot"></span>
      Mastertech
    </div>
    <div class="brand-sub">Sistema de Gestión</div>
  </div>

  <?php
  $groups = [
    'Principal'  => array_filter($navItems, fn($i) => in_array($i['file'], ['dashboard.php'])),
    'Gestión'    => array_filter($navItems, fn($i) => in_array($i['file'], ['incidencias.php','crear_incidencia.php','clientes.php'])),
    'Tienda'     => array_filter($navItems, fn($i) => in_array($i['file'], ['tienda.php'])),
  ];
  foreach($groups as $title => $items):
    $visible = array_filter($items, fn($i) => in_array($rol, $i['roles']));
    if(empty($visible)) continue;
  ?>
  <div class="sidebar-section">
    <div class="sidebar-section-title"><?=$title?></div>
    <?php foreach($visible as $item): ?>
    <a href="<?=$item['href']?>" class="nav-item <?= $current==$item['file']?'active':'' ?>">
      <i class="fa <?=$item['icon']?>"></i>
      <?=$item['label']?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar"><?=htmlspecialchars($inicial)?></div>
      <div style="min-width:0;">
        <div class="user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?=htmlspecialchars($nombre)?></div>
        <div class="user-role"><?=ucfirst($rol)?></div>
      </div>
    </div>
    <a href="/logout.php" class="nav-item" style="margin-top:8px; color:var(--text-3);">
      <i class="fa fa-right-from-bracket"></i> Cerrar sesión
    </a>
  </div>
</div>
