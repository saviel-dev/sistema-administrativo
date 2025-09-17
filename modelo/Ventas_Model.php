<?php
require_once "BaseModel.php";

class VentasModel extends BaseModel {

    public function obtenerTiposCPE(array $solo = []): array {
        $sql = "SELECT CodigoSunat_TipoDocumentoVenta AS codigo,
                       Descripcion,
                       SeriePrincipal,
                       SerieAlternativa
                  FROM tipo_documentoVenta";
        $params = [];
        $types  = '';

        if (!empty($solo)) {
            $place = implode(',', array_fill(0, count($solo), '?'));
            $sql  .= " WHERE CodigoSunat_TipoDocumentoVenta IN ($place)";
            $params = $solo;
            $types  = str_repeat('s', count($solo));
        }
        $sql .= " ORDER BY id_TipoDocumentoVenta ASC";

        return $this->consultaPreparadaMultiple($sql, $params, $types);
    }

    public function inferirEmpresaPorSede(int $idSede): ?int {
    // Buscamos empresa en serie_documento por sede (serie activa preferentemente)
    $sql = "SELECT id_empresa
              FROM serie_documento
             WHERE id_sede = ?
             ORDER BY estado DESC, id_serie_documento DESC
             LIMIT 1";
    $res = $this->consultaPreparadaMultiple($sql, [$idSede], "i");
    if (!empty($res) && isset($res[0]['id_empresa'])) {
        return (int)$res[0]['id_empresa'];
    }
    return null;
}

    public function obtenerSerieActivaPorSede(int $idEmpresa, int $idSede, string $tipo): ?array {
        $sql = "SELECT serie, correlativo_actual
                  FROM serie_documento
                 WHERE id_empresa = ?
                   AND id_sede = ?
                   AND tipo_comprobante = ?
                   AND estado = 1
              ORDER BY id_serie_documento DESC
                 LIMIT 1";
        $params = [$idEmpresa, $idSede, $tipo];
        $types  = "iis";
        $res = $this->consultaPreparadaMultiple($sql, $params, $types);
        return $res[0] ?? null;
    }

    public function obtenerSerieSugeridaPorTipo(string $tipo): ?string {
        $sql = "SELECT SeriePrincipal, SerieAlternativa
                  FROM tipo_documentoVenta
                 WHERE CodigoSunat_TipoDocumentoVenta = ?
                 LIMIT 1";
        $res = $this->consultaPreparadaMultiple($sql, [$tipo], "s");
        if (empty($res)) return null;
        $row = $res[0];
        if (!empty($row['SeriePrincipal']))   return $row['SeriePrincipal'];
        if (!empty($row['SerieAlternativa'])) return $row['SerieAlternativa'];
        return null;
    }

    public function obtenerUltimoCorrelativoVenta(int $idEmpresa, int $idSede, string $tipo, string $serie): ?int {
        $sql = "SELECT MAX(correlativo) AS maxcorr
                  FROM venta_cabecera
                 WHERE id_empresa = ?
                   AND id_sede = ?
                   AND tipo_comprobante = ?
                   AND serie = ?";
        $params = [$idEmpresa, $idSede, $tipo, $serie];
        $types  = "iiss";
        $res = $this->consultaPreparadaMultiple($sql, $params, $types);
        if (!empty($res) && $res[0]['maxcorr'] !== null) return (int)$res[0]['maxcorr'];
        return null;
    }
}