<?php
require_once "BaseModel.php";

class SerieModel extends BaseModel
{
    // Obtener series por sede con join a tipo_documentoVenta
    public function obtenerPorSede($id_sede) {
        $sql = "
            SELECT sd.id_serie_documento, sd.tipo_comprobante, td.Descripcion AS nombre_comprobante,
                   sd.serie, sd.correlativo_actual, sd.estado, sd.id_sede
            FROM serie_documento sd
            INNER JOIN tipo_documentoVenta td ON td.CodigoSunat_TipoDocumentoVenta = sd.tipo_comprobante
            WHERE sd.id_sede = ?
        ";
        return $this->consultaPreparadaMultiple($sql, [$id_sede], 'i');
    }

    // Crear nueva serie
    public function crearSerie($id_empresa, $id_sede, $tipo_comprobante, $serie, $correlativo_actual) {
        $sql = "
            INSERT INTO serie_documento (id_empresa, id_sede, tipo_comprobante, serie, correlativo_actual, estado)
            VALUES (?, ?, ?, ?, ?, 1)
        ";
        $params = [$id_empresa, $id_sede, $tipo_comprobante, $serie, $correlativo_actual];
        $types = "iissi";
        return $this->insertarPreparado($sql, $params, $types);
    }

    // Actualizar serie existente
    public function actualizarSerie($id_serie, $tipo_comprobante, $serie, $correlativo_actual, $estado) {
        $sql = "
            UPDATE serie_documento 
            SET tipo_comprobante = ?, serie = ?, correlativo_actual = ?, estado = ?
            WHERE id_serie_documento = ?
        ";
        $params = [$tipo_comprobante, $serie, $correlativo_actual, $estado, $id_serie];
        $types = "ssiii";
        return $this->actualizarPreparado($sql, $params, $types);
    }

    // Eliminar una serie (opcionalmente lógica)
    public function eliminarSerie($id_serie) {
        $sql = "DELETE FROM serie_documento WHERE id_serie_documento = ?";
        return $this->actualizarPreparado($sql, [$id_serie], "i");
    }

    // Obtener todos los tipos de comprobantes para popular el select
    public function obtenerTiposDocumento() {
        $sql = "
            SELECT CodigoSunat_TipoDocumentoVenta AS codigo, Descripcion
            FROM tipo_documentoVenta
            ORDER BY Descripcion ASC
        ";
        return $this->consultar($sql);
    }

    public function obtenerSeriePorId($id_serie_documento) {
    $sql = "
        SELECT sd.*, td.SeriePrincipal, td.SerieAlternativa 
        FROM serie_documento sd
        LEFT JOIN tipo_documentoVenta td 
            ON td.CodigoSunat_TipoDocumentoVenta = sd.tipo_comprobante
        WHERE sd.id_serie_documento = ?
        LIMIT 1
    ";
    return $this->consultaPreparadaUnica($sql, [$id_serie_documento], 'i');
}
}