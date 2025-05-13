<?php 
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

$stmt = $mysqli->prepare("SELECT * FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "Usuario no encontrado.";
    exit();
}

$mensaje = "";
if (isset($_SESSION["mensaje_exito"])) {
    $mensaje = $_SESSION["mensaje_exito"];
    unset($_SESSION["mensaje_exito"]); // Para que no se repita al refrescar
}


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información de Usuario</title>
</head>
<body>
    <h2>Mi Perfil</h2> <a href="index.php"><button>Volver al Inicio</button></a><br><br>

    
    <form action="/Tarea2-BDD/PHP/Logic/updateUserLogic.php" method="POST">
        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($user['Nombre']) ?>" required><br><br>

        <label for="rut">Rut: <?= htmlspecialchars($user['Rut']) ?></label><br><br>

        <label for="correo">Correo:</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($user['Correo']) ?>" required><br><br>

        <label for="contraseña">Contraseña:</label>
        <input type="contraseña" name="contraseña" value="<?= htmlspecialchars($user['Contraseña']) ?>" required><br><br>

        <button type="submit">Guardar cambios</button>
    </form>

    <hr>

    <form action="/Tarea2-BDD/PHP/Logic/deleteUserLogic.php" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar tu cuenta? Esta acción no se puede deshacer.');">
        <button type="submit" style="color: red;">Eliminar cuenta</button>
    </form>

    <?php if (!empty($mensaje)): ?>
        <p style="color: green;"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

</body>
</html>
