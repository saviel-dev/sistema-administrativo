<?php
// models/ModeloDocumento.php
require_once "BaseModel.php";

class DocumentoIdentificacion extends BaseModel
{
  public function listarTiposDocumento()
  {
    $sql = "SELECT id_docidentificacion_sunat ,abreviatura_docIdentificacion FROM doc_identificacion ORDER BY id_docidentificacion_sunat";
    return $this->consultar($sql);
  }

  public function listarTiposDocumentoRegistroUsuario()
  {
    $sql = "SELECT * FROM doc_identificacion WHERE id_docidentificacion_sunat IN ('1', '4', '7');";
    return $this->consultar($sql);
  }
}
