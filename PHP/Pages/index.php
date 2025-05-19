<?php 
session_start();

if ($_SESSION["user_id"] == NULL) {
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");
    exit();
}

$mysqli = require __DIR__ . "/../Logic/databaseConnect.php";

// Usamos $mysqli, no $conn
$stmt = $mysqli->prepare("SELECT * FROM usuario WHERE Rut = ?");
$stmt->bind_param("s", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Principal</title>
    <link rel="stylesheet" href="/Tarea2-BDD/CSS/index.css">
</head>
<body>
    <div class = "sup">
    <h1>GESCON</h1>
        <div class = "supder">
        <h5><a href="/Tarea2-BDD/PHP/Pages/userInfo.php"><button>Administrar Perfil</button></a> <a href="/Tarea2-BDD/PHP/Pages/logout.php"><button>Cerrar Sesion</button></a></h5>
        </div>
    </div>
    <div class = "planilla">
    <?php echo '<h2>Bienvenido ' . $user["Nombre"] . '<br>Menu de Acciones:<h2>'?>
    <div class = "botones-planilla">
    <?php if ($user["EsAutor"] == true){?>
    <H4><a href="/Tarea2-BDD/PHP/Pages/createArticle.php"><button>Crear y Enviar un Articulo</button></a></H4>
    <H4><a href="/Tarea2-BDD/PHP/Pages/checkArticle.php"><button>Revisar y Editar mis Articlos</button></a></H4>
    <?php }?>
    <?php if ($user["EsRevisor"] == true){?>
        <H4><a href="/Tarea2-BDD/PHP/Pages/reviewArticle.php"><button>Evaluar Articulos Asignados</button></a></H4>
    <?php }?>
    <?php if ($user["Rut"] == "admin"){?>
    <H4><a href="/Tarea2-BDD/PHP/Pages/articleReviewer.php"><button>Asignar Revisores a Articulos</button></a></H4>
    <H4><a href="/Tarea2-BDD/PHP/Pages/reviewerManagement.php"><button>Gestionar Comite de Revisores</button></a></H4>
    <?php }?>
    <a href="/Tarea2-BDD/PHP/Pages/evaluatedArticles.php"><button>Ver Todos los Articulos Evaluados</button></a>
    </div>
    </div>
    <!--Sección Barra de Busqueda-->
    <?php
    $sqlA = "SELECT Rut, Nombre FROM usuario WHERE EsAutor = 1";
    $sqlR = "SELECT Rut, Nombre FROM usuario WHERE EsRevisor = 1";
    $sqlT = "SELECT IDTopico, NombreTopico FROM topicos";
    $Autores = $mysqli->query($sqlA);
    $Revisores = $mysqli->query($sqlR);
    $Topicos = $mysqli->query($sqlT);
    ?>
    <form method="GET" class="barra">
        <h1>Busqueda Avanzada de Articulos</h1>
        <label for="Autores">Selecciona un Autor:</label>
        <select name="autor" id="Autores">
            <option value="">-- Elige un Autor --</option>
            <?php while ($filaA = $Autores->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($filaA["Rut"]) ?>">
                    <?= htmlspecialchars($filaA["Nombre"]) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <label for="Revisores">Selecciona un Revisor:</label>
        <select name="Revisor" id="Revisores">
            <option value="">-- Elige un Revisor --</option>
            <?php while ($filaR = $Revisores->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($filaR["Rut"]) ?>">
                    <?= htmlspecialchars($filaR["Nombre"]) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <label for="fecha">Fecha de Envío:</label>
            <input type="date" name="fecha" id="fecha">
        <br><br>
        <label for="Topicos">Selecciona un Topico:</label>
        <select name="Topico" id="Topicos">
            <option value="">-- Elige un Topico --</option>
            <?php while ($filaT = $Topicos->fetch_assoc()): ?>
                <option value="<?= htmlspecialchars($filaT["IDTopico"]) ?>">
                    <?= htmlspecialchars($filaT["NombreTopico"]) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>
        <input type="text" name="busqueda" placeholder="Buscar..."><br><br>
        <button type="submit" name="boton" value="1">Buscar</button>
    </form>

    <div class="Resultados">
            <?php
            // Capturar los filtros desde el formulario
            $autor = $_GET["autor"] ?? "";
            $revisor = $_GET["Revisor"] ?? "";
            $fecha = $_GET["fecha"] ?? "";
            $topico = $_GET["Topico"] ?? "";
            $busqueda = $_GET["busqueda"] ?? "";

            // Construir consulta SQL base
            $sql = "SELECT DISTINCT a.IDArticulo, a.Titulo, a.FechaEnvio
                    FROM articulos a
                    LEFT JOIN autoresarticulos aa ON a.IDArticulo = aa.IDArticulo
                    LEFT JOIN revisiones r ON a.IDArticulo = r.IDArticulo
                    LEFT JOIN topicosarticulos ta ON a.IDArticulo = ta.IDArticulo
                    WHERE 1=1";

            $params = [];
            $types = "";

            // Filtro: Autor
            if (!empty($autor)) {
                $sql .= " AND aa.RutAut = ?";
                $params[] = $autor;
                $types .= "s";
            }

            // Filtro: Revisor
            if (!empty($revisor)) {
                $sql .= " AND r.RutRev = ?";
                $params[] = $revisor;
                $types .= "s";
            }

            // Filtro: Fecha de envío
            if (!empty($fecha)) {
                $sql .= " AND DATE(a.FechaEnvio) = ?";
                $params[] = $fecha;
                $types .= "s";
            }

            // Filtro: Tópico
            if (!empty($topico)) {
                $sql .= " AND ta.IDTopico = ?";
                $params[] = $topico;
                $types .= "i";
            }

            // Filtro: Búsqueda textual (titulo o resumen)
            if (!empty($busqueda)) {
                $sql .= " AND (a.Titulo LIKE ? OR a.Titulo LIKE ? OR a.Titulo LIKE ? OR a.Resumen LIKE ? OR a.Resumen LIKE ? OR a.Resumen LIKE ?)";
                $like1 = "%$busqueda%";
                $like2 = "$busqueda%";
                $like3 = "%$busqueda";
                $params[] = $like1;
                $params[] = $like2;
                $params[] = $like3;
                $params[] = $like1;
                $params[] = $like2;
                $params[] = $like3;
                $types .= "ssssss";
            }

            // Preparar y ejecutar consulta
            $stmt = $mysqli->prepare($sql);
            if ($stmt === false) {
                die("Error en prepare: " . $mysqli->error);
            }

            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            $resultado = $stmt->get_result();

            // Mostrar resultados
            if ($resultado->num_rows > 0 && isset($_GET["boton"])) {
                echo "<h3>Resultados de la búsqueda:</h3>";
                while ($fila = $resultado->fetch_assoc()) {
                    $idArticulo = $fila["IDArticulo"];
                    $titulo = htmlspecialchars($fila["Titulo"]);
                    $fechaEnvio = htmlspecialchars($fila["FechaEnvio"]);

                    // Obtener autores
                    $autoresQuery = $mysqli->prepare("SELECT u.Nombre FROM usuario u INNER JOIN autoresarticulos aa ON u.Rut = aa.RutAut WHERE aa.IDArticulo = ?");
                    $autoresQuery->bind_param("i", $idArticulo);
                    $autoresQuery->execute();
                    $autoresResult = $autoresQuery->get_result();
                    $autores = [];
                    while ($a = $autoresResult->fetch_assoc()) {
                        $autores[] = htmlspecialchars($a["Nombre"]);
                    }
                    $autoresQuery->close();

                    // Obtener tópicos
                    $topicosQuery = $mysqli->prepare("SELECT t.NombreTopico FROM topicos t INNER JOIN topicosarticulos ta ON t.IDTopico = ta.IDTopico WHERE ta.IDArticulo = ?");
                    $topicosQuery->bind_param("i", $idArticulo);
                    $topicosQuery->execute();
                    $topicosResult = $topicosQuery->get_result();
                    $topicos = [];
                    while ($t = $topicosResult->fetch_assoc()) {
                        $topicos[] = htmlspecialchars($t["NombreTopico"]);
                    }
                    $topicosQuery->close();

                    // Mostrar artículo
                    echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:10px;'>";
                    echo "<strong>Título:</strong> $titulo<br>";
                    echo "<strong>Autores:</strong> " . implode(", ", $autores) . "<br>";
                    echo "<strong>Tópicos:</strong> " . implode(", ", $topicos) . "<br>";
                    echo "<strong>Fecha de envío:</strong> $fechaEnvio";
                    echo "</div>";}
            } else if (isset($_GET["boton"]) && $resultado->num_rows == 0) {
                echo "<p>No se encontraron artículos que coincidan con los filtros seleccionados.</p>";
            } else {}

            $stmt->close();
            ?>

    </div>
</body>
</html> 