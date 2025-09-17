<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../controlador/MovTipo_Controller.php';

$ctl = new MovTipoController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'listar':    $ctl->listar(); break;
    case 'registrar': $ctl->registrar(); break;
    default: echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}