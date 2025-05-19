<?php
session_start();

// Verificar que el usuario sea el Jefe del Comité (RUT ADMIN)
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Obtener todos los revisores
$sql = "
    SELECT u.Rut, u.Nombre, u.Correo, GROUP_CONCAT(t.NombreTopico ORDER BY t.NombreTopico SEPARATOR ', ') AS Topicos
    FROM usuario u
    LEFT JOIN topicosRevisores tr ON u.Rut = tr.RutRev
    LEFT JOIN topicos t ON tr.IDTopico = t.IDTopico
    WHERE u.EsRevisor = 1
    GROUP BY u.Rut, u.Nombre, u.Correo
    ORDER BY u.Nombre ASC
";

$result = $mysqli->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestión de Revisores</title>
</head>
<body>
    <h1>Gestión de Revisores</h1>
    <h5><a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al Menu Principal</button></a></h5>
    <table border="1" cellpadding="8">
        <tr>
            <th>RUT</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Tópicos</th>
            <th>Acciones</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["Rut"]) ?></td>
                <td><?= htmlspecialchars($row["Nombre"]) ?></td>
                <td><?= htmlspecialchars($row["Correo"]) ?></td>
                <td><?= nl2br(htmlspecialchars($row["Topicos"])) ?></td>
                <td>
                    <a href="reviewerManagementEdit.php?rut=<?= $row["Rut"] ?>"><button>Editar</button></a>
                    <?php
                        // Verificar si tiene artículos asignados
                        $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM revisiones WHERE RutRev = ?");
                        $stmt->bind_param("s", $row["Rut"]);
                        $stmt->execute();
                        $countResult = $stmt->get_result()->fetch_assoc();
                        $stmt->close();
                        if ($countResult["total"] == 0):
                    ?>
                        <a href="reviewerManagementDelete.php?rut=<?= $row["Rut"] ?>" onclick="return confirm('¿Eliminar este revisor?')"><button>Eliminar</button></a>
                    <?php else: ?>
                        (No se puede eliminar)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="reviewerManagementAdd.php"><button>Añadir nuevo revisor</button></a>
    
</body>
</html>
