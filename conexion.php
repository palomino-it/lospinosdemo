<?php
$serverName = "DESKTOP-K5GLIJD\SQLEXPRESS"; // o IP del servidor SQL
$connectionOptions = [
    "Database" => "RegistroDB",
    "Uid" => "sa",        // cambia por tu usuario
    "PWD" => "tu_contraseña" // cambia por tu contraseña
];

// Conexión
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
