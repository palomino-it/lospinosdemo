<?php
// obtener_beneficiarios.php
header('Content-Type: application/json');
include 'conexion.php';

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conn->connect_error]);
    exit;
}

// Consultar beneficiarios
// Seleccionamos campos clave para la búsqueda
$sql = "SELECT id, nombre, dpi, telefono, direccion FROM Beneficiarios ORDER BY nombre ASC";
$result = $conn->query($sql);

$beneficiarios = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $beneficiarios[] = $row;
    }
}

echo json_encode($beneficiarios);

$conn->close();
?>
