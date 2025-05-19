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
    <title>Document</title>
</head>
<body>
<!-- Añadir boton de cerrar sesion 
    Botones de Secciones/funciones de la pagina web
    Desplegar los Botones/Seccioned de la pagina web dependiendo del tipo de usuario Autor/Revisor/JefeRevisor
-->
    <h1>GESCON</h1>
    <!-- Añadir link a seccion de administrar perfil -->
    <h5><a href="/Tarea2-BDD/PHP/Pages/userInfo.php"><button>Administrar Perfil</button></a> <a href="/Tarea2-BDD/PHP/Pages/logout.php"><button>Cerrar Sesion</button></a></h5>

    <?php echo '<h2>Bienvenido ' . $user["Nombre"] . '<br>Menu de Acciones:<h2>'?>

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
</body>
</html> 