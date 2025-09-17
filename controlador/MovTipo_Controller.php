<?php
require_once __DIR__ . '/../modelo/MovTipo_Model.php';

class MovTipoController {
    private $mov;
    public function __construct(){ $this->mov = new MovTipo(); }

    public function listar() {
        header('Content-Type: application/json');
        echo json_encode(["success"=>true,"data"=>$this->mov->listar()]);
    }

    public function registrar() {
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success"=>false,"message"=>"Token CSRF inválido"]); return;
        }
        $ok = $this->mov->registrar([
            "codigo"=>trim($_POST['codigo'] ?? ''),
            "descripcion"=>trim($_POST['descripcion'] ?? ''),
            "signo"=>(int)($_POST['signo'] ?? 1),
        ]);
        echo json_encode(["success"=>$ok>0,"id"=>$ok ?: 0]);
    }
}