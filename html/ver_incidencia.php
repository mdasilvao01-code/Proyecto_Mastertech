<?php
require_once 'db_config.php';
verificarSesion();

$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo = '';

$pdo = getDB();

// Obtener incidencia
$stmt = $pdo->prepare("SELECT i.*, 
    uc.nombre as cliente_nombre, uc.email as cliente_email,
    ut.nombre as tecnico_nombre
    FROM incidencias i
    LEFT JOIN usuarios uc ON i.cliente_id = uc.id
    LEFT JOIN usuarios ut ON i.tecnico_id = ut.id
    WHERE i.id = ?");
$stmt->execute([$id]);
$incidencia = $stmt->fetch();

if (!$incidencia) {
    header('Location: incidencias.php');
    exit();
}

// Procesar comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'])) {
    $comentario = trim($_POST['comentario']);
    if (!empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO comentarios (incidencia_id, usuario_id, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$id, $_SESSION['usuario_id'], $comentario]);
        $mensaje = '✅ Comentario añadido';
        $tipo = 'success';
    }
}

// Cambiar estado
if (isset($_POST['nuevo_estado']) && ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'tecnico')) {
    $nuevo_estado = $_POST['nuevo_estado'];
    $stmt = $pdo->prepare("UPDATE incidencias SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $id]);
    $incidencia['estado'] = $nuevo_estado;
    $mensaje = '✅ Estado actualizado';
    $tipo = 'success';
}

// Asignar técnico
if (isset($_POST['asignar_tecnico']) && $_SESSION['rol'] == 'admin') {
    $tecnico_id = $_POST['tecnico_id'];
    $stmt = $pdo->prepare("UPDATE incidencias SET tecnico_id = ?, estado = 'En Proceso' WHERE id = ?");
    $stmt->execute([$tecnico_id, $id]);
    $mensaje = '✅ Técnico asignado';
    $tipo = 'success';
    header("Location: ver_incidencia.php?id=$id");
    exit();
}

// Obtener comentarios
$stmt = $pdo->prepare("SELECT c.*, u.nombre as usuario_nombre, u.rol 
    FROM comentarios c 
    JOIN usuarios u ON c.usuario_id = u.id 
    WHERE c.incidencia_id = ? 
    ORDER BY c.fecha DESC");
$stmt->execute([$id]);
$comentarios = $stmt->fetchAll();

// Obtener técnicos (para admin)
$tecnicos = [];
if ($_SESSION['rol'] == 'admin') {
    $tecnicos = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'tecnico'")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Incidencia #<?php echo $id; ?> - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Incidencia #<?php echo $id; ?></h1>
                <div>
                    <a href="generar_informe.php?id=<?php echo $id; ?>&tipo=pdf" class="btn btn-danger" target="_blank">📄 PDF</a>
                    <a href="generar_informe.php?id=<?php echo $id; ?>&tipo=txt" class="btn btn-secondary">📝 TXT</a>
                    <a href="incidencias.php" class="btn btn-primary">← Volver</a>
                </div>
            </div>

            <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo; ?>"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <!-- Detalles de la incidencia -->
            <div class="card mb-4">
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($incidencia['titulo']); ?></h3>
                    <hr>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($incidencia['cliente_nombre']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($incidencia['cliente_email']); ?></p>
                            <p><strong>Técnico:</strong> <?php echo htmlspecialchars($incidencia['tecnico_nombre'] ?? 'Sin asignar'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Estado:</strong> <span class="badge badge-<?php echo strtolower(str_replace(' ', '', $incidencia['estado'])); ?>"><?php echo $incidencia['estado']; ?></span></p>
                            <p><strong>Prioridad:</strong> <span class="badge badge-<?php echo strtolower($incidencia['prioridad']); ?>"><?php echo $incidencia['prioridad']; ?></span></p>
                            <p><strong>Categoría:</strong> <?php echo $incidencia['categoria']; ?></p>
                            <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($incidencia['fecha_creacion'])); ?></p>
                        </div>
                    </div>

                    <h5>Descripción:</h5>
                    <p><?php echo nl2br(htmlspecialchars($incidencia['descripcion'])); ?></p>
                </div>
            </div>

            <!-- Cambiar estado (Admin/Técnico) -->
            <?php if ($_SESSION['rol'] == 'admin' || $_SESSION['rol'] == 'tecnico'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h4>⚙️ Gestión</h4>
                    <form method="POST" class="row g-3">
                        <div class="col-md-6">
                            <label>Cambiar Estado</label>
                            <select name="nuevo_estado" class="form-control">
                                <option value="Abierto" <?php echo $incidencia['estado'] == 'Abierto' ? 'selected' : ''; ?>>Abierto</option>
                                <option value="En Proceso" <?php echo $incidencia['estado'] == 'En Proceso' ? 'selected' : ''; ?>>En Proceso</option>
                                <option value="Pendiente Cliente" <?php echo $incidencia['estado'] == 'Pendiente Cliente' ? 'selected' : ''; ?>>Pendiente Cliente</option>
                                <option value="Resuelto" <?php echo $incidencia['estado'] == 'Resuelto' ? 'selected' : ''; ?>>Resuelto</option>
                                <option value="Cerrado" <?php echo $incidencia['estado'] == 'Cerrado' ? 'selected' : ''; ?>>Cerrado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>&nbsp;</label><br>
                            <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Asignar técnico (Solo Admin) -->
            <?php if ($_SESSION['rol'] == 'admin' && empty($incidencia['tecnico_id'])): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h4>👨‍🔧 Asignar Técnico</h4>
                    <form method="POST" class="row g-3">
                        <div class="col-md-8">
                            <select name="tecnico_id" class="form-control" required>
                                <option value="">Seleccionar técnico...</option>
                                <?php foreach ($tecnicos as $tec): ?>
                                <option value="<?php echo $tec['id']; ?>"><?php echo htmlspecialchars($tec['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="asignar_tecnico" class="btn btn-success w-100">Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Comentarios -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4>💬 Comentarios</h4>
                    
                    <!-- Formulario nuevo comentario -->
                    <form method="POST" class="mb-4">
                        <div class="form-group">
                            <textarea name="comentario" class="form-control" rows="3" placeholder="Escribe un comentario..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2">💾 Añadir Comentario</button>
                    </form>

                    <!-- Lista de comentarios -->
                    <?php if (empty($comentarios)): ?>
                        <p class="text-muted">No hay comentarios todavía</p>
                    <?php else: ?>
                        <?php foreach ($comentarios as $com): ?>
                        <div class="comentario">
                            <div class="comentario-meta">
                                <strong><?php echo htmlspecialchars($com['usuario_nombre']); ?></strong> 
                                <span class="badge badge-<?php echo $com['rol']; ?>"><?php echo ucfirst($com['rol']); ?></span>
                                - <?php echo date('d/m/Y H:i', strtotime($com['fecha'])); ?>
                            </div>
                            <p><?php echo nl2br(htmlspecialchars($com['comentario'])); ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
