<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Registrate</h1>
    <form action="/Tarea2-BDD/PHP/Logic/registerLogic.php" method="post">
        <label for="nameRegister">Nombre Completo</label>
        <input required name="nameRegister" id="nameRegister" type="text" placeholder="Ingrese su nombre completo..">
        <br><br>
        <label for="rutRegister">Rut (Sin puntos ni guion)</label>
        <input required name="rutRegister" id="rutRegister" type="text" placeholder="Ingrese su rut..">
        <br><br>
        <label for="mailRegister">Correo</label>
        <input required id="mailRegister" name="mailRegister" type="email" placeholder="Ingrese su correo.." >
        <br><br>
        <label for="passwordRegister">Contraseña</label>
        <input required name="passwordRegister" id="passwordRegister" type="text" placeholder="Ingrese su contraseña..">
        <br><br>
        <label for="rolRegister">Roles</label>
        <br>
        <!-- AÑADIR SISTEMA QUE PERMITA ELEGIR ALMENOS 1 DE LA OPCIONES DE USUARIO -->
        <input name="isAutorRegister" id="isAutorRegister" type="checkbox" value="yes"><label>Autor</label>
        <br>
        <input name="isReviserRegister" id="isReviserRegister" type="checkbox" value="yes"><label>Revisor</label>
        <br>
        <button type="submit">Ingresar</button>
    </form>
    <h3>¿Ya estas registrado? <a href="/Tarea2-BDD/PHP/Pages/login.php">inicia sesion aquí.</a></h3>
</body>
</html>