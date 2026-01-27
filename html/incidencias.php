<?php
require_once 'db_config.php';
verificarSesion();

try {
    $pdo = getDB();
    
    $where = [];
    $params = [];
    
    if ($_SESSION['rol'] == 'cliente') {
        $where[] = "i.cliente_id = ?";
        $params[] = $_SESSION['usuario_id'];
    } elseif ($_SESSION['rol'] == 'tecnico' && !isset($_GET['todas'])) {
        $where[] = "i.tecnico_id = ?";
        $params[] = $_SESSION['usuario_id'];
    }
    
    $sql = "SELECT i.*, uc.nombre as cliente_nombre, ut.nombre as tecnico_nombre
            FROM incidencias i
            LEFT JOIN usuarios uc ON i.cliente_id = uc.id
            LEFT JOIN usuarios ut ON i.tecnico_id = ut.id";
    
    if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " ORDER BY i.fecha_creacion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $incidencias = $stmt->fetchAll();
    
} catch (Exception $e) {
    $incidencias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Incidencias - MASTERTECH</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container">
<div class="main-content">
<div class="d-flex justify-content-between mb-4">
<h1>📋 Gestión de Incidencias</h1>
<a href="/crear_incidencia.php" class="btn btn-primary">➕ Nueva Incidencia</a>
</div>
<div class="card">
<div class="card-body">
<p class="text-muted">Total: <?php echo count($incidencias); ?> incidencias</p>
<?php if (empty($incidencias)): ?>
<p class="text-center">No hay incidencias</p>
<div class="text-center">
<a href="/crear_incidencia.php" class="btn btn-primary">Crear Primera Incidencia</a>
</div>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th>
<th>Título</th>
<th>Cliente</th>
<th>Técnico</th>
<th>Estado</th>
<th>Prioridad</th>
<th>Fecha</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($incidencias as $inc): ?>
<tr>
<td><strong>#<?php echo $inc['id']; ?></strong></td>
<td><?php echo htmlspecialchars($inc['titulo']); ?></td>
<td><?php echo htmlspecialchars($inc['cliente_nombre'] ?? 'N/A'); ?></td>
<td><?php echo htmlspecialchars($inc['tecnico_nombre'] ?? 'Sin asignar'); ?></td>
<td><span class="badge badge-<?php echo strtolower(str_replace(' ','',$ inc['estado'])); ?>"><?php echo $inc['estado']; ?></span></td>
<td><span class="badge badge-<?php echo strtolower($inc['prioridad']); ?>"><?php echo $inc['prioridad']; ?></span></td>
<td><?php echo date('d/m/Y', strtotime($inc['fecha_creacion'])); ?></td>
<td><a href="/ver_incidencia.php?id=<?php echo $inc['id']; ?>" class="btn btn-primary btn-sm">Ver</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</div>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
