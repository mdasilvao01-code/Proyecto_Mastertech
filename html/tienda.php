<?php
require_once 'db_config.php';

if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

$cat    = $_GET['categoria'] ?? '';
$buscar = trim($_GET['q'] ?? '');
$orden  = $_GET['orden'] ?? 'destacado';

try {
    $pdo = getDB();
    $where = []; $params = [];
    if (!empty($cat))    { $where[] = "categoria=?";   $params[] = $cat; }
    if (!empty($buscar)) { $where[] = "nombre LIKE ?"; $params[] = "%$buscar%"; }

    $order_map = [
        'destacado'   => 'destacado DESC, nombre',
        'precio_asc'  => 'precio ASC',
        'precio_desc' => 'precio DESC',
        'nombre'      => 'nombre ASC',
    ];
    $order_sql = $order_map[$orden] ?? 'destacado DESC, nombre';

    $sql  = "SELECT * FROM productos"
          . ($where ? " WHERE " . implode(" AND ", $where) : "")
          . " ORDER BY $order_sql";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll();

    $categorias  = $pdo->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria")->fetchAll();
    $total_prods = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
} catch (Exception $e) { $productos = []; $categorias = []; $total_prods = 0; }

$icons = [
    'Ordenadores' => 'fa-desktop',   'Portátiles'  => 'fa-laptop',
    'Componentes' => 'fa-microchip', 'Periféricos' => 'fa-computer-mouse',
    'Redes'       => 'fa-network-wired', 'Servidores' => 'fa-server',
];
$loggedIn      = isset($_SESSION['usuario_id']);
$total_carrito = array_sum($_SESSION['carrito']);
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
  <style>
    .cat-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px;}
    .cat-tab{display:inline-flex;align-items:center;gap:7px;padding:7px 16px;border-radius:100px;font-size:.82rem;font-weight:600;text-decoration:none;border:1px solid var(--border);color:var(--text-2);background:transparent;transition:all .15s;}
    .cat-tab:hover{background:var(--glass-2);color:var(--text);border-color:var(--border-2);}
    .cat-tab.active{background:var(--blue);color:#fff;border-color:var(--blue);box-shadow:0 0 14px var(--blue-glow);}
    .tienda-header{padding:28px 0 20px;}
    .tienda-header h1{font-size:1.5rem;letter-spacing:-.4px;}
    .tienda-header p{color:var(--text-2);font-size:.875rem;margin-top:4px;}
    .tienda-toolbar{display:flex;gap:10px;align-items:center;margin-bottom:24px;flex-wrap:wrap;}
    .tienda-toolbar .input-group{flex:1;min-width:220px;}
    .product-card{position:relative;}
    .product-thumb{background:linear-gradient(135deg,#060e1c 0%,#0c1d38 60%,#0a1730 100%);height:190px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
    .product-thumb::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 130%,rgba(59,130,246,.12) 0%,transparent 60%);}
    .product-thumb img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;transition:transform .4s ease;}
    .product-card:hover .product-thumb img{transform:scale(1.05);}
    .thumb-icon{font-size:2.8rem;color:rgba(255,255,255,.15);position:relative;z-index:1;transition:all .2s;}
    .product-card:hover .thumb-icon{transform:scale(1.1);color:rgba(59,130,246,.4);}
    .product-cat-tag{position:absolute;top:11px;left:11px;z-index:2;background:rgba(59,130,246,.8);backdrop-filter:blur(4px);color:#fff;font-size:.65rem;font-weight:700;padding:3px 9px;border-radius:100px;letter-spacing:.5px;text-transform:uppercase;}
    .featured-tag{position:absolute;top:11px;right:11px;z-index:2;background:rgba(245,158,11,.85);backdrop-filter:blur(4px);color:#fff;font-size:.65rem;font-weight:700;padding:3px 9px;border-radius:100px;}
    .product-info{padding:16px;}
    .product-name{font-family:'Syne',sans-serif;font-weight:700;font-size:.9rem;margin-bottom:5px;color:var(--text);}
    .product-desc{font-size:.8rem;color:var(--text-3);line-height:1.45;margin-bottom:12px;min-height:36px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
    .product-footer{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
    .product-price{font-family:'Syne',sans-serif;font-size:1.35rem;font-weight:800;color:var(--blue);letter-spacing:-.3px;}
    .btn-addcart{width:100%;justify-content:center;gap:7px;background:var(--blue);color:#fff;border-radius:var(--radius-xs);padding:9px 14px;font-size:.82rem;font-weight:600;border:none;cursor:pointer;display:flex;align-items:center;transition:all .15s;text-decoration:none;font-family:'DM Sans',sans-serif;}
    .btn-addcart:hover{background:var(--blue-dark);box-shadow:0 0 16px var(--blue-glow);}
    .btn-addcart.in-cart{background:rgba(16,185,129,.12);color:var(--green);border:1px solid rgba(16,185,129,.3);}
    .btn-detail{margin-top:7px;padding:7px 10px;background:var(--glass);border:1px solid var(--border);color:var(--text-2);border-radius:var(--radius-xs);font-size:.8rem;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .15s;font-family:'DM Sans',sans-serif;}
    .btn-detail:hover{border-color:var(--border-2);color:var(--text);}
    .cart-fab{position:fixed;bottom:28px;right:28px;z-index:400;display:none;}
    .cart-fab.show{display:block;}
    .cart-fab a{background:var(--blue);color:#fff;border-radius:50px;padding:13px 22px;font-size:.9rem;font-weight:600;display:flex;align-items:center;gap:10px;box-shadow:0 4px 20px var(--blue-glow),0 8px 32px rgba(0,0,0,.4);text-decoration:none;font-family:'DM Sans',sans-serif;transition:all .2s;}
    .cart-fab a:hover{background:var(--blue-dark);transform:translateY(-2px);}
    .fab-badge{background:#fff;color:var(--blue);border-radius:50%;width:22px;height:22px;font-size:.78rem;font-weight:800;display:flex;align-items:center;justify-content:center;}
  </style>
</head>
<body>

<?php if ($loggedIn): include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div class="topbar-title">Tienda</div>
    <a href="/carrito.php" class="btn btn-ghost btn-sm" style="position:relative;">
      <i class="fa fa-cart-shopping"></i> Carrito
      <?php if ($total_carrito > 0): ?>
      <span class="cart-badge" style="position:absolute;top:-7px;right:-7px;background:var(--blue);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;display:none;"></span>
      <?php endif; ?>
    </a>
  </div>
<?php else: ?>
<nav style="background:rgba(6,13,26,.95);backdrop-filter:blur(12px);padding:0 32px;height:62px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;">
  <a href="/index.php" style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:1.2rem;text-decoration:none;display:flex;align-items:center;gap:8px;"><span class="brand-dot"></span>Mastertech</a>
  <div style="display:flex;gap:10px;align-items:center;">
    <a href="/carrito.php" class="btn btn-ghost btn-sm" style="position:relative;">
      <i class="fa fa-cart-shopping"></i> Carrito
      <?php if ($total_carrito > 0): ?>
      <span class="cart-badge" style="position:absolute;top:-7px;right:-7px;background:var(--blue);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1;display:none;"></span>
      <?php endif; ?>
    </a>
    <a href="/login.php"    class="btn btn-ghost btn-sm">Acceder</a>
    <a href="/registro.php" class="btn btn-primary btn-sm">Crear cuenta</a>
  </div>
</nav>
<?php endif; ?>

<div class="content">
  <div class="tienda-header">
    <h1>Catálogo de productos</h1>
    <p><?= $total_prods ?> productos<?= $cat ? " en <strong style='color:var(--text);'>{$cat}</strong>" : '' ?><?= $buscar ? " — búsqueda: <strong style='color:var(--text);'>\"".htmlspecialchars($buscar)."\"</strong>" : '' ?></p>
  </div>

  <form method="GET" class="tienda-toolbar">
    <?php if ($cat): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
    <div class="input-group">
      <i class="fa fa-search input-icon"></i>
      <input type="text" name="q" class="form-control" placeholder="Buscar productos..." value="<?= htmlspecialchars($buscar) ?>">
    </div>
    <select name="orden" class="form-select" style="width:185px;" onchange="this.form.submit()">
      <option value="destacado"   <?= $orden=='destacado'   ?'selected':'' ?>>Destacados primero</option>
      <option value="precio_asc"  <?= $orden=='precio_asc'  ?'selected':'' ?>>Precio: menor a mayor</option>
      <option value="precio_desc" <?= $orden=='precio_desc' ?'selected':'' ?>>Precio: mayor a menor</option>
      <option value="nombre"      <?= $orden=='nombre'      ?'selected':'' ?>>Nombre A-Z</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Buscar</button>
    <?php if ($buscar || $cat): ?>
    <a href="/tienda.php" class="btn btn-ghost btn-sm">Limpiar</a>
    <?php endif; ?>
  </form>

  <div class="cat-tabs">
    <a href="/tienda.php<?= $buscar?'?q='.urlencode($buscar):'' ?>" class="cat-tab <?= empty($cat)?'active':'' ?>">
      <i class="fa fa-th-large"></i> Todos (<?= $total_prods ?>)
    </a>
    <?php foreach ($categorias as $c): ?>
    <a href="/tienda.php?categoria=<?= urlencode($c['categoria']) ?><?= $buscar?'&q='.urlencode($buscar):'' ?>"
       class="cat-tab <?= $cat==$c['categoria']?'active':'' ?>">
      <i class="fa <?= $icons[$c['categoria']] ?? 'fa-box' ?>"></i> <?= $c['categoria'] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($productos)): ?>
  <div class="empty-state">
    <i class="fa fa-store" style="display:block;margin-bottom:12px;"></i>
    <h3>Sin resultados</h3>
    <p>No se encontraron productos con los filtros aplicados.</p>
    <a href="/tienda.php" class="btn btn-ghost" style="margin-top:16px;">Ver todos los productos</a>
  </div>
  <?php else: ?>
  <div class="product-grid">
    <?php foreach ($productos as $p):
      $en_carrito = !empty($_SESSION['carrito'][$p['id']]);
    ?>
    <div class="product-card">
      <div class="product-thumb">
        <?php if (!empty($p['imagen'])): ?>
          <img src="<?= htmlspecialchars($p['imagen']) ?>"
               alt="<?= htmlspecialchars($p['nombre']) ?>"
               loading="lazy"
               style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;"
               onload="this.style.opacity='1';this.nextElementSibling.style.display='none';"
               onerror="this.style.display='none';"
               >
        <?php endif; ?>
        <i class="fa <?= $icons[$p['categoria']] ?? 'fa-box' ?> thumb-icon"></i>
        <span class="product-cat-tag"><?= $p['categoria'] ?></span>
        <?php if ($p['destacado']): ?>
          <span class="featured-tag"><i class="fa fa-star"></i> Destacado</span>
        <?php endif; ?>
      </div>

      <div class="product-info">
        <div class="product-name"><?= htmlspecialchars($p['nombre']) ?></div>
        <div class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></div>

        <div class="product-footer">
          <div class="product-price"><?= number_format($p['precio'], 2) ?>€</div>
          <?php if ($p['stock'] > 10): ?>
            <span class="badge badge-enproceso" style="font-size:.68rem;"><i class="fa fa-check" style="margin-right:3px;"></i>En stock</span>
          <?php elseif ($p['stock'] > 0): ?>
            <span class="badge badge-pendiente" style="font-size:.68rem;">Últimas <?= $p['stock'] ?> ud.</span>
          <?php else: ?>
            <span class="badge badge-cerrado" style="font-size:.68rem;">Agotado</span>
          <?php endif; ?>
        </div>

        <?php if ($p['stock'] > 0): ?>
        <button class="btn-addcart"
                onclick="addToCart('<?= $p['id'] ?>','<?= addslashes($p['nombre']) ?>',<?= $p['precio'] ?>,'<?= addslashes($p['categoria']) ?>','<?= addslashes($p['imagen'] ?? '') ?>')">
          <i class="fa fa-cart-plus"></i> Añadir al carrito
        </button>
        <?php else: ?>
        <button class="btn-addcart" disabled style="opacity:.4;cursor:not-allowed;">
          <i class="fa fa-ban"></i> Sin stock
        </button>
        <?php endif; ?>

        <a href="/producto.php?id=<?= $p['id'] ?>" class="btn-detail">
          <i class="fa fa-eye"></i> Ver detalles
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<div class="cart-fab <?= $total_carrito > 0 ? 'show' : '' ?>">
  <a href="/carrito.php">
    <i class="fa fa-cart-shopping"></i>
    <span>Ver carrito</span>
    <div class="fab-badge"><?= $total_carrito ?></div>
  </a>
</div>

<?php if ($loggedIn): include 'includes/footer.php'; ?>
</div>
<?php else: ?>
<footer style="padding:20px 32px;border-top:1px solid var(--border);background:var(--navy);margin-top:40px;">
  <p style="font-size:.78rem;color:var(--text-3);">&copy; <?= date('Y') ?> Mastertech</p>
</footer>
<?php endif; ?>
<script>
const CART_KEY='mastertech_cart';
function getCart(){try{return JSON.parse(localStorage.getItem(CART_KEY)||'{}')}catch(e){return{}}}
function saveCart(c){localStorage.setItem(CART_KEY,JSON.stringify(c))}

function addToCart(id,name,price,cat,img){
  const c=getCart(); const sid=String(id);
  if(c[sid]){c[sid].qty++}else{c[sid]={id:sid,name:name,price:price,cat:cat,img:img,qty:1}}
  saveCart(c);
  updateBadge();
 
  document.querySelectorAll('[onclick*="addToCart(\''+id+'\'"]').forEach(btn=>{
    btn.classList.add('in-cart');
    btn.innerHTML='<i class="fa fa-check"></i> Añadido ('+c[sid].qty+')';
    btn.setAttribute('onclick',btn.getAttribute('onclick'));
  });
}

function updateBadge(){
  const total=Object.values(getCart()).reduce((s,i)=>s+i.qty,0);
  document.querySelectorAll('.cart-badge').forEach(b=>{b.textContent=total;b.style.display=total>0?'flex':'none'});
  const fab=document.querySelector('.cart-fab');
  if(fab) fab.classList.toggle('show',total>0);
  const fb=document.querySelector('.fab-badge');
  if(fb) fb.textContent=total;
}

(function(){
  updateBadge();
  const c=getCart();
  Object.keys(c).forEach(id=>{
    document.querySelectorAll('[onclick*="addToCart(\''+id+'\'"]').forEach(btn=>{
      btn.classList.add('in-cart');
      btn.innerHTML='<i class="fa fa-check"></i> Añadido ('+c[id].qty+')';
    });
  });
})();
</script>
</body>
</html>