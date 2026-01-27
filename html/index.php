<?php
require_once 'db_config.php';
logAccion('Vista página principal');

$totalClientes = 0;
$totalIncidencias = 0;
$pendientes = 0;

try {
    $pdo = getDB();
    $totalClientes = $pdo->query("SELECT COUNT(*) FROM cliente")->fetchColumn();
    $totalIncidencias = $pdo->query("SELECT COUNT(*) FROM incidencia")->fetchColumn();
    $pendientes = $pdo->query("SELECT COUNT(*) FROM incidencia WHERE estado = 'Pendiente'")->fetchColumn();
} catch (Exception $e) {
    // Silenciar error
}

$servidor = gethostname();
$ip = $_SERVER['SERVER_ADDR'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mastertech - Sistema de Gestión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🏢 Mastertech</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="nav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="register_cliente.php">Nuevo Cliente</a></li>
                    <li class="nav-item"><a class="nav-link" href="ver_incidencias.php">Incidencias</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero">
        <h1>🚀 Sistema Mastertech</h1>
        <p>Gestión Completa de Clientes e Incidencias</p>
        <p style="font-size: 0.9em;">Servidor: <strong><?php echo htmlspecialchars($servidor); ?></strong> | IP: <strong><?php echo htmlspecialchars($ip); ?></strong></p>
    </div>

    <div class="container">
        <div class="info-cards">
            <div class="info-card">
                <h3>👥 Clientes</h3>
                <p><?php echo $totalClientes; ?></p>
            </div>
            <div class="info-card">
                <h3>📋 Incidencias</h3>
                <p><?php echo $totalIncidencias; ?></p>
            </div>
            <div class="info-card">
                <h3>⚠️ Pendientes</h3>
                <p><?php echo $pendientes; ?></p>
            </div>
            <div class="info-card">
                <h3>🕐 Hora</h3>
                <p><?php echo date('H:i'); ?></p>
            </div>
        </div>

        <div class="row g-4 mt-3">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <h2 style="font-size: 3em;">👥</h2>
                        <h4>Clientes</h4>
                        <p class="text-muted">Gestiona clientes</p>
                        <a href="clientes.php" class="btn btn-primary mt-3">Ver Clientes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <h2 style="font-size: 3em;">📋</h2>
                        <h4>Incidencias</h4>
                        <p class="text-muted">Tickets y soporte</p>
                        <a href="ver_incidencias.php" class="btn btn-primary mt-3">Ver Incidencias</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center p-4">
                        <h2 style="font-size: 3em;">📊</h2>
                        <h4>Dashboard</h4>
                        <p class="text-muted">Estadísticas</p>
                        <a href="dashboard.php" class="btn btn-primary mt-3">Ver Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Mastertech | IES Albarregas - Proyecto Intermodular</p>
        <p>Sistema de Alta Disponibilidad con Load Balancer</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
