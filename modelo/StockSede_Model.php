<?php
require_once "BaseModel.php";

class StockSede extends BaseModel {
    public function ensureRow(int $id_sede,int $id_var): bool {
    $r = $this->consultaPreparadaUnica(
        "SELECT 1 FROM stock_por_sede WHERE id_sede=? AND id_producto_variante=? LIMIT 1",
        [$id_sede,$id_var],"ii"
    );
    if ($r) return true;

    return $this->insertarPreparado(
        "INSERT INTO stock_por_sede(id_sede,id_producto_variante,stock_actual,stock_reservado,stock_min) 
         VALUES (?,?,?,?,?)",
        [$id_sede,$id_var,0.00,0.00,0.00],
        "iiddd"
    ) > 0;
}

    public function sumarStock(int $id_sede,int $id_var,float $cant): bool {
        return $this->actualizarPreparado(
            "UPDATE stock_por_sede SET stock_actual = stock_actual + ? WHERE id_sede=? AND id_producto_variante=?",
            [$cant,$id_sede,$id_var],"dii"
        );
    }

    public function listar(int $id_sede=0): array {
        if ($id_sede>0) {
            return $this->consultaPreparadaMultiple(
                "SELECT * FROM stock_por_sede WHERE id_sede=?",[$id_sede],"i"
            );
        }
        return $this->consultaPreparadaMultiple("SELECT * FROM stock_por_sede");
    }

    public function obtener(int $id_sede,int $id_var): ?array {
        return $this->consultaPreparadaUnica(
            "SELECT * FROM stock_por_sede WHERE id_sede=? AND id_producto_variante=? LIMIT 1",
            [$id_sede,$id_var],"ii"
        );
    }

    public function actualizarMin(int $id_sede,int $id_var,float $min): bool {
        $this->ensureRow($id_sede,$id_var);
        return $this->actualizarPreparado(
            "UPDATE stock_por_sede SET stock_min=? WHERE id_sede=? AND id_producto_variante=?",
            [$min,$id_sede,$id_var],"dii"
        );
    }
}