<?php
header('Content-Type: application/json');
require_once '../../includes/conexion.php';

// Aqui esta la creacion de esta API que obtiene las ordenes y los productos cuando un usuarios realiza una compra.

session_start();
if (!isset($_SESSION['user_type']) {
    echo json_encode(['error' => true, 'message' => 'No autorizado']);
    exit;
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    echo json_encode(['error' => true, 'message' => 'ID de orden inválido']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            o.*,
            CONCAT(c.primer_nombre, ' ', c.primer_apellido) AS cliente_nombre
        FROM ordenes o
        JOIN clientes c ON o.cliente_cedula = c.cedula
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => true, 'message' => 'Orden no encontrada']);
        exit;
    }
    
    $orden = $result->fetch_assoc();
    
    $stmt = $conn->prepare("
        SELECT 
            od.*,
            p.nombre,
            p.descripcion,
            p.imagen_principal
        FROM orden_detalles od
        JOIN productos p ON od.producto_id = p.id
        WHERE od.orden_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $response = [
        'orden' => $orden,
        'productos' => $productos
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    echo json_encode(['error' => true, 'message' => $e->getMessage()]);
}