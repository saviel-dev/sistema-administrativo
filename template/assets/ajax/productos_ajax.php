<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Producto_Controller.php';

$controller = new ProductoController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'buscarProductos':
        $controller->buscarProductos();
        break;

    case 'buscarProductosCompras':
        $controller->buscarProductosCompras();
        break;

    case 'obtenerCategorias':
        $controller->obtenerCategorias();
        break;

    case 'obtenerCategoriasClasificacionGeneral':
        $controller->obtenerCategoriasClasificacionGeneral();
        break;

    case 'listarUnidadMedidas':
        $controller->listarUnidadMedidas();
        break;

    case 'listarMarcasVehiculos':
        $controller->listarMarcasVehiculo();
        break;

    case 'crearCategoria':
        $controller->crearCategoria();
        break;

    case 'generarSKU':
        $controller->generarSKU();
        break;

    case 'guardarProducto':
        $controller->guardarProducto();
        break;

    
    case 'actualizarPrecioVentaVariante':
        $controller->actualizarPrecioVentaVariante();
        break;

        case 'subirImagenProducto':
      $controller->subirImagenProducto();
      break;

      case 'actualizarProducto':
    $controller->actualizarProducto();
    break;

    case 'exportarExcel':
    $controller->exportarExcel();
    break;

    case 'crearPrecioSede':
    $controller->crearPrecioSede();
    break;



    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}