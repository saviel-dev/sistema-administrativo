<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../controlador/InventarioMov_Controller.php';

$ctl = new InventarioMovController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar': $ctl->listar(); break;
    default: echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}