<?php
require_once 'db_config.php';
$loggedIn = isset($_SESSION['usuario_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    $_SESSION['pedido_ok'] = true;
    header('Location: /tienda.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Carrito — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
  <style>
    .cart-page-grid { display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start; }
    @media(max-width:900px){ .cart-page-grid { grid-template-columns:1fr; } }
    .cart-line { display:flex; gap:16px; padding:16px; border-bottom:1px solid var(--border); align-items:center; }
    .cart-line:last-child { border-bottom:none; }
    .cl-img { width:90px; height:90px; border-radius:var(--radius-sm); object-fit:cover; flex-shrink:0; background:var(--navy-3); }
    .cl-img-fb { width:90px; height:90px; border-radius:var(--radius-sm); flex-shrink:0; background:var(--navy-3); display:flex; align-items:center; justify-content:center; }
    .cl-img-fb i { font-size:2.2rem; color:rgba(255,255,255,.15); }
    .cl-info { flex:1; min-width:0; }
    .cl-name { font-size:.95rem; font-weight:600; color:var(--text); margin-bottom:3px; }
    .cl-cat  { font-size:.78rem; color:var(--text-3); margin-bottom:8px; }
    .cl-price-unit { font-size:.8rem; color:var(--text-2); }
    .cl-right { text-align:right; flex-shrink:0; display:flex; flex-direction:column; align-items:flex-end; gap:10px; }
    .cl-total { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:800; color:var(--blue); }
    .cl-qty { display:flex; align-items:center; gap:8px; }
    .qb { width:28px; height:28px; border-radius:var(--radius-xs); border:1px solid var(--border); background:var(--navy-3); color:var(--text); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .15s; }
    .qb:hover { background:var(--navy-4); }
    .qn { font-weight:700; font-size:.9rem; min-width:20px; text-align:center; }
    .cl-remove { background:none; border:none; cursor:pointer; color:var(--text-3); padding:5px; border-radius:var(--radius-xs); transition:all .15s; display:flex; align-items:center; gap:5px; font-size:.8rem; }
    .cl-remove:hover { color:#f87171; background:rgba(239,68,68,.08); }
    .summary-card { background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:20px; position:sticky; top:20px; }
    .sum-row { display:flex; justify-content:space-between; padding:8px 0; font-size:.875rem; border-bottom:1px solid var(--border); }
    .sum-row:last-of-type { border-bottom:none; }
    .sum-row .label { color:var(--text-2); }
    .sum-total { display:flex; justify-content:space-between; padding:14px 0 16px; border-top:2px solid var(--border); margin-top:8px; }
    .sum-total .label { font-weight:700; font-size:.95rem; }
    .sum-total .value { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text); }
    .trust-badges { display:flex; flex-direction:column; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); }
    .trust-item { display:flex; align-items:center; gap:8px; font-size:.78rem; color:var(--text-3); }
    .trust-item i { color:var(--blue); width:14px; }
    .empty-cart-page { text-align:center; padding:80px 20px; }
    .empty-cart-page i.big { font-size:4rem; color:var(--text-3); opacity:.3; display:block; margin-bottom:20px; }
    .empty-cart-page h2 { margin-bottom:8px; }
    .empty-cart-page p { color:var(--text-2); margin-bottom:24px; }
    .step-bar { display:flex; align-items:center; margin-bottom:28px; }
    .step { display:flex; align-items:center; gap:8px; font-size:.82rem; font-weight:600; color:var(--text-3); }
    .step.active { color:var(--text); }
    .step.done { color:var(--green); }
    .step-dot { width:24px; height:24px; border-radius:50%; border:2px solid currentColor; display:flex; align-items:center; justify-content:center; font-size:.7rem; flex-shrink:0; }
    .step.active .step-dot { background:var(--blue); border-color:var(--blue); color:#fff; }
    .step.done .step-dot { background:var(--green); border-color:var(--green); color:#fff; }
    .step-line { flex:1; height:2px; background:var(--border); margin:0 8px; }
  </style>
</head>
<body>

<?php if($loggedIn): include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div>
      <div class="breadcrumb"><a href="/tienda.php">Tienda</a><span class="sep">/</span><span>Carrito</span></div>
      <div class="topbar-title">Carrito de compra</div>
    </div>
    <a href="/tienda.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Seguir comprando</a>
  </div>
<?php else: ?>
<nav class="pub-nav">
  <a href="/index.php" class="pub-nav-logo"><span class="brand-dot"></span>Mastertech</a>
  <div style="display:flex;gap:8px;">
    <a href="/tienda.php" class="btn btn-ghost btn-sm"><i class="fa fa-arrow-left"></i> Tienda</a>
    <a href="/login.php" class="btn btn-primary btn-sm">Acceder</a>
  </div>
</nav>
<div style="min-height:100vh;display:flex;flex-direction:column;">
<?php endif; ?>

<div class="content" style="<?=$loggedIn?'':'padding:32px;max-width:1200px;margin:0 auto;'?>">

  <!-- Steps -->
  <div class="step-bar">
    <div class="step done"><div class="step-dot"><i class="fa fa-check"></i></div><span>Productos</span></div>
    <div class="step-line"></div>
    <div class="step active"><div class="step-dot">2</div><span>Carrito</span></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-dot">3</div><span>Confirmar</span></div>
    <div class="step-line"></div>
    <div class="step"><div class="step-dot">4</div><span>Listo</span></div>
  </div>

  <!-- EMPTY STATE -->
  <div id="empty-state" class="empty-cart-page" style="display:none;">
    <i class="fa fa-cart-shopping big"></i>
    <h2>Tu carrito está vacío</h2>
    <p>Explora el catálogo y añade los productos que necesitas.</p>
    <a href="/tienda.php" class="btn btn-primary"><i class="fa fa-store"></i> Ver tienda</a>
  </div>

  <!-- CART CONTENT -->
  <div id="cart-content" class="cart-page-grid" style="display:none;">

    <!-- Lines -->
    <div>
      <div class="card">
        <div class="card-header">
          <h3><i class="fa fa-cart-shopping" style="color:var(--blue);margin-right:6px;"></i>Artículos
            <span id="item-count" style="color:var(--text-3);font-weight:400;font-size:.85rem;"></span>
          </h3>
          <button onclick="clearCart()" class="btn btn-ghost btn-sm" style="color:var(--text-3);">
            <i class="fa fa-trash"></i> Vaciar
          </button>
        </div>
        <div id="cart-lines"></div>
      </div>
      <div style="margin-top:14px;padding:14px 16px;background:var(--glass);border:1px solid rgba(16,185,129,.15);border-radius:var(--radius-sm);display:flex;align-items:center;gap:12px;">
        <i class="fa fa-truck" style="color:var(--green);font-size:1.1rem;flex-shrink:0;"></i>
        <div>
          <div style="font-size:.875rem;font-weight:600;color:var(--green);">Envío gratuito incluido</div>
          <div style="font-size:.8rem;color:var(--text-2);">Entrega en 24-48h laborables para pedidos antes de las 14:00h</div>
        </div>
      </div>
    </div>

    <!-- Summary & Checkout -->
    <div>
      <div class="summary-card">
        <h3 style="font-size:.85rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-2);margin-bottom:14px;">Resumen del pedido</h3>
        <div id="sum-lines"></div>
        <div class="sum-row"><span class="label">Subtotal (sin IVA)</span><span id="sum-subtotal">—</span></div>
        <div class="sum-row"><span class="label">IVA 21%</span><span id="sum-iva">—</span></div>
        <div class="sum-row"><span class="label">Envío</span><span style="color:var(--green);">Gratis</span></div>
        <div class="sum-total">
          <span class="label">Total</span>
          <span class="value" id="sum-total">—</span>
        </div>

        <!-- Checkout form -->
        <form id="checkout-form" onsubmit="submitOrder(event)" style="display:flex;flex-direction:column;gap:12px;">
          <?php if(!$loggedIn): ?>
          <div>
            <label class="form-label">Nombre completo *</label>
            <input type="text" name="nombre" class="form-control" required placeholder="Tu nombre">
          </div>
          <div>
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required placeholder="tu@empresa.com">
          </div>
          <div>
            <label class="form-label">Teléfono</label>
            <input type="tel" name="telefono" class="form-control" placeholder="666 123 456">
          </div>
          <?php else: ?>
          <div style="padding:10px 12px;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.18);border-radius:var(--radius-sm);">
            <div style="font-size:.75rem;color:var(--text-2);margin-bottom:2px;">Pedido como</div>
            <div style="font-weight:600;"><?=htmlspecialchars($_SESSION['nombre']??'')?></div>
            <div style="font-size:.82rem;color:var(--text-2);"><?=htmlspecialchars($_SESSION['email']??'')?></div>
          </div>
          <?php endif; ?>
          <div>
            <label class="form-label">Empresa / Referencia</label>
            <input type="text" name="empresa" class="form-control" placeholder="Nombre empresa (opcional)">
          </div>
          <div>
            <label class="form-label">Notas del pedido</label>
            <textarea name="notas" class="form-control" rows="2" placeholder="Instrucciones de entrega..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;font-size:.95rem;">
            <i class="fa fa-lock"></i> Confirmar pedido
          </button>
          <p style="font-size:.7rem;color:var(--text-3);text-align:center;margin-top:-4px;">
            Al confirmar aceptas los <a href="#" style="color:var(--blue);">términos y condiciones</a>
          </p>
        </form>

        <div class="trust-badges">
          <div class="trust-item"><i class="fa fa-shield-halved"></i> Datos protegidos (RGPD)</div>
          <div class="trust-item"><i class="fa fa-award"></i> Garantía oficial 2 años</div>
          <div class="trust-item"><i class="fa fa-rotate-left"></i> Devolución gratuita en 30 días</div>
          <div class="trust-item"><i class="fa fa-headset"></i> Soporte técnico post-venta incluido</div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if($loggedIn): ?>
  <?php include 'includes/footer.php'; ?>
</div>
<?php else: ?>
<footer class="site-footer" style="margin-top:auto;">
  <p>&copy; <?=date('Y')?> Mastertech &mdash; IES Albarregas</p>
</footer>
</div>
<?php endif; ?>

<div id="success-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:600;align-items:center;justify-content:center;">
  <div style="background:var(--navy-2);border:1px solid rgba(16,185,129,.3);border-radius:var(--radius);padding:40px;max-width:440px;width:90%;text-align:center;">
    <div style="width:70px;height:70px;border-radius:50%;background:rgba(16,185,129,.12);border:2px solid var(--green);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
      <i class="fa fa-check" style="font-size:1.8rem;color:var(--green);"></i>
    </div>
    <h2 style="margin-bottom:8px;">¡Pedido confirmado!</h2>
    <p style="color:var(--text-2);font-size:.9rem;margin-bottom:24px;line-height:1.65;">
      Tu solicitud ha sido recibida correctamente. Nuestro equipo comercial se pondrá en contacto contigo en menos de 2 horas para coordinar el pago y la entrega.
    </p>
    <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
      <a href="/tienda.php" class="btn btn-primary"><i class="fa fa-store"></i> Seguir comprando</a>
      <?php if($loggedIn): ?>
      <a href="/dashboard.php" class="btn btn-ghost"><i class="fa fa-gauge"></i> Dashboard</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const CART_KEY = 'mastertech_cart';
const ICONS = {
  'Ordenadores':'fa-desktop','Portátiles':'fa-laptop','Componentes':'fa-microchip',
  'Periféricos':'fa-computer-mouse','Redes':'fa-network-wired','Servidores':'fa-server'
};

function getCart(){ try{ return JSON.parse(localStorage.getItem(CART_KEY)||'{}'); }catch(e){ return {}; } }
function saveCart(c){ localStorage.setItem(CART_KEY,JSON.stringify(c)); }
function fmt(n){ return n.toLocaleString('es-ES',{minimumFractionDigits:2,maximumFractionDigits:2})+'€'; }

function clearCart(){
  if(confirm('¿Vaciar el carrito?')){ saveCart({}); renderPage(); }
}
function updateQty(id,delta){
  const c=getCart();id=String(id);if(!c[id])return;
  c[id].qty+=delta;if(c[id].qty<=0)delete c[id];
  saveCart(c);renderPage();
}
function removeItem(id){ const c=getCart();delete c[String(id)];saveCart(c);renderPage(); }

function renderPage(){
  const c=getCart(); const items=Object.values(c);
  const empty=document.getElementById('empty-state');
  const content=document.getElementById('cart-content');
  if(items.length===0){ empty.style.display='block'; content.style.display='none'; return; }
  empty.style.display='none'; content.style.display='grid';

  document.getElementById('item-count').textContent='('+items.reduce((s,i)=>s+i.qty,0)+' artículos)';

  document.getElementById('cart-lines').innerHTML=items.map(item=>{
    const icon=ICONS[item.cat]||'fa-box';
    const imgHtml=item.img
      ?`<img class="cl-img" src="${item.img}" alt="${item.name}" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"><div class="cl-img-fb" style="display:none"><i class="fa ${icon}"></i></div>`
      :`<div class="cl-img-fb"><i class="fa ${icon}"></i></div>`;
    return`<div class="cart-line">${imgHtml}<div class="cl-info"><div class="cl-name">${item.name}</div><div class="cl-cat">${item.cat}</div><div class="cl-price-unit">${fmt(item.price)} / ud.</div></div><div class="cl-right"><div class="cl-total">${fmt(item.price*item.qty)}</div><div class="cl-qty"><button class="qb" onclick="updateQty('${item.id}',-1)">−</button><span class="qn">${item.qty}</span><button class="qb" onclick="updateQty('${item.id}',1)">+</button></div><button class="cl-remove" onclick="removeItem('${item.id}')"><i class="fa fa-trash-can"></i> Eliminar</button></div></div>`;
  }).join('');

  document.getElementById('sum-lines').innerHTML=items.map(i=>
    `<div class="sum-row" style="font-size:.78rem;"><span class="label" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:190px;">${i.name} ×${i.qty}</span><span>${fmt(i.price*i.qty)}</span></div>`
  ).join('');

  const total=items.reduce((s,i)=>s+i.price*i.qty,0);
  const base=total/1.21, iva=total-base;
  document.getElementById('sum-subtotal').textContent=fmt(base);
  document.getElementById('sum-iva').textContent=fmt(iva);
  document.getElementById('sum-total').textContent=fmt(total);
}

function submitOrder(e){
  e.preventDefault();
  document.getElementById('success-modal').style.display='flex';
  saveCart({});
}

renderPage();
</script>
</body>
</html>
