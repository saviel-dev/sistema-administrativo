<?php
require_once __DIR__ . '/../modelo/Rol_Model.php';

class RolController {

    private $usuario;

    public function __construct() {
        $this->usuario = new RolModel();
    }

    public function listarRoles() {
    $data = $this->usuario->obtenerTodosLosRoles();
    echo json_encode(['data' => $data]);
}


 
}