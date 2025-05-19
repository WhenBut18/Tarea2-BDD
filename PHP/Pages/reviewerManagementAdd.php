<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["user_id"] !== "admin") {
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
$errores = [];
$exito = false;

// Obtener los tópicos disponibles
$topicos = $mysqli->query("SELECT IDTopico, NombreTopico FROM topicos");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rut = trim($_POST["rut"]);
    $nombre = trim($_POST["nombre"]);
    $correo = trim($_POST["correo"]);
    $password = trim($_POST["password"]);
    $topicosSeleccionados = $_POST["topicos"] ?? [];

    // Validaciones
    if (empty($rut) || empty($nombre) || empty($correo) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    if (count($topicosSeleccionados) < 1) {
        $errores[] = "Debes seleccionar al menos un tópico.";
    }

    // Verificar duplicados por nombre y correo
    $stmt = $mysqli->prepare("SELECT COUNT(*) as total FROM usuario WHERE Nombre = ? AND Correo = ?");
    $stmt->bind_param("ss", $nombre, $correo);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res["total"] > 0) {
        $errores[] = "Ya existe un revisor con el mismo nombre y correo.";
    }

    // Insertar si no hay errores
    if (empty($errores)) {
        $stmt = $mysqli->prepare("INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsRevisor) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $rut, $nombre, $correo, $password);
        if ($stmt->execute()) {
            // Insertar los tópicos
            $stmtTopico = $mysqli->prepare("INSERT INTO topicosRevisores (IDTopico, RutRev) VALUES (?, ?)");
            foreach ($topicosSeleccionados as $idTopico) {
                $stmtTopico->bind_param("is", $idTopico, $rut);
                $stmtTopico->execute();
            }
            $stmtTopico->close();
            $exito = true;
        } else {
            $errores[] = "Error al insertar el revisor.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Añadir Revisor</title>
</head>
<body>
<h1>Añadir Nuevo Revisor</h1>

<?php if ($exito): ?>
    <p style="color: green;">Revisor añadido exitosamente.</p>
<?php endif; ?>

<?php if (!empty($errores)): ?>
    <ul style="color: red;">
        <?php foreach ($errores as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post">
    <label>RUT: <input type="text" name="rut" required></label><br><br>
    <label>Nombre: <input type="text" name="nombre" required></label><br><br>
    <label>Correo: <input type="email" name="correo" required></label><br><br>
    <label>Contraseña: <input type="text" name="password" required></label><br><br>

    <label>Tópicos de Especialidad:</label><br>
    <?php while ($topico = $topicos->fetch_assoc()): ?>
        <label>
            <input type="checkbox" name="topicos[]" value="<?= $topico["IDTopico"] ?>">
            <?= htmlspecialchars($topico["NombreTopico"]) ?>
        </label><br>
    <?php endwhile; ?>
    <br>
    <button type="submit">Añadir Revisor</button>
</form>

<p><a href="reviewerManagement.php"><button>Volver a la gestión de revisores</button></a></p>

</body>
</html>
