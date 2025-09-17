<?php
require_once "BaseModel.php";

class RolModel extends BaseModel
{


    public function obtenerTodosLosRoles() {
        $sql = "SELECT id_rol, nombre_rol FROM rol";
        return $this->consultar($sql);
    }

}
