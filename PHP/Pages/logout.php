<?php
    session_start();
    session_destroy();
    header("Location: /Tarea2-BDD/PHP/Pages/login.php");