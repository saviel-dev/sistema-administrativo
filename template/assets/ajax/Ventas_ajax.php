<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Ventas_Controller.php';

$controller = new VentasController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listarTiposCPE':
        $controller->listarTiposCPE();
        break;

    case 'sugerirSerieYCorrelativo':
        $controller->sugerirSerieYCorrelativo();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}