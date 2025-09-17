<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Cliente_Controller.php';

$controller = new ClienteController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        $controller->listar();
        break;

    case 'buscar':
        $controller->buscar();
        break;

    case 'registrar':
        $controller->registrar();
        break;

    case 'modificar':
        $controller->modificar();
        break;

    case 'cambiarEstado':
        $controller->cambiarEstado();
        break;

    case 'consultarSunatRUC':
        $controller->consultarSunatRUC();
        break;

    case 'consultarReniecDNI':
        $controller->consultarReniecDNI();
        break;

    case 'buscarPorDocumento':
        $controller->buscarPorDocumento(); // ✔ usa método del controlador
        break;

    case 'obtenerUltimoId':
        $id = $controller->obtenerUltimoId();
        echo json_encode(['success' => true, 'id' => $id]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}