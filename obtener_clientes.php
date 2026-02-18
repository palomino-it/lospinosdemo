<?php
// obtener_clientes.php
header('Content-Type: application/json');
include 'conexion.php';

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Consultar clientes (Titulares)
// Ordenamos por fecha de registro descendente para ver los nuevos primero
$sql = "SELECT id, nombre, dpi, telefono, direccion, fecha_registro FROM Titulares ORDER BY fecha_registro DESC";
$result = $conn->query($sql);

$clientes = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
}

echo json_encode($clientes);

$conn->close();
?>
