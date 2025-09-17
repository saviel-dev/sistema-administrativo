<?php
require_once __DIR__ . '/../modelo/Compras_Model.php';

class ComprasController
{
    private $compras;

    public function __construct()
    {
        $this->compras = new Compras();
    }

    public function listar()
    {
        header('Content-Type: application/json');
        echo json_encode(["success" => true, "data" => $this->compras->listarCabeceras()]);
    }

    public function obtener()
    {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID inválido"]);
            return;
        }
        $cab = $this->compras->obtenerCabecera($id);
        if (!$cab) {
            echo json_encode(["success" => false, "message" => "Compra no encontrada"]);
            return;
        }
        $det = $this->compras->listarDetalle($id);
        echo json_encode(["success" => true, "cabecera" => $cab, "detalle" => $det]);
    }

    public function registrar() {
    session_start();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        echo json_encode(["success"=>false,"message"=>"Token CSRF inválido"]); return;
    }

    // 1) Id de usuario desde la sesión (ajusta a tu estructura real)
    $idUsuarioSesion = (int)($_SESSION['usuario']['id_usuario'] ?? $_SESSION['id_usuario'] ?? 0);

    // 2) Armar cabecera con claves correctas
    $cab = [
        "id_proveedor"           => (int)($_POST["id_proveedor"] ?? 0),
        "id_sede"                => (int)($_POST["id_sede"] ?? 0),
        "fechaEmision_documento" => trim($_POST["fechaEmision_documento"] ?? null),
        "tipoDocumento_compra"   => trim($_POST["tipoDocumento_compra"] ?? null),
        "numeroDocumento_compra" => trim($_POST["numeroDocumento_compra"] ?? null),
        "tipo_moneda"            => trim($_POST["tipo_moneda"] ?? 'PEN'),
        "condicionesPago_compra" => trim($_POST["condicionesPago_compra"] ?? null),
        "observaciones"          => trim($_POST["observaciones"] ?? null),

        // ⚠️ CLAVE CORRECTA:
        "subtotal_compra"        => (float)($_POST["subtotal_compra"] ?? 0),
        "igv_compra"             => (float)($_POST["igv_compra"] ?? 0),
        "total_compra"           => (float)($_POST["total_compra"] ?? 0),

        // Siempre RECIBIDO (como pediste)
        "estado_compra"          => 'RECIBIDO',

        // Usuario de sesión
        "id_usuario"             => $idUsuarioSesion,
    ];

    if (!$cab["id_proveedor"] || !$cab["id_sede"]) {
        echo json_encode(["success"=>false,"message"=>"Faltan proveedor o sede"]);
        return;
    }

    // Detalle (igual que ya tenías)
    $detalles = [];
    if (!empty($_POST["detalle_json"])) {
        $detalles = json_decode($_POST["detalle_json"], true) ?: [];
    } else {
        $ids = $_POST['id_producto_variante'] ?? [];
        $cants = $_POST['cantidad'] ?? [];
        $precios = $_POST['precio_unitario'] ?? [];
        $fv = $_POST['fecha_vencimiento'] ?? [];
        for ($i=0; $i<count($ids); $i++) {
            $detalles[] = [
                "id_producto_variante" => (int)$ids[$i],
                "cantidad"             => (float)$cants[$i],
                "precio_unitario"      => (float)$precios[$i],
                "fecha_vencimiento"    => $fv[$i] ?? null
            ];
        }
    }
    if (!$detalles) {
        echo json_encode(["success"=>false,"message"=>"Detalle vacío"]);
        return;
    }

    $id = $this->compras->registrarCompra($cab, $detalles);
    echo json_encode(["success"=> $id>0, "id_compra"=>$id, "message"=>$id>0?"Compra registrada":"Error al registrar"]);
}

    public function cambiarEstado()
    {
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido"]);
            return;
        }
        $id = (int)($_POST['id_compra'] ?? 0);
        $estado = trim($_POST['estado_compra'] ?? '');
        if (!$id || !in_array($estado, ['PENDIENTE', 'RECIBIDO', 'CANCELADO'])) {
            echo json_encode(["success" => false, "message" => "Parámetros inválidos"]);
            return;
        }
        echo json_encode(["success" => $this->compras->cambiarEstadoCompra($id, $estado)]);
    }

    public function agregarItem()
    {
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido"]);
            return;
        }
        $id_compra = (int)($_POST['id_compra'] ?? 0);
        $item = [
            "id_producto_variante" => (int)($_POST["id_producto_variante"] ?? 0),
            "cantidad"             => (float)($_POST["cantidad"] ?? 0),
            "precio_unitario"      => (float)($_POST["precio_unitario"] ?? 0),
            "fecha_vencimiento"    => trim($_POST["fecha_vencimiento"] ?? null),
        ];
        $ok = ($id_compra && $item["id_producto_variante"] && $item["cantidad"] > 0)
            ? $this->compras->agregarItem($id_compra, $item) : false;
        echo json_encode(["success" => $ok]);
    }

    public function eliminarItem()
    {
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido"]);
            return;
        }
        $id_det = (int)($_POST['id_compra_detalle'] ?? 0);
        echo json_encode(["success" => $id_det ? $this->compras->eliminarItem($id_det) : false]);
    }
}
