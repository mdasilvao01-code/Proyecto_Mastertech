<?php
require_once 'db_config.php';
verificarSesion();

$id = $_GET['id'] ?? 0;
$tipo = $_GET['tipo'] ?? 'txt';

$pdo = getDB();

// Obtener incidencia
$stmt = $pdo->prepare("SELECT i.*, 
    uc.nombre as cliente_nombre, uc.email as cliente_email, uc.empresa as cliente_empresa,
    ut.nombre as tecnico_nombre, ut.email as tecnico_email
    FROM incidencias i
    LEFT JOIN usuarios uc ON i.cliente_id = uc.id
    LEFT JOIN usuarios ut ON i.tecnico_id = ut.id
    WHERE i.id = ?");
$stmt->execute([$id]);
$inc = $stmt->fetch();

if (!$inc) {
    die('Incidencia no encontrada');
}

// Obtener comentarios
$stmt = $pdo->prepare("SELECT c.*, u.nombre as usuario_nombre, u.rol 
    FROM comentarios c 
    JOIN usuarios u ON c.usuario_id = u.id 
    WHERE c.incidencia_id = ? 
    ORDER BY c.fecha ASC");
$stmt->execute([$id]);
$comentarios = $stmt->fetchAll();

if ($tipo == 'txt') {
    // Generar TXT
    $filename = "incidencia_{$id}_" . date('YmdHis') . ".txt";
    $filepath = "reports/txt/" . $filename;
    
    $contenido = "=====================================\n";
    $contenido .= "   INFORME DE INCIDENCIA #$id\n";
    $contenido .= "   SISTEMA MASTERTECH\n";
    $contenido .= "=====================================\n\n";
    
    $contenido .= "INFORMACIÓN GENERAL\n";
    $contenido .= "-------------------\n";
    $contenido .= "ID: #$id\n";
    $contenido .= "Título: " . $inc['titulo'] . "\n";
    $contenido .= "Estado: " . $inc['estado'] . "\n";
    $contenido .= "Prioridad: " . $inc['prioridad'] . "\n";
    $contenido .= "Categoría: " . $inc['categoria'] . "\n";
    $contenido .= "Fecha Creación: " . date('d/m/Y H:i:s', strtotime($inc['fecha_creacion'])) . "\n\n";
    
    $contenido .= "CLIENTE\n";
    $contenido .= "-------\n";
    $contenido .= "Nombre: " . $inc['cliente_nombre'] . "\n";
    $contenido .= "Email: " . $inc['cliente_email'] . "\n";
    $contenido .= "Empresa: " . ($inc['cliente_empresa'] ?? 'N/A') . "\n\n";
    
    $contenido .= "TÉCNICO ASIGNADO\n";
    $contenido .= "----------------\n";
    $contenido .= "Nombre: " . ($inc['tecnico_nombre'] ?? 'Sin asignar') . "\n";
    $contenido .= "Email: " . ($inc['tecnico_email'] ?? 'N/A') . "\n\n";
    
    $contenido .= "DESCRIPCIÓN\n";
    $contenido .= "-----------\n";
    $contenido .= $inc['descripcion'] . "\n\n";
    
    if (!empty($comentarios)) {
        $contenido .= "HISTORIAL DE COMENTARIOS\n";
        $contenido .= "------------------------\n\n";
        
        foreach ($comentarios as $com) {
            $contenido .= "[" . date('d/m/Y H:i', strtotime($com['fecha'])) . "] ";
            $contenido .= $com['usuario_nombre'] . " (" . ucfirst($com['rol']) . "):\n";
            $contenido .= $com['comentario'] . "\n\n";
        }
    }
    
    $contenido .= "\n=====================================\n";
    $contenido .= "Generado: " . date('d/m/Y H:i:s') . "\n";
    $contenido .= "Por: " . $_SESSION['nombre'] . "\n";
    $contenido .= "=====================================\n";
    
    file_put_contents($filepath, $contenido);
    
    // Descargar
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit();
    
} elseif ($tipo == 'pdf') {
    // Generar PDF usando HTML2PDF simple
    $filename = "incidencia_{$id}_" . date('YmdHis') . ".pdf";
    
    // HTML para el PDF
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .header { text-align: center; background: #667eea; color: white; padding: 20px; margin-bottom: 30px; }
            .section { margin-bottom: 20px; }
            .section h3 { background: #f3f4f6; padding: 10px; border-left: 4px solid #667eea; }
            .info-grid { display: grid; grid-template-columns: 150px 1fr; gap: 10px; }
            .label { font-weight: bold; }
            .badge { padding: 5px 10px; border-radius: 5px; display: inline-block; }
            .badge-critica { background: #ef4444; color: white; }
            .badge-alta { background: #f59e0b; color: white; }
            .badge-media { background: #3b82f6; color: white; }
            .badge-baja { background: #10b981; color: white; }
            .comentario { background: #f9fafb; padding: 15px; margin: 10px 0; border-left: 3px solid #667eea; }
            .footer { text-align: center; margin-top: 40px; color: #6b7280; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🛠️ MASTERTECH</h1>
            <h2>Informe de Incidencia #' . $id . '</h2>
        </div>
        
        <div class="section">
            <h3>📋 Información General</h3>
            <div class="info-grid">
                <div class="label">ID:</div><div>#' . $id . '</div>
                <div class="label">Título:</div><div>' . htmlspecialchars($inc['titulo']) . '</div>
                <div class="label">Estado:</div><div>' . $inc['estado'] . '</div>
                <div class="label">Prioridad:</div><div><span class="badge badge-' . strtolower($inc['prioridad']) . '">' . $inc['prioridad'] . '</span></div>
                <div class="label">Categoría:</div><div>' . $inc['categoria'] . '</div>
                <div class="label">Fecha:</div><div>' . date('d/m/Y H:i', strtotime($inc['fecha_creacion'])) . '</div>
            </div>
        </div>
        
        <div class="section">
            <h3>👤 Cliente</h3>
            <div class="info-grid">
                <div class="label">Nombre:</div><div>' . htmlspecialchars($inc['cliente_nombre']) . '</div>
                <div class="label">Email:</div><div>' . htmlspecialchars($inc['cliente_email']) . '</div>
                <div class="label">Empresa:</div><div>' . htmlspecialchars($inc['cliente_empresa'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <h3>🔧 Técnico Asignado</h3>
            <div class="info-grid">
                <div class="label">Nombre:</div><div>' . htmlspecialchars($inc['tecnico_nombre'] ?? 'Sin asignar') . '</div>
                <div class="label">Email:</div><div>' . htmlspecialchars($inc['tecnico_email'] ?? 'N/A') . '</div>
            </div>
        </div>
        
        <div class="section">
            <h3>📝 Descripción</h3>
            <p>' . nl2br(htmlspecialchars($inc['descripcion'])) . '</p>
        </div>';
    
    if (!empty($comentarios)) {
        $html .= '<div class="section">
            <h3>💬 Historial de Comentarios</h3>';
        
        foreach ($comentarios as $com) {
            $html .= '<div class="comentario">
                <strong>' . htmlspecialchars($com['usuario_nombre']) . '</strong> (' . ucfirst($com['rol']) . ') - ' . date('d/m/Y H:i', strtotime($com['fecha'])) . '<br>
                ' . nl2br(htmlspecialchars($com['comentario'])) . '
            </div>';
        }
        
        $html .= '</div>';
    }
    
    $html .= '
        <div class="footer">
            <p>Generado: ' . date('d/m/Y H:i:s') . ' | Por: ' . $_SESSION['nombre'] . '</p>
            <p>© 2026 MASTERTECH - Sistema de Gestión de Incidencias</p>
        </div>
    </body>
    </html>';
    
    // Guardar HTML temporal y convertir a PDF usando wkhtmltopdf o similar
    // Por simplicidad, enviamos como HTML que se puede imprimir como PDF desde el navegador
    header('Content-Type: text/html; charset=UTF-8');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $html;
    echo '<script>window.print();</script>';
    exit();
}
?>
