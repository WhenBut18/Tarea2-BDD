<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $passwordLogin = $_POST["passwordLogin"];
    $mailLogin = $_POST["mailLogin"];
    if ($passwordLogin == NULL or $mailLogin == NULL) {
        /* AÑADIR REDIRIGIR AL LOGIN POR FALTA DE CREDENCIALES
        header()
        */
        exit();
    }
    // AÑADIR VALIDACIONES ANTI INYECCIONES DE CODIGO
    // AÑADIR VALIDACIONES DEL TIPO DE DATO EJEMPLO: MANDE UN TEXT EN VEZ DE UN EMAIL YA QUE MODIFICAARON LA MIERDA DE FRONTEND
    
}
/*
Añadir header que redireccione al Index debido a que se ingreso sin el metodo correcto
header()
*/

