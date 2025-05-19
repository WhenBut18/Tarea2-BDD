<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

if (!isset($_GET["id"])) {
    echo "ID del artículo no especificado.";
    exit();
}

$idArticulo = intval($_GET["id"]);

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Obtener revisores asignados a este artículo
$sql = "SELECT * FROM vista_revisores_asignados WHERE IDArticulo = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Este artículo no tiene revisores asignados.</p>";
    echo '<a href="articleReviewer.php">Volver al dashboard</a>';
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quitar Revisor</title>
</head>
<body>
<h1>Revisores asignados al artículo #<?= $idArticulo ?></h1>
<table border="1">
    <tr>
        <th>Nombre</th>
        <th>Tópicos</th>
        <th>Artículos Asignados</th>
        <th>Acción</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row["NombreRevisor"]) ?></td>
            <td><?= nl2br(htmlspecialchars($row["TopicosRevisor"])) ?></td>
            <td><?= nl2br(htmlspecialchars($row["ArticulosAsignados"])) ?></td>
            <td>
                <form action="../Logic/deleteArticleReviewerLogic.php" method="post" onsubmit="return confirm('¿Estás seguro de quitar este revisor?');">
                    <input type="hidden" name="idArticulo" value="<?= $idArticulo ?>">
                    <input type="hidden" name="rutRevisor" value="<?= $row["RutRevisor"] ?>">
                    <button type="submit">Quitar</button>
                </form>
            </td>
        </tr>
    <?php endwhile; ?>
</table>
<br>
<a href="articleReviewer.php"><button>Volver</button></a>
</body>
</html>
