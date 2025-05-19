<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Captura de datos del formulario
    $passwordRegister = $_POST["passwordRegister"] ?? null;
    $mailRegister = $_POST["mailRegister"] ?? null;
    $nameRegister = $_POST["nameRegister"] ?? null;
    $rutRegister = $_POST["rutRegister"] ?? null;

    $isAutorRegister = ($_POST["isAutorRegister"] ?? null) === "yes" ? 1 : 0;
    $isRevisorRegister = ($_POST["isRevisorRegister"] ?? null) === "yes" ? 1 : 0;

    // Validación de campos obligatorios
    if (!$passwordRegister || !$mailRegister || !$rutRegister || !$nameRegister) {
        header("Location: /Tarea2-BDD/PHP/Pages/register.php");
        exit();
    }

    // Validacion de Formatos correctos
    if (!preg_match("/^\d{7,8}-[\dkK]$/", $rutRegister) && strlen($passwordRegister)<8){
        echo "REGISTRO INVÁLIDO<br><br>";
        echo"Contraseña debe tener al menos 8 caractéres. RUT ingresado no tiene un formato válido. Debe ser como '12345678-9'.";
        echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php"><button>Volver al registro</button></a>';
        exit();
    } else{
        // Validación de formato RUT
        if (!preg_match("/^\d{7,8}-[\dkK]$/", $rutRegister)) {
            echo "REGISTRO INVÁLIDO<br><br>";
            echo "El RUT ingresado no tiene un formato válido. Debe ser como '12345678-9'.";
            echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php"><button>Volver al registro</button></a>';
            exit();
        }
        if (strlen($passwordRegister)<8) {
            echo "REGISTRO INVÁLIDO<br><br>";
            echo "Su contraseña debe tener al menos 8 caractéres";
            echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php"><button>Volver al registro</button></a>';
            exit();
        }
    }

    // Conexión a la base de datos
    $mysqli = require "databaseConnect.php";

    // Verificación de RUT o correo duplicado
    $sql = "SELECT 1 FROM usuario WHERE Rut = ? OR Correo = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $rutRegister, $mailRegister);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "El Rut o el Correo ya están registrados.";
        echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php"><button>Volver al registro</button></a>';
        exit();
    }

    $stmt->close();

    // Insertar usuario
    $sql = "INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsAutor, EsRevisor)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        die("Error en la preparación: " . $mysqli->error);
    }

    $stmt->bind_param("ssssii", $rutRegister, $nameRegister, $mailRegister, $passwordRegister, $isAutorRegister, $isRevisorRegister);
    $stmt->execute();
    $stmt->close();

    // Insertar tópicos si es revisor
    if ($isRevisorRegister && isset($_POST["topicosSeleccionados"])) {
        $topicosSeleccionados = $_POST["topicosSeleccionados"];
        foreach ($topicosSeleccionados as $idTopico) {
            $sql = "INSERT INTO topicosRevisores (IDTopico, RutRev) VALUES (?, ?)";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("is", $idTopico, $rutRegister);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Redirigir tras registro exitoso
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}
