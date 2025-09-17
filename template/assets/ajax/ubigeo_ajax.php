<?php
header('Content-Type: application/json');

// Habilita errores (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../../modelo/Ubigeo_Model.php';
require_once __DIR__ . '/../../../controlador/Ubigeo_Controller.php';

$ctrl = new UbigeoController();
$action = $_GET['action'] ?? '';

switch ($action) {
  case 'departamentos':
    $ctrl->departamentos();
    break;
  case 'provincias':
    $ctrl->provincias();
    break;
  case 'distritos':
    $ctrl->distritos();
    break;
    case 'buscarUbigeo':
    $ctrl->buscarUbigeo();
    break;
  default:
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    break;
}



