<?php
session_start();

/*
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "ADMIN") {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}*/

if (!isset($_POST["idArticulo"]) || !isset($_POST["rutRevisor"])) {
    echo "Faltan datos para quitar el revisor.";
    exit();
}

$idArticulo = intval($_POST["idArticulo"]);
$rutRevisor = $_POST["rutRevisor"];

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

$sql = "DELETE FROM revisiones WHERE IDArticulo = ? AND RutRev = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rutRevisor);

if ($stmt->execute()) {
    header("Location: ../Pages/articleReviewer.php");
    exit();
} else {
    echo "Error al quitar revisor: " . $stmt->error;
}
?>
