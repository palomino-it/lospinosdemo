<?php
// procesar_formulario.php
header('Content-Type: application/json');

include 'conexion.php'; // Asegúrate de que este archivo tiene la conexión $conn creada con sqlsrv_connect

// Leer el JSON recibido
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos JSON válidos.']);
    exit;
}

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.', 'errors' => sqlsrv_errors()]);
    exit;
}

// Iniciar transacción
if (sqlsrv_begin_transaction($conn) === false) {
    echo json_encode(['success' => false, 'message' => 'No se pudo iniciar la transacción.', 'errors' => sqlsrv_errors()]);
    exit;
}

try {
    /* -------------------------------------------------------------
       1. Insertar TITULAR
       ------------------------------------------------------------- */
    $t = $data['titular'];
    $sqlTitular = "INSERT INTO Titulares (Nombre, Direccion, Telefono, DPI, NIT, FechaNacimiento, Edad, EstadoCivil, Nacionalidad, Profesion, Empresa, DireccionTrabajo, TelefonosTrabajo, Puesto, TiempoTrabajo, Salario, OtrosIngresos) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?); 
                   SELECT SCOPE_IDENTITY() AS id;";
    
    $paramsTitular = [
        $t['nombre'], $t['direccion'], $t['telefono'], $t['dpi'], $t['nit'], $t['fecha_nacimiento'], 
        $t['edad'], $t['estado_civil'], $t['nacionalidad'], $t['profesion'], $t['empresa'], 
        $t['direccion_trabajo'], $t['telefonos_trabajo'], $t['puesto'], $t['tiempo_trabajo'], 
        $t['salario'], $t['otros_ingresos']
    ];

    $stmtTitular = sqlsrv_query($conn, $sqlTitular, $paramsTitular);
    if ($stmtTitular === false) throw new Exception("Error insertando Titular: " . print_r(sqlsrv_errors(), true));
    
    sqlsrv_next_result($stmtTitular); // Mover al resultado del SELECT
    $rowTitular = sqlsrv_fetch_array($stmtTitular, SQLSRV_FETCH_ASSOC);
    $titularId = $rowTitular['id'];

    /* -------------------------------------------------------------
       2. Insertar BENEFICIARIO
       ------------------------------------------------------------- */
    // Asumiendo que quieres relacionarlo con el Titular, si la tabla Beneficiarios tiene TitularID
    $b = $data['beneficiario'];
    $sqlBeneficiario = "INSERT INTO Beneficiarios (TitularID, Nombre, Direccion, DPI, Telefonos, Email) 
                        VALUES (?, ?, ?, ?, ?, ?)";
    $paramsBeneficiario = [
        $titularId, $b['b_nombre'], $b['b_direccion'], $b['b_dpi'], $b['b_telefonos'], $b['b_email']
    ];
    
    $stmtBeneficiario = sqlsrv_query($conn, $sqlBeneficiario, $paramsBeneficiario);
    if ($stmtBeneficiario === false) throw new Exception("Error insertando Beneficiario: " . print_r(sqlsrv_errors(), true));

    /* -------------------------------------------------------------
       3. Insertar INMUEBLE
       ------------------------------------------------------------- */
    $i = $data['inmueble'];
    $sqlInmueble = "INSERT INTO Inmuebles (Lotes, Manzana, Area, Finca, Folio, Libro, DeLugar) 
                    VALUES (?, ?, ?, ?, ?, ?, ?);
                    SELECT SCOPE_IDENTITY() AS id;";
    $paramsInmueble = [
        $i['lotes'], $i['manzana'], $i['area'], $i['finca'], $i['folio'], $i['libro'], $i['de_lugar']
    ];
    
    $stmtInmueble = sqlsrv_query($conn, $sqlInmueble, $paramsInmueble);
    if ($stmtInmueble === false) throw new Exception("Error insertando Inmueble: " . print_r(sqlsrv_errors(), true));
    
    sqlsrv_next_result($stmtInmueble);
    $rowInmueble = sqlsrv_fetch_array($stmtInmueble, SQLSRV_FETCH_ASSOC);
    $inmuebleId = $rowInmueble['id'];

    /* -------------------------------------------------------------
       4. Insertar COMPRAVENTA
       ------------------------------------------------------------- */
    $c = $data['compraventa'];
    $sqlCompraventa = "INSERT INTO Compraventas (TitularID, InmuebleID, PrecioTerreno, GastosUrbanizacion, Total, OtrosDescuentos, PagoInicial, Saldo, SumaPagosFraccionados) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
                       SELECT SCOPE_IDENTITY() AS id;";
    $paramsCompraventa = [
        $titularId, $inmuebleId, $c['precio_terreno'], $c['gastos_urbanizacion'], $c['total'], 
        $c['otros_descuentos'], $c['pago'], $c['saldo'], $c['suma_pagos']
    ];
    
    $stmtCompraventa = sqlsrv_query($conn, $sqlCompraventa, $paramsCompraventa);
    if ($stmtCompraventa === false) throw new Exception("Error insertando Compraventa: " . print_r(sqlsrv_errors(), true));
    
    sqlsrv_next_result($stmtCompraventa);
    $rowCompraventa = sqlsrv_fetch_array($stmtCompraventa, SQLSRV_FETCH_ASSOC);
    $compraventaId = $rowCompraventa['id'];

    /* -------------------------------------------------------------
       5. Insertar PAGOS FRACCIONADOS
       ------------------------------------------------------------- */
    if (isset($c['pagos']) && is_array($c['pagos'])) {
        foreach ($c['pagos'] as $indice => $pago) {
            if(!empty($pago['fecha']) && !empty($pago['cantidad'])) {
                $sqlPagos = "INSERT INTO PagosFraccionados (CompraventaID, NumeroPago, Fecha, Cantidad) VALUES (?, ?, ?, ?)";
                $paramsPagos = [$compraventaId, $indice+1, $pago['fecha'], $pago['cantidad']];
                $stmtPagos = sqlsrv_query($conn, $sqlPagos, $paramsPagos);
                if ($stmtPagos === false) throw new Exception("Error insertando Pago {$indice}: " . print_r(sqlsrv_errors(), true));
            }
        }
    }

    // Confirmar transacción
    sqlsrv_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Expediente guardado correctamente. ID de Compraventa: ' . $compraventaId]);

} catch (Exception $e) {
    // Revertir cambios si algo falló
    sqlsrv_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
