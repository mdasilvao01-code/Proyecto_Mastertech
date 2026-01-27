<?php
require_once 'db_config.php';
verificarSesion();

try {
    $pdo = getDB();
    
    // Estadísticas
    $total = $pdo->query("SELECT COUNT(*) FROM incidencias")->fetchColumn();
    $abiertas = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'Abierto'")->fetchColumn();
    $proceso = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'En Proceso'")->fetchColumn();
    $resueltas = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE estado = 'Resuelto'")->fetchColumn();
    $criticas = $pdo->query("SELECT COUNT(*) FROM incidencias WHERE prioridad = 'Crítica'")->fetchColumn();
    $clientes_total = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
    
    // Incidencias recientes
    if ($_SESSION['rol'] == 'cliente') {
        $stmt = $pdo->prepare("SELECT * FROM incidencias WHERE cliente_id = ? ORDER BY fecha_creacion DESC LIMIT 5");
        $stmt->execute([$_SESSION['usuario_id']]);
    } elseif ($_SESSION['rol'] == 'tecnico') {
        $stmt = $pdo->prepare("SELECT * FROM incidencias WHERE tecnico_id = ? ORDER BY fecha_creacion DESC LIMIT 5");
        $stmt->execute([$_SESSION['usuario_id']]);
    } else {
        $stmt = $pdo->query("SELECT * FROM incidencias ORDER BY fecha_creacion DESC LIMIT 5");
    }
    $incidencias_recientes = $stmt->fetchAll();
    
} catch (Exception $e) {
    $total = 0;
    $abiertas = 0;
    $proceso = 0;
    $resueltas = 0;
    $criticas = 0;
    $clientes_total = 0;
    $incidencias_recientes = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1>📊 Dashboard</h1>
                    <p class="text-muted">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong> 
                    <span class="badge badge-<?php echo $_SESSION['rol']; ?>"><?php echo ucfirst($_SESSION['rol']); ?></span></p>
                </div>
                <a href="/crear_incidencia.php" class="btn btn-primary btn-lg">➕ Nueva Incidencia</a>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>📋 Total Incidencias</h3>
                    <p><?php echo $total; ?></p>
                </div>
                <div class="stat-card">
                    <h3>🆕 Abiertas</h3>
                    <p><?php echo $abiertas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>⚙️ En Proceso</h3>
                    <p><?php echo $proceso; ?></p>
                </div>
                <div class="stat-card">
                    <h3>✅ Resueltas</h3>
                    <p><?php echo $resueltas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>🚨 Críticas</h3>
                    <p><?php echo $criticas; ?></p>
                </div>
                <div class="stat-card">
                    <h3>👥 Clientes</h3>
                    <p><?php echo $clientes_total; ?></p>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-center p-3">
                        <h3 style="font-size: 3em;">📋</h3>
                        <h5>Incidencias</h5>
                        <a href="/incidencias.php" class="btn btn-primary btn-sm">Ver Todas</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3">
                        <h3 style="font-size: 3em;">➕</h3>
                        <h5>Nueva Incidencia</h5>
                        <a href="/crear_incidencia.php" class="btn btn-success btn-sm">Crear</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3">
                        <h3 style="font-size: 3em;">🛒</h3>
                        <h5>Tienda</h5>
                        <a href="/tienda.php" class="btn btn-info btn-sm">Comprar</a>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center p-3">
                        <h3 style="font-size: 3em;">👥</h3>
                        <h5>Clientes</h5>
                        <a href="/clientes.php" class="btn btn-warning btn-sm">Gestionar</a>
                    </div>
                </div>
            </div>

            <!-- Incidencias recientes -->
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-4">📌 Incidencias Recientes</h3>
                    <?php if (empty($incidencias_recientes)): ?>
                        <p class="text-center text-muted">No hay incidencias registradas</p>
                        <div class="text-center">
                            <a href="/crear_incidencia.php" class="btn btn-primary">Crear la primera incidencia</a>
                        </div>
                    <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Estado</th>
                                <th>Prioridad</th>
                                <th>Fecha</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias_recientes as $inc): ?>
                            <tr>
                                <td><strong>#<?php echo $inc['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($inc['titulo']); ?></td>
                                <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '', $inc['estado'])); ?>"><?php echo $inc['estado']; ?></span></td>
                                <td><span class="badge badge-<?php echo strtolower($inc['prioridad']); ?>"><?php echo $inc['prioridad']; ?></span></td>
                                <td><?php echo date('d/m/Y', strtotime($inc['fecha_creacion'])); ?></td>
                                <td><a href="/ver_incidencia.php?id=<?php echo $inc['id']; ?>" class="btn btn-primary btn-sm">Ver</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="/incidencias.php" class="btn btn-secondary">Ver Todas</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
