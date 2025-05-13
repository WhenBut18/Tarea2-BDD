<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

// Validaciones básicas
if (empty($_POST["nombre"]) || empty($_POST["correo"])) {
    $_SESSION["mensaje_error"] = "Nombre y correo son obligatorios.";
    header("Location: /Tarea2-BDD/PHP/Pages/userInfo.php");
    exit();
}

// Validar formato de correo
if (!filter_var($_POST["correo"], FILTER_VALIDATE_EMAIL)) {
    $_SESSION["mensaje_error"] = "El correo no es válido.";
    header("Location: /Tarea2-BDD/PHP/Pages/userInfo.php");
    exit();
}

// Datos limpios
$nombre = trim($_POST["nombre"]);
$correo = trim($_POST["correo"]);
$rut = trim($_SESSION["user_id"]);
$contraseña = trim($_POST["contraseña"] ?? "");

$mysqli = require "databaseConnect.php";

// Si se proporcionó contraseña, actualizarla; si no, mantener la anterior
if (!empty($contraseña)) {
    $stmt = $mysqli->prepare("UPDATE usuario SET Nombre = ?, Correo = ?, Contraseña = ? WHERE Rut = ?");
    $stmt->bind_param("ssss", $nombre, $correo, $contraseña, $rut);
} else {
    $stmt = $mysqli->prepare("UPDATE usuario SET Nombre = ?, Correo = ? WHERE Rut = ?");
    $stmt->bind_param("sss", $nombre, $correo, $rut);
}

if (!$stmt) {
    $_SESSION["mensaje_error"] = "Error en la base de datos: " . $mysqli->error;
    header("Location: /Tarea2-BDD/PHP/Pages/userInfo.php");
    exit();
}

if ($stmt->execute()) {
    $_SESSION["mensaje_exito"] = "Datos actualizados correctamente.";
} else {
    $_SESSION["mensaje_error"] = "Error al guardar: " . $stmt->error;
}

$stmt->close();
$mysqli->close();

header("Location: /Tarea2-BDD/PHP/Pages/userInfo.php");
exit();
?>



