<?php 
session_start();
if ($_SESSION["user_id"] == NULL) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

$rut = $_SESSION["user_id"];

// Verificar que el usuario es revisor
$stmt = $mysqli->prepare("SELECT * FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !$user["EsRevisor"]) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID de artículo no especificado.";
    exit();
}

$idArticulo = (int)$_GET['id'];

// Verificar que este revisor está asignado a este artículo
$stmt = $mysqli->prepare("SELECT * FROM revisiones WHERE IDArticulo = ? AND RutRev = ?");
$stmt->bind_param("is", $idArticulo, $rut);
$stmt->execute();
$result = $stmt->get_result();
$revision = $result->fetch_assoc();

if (!$revision) {
    echo "No tienes asignado este artículo para revisar.";
    exit();
}

// Inicializar variables para el formulario con valores actuales o vacíos
$calidadTecnica = $revision['CalidadTecnica'] ?? '';
$originalidad = $revision['Originalidad'] ?? '';
$valoracionGlobal = $revision['ValoracionGlobal'] ?? '';
$argumentosValoracion = $revision['ArgumentosValoracion'] ?? '';
$comentariosRevisor = $revision['ComentariosRevisor'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar'])) {
        // Guardar o actualizar la valoración
        $calidadTecnica = $_POST['calidadTecnica'] ?: NULL;
        $originalidad = $_POST['originalidad'] ?: NULL;
        $valoracionGlobal = $_POST['valoracionGlobal'] ?: NULL;
        $argumentosValoracion = $_POST['argumentosValoracion'] ?? '';
        $comentariosRevisor = $_POST['comentariosRevisor'] ?? '';

        $stmt = $mysqli->prepare("UPDATE revisiones SET CalidadTecnica = ?, Originalidad = ?, ValoracionGlobal = ?, ArgumentosValoracion = ?, ComentariosRevisor = ? WHERE IDArticulo = ? AND RutRev = ?");
        $stmt->bind_param("iiissis", 
            $calidadTecnica, 
            $originalidad, 
            $valoracionGlobal, 
            $argumentosValoracion, 
            $comentariosRevisor, 
            $idArticulo, 
            $rut
        );
        $stmt->execute();

        header("Location: reviewArticle.php");
        exit();

    } elseif (isset($_POST['eliminar'])) {
        // Eliminar solo las valoraciones, no la relación
        $stmt = $mysqli->prepare("UPDATE revisiones SET CalidadTecnica = NULL, Originalidad = NULL, ValoracionGlobal = NULL, ArgumentosValoracion = '', ComentariosRevisor = '' WHERE IDArticulo = ? AND RutRev = ?");
        $stmt->bind_param("is", $idArticulo, $rut);
        $stmt->execute();

        // Limpiar variables para mostrar formulario vacío
        $calidadTecnica = '';
        $originalidad = '';
        $valoracionGlobal = '';
        $argumentosValoracion = '';
        $comentariosRevisor = '';
    }
}

// Obtener título del artículo para mostrar
$stmt = $mysqli->prepare("SELECT Titulo FROM articulos WHERE IDArticulo = ?");
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$result = $stmt->get_result();
$articulo = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Revisión del Artículo</title>
</head>
<body>
    <h1>Revisar Artículo: <?= htmlspecialchars($articulo['Titulo']) ?></h1>
    <a href="/Tarea2-BDD/PHP/Pages/reviewArticle.php"><button>Volver</button></a>
    <form method="POST">
        <label>Calidad Técnica (1-10):</label><br>
        <input type="number" name="calidadTecnica" min="1" max="10" value="<?= htmlspecialchars($calidadTecnica) ?>" required><br><br>

        <label>Originalidad (1-10):</label><br>
        <input type="number" name="originalidad" min="1" max="10" value="<?= htmlspecialchars($originalidad) ?>" required><br><br>

        <label>Valoración Global (1-10):</label><br>
        <input type="number" name="valoracionGlobal" min="1" max="10" value="<?= htmlspecialchars($valoracionGlobal) ?>" required><br><br>

        <label>Argumentos de Valoración:</label><br>
        <textarea name="argumentosValoracion" rows="4" cols="50"><?= htmlspecialchars($argumentosValoracion) ?></textarea><br><br>

        <label>Comentarios del Revisor:</label><br>
        <textarea name="comentariosRevisor" rows="4" cols="50"><?= htmlspecialchars($comentariosRevisor) ?></textarea><br><br>

        <button type="submit" name="guardar">Guardar</button>
        <button type="submit" name="eliminar" onclick="return confirm('¿Estás seguro de eliminar las valoraciones?');">Eliminar valoraciones</button>
    </form>
</body>
</html>
