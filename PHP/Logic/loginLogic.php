<?php

// Revisa si se accedio con el metodo POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Registra en variables los valores recibidos por el formulario
    $passwordLogin = $_POST["passwordLogin"];
    $rutLogin = $_POST["rutLogin"];
    if ($passwordLogin == NULL or $rutLogin == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }
    // AÑADIR VALIDACIONES ANTI INYECCIONES DE CODIGO
    // AÑADIR VALIDACIONES DEL TIPO DE DATO EJEMPLO: MANDE UN TEXT EN VEZ DE UN EMAIL YA QUE MODIFICAARON LA MIERDA DE FRONTEND

    // Revisa que las credenciales coincidan retonando un True si lo son, y un False si no.
    $mysqli = require "databaseConnect.php";
    $sql = "SELECT 
                CASE 
                    WHEN EXISTS (
                        SELECT 1 
                        FROM usuario 
                        WHERE Rut = ? AND Contraseña = ?
                    )
                    THEN TRUE 
                    ELSE FALSE 
                END AS login_valido;";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("SQL error: " . $mysqli->error);
    }
    $stmt->bind_param("ss", $rutLogin, $passwordLogin);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        $row = $result->fetch_assoc();
        $login_valido = $row['login_valido'];

        if ($login_valido) {
            echo "Login exitoso.";
        } else {
            echo "Rut o contraseña incorrectos.";
        }
    } else {
        echo "Error al obtener resultados.";
    }

    $stmt->close();

}
/*
Añadir header que redireccione al Index debido a que se ingreso sin el metodo correcto
header()
*/

