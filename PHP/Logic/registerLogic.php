<?php

// Revisa si se accedió con el método POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Registra en variables los valores recibidos por el formulario
    $passwordRegister = $_POST["passwordRegister"] ?? null;
    $mailRegister = $_POST["mailRegister"] ?? null;
    $nameRegister = $_POST["nameRegister"] ?? null;
    $rutRegister = $_POST["rutRegister"] ?? null;

    $isAutorRegister = ($_POST["isAutorRegister"] ?? null) === "yes" ? 1 : 0;
    $isRevisorRegister = ($_POST["isReviserRegister"] ?? null) === "yes" ? 1 : 0;

    // Validación de campos vacíos
    if ($passwordRegister == null || $mailRegister == null || $rutRegister == null || $nameRegister == null) {
        header("Location: /Tarea2-BDD/PHP/Pages/register.php");
        exit();
    }

    // Validación de formato de RUT (ejemplo: 12345678-9 o 12345678-k)
    if (!preg_match("/^\d{7,8}-[\dkK]$/", $rutRegister)) {
        echo "El RUT ingresado no tiene un formato válido. Debe ser como '12345678-9'.";
        echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php">Volver al registro</a>';
        exit();
    }

    // Conexión a la base de datos
    $mysqli = require "databaseConnect.php";

    // Verificar si ya existe usuario con mismo RUT o correo
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

    if ($row['existe_usuario']) {
        echo "El Rut o el Correo ya están registrados.";
        echo '<br><a href="/Tarea2-BDD/PHP/Pages/register.php">Intentar de nuevo</a>';
        exit();
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuario (Rut, Nombre, Correo, Contraseña, EsAutor, EsRevisor)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            die("Error en la preparación: " . $mysqli->error);
        }
        $stmt->bind_param("ssssii", $rutRegister, $nameRegister, $mailRegister, $passwordRegister, $isAutorRegister, $isRevisorRegister);
        $stmt->execute();
        $stmt->close();

        // Si es revisor, insertar los tópicos seleccionados
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
    }

    // Redirigir al inicio tras registrar
    header("Location: /Tarea2-BDD/PHP/Pages/index.php");
    exit();
}
