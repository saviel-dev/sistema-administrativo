<?php
require_once __DIR__ . '/../modelo/TipoMoneda_Model.php';

class TipoMonedaController {
    private $model;

    public function __construct() {
        $this->model = new TipoMonedaModel();
    }

    public function obtenerTodas() {
        $monedas = $this->model->obtenerTodas();
        echo json_encode([
            'success' => true,
            'data' => $monedas
        ]);
    }
}
?>