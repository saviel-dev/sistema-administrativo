<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Sedes_Controller.php';

$controller = new SedesController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listarSedes':
        $controller->listarSedes();
        break;

    case 'agregarSede':
        $controller->agregarSede();
        break;

        case 'cambiarEstado':
    $controller->cambiarEstadoSede();
    break;

    case 'obtenerSede':
    $controller->obtenerSedePorId();
    break;

case 'editarSede':
    $controller->editarSede();
    break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
