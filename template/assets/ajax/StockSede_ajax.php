<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../controlador/StockSede_Controller.php';

$ctl = new StockSedeController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':        $ctl->listar(); break;
    case 'obtener':       $ctl->obtener(); break;
    case 'actualizarMin': $ctl->actualizarMin(); break;
    default: echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}