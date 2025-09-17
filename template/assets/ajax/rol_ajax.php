<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Rol_Controller.php';

$controller = new RolController();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listarRoles':
        $controller->listarRoles();
        break;





    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
