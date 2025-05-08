<?php

// Revisa si se accedio con el metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Registra en variables los valores recibidos por el formulario
    $passwordRegister = $_POST["passwordRegister"];
    $mailRegister = $_POST["mailRegister"];
    $nameRegister = $_POST["nameRegister"];
    $rutRegister = $_POST["rutRegister"];
    if ($_POST["isAutorRegister"] == "yes"){
        $isAutorRegister = 1;
    } else {
        $isAutorRegister = 0;
    }
    if ($_POST["isReviserRegister"] == "yes"){
        $isRevisorRegister = 1;
    } else {
        $isRevisorRegister = 0;
    }

    // Revisa si algun dato del formulacion esta NULL
    if ($passwordRegister == NULL or $mailRegister == NULL or $rutRegister == NULL or $nameRegister == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/register.php");
        exit();
    }
    // AÑADIR VALIDACIONES ANTI INYECCIONES DE CODIGO
    // AÑADIR VALIDACIONES DEL TIPO DE DATO EJEMPLO: MANDE UN TEXT EN VEZ DE UN EMAIL YA QUE MODIFICAARON LA MIERDA DE FRONTEND
    // AÑADIR REQUISITOS DE TAMAÑO DE RUT Y CONTRASEÑA


    $mysqli = require "databaseConnect.php";
    
    // Aqui se realiza una query a la DB para revisar si el Rut y/o el Correo ya se usaron.
    $sql = "SELECT CASE WHEN EXISTS (
                SELECT 1 
                FROM usuario 
                WHERE Rut = ? OR Correo = ?
            ) THEN TRUE ELSE FALSE END AS existe_usuario;";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $rutRegister, $mailRegister);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Si no existe el usuario realizar el insert en la DB
    if ($row['existe_usuario']) {
        echo ("El Rut o el Correo ya están registrados.");
        echo '<a href="/Tarea2-BDD/PHP/Pages/register.php">Intentar denuevo</a>';
        exit();
    } else {

        // Codigo para insertar el usuario a la DB
        $sql = "INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsAutor, EsRevisor)
            VALUES(?,?,?,?,?,?)";
        $stmt = $mysqli->stmt_init();
        if ( ! $stmt->prepare($sql)){
            die("SQL error: " . $mysqli->error);
        }
        $stmt->bind_param("ssssii", $rutRegister, $nameRegister, $mailRegister, $passwordRegister,$isAutorRegister,$isRevisorRegister);
        $stmt->execute();
        $stmt->close();
    }
    // Codigo Auxiliar para imprimir todos los autores registrados BORRAR DESPUES
    $sql = "SELECT * FROM usuario";
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "Rut: " . $row["Rut"] . "<br>";
        echo "Nombre: " . $row["Nombre"] . "<br>";
        echo "Contraseña: " . $row["Contraseña"] . "<br>";
        echo "Correo: " . $row["Correo"] . "<br>";
        echo "Es Autor: " . $row["EsAutor"] . "<br>";
        echo "Es Revisor: " . $row["EsRevisor"] . "<br><br>";
    }

}
header("Location: /Tarea2-BDD/PHP/Pages/index.php");