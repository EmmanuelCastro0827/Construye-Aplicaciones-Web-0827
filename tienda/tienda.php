<?php
include "conexion.php";

// Si no ha iniciado sesión, lo mandamos al login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
}

$mensaje = "";

// Cuando se envía el formulario, guardamos el pedido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION["usuario_id"];
    $marca_id   = (int) $_POST["marca_id"];
    $producto   = $conexion->real_escape_string($_POST["producto"]);
    $cantidad   = (int) $_POST["cantidad"];
    $direccion  = $conexion->real_escape_string($_POST["direccion"]);

    $conexion->query("INSERT INTO pedidos (usuario_id, marca_id, producto, cantidad, direccion)
                      VALUES ($usuario_id, $marca_id, '$producto', $cantidad, '$direccion')");

    $mensaje = "¡Pedido guardado correctamente!";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <header>
        <h1>Mi Tienda</h1>
        <p>Hola, <?php echo $_SESSION["nombre"]; ?> | <a href="logout.php">Cerrar sesión</a></p>
    </header>

    <div class="caja ancha">
        <h2>Hacer un pedido</h2>
        <p class="ok"><?php echo $mensaje; ?></p>

        <form method="POST">
            <label>Modelo Ford:</label>
            <select name="marca_id" required>
                <option value="">-- Selecciona un modelo --</option>
                <?php
                // Por cada categoría mostramos sus modelos
                $categorias = $conexion->query("SELECT * FROM categorias");
                while ($cat = $categorias->fetch_assoc()) {
                    echo "<optgroup label='" . $cat["nombre"] . "'>";

                    $modelos = $conexion->query("SELECT * FROM marcas WHERE categoria_id = " . $cat["id"]);
                    while ($mod = $modelos->fetch_assoc()) {
                        echo "<option value='" . $mod["id"] . "'>" . $mod["nombre"] . "</option>";
                    }

                    echo "</optgroup>";
                }
                ?>
            </select>

            <label>Producto (versión):</label>
            <select name="producto" required>
                <option value="">-- Selecciona una versión --</option>
                <option value="Base">Base</option>
                <option value="Sport">Sport</option>
                <option value="Titanium">Titanium</option>
                <option value="Platinum">Platinum</option>
            </select>

            <label>Cantidad:</label>
            <input type="number" name="cantidad" min="1" value="1" required>

            <label>Dirección de entrega:</label>
            <input type="text" name="direccion" required>

            <button type="submit">Enviar pedido</button>
        </form>
    </div>

    <div class="caja ancha">
        <h2>Mis pedidos</h2>
        <table>
            <tr>
                <th>Modelo</th>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Dirección</th>
                <th>Fecha</th>
            </tr>
            <?php
            // Mostramos solo los pedidos del usuario que inició sesión
            $pedidos = $conexion->query("SELECT * FROM pedidos WHERE usuario_id = " . $_SESSION["usuario_id"] . " ORDER BY id DESC");

            while ($p = $pedidos->fetch_assoc()) {
                // Buscamos el nombre del modelo con su id
                $modelo = $conexion->query("SELECT nombre FROM marcas WHERE id = " . $p["marca_id"])->fetch_assoc();

                echo "<tr>";
                echo "<td>" . $modelo["nombre"] . "</td>";
                echo "<td>" . $p["producto"] . "</td>";
                echo "<td>" . $p["cantidad"] . "</td>";
                echo "<td>" . $p["direccion"] . "</td>";
                echo "<td>" . $p["fecha"] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
