<?php
require_once 'db_config.php';

// No requiere sesión para ver productos
$categoria_filtro = $_GET['categoria'] ?? '';

try {
    $pdo = getDB();
    
    if (empty($categoria_filtro)) {
        $stmt = $pdo->query("SELECT * FROM productos ORDER BY destacado DESC, nombre");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE categoria = ? ORDER BY nombre");
        $stmt->execute([$categoria_filtro]);
    }
    
    $productos = $stmt->fetchAll();
    
    $categorias = $pdo->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria")->fetchAll();
    
} catch (Exception $e) {
    $productos = [];
    $categorias = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda - MASTERTECH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <style>
        .product-card {
            height: 100%;
            transition: all 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .product-img {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4em;
        }
        .badge-destacado {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ef4444;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: bold;
        }
        .price {
            font-size: 1.8em;
            font-weight: bold;
            color: #667eea;
        }
        .stock-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.85em;
        }
        .stock-disponible { background: #d1fae5; color: #065f46; }
        .stock-bajo { background: #fef3c7; color: #92400e; }
        .stock-agotado { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['usuario_id'])): ?>
        <?php include 'includes/navbar.php'; ?>
    <?php else: ?>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">🛠️ MASTERTECH</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/index.php">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link active" href="/tienda.php">🛒 Tienda</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="/login.php">🔐 Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/registro.php">➕ Registro</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div class="hero">
        <h1>🛒 Tienda MASTERTECH</h1>
        <p style="font-size: 1.2em;">Los mejores productos tecnológicos al mejor precio</p>
    </div>

    <div class="container">
        <div class="main-content">
            <!-- Filtros por categoría -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Filtrar por categoría:</h5>
                    <div class="btn-group flex-wrap" role="group">
                        <a href="/tienda.php" class="btn btn-<?php echo empty($categoria_filtro) ? 'primary' : 'outline-primary'; ?>">Todas</a>
                        <?php foreach ($categorias as $cat): ?>
                        <a href="/tienda.php?categoria=<?php echo urlencode($cat['categoria']); ?>" 
                           class="btn btn-<?php echo $categoria_filtro == $cat['categoria'] ? 'primary' : 'outline-primary'; ?>">
                            <?php echo $cat['categoria']; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Productos destacados -->
            <?php if (empty($categoria_filtro)): ?>
            <?php
            $destacados = array_filter($productos, function($p) { return $p['destacado']; });
            if (!empty($destacados)):
            ?>
            <h2 class="mb-4">⭐ Productos Destacados</h2>
            <div class="row g-4 mb-5">
                <?php foreach (array_slice($destacados, 0, 4) as $prod): ?>
                <div class="col-md-3">
                    <div class="card product-card">
                        <div class="position-relative">
                            <div class="product-img">
                                <?php
                                $icons = [
                                    'Ordenadores' => '🖥️',
                                    'Portátiles' => '💻',
                                    'Componentes' => '🔧',
                                    'Periféricos' => '🖱️',
                                    'Redes' => '🌐',
                                    'Servidores' => '🗄️'
                                ];
                                echo $icons[$prod['categoria']] ?? '📦';
                                ?>
                            </div>
                            <span class="badge-destacado">⭐ Destacado</span>
                        </div>
                        <div class="card-body">
                            <small class="text-muted"><?php echo $prod['categoria']; ?></small>
                            <h5 class="mt-2"><?php echo htmlspecialchars($prod['nombre']); ?></h5>
                            <p class="text-muted" style="font-size: 0.9em; min-height: 60px;">
                                <?php echo substr($prod['descripcion'], 0, 80); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price"><?php echo number_format($prod['precio'], 2); ?>€</span>
                                <?php if ($prod['stock'] > 10): ?>
                                    <span class="stock-badge stock-disponible">✓ En stock</span>
                                <?php elseif ($prod['stock'] > 0): ?>
                                    <span class="stock-badge stock-bajo">⚠️ Pocas unidades</span>
                                <?php else: ?>
                                    <span class="stock-badge stock-agotado">✗ Agotado</span>
                                <?php endif; ?>
                            </div>
                            <a href="/producto.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary w-100">Ver Detalles</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Todos los productos -->
            <h2 class="mb-4">
                <?php echo $categoria_filtro ? $categoria_filtro : 'Todos los Productos'; ?>
                <small class="text-muted">(<?php echo count($productos); ?> productos)</small>
            </h2>

            <?php if (empty($productos)): ?>
                <div class="alert alert-info text-center">
                    <h4>No hay productos en esta categoría</h4>
                    <a href="/tienda.php" class="btn btn-primary mt-2">Ver todos los productos</a>
                </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($productos as $prod): ?>
                <div class="col-md-3">
                    <div class="card product-card">
                        <div class="position-relative">
                            <div class="product-img">
                                <?php
                                $icons = [
                                    'Ordenadores' => '🖥️',
                                    'Portátiles' => '💻',
                                    'Componentes' => '🔧',
                                    'Periféricos' => '🖱️',
                                    'Redes' => '🌐',
                                    'Servidores' => '🗄️'
                                ];
                                echo $icons[$prod['categoria']] ?? '📦';
                                ?>
                            </div>
                            <?php if ($prod['destacado']): ?>
                            <span class="badge-destacado">⭐ Destacado</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <small class="text-muted"><?php echo $prod['categoria']; ?></small>
                            <h6 class="mt-2"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="price" style="font-size: 1.5em;"><?php echo number_format($prod['precio'], 2); ?>€</span>
                                <?php if ($prod['stock'] > 10): ?>
                                    <span class="stock-badge stock-disponible">✓ Stock</span>
                                <?php elseif ($prod['stock'] > 0): ?>
                                    <span class="stock-badge stock-bajo">⚠️ Poco</span>
                                <?php else: ?>
                                    <span class="stock-badge stock-agotado">✗ No</span>
                                <?php endif; ?>
                            </div>
                            <a href="/producto.php?id=<?php echo $prod['id']; ?>" class="btn btn-primary btn-sm w-100">Ver más</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['usuario_id'])): ?>
        <?php include 'includes/footer.php'; ?>
    <?php else: ?>
    <footer class="footer">
        <p>&copy; 2026 MASTERTECH - Tienda Online</p>
    </footer>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
