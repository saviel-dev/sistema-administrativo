<?php
require_once "BaseModel.php";
require_once __DIR__ . "/StockSede_Model.php";
require_once __DIR__ . "/InventarioMov_Model.php";
require_once __DIR__ . "/MovTipo_Model.php";

class Compras extends BaseModel {
    private float $igvRate = 0.18;
    private StockSede $stockModel;
    private InventarioMov $kardexModel;
    private MovTipo $movTipoModel;

    public function __construct() {
        parent::__construct();
        $this->stockModel   = new StockSede();
        $this->kardexModel  = new InventarioMov();
        $this->movTipoModel = new MovTipo();
    }

    public function calcularTotales(array $items): array {
        $subtotal = 0.0;
        foreach ($items as $it) $subtotal += (float)$it['cantidad'] * (float)$it['precio_unitario'];
        $igv = round($subtotal * $this->igvRate, 2);
        return ["subtotal"=>round($subtotal,2), "igv"=>$igv, "total"=>round($subtotal+$igv,2)];
    }

    public function listarCabeceras(): array {
        $q = "SELECT c.id_compra,c.fecha_registro,c.fechaEmision_documento,c.tipoDocumento_compra,
                     c.numeroDocumento_compra,c.tipo_moneda,c.subtotal_compra,c.igv_compra,c.total_compra,
                     c.estado_compra,c.id_sede,c.id_proveedor,p.razon_social AS proveedor
                FROM compras_cabecera c
           LEFT JOIN proveedores p ON c.id_proveedor = p.id_proveedor
            ORDER BY c.id_compra DESC";
        return $this->consultaPreparadaMultiple($q);
    }

    public function obtenerCabecera(int $id): ?array {
        $q = "SELECT c.*, p.razon_social AS proveedor
                FROM compras_cabecera c
           LEFT JOIN proveedores p ON c.id_proveedor=p.id_proveedor
               WHERE c.id_compra=? LIMIT 1";
        return $this->consultaPreparadaUnica($q, [$id], "i");
    }

    public function listarDetalle(int $id_compra): array {
        $q = "SELECT d.id_compra_detalle,d.id_compra,d.id_producto_variante,d.cantidad,
                     d.`precio_unitario_compra` AS precio_unitario,d.fecha_vencimiento
                FROM compras_detalle d
               WHERE d.id_compra=?";
        return $this->consultaPreparadaMultiple($q, [$id_compra], "i");
    }

    public function registrarCompra(array $cab, array $detalles): int {
        try {
            mysqli_begin_transaction($this->cn);

            // totales (si hiciera falta)
            if ($cab['total_compra'] <= 0) {
                $t = $this->calcularTotales($detalles);
                $cab['subtotal_compra']=$t['subtotal']; $cab['igv_compra']=$t['igv']; $cab['total_compra']=$t['total'];
            }

            $qCab = "INSERT INTO compras_cabecera(
                        id_proveedor,id_sede,fechaEmision_documento,tipoDocumento_compra,numeroDocumento_compra,
                        tipo_moneda,condicionesPago_compra,observaciones,subtotal_compra,igv_compra,total_compra,
                        estado_compra,id_usuario
                    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $id_compra = $this->insertarPreparado($qCab, [
                (int)$cab['id_proveedor'], (int)$cab['id_sede'], $cab['fechaEmision_documento'],
                $cab['tipoDocumento_compra'], $cab['numeroDocumento_compra'], $cab['tipo_moneda'],
                $cab['condicionesPago_compra'], $cab['observaciones'], (float)$cab['subtotal_compra'],
                (float)$cab['igv_compra'], (float)$cab['total_compra'], $cab['estado_compra'], (int)$cab['id_usuario']
            ], "iissssssddssi");

            $qDet = "INSERT INTO compras_detalle(id_compra,id_producto_variante,cantidad,`precio_unitario_compra`,fecha_vencimiento)
                     VALUES (?,?,?,?,?)";
            foreach ($detalles as $it) {
                $this->insertarPreparado($qDet, [
                    (int)$id_compra, (int)$it['id_producto_variante'],
                    (float)$it['cantidad'], (float)$it['precio_unitario'], ($it['fecha_vencimiento'] ?: null)
                ], "iidds");
            }

            // Siempre impactar recepción como si fuera RECIBIDO
$this->impactarRecepcion($id_compra, (int)$cab['id_sede']);

            mysqli_commit($this->cn);
            return (int)$id_compra;
        } catch (\Throwable $e) {
            mysqli_rollback($this->cn);
            Conexion::logError($e);
            return 0;
        }
    }

