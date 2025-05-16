<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
$rut = $_SESSION["user_id"];
$idArticulo = $_GET["id"] ?? null;

if (!$idArticulo) {
    echo "ID de artículo no proporcionado.";
    exit();
}

// Verificar que el usuario sea autor de contacto del artículo
$sql = "SELECT * FROM vista_articulos_autor WHERE IDArticulo = ? AND RutAut = ? AND EsContacto = 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rut);
$stmt->execute();
$result = $stmt->get_result();
$articulo = $result->fetch_assoc();

if (!$articulo) {
    echo "No tienes permiso para eliminar este artículo.";
    exit();
}

// Eliminar el artículo
$sql = "DELETE FROM articulos WHERE IDArticulo = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();

header("Location: checkArticle.php");
exit();
?>
