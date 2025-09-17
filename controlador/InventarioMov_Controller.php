<?php
require_once __DIR__ . '/../modelo/InventarioMov_Model.php';

class InventarioMovController {
    private $kardex;
    public function __construct(){ $this->kardex = new InventarioMov(); }

    public function listar() {
        header('Content-Type: application/json');
        $id_sede = (int)($_GET['id_sede'] ?? 0);
        $id_producto = (int)($_GET['id_producto'] ?? 0); // aquí pasas id_variante
        echo json_encode(["success"=>true,"data"=>$this->kardex->listar($id_sede,$id_producto)]);
    }
}