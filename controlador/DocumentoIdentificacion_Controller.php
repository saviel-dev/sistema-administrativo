<?php
require_once __DIR__ . '/../modelo/DocumentoIdentificacion_Model.php';

class DocumentoIdentidificacion_Controller {
  private $modelo;

  public function __construct() {
    $this->modelo = new DocumentoIdentificacion();
  }

  public function listar() {
    $data = $this->modelo->listarTiposDocumento();
    echo json_encode(["success" => true, "data" => $data]);
  }

  public function listarDocumentosRegistroUsuario() {
    $data = $this->modelo->listarTiposDocumentoRegistroUsuario();
    echo json_encode(["success" => true, "data" => $data]);
  }
}