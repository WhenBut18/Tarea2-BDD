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

    // Conexión
    $mysqli = require "databaseConnect.php";

    // Validar que ningún autor esté en otro artículo con el mismo título
    $placeholders = implode(',', array_fill(0, count($autores), '?'));
    $types = str_repeat('s', count($autores) + 1); // 's' para cada autor + título
    $sql = "
        SELECT DISTINCT da.RutAut 
        FROM articulos a
        JOIN autoresArticulos da ON a.IDArticulo = da.IDArticulo
        WHERE a.Titulo = ? AND da.RutAut IN ($placeholders)
    ";
    $stmt = $mysqli->prepare($sql);
    
    // Construir parámetros dinámicamente
    $params = array_merge([$title], $autores);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $autoresRepetidos = [];
        while ($row = $result->fetch_assoc()) {
            $autoresRepetidos[] = $row["RutAut"];
        }
        echo "Error: los siguientes autores ya están participando en un artículo llamado '$title':<br>";
        echo implode(", ", $autoresRepetidos);
        echo '<br><a href="/Tarea2-BDD/PHP/Pages/createArticle.php"><button>Volver</button></a>';
        exit();
    }

    // Convertir arrays en strings separados por comas
    $autoresStr = implode(",", $autores);
    $topicosStr = implode(",", $topicos);

    // Llamada al procedimiento
    $stmt = $mysqli->prepare("CALL crear_articulo(?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $summary, $autoresStr, $topicosStr, $contacto);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();

    echo "Artículo registrado correctamente.";
    echo '<h4><a href="/Tarea2-BDD/PHP/Pages/createArticle.php"><button>Volver a Crear Articulo</button></a></h4>';
    exit();
}

header("Location: /Tarea2-BDD/PHP/Pages/index.php");
