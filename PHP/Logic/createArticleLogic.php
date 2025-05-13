<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Datos del formulario
    $title = $_POST["titleSubmit"];
    $summary = $_POST["summarySubmit"];
    $autores = $_POST["seleccionarAutor"];      // array
    $contacto = $_POST["autorContacto"];        // string
    $topicos = $_POST["seleccionarTopico"];     // array

    // Validaciones simples
    if (!in_array($contacto, $autores)) {
        die("Error: el autor de contacto no está en la lista de autores.");
    }

    // Convertir arrays en strings separados por comas
    $autoresStr = implode(",", $autores);
    $topicosStr = implode(",", $topicos);

    // Conexión
    $mysqli = require "databaseConnect.php";

    // Llamada al procedimiento
    $stmt = $mysqli->prepare("CALL crear_articulo(?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $summary, $autoresStr, $topicosStr, $contacto);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
    echo "✅ Artículo registrado correctamente.";
    echo '<h4><a href="/Tarea2-BDD/PHP/Pages/createArticle.php">Volver a Crear Articulo</a></h4>';
    exit();
}
header("Location: /Tarea2-BDD/PHP/Pages/index.php");
