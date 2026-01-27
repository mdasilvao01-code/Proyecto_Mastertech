<?php
require_once 'db_config.php';

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $empresa = trim($_POST['empresa'] ?? '');

        if (empty($nombre) || empty($email)) {
            throw new Exception('Nombre y email obligatorios');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email no válido');
        }

        $stmt = $conexion->prepare("INSERT INTO cliente (nombre, email, telefono, empresa) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $telefono, $empresa);
        
        if ($stmt->execute()) {
            logAccion('Registro cliente', "Cliente: $nombre");
            $mensaje = '✅ Cliente registrado correctamente';
            $tipo = 'success';
            $_POST = array();
        } else {
            throw new Exception($conexion->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $mensaje = '❌ Error: ' . $e->getMessage();
        $tipo = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Cliente - Mastertech</title>
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
                    <li class="nav-item"><a class="nav-link" href="clientes.php">Clientes</a></li>
                    <li class="nav-item"><a class="nav-link active" href="register_cliente.php">Nuevo</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <h1 class="text-center mb-4">➕ Registrar Nuevo Cliente</h1>
        
        <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
            <?php if ($tipo === 'success'): ?>
                <div class="mt-3">
                    <a href="clientes.php" class="btn btn-primary">Ver Clientes</a>
                    <a href="register_cliente.php" class="btn btn-secondary">Registrar Otro</a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-4">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nombre Completo *</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Teléfono</label>
                                <input type="tel" name="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Empresa</label>
                                <input type="text" name="empresa" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">💾 Registrar Cliente</button>
                        <a href="clientes.php" class="btn btn-secondary btn-lg">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Mastertech</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