    public function cambiarEstadoCompra(int $id_compra, string $nuevoEstado): bool {
        try {
            mysqli_begin_transaction($this->cn);

            $cab = $this->obtenerCabecera($id_compra);
            if (!$cab) throw new Exception("Compra no existe");
            if ($cab['estado_compra']==='RECIBIDO' && $nuevoEstado==='RECIBIDO') { mysqli_commit($this->cn); return true; }

            $ok = $this->actualizarPreparado(
                "UPDATE compras_cabecera SET estado_compra=? WHERE id_compra=?",
                [$nuevoEstado,$id_compra],"si"
            );
            if (!$ok) throw new Exception("No se actualizó estado");

            // Siempre impactamos recepción al cambiar estado
$this->impactarRecepcion($id_compra, (int)$cab['id_sede']);

            mysqli_commit($this->cn);
            return true;
        } catch (\Throwable $e) {
            mysqli_rollback($this->cn);
            Conexion::logError($e);
            return false;
        }
    }

    private function impactarRecepcion(int $id_compra, int $id_sede): void {
    $items = $this->listarDetalle($id_compra);

    // 1) Tipo de movimiento COMPRA (opción A): si no existe, lo crea
    $idMovTipo = $this->movTipoModel->getIdByCodigoOrSigno('COMPRA', +1);
    if ($idMovTipo <= 0) {
        // crea código COMPRA con signo +1
        $nuevoId = $this->movTipoModel->registrar([
            "codigo"      => "COMPRA",
            "descripcion" => "Ingreso por compra",
            "signo"       => 1,
        ]);
        // intenta recuperar (si registrar devolvió 0 por unique, intenta lookup otra vez)
        $idMovTipo = $nuevoId > 0 ? $nuevoId : $this->movTipoModel->getIdByCodigoOrSigno('COMPRA', +1);
    }

    foreach ($items as $it) {
        $idVar = (int)($it['id_producto_variante'] ?? 0);
        // “si está vacío, igual inserta”: normalizamos a 0.00
        $cant  = (float)($it['cantidad'] ?? 0);
        $costo = (float)($it['precio_unitario'] ?? 0); // viene como 'precio_unitario' del SELECT

        if ($idVar <= 0) continue; // sin variante no se puede impactar

        // 2) Stock por sede: asegurar fila y sumar stock (aunque cantidad sea 0.00, no falla)
        $this->stockModel->ensureRow($id_sede, $idVar);
        if ($cant != 0.0) {
            $this->stockModel->sumarStock($id_sede, $idVar, $cant);
        }

        // 3) Kardex
        $this->kardexModel->insertMovimiento([
            "id_sede" => $id_sede,
            "id_producto" => $idVar,               // usamos variante como producto
            "id_movimientoTipo" => $idMovTipo ?: 0,
            "cantidad" => $cant,                    // si es 0.00 igual registra
            "costo_unit" => $costo,                 // si es 0.00 igual registra
            "referencia_tipoTransaccion" => 'COMPRA',
            "referencia_id" => $id_compra
        ]);
    }
}

    public function agregarItem(int $id_compra, array $it): bool {
        $cab = $this->obtenerCabecera($id_compra);
        if (!$cab || $cab['estado_compra']!=='PENDIENTE') return false;

        try {
            mysqli_begin_transaction($this->cn);

            $this->insertarPreparado(
                "INSERT INTO compras_detalle(id_compra,id_producto_variante,cantidad,`precio_unitario_compra`,fecha_vencimiento)
                 VALUES (?,?,?,?,?)",
                [$id_compra,(int)$it['id_producto_variante'],(float)$it['cantidad'],(float)$it['precio_unitario'],($it['fecha_vencimiento'] ?: null)],
                "iidds"
            );

            $this->recalcularTotales($id_compra);

            mysqli_commit($this->cn);
            return true;
        } catch (\Throwable $e) {
            mysqli_rollback($this->cn);
            Conexion::logError($e);
            return false;
        }
    }

    public function eliminarItem(int $id_compra_detalle): bool {
        $row = $this->consultaPreparadaUnica("SELECT id_compra FROM compras_detalle WHERE id_compra_detalle=? LIMIT 1", [$id_compra_detalle], "i");
        if (!$row) return false;
        $id_compra = (int)$row['id_compra'];

        $cab = $this->obtenerCabecera($id_compra);
        if (!$cab || $cab['estado_compra']!=='PENDIENTE') return false;

        try {
            mysqli_begin_transaction($this->cn);
            $this->actualizarPreparado("DELETE FROM compras_detalle WHERE id_compra_detalle=?", [$id_compra_detalle], "i");
            $this->recalcularTotales($id_compra);
            mysqli_commit($this->cn);
            return true;
        } catch (\Throwable $e) {
            mysqli_rollback($this->cn);
            Conexion::logError($e);
            return false;
        }
    }

    private function recalcularTotales(int $id_compra): void {
        $items = $this->listarDetalle($id_compra);
        $data = [];
        foreach ($items as $it) $data[] = ["cantidad" => (float)$it['cantidad'], "precio_unitario" => (float)$it['precio_unitario']];
        $t = $this->calcularTotales($data);

        $this->actualizarPreparado(
            "UPDATE compras_cabecera SET subtotal_compra=?, igv_compra=?, total_compra=? WHERE id_compra=?",
            [$t['subtotal'],$t['igv'],$t['total'],$id_compra],"dddi"
        );
    }
}