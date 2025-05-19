<?php
session_start();

// Verificar si hay sesión iniciada
if (!isset($_SESSION['user_id'])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . '/../Logic/databaseConnect.php';
$rut = $_SESSION['user_id'];

// Verificar que el usuario sea autor o revisor
$stmt = $mysqli->prepare("SELECT EsAutor, EsRevisor FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Obtener artículos completamente evaluados
$sql = "SELECT * FROM vista_articulos_evaluados ORDER BY IDArticulo DESC";
$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Artículos Evaluados</title>
</head>
<body>
    <h1>Artículos Completamente Evaluados</h1>
    <a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al menú principal</button></a>
    <br><br>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Resumen</th>
            <th>Tópicos</th>
            <th>Autores</th>
        </tr>
        <?php if ($result->num_rows === 0): ?>
            <tr><td colspan="6">No hay artículos evaluados completamente aún.</td></tr>
        <?php else: ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row["IDArticulo"] ?></td>
                    <td><?= htmlspecialchars($row["Titulo"]) ?></td>
                    <td><?= htmlspecialchars($row["Resumen"]) ?></td>
                    <td><?= nl2br(htmlspecialchars($row["Topicos"])) ?></td>
                    <td><?= nl2br(htmlspecialchars($row["Autores"])) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
</body>
</html>
