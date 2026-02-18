<?php
// procesar_formulario.php
header('Content-Type: application/json');

include 'conexion.php';

// Leer el JSON recibido
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos JSON válidos.']);
    exit;
}

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a BD.', 'error' => $conn->connect_error]);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    /* -------------------------------------------------------------
       1. Insertar TITULAR
       ------------------------------------------------------------- */
    $t = $data['titular'];
    $sqlTitular = "INSERT INTO Titulares (nombre, direccion, telefono, dpi, nit, fecha_nacimiento, edad, estado_civil, nacionalidad, profesion, empresa, direccion_trabajo, telefonos_trabajo, puesto, tiempo_trabajo, salario, otros_ingresos) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sqlTitular);
    $stmt->bind_param("ssssssissssssssdd", 
        $t['nombre'], $t['direccion'], $t['telefono'], $t['dpi'], $t['nit'], $t['fecha_nacimiento'], 
        $t['edad'], $t['estado_civil'], $t['nacionalidad'], $t['profesion'], $t['empresa'], 
        $t['direccion_trabajo'], $t['telefonos_trabajo'], $t['puesto'], $t['tiempo_trabajo'], 
        $t['salario'], $t['otros_ingresos']
    );
    
    if (!$stmt->execute()) throw new Exception("Error Titular: " . $stmt->error);
    $titularId = $conn->insert_id;
    $stmt->close();

    /* -------------------------------------------------------------
       2. Insertar BENEFICIARIO
       ------------------------------------------------------------- */
    $b = $data['beneficiario'];
    // Validar si existe beneficiario
    if (!empty($b['b_nombre'])) {
        $sqlBeneficiario = "INSERT INTO Beneficiarios (titular_id, nombre, direccion, dpi, telefonos, email) 
                            VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlBeneficiario);
        $stmt->bind_param("isssss", $titularId, $b['b_nombre'], $b['b_direccion'], $b['b_dpi'], $b['b_telefonos'], $b['b_email']);
        if (!$stmt->execute()) throw new Exception("Error Beneficiario: " . $stmt->error);
        $stmt->close();
    }

    /* -------------------------------------------------------------
       3. Insertar INMUEBLE
       ------------------------------------------------------------- */
    $i = $data['inmueble'];
    $sqlInmueble = "INSERT INTO Inmuebles (titular_id, lotes, manzana, area, finca, folio, libro, de_lugar) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlInmueble);
    $stmt->bind_param("isssssss", $titularId, $i['lotes'], $i['manzana'], $i['area'], $i['finca'], $i['folio'], $i['libro'], $i['de_lugar']);
    if (!$stmt->execute()) throw new Exception("Error Inmueble: " . $stmt->error);
    $inmuebleId = $conn->insert_id;
    $stmt->close();

    /* -------------------------------------------------------------
       4. Insertar COMPRAVENTA
       ------------------------------------------------------------- */
    $c = $data['compraventa'];
    $sqlCompraventa = "INSERT INTO Compraventas (titular_id, inmueble_id, precio_terreno, gastos_urbanizacion, total, otros_descuentos, pago_inicial, saldo, suma_pagos_fraccionados) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sqlCompraventa);
    
    // Convertir a float/double
    $precio = (float)$c['precio_terreno'];
    $gastos = (float)$c['gastos_urbanizacion'];
    $total = (float)$c['total'];
    $descuentos = (float)$c['otros_descuentos'];
    $pago = (float)$c['pago'];
    $saldo = (float)$c['saldo'];
    $sumaPagos = (float)$c['suma_pagos'];

    $stmt->bind_param("iiddddddd", 
        $titularId, $inmuebleId, $precio, $gastos, $total, $descuentos, $pago, $saldo, $sumaPagos
    );

    if (!$stmt->execute()) throw new Exception("Error Compraventa: " . $stmt->error);
    $compraventaId = $conn->insert_id;
    $stmt->close();

    /* -------------------------------------------------------------
       5. Insertar PAGOS FRACCIONADOS
       ------------------------------------------------------------- */
    if (isset($c['pagos']) && is_array($c['pagos'])) {
        $sqlPagos = "INSERT INTO PagosFraccionados (compraventa_id, numero_pago, fecha, cantidad) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sqlPagos);
        
        foreach ($c['pagos'] as $indice => $pagoData) {
            if(!empty($pagoData['fecha']) && !empty($pagoData['cantidad'])) {
                $numPago = $indice + 1;
                $cant = (float)$pagoData['cantidad'];
                $stmt->bind_param("iisd", $compraventaId, $numPago, $pagoData['fecha'], $cant);
                if (!$stmt->execute()) throw new Exception("Error Pago {$numPago}: " . $stmt->error);
            }
        }
        $stmt->close();
    }

    // Confirmar transacción
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Expediente guardado exitosamente en MySQL.', 'id' => $titularId]);

} catch (Exception $e) {
    // Revertir cambios si algo falló
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
