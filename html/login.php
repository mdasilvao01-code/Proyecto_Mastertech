<?php
require_once 'db_config.php';

$error = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: /dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($password, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['rol'] = $usuario['rol'];
            
            logAccion('Login exitoso', "Usuario: $email");
            header('Location: /dashboard.php');
            exit();
        } else {
            $error = 'Email o contraseña incorrectos';
        }
    } catch (Exception $e) {
        $error = 'Error al iniciar sesión';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="container">
        <div class="main-content" style="max-width: 500px; margin-top: 100px;">
            <div class="text-center mb-4">
                <h1 style="color: #667eea; font-size: 3.5em;">🛠️</h1>
                <h1 style="color: #667eea;">MASTERTECH</h1>
                <p class="text-muted">Sistema de Gestión de Incidencias</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>❌ Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" class="form-control" placeholder="tu@email.com" required autofocus>
                </div>
                <div class="form-group">
                    <label>🔒 Contraseña</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    🚀 Iniciar Sesión
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="mb-2">¿No tienes cuenta?</p>
                <a href="/registro.php" class="btn btn-success w-100">
                    ➕ Crear Cuenta Nueva
                </a>
            </div>

            <hr class="my-4">

            <div class="text-center">
                <small class="text-muted">
                    <strong>Usuarios de prueba:</strong><br>
                    <strong>Admin:</strong> admin@mastertech.com<br>
                    <strong>Técnico:</strong> tecnico@mastertech.com<br>
                    <strong>Cliente:</strong> cliente@mastertech.com<br>
                    <em>Contraseña para todos: password123</em>
                </small>
            </div>

            <div class="text-center mt-4">
                <a href="/index.php" class="btn btn-secondary">
                    ← Volver al Inicio
                </a>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 MASTERTECH - Mario Da Silva Ortega - 2º ASIR</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
