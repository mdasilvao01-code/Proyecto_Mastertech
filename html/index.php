<?php
require_once 'db_config.php';
if(isset($_SESSION['usuario_id'])){ header('Location:/dashboard.php'); exit(); }
$servidor = gethostname();
$ip = $_SERVER['SERVER_ADDR'] ?? '—';
try{
    $pdo=getDB();
    $totalClientes=$pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
    $totalInc=$pdo->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
    $pendientes=$pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='Abierto'")->fetchColumn();
    $resueltas=$pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='Resuelto'")->fetchColumn();
}catch(Exception $e){$totalClientes=$totalInc=$pendientes=$resueltas=0;}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mastertech — Sistema de Gestión IT</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
  <style>
    body { background: var(--bg); overflow-x: hidden; }

    /* ── NAVBAR ── */
    .pub-nav {
      position: sticky; top: 0; z-index: 100;
      height: 62px;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 48px;
      background: rgba(6,13,26,.75);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-bottom: 1px solid var(--border);
    }
    .pub-nav-logo {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.25rem;
      color: #fff;
      letter-spacing: -.5px;
      display: flex; align-items: center; gap: 10px;
      text-decoration: none;
    }
    .pub-nav-links { display: flex; align-items: center; gap: 8px; }
    .pub-nav-links a.link {
      color: var(--text-2);
      text-decoration: none;
      font-size: .875rem;
      font-weight: 500;
      padding: 6px 14px;
      border-radius: 6px;
      transition: all .15s;
    }
    .pub-nav-links a.link:hover { color: var(--text); background: var(--glass-2); }

    /* ── HERO ── */
    .hero {
      min-height: calc(100vh - 62px);
      display: flex; align-items: center;
      padding: 80px 48px;
      position: relative;
      overflow: hidden;
      background:
        radial-gradient(ellipse 80% 60% at 70% 40%, rgba(59,130,246,.1) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 20% 80%, rgba(6,182,212,.07) 0%, transparent 55%),
        var(--bg);
    }

    /* Grid */
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background-image:
        linear-gradient(rgba(59,130,246,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(59,130,246,.06) 1px, transparent 1px);
      background-size: 44px 44px;
      mask-image: radial-gradient(ellipse 70% 80% at 60% 40%, black 0%, transparent 75%);
      pointer-events: none;
    }

    /* Orbs */
    .orb {
      position: absolute;
      border-radius: 50%;
      pointer-events: none;
      animation: float 8s ease-in-out infinite;
    }
    .orb-1 {
      width: 600px; height: 600px;
      top: -200px; right: -100px;
      background: radial-gradient(circle, rgba(59,130,246,.12) 0%, transparent 65%);
    }
    .orb-2 {
      width: 350px; height: 350px;
      bottom: -100px; left: -50px;
      background: radial-gradient(circle, rgba(6,182,212,.08) 0%, transparent 65%);
      animation-delay: -4s;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50%       { transform: translateY(-24px) scale(1.03); }
    }

    .hero-content { position: relative; z-index: 2; max-width: 700px; }

    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(59,130,246,.1);
      border: 1px solid rgba(59,130,246,.25);
      border-radius: 100px;
      padding: 6px 14px;
      margin-bottom: 28px;
    }
    .hero-eyebrow-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: var(--cyan);
      box-shadow: 0 0 8px var(--cyan);
      animation: pulse 2s infinite;
    }
    .hero-eyebrow span {
      font-size: .72rem;
      color: #7dd3fc;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1.2px;
    }

    .hero h1 {
      font-size: clamp(2.4rem, 5vw, 4rem);
      font-weight: 800;
      line-height: 1.07;
      letter-spacing: -.8px;
      margin-bottom: 22px;
      color: #fff;
    }

    .hero h1 .line2 {
      background: linear-gradient(135deg, #60a5fa 20%, #22d3ee 80%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-desc {
      font-size: 1.1rem;
      color: var(--text-2);
      max-width: 540px;
      line-height: 1.7;
      margin-bottom: 40px;
    }

    .hero-btns { display: flex; gap: 12px; flex-wrap: wrap; }

    /* ── STATS STRIP ── */
    .stats-strip {
      padding: 36px 48px;
      background: rgba(13,25,43,.6);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
      backdrop-filter: blur(8px);
    }
    .stats-strip-inner {
      max-width: 900px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(4,1fr);
      gap: 0;
    }
    .stat-strip-item {
      text-align: center;
      padding: 0 20px;
      position: relative;
    }
    .stat-strip-item + .stat-strip-item::before {
      content: '';
      position: absolute;
      left: 0; top: 10%; height: 80%;
      width: 1px;
      background: var(--border);
    }
    .stat-strip-icon {
      font-size: 1.2rem;
      margin-bottom: 10px;
      display: block;
    }
    .stat-strip-val {
      font-family: 'Syne', sans-serif;
      font-size: 2rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.5px;
      line-height: 1;
      margin-bottom: 4px;
    }
    .stat-strip-label {
      font-size: .72rem;
      text-transform: uppercase;
      letter-spacing: 1.2px;
      color: var(--text-3);
      font-weight: 600;
    }

    /* ── FEATURES ── */
    .features-section {
      padding: 100px 48px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .section-label {
      font-size: .72rem;
      text-transform: uppercase;
      letter-spacing: 2px;
      color: #60a5fa;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .section-title {
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 800;
      color: #fff;
      letter-spacing: -.5px;
      line-height: 1.15;
      margin-bottom: 14px;
    }
    .section-sub {
      color: var(--text-2);
      max-width: 500px;
      font-size: 1rem;
      line-height: 1.6;
    }
    .features-grid {
      display: grid;
      grid-template-columns: repeat(3,1fr);
      gap: 18px;
      margin-top: 52px;
    }
    .feature-card {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 28px;
      transition: all .2s;
      backdrop-filter: blur(8px);
      position: relative;
      overflow: hidden;
    }
    .feature-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 1px;
      opacity: 0;
      transition: opacity .2s;
    }
    .feature-card:hover {
      border-color: var(--border-2);
      transform: translateY(-4px);
      box-shadow: var(--shadow-lg);
    }
    .feature-card:hover::before { opacity: 1; }

    .feature-icon-wrap {
      width: 46px; height: 46px;
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 18px;
      font-size: .95rem;
    }
    .feature-card h3 { font-size: .95rem; margin-bottom: 8px; }
    .feature-card p  { font-size: .875rem; color: var(--text-2); line-height: 1.65; }

    /* ── HOW IT WORKS ── */
    .how-section {
      padding: 80px 48px;
      background: rgba(13,25,43,.5);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .how-inner { max-width: 960px; margin: 0 auto; }
    .how-steps {
      display: grid;
      grid-template-columns: repeat(3,1fr);
      gap: 32px;
      margin-top: 52px;
    }
    .how-step { text-align: center; }
    .how-step-num {
      width: 52px; height: 52px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(59,130,246,.2), rgba(6,182,212,.15));
      border: 1px solid rgba(59,130,246,.3);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1.1rem;
      color: #60a5fa;
      margin: 0 auto 20px;
      box-shadow: 0 0 20px rgba(59,130,246,.1);
    }
    .how-step h3 { font-size: 1rem; margin-bottom: 8px; }
    .how-step p  { font-size: .875rem; color: var(--text-2); line-height: 1.65; }

    /* ── CTA ── */
    .cta-section {
      padding: 100px 48px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cta-section::before {
      content: '';
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%,-50%);
      width: 700px; height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(59,130,246,.1) 0%, transparent 65%);
      pointer-events: none;
    }
    .cta-section h2 {
      font-size: clamp(1.8rem, 3.5vw, 2.8rem);
      font-weight: 800;
      letter-spacing: -.5px;
      margin-bottom: 16px;
      color: #fff;
    }
    .cta-section p { color: var(--text-2); font-size: 1rem; margin-bottom: 36px; }

    /* ── FOOTER ── */
    .pub-footer {
      background: var(--navy);
      border-top: 1px solid var(--border);
      padding: 32px 48px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }
    .pub-footer-logo {
      font-family: 'Syne', sans-serif;
      font-weight: 800;
      font-size: 1rem;
      color: #fff;
    }
    .pub-footer p { font-size: .78rem; color: var(--text-3); }

    /* server badge */
    .server-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(16,185,129,.1);
      border: 1px solid rgba(16,185,129,.25);
      border-radius: 100px;
      padding: 6px 14px;
      font-size: .75rem;
      color: #34d399;
      font-weight: 600;
    }
    .server-badge-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #34d399;
      box-shadow: 0 0 8px #34d399;
      animation: pulse 2s infinite;
    }

    @media(max-width:768px){
      .pub-nav { padding: 0 20px; }
      .hero { padding: 60px 24px; }
      .stats-strip { padding: 28px 24px; }
      .stats-strip-inner { grid-template-columns: repeat(2,1fr); gap: 20px; }
      .stat-strip-item + .stat-strip-item::before { display: none; }
      .features-section, .how-section, .cta-section { padding: 60px 24px; }
      .features-grid, .how-steps { grid-template-columns: 1fr; }
      .pub-footer { padding: 24px; flex-direction: column; text-align: center; }
    }
  </style>
