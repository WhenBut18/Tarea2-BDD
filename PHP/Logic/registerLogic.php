<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $passwordRegister = $_POST["passwordRegister"];
    $mailRegister = $_POST["mailRegister"];
    $nameRegister = $_POST["nameRegister"];
    $rutRegister = $_POST["rutRegister"];
    if ($passwordRegister == NULL or $mailRegister == NULL) {
        /* AÑADIR REDIRIGIR AL LOGIN POR FALTA DE CREDENCIALES
        header()
        */
        exit();
    }
    // AÑADIR VALIDACIONES ANTI INYECCIONES DE CODIGO
    // AÑADIR VALIDACIONES DEL TIPO DE DATO EJEMPLO: MANDE UN TEXT EN VEZ DE UN EMAIL YA QUE MODIFICAARON LA MIERDA DE FRONTEND
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