<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../controlador/TiposDocumentosVenta_Controller.php';

$controller = new TiposDocumentosVentaController();

switch ($_GET['action']) {
    case 'listar':
        $controller->listar();
        break;

    case 'obtener':
        $controller->obtener();
        break;

    case 'crear':
        $controller->crear();
        break;

    case 'actualizar':
        $controller->actualizar();
        break;

    case 'eliminar':
        $controller->eliminar();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;
}