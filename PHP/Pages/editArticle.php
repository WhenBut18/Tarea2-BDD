<?php 
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] == NULL) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

$rut = $_SESSION["user_id"];

// Verificar que el usuario es autor
$stmt = $mysqli->prepare("SELECT EsAutor FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user["EsAutor"] == false) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

// Obtener ID del artículo a editar por GET
if (!isset($_GET["id"])) {
    die("ID de artículo no especificado.");
}

$idArticulo = $_GET["id"];

// Obtener datos del artículo y sus autores para este usuario
$sql = "SELECT * FROM vista_articulos_autor WHERE IDArticulo = ? AND RutAut = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("is", $idArticulo, $rut);
$stmt->execute();
$result = $stmt->get_result();

$articulo = null;
$autores = [];
while ($row = $result->fetch_assoc()) {
    if ($articulo === null) {
        $articulo = [
            "IDArticulo" => $row["IDArticulo"],
            "Titulo" => $row["Titulo"],
            "FechaEnvio" => $row["FechaEnvio"],
            "Resumen" => $row["Resumen"],
            "EnRevision" => $row["EnRevision"]
        ];
    }
    $autores[] = [
        "RutAut" => $row["RutAut"],
        "EsContacto" => $row["EsContacto"]
    ];
}

if ($articulo === null) {
    die("Artículo no encontrado o no tienes permiso para editarlo.");
}

// Obtener todos los usuarios que son autores para listarlos como opciones
$sqlAutores = "SELECT Rut, Nombre FROM usuario WHERE EsAutor = TRUE ORDER BY Nombre";
$resultAutores = $mysqli->query($sqlAutores);
$listaAutores = [];
while ($fila = $resultAutores->fetch_assoc()) {
    $listaAutores[] = $fila;
}

// Obtener tópicos asociados al artículo
$sqlTopicos = "SELECT t.IDTopico, t.NombreTopico FROM topicos t
               JOIN topicosArticulos ta ON t.IDTopico = ta.IDTopico
               WHERE ta.IDArticulo = ?";
$stmt = $mysqli->prepare($sqlTopicos);
$stmt->bind_param("i", $idArticulo);
$stmt->execute();
$resultTopicos = $stmt->get_result();
$topicosSeleccionados = [];
while ($row = $resultTopicos->fetch_assoc()) {
    $topicosSeleccionados[] = $row["IDTopico"];
}

// Obtener todos los tópicos disponibles
$sqlTodosTopicos = "SELECT IDTopico, NombreTopico FROM topicos ORDER BY NombreTopico";
$resultTodosTopicos = $mysqli->query($sqlTodosTopicos);
$listaTopicos = [];
while ($fila = $resultTodosTopicos->fetch_assoc()) {
    $listaTopicos[] = $fila;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Artículo</title>
</head>
<body>

<h2>Editar Artículo: <?= htmlspecialchars($articulo['Titulo']) ?></h2>
<a href="/Tarea2-BDD/PHP/Pages/checkArticle.php"><button>Volver</button></a>
<form action="..\Logic\updateArticle.php" method="post">
    <input type="hidden" name="idArticulo" value="<?= $articulo['IDArticulo'] ?>">

    <label for="titulo">Título:</label><br>
    <input type="text" id="titulo" name="titulo" maxlength="50" required
           value="<?= htmlspecialchars($articulo['Titulo']) ?>"><br><br>

    <label for="resumen">Resumen:</label><br>
    <textarea id="resumen" name="resumen" maxlength="150" required rows="4" cols="50"><?= htmlspecialchars($articulo['Resumen']) ?></textarea><br><br>

    <!-- Tabla Autores -->
    <table border="1" cellpadding="8">
        <tr>
            <th>Nombre</th>
            <th>RUT</th>
            <th>Seleccionar Autor</th>
        </tr>
        <?php foreach ($listaAutores as $index => $autor): 
            $esSeleccionado = false;
            $esContacto = false;
            foreach ($autores as $a) {
                if ($a["RutAut"] === $autor["Rut"]) {
                    $esSeleccionado = true;
                    $esContacto = $a["EsContacto"] == 1;
                    break;
                }
            }
        ?>
        <tr>
            <td><?= htmlspecialchars($autor['Nombre']) ?></td>
            <td><?= htmlspecialchars($autor['Rut']) ?></td>
            <td>
                <input type="checkbox" 
                    name="autores[]" 
                    value="<?= $autor['Rut'] ?>" 
                    id="autor-<?= $index ?>" 
                    <?= $esSeleccionado ? 'checked' : '' ?>
                    <?= $esContacto ? 'disabled' : '' ?>>

                <?php if ($esContacto): ?>
                    <input type="hidden" name="autores[]" value="<?= $autor['Rut'] ?>">
                    <em>Autor de contacto</em>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>

    <!-- Tabla Tópicos -->
    <table border="1" cellpadding="8">
        <tr>
            <th>Tópico</th>
            <th>Seleccionar Tópico</th>
        </tr>
        <?php foreach ($listaTopicos as $index => $topico): ?>
        <tr>
            <td><?= htmlspecialchars($topico['NombreTopico']) ?></td>
            <td>
                <input type="checkbox" 
                       name="topicos[]" 
                       value="<?= $topico['IDTopico'] ?>" 
                       id="topico-<?= $index ?>"
                       <?= in_array($topico['IDTopico'], $topicosSeleccionados) ? 'checked' : '' ?>>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <br>
    <button type="submit">Guardar Cambios</button>
</form>

</body>
</html>
