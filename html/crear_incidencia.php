<?php
require_once 'db_config.php';
verificarSesion();

$mensaje = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getDB();
        
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $prioridad = $_POST['prioridad'];
        $categoria = $_POST['categoria'];
        $cliente_id = $_SESSION['rol'] == 'cliente' ? $_SESSION['usuario_id'] : ($_POST['cliente_id'] ?? $_SESSION['usuario_id']);
        
        $stmt = $pdo->prepare("INSERT INTO incidencias (titulo, descripcion, prioridad, categoria, cliente_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$titulo, $descripcion, $prioridad, $categoria, $cliente_id]);
        
        $incidencia_id = $pdo->lastInsertId();
        logAccion('Nueva incidencia', "ID: $incidencia_id");
        
        $mensaje = "✅ Incidencia #$incidencia_id creada correctamente";
        $tipo = 'success';
        
    } catch (Exception $e) {
        $mensaje = "❌ Error: " . $e->getMessage();
        $tipo = 'danger';
    }
}

$clientes = [];
if ($_SESSION['rol'] != 'cliente') {
    try {
        $pdo = getDB();
        $clientes = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'cliente'")->fetchAll();
    } catch (Exception $e) {
        $clientes = [];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nueva Incidencia - MASTERTECH</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="/style.css">
</head>
<body>
<?php include 'includes/navbar.php'; ?>
<div class="container">
<div class="main-content">
<h1>➕ Crear Nueva Incidencia</h1>
<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo; ?>">
<?php echo $mensaje; ?>
<?php if ($tipo == 'success'): ?>
<div class="mt-3">
<a href="/incidencias.php" class="btn btn-primary">Ver Todas</a>
<a href="/crear_incidencia.php" class="btn btn-secondary">Crear Otra</a>
</div>
<?php endif; ?>
</div>
<?php endif; ?>
<div class="card">
<div class="card-body p-4">
<form method="POST">
<?php if ($_SESSION['rol'] != 'cliente' && !empty($clientes)): ?>
<div class="form-group">
<label>Cliente</label>
<select name="cliente_id" class="form-control">
<option value="">Seleccionar...</option>
<?php foreach ($clientes as $cli): ?>
<option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nombre']); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endif; ?>
<div class="form-group">
<label>Título *</label>
<input type="text" name="titulo" class="form-control" required>
</div>
<div class="form-group">
<label>Descripción *</label>
<textarea name="descripcion" class="form-control" rows="5" required></textarea>
</div>
<div class="row">
<div class="col-md-6">
<div class="form-group">
<label>Prioridad</label>
<select name="prioridad" class="form-control">
<option value="Baja">Baja</option>
<option value="Media" selected>Media</option>
<option value="Alta">Alta</option>
<option value="Crítica">Crítica</option>
</select>
</div>
</div>
<div class="col-md-6">
<div class="form-group">
<label>Categoría</label>
<select name="categoria" class="form-control">
<option value="Hardware">Hardware</option>
<option value="Software">Software</option>
<option value="Red">Red</option>
<option value="Seguridad">Seguridad</option>
<option value="Base de Datos">Base de Datos</option>
<option value="Correo Electrónico">Correo</option>
<option value="Otros">Otros</option>
</select>
</div>
</div>
</div>
<div class="text-center mt-4">
<button type="submit" class="btn btn-success btn-lg">💾 Crear</button>
<a href="/incidencias.php" class="btn btn-secondary btn-lg">Cancelar</a>
</div>
</form>
</div>
</div>
</div>
</div>
<?php include 'includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
