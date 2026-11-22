<?php
include 'Usuario.php';

class BaseDatos {
    private $servidor = "localhost";
    private $nombreBD = "clasificados_db";
    private $usuarioBD;
    private $contrasenaBD;

    public function __construct($perfil = 'visitante') {
        if ($perfil == 'admin') {
            $this->usuarioBD = "usr_admin";
            $this->contrasenaBD = "AdminPass2026!";
        } elseif ($perfil == 'publicador') {
            $this->usuarioBD = "usr_publicador";
            $this->contrasenaBD = "PubPass2026!";
        } else {
            $this->usuarioBD = "usr_visitante";
            $this->contrasenaBD = "VisPass2026!";
        }
    }

    public function conectar() {
        try {
            $conexion = new PDO("mysql:host=" . $this->servidor . ";dbname=" . $this->nombreBD . ";charset=utf8mb4", $this->usuarioBD, $this->contrasenaBD);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $error) {
            echo "Error de conexión: " . $error->getMessage();
            return null;
        }
    }

    // VERIFICAR EMAIL, CONTRASEÑA Y ROL EN Autor + Rol
    public function validarUsuario($nombreUsuario, $contrasena, $rol) {
        $conexion = $this->conectar();
        try {
            $sql = "SELECT au.id_autor
                    FROM Autor au
                    INNER JOIN Rol r ON au.id_rol = r.id_rol
                    WHERE au.email = ? AND au.contrasena = ? AND r.nombre = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($nombreUsuario, $contrasena, $rol));

            foreach ($consulta as $fila) {
                return true;
            }
            return false;
        } catch (PDOException $error) {
            echo "Error al validar usuario: " . $error->getMessage();
            return false;
        } finally {
            $conexion = null;
        }
    }

    // CREAR PUBLICADOR EN TABLA Autor
    public function crearPublicador($usuario) {
        $conexion = $this->conectar();
        try {
            $stmtRol = $conexion->prepare("SELECT id_rol FROM Rol WHERE nombre = ?");
            $stmtRol->execute(array($usuario->getRol()));
            $idRol = $stmtRol->fetchColumn();

            $sql = "INSERT INTO Autor (nombre, email, contrasena, telefono, id_rol) VALUES (?, ?, ?, ?, ?)";
            $consulta = $conexion->prepare($sql);
            $datos = array(
                $usuario->getNombreUsuario(),
                $usuario->getEmail(),
                $usuario->getContrasena(),
                $usuario->getTelefono(),
                $idRol
            );
            $consulta->execute($datos);
            return "Publicador creado con éxito.";
        } catch (PDOException $error) {
            return "Error al crear publicador: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // GUARDAR UN AVISO NUEVO Y SU SUBTIPO CON TRANSACCIÓN (TCL)
    public function crearAviso($aviso) {
        $conexion = $this->conectar();
        try {
            $categoria = $aviso->getCategoria();

            $stmtAutor = $conexion->prepare("SELECT id_autor FROM Autor WHERE email = ?");
            $stmtAutor->execute(array($aviso->getUsuarioPublicador()));
            $idAutor = $stmtAutor->fetchColumn();

            $nombreCatBD = ($categoria == 'laboral') ? 'Laboral' : (($categoria == 'producto') ? 'Producto' : 'Inmobiliario');
            $stmtCat = $conexion->prepare("SELECT id_categoria FROM Categoria WHERE nombre = ?");
            $stmtCat->execute(array($nombreCatBD));
            $idCategoria = $stmtCat->fetchColumn();

            $conexion->beginTransaction();

            $sqlAviso = "INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria) VALUES (?, ?, ?, ?, ?)";
            $stmtAviso = $conexion->prepare($sqlAviso);
            $stmtAviso->execute(array(
                $aviso->getTitulo(),
                $aviso->getFechaCreacion(),
                $aviso->getResaltado(),
                $idAutor,
                $idCategoria
            ));
            $idAviso = $conexion->lastInsertId();

            if ($categoria == 'laboral') {
                $sql = "INSERT INTO Aviso_Laboral (id_aviso, empresa, descripcion_cargo, sueldo) VALUES (?, ?, ?, ?)";
                $sueldo = ($aviso->getSueldoMensual() === '') ? null : $aviso->getSueldoMensual();
                $datos = array($idAviso, $aviso->getEmpresa(), $aviso->getDescripcionCargo(), $sueldo);
            }
            if ($categoria == 'producto') {
                $sql = "INSERT INTO Aviso_Producto (id_aviso, descripcion, precio) VALUES (?, ?, ?)";
                $datos = array($idAviso, $aviso->getDescripcionProducto(), $aviso->getPrecio());
            }
            if ($categoria == 'inmobiliario_venta') {
                $sql = "INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
                        VALUES (?, ?, ?, ?, 'Venta', ?, NULL)";
                $datos = array($idAviso, $aviso->getDireccion(), $aviso->getTipoPropiedad(), $aviso->getInmobiliaria(), $aviso->getPrecioVenta());
            }
            if ($categoria == 'inmobiliario_alquiler') {
                $sql = "INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
                        VALUES (?, ?, ?, ?, 'Alquiler', ?, ?)";
                $gc = ($aviso->getGastosComunes() === '') ? null : $aviso->getGastosComunes();
                $datos = array($idAviso, $aviso->getDireccion(), $aviso->getTipoPropiedad(), $aviso->getInmobiliaria(), $aviso->getPrecioAlquiler(), $gc);
            }

            $consulta = $conexion->prepare($sql);
            $consulta->execute($datos);
            $conexion->commit();
            return "Aviso creado con éxito.";
        } catch (PDOException $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return "Error al crear el aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // TRANSFORMAR UNA FILA DEL JOIN EN UN OBJETO AVISO
    public function transformarFilaEnAviso($fila) {
        $categoria = $fila['categoria'];
        $aviso = null;

        if ($categoria == 'Laboral') {
            $aviso = new AvisoLaboral(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['email_autor'],
                $fila['empresa'], $fila['descripcion_cargo'], $fila['sueldo'], $fila['id_aviso'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'Producto') {
            $aviso = new AvisoProducto(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['email_autor'],
                $fila['descripcion_producto'], $fila['precio_producto'], $fila['id_aviso'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'Inmobiliario' && strtolower($fila['tipo_operacion']) == 'venta') {
            $aviso = new AvisoInmobiliarioVenta(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['email_autor'],
                $fila['direccion'], $fila['tipo_inmueble'], $fila['inmobiliaria'], $fila['precio_inmueble'], $fila['id_aviso'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'Inmobiliario' && strtolower($fila['tipo_operacion']) != 'venta') {
            $aviso = new AvisoInmobiliarioAlquiler(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['email_autor'],
                $fila['direccion'], $fila['tipo_inmueble'], $fila['inmobiliaria'], $fila['precio_inmueble'], $fila['gastos_comunes'], $fila['id_aviso'], $fila['fecha_creacion']
            );
        }

        if ($aviso !== null) {
            $aviso->setTitulo($fila['titulo']);
        }
        return $aviso;
    }

    // OBTENER TODOS LOS AVISOS (CON JOIN Y COLUMNAS PERMITIDAS PARA usr_visitante)
    public function obtenerTodosLosAvisos() {
        $listaAvisos = array();
        $conexion = $this->conectar();
        try {
            $sql = "SELECT a.id_aviso, a.titulo, a.fecha_creacion, a.resaltado,
                           c.nombre AS categoria,
                           au.nombre AS nombre_autor, au.email AS email_autor, au.telefono AS telefono_autor,
                           al.empresa, al.descripcion_cargo, al.sueldo,
                           ap.descripcion AS descripcion_producto, ap.precio AS precio_producto,
                           ai.direccion, ai.tipo_inmueble, ai.inmobiliaria, ai.tipo_operacion, ai.precio AS precio_inmueble, ai.gastos_comunes
                    FROM Aviso a
                    INNER JOIN Categoria c ON a.id_categoria = c.id_categoria
                    INNER JOIN Autor au ON a.id_autor = au.id_autor
                    LEFT JOIN Aviso_Laboral al ON a.id_aviso = al.id_aviso
                    LEFT JOIN Aviso_Producto ap ON a.id_aviso = ap.id_aviso
                    LEFT JOIN Aviso_Inmobiliario ai ON a.id_aviso = ai.id_aviso
                    ORDER BY c.nombre ASC, a.fecha_creacion ASC";
            $consulta = $conexion->prepare($sql);
            $consulta->execute();

            foreach ($consulta as $fila) {
                $listaAvisos[] = $this->transformarFilaEnAviso($fila);
            }
            return $listaAvisos;
        } catch (PDOException $error) {
            echo "Error al obtener avisos: " . $error->getMessage();
            return $listaAvisos;
        } finally {
            $conexion = null;
        }
    }

    // CARGAR AVISOS DE UN PUBLICADOR (FILTRADO POR EMAIL)
    public function cargarAvisosDePublicador($publicador) {
        $conexion = $this->conectar();
        try {
            $sql = "SELECT a.id_aviso, a.titulo, a.fecha_creacion, a.resaltado,
                           c.nombre AS categoria,
                           au.nombre AS nombre_autor, au.email AS email_autor, au.telefono AS telefono_autor,
                           al.empresa, al.descripcion_cargo, al.sueldo,
                           ap.descripcion AS descripcion_producto, ap.precio AS precio_producto,
                           ai.direccion, ai.tipo_inmueble, ai.inmobiliaria, ai.tipo_operacion, ai.precio AS precio_inmueble, ai.gastos_comunes
                    FROM Aviso a
                    INNER JOIN Categoria c ON a.id_categoria = c.id_categoria
                    INNER JOIN Autor au ON a.id_autor = au.id_autor
                    LEFT JOIN Aviso_Laboral al ON a.id_aviso = al.id_aviso
                    LEFT JOIN Aviso_Producto ap ON a.id_aviso = ap.id_aviso
                    LEFT JOIN Aviso_Inmobiliario ai ON a.id_aviso = ai.id_aviso
                    WHERE au.email = ?
                    ORDER BY c.nombre ASC, a.fecha_creacion ASC";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($publicador->getNombreUsuario()));

            foreach ($consulta as $fila) {
                $nuevoAviso = $this->transformarFilaEnAviso($fila);
                $publicador->agregarAviso($nuevoAviso);
            }
        } catch (PDOException $error) {
            echo "Error al obtener avisos del publicador: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // BORRAR AVISO COMO ADMINISTRADOR (ON DELETE CASCADE BORRA EL SUBTIPO)
    public function borrarAvisoAdmin($idAviso) {
        $conexion = $this->conectar();
        try {
            $sql = "DELETE FROM Aviso WHERE id_aviso = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($idAviso));
            return "Operación de borrado realizada.";
        } catch (PDOException $error) {
            return "Error al borrar aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // BORRAR AVISO COMO PUBLICADOR (SOLO SI ES SUYO)
    public function borrarAvisoPublicador($idAviso, $nombrePublicador) {
        $conexion = $this->conectar();
        try {
            $sql = "DELETE FROM Aviso WHERE id_aviso = ? AND id_autor = (SELECT id_autor FROM Autor WHERE email = ?)";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($idAviso, $nombrePublicador));
            return "Operación de borrado realizada (si el aviso era tuyo).";
        } catch (PDOException $error) {
            return "Error al borrar aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }
}
?>
