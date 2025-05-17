<?php 
    $mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
    $sql = "SELECT * FROM topicos";
    $result = $mysqli->query($sql);
    $topicos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $topicos[] = $row;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script>
        function validarFormulario(e) {
            const autor = document.getElementById('isAutorRegister').checked;
            const revisor = document.getElementById('isRevisorRegister').checked;

            if (!autor && !revisor) {
                e.preventDefault();
                alert("Debes seleccionar al menos un rol: Autor y/o Revisor.");
                return false;
            }
            if (revisor) {
                const topicos = document.querySelectorAll('input[name="topicosSeleccionados[]"]:checked');
                if (topicos.length === 0) {
                        e.preventDefault();
                        alert("Debes seleccionar al menos un tópico si deseas ser revisor.");
                        return false;
                }
            }
            return true;
        }

        function toggleTopicos() {
            const revisorChecked = document.getElementById('isRevisorRegister').checked;
            const topicosDiv = document.getElementById('topicosRevisor');
            topicosDiv.style.display = revisorChecked ? 'block' : 'none';
        }

        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('isRevisorRegister').addEventListener('change', toggleTopicos);
            document.getElementById('registroForm').addEventListener('submit', validarFormulario);
            toggleTopicos(); // Mostrar u ocultar tópicos al cargar
        });
    </script>
</head>
<body>
    <h1>Regístrate</h1>
    <form id="registroForm" action="/Tarea2-BDD/PHP/Logic/registerLogic.php" method="post">
        <label for="nameRegister">Nombre Completo</label>
        <input required name="nameRegister" id="nameRegister" type="text" placeholder="Ingrese su nombre completo..">
        <br><br>

        <label for="rutRegister">RUT (Sin puntos ni guion)</label>
        <input required name="rutRegister" id="rutRegister" type="text" placeholder="Ingrese su rut..">
        <br><br>

        <label for="mailRegister">Correo</label>
        <input required id="mailRegister" name="mailRegister" type="email" placeholder="Ingrese su correo..">
        <br><br>

        <label for="passwordRegister">Contraseña</label>
        <input required name="passwordRegister" id="passwordRegister" type="password" placeholder="Ingrese su contraseña..">
        <br><br>

        <label>Roles (selecciona al menos uno)</label><br>
        <input name="isAutorRegister" id="isAutorRegister" type="checkbox" value="yes">
        <label for="isAutorRegister">Autor</label><br>

        <input name="isRevisorRegister" id="isRevisorRegister" type="checkbox" value="yes">
        <label for="isRevisorRegister">Revisor</label>
        <br><br>

        <div id="topicosRevisor" style="display:none; border:1px solid #ccc; padding:10px;">
            <strong>Selecciona tus tópicos de revisión:</strong><br>
            <?php foreach ($topicos as $topico): ?>
                <input type="checkbox" name="topicosSeleccionados[]" value="<?= htmlspecialchars($topico['IDTopico']) ?>">
                <label><?= htmlspecialchars($topico['NombreTopico']) ?></label><br>
            <?php endforeach; ?>
        </div>
        <br>

        <button type="submit">Ingresar</button>
    </form>

    <h3>¿Ya estás registrado? <a href="/Tarea2-BDD/PHP/Pages/login.php">Inicia sesión aquí.</a></h3>
</body>
</html>