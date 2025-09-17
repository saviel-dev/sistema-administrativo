<?php
require_once "BaseModel.php";

class InventarioMov extends BaseModel {

    public function insertMovimiento(array $d): int {
        return $this->insertarPreparado(
            "INSERT INTO inventario_mov (id_sede,id_producto,id_movimientoTipo,fecha_hora,cantidad,costo_unit,referencia_tipoTransaccion,referencia_id)
             VALUES (?,?,?,NOW(),?,?,?,?)",
            [
                (int)$d['id_sede'], (int)$d['id_producto'], (int)$d['id_movimientoTipo'],
                (float)$d['cantidad'], (float)$d['costo_unit'],
                (string)$d['referencia_tipoTransaccion'], (int)$d['referencia_id']
            ],
            "iiiddsi"
        );
    }

    public function listar(int $id_sede=0,int $id_producto=0): array {
        $base = "SELECT * FROM inventario_mov WHERE 1=1";
        $params = []; $types = "";
        if ($id_sede>0) { $base.=" AND id_sede=?"; $params[]=$id_sede; $types.="i"; }
        if ($id_producto>0) { $base.=" AND id_producto=?"; $params[]=$id_producto; $types.="i"; }
        $base.=" ORDER BY id_inventarioMovimiento DESC";
        return $types ? $this->consultaPreparadaMultiple($base,$params,$types) : $this->consultaPreparadaMultiple($base);
    }
}