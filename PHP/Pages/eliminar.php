<?php
session_start();
$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Obtener el RUT del usuario desde la sesión
$rut = $_SESSION["user_id"];

// Verificar que el RUT no esté vacío
if (!empty($rut)) {
    // Preparar la consulta para eliminar el usuario por RUT
    $stmt = $mysqli->prepare("DELETE FROM usuario WHERE Rut = ?");
    $stmt->bind_param("s", $rut);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Antes de destruir la sesión, guardamos el mensaje
        $_SESSION["mensaje_exito"] = "Tu cuenta ha sido eliminada con éxito.";

        // Destruir la sesión
        session_destroy();
        
        // Redirigir al login.php
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    } else {
        // Si hubo un error al ejecutar la consulta
        echo "Error al eliminar la cuenta.";
    }
}
?>

