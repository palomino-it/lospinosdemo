<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Prueba de Conexión a Base de Datos</h1>";
echo "<p>Intentando conectar...</p>";

include 'conexion.php';

if ($conn->connect_error) {
    echo "<h2 style='color:red'>❌ FALLÓ: " . $conn->connect_error . "</h2>";
} else {
    echo "<h2 style='color:green'>✅ ¡ÉXITO! Conexión Establecida Correctamente.</h2>";
    echo "<p>Base de datos seleccionada: registro_db</p>";
}
?>
