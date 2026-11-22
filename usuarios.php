<?php
include 'avisos.php';

abstract class Usuario {
    private $nombreUsuario;
    private $contrasena;
    private $email;
    private $telefono;

    public function __construct($nombreUsuario, $contrasena, $email = "", $telefono = "") {
        $this->nombreUsuario = $nombreUsuario;
        $this->contrasena = $contrasena;
        $this->email = $email;
        $this->telefono = $telefono;
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

    public function getEmail() {
        return $this->email;
    }
    public function setEmail($email) {
        $this->email = $email;
    }

    public function getTelefono() {
        return $this->telefono;
    }
    public function setTelefono($telefono) {
        $this->telefono = $telefono;
    }
    
    abstract public function getRol();
}

class Publicador extends Usuario {
    // AGREGACIÓN: Un Publicador contiene una lista de objetos Aviso
    private $misAvisos = array();

    public function __construct($nombreUsuario, $contrasena, $email = "", $telefono = "") {
        parent::__construct($nombreUsuario, $contrasena, $email, $telefono);
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
        return 'administrador';
    }
}
?>
