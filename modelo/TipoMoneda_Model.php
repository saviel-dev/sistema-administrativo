<?php

require_once "BaseModel.php";
class TipoMonedaModel extends BaseModel
{
    public function obtenerTodas()
    {
        $sql = "
            SELECT * FROM tipo_monedas
ORDER BY 
  CASE 
    WHEN id_tipoMoneda = 7 THEN 0
    WHEN id_tipoMoneda = 12 THEN 1
    ELSE 2
  END,
  Moneda ASC;
        ";
        return $this->consultar($sql);
    }
}
