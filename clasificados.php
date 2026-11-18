<?php
include 'basedatos.php';
$bd = new BaseDatos();
$lista = $bd->obtenerTodosLosAvisos();
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Clasificados</title></head>
<body>
    <h1>Avisos Clasificados</h1>
    <a href="clasificados.php">Ver</a> | <a href="publicador.php">Publicar</a> | <a href="admin.php">Admin</a>
    <hr>
    <?php foreach ($lista as $a) { ?>
        <div>
            <?php echo $a->mostrarDetalle(); ?>
            <p>Autor: <?php echo $a->getNombreAutor(); ?> - Tel: <?php echo $a->getTelefonoAutor(); ?></p>
        </div>
        <hr>
    <?php } ?>
</body>
</html>
