<?php
session_start();

// Verificar si hay sesión iniciada
if (!isset($_SESSION['user_id'])) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . '/../Logic/databaseConnect.php';
$rut = $_SESSION['user_id'];

// Verificar que el usuario sea revisor
$stmt = $mysqli->prepare("SELECT EsRevisor FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user['EsRevisor']) {
    // No es revisor o no existe el usuario, lo redirigimos fuera
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

// Obtener artículos asignados a este revisor
$sql = "SELECT a.IDArticulo, a.Titulo, a.FechaEnvio, a.Resumen
        FROM articulos a
        JOIN revisiones r ON a.IDArticulo = r.IDArticulo
        WHERE r.RutRev = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$articulos = [];
while ($row = $result->fetch_assoc()) {
    $articulos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Artículos asignados para revisión</title>
</head>
<body>
    <h1>Artículos asignados para revisión</h1>
    <a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al menú principal</button></a>
    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>Título</th>
                <th>Fecha de envío</th>
                <th>Resumen</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($articulos)): ?>
                <tr><td colspan="4">No tienes artículos asignados.</td></tr>
            <?php else: ?>
                <?php foreach ($articulos as $articulo): ?>
                    <tr>
                        <td><?= htmlspecialchars($articulo['Titulo']) ?></td>
                        <td><?= htmlspecialchars($articulo['FechaEnvio']) ?></td>
                        <td><?= htmlspecialchars($articulo['Resumen']) ?></td>
                        <td>
                            <a href="reviewArticleEditDelete.php?id=<?= $articulo['IDArticulo'] ?>">
                                <button>Revisar</button>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
