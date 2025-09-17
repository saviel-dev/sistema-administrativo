<?php
require_once __DIR__ . '/../modelo/Sedes_Model.php';

class SedesController {

    private $usuario;

    public function __construct() {
        $this->usuario = new SedeModel();
    }


public function listarSedes() {
    $data = $this->usuario->obtenerTodasLasSedes();
    echo json_encode(['data' => $data]);
}

public function agregarSede() {
    $nombre = $_POST['nombre'] ?? '';
    $seudonimo = $_POST['seudonimo_sede'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (empty($nombre) || empty($direccion)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos.']);
        return;
    }

    $result = $this->usuario->agregarNuevaSede($nombre, $seudonimo, $direccion, $telefono);
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar sede']);
    }
}

public function cambiarEstadoSede() {
    $id = $_POST['id_sede'] ?? 0;
    $estado = $_POST['estado'] ?? '';

    if (!$id || !in_array($estado, ['Activo', 'Suspendido', 'Inactivo'])) {
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
        return;
    }

    $result = $this->usuario->cambiarEstadoSede($id, $estado);
    echo json_encode(['success' => $result]);
}

public function obtenerSedePorId() {
    $id = $_GET['id'] ?? 0;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        return;
    }

    $sede = $this->usuario->obtenerSede($id);
    if ($sede) {
        echo json_encode(['success' => true, 'data' => $sede]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Sede no encontrada']);
    }
}

public function editarSede() {
    $id = $_POST['id_sede'] ?? 0;
    $nombre = $_POST['nombre'] ?? '';
    $seudonimo = $_POST['seudonimo_sede'] ?? '';
    $direccion = $_POST['direccion'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    if (!$id || !$nombre || !$direccion) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }

    $res = $this->usuario->editarSede($id, $nombre, $seudonimo, $direccion, $telefono);
    echo json_encode(['success' => $res]);
}

}