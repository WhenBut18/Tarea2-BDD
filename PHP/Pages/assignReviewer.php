<?php
session_start();

// Verificar si el usuario es ADMIN
if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

if (!isset($_GET["id"])) {
    echo "No se especificó el artículo.";
    exit();
}

$idArticulo = intval($_GET["id"]);

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Obtener los tópicos del artículo
$sqlTopicos = "SELECT IDTopico FROM topicosArticulos WHERE IDArticulo = ?";
$stmt = $mysqli->prepare($sqlTopicos);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();

$idTopicos = [];
while ($row = $result->fetch_assoc()) {
    $idTopicos[] = $row["IDTopico"];
}

if (count($idTopicos) === 0) {
    echo "El artículo no tiene tópicos asignados.";
    exit();
}

// Preparar placeholders para IN (...)
$placeholders = implode(',', array_fill(0, count($idTopicos), '?'));

// Preparamos la consulta para obtener revisores válidos según la vista
$sqlRevisores = "
    SELECT vr.*
    FROM vista_revisores_info vr
    JOIN topicosRevisores tr ON vr.Rut = tr.RutRev
    WHERE tr.IDTopico IN ($placeholders)
      AND vr.Rut NOT IN (
          SELECT RutAut FROM autoresArticulos WHERE IDArticulo = ?
      )
      AND vr.Rut NOT IN (
          SELECT RutRev FROM revisiones WHERE IDArticulo = ?
      )
      AND vr.TotalAsignados < 3
    GROUP BY vr.Rut
    ORDER BY vr.TotalAsignados ASC, vr.Nombre ASC
";

$stmt = $mysqli->prepare($sqlRevisores);

$types = str_repeat("i", count($idTopicos)) . "ii";
$params = array_merge($idTopicos, [$idArticulo, $idArticulo]);

// bind_param requiere referencias, así que usamos esta función auxiliar
function refValues($arr){
    $refs = [];
    foreach($arr as $key => $value){
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

$stmt_params = array_merge([$types], $params);
call_user_func_array([$stmt, 'bind_param'], refValues($stmt_params));

$stmt->execute();
$revisores = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Asignar Revisor al Artículo #<?= $idArticulo ?></title>
</head>
<body>
<h2>Asignar Revisor al Artículo #<?= $idArticulo ?></h2>

<?php if ($revisores->num_rows === 0): ?>
    <p>No hay revisores disponibles que cumplan con los requisitos.</p>
<?php else: ?>
    <table border="1">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Especialidades (Tópicos)</th>
                <th>Artículos Asignados</th>
                <th>Total Revisiones Asignadas</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($rev = $revisores->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($rev["Nombre"]) ?></td>
                    <td><?= htmlspecialchars($rev["Especialidades"]) ?></td>
                    <td><?= htmlspecialchars($rev["ArticulosAsignados"] ?? "Ninguno") ?></td>
                    <td><?= $rev["TotalAsignados"] ?></td>
                    <td>
                        <form method="post" action="../Logic/assignReviewerLogic.php">
                            <input type="hidden" name="idArticulo" value="<?= $idArticulo ?>">
                            <input type="hidden" name="rutRevisor" value="<?= htmlspecialchars($rev["Rut"]) ?>">
                            <button type="submit">Asignar Revisor</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<p><a href="articleReviewer.php"><button>Volver</button></a></p>
</body>
</html>
