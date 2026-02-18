<?php
// obtener_lotes_disponibles.php
header('Content-Type: application/json');
include 'conexion.php';

$sql = "SELECT id, manzana, lote, area, finca, folio, libro, valor_vara2, precio_total 
        FROM InventarioLotes 
        WHERE estado = 'DISPONIBLE' 
        ORDER BY manzana, lote ASC";

$result = $conn->query($sql);

$lotes = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $lotes[] = $row;
    }
}

echo json_encode($lotes);
$conn->close();
?>
