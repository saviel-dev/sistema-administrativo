<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Usuario_Controller.php';

$controller = new UsuarioController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'registrar':
        $controller->registrar();
        break;

    case 'validarDocumento':
        $controller->validarDocumento();
        break;

    case 'login':
        $controller->login();
        break;

    case 'listarUsuarios':
        $controller->obtenerUsuarios();
        break;


    case 'cambiarEstado':
        $controller->cambiarEstado();
        break;

    case 'cambiarRol':
        $controller->cambiarRol();
        break;

    case 'cambiarSede':
        $controller->cambiarSede();
        break;

        case 'modificarUsuario':
    $controller->modificar();
    break;

    case 'obtenerUsuario':
    $controller->obtenerUsuario();
    break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
