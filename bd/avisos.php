<?php

abstract class Aviso {
    private $id;
    private $titulo;
    private $nombreAutor;
    private $telefonoAutor;
    private $resaltado;
    private $usuarioPublicador;
    private $fechaCreacion;

    public function __construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $id,$fechaCreacion) {
        $this->titulo =$titulo;
        $this->nombreAutor =$nombreAutor;
        $this->telefonoAutor =$telefonoAutor;
        $this->resaltado =$resaltado;
        $this->usuarioPublicador =$usuarioPublicador;
        $this->id =$id;
        $this->fechaCreacion =$fechaCreacion;
    }

    protected function esc($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }

    protected function envolverResaltado($html) {
        if ((int)$this->resaltado === 1) {
            return "<strong><big>" . $html . "</big></strong>";
        }
        return $html;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id =$id;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function setTitulo($titulo) {
        $this->titulo =$titulo;
    }

    public function getNombreAutor() {
        return $this->nombreAutor;
    }

    public function setNombreAutor($nombreAutor) {
        $this->nombreAutor =$nombreAutor;
    }

    public function getTelefonoAutor() {
        return $this->telefonoAutor;
    }

    public function setTelefonoAutor($telefonoAutor) {
        $this->telefonoAutor =$telefonoAutor;
    }

    public function getResaltado() {
        return $this->resaltado;
    }

    public function setResaltado($resaltado) {
        $this->resaltado =$resaltado;
    }

    public function getUsuarioPublicador() {
        return $this->usuarioPublicador;
    }

    public function setUsuarioPublicador($usuarioPublicador) {
        $this->usuarioPublicador =$usuarioPublicador;
    }

    public function getFechaCreacion() {
        return $this->fechaCreacion;
    }

    public function setFechaCreacion($fechaCreacion) {
        $this->fechaCreacion =$fechaCreacion;
    }

    abstract public function getCategoria();

    public function obtenerDatosBasicos() {
        return "ID: " . $this->esc($this->id) .
               " | Título: " . $this->esc($this->titulo) .
               " | Fecha: " . $this->esc($this->fechaCreacion) .
               " | Autor: " . $this->esc($this->nombreAutor) .
               " (Tel: " . $this->esc($this->telefonoAutor) . ")";
    }

    abstract public function mostrarDetalle();
}

class AvisoLaboral extends Aviso {
    private $empresa;
    private $descripcionCargo;
    private $sueldoMensual;

    public function __construct($titulo,$nombreAutor, $telefonoAutor,$resaltado, $usuarioPublicador,$empresa, $descripcionCargo,$sueldoMensual, $id,$fechaCreacion) {
        parent::__construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $id,$fechaCreacion);
        $this->empresa =$empresa;
        $this->descripcionCargo =$descripcionCargo;
        $this->sueldoMensual =$sueldoMensual;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa =$empresa;
    }

    public function getDescripcionCargo() {
        return $this->descripcionCargo;
    }

    public function setDescripcionCargo($descripcionCargo) {
        $this->descripcionCargo =$descripcionCargo;
    }

    public function getSueldoMensual() {
        return $this->sueldoMensual;
    }

    public function setSueldoMensual($sueldoMensual) {
        $this->sueldoMensual =$sueldoMensual;
    }

    public function getCategoria() {
        return 'laboral';
    }

    public function mostrarDetalle() {
        $sueldoTexto = ($this->sueldoMensual !== null && $this->sueldoMensual !== '') ? "$" . $this->esc($this->sueldoMensual) : "A convenir";
        $texto = "[LABORAL] " . $this->obtenerDatosBasicos() .
                 " | Empresa: " . $this->esc($this->empresa) .
                 " | Cargo: " . $this->esc($this->descripcionCargo) .
                 " | Sueldo: " . $sueldoTexto;
        return $this->envolverResaltado($texto);
    }
}

class AvisoProducto extends Aviso {
    private $descripcionProducto;
    private $precio;

    public function __construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $descripcionProducto,$precio, $id,$fechaCreacion) {
        parent::__construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $id,$fechaCreacion);
        $this->descripcionProducto =$descripcionProducto;
        $this->precio =$precio;
    }

    public function getDescripcionProducto() {
        return $this->descripcionProducto;
    }

    public function setDescripcionProducto($descripcionProducto) {
        $this->descripcionProducto =$descripcionProducto;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function setPrecio($precio) {
        $this->precio =$precio;
    }

    public function getCategoria() {
        return 'producto';
    }

    public function mostrarDetalle() {
        $texto = "[PRODUCTO] " . $this->obtenerDatosBasicos() .
                 " | Producto: " . $this->esc($this->descripcionProducto) .
                 " | Precio: $" . $this->esc($this->precio);
        return $this->envolverResaltado($texto);
    }
}

