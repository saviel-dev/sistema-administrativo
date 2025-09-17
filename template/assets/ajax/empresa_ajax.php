<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Empresa_Controller.php';

$controller = new EmpresaController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'obtenerPerfil':
        $controller->obtenerPerfil();
        break;

    case 'actualizarPerfil':
        $controller->actualizarPerfil();
        break;

    default:
        echo json_encode(["success" => false, "message" => "Acción no válida"]);
        break;
}