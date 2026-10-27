<?php
include "conexion.php";

// Si no ha iniciado sesión, lo mandamos al login
if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

// Opciones del producto (puedes agregar o quitar versiones aquí)
$versiones = ["Base", "Sport", "Titanium", "Platinum"];

// Si todavía están las marcas viejas (o la tabla está vacía), se cambian solas por las camionetas Ford
$total = $conexion->query("SELECT COUNT(*) AS n FROM categorias WHERE nombre = 'Pickups Ford'")->fetch_assoc()["n"];
if ($total == 0) {
    $catalogo = [
        "Pickups Ford" => ["Ranger", "Ranger Raptor", "Maverick", "F-150", "F-150 Raptor", "F-150 Lightning", "Lobo", "F-250 Super Duty", "F-350 Super Duty"],
        "SUV Ford"     => ["Escape", "Territory", "Bronco Sport", "Bronco", "Edge", "Explorer", "Expedition"]
    ];
    $conexion->query("DELETE FROM pedidos");
    $conexion->query("DELETE FROM marcas");
    $conexion->query("DELETE FROM categorias");
    foreach ($catalogo as $categoria => $modelos) {
        $stmt = $conexion->prepare("INSERT INTO categorias (nombre) VALUES (?)");
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $categoria_id = $conexion->insert_id;
        foreach ($modelos as $modelo) {
            $stmt = $conexion->prepare("INSERT INTO marcas (nombre, categoria_id) VALUES (?, ?)");
            $stmt->bind_param("si", $modelo, $categoria_id);
            $stmt->execute();
        }
    }
}

// Guardar el pedido cuando se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION["usuario_id"];
    $marca_id   = (int) $_POST["marca_id"];
    $producto   = trim($_POST["producto"]);
    $cantidad   = (int) $_POST["cantidad"];
    $direccion  = trim($_POST["direccion"]);

    if ($marca_id > 0 && in_array($producto, $versiones) && $cantidad > 0 && $direccion != "") {
        $stmt = $conexion->prepare("INSERT INTO pedidos (usuario_id, marca_id, producto, cantidad, direccion) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisis", $usuario_id, $marca_id, $producto, $cantidad, $direccion);
        $stmt->execute();
        $mensaje = "¡Pedido guardado correctamente!";
    } else {
        $mensaje = "Llena todos los campos.";
    }
}

// Traer las marcas junto con su categoría
$marcas = $conexion->query("SELECT marcas.id, marcas.nombre AS marca, categorias.nombre AS categoria
                            FROM marcas
                            INNER JOIN categorias ON marcas.categoria_id = categorias.id
                            ORDER BY categorias.id, marcas.nombre");

// Traer los pedidos de este usuario
$stmt = $conexion->prepare("SELECT pedidos.producto, pedidos.cantidad, pedidos.direccion, pedidos.fecha,
                                   marcas.nombre AS marca, categorias.nombre AS categoria
                            FROM pedidos
                            INNER JOIN marcas ON pedidos.marca_id = marcas.id
                            INNER JOIN categorias ON marcas.categoria_id = categorias.id
                            WHERE pedidos.usuario_id = ?
                            ORDER BY pedidos.fecha DESC");
$stmt->bind_param("i", $_SESSION["usuario_id"]);
$stmt->execute();
$pedidos = $stmt->get_result();
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
        <span>Hola, <?php echo htmlspecialchars($_SESSION["nombre"]); ?> | <a href="logout.php">Cerrar sesión</a></span>
    </header>

    <div class="caja ancha">
        <h2>Hacer un pedido</h2>
        <?php if ($mensaje != "") echo "<p class='ok'>$mensaje</p>"; ?>

        <form method="POST">
            <label>Modelo Ford:</label>
            <select name="marca_id" required>
                <option value="">-- Selecciona un modelo --</option>
                <?php
                $categoria_actual = "";
                while ($fila = $marcas->fetch_assoc()) {
                    if ($fila["categoria"] != $categoria_actual) {
                        if ($categoria_actual != "") echo "</optgroup>";
                        echo "<optgroup label='" . $fila["categoria"] . "'>";
                        $categoria_actual = $fila["categoria"];
                    }
                    echo "<option value='" . $fila["id"] . "'>" . $fila["marca"] . "</option>";
                }
                echo "</optgroup>";
                ?>
            </select>

            <label>Producto (versión):</label>
            <select name="producto" required>
                <option value="">-- Selecciona una versión --</option>
                <?php foreach ($versiones as $v) { ?>
                    <option value="<?php echo $v; ?>"><?php echo $v; ?></option>
                <?php } ?>
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
                <th>Categoría</th><th>Modelo</th><th>Producto</th><th>Cant.</th><th>Dirección</th><th>Fecha</th>
            </tr>
            <?php while ($p = $pedidos->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $p["categoria"]; ?></td>
                <td><?php echo $p["marca"]; ?></td>
                <td><?php echo htmlspecialchars($p["producto"]); ?></td>
                <td><?php echo $p["cantidad"]; ?></td>
                <td><?php echo htmlspecialchars($p["direccion"]); ?></td>
                <td><?php echo $p["fecha"]; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
