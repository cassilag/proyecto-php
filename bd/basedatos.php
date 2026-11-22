<?php
include 'Usuario.php';

class BaseDatos {
    private $servidor = "localhost";
    private $puerto = "3307";
    private $nombreBD = "clasificados_bd";
    private $usuarioBD = "root";
    private $contrasenaBD = "";

    public function conectar() {
        try {
            $dsn = "mysql:host=" . $this->servidor . ";port=" . $this->puerto . ";dbname=" . $this->nombreBD . ";charset=utf8mb4";
            $conexion = new PDO($dsn, $this->usuarioBD, $this->contrasenaBD);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $error) {
            error_log("Error de conexión: " . $error->getMessage());
            return null;
        }
    }

    public function validarUsuario($nombreOEmail, $contrasena, $rol) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return false;
        }

        $rolEsperado = ($rol === 'admin') ? 'administrador' : $rol;

        try {
            $sql = "SELECT a.id_autor
                    FROM Autor a
                    INNER JOIN Rol r ON a.id_rol = r.id_rol
                    WHERE (a.email = ? OR a.nombre = ?)
                      AND a.contrasena = ?
                      AND r.nombre = ?
                    LIMIT 1";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($nombreOEmail, $nombreOEmail, $contrasena, $rolEsperado));
            return $consulta->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $error) {
            error_log("Error al validar usuario: " . $error->getMessage());
            return false;
        } finally {
            $conexion = null;
        }
    }

    private function obtenerOCrearAutor($conexion, $nombreAutor, $telefonoAutor, $usuarioPublicador, $idRolPublicador) {
        $sqlBuscar = "SELECT id_autor FROM Autor WHERE nombre = ? OR email = ? LIMIT 1";
        $stmtBuscar = $conexion->prepare($sqlBuscar);
        $stmtBuscar->execute(array($nombreAutor, $usuarioPublicador));
        $autorExistente = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

        if ($autorExistente) {
            $sqlUpdate = "UPDATE Autor SET telefono = ? WHERE id_autor = ?";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->execute(array($telefonoAutor, $autorExistente['id_autor']));
            return (int)$autorExistente['id_autor'];
        }

        $emailGenerado = filter_var($usuarioPublicador, FILTER_VALIDATE_EMAIL)
            ? $usuarioPublicador
            : preg_replace('/[^a-zA-Z0-9._-]/', '_', strtolower($nombreAutor)) . "_" . uniqid() . "@clasificados.local";

        $sqlInsert = "INSERT INTO Autor (nombre, email, contrasena, telefono, id_rol) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $conexion->prepare($sqlInsert);
        $stmtInsert->execute(array($nombreAutor, $emailGenerado, '1234', $telefonoAutor, $idRolPublicador));
        return (int)$conexion->lastInsertId();
    }

    private function obtenerIdCategoria($conexion, $nombreCategoria) {
        $sql = "SELECT id_categoria FROM Categoria WHERE LOWER(nombre) = LOWER(?) LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(array($nombreCategoria));
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? (int)$fila['id_categoria'] : null;
    }

    public function crearAviso($aviso) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return "Error de conexión con la base de datos.";
        }

        try {
            $conexion->beginTransaction();

            $categoria = $aviso->getCategoria();
            $idRolPub = $this->obtenerIdRol($conexion, 'publicador');
            $idAutor = $this->obtenerOCrearAutor(
                $conexion,
                $aviso->getNombreAutor(),
                $aviso->getTelefonoAutor(),
                $aviso->getUsuarioPublicador(),
                $idRolPub
            );

            $nombreCatBD = (strpos($categoria, 'inmobiliario') === 0) ? 'Inmobiliario' : ucfirst($categoria);
            $idCategoria = $this->obtenerIdCategoria($conexion, $nombreCatBD);
            if (!$idCategoria) {
                throw new Exception("Categoría no encontrada en la base de datos.");
            }

            $fecha = !empty($aviso->getFechaCreacion()) ? $aviso->getFechaCreacion() : date('Y-m-d H:i:s');

            $sqlAviso = "INSERT INTO Aviso (titulo, fecha_creacion, resaltado, id_autor, id_categoria)
                         VALUES (?, ?, ?, ?, ?)";
            $stmtAviso = $conexion->prepare($sqlAviso);
            $stmtAviso->execute(array(
                $aviso->getTitulo(),
                $fecha,
                (int)$aviso->getResaltado(),
                $idAutor,
                $idCategoria
            ));
            $idAviso = (int)$conexion->lastInsertId();

            if ($categoria === 'laboral') {
                $sqlSub = "INSERT INTO Aviso_Laboral (id_aviso, empresa, descripcion_cargo, sueldo)
                           VALUES (?, ?, ?, ?)";
                $stmtSub = $conexion->prepare($sqlSub);
                $sueldo = ($aviso->getSueldoMensual() === '' || $aviso->getSueldoMensual() === null) ? null : $aviso->getSueldoMensual();
                $stmtSub->execute(array($idAviso, $aviso->getEmpresa(), $aviso->getDescripcionCargo(), $sueldo));
            } elseif ($categoria === 'producto') {
                $sqlSub = "INSERT INTO Aviso_Producto (id_aviso, descripcion, precio)
                           VALUES (?, ?, ?)";
                $stmtSub = $conexion->prepare($sqlSub);
                $stmtSub->execute(array($idAviso, $aviso->getDescripcionProducto(), $aviso->getPrecio()));
            } elseif ($categoria === 'inmobiliario_venta') {
                $sqlSub = "INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
                           VALUES (?, ?, ?, ?, 'Venta', ?, NULL)";
                $stmtSub = $conexion->prepare($sqlSub);
                $stmtSub->execute(array(
                    $idAviso,
                    $aviso->getDireccion(),
                    $aviso->getTipoPropiedad(),
                    $aviso->getInmobiliaria(),
                    $aviso->getPrecioVenta()
                ));
            } elseif ($categoria === 'inmobiliario_alquiler') {
                $sqlSub = "INSERT INTO Aviso_Inmobiliario (id_aviso, direccion, tipo_inmueble, inmobiliaria, tipo_operacion, precio, gastos_comunes)
                           VALUES (?, ?, ?, ?, 'Alquiler', ?, ?)";
                $stmtSub = $conexion->prepare($sqlSub);
                $gc = ($aviso->getGastosComunes() === '' || $aviso->getGastosComunes() === null) ? null : $aviso->getGastosComunes();
                $stmtSub->execute(array(
                    $idAviso,
                    $aviso->getDireccion(),
                    $aviso->getTipoPropiedad(),
                    $aviso->getInmobiliaria(),
                    $aviso->getPrecioAlquiler(),
                    $gc
                ));
            } else {
                throw new Exception("Tipo de aviso no válido.");
            }

            $conexion->commit();
            return "Aviso creado con éxito.";
        } catch (Exception $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return "Error al crear el aviso: " . $error->getMessage();
        } finally {
            $conexion = null;
        }
    }

    private function obtenerIdRol($conexion, $nombreRol) {
        $sql = "SELECT id_rol FROM Rol WHERE nombre = ? LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(array($nombreRol));
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fila ? (int)$fila['id_rol'] : 2;
    }

    public function transformarFilaEnAviso($fila) {
        $catNombre = strtolower($fila['categoria']);
        $usuarioRef = $fila['email_autor'] ?? $fila['nombre_autor'];

        if ($catNombre === 'laboral') {
            return new AvisoLaboral(
                $fila['titulo'],
                $fila['nombre_autor'],
                $fila['telefono_autor'],
                $fila['resaltado'],
                $usuarioRef,
                $fila['empresa'],
                $fila['descripcion_cargo'],
                $fila['sueldo'],
                $fila['id_aviso'],
                $fila['fecha_creacion']
            );
        }

        if ($catNombre === 'producto') {
            return new AvisoProducto(
                $fila['titulo'],
                $fila['nombre_autor'],
                $fila['telefono_autor'],
                $fila['resaltado'],
                $usuarioRef,
                $fila['descripcion_producto'],
                $fila['precio_producto'],
                $fila['id_aviso'],
                $fila['fecha_creacion']
            );
        }

        if ($catNombre === 'inmobiliario') {
            $operacion = strtolower((string)$fila['tipo_operacion']);
            if ($operacion === 'venta') {
                return new AvisoInmobiliarioVenta(
                    $fila['titulo'],
                    $fila['nombre_autor'],
                    $fila['telefono_autor'],
                    $fila['resaltado'],
                    $usuarioRef,
                    $fila['direccion'],
                    $fila['tipo_inmueble'],
                    $fila['inmobiliaria'],
                    $fila['precio_inmueble'],
                    $fila['id_aviso'],
                    $fila['fecha_creacion']
                );
            }
            return new AvisoInmobiliarioAlquiler(
                $fila['titulo'],
                $fila['nombre_autor'],
                $fila['telefono_autor'],
                $fila['resaltado'],
                $usuarioRef,
                $fila['direccion'],
                $fila['tipo_inmueble'],
                $fila['inmobiliaria'],
                $fila['precio_inmueble'],
                $fila['gastos_comunes'],
                $fila['id_aviso'],
                $fila['fecha_creacion']
            );
        }

        return null;
    }

    public function obtenerTodosLosAvisos() {
        $listaAvisos = array();
        $conexion = $this->conectar();
        if (!$conexion) {
            return $listaAvisos;
        }

        try {
            $sql = "SELECT
                        a.id_aviso,
                        a.titulo,
                        a.fecha_creacion,
                        a.resaltado,
                        c.nombre AS categoria,
                        au.nombre AS nombre_autor,
                        au.telefono AS telefono_autor,
                        au.email AS email_autor,
                        al.empresa,
                        al.descripcion_cargo,
                        al.sueldo,
                        ap.descripcion AS descripcion_producto,
                        ap.precio AS precio_producto,
                        ai.direccion,
                        ai.tipo_inmueble,
                        ai.inmobiliaria,
                        ai.tipo_operacion,
                        ai.precio AS precio_inmueble,
                        ai.gastos_comunes
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
                $obj = $this->transformarFilaEnAviso($fila);
                if ($obj !== null) {
                    $listaAvisos[] = $obj;
                }
            }
            return $listaAvisos;
        } catch (PDOException $error) {
            error_log("Error al obtener avisos: " . $error->getMessage());
            return $listaAvisos;
        } finally {
            $conexion = null;
        }
    }

    public function cargarAvisosDePublicador($publicador) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return;
        }

        try {
            $filtro = $publicador->getNombreUsuario();
            $sql = "SELECT
                        a.id_aviso,
                        a.titulo,
                        a.fecha_creacion,
                        a.resaltado,
                        c.nombre AS categoria,
                        au.nombre AS nombre_autor,
                        au.telefono AS telefono_autor,
                        au.email AS email_autor,
                        al.empresa,
                        al.descripcion_cargo,
                        al.sueldo,
                        ap.descripcion AS descripcion_producto,
                        ap.precio AS precio_producto,
                        ai.direccion,
                        ai.tipo_inmueble,
                        ai.inmobiliaria,
                        ai.tipo_operacion,
                        ai.precio AS precio_inmueble,
                        ai.gastos_comunes
                    FROM Aviso a
                    INNER JOIN Categoria c ON a.id_categoria = c.id_categoria
                    INNER JOIN Autor au ON a.id_autor = au.id_autor
                    LEFT JOIN Aviso_Laboral al ON a.id_aviso = al.id_aviso
                    LEFT JOIN Aviso_Producto ap ON a.id_aviso = ap.id_aviso
                    LEFT JOIN Aviso_Inmobiliario ai ON a.id_aviso = ai.id_aviso
                    WHERE au.email = ? OR au.nombre = ?
                    ORDER BY c.nombre ASC, a.fecha_creacion ASC";

            $consulta = $conexion->prepare($sql);
            $consulta->execute(array($filtro, $filtro));

            foreach ($consulta as $fila) {
                $nuevoAviso = $this->transformarFilaEnAviso($fila);
                if ($nuevoAviso !== null) {
                    $publicador->agregarAviso($nuevoAviso);
                }
            }
        } catch (PDOException $error) {
            error_log("Error al obtener avisos del publicador: " . $error->getMessage());
        } finally {
            $conexion = null;
        }
    }

    public function borrarAvisoAdmin($idAviso) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return "Error de conexión con la base de datos.";
        }

        try {
            $conexion->beginTransaction();
            $sql = "DELETE FROM Aviso WHERE id_aviso = ?";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array((int)$idAviso));
            $conexion->commit();
            return "Operación de borrado realizada.";
        } catch (PDOException $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return "Error al borrar aviso.";
        } finally {
            $conexion = null;
        }
    }

    public function borrarAvisoPublicador($idAviso, $nombrePublicador) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return "Error de conexión con la base de datos.";
        }

        try {
            $conexion->beginTransaction();
            $sql = "DELETE a FROM Aviso a
                    INNER JOIN Autor au ON a.id_autor = au.id_autor
                    WHERE a.id_aviso = ? AND (au.email = ? OR au.nombre = ?)";
            $consulta = $conexion->prepare($sql);
            $consulta->execute(array((int)$idAviso, $nombrePublicador, $nombrePublicador));
            $conexion->commit();
            return "Operación de borrado realizada (si el aviso era tuyo).";
        } catch (PDOException $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            return "Error al borrar aviso.";
        } finally {
            $conexion = null;
        }
    }

    public function registrarAuditoriaBackup($nombreOEmailAutor, $tipoRespaldo, $archivoGenerado, $estado, $observaciones) {
        $conexion = $this->conectar();
        if (!$conexion) {
            return "Error de conexión con la base de datos.";
        }

        try {
            $sqlAutor = "SELECT id_autor FROM Autor WHERE nombre = ? OR email = ? LIMIT 1";
            $stmtAutor = $conexion->prepare($sqlAutor);
            $stmtAutor->execute(array($nombreOEmailAutor, $nombreOEmailAutor));
            $autor = $stmtAutor->fetch(PDO::FETCH_ASSOC);

            if (!$autor) {
                return "Autor no encontrado para registrar auditoría.";
            }

            $sql = "INSERT INTO Auditoria_Backup (id_autor, tipo_respaldo, archivo_generado, estado, observaciones)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute(array(
                (int)$autor['id_autor'],
                $tipoRespaldo,
                $archivoGenerado,
                $estado,
                $observaciones
            ));
            return "Auditoría de respaldo registrada.";
        } catch (PDOException $error) {
            error_log("Error al registrar auditoría de backup: " . $error->getMessage());
            return "Error al registrar auditoría de backup.";
        } finally {
            $conexion = null;
        }
    }

    public function obtenerAuditoriaBackups() {
        $conexion = $this->conectar();
        if (!$conexion) {
            return array();
        }

        try {
            $sql = "SELECT ab.id_backup, ab.fecha_hora, au.nombre AS autor, ab.tipo_respaldo,
                           ab.archivo_generado, ab.estado, ab.observaciones
                    FROM Auditoria_Backup ab
                    INNER JOIN Autor au ON ab.id_autor = au.id_autor
                    ORDER BY ab.fecha_hora DESC";
            $stmt = $conexion->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $error) {
            error_log("Error al consultar auditoría de backups: " . $error->getMessage());
            return array();
        } finally {
            $conexion = null;
        }
    }
}
?>