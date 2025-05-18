<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

if (!isset($_POST["idArticulo"]) || !isset($_POST["rutRevisor"])) {
    echo "Faltan datos para asignar revisor.";
    exit();
}

$idArticulo = intval($_POST["idArticulo"]);
$rutRevisor = $_POST["rutRevisor"];

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// 1. Validar que el revisor no sea autor del artículo
$sql = "SELECT 1 FROM autoresArticulos WHERE IDArticulo = ? AND RutAut = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rutRevisor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "El revisor no puede ser asignado porque es autor del artículo.";
    exit();
}

// 2. Validar que el artículo no tenga ya 3 revisores
$sql = "SELECT COUNT(*) AS cantidad FROM revisiones WHERE IDArticulo = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row["cantidad"] >= 3) {
    echo "El artículo ya tiene el máximo de 3 revisores asignados.";
    exit();
}

// 3. Validar que el revisor no esté ya asignado
$sql = "SELECT 1 FROM revisiones WHERE IDArticulo = ? AND RutRev = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rutRevisor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "El revisor ya está asignado a este artículo.";
    exit();
}

// 4. Insertar la asignación
$sql = "INSERT INTO revisiones (IDArticulo, RutRev) VALUES (?, ?)";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rutRevisor);

if ($stmt->execute()) {
    header("Location: ../Pages/articleReviewer.php");
    exit();
} else {
    echo "Error al asignar revisor: " . $stmt->error;
}
?>
