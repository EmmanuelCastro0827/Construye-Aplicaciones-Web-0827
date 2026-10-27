<?php
include "conexion.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo   = $conexion->real_escape_string($_POST["correo"]);
    $password = $_POST["password"];

    // Buscamos al usuario por su correo
    $resultado = $conexion->query("SELECT * FROM usuarios WHERE correo = '$correo'");
    $usuario = $resultado->fetch_assoc();

    // Si existe y la contraseña coincide, guardamos sus datos en la sesión
    if ($usuario && password_verify($password, $usuario["password"])) {
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["nombre"] = $usuario["nombre"];
        header("Location: tienda.php");
    } else {
        $mensaje = "Correo o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="caja">
        <h1>Iniciar sesión</h1>

        <?php
        if (isset($_GET["registrado"])) {
            echo "<p class='ok'>Registro exitoso, ya puedes entrar.</p>";
        }
        ?>
        <p class="error"><?php echo $mensaje; ?></p>

        <form method="POST">
            <label>Correo:</label>
            <input type="email" name="correo" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>

        <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
    </div>
</body>
</html>
