<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
$rut = $_SESSION["user_id"];

// Validación: ID de artículo por GET
if (!isset($_GET["id"])) {
    die("ID de artículo no especificado.");
}

$idArticulo = $_GET["id"];

// Verificar que el usuario es autor del artículo
$stmt = $mysqli->prepare("SELECT * FROM autoresArticulos WHERE IDArticulo = ? AND RutAut = ?");
$stmt->bind_param("is", $idArticulo, $rut);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("No tienes permiso para ver las evaluaciones de este artículo.");
}

// Verificar que todas las evaluaciones estén completas (ninguna nota en NULL)
$stmt = $mysqli->prepare("
    SELECT COUNT(*) AS incompletas 
    FROM revisiones 
    WHERE IDArticulo = ? 
    AND (CalidadTecnica IS NULL OR Originalidad IS NULL OR ValoracionGlobal IS NULL)
");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$res = $stmt->get_result();
$data = $res->fetch_assoc();

if ($data["incompletas"] > 0) {
    die("Las evaluaciones aún no están completas.");
}

// Obtener las evaluaciones completas
$stmt = $mysqli->prepare("
    SELECT r.RutRev, u.Nombre, r.CalidadTecnica, r.Originalidad, r.ValoracionGlobal, 
           r.ArgumentosValoracion, r.ComentariosRevisor
    FROM revisiones r
    JOIN usuario u ON r.RutRev = u.Rut
    WHERE r.IDArticulo = ?
");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Evaluaciones del Artículo</title>
</head>
<body>

<h2>Evaluaciones del Artículo #<?= htmlspecialchars($idArticulo) ?></h2>
<a href="checkArticle.php"><button>Volver</button></a>
<br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>Revisor</th>
        <th>Calidad Técnica</th>
        <th>Originalidad</th>
        <th>Valoración Global</th>
        <th>Argumentos Valoracion Global</th>
        <th>Comentarios al Autor</th>
    </tr>
    <?php 
    while ($row = $result->fetch_assoc()): 
    ?>
    <tr>
        <td><?= htmlspecialchars($row["Nombre"]) ?></td>
        <td><?= $row["CalidadTecnica"] ?></td>
        <td><?= $row["Originalidad"] ?></td>
        <td><?= $row["ValoracionGlobal"] ?></td>
        <td><?= nl2br(htmlspecialchars($row["ArgumentosValoracion"])) ?></td>
        <td><?= nl2br(htmlspecialchars($row["ComentariosRevisor"])) ?></td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
