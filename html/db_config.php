<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php_errors.log');

$host     = "192.168.40.13";
$usuario  = "webuser";
$password = "WebPass@2024";
$database = "mastertech";

try {
    $conexion = new mysqli($host, $usuario, $password, $database);
    if ($conexion->connect_error) throw new Exception($conexion->connect_error);
    $conexion->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error BD: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) session_start();

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO("mysql:host=192.168.40.13;dbname=mastertech;charset=utf8mb4",
            "webuser", "WebPass@2024",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    }
    return $pdo;
}

function verificarSesion() {
    if (!isset($_SESSION['usuario_id'])) { header('Location: /login.php'); exit(); }
}

function verificarRol($roles = []) {
    verificarSesion();
    if (!empty($roles) && !in_array($_SESSION['rol'], $roles)) { header('Location: /dashboard.php'); exit(); }
}

function logAccion($accion, $detalles = '') {
    global $conexion;
    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $u  = $_SESSION['nombre'] ?? 'guest';
        $st = $conexion->prepare("INSERT INTO app_logs (ip_origen,usuario,accion,detalles) VALUES (?,?,?,?)");
        if ($st) { $st->bind_param("ssss",$ip,$u,$accion,$detalles); $st->execute(); $st->close(); }
    } catch (Exception $e) { error_log($e->getMessage()); }
}
?>