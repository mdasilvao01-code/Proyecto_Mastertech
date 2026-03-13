<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

$host = "192.168.40.13";
$usuario = "webuser";
$password = "WebPass@2024";
$database = "mastertech";

try {
    $conexion = new mysqli($host, $usuario, $password, $database);
    if ($conexion->connect_error) {
        throw new Exception("Error de conexión: " . $conexion->connect_error);
    }
    $conexion->set_charset("utf8mb4");
} catch (Exception $e) {
    die("❌ Error de base de datos: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getDB() {
    try {
        $dsn = "mysql:host=192.168.40.13;dbname=mastertech;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        return new PDO($dsn, "webuser", "WebPass@2024", $options);
    } catch (PDOException $e) {
        die("Error PDO: " . $e->getMessage());
    }
}

function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /login.php');
        exit();
    }
}

function verificarRol($roles_permitidos = []) {
    verificarSesion();
    if (!empty($roles_permitidos) && !in_array($_SESSION['rol'], $roles_permitidos)) {
        header('Location: /dashboard.php');
        exit();
    }
}

function logAccion($accion, $detalles = '') {
    global $conexion;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $usuario = $_SESSION['nombre'] ?? 'guest';
        $stmt = $conexion->prepare("INSERT INTO app_logs (ip_origen, usuario, accion, detalles) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssss", $ip, $usuario, $accion, $detalles);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        error_log("Error al registrar log: " . $e->getMessage());
    }
}
?>
