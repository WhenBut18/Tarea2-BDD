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

// Verificar que el usuario sea autor de contacto de este artículo
$sql = "SELECT * FROM vista_articulos_autor WHERE IDArticulo = ? AND RutAut = ? AND EsContacto = 1";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rut);
$stmt->execute();
$result = $stmt->get_result();
$articulo = $result->fetch_assoc();

if (!$articulo) {
    echo "No tienes permisos para editar este artículo.";
    exit();
}

// Si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST["titulo"];
    $resumen = $_POST["resumen"];
    $fecha = $_POST["fecha"];

    $sql = "UPDATE articulos SET Titulo = ?, Resumen = ?, FechaEnvio = ? WHERE IDArticulo = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssi", $titulo, $resumen, $fecha, $idArticulo);
    $stmt->execute();

    header("Location: checkArticle.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Editar Artículo</title></head>
<body>
<h2>Editar Artículo</h2>
<form method="POST">
    <label>Título:</label><br>
    <input type="text" name="titulo" value="<?= htmlspecialchars($articulo["Titulo"]) ?>" required><br><br>

    <label>Resumen:</label><br>
    <textarea name="resumen" rows="4" cols="50" required><?= htmlspecialchars($articulo["Resumen"]) ?></textarea><br><br>

    <label>Fecha de Envío:</label><br>
    <input type="date" name="fecha" value="<?= $articulo["FechaEnvio"] ?>" required><br><br>

    <button type="submit">Guardar Cambios</button>
</form>
<br>
<a href="checkArticle.php"><button>Volver</button></a>
</body>
</html>
