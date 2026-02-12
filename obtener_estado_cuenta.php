<?php
// obtener_estado_cuenta.php
header('Content-Type: application/json');

include 'conexion.php'; 

// Recibir término de búsqueda o ID
$termino = isset($GET['termino']) ? $GET['termino'] : '';
$id = isset($GET['id']) ? $GET['id'] : '';

if(empty($termino) && empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros de búsqueda.']);
    exit;
}

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión DB.', 'errors' => sqlsrv_errors()]);
    exit;
}

// TODO: Esta consulta es un ejemplo. Debes ajustarla a la estructura real de tus tablas.
// Asumimos relaciones: Titulares -> Compraventas -> Inmuebles, PagosFraccionados, AbonosRealizados

if(!empty($id)) {
    // Buscar detalle de un cliente específico por CompraventaID o TitularID
    // Aquí asumimos ID = CompraventaID para simplificar
    $sql = "SELECT c.id as CompraventaID, c.Total, c.PagoInicial, c.OtrosDescuentos, 
                   t.Nombre, t.DPI, t.Telefono, 
                   i.Lotes, i.Manzana, i.Area
            FROM Compraventas c
            JOIN Titulares t ON c.TitularID = t.ID
            JOIN Inmuebles i ON c.InmuebleID = i.ID
            WHERE c.ID = ?";
    
    $params = [$id];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if($stmt === false || sqlsrv_has_rows($stmt) === false) {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
        exit;
    }
    
    $cliente = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    // Obtener Pagos Pactados
    $sqlPactados = "SELECT NumeroPago, Fecha, Cantidad FROM PagosFraccionados WHERE CompraventaID = ?";
    $stmtPactados = sqlsrv_query($conn, $sqlPactados, [$id]);
    $pactados = [];
    while($row = sqlsrv_fetch_array($stmtPactados, SQLSRV_FETCH_ASSOC)) {
        $pactados[] = $row;
    }
    
    // Obtener Abonos Realizados
    $sqlAbonos = "SELECT Fecha, Monto, ReciboReferencia FROM AbonosRealizados WHERE CompraventaID = ?";
    $stmtAbonos = sqlsrv_query($conn, $sqlAbonos, [$id]);
    $abonos = [];
    while($row = sqlsrv_fetch_array($stmtAbonos, SQLSRV_FETCH_ASSOC)) {
        $abonos[] = $row;
    }
    
    // Estructurar respuesta para que coincida con lo que espera el frontend
    $respuesta = [
        'id' => $cliente['CompraventaID'],
        'titular' => [
            'nombre' => $cliente['Nombre'],
            'dpi' => $cliente['DPI'],
            'telefono' => $cliente['Telefono']
        ],
        'inmueble' => [
            'lotes' => $cliente['Lotes'],
            'manzana' => $cliente['Manzana'],
            'area' => $cliente['Area']
        ],
        'compraventa' => [
            'total' => $cliente['Total'],
            'pago' => $cliente['PagoInicial'],
            'otros_descuentos' => $cliente['OtrosDescuentos'],
            'pagos' => $pactados
        ],
        'abonos' => $abonos
    ];
    
    echo json_encode($respuesta);

} else {
    // Búsqueda general
    $sql = "SELECT c.ID, t.Nombre, t.DPI, i.Lotes 
            FROM Compraventas c
            JOIN Titulares t ON c.TitularID = t.ID
            JOIN Inmuebles i ON c.InmuebleID = i.ID
            WHERE t.Nombre LIKE ? OR t.DPI LIKE ?";
    $params = ["%$termino%", "%$termino%"];
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    $resultados = [];
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Formato ligero para lista
        $resultados[] = [
            'id' => $row['ID'],
            'titular' => ['nombre' => $row['Nombre'], 'dpi' => $row['DPI']],
            'inmueble' => ['lotes' => $row['Lotes']]
        ];
    }
    echo json_encode($resultados);
}
?>
