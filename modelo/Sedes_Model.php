<?php
require_once "BaseModel.php";

class SedeModel extends BaseModel
{
    public function obtenerTodasLasSedes() {
        $sql = "SELECT * FROM sede";
        return $this->consultar($sql);
    }

   public function agregarNuevaSede($nombre, $seudonimo, $direccion, $telefono) {
    $sql = "INSERT INTO sede (nombre, seudonimo_sede, direccion, telefono, estado_sede) 
            VALUES (?, ?, ?, ?, ?)";
    $params = [$nombre, $seudonimo, $direccion, $telefono, 'Activo'];
    $types  = "sssss"; // 5 parámetros de tipo string

    return $this->insertarPreparado($sql, $params, $types);
}

public function cambiarEstadoSede($id_sede, $estado) {
    $sql = "UPDATE sede SET estado_sede = ? WHERE id_sede = ?";
    $params = [$estado, $id_sede];
    $types = "si";
    return $this->actualizarPreparado($sql, $params, $types);
}

public function obtenerSede($id) {
    $sql = "SELECT * FROM sede WHERE id_sede = ?";
    $params = [$id];
    $types = "i";
    return $this->consultaPreparadaUnica($sql, $params, $types); // ya retorna una sola fila
}
public function editarSede($id, $nombre, $seudonimo, $direccion, $telefono) {
    $sql = "UPDATE sede SET nombre = ?, seudonimo_sede = ?, direccion = ?, telefono = ? WHERE id_sede = ?";
    $params = [$nombre, $seudonimo, $direccion, $telefono, $id];
    $types = "ssssi";
    return $this->actualizarPreparado($sql, $params, $types);
}


}