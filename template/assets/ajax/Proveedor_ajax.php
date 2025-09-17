<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Proveedor_Controller.php';

$controller = new ProveedorController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'registrar':
        $controller->registrar();
        break;

    case 'modificar':
        $controller->modificar();
        break;

    case 'validarDocumento':
        $controller->validarDocumento();
        break;

    case 'obtenerProveedor':
        $controller->obtenerProveedor();
        break;

    case 'listarProveedores':
        $controller->listarProveedores();
        break;

    case 'cambiarEstado':
        $controller->cambiarEstado();
        break;

    case 'actualizarLimiteCredito':
        $controller->actualizarLimiteCredito();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}