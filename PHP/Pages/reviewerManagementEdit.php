<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
$errores = [];
$exito = false;

// Validar que se pase el RUT del revisor a editar
if (!isset($_GET["rut"])) {
    echo "Revisor no especificado.";
    exit();
}

$rut = $_GET["rut"];

// Obtener datos del revisor
$stmt = $mysqli->prepare("SELECT * FROM usuario WHERE Rut = ? AND EsRevisor = 1");
$stmt->bind_param("s", $rut);
$stmt->execute();
$revisor = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$revisor) {
    echo "Revisor no encontrado.";
    exit();
}

// Obtener tópicos disponibles
$topicosDisponibles = $mysqli->query("SELECT IDTopico, NombreTopico FROM topicos");

// Obtener tópicos actuales del revisor
$stmt = $mysqli->prepare("SELECT IDTopico FROM topicosRevisores WHERE RutRev = ?");
$stmt->bind_param("s", $rut);
$stmt->execute();
$resultTopicos = $stmt->get_result();
$topicosActuales = [];
while ($row = $resultTopicos->fetch_assoc()) {
    $topicosActuales[] = $row["IDTopico"];
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    $topicosSeleccionados = $_POST["topicos"] ?? [];

    if (empty($nombre) || empty($correo) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    if (count($topicosSeleccionados) < 1) {
        $errores[] = "Debes seleccionar al menos un tópico.";
    }

    // Validar que no se repita nombre y correo con otro revisor
    $stmt = $mysqli->prepare("SELECT COUNT(*) AS total FROM usuario WHERE Nombre = ? AND Correo = ? AND Rut != ?");
    $stmt->bind_param("sss", $nombre, $correo, $rut);
    $stmt->execute();
    $dup = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($dup["total"] > 0) {
        $errores[] = "Otro revisor ya tiene el mismo nombre y correo.";
    }

    if (empty($errores)) {
        // Actualizar datos
        $stmt = $mysqli->prepare("UPDATE usuario SET Nombre = ?, Correo = ?, Contraseña = ? WHERE Rut = ?");
        $stmt->bind_param("ssss", $nombre, $correo, $password, $rut);
        if ($stmt->execute()) {
            $stmt->close();

            // Actualizar tópicos: eliminar todos y volver a insertar los seleccionados
            $mysqli->query("DELETE FROM topicosRevisores WHERE RutRev = '$rut'");

            $stmtTopico = $mysqli->prepare("INSERT INTO topicosRevisores (IDTopico, RutRev) VALUES (?, ?)");
            foreach ($topicosSeleccionados as $idTopico) {
                $stmtTopico->bind_param("is", $idTopico, $rut);
                $stmtTopico->execute();
            }
            $stmtTopico->close();

            $exito = true;
            // Recargar tópicos actuales para mostrarlos en el formulario
            $topicosActuales = $topicosSeleccionados;
        } else {
            $errores[] = "Error al actualizar los datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Editar Revisor</title>
</head>
<body>
<h1>Editar Revisor (<?= htmlspecialchars($rut) ?>)</h1>

<?php if ($exito): ?>
    <p style="color: green;">Datos actualizados correctamente.</p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul style="color: red;">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <label>Nombre: <input type="text" name="nombre" value="<?= htmlspecialchars($revisor["Nombre"]) ?>" required></label><br><br>
    <label>Correo: <input type="email" name="correo" value="<?= htmlspecialchars($revisor["Correo"]) ?>" required></label><br><br>
    <label>Contraseña: <input type="text" name="password" value="<?= htmlspecialchars($revisor["Contraseña"]) ?>" required></label><br><br>

    <label>Tópicos de Especialidad:</label><br>
    <?php while ($topico = $topicosDisponibles->fetch_assoc()): ?>
        <label>
            <input type="checkbox" name="topicos[]" value="<?= $topico["IDTopico"] ?>"
                <?= in_array($topico["IDTopico"], $topicosActuales) ? "checked" : "" ?>>
            <?= htmlspecialchars($topico["NombreTopico"]) ?>
        </label><br>
    <?php endwhile; ?>
    <br>

    <button type="submit">Guardar Cambios</button>
</form>

<p><a href="reviewerManagement.php"><button>Volver a la gestión de revisores</button></a></p>

</body>
</html>
