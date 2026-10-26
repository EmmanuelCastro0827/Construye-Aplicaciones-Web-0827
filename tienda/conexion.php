<?php
// Mostrar errores en pantalla (para que no salga en blanco)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Si tu MySQL tiene contraseña, escríbela en el tercer valor
$servidor = "localhost";
$usuario  = "root";
$clave    = "";
$basedatos = "tienda_db";

mysqli_report(MYSQLI_REPORT_OFF);
$conexion = @new mysqli($servidor, $usuario, $clave, $basedatos);

if ($conexion->connect_errno) {
    die("<h3>No se pudo conectar a la base de datos</h3>
         <p>Error: " . $conexion->connect_error . "</p>
         <p>Revisa que MySQL esté en <b>Start</b> en XAMPP y que ejecutaste los 3 archivos SQL en phpMyAdmin.</p>");
}
$conexion->set_charset("utf8mb4");

session_start();
?>
