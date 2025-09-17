<?php
header('Content-Type: application/json');

// Habilita errores (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once __DIR__ . '/../../../controlador/DocumentoIdentificacion_Controller.php';
require_once __DIR__ . '/../../../modelo/DocumentoIdentificacion_Model.php';

$ctrl = new DocumentoIdentidificacion_Controller();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':
        $ctrl->listar();
        break;

    case 'listarDocumentosRegistroUsuario':
        $ctrl->listarDocumentosRegistroUsuario();
        break;

   
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
