<?php
$current = basename($_SERVER['PHP_SELF']);
$rol = $_SESSION['rol'] ?? 'guest';
$nombre = $_SESSION['nombre'] ?? '';
$inicial = strtoupper(substr($nombre, 0, 1));
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="brand-name">Mastertech</div>
    <div class="brand-sub">Sistema de Gestión</div>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-title">Principal</div>
    <nav class="sidebar-nav" style="padding:0">
      <a href="/dashboard.php" class="nav-item <?= $current=='dashboard.php'?'active':'' ?>">
        <i class="fa fa-gauge"></i> Dashboard
      </a>
    </nav>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-title">Gestión</div>
    <nav class="sidebar-nav" style="padding:0">
      <a href="/incidencias.php" class="nav-item <?= $current=='incidencias.php'?'active':'' ?>">
        <i class="fa fa-ticket"></i> Incidencias
      </a>
      <a href="/crear_incidencia.php" class="nav-item <?= $current=='crear_incidencia.php'?'active':'' ?>">
        <i class="fa fa-plus"></i> Nueva Incidencia
      </a>
      <?php if ($rol == 'admin'): ?>
      <a href="/clientes.php" class="nav-item <?= $current=='clientes.php'?'active':'' ?>">
        <i class="fa fa-users"></i> Clientes
      </a>
      <?php endif; ?>
    </nav>
  </div>

  <div class="sidebar-section">
    <div class="sidebar-section-title">Tienda</div>
    <nav class="sidebar-nav" style="padding:0">
      <a href="/tienda.php" class="nav-item <?= $current=='tienda.php'?'active':'' ?>">
        <i class="fa fa-store"></i> Catálogo
      </a>
    </nav>
  </div>

  <div class="sidebar-footer">
    <div class="user-pill">
      <div class="user-avatar"><?= htmlspecialchars($inicial) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($nombre) ?></div>
        <div class="user-role"><?= ucfirst($rol) ?></div>
      </div>
    </div>
    <a href="/logout.php" class="nav-item" style="margin-top:8px; color:#6b7280;">
      <i class="fa fa-right-from-bracket"></i> Cerrar sesión
    </a>
  </div>
</div>