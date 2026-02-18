<?php
// agregar_lote.php
header('Content-Type: application/json');
include 'conexion.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

try {
    $sql = "INSERT INTO InventarioLotes (manzana, lote, area, finca, folio, libro, valor_vara2, precio_total, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'DISPONIBLE')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssdd", 
        $data['manzana'], $data['lote'], $data['area'], 
        $data['finca'], $data['folio'], $data['libro'],
        $data['valor_vara2'], $data['precio_total']
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Lote agregado al inventario.']);
    } else {
        throw new Exception($stmt->error);
    }
    $stmt->close();
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        echo json_encode(['success' => false, 'message' => 'Error: Ya existe un lote con esa ubicación o registro.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
    }
}
$conn->close();
?>
