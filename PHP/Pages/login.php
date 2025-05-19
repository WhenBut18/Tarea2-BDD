<?php
session_start();

// Mostrar el mensaje de éxito si está disponible
if (isset($_SESSION["mensaje_exito"])) {
    echo "<p style='color: green;'>" . htmlspecialchars($_SESSION["mensaje_exito"]) . "</p>";
    unset($_SESSION["mensaje_exito"]); // Borrar el mensaje de la sesión para que no se repita
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicia Sesion</title>
    <link rel="stylesheet" href="/Tarea2-BDD/CSS/login.css">
</head>
<body>
    <div class = "login">
    <h1>Inicia Sesion</h1>
    <form action="/Tarea2-BDD/PHP/Logic/loginLogic.php" method="post">
        <label for="rutLogin">Rut Usuario (sin puntos ni guion)</label>
        <input required id="rutLogin" name="rutLogin" type="text" class = "barras" placeholder="Ingrese su rut.." >
        <br><br>
        <label for="passwordLogin">Contraseña</label>
        <input required name="passwordLogin" id="passwordLogin" type="text" class = "barras" placeholder="Ingrese su contraseña..">
        <br><br>
        <button type="submit">Ingresar</button>
    </form>
    <h3>¿Eres nuevo? <a href="/Tarea2-BDD/PHP/Pages/register.php">registrate aquí.</a></h3>
    </div>
</body>
</html>