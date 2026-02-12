<?php
// registrar_pago.php
header('Content-Type: application/json');

include 'conexion.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['compraventaId']) || !isset($data['monto'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión DB.']);
    exit;
}

// Iniciar transacción
if (sqlsrv_begin_transaction($conn) === false) {
    echo json_encode(['success' => false, 'message' => 'Error iniciando transacción.']);
    exit;
}

try {
    $sql = "INSERT INTO AbonosRealizados (CompraventaID, Fecha, Monto, ReciboReferencia) VALUES (?, ?, ?, ?)";
    $params = [
        $data['compraventaId'],
        $data['fecha'],
        $data['monto'],
        $data['recibo']
    ];
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt === false) throw new Exception("Error insertando abono: " . print_r(sqlsrv_errors(), true));
    
    // Opcional: Actualizar saldo en tabla Compraventas si tienes una columna de saldo actualizable
    // $sqlUpdate = "UPDATE Compraventas SET Saldo = Saldo - ? WHERE ID = ?";
    // sqlsrv_query(...)

    sqlsrv_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Abono registrado correctamente.']);

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
