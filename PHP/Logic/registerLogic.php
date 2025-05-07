<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $passwordRegister = $_POST["passwordRegister"];
    $mailRegister = $_POST["mailRegister"];
    $nameRegister = $_POST["nameRegister"];
    $rutRegister = $_POST["rutRegister"];
    // AÑADIR EL ROL DEL USUARIO
    if ($passwordRegister == NULL or $mailRegister == NULL) {
        /* AÑADIR REDIRIGIR AL LOGIN POR FALTA DE CREDENCIALES
        header()
        */
        exit();
    }
    // AÑADIR VALIDACIONES ANTI INYECCIONES DE CODIGO
    // AÑADIR VALIDACIONES DEL TIPO DE DATO EJEMPLO: MANDE UN TEXT EN VEZ DE UN EMAIL YA QUE MODIFICAARON LA MIERDA DE FRONTEND
    // AÑADIR VERIFICACION DEL RUT / CORREO NO SE REPITAN EN DB

    //Esta seccion del codigo se encarga de llamar a la Base de Datos para insertar los nuevos datos de Register
    // REFORMAR CODIGO PARA AÑADIR EL ROL O CAMBIAR EL TIPO DE INSERT (AUTOR O REVISOR)
    $mysqli = require "databaseConnect.php";
    $sql = "INSERT INTO autores (RutAut, Nombre, Correo, Contraseña)
            VALUES(?,?,?,?)";

    $stmt = $mysqli->stmt_init();
    if ( ! $stmt->prepare($sql)){
        die("SQL error: " . $mysqli->error);
    }
    $stmt->bind_param("ssss", $rutRegister, $nameRegister, $mailRegister, $passwordRegister);
    $stmt->execute();
    echo "Registor exitosos<br><br>";

    // Codigo Auxiliar para imprimir todos los autores registrados
    $sql = "SELECT * FROM Autores";
    $result = $mysqli->query($sql);
    while ($row = $result->fetch_assoc()) {
        echo "RutAut: " . $row["RutAut"] . "<br>";
        echo "Nombre: " . $row["Nombre"] . "<br>";
        echo "Contraseña: " . $row["Contraseña"] . "<br>";
        echo "Correo: " . $row["Correo"] . "<br><br>";
    }

    



}
/*
Añadir header que redireccione al Index debido a que se ingreso sin el metodo correcto
header()
*/