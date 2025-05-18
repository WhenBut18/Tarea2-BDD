<?php 
    session_start();
    if ($_SESSION["user_id"] == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }
    $mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
    $rut = $_SESSION["user_id"];
    $stmt = $mysqli->prepare("SELECT * FROM usuario WHERE Rut = ?");
    $stmt->bind_param("s", $rut);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user["EsAutor"] == false ){
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }  
    $sql = "SELECT * FROM vista_articulos_autor WHERE RutAut = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $rut);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head><title>Mis Artículos</title></head>
<body>
    <h5><a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al Menu Principal</button></a></h5>
    <h2>Mis Artículos</h2>
    <table border="1">
        <tr>
            <th>ID</th><th>Título</th><th>Fecha Envío</th><th>Resumen</th><th>Acciones</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row["IDArticulo"] ?></td>
                <td><?= htmlspecialchars($row["Titulo"]) ?></td>
                <td><?= $row["FechaEnvio"] ?></td>
                <td><?= htmlspecialchars($row["Resumen"]) ?></td>
                <td>
                    <?php if ($row["EsContacto"]): ?>
                        <?php if (!$row["EnRevision"]): ?>
                            <a href="editArticle.php?id=<?= $row["IDArticulo"] ?>"><button>Editar</button></a> | 
                            <a href="deleteArticle.php?id=<?= $row["IDArticulo"] ?>" onclick="return confirm('¿Estás seguro de eliminar este artículo?')"><button>Eliminar</button></a>
                        <?php else: ?>
                            En revisión (no editable)
                        <?php endif; ?>
                    <?php else: ?>
                        (No autorizado)
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
