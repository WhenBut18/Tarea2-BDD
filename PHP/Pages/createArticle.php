<?php 
    session_start();
    if ($_SESSION["user_id"] == NULL) {
        header("Location: /Tarea2-BDD/PHP/Pages/login.php");
        exit();
    }
    $mysqli = require __DIR__ . "/../Logic/databaseConnect.php";
    $sql = "SELECT * FROM usuario WHERE Rut = {$_SESSION["user_id"]}";
    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();
    if ($user["EsAutor"] == false) {
        header("Location: /Tarea2-BDD/PHP/Pages/index.php");
        exit();
    }
    $sql = "SELECT * FROM topicos";
    $result = $mysqli->query($sql);
    $topicos = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $topicos[] = $row;
        }
    }
    $sql = "SELECT * FROM usuario";
    $result = $mysqli->query($sql);
    $autores = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if ($row["EsAutor"] == true ){
                $autores[] = $row;
            }
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
        // Habilita/deshabilita radios de contacto según los checkboxes de autores
        function toggleContacto(index) {
            const autorCheckbox = document.getElementById(`autor-${index}`);
            const contactoRadio = document.getElementById(`contacto-${index}`);
            contactoRadio.disabled = !autorCheckbox.checked;
            if (!autorCheckbox.checked) {
            contactoRadio.checked = false;
            }
        }
    </script>
</head>
<body>
    <h5><a href="/Tarea2-BDD/PHP/Pages/index.php"><button>Volver al Menu Principal</button></a></h5>
    <h1>Envio de Articulo</h1>
    <form id="formularioArticulo" action="/Tarea2-BDD/PHP/Logic/createArticleLogic.php" method="POST">
        <label for="titleSubmit">Titulo del Articulo</label>
        <br>
        <input required id="titleSubmit" name="titleSubmit" type="text" placeholder="Ingrese el titulo.." >
        <br><br>
        <label for="summarySubmit">Resumen</label>
        <br>
        <input required name="summarySubmit" id="summarySubmit" type="text" placeholder="Ingrese el resumen..">
        <br><br>
        <table border="1" cellpadding="8">
            <tr>
            <th>Nombre</th>
            <th>RUT</th>
            <th>Seleccionar Autor</th>
            <th>Es Autor de Contacto</th>
            </tr>

            <?php foreach ($autores as $index => $autor): ?>
            <tr>
                <td><?= htmlspecialchars($autor['Nombre']) ?></td>
                <td><?= htmlspecialchars($autor['Rut']) ?></td>
                <td>
                <input type="checkbox"
                        name="seleccionarAutor[]"
                        value="<?= $autor['Rut'] ?>"
                        id="autor-<?= $index ?>"
                        onchange="toggleContacto(<?= $index ?>)">
                </td>
                <td>
                <input type="radio"
                        name="autorContacto"
                        value="<?= $autor['Rut'] ?>"
                        id="contacto-<?= $index ?>"
                        disabled>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <table border="1" cellpadding="8">
            <tr>
            <th>Topico</th>
            <th>Seleccionar Topico</th>
            </tr>

            <?php foreach ($topicos as $topico): ?>  
            <tr>
                <td><?= htmlspecialchars($topico['NombreTopico']) ?></td>
                <td>
                <input type="checkbox" name="seleccionarTopico[]" value="<?= $topico['IDTopico'] ?>">
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <button type="submit">Enviar</button>
    </form>
    <script>
        
        // Validación al enviar
        document.getElementById('formularioArticulo').addEventListener('submit', function(event) {
            // Validar autor de contacto
            const radios = document.querySelectorAll('input[name="autorContacto"]');
            let autorContactoSeleccionado = false;
            for (let radio of radios) {
            if (radio.checked) {
                autorContactoSeleccionado = true;
                break;
            }
            }

            // Validar al menos un tópico seleccionado
            const topicos = document.querySelectorAll('input[name="seleccionarTopico[]"]');
            let topicoSeleccionado = false;
            for (let checkbox of topicos) {
            if (checkbox.checked) {
                topicoSeleccionado = true;
                break;
            }
            }

            // Mostrar alertas si falta algo
            if (!autorContactoSeleccionado) {
            alert("Debes seleccionar un autor de contacto.");
            event.preventDefault();
            return;
            }

            if (!topicoSeleccionado) {
            alert("Debes seleccionar al menos un tópico.");
            event.preventDefault();
            return;
            }
        });
    </script>
</body>
</html>