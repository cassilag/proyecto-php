<?php
require_once 'avisos.php';

abstract class Usuario {
    private $nombreUsuario;
    private $contrasena;

    public function __construct($nombreUsuario, $contrasena) {
        $this->nombreUsuario = $nombreUsuario;
        $this->contrasena = $contrasena;
    }

    public function getNombreUsuario() {
        return $this->nombreUsuario;
    }

    public function setNombreUsuario($nombreUsuario) {
        $this->nombreUsuario = $nombreUsuario;
    }

    public function getContrasena() {
        return $this->contrasena;
    }

    public function setContrasena($contrasena) {
        $this->contrasena = $contrasena;
    }

    abstract public function getRol();
}

class Publicador extends Usuario {
    // AGREGACIÓN: Un Publicador contiene una lista de objetos Aviso
    private $misAvisos = array();

    public function __construct($nombreUsuario, $contrasena) {
        parent::__construct($nombreUsuario, $contrasena);
    }

    public function agregarAviso(Aviso $aviso) {
        $this->misAvisos[] = $aviso;
    }

    public function getMisAvisos() {
        return $this->misAvisos;
    }

    public function getRol() {
        return 'publicador';
    }
}

class Administrador extends Usuario {
    public function __construct($nombreUsuario, $contrasena) {
        parent::__construct($nombreUsuario, $contrasena);
    }

    public function getRol() {
        return 'admin';
    }
}
?>
