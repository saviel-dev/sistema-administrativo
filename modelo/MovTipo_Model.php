<?php
require_once "BaseModel.php";

class MovTipo extends BaseModel {
    public function listar(): array {
        return $this->consultaPreparadaMultiple("SELECT * FROM movimiento_tipo ORDER BY id_movimientoTipo ASC");
    }

    public function registrar(array $d): int {
        return $this->insertarPreparado(
            "INSERT INTO movimiento_tipo(codigo,descripcion,signo) VALUES (?,?,?)",
            [ (string)$d['codigo'], (string)$d['descripcion'], (int)$d['signo'] ],
            "ssi"
        );
    }

    public function getIdByCodigoOrSigno(string $codigo, int $fallbackSigno=1): int {
        $r = $this->consultaPreparadaUnica("SELECT id_movimientoTipo FROM movimiento_tipo WHERE codigo=? LIMIT 1", [$codigo], "s");
        if ($r) return (int)$r['id_movimientoTipo'];
        $r2 = $this->consultaPreparadaUnica("SELECT id_movimientoTipo FROM movimiento_tipo WHERE signo=? LIMIT 1", [$fallbackSigno], "i");
        return $r2 ? (int)$r2['id_movimientoTipo'] : 0;
    }
}