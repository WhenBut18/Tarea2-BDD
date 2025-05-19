<?php
session_start();

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

$placeholders = implode(',', array_fill(0, count($idTopicos), '?'));

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

function refValues($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}

$stmt_params = array_merge([$types], $params);
call_user_func_array([$stmt, 'bind_param'], refValues($stmt_params));
$stmt->execute();
$result = $stmt->get_result();

$asignados = 0;

while ($row = $result->fetch_assoc()) {
    if ($asignados >= 3) break;

    $rutRevisor = $row["Rut"];
    $sqlInsert = "INSERT INTO revisiones (IDArticulo, RutRev) VALUES (?, ?)";
    $stmtInsert = $mysqli->prepare($sqlInsert);
    $stmtInsert->bind_param("is", $idArticulo, $rutRevisor);
    $stmtInsert->execute();
    $asignados++;
}

if ($asignados > 0) {
    header("Location: articleReviewer.php?success=1");
    exit();
} else {
    echo '<a href="/Tarea2-BDD/PHP/Pages/articleReviewer.php"><button>Volver</button></a><br>';
    echo "No hay revisores disponibles para asignar automáticamente.";
}
?>
