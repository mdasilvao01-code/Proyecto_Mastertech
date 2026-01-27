<?php
require_once 'db_config.php';

$mensaje = '';
$tipo = '';

if (isset($_SESSION['usuario_id'])) {
    header('Location: /dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        $empresa = trim($_POST['empresa']);
        $telefono = trim($_POST['telefono']);
        
        if (empty($nombre) || empty($email) || empty($password)) {
            throw new Exception('Nombre, email y contraseña son obligatorios');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('El email no es válido');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('La contraseña debe tener al menos 6 caracteres');
        }
        
        if ($password !== $password_confirm) {
            throw new Exception('Las contraseñas no coinciden');
        }
        
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            throw new Exception('Este email ya está registrado');
        }
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, empresa, telefono) VALUES (?, ?, ?, 'cliente', ?, ?)");
        $stmt->execute([$nombre, $email, $password_hash, $empresa, $telefono]);
        
        logAccion('Nuevo registro', "Usuario: $nombre - $email");
        
        $mensaje = '✅ Registro exitoso. Ya puedes iniciar sesión con tus credenciales.';
        $tipo = 'success';
        $_POST = array();
        
    } catch (Exception $e) {
        $mensaje = '❌ Error: ' . $e->getMessage();
        $tipo = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="container">
        <div class="main-content" style="max-width: 700px; margin-top: 50px;">
            <div class="text-center mb-4">
                <h1 style="color: #667eea; font-size: 3em;">🛠️ MASTERTECH</h1>
                <h2>Crear Nueva Cuenta</h2>
                <p class="text-muted">Regístrate para gestionar tus incidencias</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo; ?>">
                <?php echo $mensaje; ?>
                <?php if ($tipo === 'success'): ?>
                    <div class="mt-3 text-center">
                        <a href="/login.php" class="btn btn-primary btn-lg">Iniciar Sesión Ahora</a>
                    </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-4">
                    <form method="POST" id="formRegistro">
                        <h5 class="mb-3">📋 Información Personal</h5>
                        
                        <div class="form-group">
                            <label>Nombre Completo *</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   required>
                            <small class="text-muted">Usarás este email para iniciar sesión</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Empresa</label>
                                    <input type="text" name="empresa" class="form-control" 
                                           value="<?php echo isset($_POST['empresa']) ? htmlspecialchars($_POST['empresa']) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control" 
                                           value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>" 
                                           placeholder="666123456">
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">🔒 Seguridad</h5>

                        <div class="form-group">
                            <label>Contraseña *</label>
                            <input type="password" name="password" id="password" class="form-control" 
                                   minlength="6" required>
                            <small class="text-muted">Mínimo 6 caracteres</small>
                        </div>

                        <div class="form-group">
                            <label>Confirmar Contraseña *</label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" 
                                   minlength="6" required>
                            <small id="passwordMatch" class="text-muted"></small>
                        </div>

                        <hr class="my-4">

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                ✅ Crear Cuenta
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-2">¿Ya tienes cuenta?</p>
                        <a href="/login.php" class="btn btn-secondary">Iniciar Sesión</a>
                        <a href="/index.php" class="btn btn-secondary">Volver al Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 MASTERTECH</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const pass1 = document.getElementById('password');
        const pass2 = document.getElementById('password_confirm');
        const match = document.getElementById('passwordMatch');

        function checkMatch() {
            if (pass2.value === '') {
                match.textContent = '';
                return;
            }
            if (pass1.value === pass2.value) {
                match.textContent = '✓ Las contraseñas coinciden';
                match.className = 'text-success';
            } else {
                match.textContent = '✗ Las contraseñas no coinciden';
                match.className = 'text-danger';
            }
        }

        pass1.addEventListener('input', checkMatch);
        pass2.addEventListener('input', checkMatch);
    </script>
</body>
</html>
