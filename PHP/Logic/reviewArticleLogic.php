<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$rut = $_SESSION["user_id"];
$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Verificar que es revisor
$stmt = $mysqli->prepare("SELECT EsRevisor FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || !$user["EsRevisor"]) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idArticulo = $_POST["id_articulo"];
    $accion = $_POST["accion"];

    if ($accion === "guardar") {
        $calidad = $_POST["calidad"];
        $originalidad = $_POST["originalidad"];
        $valoracion = $_POST["valoracion"];
        $argumentos = $_POST["argumentos"];
        $comentarios = $_POST["comentarios"];

        $stmt = $mysqli->prepare("
            UPDATE revisiones
            SET CalidadTecnica = ?, Originalidad = ?, ValoracionGlobal = ?, 
                ArgumentosValoracion = ?, ComentariosRevisor = ?
            WHERE IDArticulo = ? AND RutRev = ?
        ");
        $stmt->bind_param("iiissis", $calidad, $originalidad, $valoracion, $argumentos, $comentarios, $idArticulo, $rut);
        $stmt->execute();

        header("Location: ..\Pages\reviewArticle.php");
        exit();
    }

    if ($accion === "eliminar") {
        $stmt = $mysqli->prepare("DELETE FROM revisiones WHERE IDArticulo = ? AND RutRev = ?");
        $stmt->bind_param("is", $idArticulo, $rut);
        $stmt->execute();

        header("Location: ..\Pages\reviewArticle.php");
        exit();
    }
}
?>
