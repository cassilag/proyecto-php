<?php
include 'BaseDatos.php';
$bd = new BaseDatos();
$mensaje = "";
$misAvisos = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $u = $_POST['usuario'];
    $p = $_POST['contrasena'];
    $accion = $_POST['accion'];

    if ($bd->validarUsuario($u, $p, 'publicador')) {
        if ($accion == 'crear') {
            $resaltadoValor = isset($_POST['resaltado']) ? 1 : 0;
            $aviso = new AvisoLaboral($_POST['autor'], $_POST['tel'], $resaltadoValor, $u, $_POST['empresa'], $_POST['cargo'], $_POST['sueldo'], null, date('Y-m-d H:i:s'));
            $mensaje = $bd->crearAviso($aviso);
        }
        if ($accion == 'borrar') {
            $mensaje = $bd->borrarAvisoPublicador($_POST['id'], $u);
        }
        $pub = new Publicador($u, $p);
        $bd->cargarAvisosDePublicador($pub);
        $misAvisos = $pub->getMisAvisos();
    } else {
        $mensaje = "Error de login";
    }
}
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Publicador</title></head>
<body>
    <h1>Publicador</h1>
    <a href="clasificados.php">Ver</a> | <a href="publicador.php">Publicar</a> | <a href="admin.php">Admin</a>
    <hr>
    <p><b><?php echo $mensaje; ?></b></p>
    
    <form method="POST">
        <input type="hidden" name="accion" value="ver">
        Usuario: <input type="text" name="usuario" required>
        Pass: <input type="password" name="contrasena" required>
        <button type="submit">Ver mis avisos</button>
    </form>

    <?php foreach ($misAvisos as $a) { ?>
        <div>
            <?php echo $a->mostrarDetalle(); ?>
            <form method="POST">
                <input type="hidden" name="accion" value="borrar">
                <input type="hidden" name="usuario" value="<?php echo $_POST['usuario']; ?>">
                <input type="hidden" name="contrasena" value="<?php echo $_POST['contrasena']; ?>">
                <input type="hidden" name="id" value="<?php echo $a->getId(); ?>">
                <button type="submit">Borrar</button>
            </form>
        </div>
    <?php } ?>

    <hr>
    <h3>Crear Aviso Laboral</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear">
        Usuario: <input type="text" name="usuario" required>
        Pass: <input type="password" name="contrasena" required><br>
        Autor: <input type="text" name="autor" required>
        Tel: <input type="text" name="tel" required><br>
        Empresa: <input type="text" name="empresa" required>
        Cargo: <input type="text" name="cargo" required>
        Sueldo: <input type="number" name="sueldo" required><br>
        <label><input type="checkbox" name="resaltado" value="1"> Resaltado</label><br>
        <button type="submit">Crear</button>
    </form>
</body>
</html>
