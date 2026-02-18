<?php
// conexion.php
$servername = "localhost";
$username = "root";  // Usuario por defecto en XAMPP
$password = "";      // Contraseña por defecto en XAMPP (vacía)
$dbname = "registro_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset a utf8
$conn->set_charset("utf8");
?>
