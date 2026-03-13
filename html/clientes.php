<?php
require_once 'db_config.php';
$busqueda = $_GET['q'] ?? '';
try {
    $pdo = getDB();
    if (empty($busqueda)) {
        $stmt = $pdo->query("SELECT * FROM cliente ORDER BY fecha_registro DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE nombre LIKE ? OR email LIKE ? OR empresa LIKE ? ORDER BY fecha_registro DESC");
        $t="%$busqueda%"; $stmt->execute([$t,$t,$t]);
    }
    $clientes = $stmt->fetchAll();
} catch (Exception $e) { $clientes=[]; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Clientes — Mastertech</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="main-wrapper">
  <div class="topbar">
    <div class="topbar-title">Clientes</div>
    <a href="/register_cliente.php" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Nuevo cliente</a>
  </div>
  <div class="content">
    <form method="GET" class="filter-bar">
      <div class="input-group" style="flex:1;">
        <i class="fa fa-search input-icon"></i>
        <input type="text" name="q" class="form-control" placeholder="Buscar por nombre, email o empresa..." value="<?=htmlspecialchars($busqueda)?>">
      </div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Buscar</button>
      <a href="/clientes.php" class="btn btn-ghost btn-sm">Limpiar</a>
    </form>

    <div class="card">
      <div class="card-header">
        <h3><?=count($clientes)?> clientes registrados</h3>
        <a href="/register_cliente.php" class="btn btn-ghost btn-sm"><i class="fa fa-plus"></i> Añadir</a>
      </div>
      <?php if(empty($clientes)): ?>
      <div class="empty-state">
        <i class="fa fa-users" style="display:block;margin-bottom:12px;"></i>
        <h3>Sin clientes</h3>
        <p style="margin-bottom:20px;">Registra el primer cliente para empezar.</p>
        <a href="/register_cliente.php" class="btn btn-primary">Nuevo cliente</a>
      </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Empresa</th><th>Registro</th></tr></thead>
          <tbody>
            <?php foreach($clientes as $c): ?>
            <tr>
              <td style="color:var(--text-3);font-size:.82rem;"><?=$c['id_cliente']?></td>
              <td style="font-weight:500;"><?=htmlspecialchars($c['nombre'])?></td>
              <td style="font-size:.88rem;"><?=htmlspecialchars($c['email'])?></td>
              <td style="font-size:.88rem;color:var(--text-2);"><?=htmlspecialchars($c['telefono']??'—')?></td>
              <td style="font-size:.88rem;color:var(--text-2);"><?=htmlspecialchars($c['empresa']??'—')?></td>
              <td style="font-size:.85rem;color:var(--text-2);"><?=date('d/m/Y',strtotime($c['fecha_registro']))?></td>
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