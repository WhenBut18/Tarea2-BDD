<?php 
    session_start();
    if ($_SESSION["user_id"] == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }
    $mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>