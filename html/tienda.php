<?php
require_once 'db_config.php';
$cat = $_GET['categoria'] ?? '';
try {
    $pdo = getDB();
    if (empty($cat)) {
        $productos = $pdo->query("SELECT * FROM productos ORDER BY destacado DESC, nombre")->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria=? ORDER BY nombre");
        $stmt->execute([$cat]); $productos = $stmt->fetchAll();
    }
    $categorias = $pdo->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria")->fetchAll();
} catch(Exception $e){ $productos=[]; $categorias=[]; }

$icons = ['Ordenadores'=>'fa-desktop','Portátiles'=>'fa-laptop','Componentes'=>'fa-microchip',
          'Periféricos'=>'fa-computer-mouse','Redes'=>'fa-network-wired','Servidores'=>'fa-server'];
$loggedIn = isset($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tienda — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php if($loggedIn): include 'includes/navbar.php'; ?>
<div class="main-wrapper">
<?php else: ?>
<nav style="background:var(--navy);padding:0 32px;height:64px;display:flex;align-items:center;justify-content:space-between;">
  <span style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:1.2rem;">Mastertech</span>
  <div style="display:flex;gap:8px;">
    <a href="/login.php" class="btn btn-ghost btn-sm" style="color:#9ca3af;border-color:#374151;">Acceder</a>
    <a href="/registro.php" class="btn btn-primary btn-sm">Crear cuenta</a>
  </div>
</nav>
<?php endif; ?>

<?php if($loggedIn): ?>
<div class="topbar">
  <div class="topbar-title">Tienda</div>
</div>
<div class="content">
<?php else: ?>
<div style="padding:32px;">
<?php endif; ?>

  <!-- Category tabs -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;">
    <a href="/tienda.php" class="btn <?=empty($cat)?'btn-dark':'btn-ghost'?> btn-sm">Todos</a>
    <?php foreach($categorias as $c): ?>
    <a href="/tienda.php?categoria=<?=urlencode($c['categoria'])?>" class="btn <?=$cat==$c['categoria']?'btn-dark':'btn-ghost'?> btn-sm">
      <i class="fa <?=$icons[$c['categoria']]??'fa-box'?>"></i> <?=$c['categoria']?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if(empty($productos)): ?>
  <div class="empty-state"><i class="fa fa-store" style="display:block;margin-bottom:12px;"></i><h3>Sin productos</h3></div>
  <?php else: ?>
  <div class="product-grid">
    <?php foreach($productos as $p): ?>
    <div class="product-card">
      <div class="product-thumb">
        <i class="fa <?=$icons[$p['categoria']]??'fa-box'?>"></i>
        <span class="product-cat-tag"><?=$p['categoria']?></span>
        <?php if($p['destacado']): ?><span class="featured-tag">Destacado</span><?php endif; ?>
      </div>
      <div class="product-info">
        <div class="product-name"><?=htmlspecialchars($p['nombre'])?></div>
        <div style="font-size:.82rem;color:var(--text-2);margin:6px 0 12px;line-height:1.5;min-height:40px;">
          <?=htmlspecialchars(substr($p['descripcion'],0,75))?>...
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <div class="product-price"><?=number_format($p['precio'],2)?>€</div>
          <?php if($p['stock']>10): ?>
            <span class="badge badge-enproceso">En stock</span>
          <?php elseif($p['stock']>0): ?>
            <span class="badge badge-pendiente">Pocas unidades</span>
          <?php else: ?>
            <span class="badge badge-cerrado">Agotado</span>
          <?php endif; ?>
        </div>
        <a href="/producto.php?id=<?=$p['id']?>" class="btn btn-primary" style="width:100%;justify-content:center;">Ver detalles</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php if($loggedIn): ?>
  <?php include 'includes/footer.php'; ?>
</div>
<?php else: ?>
<footer style="padding:24px 32px;border-top:1px solid var(--border);background:var(--card);margin-top:40px;">
  <p style="font-size:.8rem;color:var(--text-3);">&copy; <?=date('Y')?> Mastertech</p>
</footer>
<?php endif; ?>
</body>
</html>