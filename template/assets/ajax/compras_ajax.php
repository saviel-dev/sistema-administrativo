<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../controlador/Compras_Controller.php';

$ctl = new ComprasController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':        $ctl->listar(); break;
    case 'obtener':       $ctl->obtener(); break;
    case 'registrar':     $ctl->registrar(); break;
    case 'cambiarEstado': $ctl->cambiarEstado(); break;
    case 'agregarItem':   $ctl->agregarItem(); break;
    case 'eliminarItem':  $ctl->eliminarItem(); break;
    default: echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}