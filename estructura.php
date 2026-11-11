<?php

abstract class Aviso { // es abstracta porque el punto es que el aviso sea de un tipo, asi que no se puede crear un aviso sin tipo porque la clase es abstracta
    private $id;
    private $nombreAutor;
    private $telefonoAutor;
    private $resaltado;
    private $usuarioPublicador;
    private $fechaCreacion;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $id, $fechaCreacion) {
        $this->nombreAutor = $nombreAutor;
        $this->telefonoAutor = $telefonoAutor;
        $this->resaltado = $resaltado;
        $this->usuarioPublicador = $usuarioPublicador;
        $this->id = $id;
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id=$id;
    }

    public function getNombreAutor() {
        return $this->nombreAutor;
    }

    public function setNombreAutor($nombreAutor) {
        $this->nombreAutor=$nombreAutor;
    }

    public function getTelefonoAutor() {
        return $this->telefonoAutor;
    }

    public function setTelefonoAutor($telefonoAutor) {
        $this->telefonoAutor=$telefonoAutor;
    }

    public function getResaltado() {
        return $this->resaltado;
    }

    public function setResaltado($resaltado) {
        $this->resaltado=$resaltado;
    }

    public function getUsuarioPublicador() {
        return $this->usuarioPublicador;
    }

    public function setUsuarioPublicador($usuarioPublicador) {
        $this->usuarioPublicador=$usuarioPublicador;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion=$fechaCreacion;
    }

    abstract public function getCategoria(); // esto obliga a que las clases hijas tengan esto implementado porque tienen que devolver que a categoria pertenecen
}

class AvisoLaboral extends Aviso {
    private $empresa;
    private $descripcionCargo;
    private $sueldoMensual;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $empresa, $descripcionCargo, $sueldoMensual, $id, $fechaCreacion) {
        parent::__construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $id, $fechaCreacion);
        $this->empresa = $empresa;
        $this->descripcionCargo = $descripcionCargo;
        $this->sueldoMensual = $sueldoMensual;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa=$empresa;
    }

    public function getDescripcionCargo() {
        return $this->descripcionCargo;
    }

    public function setDescripcionCargo($descripcionCargo) {
        $this->descripcionCargo=$descripcionCargo;
    }

    public function getSueldoMensual() {
        return $this->sueldoMensual;
    }

    public function setSueldoMensual($sueldoMensual) {
        $this->sueldoMensual=$sueldoMensual;
    }

    public function getCategoria() {
        return 'laboral';
    }
}

class AvisoProducto extends Aviso {
    private $descripcionProducto;
    private $precio;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $descripcionProducto, $precio, $id, $fechaCreacion) {
        parent::__construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $id, $fechaCreacion);
        $this->descripcionProducto = $descripcionProducto;
        $this->precio = $precio;
    }

    public function getDescripcionProducto() {
        return $this->descripcionProducto;
    }

    public function setDescripcionProducto($descripcionProducto) {
        $this->descripcionProducto=$descripcionProducto;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio=$precio;
    }

    public function getCategoria() {
        return 'producto';
    }
}

abstract class AvisoInmobiliario extends Aviso { // es abstracta porque la idea es que no se pueda crear un objeto de esta clase si no es alquiler o compra
    private $direccion;
    private $tipoPropiedad;
    private $inmobiliaria;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $direccion, $tipoPropiedad, $inmobiliaria, $id, $fechaCreacion) {
        parent::__construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $id, $fechaCreacion);
        $this->direccion = $direccion;
        $this->tipoPropiedad = $tipoPropiedad;
        $this->inmobiliaria = $inmobiliaria;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setDireccion($direccion) {
        $this->direccion=$direccion;
    }

    public function getTipoPropiedad() {
        return $this->tipoPropiedad;
    }

    public function setTipoPropiedad($tipoPropiedad) {
        $this->tipoPropiedad=$tipoPropiedad;
    }

    public function getInmobiliaria() {
        return $this->inmobiliaria;
    }

    public function setInmobiliaria($inmobiliaria) {
        $this->inmobiliaria=$inmobiliaria;
    }
}

class AvisoInmobiliarioVenta extends AvisoInmobiliario {
    private $precioVenta;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $direccion, $tipoPropiedad, $inmobiliaria, $precioVenta, $id, $fechaCreacion) {
        parent::__construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $direccion, $tipoPropiedad, $inmobiliaria, $id, $fechaCreacion);
        $this->precioVenta = $precioVenta;
    }

    public function getPrecioVenta() {
        return $this->precioVenta;
    }

    public function setPrecioVenta($precioVenta) {
        $this->precioVenta=$precioVenta;
    }

    public function getCategoria() {
        return 'inmobiliario_venta';
    }
}

class AvisoInmobiliarioAlquiler extends AvisoInmobiliario {
    private $precioAlquiler;
    private $gastosComunes;

    public function __construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $direccion, $tipoPropiedad, $inmobiliaria, $precioAlquiler, $gastosComunes, $id, $fechaCreacion) {
        parent::__construct($nombreAutor, $telefonoAutor, $resaltado, $usuarioPublicador, $direccion, $tipoPropiedad, $inmobiliaria, $id, $fechaCreacion);
        $this->precioAlquiler = $precioAlquiler;
        $this->gastosComunes = $gastosComunes;
    }

    public function getPrecioAlquiler() {
        return $this->precioAlquiler;
    }

    public function setPrecioAlquiler($precioAlquiler) {
        $this->precioAlquiler=$precioAlquiler;
    }

    public function getGastosComunes() {
        return $this->gastosComunes;
    }

    public function setGastosComunes($gastosComunes) {
        $this->gastosComunes=$gastosComunes;
    }

    public function getCategoria() {
        return 'inmobiliario_alquiler';
    }
}
