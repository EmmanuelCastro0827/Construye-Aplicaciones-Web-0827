<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
echo "<h2>Diagnóstico</h2>";
echo "<p>✔ PHP funciona (versión " . phpversion() . ")</p>";

if (!extension_loaded("mysqli")) {
    die("<p>✘ La extensión mysqli no está activa.</p>");
}
echo "<p>✔ mysqli activo</p>";

mysqli_report(MYSQLI_REPORT_OFF);
$c = @new mysqli("localhost", "root", "", "tienda_db");
if ($c->connect_errno) {
    die("<p>✘ No conecta: " . $c->connect_error . "<br>Si dice 'Unknown database', falta ejecutar los archivos SQL. Si dice 'Connection refused', MySQL no está en Start.</p>");
}
echo "<p>✔ Conexión a tienda_db correcta</p>";

foreach (["usuarios", "categorias", "marcas", "pedidos"] as $t) {
    $r = $c->query("SELECT COUNT(*) AS n FROM $t");
    if ($r) {
        echo "<p>✔ Tabla $t: " . $r->fetch_assoc()["n"] . " registros</p>";
    } else {
        echo "<p>✘ Falta la tabla $t (ejecuta 02_tablas.sql)</p>";
    }
}
?>
