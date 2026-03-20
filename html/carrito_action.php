<?php
require_once 'db_config.php';

// Inicializar carrito en sesión
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$accion     = $_POST['accion']     ?? $_GET['accion']     ?? '';
$producto_id = (int)($_POST['producto_id'] ?? $_GET['producto_id'] ?? 0);
$cantidad    = (int)($_POST['cantidad']    ?? 1);
$redirect    = $_POST['redirect']  ?? $_SERVER['HTTP_REFERER'] ?? '/tienda.php';

try {
    $pdo = getDB();

    switch ($accion) {

        case 'agregar':
            if ($producto_id > 0) {
                $stmt = $pdo->prepare("SELECT id, stock FROM productos WHERE id = ?");
                $stmt->execute([$producto_id]);
                $prod = $stmt->fetch();
                if ($prod) {
                    $actual = $_SESSION['carrito'][$producto_id] ?? 0;
                    $nueva  = $actual + max(1, $cantidad);
                    // No superar stock
                    $_SESSION['carrito'][$producto_id] = min($nueva, $prod['stock']);
                }
            }
            break;

        case 'actualizar':
            if ($producto_id > 0) {
                if ($cantidad <= 0) {
                    unset($_SESSION['carrito'][$producto_id]);
                } else {
                    $stmt = $pdo->prepare("SELECT stock FROM productos WHERE id = ?");
                    $stmt->execute([$producto_id]);
                    $prod = $stmt->fetch();
                    if ($prod) {
                        $_SESSION['carrito'][$producto_id] = min($cantidad, $prod['stock']);
                    }
                }
            }
            break;

        case 'eliminar':
            unset($_SESSION['carrito'][$producto_id]);
            break;

        case 'vaciar':
            $_SESSION['carrito'] = [];
            break;
    }

} catch (Exception $e) {
    // Silenciar errores de BD en acciones de carrito
}

// Responder con JSON si es petición AJAX
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('Content-Type: application/json');
    $total_items = array_sum($_SESSION['carrito']);
    echo json_encode(['ok' => true, 'total_items' => $total_items]);
    exit();
}

// Redirección normal
header('Location: ' . $redirect);
exit();
