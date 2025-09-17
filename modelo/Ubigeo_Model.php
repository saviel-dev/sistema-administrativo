<?php
require_once "BaseModel.php";

class Ubigeo extends BaseModel {

  public function obtenerDepartamentos() {
    $sql = "SELECT DISTINCT departamento FROM ubigeo_inei ORDER BY departamento";
    return $this->consultar($sql);
  }

  public function obtenerProvinciasPorDepartamento($departamento) {
    $sql = "SELECT DISTINCT provincia FROM ubigeo_inei WHERE departamento = '$departamento' ORDER BY provincia";
    return $this->consultar($sql);
  }

  public function obtenerDistritosPorProvincia($departamento, $provincia) {
    $sql = "SELECT codigo_ubigeo, distrito FROM ubigeo_inei WHERE departamento = '$departamento' AND provincia = '$provincia' ORDER BY distrito";
    return $this->consultar($sql);
  }

  public function buscarUbigeoPorCodigo($codigo) {
  $sql = "SELECT departamento, provincia, distrito FROM ubigeo_inei WHERE codigo_ubigeo = '$codigo' LIMIT 1";
  return $this->consultaUnica($sql);
}
}