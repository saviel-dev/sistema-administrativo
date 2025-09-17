<?php
require_once __DIR__ . '/../modelo/Ubigeo_Model.php';

class UbigeoController {

  private $modelo;

  public function __construct() {
    $this->modelo = new Ubigeo();
  }

  public function departamentos() {
    $data = $this->modelo->obtenerDepartamentos();
    echo json_encode(["success" => true, "data" => $data]);
  }

  public function provincias() {
    $dep = $_GET['departamento'] ?? '';
    $data = $this->modelo->obtenerProvinciasPorDepartamento($dep);
    echo json_encode(["success" => true, "data" => $data]);
  }

  public function distritos() {
    $dep = $_GET['departamento'] ?? '';
    $prov = $_GET['provincia'] ?? '';
    $data = $this->modelo->obtenerDistritosPorProvincia($dep, $prov);
    echo json_encode(["success" => true, "data" => $data]);
  }

  public function buscarUbigeo() {
  $codigo = $_GET['codigo'] ?? '';
  $data = $this->modelo->buscarUbigeoPorCodigo($codigo);
  echo json_encode(["success" => $data ? true : false, "data" => $data]);
}
}