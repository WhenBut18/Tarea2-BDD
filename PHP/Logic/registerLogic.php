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
    // AÑADIR VERIFICACION DEL RUT / CORREO NO SE REPITAN EN DB

    //Esta seccion del codigo se encarga de llamar a la Base de Datos para insertar los nuevos datos de Register
    // REFORMAR CODIGO PARA AÑADIR EL ROL O CAMBIAR EL TIPO DE INSERT (AUTOR O REVISOR)
    $mysqli = require "databaseConnect.php";
    $sql = "INSERT INTO usuario (RutAut, Nombre, Correo, Contraseña, EsAutor, EsRevisor)
            VALUES(?,?,?,?,?,?)";

    $stmt = $mysqli->stmt_init();
    if ( ! $stmt->prepare($sql)){
        die("SQL error: " . $mysqli->error);
    }
    $stmt->bind_param("ssssii", $rutRegister, $nameRegister, $mailRegister, $passwordRegister,$isAutorRegister,$isRevisorRegister);
    $stmt->execute();
    echo "Registor exitosos<br><br>";

    // Codigo Auxiliar para imprimir todos los autores registrados BORRAR DESPUES
    $sql = "SELECT * FROM usuario";
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "RutAut: " . $row["RutAut"] . "<br>";
        echo "Nombre: " . $row["Nombre"] . "<br>";
        echo "Contraseña: " . $row["Contraseña"] . "<br>";
        echo "Correo: " . $row["Correo"] . "<br>";
        echo "Es Autor: " . $row["EsAutor"] . "<br>";
        echo "Es Revisor: " . $row["EsRevisor"] . "<br><br>";
    }
    
}
/*
Añadir header que redireccione al Index debido a que se ingreso sin el metodo correcto
header()
*/