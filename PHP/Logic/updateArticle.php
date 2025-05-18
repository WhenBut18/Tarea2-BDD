<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == NULL) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/databaseConnect.php";

$rutUsuario = $_SESSION["user_id"];

$idArticulo = $_POST["idArticulo"] ?? null;
$titulo = $_POST["titulo"] ?? null;
$resumen = $_POST["resumen"] ?? null;
$autoresSeleccionados = $_POST["autores"] ?? [];  // Array con Rut de autores NO contacto
$topicosSeleccionados = $_POST["topicos"] ?? [];  // Array con IDs de tópicos

if (!$idArticulo || !$titulo || !$resumen) {
    die("Faltan datos obligatorios.");
}

// Validar que el usuario es autor y tiene permiso para editar este artículo
$stmt = $mysqli->prepare("SELECT EsAutor FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rutUsuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || $user["EsAutor"] == false) {
    die("No tienes permiso para editar.");
}

// Validar que el usuario es autor del artículo
$stmt = $mysqli->prepare("SELECT COUNT(*) as count FROM autoresArticulos WHERE IDArticulo = ? AND RutAut = ?");
$stmt->bind_param("is", $idArticulo, $rutUsuario);
$stmt->execute();
$result = $stmt->get_result();
$count = $result->fetch_assoc()["count"];
if ($count == 0) {
    die("No tienes permiso para editar este artículo.");
}

// Actualizar título y resumen en tabla articulos
$stmt = $mysqli->prepare("UPDATE articulos SET Titulo = ?, Resumen = ? WHERE IDArticulo = ?");
$stmt->bind_param("ssi", $titulo, $resumen, $idArticulo);
$stmt->execute();

// Actualizar tópicos: eliminar todos los existentes y luego insertar los nuevos seleccionados
$stmt = $mysqli->prepare("DELETE FROM topicosArticulos WHERE IDArticulo = ?");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();

if (count($topicosSeleccionados) > 0) {
    $stmtInsertTopico = $mysqli->prepare("INSERT INTO topicosArticulos (IDArticulo, IDTopico) VALUES (?, ?)");
    foreach ($topicosSeleccionados as $idTopico) {
        $idTopicoInt = (int)$idTopico;
        $stmtInsertTopico->bind_param("ii", $idArticulo, $idTopicoInt);
        $stmtInsertTopico->execute();
    }
    $stmtInsertTopico->close();
}

// Actualizar autores NO contacto:
// Primero obtener el Rut del autor de contacto para este artículo
$stmt = $mysqli->prepare("SELECT RutAut FROM autoresArticulos WHERE IDArticulo = ? AND EsContacto = TRUE");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();
$autorContacto = null;
if ($row = $result->fetch_assoc()) {
    $autorContacto = $row["RutAut"];
}

// Borrar todos los autores NO contacto del artículo
$stmt = $mysqli->prepare("DELETE FROM autoresArticulos WHERE IDArticulo = ? AND EsContacto = FALSE");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();

// Insertar autores NO contacto seleccionados
if (count($autoresSeleccionados) > 0) {
    $stmtInsertAutor = $mysqli->prepare("INSERT INTO autoresArticulos (IDArticulo, RutAut, EsContacto) VALUES (?, ?, FALSE)");
    foreach ($autoresSeleccionados as $rutAutor) {
        // Evitar insertar el autor de contacto aquí (solo no contacto)
        var_dump($autoresSeleccionados);
        if ($rutAutor === $autorContacto) {
            continue;
        }
        $stmtInsertAutor->bind_param("is", $idArticulo, $rutAutor);
        $stmtInsertAutor->execute();
    }
    $stmtInsertAutor->close();
}
header("Location: /Tarea2-BDD/PHP/Pages/checkArticle.php");
exit();
