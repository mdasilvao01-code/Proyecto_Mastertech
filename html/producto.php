<?php
require_once 'db_config.php';

$id = $_GET['id'] ?? 0;

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch();
    
    if (!$producto) {
        header('Location: /tienda.php');
        exit();
    }
    
} catch (Exception $e) {
    header('Location: /tienda.php');
    exit();
}

$icons = [
    'Ordenadores' => '🖥️',
    'Portátiles' => '💻',
    'Componentes' => '🔧',
    'Periféricos' => '🖱️',
    'Redes' => '🌐',
    'Servidores' => '🗄️'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['nombre']); ?> - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <?php include 'includes/navbar.php'; ?>
    <?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">🛠️ MASTERTECH</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/tienda.php">← Volver a Tienda</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <div class="container">
        <div class="main-content">
            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 400px; display: flex; align-items: center; justify-content: center; font-size: 10em; border-radius: 15px;">
                            <?php echo $icons[$producto['categoria']] ?? '📦'; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-7">
                    <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>
                    <p class="text-muted"><?php echo $producto['categoria']; ?></p>
                    
                    <?php if ($producto['destacado']): ?>
                    <span class="badge" style="background: #ef4444; color: white; padding: 8px 15px; font-size: 1em;">⭐ Producto Destacado</span>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <h2 style="color: #667eea; font-size: 3em; font-weight: bold;">
                        <?php echo number_format($producto['precio'], 2); ?>€
                    </h2>
                    
                    <div class="mb-4">
                        <?php if ($producto['stock'] > 10): ?>
                            <span class="badge" style="background: #10b981; color: white; padding: 8px 15px;">✓ En stock (<?php echo $producto['stock']; ?> unidades)</span>
                        <?php elseif ($producto['stock'] > 0): ?>
                            <span class="badge" style="background: #f59e0b; color: white; padding: 8px 15px;">⚠️ Pocas unidades (<?php echo $producto['stock']; ?> disponibles)</span>
                        <?php else: ?>
                            <span class="badge" style="background: #ef4444; color: white; padding: 8px 15px;">✗ Producto agotado</span>
                        <?php endif; ?>
                    </div>
                    
                    <h4>Descripción:</h4>
                    <p style="font-size: 1.1em; line-height: 1.6;">
                        <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                    </p>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <?php if ($producto['stock'] > 0): ?>
                        <button class="btn btn-success btn-lg" onclick="alert('Funcionalidad de compra próximamente. Contacta con ventas@mastertech.com')">
                            🛒 Comprar Ahora
                        </button>
                        <button class="btn btn-primary btn-lg" onclick="alert('Añadido al carrito! (Demo)')">
                            ➕ Añadir al Carrito
                        </button>
                        <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>
                            ✗ Producto No Disponible
                        </button>
                        <?php endif; ?>
                        <a href="/tienda.php?categoria=<?php echo urlencode($producto['categoria']); ?>" class="btn btn-outline-primary">
                            Ver más productos de <?php echo $producto['categoria']; ?>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-5">
            
            <h3>Especificaciones Técnicas</h3>
            <div class="card">
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="200">Categoría:</th>
                            <td><?php echo $producto['categoria']; ?></td>
                        </tr>
                        <tr>
                            <th>Precio:</th>
                            <td style="font-size: 1.3em; color: #667eea; font-weight: bold;"><?php echo number_format($producto['precio'], 2); ?>€</td>
                        </tr>
                        <tr>
                            <th>Disponibilidad:</th>
                            <td><?php echo $producto['stock']; ?> unidades en stock</td>
                        </tr>
                        <tr>
                            <th>Código Producto:</th>
                            <td>MTEC-<?php echo str_pad($producto['id'], 6, '0', STR_PAD_LEFT); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['usuario_id'])): ?>
        <?php include 'includes/footer.php'; ?>
    <?php else: ?>
    <footer class="footer">
        <p>&copy; 2026 MASTERTECH</p>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
