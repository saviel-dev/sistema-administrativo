<?php
require_once __DIR__ . '/../modelo/Empresa_Model.php';

class EmpresaController {

    private $empresa;

    public function __construct() {
        $this->empresa = new Empresa();
    }

    public function obtenerPerfil() {
        $data = $this->empresa->obtenerPerfil();
        echo json_encode(["success" => true, "data" => $data]);
    }

    public function actualizarPerfil() {
        $post = $_POST;

        // Validar que venga ID para update, si no, responder error
        if (!isset($post['id_empresa'])) {
            echo json_encode(["success" => false, "message" => "Falta ID de empresa"]);
            return;
        }

        $res = $this->empresa->actualizarPerfil($post);
        echo json_encode(["success" => $res]);
    }
}