<?php
require_once 'db_config.php';
if(isset($_SESSION['usuario_id'])){ header('Location:/dashboard.php'); exit(); }
$servidor = gethostname();
$ip = $_SERVER['SERVER_ADDR'];
try{
    $pdo=getDB();
    $totalClientes=$pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
    $totalInc=$pdo->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
    $pendientes=$pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado='Abierto'")->fetchColumn();
}catch(Exception $e){$totalClientes=$totalInc=$pendientes=0;}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mastertech — Sistema de Gestión</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body style="background:var(--bg);">

<!-- NAV -->
<nav style="background:var(--navy);padding:0 40px;height:64px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;">
  <span style="font-family:'Syne',sans-serif;font-weight:800;color:#fff;font-size:1.25rem;letter-spacing:-.5px;">Mastertech</span>
  <div style="display:flex;align-items:center;gap:24px;">
    <a href="/tienda.php" style="color:#9ca3af;text-decoration:none;font-size:.9rem;font-weight:500;">Tienda</a>
    <a href="/login.php" class="btn btn-ghost btn-sm" style="color:#9ca3af;border-color:#374151;">Acceder</a>
    <a href="/registro.php" class="btn btn-primary btn-sm">Registrarse</a>
  </div>
</nav>

<!-- HERO -->
<section style="background:var(--navy);padding:96px 40px;position:relative;overflow:hidden;">
  <div style="position:absolute;top:-100px;right:-50px;width:500px;height:500px;border-radius:50%;background:radial-gradient(circle,rgba(59,130,246,.18) 0%,transparent 65%);pointer-events:none;"></div>
  <div style="max-width:720px;">
    <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);border-radius:100px;padding:6px 14px;margin-bottom:24px;">
      <span style="width:6px;height:6px;background:var(--blue);border-radius:50%;"></span>
      <span style="font-size:.78rem;color:#93c5fd;font-weight:600;text-transform:uppercase;letter-spacing:1.2px;">Alta disponibilidad activa</span>
    </div>
    <h1 style="font-size:3.2rem;color:#fff;line-height:1.1;margin-bottom:20px;">Infraestructura<br>gestionada con precisión.</h1>
    <p style="font-size:1.1rem;color:#6b7280;max-width:540px;line-height:1.7;margin-bottom:36px;">Sistema centralizado para la gestión de incidencias, clientes y monitorización. Diseñado sobre arquitectura de alta disponibilidad con balanceo de carga.</p>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
      <a href="/registro.php" class="btn btn-primary btn-lg">Crear cuenta <i class="fa fa-arrow-right"></i></a>
      <a href="/login.php" class="btn btn-ghost btn-lg" style="color:#9ca3af;border-color:#374151;">Iniciar sesión</a>
    </div>
  </div>
</section>

<!-- STATS -->
<section style="padding:40px;background:var(--card);border-bottom:1px solid var(--border);">
  <div style="max-width:960px;margin:0 auto;display:grid;grid-template-columns:repeat(4,1fr);gap:32px;">
    <?php
    $stats=[
      ['Clientes',$totalClientes,'fa-users','var(--blue)'],
      ['Incidencias',$totalInc,'fa-ticket','var(--purple)'],
      ['Abiertas',$pendientes,'fa-circle-dot','var(--orange)'],
      ['Servidor',$servidor,'fa-server','var(--green)'],
    ];
    foreach($stats as [$label,$val,$icon,$color]):?>
    <div style="text-align:center;">
      <i class="fa <?=$icon?>" style="font-size:1.5rem;color:<?=$color?>;margin-bottom:10px;display:block;"></i>
      <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--text);"><?=$val?></div>
      <div style="font-size:.78rem;text-transform:uppercase;letter-spacing:1px;color:var(--text-3);font-weight:600;"><?=$label?></div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- FEATURES -->
<section style="padding:80px 40px;max-width:1100px;margin:0 auto;">
  <div style="text-align:center;margin-bottom:52px;">
    <h2 style="font-size:2rem;margin-bottom:12px;">Todo lo que necesitas</h2>
    <p style="color:var(--text-2);max-width:480px;margin:0 auto;">Una plataforma completa para equipos de soporte IT y sus clientes.</p>
  </div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
    <?php
    $features=[
      ['fa-ticket','Gestión de incidencias','Crea, asigna y resuelve tickets de soporte con historial completo de comentarios.','var(--blue)'],
      ['fa-users','Base de clientes','Registro completo de clientes con histórico de incidencias y datos de contacto.','var(--purple)'],
      ['fa-store','Tienda online','Catálogo de productos tecnológicos con gestión de stock e información detallada.','var(--green)'],
      ['fa-chart-bar','Dashboard analítico','Vista general de métricas clave: incidencias abiertas, tiempo de resolución y más.','var(--orange)'],
      ['fa-file-lines','Informes exportables','Genera informes en PDF o TXT de cualquier incidencia con un solo clic.','var(--accent)'],
      ['fa-shield-halved','Alta disponibilidad','Infraestructura con balanceo de carga y múltiples servidores web redundantes.','var(--red)'],
    ];
    foreach($features as [$icon,$title,$desc,$color]):?>
    <div class="card" style="padding:24px;">
      <div style="width:44px;height:44px;border-radius:12px;background:<?=$color?>1a;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <i class="fa <?=$icon?>" style="color:<?=$color?>;font-size:1rem;"></i>
      </div>
      <h3 style="font-size:1rem;margin-bottom:8px;"><?=$title?></h3>
      <p style="font-size:.875rem;color:var(--text-2);line-height:1.6;"><?=$desc?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<footer style="background:var(--navy);padding:32px 40px;text-align:center;">
  <p style="font-size:.8rem;color:#4b5563;">&copy; <?=date('Y')?> Mastertech &mdash; IES Albarregas &mdash; Proyecto Intermodular 2&ordm; ASIR &mdash; Mario Da Silva Ortega</p>
</footer>
</body>
</html>