</head>
<body>

<!-- NAV -->
<nav class="pub-nav">
  <a href="/index.php" class="pub-nav-logo">
    <span class="brand-dot"></span>
    Mastertech
  </a>
  <div class="pub-nav-links">
    <a href="/tienda.php" class="link"><i class="fa fa-store"></i> Tienda</a>
    <a href="/login.php" class="btn btn-ghost btn-sm">Acceder</a>
    <a href="/registro.php" class="btn btn-primary btn-sm">Registrarse</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="hero-eyebrow-dot"></span>
      <span>Sistema operativo · Alta disponibilidad</span>
    </div>

    <h1>
      Infraestructura IT<br>
      <span class="line2">gestionada con precisión.</span>
    </h1>

    <p class="hero-desc">
      Panel centralizado para la gestión de incidencias, clientes y monitorización de infraestructura. 
      Arquitectura de alta disponibilidad con balanceo de carga automático.
    </p>

    <div class="hero-btns">
      <a href="/registro.php" class="btn btn-primary btn-lg">
        Empezar ahora <i class="fa fa-arrow-right"></i>
      </a>
      <a href="/login.php" class="btn btn-ghost btn-lg">
        Iniciar sesión
      </a>
      <a href="/tienda.php" class="btn btn-ghost btn-lg">
        <i class="fa fa-store"></i> Ver tienda
      </a>
    </div>

    <div style="margin-top: 36px;">
      <div class="server-badge">
        <span class="server-badge-dot"></span>
        Servidor activo: <?=htmlspecialchars($servidor)?> &nbsp;·&nbsp; <?=htmlspecialchars($ip)?>
      </div>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<div class="stats-strip">
  <div class="stats-strip-inner">
    <?php
    $strip=[
      ['fa-users','#60a5fa',$totalClientes,'Clientes'],
      ['fa-ticket','#a78bfa',$totalInc,'Incidencias'],
      ['fa-circle-dot','#fbbf24',$pendientes,'Abiertas'],
      ['fa-check-circle','#34d399',$resueltas,'Resueltas'],
    ];
    foreach($strip as [$ic,$color,$val,$label]):?>
    <div class="stat-strip-item">
      <i class="fa <?=$ic?> stat-strip-icon" style="color:<?=$color?>;filter:drop-shadow(0 0 6px <?=$color?>44);"></i>
      <div class="stat-strip-val"><?=$val?></div>
      <div class="stat-strip-label"><?=$label?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- FEATURES -->
