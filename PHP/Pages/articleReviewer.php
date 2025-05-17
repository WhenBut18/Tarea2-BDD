<?php
session_start();

// Asegurarse de que el usuario es el Jefe del Comité
/*
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== 'ADMIN') {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}*/

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

$sql = "SELECT * FROM vista_admin_articulos_ordenada";
$result = $mysqli->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Jefe del Comité</title>
</head>
<body>
    <h2>Panel del Jefe del Comité</h2>
    <table border="1">
        <thead>
            <tr>
                <th>ID Artículo</th>
                <th>Título</th>
                <th>Tópicos</th>
                <th>Autores</th>
                <th>Revisores Asignados</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row["IDArticulo"] ?></td>
                <td><?= htmlspecialchars($row["Titulo"]) ?></td>
                <td><?= $row["Topicos"] ?></td>
                <td><?= $row["Autores"] ?></td>
                <td><?= $row["Revisores"] ?></td>
                <td>
                    <?php if ((int)$row["CantRevisores"] < 3): ?>
                        <a href="assignReviewer.php?id=<?= $row["IDArticulo"] ?>"><button>Asignar Revisor</button></a>
                    <?php endif; ?>
                    <?php if ((int)$row["CantRevisores"] > 0): ?>
                        <a href="deleteArticleReviewer.php?id=<?= $row["IDArticulo"] ?>"><button>Quitar Revisor</button></a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <h5><a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al Menu Principal</button></a></h5>
</body>
</html>

