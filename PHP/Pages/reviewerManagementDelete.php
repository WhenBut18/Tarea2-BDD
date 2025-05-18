<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

if (!isset($_GET["rut"])) {
    echo "Revisor no especificado.";
    exit();
}

$rut = $_GET["rut"];

// Verificar si tiene revisiones asignadas
$stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM revisiones WHERE RutRev = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($result["total"] > 0) {
    echo "<p>No se puede eliminar el revisor porque tiene artículos asignados para revisión.</p>";
    echo '<p><a href="reviewerManagement.php.php"><button>Volver a la gestión de revisores</button></a></p>';
    exit();
}

// Eliminar tópicos del revisor
$stmt = $mysqli->prepare("DELETE FROM topicosRevisores WHERE RutRev = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$stmt->close();

// Eliminar usuario
$stmt = $mysqli->prepare("DELETE FROM usuario WHERE Rut = ? AND EsRevisor = 1");
$stmt->bind_param("s", $rut);
if ($stmt->execute()) {
    echo "<p>Revisor eliminado correctamente.</p>";
} else {
    echo "<p>Error al eliminar revisor.</p>";
}
$stmt->close();

echo '<p><a href="reviewerManagement.php"><button>Volver a la gestión de revisores</button></a></p>';
?>