abstract class AvisoInmobiliario extends Aviso {
    private $direccion;
    private $tipoPropiedad;
    private $inmobiliaria;

    public function __construct($titulo,$nombreAutor, $telefonoAutor,$resaltado, $usuarioPublicador,$direccion, $tipoPropiedad,$inmobiliaria, $id,$fechaCreacion) {
        parent::__construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $id,$fechaCreacion);
        $this->direccion =$direccion;
        $this->tipoPropiedad =$tipoPropiedad;
        $this->inmobiliaria =$inmobiliaria;
    }

    public function getDireccion() {
        return $this->direccion;
    }

    public function setDireccion($direccion) {
        $this->direccion =$direccion;
    }

    public function getTipoPropiedad() {
        return $this->tipoPropiedad;
    }

    public function setTipoPropiedad($tipoPropiedad) {
        $this->tipoPropiedad =$tipoPropiedad;
    }

    public function getInmobiliaria() {
        return $this->inmobiliaria;
    }

    public function setInmobiliaria($inmobiliaria) {
        $this->inmobiliaria =$inmobiliaria;
    }
}

class AvisoInmobiliarioVenta extends AvisoInmobiliario {
    private $precioVenta;

    public function __construct($titulo, $nombreAutor,$telefonoAutor, $resaltado,$usuarioPublicador, $direccion,$tipoPropiedad, $inmobiliaria,$precioVenta, $id,$fechaCreacion) {
        parent::__construct($titulo,$nombreAutor, $telefonoAutor,$resaltado, $usuarioPublicador,$direccion, $tipoPropiedad,$inmobiliaria, $id,$fechaCreacion);
        $this->precioVenta =$precioVenta;
    }

    public function getPrecioVenta() {
        return $this->precioVenta;
    }

    public function setPrecioVenta($precioVenta) {
        $this->precioVenta =$precioVenta;
    }

    public function getCategoria() {
        return 'inmobiliario_venta';
    }

    public function mostrarDetalle() {
        $texto = "[INMOBILIARIO - VENTA] " . $this->obtenerDatosBasicos() .
                 " | Dirección: " . $this->esc($this->getDireccion()) .
                 " | Tipo: " . $this->esc($this->getTipoPropiedad()) .
                 " | Inmobiliaria: " . $this->esc($this->getInmobiliaria()) .
                 " | Precio Venta: $" . $this->esc($this->precioVenta);
        return $this->envolverResaltado($texto);
    }
}

class AvisoInmobiliarioAlquiler extends AvisoInmobiliario {
    private $precioAlquiler;
    private $gastosComunes;

    public function __construct($titulo,$nombreAutor, $telefonoAutor,$resaltado, $usuarioPublicador,$direccion, $tipoPropiedad,$inmobiliaria, $precioAlquiler,$gastosComunes, $id,$fechaCreacion) {
        parent::__construct($titulo,$nombreAutor, $telefonoAutor,$resaltado, $usuarioPublicador,$direccion, $tipoPropiedad,$inmobiliaria, $id,$fechaCreacion);
        $this->precioAlquiler =$precioAlquiler;
        $this->gastosComunes =$gastosComunes;
    }

    public function getPrecioAlquiler() {
        return $this->precioAlquiler;
    }

    public function setPrecioAlquiler($precioAlquiler) {
        $this->precioAlquiler =$precioAlquiler;
    }

    public function getGastosComunes() {
        return $this->gastosComunes;
    }

    public function setGastosComunes($gastosComunes) {
        $this->gastosComunes =$gastosComunes;
    }

    public function getCategoria() {
        return 'inmobiliario_alquiler';
    }

    public function mostrarDetalle() {
        $gcTexto = ($this->gastosComunes !== null && $this->gastosComunes !== '') ? "$" . $this->esc($this->gastosComunes) : "$0";
        $texto = "[INMOBILIARIO - ALQUILER] " . $this->obtenerDatosBasicos() .
                 " | Dirección: " . $this->esc($this->getDireccion()) .
                 " | Tipo: " . $this->esc($this->getTipoPropiedad()) .
                 " | Inmobiliaria: " . $this->esc($this->getInmobiliaria()) .
                 " | Alquiler Mensual: $" . $this->esc($this->precioAlquiler) .
                 " | Gastos Comunes: " . $gcTexto;
        return $this->envolverResaltado($texto);
    }
}
?>