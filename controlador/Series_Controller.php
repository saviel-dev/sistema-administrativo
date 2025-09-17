<?php
require_once __DIR__ . '/../modelo/Series_Model.php';

class SerieController {
    private $serie;

    public function __construct() {
        $this->serie = new SerieModel();
    }

    public function listarPorSede() {
        $id_sede = $_GET['id'] ?? null;
        if (!$id_sede) {
            echo json_encode(['success' => false, 'message' => 'ID de sede no enviado']);
            return;
        }

        $result = $this->serie->obtenerPorSede($id_sede);
        echo json_encode(['success' => true, 'data' => $result]);
    }

    public function registrarSerie() {
        $id_empresa = $_POST['id_empresa'] ?? null;
        $id_sede = $_POST['id_sede'] ?? null;
        $tipo = $_POST['tipo_comprobante'] ?? '';
        $serie = $_POST['serie'] ?? '';
        $correlativo = $_POST['correlativo_actual'] ?? 1;

        if (!$id_empresa || !$id_sede || !$tipo || !$serie) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $res = $this->serie->crearSerie($id_empresa, $id_sede, $tipo, $serie, $correlativo);
        echo json_encode(['success' => $res]);
    }

    public function editarSerie() {
        $id_serie = $_POST['id_serie_documento'] ?? null;
        $tipo = $_POST['tipo_comprobante'] ?? '';
        $serie = $_POST['serie'] ?? '';
        $correlativo = $_POST['correlativo_actual'] ?? 1;
        $estado = $_POST['estado'] ?? 1;

        if (!$id_serie || !$tipo || !$serie) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            return;
        }

        $res = $this->serie->actualizarSerie($id_serie, $tipo, $serie, $correlativo, $estado);
        echo json_encode(['success' => $res]);
    }

    public function eliminarSerie() {
        $id = $_POST['id_serie_documento'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no enviado']);
            return;
        }

        $res = $this->serie->eliminarSerie($id);
        echo json_encode(['success' => $res]);
    }

    public function obtenerTiposDocumentoVenta() {
        $tipos = $this->serie->obtenerTiposDocumento();
        echo json_encode(['success' => true, 'data' => $tipos]);
    }

    public function obtenerSerie() {
    $id_serie = $_GET['id'] ?? null;

    if (!$id_serie) {
        echo json_encode(['success' => false, 'message' => 'ID de serie no enviado']);
        return;
    }

    $data = $this->serie->obtenerSeriePorId($id_serie);

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Serie no encontrada']);
    }
}
}