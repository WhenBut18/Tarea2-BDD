<?php 
    session_start();
    if ($_SESSION["user_id"] == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }
    $mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
    $sql = "SELECT * FROM usuario WHERE Rut = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
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
    <h1>TETEO</h1>
    <?php echo $user["Nombre"]?>
    <br><br>
    <a href="/Tarea2-BDD/PHP/Pages/logout.php">cierra sesion</a>
</body>
</html>