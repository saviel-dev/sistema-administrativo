<?php
require_once __DIR__ . '/../modelo/TiposDocumentosVenta_Model.php';

class TiposDocumentosVentaController
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new TiposDocumentosVentaModel();
    }

    public function listar()
    {
        $result = $this->modelo->listar();
        echo json_encode(['success' => true, 'data' => $result]);
    }

    public function obtener()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false]);
            return;
        }

        $data = $this->modelo->obtener($id);
        echo json_encode(['success' => true, 'data' => $data]);
    }

    public function crear()
    {
        $data = [
            'CodigoSunat_TipoDocumentoVenta' => $_POST['codigo'],
            'SeriePrincipal' => $_POST['principal'] ?? null,
            'SerieAlternativa' => $_POST['alternativa'] ?? null,
            'Descripcion' => $_POST['descripcion']
        ];

        $r = $this->modelo->crear($data);
        echo json_encode(['success' => $r]);
    }

    public function actualizar()
    {
        $data = [
            'id' => $_POST['id'],
            'CodigoSunat_TipoDocumentoVenta' => $_POST['codigo'],
            'SeriePrincipal' => $_POST['principal'] ?? null,
            'SerieAlternativa' => $_POST['alternativa'] ?? null,
            'Descripcion' => $_POST['descripcion']
        ];

        $r = $this->modelo->actualizar($data);
        echo json_encode(['success' => $r]);
    }

    public function eliminar()
    {
        $id = $_POST['id'] ?? null;
        $r = $this->modelo->eliminar($id);
        echo json_encode(['success' => $r]);
    }
}