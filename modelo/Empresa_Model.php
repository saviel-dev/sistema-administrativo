<?php
require_once "BaseModel.php";

class Empresa extends BaseModel {

    public function obtenerPerfil() {
        $sql = "SELECT * FROM empresa LIMIT 1";
        return $this->consultaUnica($sql);
    }

    public function actualizarPerfil($data) {
        $sql = "UPDATE empresa SET
                    razon_social = ?,
                    nombre_comercial = ?,
                    id_doc_identificacion = ?,
                    nro_documento = ?,
                    representante = ?,
                    direccion_fiscal = ?,
                    telefono = ?,
                    email = ?,
                    web = ?,
                    logo_path = ?
                WHERE id_empresa = ?";

        $params = [
            $data['razon_social'],
            $data['nombre_comercial'],
            $data['id_doc_identificacion'],
            $data['nro_documento'],
            $data['representante'],
            $data['direccion_fiscal'],
            $data['telefono'],
            $data['email'],
            $data['web'],
            $data['logo_path'] ?? '', // si aún no se sube
            $data['id_empresa']
        ];

        $types = "ssisssssssi";

        return $this->actualizarPreparado($sql, $params, $types);
    }
}