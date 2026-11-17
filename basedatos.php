<?php
include 'Usuario.php';

class BaseDatos {
    private $servidor = "localhost";
    private $nombreBD = "clasificados_bd";
    private $usuarioBD = "root";
    private $contrasenaBD = "";

    // CONECTAR A LA BASE DE DATOS
    public function conectar() {
        try {
            $conexion = new PDO("mysql:host=" . $this->servidor . ";port=3307;dbname=" . $this->nombreBD, $this->usuarioBD, $this->contrasenaBD);
            return $conexion;
        } catch (PDOException $error) {
            echo "Error de conexión: " . $error->getMessage();
            return null;
        }
    }

    // VERIFICAR USUARIO Y CONTRASEÑA (SELECT)
    public function validarUsuario($nombreUsuario, $contrasena, $rol) {
        $conexion = $this->conectar();
        try {
            $sql = "SELECT * FROM usuarios WHERE nombre_usuario = ? AND contrasena = ? AND rol = ?";
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

    // CREAR PUBLICADOR USANDO la CLASE Usuario.php (INSERT)
    public function crearPublicador($usuario) {
        $conexion = $this->conectar();
        try {
            $sql = "INSERT INTO usuarios (nombre_usuario, contrasena, rol) VALUES (?, ?, ?)";
            $consulta = $conexion->prepare($sql);
            $datos = array($usuario->getNombreUsuario(), $usuario->getContrasena(), $usuario->getRol());
            $consulta->execute($datos);
            return "Publicador creado con éxito.";
        } catch (PDOException $error) {
            return "Error al crear publicador: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // BORRAR PUBLICADOR (DELETE)
    public function borrarPublicador($nombrePublicador) {
        $conexion = $this->conectar();
        try {
            $sql = "DELETE FROM usuarios WHERE nombre_usuario = ? AND rol = 'publicador'";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($nombrePublicador));
            return "Publicador borrado.";
        } catch (PDOException $error) {
            return "Error al borrar publicador: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // GUARDAR UN AVISO NUEVO (INSERT)
    public function crearAviso($aviso) {
        $conexion = $this->conectar();
        try {
            $categoria = $aviso->getCategoria();
            $sql = "";
            $datos = array();

            if ($categoria == 'laboral') {
                $sql = "INSERT INTO avisos (categoria, nombre_autor, telefono_autor, resaltado, usuario_publicador, fecha_creacion, empresa, descripcion_cargo, sueldo_mensual) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $datos = array(
                    $categoria, $aviso->getNombreAutor(), $aviso->getTelefonoAutor(), $aviso->getResaltado(),
                    $aviso->getUsuarioPublicador(), $aviso->getFechaCreacion(),
                    $aviso->getEmpresa(), $aviso->getDescripcionCargo(), $aviso->getSueldoMensual()
                );
            }
            if ($categoria == 'producto') {
                $sql = "INSERT INTO avisos (categoria, nombre_autor, telefono_autor, resaltado, usuario_publicador, fecha_creacion, descripcion_producto, precio) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $datos = array(
                    $categoria, $aviso->getNombreAutor(), $aviso->getTelefonoAutor(), $aviso->getResaltado(),
                    $aviso->getUsuarioPublicador(), $aviso->getFechaCreacion(),
                    $aviso->getDescripcionProducto(), $aviso->getPrecio()
                );
            }
            if ($categoria == 'inmobiliario_venta') {
                $sql = "INSERT INTO avisos (categoria, nombre_autor, telefono_autor, resaltado, usuario_publicador, fecha_creacion, direccion, tipo_propiedad, inmobiliaria, precio_venta) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $datos = array(
                    $categoria, $aviso->getNombreAutor(), $aviso->getTelefonoAutor(), $aviso->getResaltado(),
                    $aviso->getUsuarioPublicador(), $aviso->getFechaCreacion(),
                    $aviso->getDireccion(), $aviso->getTipoPropiedad(), $aviso->getInmobiliaria(), $aviso->getPrecioVenta()
                );
            }
            if ($categoria == 'inmobiliario_alquiler') {
                $sql = "INSERT INTO avisos (categoria, nombre_autor, telefono_autor, resaltado, usuario_publicador, fecha_creacion, direccion, tipo_propiedad, inmobiliaria, precio_alquiler, gastos_comunes) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $datos = array(
                    $categoria, $aviso->getNombreAutor(), $aviso->getTelefonoAutor(), $aviso->getResaltado(),
                    $aviso->getUsuarioPublicador(), $aviso->getFechaCreacion(),
                    $aviso->getDireccion(), $aviso->getTipoPropiedad(), $aviso->getInmobiliaria(), $aviso->getPrecioAlquiler(), $aviso->getGastosComunes()
                );
            }

            $consulta = $conexion->prepare($sql);
            $consulta->execute($datos);
            return "Aviso creado con éxito.";
        } catch (PDOException $error) {
            return "Error al crear el aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // TRANSFORMAR UNA FILA DE LA TABLA EN UN OBJETO AVISO
    public function transformarFilaEnAviso($fila) {
        $categoria = $fila['categoria'];

        if ($categoria == 'laboral') {
            return new AvisoLaboral(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['usuario_publicador'],
                $fila['empresa'], $fila['descripcion_cargo'], $fila['sueldo_mensual'], $fila['id'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'producto') {
            return new AvisoProducto(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['usuario_publicador'],
                $fila['descripcion_producto'], $fila['precio'], $fila['id'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'inmobiliario_venta') {
            return new AvisoInmobiliarioVenta(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['usuario_publicador'],
                $fila['direccion'], $fila['tipo_propiedad'], $fila['inmobiliaria'], $fila['precio_venta'], $fila['id'], $fila['fecha_creacion']
            );
        }
        if ($categoria == 'inmobiliario_alquiler') {
            return new AvisoInmobiliarioAlquiler(
                $fila['nombre_autor'], $fila['telefono_autor'], $fila['resaltado'], $fila['usuario_publicador'],
                $fila['direccion'], $fila['tipo_propiedad'], $fila['inmobiliaria'], $fila['precio_alquiler'], $fila['gastos_comunes'], $fila['id'], $fila['fecha_creacion']
            );
        }
        return null;
    }

    // OBTENER TODOS LOS AVISOS
    public function obtenerTodosLosAvisos() {
        $listaAvisos = array();
        $conexion = $this->conectar();
        try {
            $sql = "SELECT * FROM avisos ORDER BY categoria ASC, fecha_creacion ASC";
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

    // CARGAR AVISOS DE UN PUBLICADOR (AGREGACIÓN)
    public function cargarAvisosDePublicador($publicador) {
        $conexion = $this->conectar();
        try {
            $sql = "SELECT * FROM avisos WHERE usuario_publicador = ? ORDER BY categoria ASC, fecha_creacion ASC";
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

    // BORRAR AVISO COMO ADMINISTRADOR
    public function borrarAvisoAdmin($idAviso) {
        $conexion = $this->conectar();
        try {
            $sql = "DELETE FROM avisos WHERE id = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($idAviso));
            return "Operación de borrado realizada.";
        } catch (PDOException $error) {
            return "Error al borrar aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    // BORRAR AVISO COMO PUBLICADOR
    public function borrarAvisoPublicador($idAviso, $nombrePublicador) {
        $conexion = $this->conectar();
        try {
            $sql = "DELETE FROM avisos WHERE id = ? AND usuario_publicador = ?";
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
