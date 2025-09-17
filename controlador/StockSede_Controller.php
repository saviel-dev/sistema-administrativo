<?php
require_once __DIR__ . '/../modelo/StockSede_Model.php';

class StockSedeController {
    private $stock;
    public function __construct(){ $this->stock = new StockSede(); }

    public function listar() {
        header('Content-Type: application/json');
        $id_sede = (int)($_GET['id_sede'] ?? 0);
        echo json_encode(["success"=>true,"data"=>$this->stock->listar($id_sede)]);
    }

    public function obtener() {
        $id_sede = (int)($_GET['id_sede'] ?? 0);
        $id_var  = (int)($_GET['id_producto_variante'] ?? 0);
        if (!$id_sede || !$id_var) { echo json_encode(["success"=>false,"message"=>"Parámetros inválidos"]); return; }
        echo json_encode(["success"=>true,"data"=>$this->stock->obtener($id_sede,$id_var)]);
    }

    public function actualizarMin() {
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success"=>false,"message"=>"Token CSRF inválido"]); return;
        }
        $ok = $this->stock->actualizarMin(
            (int)($_POST['id_sede'] ?? 0),
            (int)($_POST['id_producto_variante'] ?? 0),
            (float)($_POST['stock_min'] ?? 0)
        );
        echo json_encode(["success"=>$ok]);
    }
}