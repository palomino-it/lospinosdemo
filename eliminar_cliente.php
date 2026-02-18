<?php
// eliminar_cliente.php
header('Content-Type: application/json');
include 'conexion.php';

// Leer el JSON recibido
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de cliente no proporcionado.']);
    exit;
}

$clienteId = intval($data['id']);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a BD.']);
    exit;
}

try {
    // 1. Verificar si tiene compraventas registradas
    $sqlCheck = "SELECT COUNT(*) as total FROM Compraventas WHERE titular_id = ?";
    $stmt = $conn->prepare($sqlCheck);
    $stmt->bind_param("i", $clienteId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $totalCompras = $row['total'];
    $stmt->close();

    if ($totalCompras > 0) {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar: El cliente tiene ' . $totalCompras . ' compra(s) registrada(s).']);
        exit;
    }

    // 2. Si no tiene compras, proceder a eliminar
    // Gracias al ON DELETE CASCADE en Titulares_Beneficiarios, se borrarán las relaciones automáticamente
    // Pero Beneficiarios (entidad) NO se borra porque es independiente
    $sqlDelete = "DELETE FROM Titulares WHERE id = ?";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->bind_param("i", $clienteId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado.']);
        }
    } else {
        throw new Exception($stmt->error);
    }
    $stmt->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
}

$conn->close();
?>
