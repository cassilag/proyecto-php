<?php
require_once 'BaseDatos.php';
$bd = new BaseDatos();
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $au = $_POST['admin_user'];
    $ap = $_POST['admin_pass'];
    if ($bd->validarUsuario($au, $ap, 'admin')) {
        if ($_POST['accion'] == 'borrar') {
            $mensaje = $bd->borrarAvisoAdmin($_POST['id']);
        }
    } else {
        $mensaje = "Admin incorrecto";
    }
}
$todos = $bd->obtenerTodosLosAvisos();
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Admin</title></head>
<body>
    <h1>Admin</h1>
    <a href="clasificados.php">Ver</a> | <a href="publicador.php">Publicar</a> | <a href="admin.php">Admin</a>
    <hr>
    <p><b><?php echo $mensaje; ?></b></p>

    <?php foreach ($todos as $a): ?>
        <div>
            <?php echo $a->mostrarDetalle(); ?>
            <form method="POST">
                <input type="hidden" name="accion" value="borrar">
                <input type="hidden" name="id" value="<?php echo $a->getId(); ?>">
                Admin User: <input type="text" name="admin_user" required>
                Admin Pass: <input type="password" name="admin_pass" required>
                <button type="submit">Eliminar este aviso</button>
            </form>
        </div>
        <hr>
    <?php endforeach; ?>
</body>
</html>
