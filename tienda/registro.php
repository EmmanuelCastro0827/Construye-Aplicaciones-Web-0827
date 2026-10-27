<?php
include "conexion.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Guardamos lo que escribió el usuario en variables
    $nombre   = $conexion->real_escape_string($_POST["nombre"]);
    $correo   = $conexion->real_escape_string($_POST["correo"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT); // contraseña encriptada

    // Revisamos si el correo ya existe
    $buscar = $conexion->query("SELECT id FROM usuarios WHERE correo = '$correo'");

    if ($buscar->num_rows > 0) {
        $mensaje = "Ese correo ya está registrado.";
    } else {
        // Guardamos el usuario en la base de datos
        $conexion->query("INSERT INTO usuarios (nombre, correo, password) VALUES ('$nombre', '$correo', '$password')");
        header("Location: login.php?registrado=1");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="caja">
        <h1>Crear cuenta</h1>
        <p class="error"><?php echo $mensaje; ?></p>

        <form method="POST">
            <label>Nombre:</label>
            <input type="text" name="nombre" required>

            <label>Correo:</label>
            <input type="email" name="correo" required>

            <label>Contraseña (mínimo 6 caracteres):</label>
            <input type="password" name="password" minlength="6" required>

            <button type="submit">Registrarme</button>
        </form>

        <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    </div>
</body>
</html>
