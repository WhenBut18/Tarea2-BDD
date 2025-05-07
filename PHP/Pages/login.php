<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Inicia Sesion</h1>
    <form action="/Tarea2-BDD/PHP/Logic/loginLogic.php" method="post">
        <label for="mailLogin">Correo</label>
        <input required id="mailLogin" name="mailLogin" type="email" placeholder="Ingrese su correo.." >
        <br><br>
        <label for="passwordLogin">Contraseña</label>
        <input required name="passwordLogin" id="passwordLogin" type="text" placeholder="Ingrese su contraseña..">
        <br><br>
        <button type="submit">Ingresar</button>
    </form>
    <h3>¿Eres nuevo? <a href="/Tarea2-BDD/PHP/Pages/register.php">registrate aquí.</a></h3>
</body>
</html>