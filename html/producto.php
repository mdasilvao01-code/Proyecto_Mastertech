<?php
require_once 'db_config.php';
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];
$id = intval($_GET['id'] ?? 0);
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    if (!$producto) { header('Location: /tienda.php'); exit(); }
    $rel_stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria=? AND id!=? ORDER BY destacado DESC LIMIT 4");
    $rel_stmt->execute([$producto['categoria'], $id]);
    $relacionados = $rel_stmt->fetchAll();
} catch (Exception $e) { header('Location: /tienda.php'); exit(); }
$icons = ['Ordenadores'=>'fa-desktop','Portátiles'=>'fa-laptop','Componentes'=>'fa-microchip','Periféricos'=>'fa-computer-mouse','Redes'=>'fa-network-wired','Servidores'=>'fa-server'];
$loggedIn    = isset($_SESSION['usuario_id']);
$en_carrito  = !empty($_SESSION['carrito'][$id]);
$qty_carrito = $_SESSION['carrito'][$id] ?? 0;
$tc          = array_sum($_SESSION['carrito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($producto['nombre']) ?> — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
  <style>
    .prod-layout{display:grid;grid-template-columns:1fr 1fr;gap:28px;align-items:start;}
    @media(max-width:800px){.prod-layout{grid-template-columns:1fr;}}
    .prod-img-wrap{height:380px;background:linear-gradient(135deg,#060e1c 0%,#0c1d38 50%,#091830 100%);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
    .prod-img-wrap::before{content:'';position:absolute;inset:0;background:radial-gradient(circle at 50% 100%,rgba(59,130,246,.15) 0%,transparent 60%);}
    .prod-img-wrap img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
    .prod-img-icon{font-size:8rem;color:rgba(59,130,246,.3);position:relative;z-index:1;}
    .prod-thumbs{display:flex;gap:8px;padding:12px;}
    .prod-thumb-mini{width:58px;height:58px;border-radius:var(--radius-xs);border:1px solid var(--border);background:var(--navy-3);display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .prod-thumb-mini img{width:100%;height:100%;object-fit:cover;}
    .price-box{background:var(--card);border:1px solid var(--border);border-radius:var(--radius);padding:20px;}
    .big-price{font-family:'Syne',sans-serif;font-size:2.6rem;font-weight:800;color:var(--blue);letter-spacing:-.5px;line-height:1;margin-bottom:10px;}
    .qty-ctrl{display:flex;align-items:center;gap:0;background:var(--navy-3);border:1px solid var(--border);border-radius:var(--radius-xs);overflow:hidden;}
    .qbtn{width:36px;height:38px;background:none;border:none;color:var(--text-2);cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .1s;font-size:.85rem;}
    .qbtn:hover{background:var(--glass-2);color:var(--text);}
    .qinp{width:48px;height:38px;background:none;border:none;border-left:1px solid var(--border);border-right:1px solid var(--border);color:var(--text);font-size:.95rem;font-weight:600;text-align:center;font-family:'DM Sans',sans-serif;}
    .qinp::-webkit-inner-spin-button{display:none;}
    .btn-buy{width:100%;justify-content:center;padding:12px;font-size:.95rem;background:var(--blue);color:#fff;border:none;border-radius:var(--radius-sm);cursor:pointer;display:flex;align-items:center;gap:8px;font-weight:600;font-family:'DM Sans',sans-serif;transition:all .15s;}
    .btn-buy:hover{background:var(--blue-dark);box-shadow:0 0 20px var(--blue-glow);}
    .btn-buy.in-cart{background:rgba(16,185,129,.12);color:var(--green);border:1px solid rgba(16,185,129,.3);}
    .spec-key{color:var(--text-3);font-weight:600;font-size:.85rem;padding:10px 22px;border-right:1px solid var(--border);width:180px;vertical-align:middle;}
    .spec-val{padding:10px 22px;font-size:.88rem;vertical-align:middle;}
    .rel-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px;}
    .rel-card{background:var(--card);border:1px solid var(--border);border-radius:var(--radius-sm);overflow:hidden;transition:all .2s;text-decoration:none;display:block;}
    .rel-card:hover{border-color:rgba(59,130,246,.35);transform:translateY(-3px);}
    .rel-thumb{height:100px;background:linear-gradient(135deg,#060e1c,#0c1d38);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
    .rel-thumb img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;}
    .rel-thumb i{font-size:1.8rem;color:rgba(59,130,246,.3);position:relative;z-index:1;}
    .rel-info{padding:10px 12px;}
    .rel-name{font-size:.82rem;font-weight:600;color:var(--text);margin-bottom:4px;}
    .rel-price{font-family:'Syne',sans-serif;font-size:.95rem;font-weight:800;color:var(--blue);}
  </style>
</head>
<body>

<?php if ($loggedIn): include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div>
      <div class="breadcrumb">
        <a href="/tienda.php">Tienda</a><span class="sep">/</span>
        <a href="/tienda.php?categoria=<?= urlencode($producto['categoria']) ?>"><?= htmlspecialchars($producto['categoria']) ?></a><span class="sep">/</span>
        <span><?= htmlspecialchars(substr($producto['nombre'],0,40)) ?></span>
      </div>
      <div class="topbar-title"><?= htmlspecialchars($producto['nombre']) ?></div>
    </div>
    <div style="display:flex;gap:8px;">
      <a href="/carrito.php" class="btn btn-ghost btn-sm" style="position:relative;">
        <i class="fa fa-cart-shopping"></i> Carrito
        <?php if($tc>0): ?><span style="position:absolute;top:-7px;right:-7px;background:var(--blue);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?=$tc?></span><?php endif; ?>
      </a>
      <a href="/tienda.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Volver</a>
    </div>
  </div>
<?php else: ?>
<nav style="background:rgba(6,13,26,.95);backdrop-filter:blur(12px);padding:0 32px;height:62px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100;">
  <a href="/index.php" style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:1.2rem;text-decoration:none;display:flex;align-items:center;gap:8px;"><span class="brand-dot"></span>Mastertech</a>
  <div style="display:flex;gap:8px;">
    <a href="/tienda.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Tienda</a>
    <a href="/carrito.php" class="btn btn-ghost btn-sm" style="position:relative;">
      <i class="fa fa-cart-shopping"></i>
      <?php if($tc>0): ?><span style="position:absolute;top:-7px;right:-7px;background:var(--blue);color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:flex;align-items:center;justify-content:center;"><?=$tc?></span><?php endif; ?>
    </a>
    <a href="/login.php" class="btn btn-primary btn-sm">Acceder</a>
  </div>
</nav>
<div style="min-height:100vh;display:flex;flex-direction:column;">
<?php endif; ?>

<div class="content" style="<?= $loggedIn ? '' : 'padding:24px 32px;max-width:1100px;margin:0 auto;' ?>">

  <div class="prod-layout">

    <!-- IMAGEN -->
    <div class="card">
      <div class="prod-img-wrap">
        <?php if (!empty($producto['imagen'])): ?>
          <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" onerror="this.style.display='none';document.getElementById('fallIcon').style.display='block';">
          <i class="fa <?= $icons[$producto['categoria']] ?? 'fa-box' ?> prod-img-icon" id="fallIcon" style="display:none;"></i>
        <?php else: ?>
          <i class="fa <?= $icons[$producto['categoria']] ?? 'fa-box' ?> prod-img-icon"></i>
        <?php endif; ?>
        <?php if ($producto['destacado']): ?>
          <span class="featured-tag" style="top:16px;right:16px;font-size:.75rem;padding:5px 12px;"><i class="fa fa-star"></i> Destacado</span>
        <?php endif; ?>
      </div>
      <div class="prod-thumbs">
        <?php for($i=1;$i<=3;$i++): ?>
        <div class="prod-thumb-mini">
          <?php if (!empty($producto['imagen'])): ?><img src="<?= htmlspecialchars($producto['imagen']) ?>" alt=""><?php else: ?><i class="fa <?= $icons[$producto['categoria']] ?? 'fa-box' ?>" style="font-size:.9rem;color:var(--text-3);"></i><?php endif; ?>
        </div>
        <?php endfor; ?>
        <span style="margin-left:auto;font-size:.75rem;color:var(--text-3);display:flex;align-items:center;">MTEC-<?= str_pad($id,6,'0',STR_PAD_LEFT) ?></span>
      </div>
    </div>

    <!-- DATOS -->
    <div style="display:flex;flex-direction:column;gap:18px;">

      <div>
        <div style="margin-bottom:10px;">
          <span class="badge badge-media"><?= htmlspecialchars($producto['categoria']) ?></span>
          <?php if ($producto['destacado']): ?><span class="badge badge-alta" style="margin-left:6px;"><i class="fa fa-star" style="margin-right:3px;"></i>Destacado</span><?php endif; ?>
        </div>
        <h1 style="font-size:1.7rem;letter-spacing:-.4px;line-height:1.2;margin-bottom:10px;"><?= htmlspecialchars($producto['nombre']) ?></h1>
        <p style="color:var(--text-2);font-size:.9rem;line-height:1.65;"><?= nl2br(htmlspecialchars($producto['descripcion'])) ?></p>
      </div>

      <div class="price-box">
        <div class="big-price"><?= number_format($producto['precio'],2) ?>€</div>
        <div style="font-size:.78rem;color:var(--text-3);margin-bottom:14px;">Precio con IVA (21%) incluido</div>
        <?php if ($producto['stock'] > 10): ?>
          <div style="display:flex;align-items:center;gap:8px;color:var(--green);font-size:.9rem;font-weight:600;"><i class="fa fa-circle-check"></i> En stock — <?=$producto['stock']?> unidades</div>
        <?php elseif ($producto['stock'] > 0): ?>
          <div style="display:flex;align-items:center;gap:8px;color:var(--orange);font-size:.9rem;font-weight:600;"><i class="fa fa-triangle-exclamation"></i> Últimas <?=$producto['stock']?> unidades</div>
        <?php else: ?>
          <div style="display:flex;align-items:center;gap:8px;color:var(--red);font-size:.9rem;font-weight:600;"><i class="fa fa-circle-xmark"></i> Agotado</div>
        <?php endif; ?>
      </div>

      <?php if ($producto['stock'] > 0): ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
        <div class="qty-ctrl">
          <button type="button" class="qbtn" onclick="var i=document.getElementById('qi');i.value=Math.max(1,+i.value-1)"><i class="fa fa-minus" style="font-size:.7rem;"></i></button>
          <input type="number" id="qi" class="qinp" value="1" min="1" max="<?=$producto['stock']?>">
          <button type="button" class="qbtn" onclick="var i=document.getElementById('qi');var m=+i.max||99;i.value=Math.min(m,+i.value+1)"><i class="fa fa-plus" style="font-size:.7rem;"></i></button>
        </div>
        <span style="font-size:.78rem;color:var(--text-3);">Máx. <?=$producto['stock']?> ud.</span>
      </div>
      <button id="btn-buy" class="btn-buy"
              onclick="addToCartQty('<?=$id?>','<?=addslashes($producto['nombre'])?>',<?=$producto['precio']?>,'<?=addslashes($producto['categoria'])?>','<?=addslashes($producto['imagen']??'')?>',+document.getElementById('qi').value)">
        <i class="fa fa-cart-shopping"></i> Añadir al carrito
      </button>
      <a href="/carrito.php" class="btn btn-ghost" style="width:100%;justify-content:center;display:flex;"><i class="fa fa-bag-shopping"></i> Ir al carrito<?=$en_carrito?" ({$qty_carrito} ud.)":''?></a>
      <?php else: ?>
      <button class="btn-buy" disabled style="opacity:.4;cursor:not-allowed;"><i class="fa fa-ban"></i> No disponible</button>
      <?php endif; ?>

      <a href="/tienda.php?categoria=<?=urlencode($producto['categoria'])?>" class="btn btn-ghost" style="width:100%;justify-content:center;display:flex;">
        <i class="fa fa-layer-group"></i> Ver más en <?=htmlspecialchars($producto['categoria'])?>
      </a>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
        <?php foreach([['fa-shield-halved','Pago seguro','SSL 256-bit'],['fa-rotate-left','Devolución','14 días'],['fa-truck','Envío rápido','24-48h Península'],['fa-headset','Soporte','Técnicos expertos']] as [$i,$t,$s]): ?>
        <div style="background:var(--glass);border:1px solid var(--border);border-radius:var(--radius-xs);padding:10px 12px;display:flex;align-items:flex-start;gap:9px;">
          <i class="fa <?=$i?>" style="color:var(--cyan);font-size:.85rem;margin-top:2px;"></i>
          <div><div style="font-size:.78rem;font-weight:600;color:var(--text);"><?=$t?></div><div style="font-size:.7rem;color:var(--text-3);"><?=$s?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- ESPECIFICACIONES -->
  <div class="card" style="margin-top:24px;">
    <div class="card-header"><h3>Especificaciones técnicas</h3></div>
    <div class="table-wrapper">
      <table>
        <tbody>
          <tr><td class="spec-key">Referencia</td><td class="spec-val">MTEC-<?=str_pad($id,6,'0',STR_PAD_LEFT)?></td></tr>
          <tr><td class="spec-key">Categoría</td><td class="spec-val"><span style="display:inline-flex;align-items:center;gap:6px;"><i class="fa <?=$icons[$producto['categoria']]??'fa-box'?>" style="color:var(--blue);"></i><?=htmlspecialchars($producto['categoria'])?></span></td></tr>
          <tr><td class="spec-key">Precio (IVA incl.)</td><td class="spec-val"><span style="font-family:'Syne',sans-serif;font-weight:800;color:var(--blue);font-size:1.1rem;"><?=number_format($producto['precio'],2)?>€</span></td></tr>
          <tr><td class="spec-key">Base imponible</td><td class="spec-val"><?=number_format($producto['precio']/1.21,2)?>€</td></tr>
          <tr><td class="spec-key">IVA (21%)</td><td class="spec-val"><?=number_format($producto['precio']-($producto['precio']/1.21),2)?>€</td></tr>
          <tr><td class="spec-key">Stock</td><td class="spec-val">
            <?php if($producto['stock']>10): ?><span class="badge badge-enproceso"><?=$producto['stock']?> unidades</span>
            <?php elseif($producto['stock']>0): ?><span class="badge badge-pendiente"><?=$producto['stock']?> unidades (pocas)</span>
            <?php else: ?><span class="badge badge-cerrado">Agotado</span><?php endif; ?>
          </td></tr>
          <tr><td class="spec-key">Destacado</td><td class="spec-val"><?=$producto['destacado']?'<span class="badge badge-alta"><i class="fa fa-star" style="margin-right:3px;"></i>Sí</span>':'<span class="badge badge-cerrado">No</span>'?></td></tr>
          <tr><td class="spec-key">Descripción</td><td class="spec-val" style="line-height:1.65;color:var(--text-2);"><?=nl2br(htmlspecialchars($producto['descripcion']))?></td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <?php if (!empty($relacionados)): ?>
  <div style="margin-top:28px;">
    <h3 style="font-size:1rem;margin-bottom:16px;">Más productos en <?=htmlspecialchars($producto['categoria'])?></h3>
    <div class="rel-grid">
      <?php foreach($relacionados as $r): ?>
      <a href="/producto.php?id=<?=$r['id']?>" class="rel-card">
        <div class="rel-thumb">
          <?php if(!empty($r['imagen'])): ?><img src="<?=htmlspecialchars($r['imagen'])?>" alt="" loading="lazy" onerror="this.style.display='none';"><?php endif; ?>
          <i class="fa <?=$icons[$r['categoria']]??'fa-box'?>"></i>
        </div>
        <div class="rel-info">
          <div class="rel-name"><?=htmlspecialchars($r['nombre'])?></div>
          <div class="rel-price"><?=number_format($r['precio'],2)?>€</div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<?php if($loggedIn): include 'includes/footer.php'; ?>
</div>
<?php else: ?>
<footer style="padding:20px 32px;border-top:1px solid var(--border);background:var(--navy);margin-top:40px;">
  <p style="font-size:.78rem;color:var(--text-3);">&copy; <?=date('Y')?> Mastertech</p>
</footer>
</div>
<?php endif; ?>
<script>
const CART_KEY='mastertech_cart';
function getCart(){try{return JSON.parse(localStorage.getItem(CART_KEY)||'{}')}catch(e){return{}}}
function saveCart(c){localStorage.setItem(CART_KEY,JSON.stringify(c))}

function addToCartQty(id,name,price,cat,img,qty){
  qty=Math.max(1,qty||1);
  const c=getCart(); const sid=String(id);
  if(c[sid]){c[sid].qty+=qty}else{c[sid]={id:sid,name:name,price:price,cat:cat,img:img,qty:qty}}
  saveCart(c);
  const btn=document.getElementById('btn-buy');
  btn.classList.add('in-cart');
  btn.innerHTML='<i class="fa fa-check"></i> En carrito ('+c[sid].qty+' ud.) — añadir más';
  updateBadge();
}

function updateBadge(){
  const total=Object.values(getCart()).reduce((s,i)=>s+i.qty,0);
  document.querySelectorAll('.cart-badge').forEach(b=>{b.textContent=total;b.style.display=total>0?'flex':'none'});
}

(function(){
  updateBadge();
  const c=getCart(); const sid='<?=$id?>';
  if(c[sid]){
    const btn=document.getElementById('btn-buy');
    if(btn){btn.classList.add('in-cart');btn.innerHTML='<i class="fa fa-check"></i> En carrito ('+c[sid].qty+' ud.) — añadir más';}
  }
})();
</script>
</body>
</html>