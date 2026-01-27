<?php
require_once 'db_config.php';

$busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$clientes = array();

try {
    $pdo = getDB();
    
    if (empty($busqueda)) {
        $stmt = $pdo->query("SELECT * FROM cliente ORDER BY fecha_registro DESC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM cliente WHERE nombre LIKE ? OR email LIKE ? OR empresa LIKE ? ORDER BY fecha_registro DESC");
        $termino = "%$busqueda%";
        $stmt->execute([$termino, $termino, $termino]);
    }
    
    $clientes = $stmt->fetchAll();
    
} catch (Exception $e) {
    $clientes = array();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Clientes - Mastertech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🏢 Mastertech</a>
            <div class="collapse navbar-collapse justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="clientes.php">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link" href="register_cliente.php">Nuevo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>👥 Gestión de Clientes</h1>
                <p class="text-muted">Total: <?php echo count($clientes); ?> clientes</p>
            </div>
            <a href="register_cliente.php" class="btn btn-primary">➕ Nuevo Cliente</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-10">
                        <input type="text" name="buscar" class="form-control" 
                               placeholder="Buscar por nombre, email o empresa..."
                               value="<?php echo htmlspecialchars($busqueda); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">🔍 Buscar</button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($clientes)): ?>
            <div class="alert alert-warning text-center">
                <h4>No hay clientes registrados</h4>
                <a href="register_cliente.php" class="btn btn-primary mt-2">Registrar el primero</a>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Empresa</th>
                        <th>Fecha Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cli): ?>
                    <tr>
                        <td><strong><?php echo $cli['id_cliente']; ?></strong></td>
                        <td><?php echo htmlspecialchars($cli['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cli['email']); ?></td>
                        <td><?php echo htmlspecialchars($cli['telefono'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($cli['empresa'] ?? 'N/A'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($cli['fecha_registro'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Mastertech</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
