<?php
require_once __DIR__ . '/../modelo/Proveedor_Model.php';

class ProveedorController {

    private $proveedor;

    public function __construct() {
        $this->proveedor = new Proveedor();
    }

    /* ========= VALIDACIONES BÁSICAS ========= */

    public function validarDocumento() {
        $tipo   = (int) ($_GET['tipo'] ?? 0);
        $numero = trim($_GET['numero'] ?? '');
        $exists = $this->proveedor->existeDocumento($tipo, $numero);

        echo json_encode(['success' => true, 'exists' => $exists]);
    }

    /* ========= CRUD ========= */

    public function registrar() {
        session_start();

        // CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido."]);
            return;
        }

        // Requeridos mínimos
        $idDoc  = (int) ($_POST['id_doc_identificacion'] ?? 0);
        $numDoc = trim($_POST['numero_documento'] ?? '');
        $razon  = trim($_POST['razon_social'] ?? '');

        if (!$idDoc || !$numDoc || !$razon) {
            echo json_encode(["success" => false, "message" => "Faltan campos requeridos (tipo doc, número y razón social)."]);
            return;
        }

        // Unicidad
        if ($this->proveedor->existeDocumento($idDoc, $numDoc)) {
            echo json_encode(["success" => false, "message" => "El proveedor ya está registrado con ese documento."]);
            return;
        }

        // Normalización de datos
        $data = [
            'id_doc_identificacion' => $idDoc,
            'numero_documento'      => $numDoc,
            'razon_social'          => strtoupper($razon),
            'nombre_comercial'      => trim($_POST['nombre_comercial'] ?? ''),
            'representante'         => trim($_POST['representante'] ?? ''),
            'direccion'             => trim($_POST['direccion'] ?? ''),
            'id_ubigeo_inei'        => trim($_POST['id_ubigeo_inei'] ?? ''),
            'pais'                  => trim($_POST['pais'] ?? ''),
            'telefono_fijo'         => trim($_POST['telefono_fijo'] ?? ''),
            'celular'               => trim($_POST['celular'] ?? ''),
            'email'                 => trim($_POST['email'] ?? ''),
            'web'                   => trim($_POST['web'] ?? ''),
            'limite_credito'        => (float) ($_POST['limite_credito'] ?? 0),
            'estado_proveedor'      => trim($_POST['estado_proveedor'] ?? 'ACTIVO'),
            'notas'                 => trim($_POST['notas'] ?? '')
        ];

        $id = $this->proveedor->registrar($data);

        echo json_encode([
            "success" => $id > 0,
            "id_proveedor" => $id,
            "message" => $id > 0 ? "Proveedor registrado correctamente" : "Error al registrar proveedor"
        ]);
    }

    public function modificar() {
        session_start();

        // CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            echo json_encode(["success" => false, "message" => "Token CSRF inválido."]);
            return;
        }

        $id = (int) ($_POST['id_proveedor'] ?? 0);
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID de proveedor inválido."]);
            return;
        }

        // Requeridos mínimos
        $idDoc  = (int) ($_POST['id_doc_identificacion'] ?? 0);
        $numDoc = trim($_POST['numero_documento'] ?? '');
        $razon  = trim($_POST['razon_social'] ?? '');

        if (!$idDoc || !$numDoc || !$razon) {
            echo json_encode(["success" => false, "message" => "Faltan campos requeridos (tipo doc, número y razón social)."]);
            return;
        }

        $data = [
            'id_proveedor'          => $id,
            'id_doc_identificacion' => $idDoc,
            'numero_documento'      => $numDoc,
            'razon_social'          => strtoupper($razon),
            'nombre_comercial'      => trim($_POST['nombre_comercial'] ?? ''),
            'representante'         => trim($_POST['representante'] ?? ''),
            'direccion'             => trim($_POST['direccion'] ?? ''),
            'id_ubigeo_inei'        => trim($_POST['id_ubigeo_inei'] ?? ''),
            'pais'                  => trim($_POST['pais'] ?? ''),
            'telefono_fijo'         => trim($_POST['telefono_fijo'] ?? ''),
            'celular'               => trim($_POST['celular'] ?? ''),
            'email'                 => trim($_POST['email'] ?? ''),
            'web'                   => trim($_POST['web'] ?? ''),
            'limite_credito'        => (float) ($_POST['limite_credito'] ?? 0),
            'estado_proveedor'      => trim($_POST['estado_proveedor'] ?? 'ACTIVO'),
            'notas'                 => trim($_POST['notas'] ?? '')
        ];

        $ok = $this->proveedor->modificar($data);

        echo json_encode([
            "success" => $ok,
            "message" => $ok ? "Proveedor modificado correctamente" : "No se realizaron cambios o falló la operación"
        ]);
    }

    /* ========= OBTENCIONES ========= */

    public function obtenerProveedor() {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID inválido"]);
            return;
        }

        $prov = $this->proveedor->obtenerPorId($id);
        if (!$prov) {
            echo json_encode(["success" => false, "message" => "Proveedor no encontrado"]);
            return;
        }

        echo json_encode(["success" => true, "data" => $prov]);
    }

    public function listarProveedores() {
        header('Content-Type: application/json');
        $lista = $this->proveedor->listarTodos();
        echo json_encode(["success" => true, "data" => $lista]); // Compatible con DataTables
    }

    /* ========= CAMBIOS SIMPLES ========= */

    public function cambiarEstado() {
        $id     = (int) ($_POST['id_proveedor'] ?? 0);
        $estado = trim($_POST['estado_proveedor'] ?? '');
        if (!$id || !$estado) {
            echo json_encode(["success" => false, "message" => "Datos inválidos"]);
            return;
        }
        $ok = $this->proveedor->actualizarCampoProveedor('estado_proveedor', $estado, $id);
        echo json_encode([
            "success" => $ok,
            "message" => $ok ? "Estado actualizado" : "Error al actualizar estado"
        ]);
    }

    public function actualizarLimiteCredito() {
        $id     = (int) ($_POST['id_proveedor'] ?? 0);
        $limite = (float) ($_POST['limite_credito'] ?? 0);
        if (!$id) {
            echo json_encode(["success" => false, "message" => "ID inválido"]);
            return;
        }
        $ok = $this->proveedor->actualizarCampoProveedor('limite_credito', $limite, $id);
        echo json_encode([
            "success" => $ok,
            "message" => $ok ? "Límite de crédito actualizado" : "Error al actualizar límite"
        ]);
    }
}