<section class="features-section">
  <div>
    <div class="section-label">Capacidades</div>
    <h2 class="section-title">Todo lo que necesita<br>tu equipo de soporte.</h2>
    <p class="section-sub">Una plataforma completa con todos los módulos integrados para gestionar tu infraestructura IT eficientemente.</p>
  </div>

  <div class="features-grid">
    <?php
    $features=[
      ['fa-ticket','var(--blue)','rgba(59,130,246,.12)','linear-gradient(135deg, var(--blue), var(--cyan))','Gestión de incidencias','Crea, asigna y resuelve tickets con historial completo, comentarios en tiempo real y exportación PDF/TXT.'],
      ['fa-users','var(--purple)','rgba(139,92,246,.12)','linear-gradient(135deg, var(--purple), #c084fc)','Base de clientes','Registro centralizado con histórico de incidencias, datos de contacto y búsqueda avanzada por empresa.'],
      ['fa-store','var(--green)','rgba(16,185,129,.12)','linear-gradient(135deg, var(--green), #34d399)','Tienda online','Catálogo de productos tecnológicos con gestión de stock, categorías y fichas de producto detalladas.'],
      ['fa-chart-bar','var(--orange)','rgba(245,158,11,.12)','linear-gradient(135deg, var(--orange), #fbbf24)','Dashboard analítico','Métricas en tiempo real: incidencias por estado, tiempo de resolución, carga de trabajo por técnico.'],
      ['fa-file-lines','var(--cyan)','rgba(6,182,212,.12)','linear-gradient(135deg, var(--cyan), #67e8f9)','Informes exportables','Genera documentos PDF y TXT profesionales de cualquier incidencia con un solo clic.'],
      ['fa-shield-halved','var(--red)','rgba(239,68,68,.12)','linear-gradient(135deg, var(--red), #f87171)','Alta disponibilidad','Infraestructura redundante con balanceo de carga, múltiples nodos web y failover automático.'],
    ];
    foreach($features as [$icon,$color,$bg,$grad,$title,$desc]):?>
    <div class="feature-card" style="--fc: <?=$color?>;">
      <div class="feature-icon-wrap" style="background:<?=$bg?>; box-shadow: 0 0 16px <?=$bg?>;">
        <i class="fa <?=$icon?>" style="color:<?=$color?>;"></i>
      </div>
      <h3><?=$title?></h3>
      <p><?=$desc?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="how-section">
  <div class="how-inner">
    <div style="text-align:center;">
      <div class="section-label">Proceso</div>
      <h2 class="section-title">¿Cómo funciona?</h2>
      <p class="section-sub" style="margin: 0 auto;">Tres pasos para empezar a gestionar tu infraestructura.</p>
    </div>
    <div class="how-steps">
      <div class="how-step">
        <div class="how-step-num">01</div>
        <h3>Crea tu cuenta</h3>
        <p>Regístrate en segundos. El sistema te asigna automáticamente el rol de cliente con acceso al portal de soporte.</p>
      </div>
      <div class="how-step">
        <div class="how-step-num">02</div>
        <h3>Abre una incidencia</h3>
        <p>Describe el problema, elige la prioridad y categoría. El sistema notifica al equipo técnico inmediatamente.</p>
      </div>
      <div class="how-step">
        <div class="how-step-num">03</div>
        <h3>Seguimiento en tiempo real</h3>
        <p>Consulta el estado, añade comentarios y descarga el informe completo cuando se resuelva tu incidencia.</p>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <div style="position:relative;z-index:2;">
    <div class="glow-pill" style="margin: 0 auto 24px; width: fit-content;">
      <span class="glow-dot"></span>
      Acceso gratuito
    </div>
    <h2>¿Listo para empezar?</h2>
    <p>Crea tu cuenta y accede al panel de gestión en menos de un minuto.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
      <a href="/registro.php" class="btn btn-primary btn-lg">
        Crear cuenta gratis <i class="fa fa-arrow-right"></i>
      </a>
      <a href="/tienda.php" class="btn btn-ghost btn-lg">
        <i class="fa fa-store"></i> Explorar tienda
      </a>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer class="pub-footer">
  <div class="pub-footer-logo">
    <span style="color:var(--cyan)">M</span>astertech
  </div>
  <p>&copy; <?=date('Y')?> Mastertech &mdash; IES Albarregas &mdash; Proyecto Intermodular 2º ASIR &mdash; Mario Da Silva Ortega</p>
  <div style="display:flex;gap:16px;">
    <a href="/tienda.php" style="font-size:.8rem;color:var(--text-3);text-decoration:none;">Tienda</a>
    <a href="/login.php"  style="font-size:.8rem;color:var(--text-3);text-decoration:none;">Acceso</a>
  </div>
</footer>

</body>
</html>
