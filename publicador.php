<?php
include 'basedatos.php';
$bd = new BaseDatos('publicador');
$mensaje = "";
$misAvisos = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $accion = $_POST['accion'];

    // Si la acción es crear un publicador nuevo no requiere login previo
    if ($accion == 'crear_pub') {
        $nuevoPub = new Publicador($_POST['nuevo_usuario'], $_POST['nueva_contrasena'], $_POST['nuevo_email'], $_POST['nuevo_tel']);
        $mensaje = $bd->crearPublicador($nuevoPub);
    } else {
        // Para el resto de acciones se valida el usuario y contraseña del formulario
        $u = $_POST['usuario'];
        $p = $_POST['contrasena'];

        if ($bd->validarUsuario($u, $p, 'publicador')) {
            $resaltadoValor = isset($_POST['resaltado']) ? 1 : 0;

            if ($accion == 'crear_laboral') {
                $aviso = new AvisoLaboral($_POST['autor'], $_POST['tel'], $resaltadoValor, $u, $_POST['empresa'], $_POST['cargo'], $_POST['sueldo'], null, date('Y-m-d H:i:s'));
                $aviso->setTitulo($_POST['titulo']);
                $mensaje = $bd->crearAviso($aviso);
            } elseif ($accion == 'crear_producto') {
                $aviso = new AvisoProducto($_POST['autor'], $_POST['tel'], $resaltadoValor, $u, $_POST['descripcion_producto'], $_POST['precio'], null, date('Y-m-d H:i:s'));
                $aviso->setTitulo($_POST['titulo']);
                $mensaje = $bd->crearAviso($aviso);
            } elseif ($accion == 'crear_venta') {
                $aviso = new AvisoInmobiliarioVenta($_POST['autor'], $_POST['tel'], $resaltadoValor, $u, $_POST['direccion'], $_POST['tipo_propiedad'], $_POST['inmobiliaria'], $_POST['precio_venta'], null, date('Y-m-d H:i:s'));
                $aviso->setTitulo($_POST['titulo']);
                $mensaje = $bd->crearAviso($aviso);
            } elseif ($accion == 'crear_alquiler') {
                $aviso = new AvisoInmobiliarioAlquiler($_POST['autor'], $_POST['tel'], $resaltadoValor, $u, $_POST['direccion'], $_POST['tipo_propiedad'], $_POST['inmobiliaria'], $_POST['precio_alquiler'], $_POST['gastos_comunes'], null, date('Y-m-d H:i:s'));
                $aviso->setTitulo($_POST['titulo']);
                $mensaje = $bd->crearAviso($aviso);
            } elseif ($accion == 'borrar') {
                $mensaje = $bd->borrarAvisoPublicador($_POST['id'], $u);
            }

            $pub = new Publicador($u, $p);
            $bd->cargarAvisosDePublicador($pub);
            $misAvisos = $pub->getMisAvisos();
        } else {
            $mensaje = "Error de login";
        }
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

    <h3>Nuevo Publicador</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear_pub">
        Nuevo Usuario: <input type="text" name="nuevo_usuario" required> 
        Nueva Contraseña: <input type="password" name="nueva_contrasena" required>
        Email: <input type="email" name="nuevo_email" required>
        Teléfono: <input type="text" name="nuevo_tel">
        <button type="submit">Crear Cuenta</button>
    </form>

    <hr>
    <h3>Ver mis avisos</h3>
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
        <hr>
    <?php } ?>

    <hr>
    <h3>Crear Aviso Laboral</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear_laboral">
        Usuario: <input type="text" name="usuario" required> Pass: <input type="password" name="contrasena" required><br>
        Autor: <input type="text" name="autor" required> Tel: <input type="text" name="tel" required><br>
        Empresa: <input type="text" name="empresa" required> Cargo: <input type="text" name="cargo" required> Sueldo: <input type="number" name="sueldo" required><br>
        Título: <input type="text" name="titulo" required>
        <label><input type="checkbox" name="resaltado" value="1"> Resaltado (Paga el doble)</label><br>
        <button type="submit">Crear Laboral</button>
    </form>

    <hr>
    <h3>Crear Aviso Producto</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear_producto">
        Usuario: <input type="text" name="usuario" required> Pass: <input type="password" name="contrasena" required><br>
        Autor: <input type="text" name="autor" required> Tel: <input type="text" name="tel" required><br>
        Descripción: <input type="text" name="descripcion_producto" required> Precio: <input type="number" name="precio" required><br>
        Título: <input type="text" name="titulo" required><br>
        <label><input type="checkbox" name="resaltado" value="1"> Resaltado (Paga el doble)</label><br>
        <button type="submit">Crear Producto</button>
    </form>

    <hr>
    <h3>Crear Aviso Inmobiliario - Venta</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear_venta">
        Usuario: <input type="text" name="usuario" required> Pass: <input type="password" name="contrasena" required><br>
        Autor: <input type="text" name="autor" required> Tel: <input type="text" name="tel" required><br>
        Dirección: <input type="text" name="direccion" required> Tipo (Casa/Apto): <input type="text" name="tipo_propiedad" required><br>
        Inmobiliaria: <input type="text" name="inmobiliaria" required> Precio Venta: <input type="number" name="precio_venta" required><br>
        Título: <input type="text" name="titulo" required><br>
        <label><input type="checkbox" name="resaltado" value="1"> Resaltado (Paga el doble)</label><br>
        <button type="submit">Crear Venta</button>
    </form>

    <hr>
    <h3>Crear Aviso Inmobiliario - Alquiler</h3>
    <form method="POST">
        <input type="hidden" name="accion" value="crear_alquiler">
        Usuario: <input type="text" name="usuario" required> Pass: <input type="password" name="contrasena" required><br>
        Autor: <input type="text" name="autor" required> Tel: <input type="text" name="tel" required><br>
        Dirección: <input type="text" name="direccion" required> Tipo (Casa/Apto): <input type="text" name="tipo_propiedad" required><br>
        Inmobiliaria: <input type="text" name="inmobiliaria" required> Alquiler: <input type="number" name="precio_alquiler" required> Gastos Comunes: <input type="number" name="gastos_comunes" required><br>
        Título: <input type="text" name="titulo" required><br>
        <label><input type="checkbox" name="resaltado" value="1"> Resaltado (Paga el doble)</label><br>
        <button type="submit">Crear Alquiler</button>
    </form>
</body>
</html>
