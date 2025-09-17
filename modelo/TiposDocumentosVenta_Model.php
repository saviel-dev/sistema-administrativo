<?php
require_once "BaseModel.php";

class TiposDocumentosVentaModel extends BaseModel
{
    public function listar()
    {
        return $this->consultar("SELECT * FROM tipo_documentoVenta");
    }

    public function obtener($id)
    {
        $sql = "SELECT * FROM tipo_documentoVenta WHERE id_TipoDocumentoVenta = ?";
        return $this->consultaPreparadaUnica($sql, [$id], "i");
    }

    public function crear($data)
    {
        $sql = "INSERT INTO tipo_documentoVenta (CodigoSunat_TipoDocumentoVenta, SeriePrincipal, SerieAlternativa, Descripcion)
                VALUES (?, ?, ?, ?)";
        return $this->insertarPreparado($sql, [
            $data['CodigoSunat_TipoDocumentoVenta'],
            $data['SeriePrincipal'],
            $data['SerieAlternativa'],
            $data['Descripcion']
        ], "ssss");
    }

    public function actualizar($data)
    {
        $sql = "UPDATE tipo_documentoVenta SET 
                    CodigoSunat_TipoDocumentoVenta = ?, 
                    SeriePrincipal = ?, 
                    SerieAlternativa = ?, 
                    Descripcion = ?
                WHERE id_TipoDocumentoVenta = ?";
        return $this->actualizarPreparado($sql, [
            $data['CodigoSunat_TipoDocumentoVenta'],
            $data['SeriePrincipal'],
            $data['SerieAlternativa'],
            $data['Descripcion'],
            $data['id']
        ], "ssssi");
    }

    public function eliminar($id)
    {
        $sql = "DELETE FROM tipo_documentoVenta WHERE id_TipoDocumentoVenta = ?";
        return $this->actualizarPreparado($sql, [$id], "i");
    }
}