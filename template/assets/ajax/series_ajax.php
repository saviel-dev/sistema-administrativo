<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../../controlador/Series_Controller.php';

$controller = new SerieController();

switch ($_GET['action']) {
    case 'porSede':
        $controller->listarPorSede();
        break;

    case 'registrarSerie':
        $controller->registrarSerie();
        break;

    case 'editarSerie':
        $controller->editarSerie();
        break;

    case 'eliminarSerie':
        $controller->eliminarSerie();
        break;

    case 'tiposDocumento':
        $controller->obtenerTiposDocumentoVenta();
        break;

        case 'obtenerSerie':
    $controller->obtenerSerie(); // Este método ya debe estar creado en el controller
    break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
        break;

}
