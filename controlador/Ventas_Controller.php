<?php
require_once __DIR__ . '/../modelo/Ventas_Model.php';

class VentasController {
    private $venta;

    public function __construct() {
        $this->venta = new VentasModel();
    }

    public function listarTiposCPE() {
        // Limita a los tipos usuales para ventas (ajústalo si quieres más)
        $tipos = $this->venta->obtenerTiposCPE(['01','03','07','08','09','NV','TK','TI','DV']);
        echo json_encode(['success' => true, 'data' => $tipos]); 
        exit;
    }

    public function sugerirSerieYCorrelativo() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $tipo      = $_GET['tipo'] ?? '';
    $idEmpresa = (int)($_SESSION['id_empresa'] ?? 0);
    $idSede    = (int)($_SESSION['id_sede'] ?? 0);

    if ($tipo === '') {
        echo json_encode(['success' => false, 'message' => 'Falta parámetro tipo']);
        exit;
    }

    // ✅ Si no hay id_empresa en sesión, lo inferimos por id_sede desde serie_documento
    if ($idEmpresa <= 0 && $idSede > 0) {
        $idEmpresa = (int)($this->venta->inferirEmpresaPorSede($idSede) ?? 0);
        // opcional: actualizar la sesión para próximas llamadas
        if ($idEmpresa > 0) $_SESSION['id_empresa'] = $idEmpresa;
    }

    if ($idEmpresa <= 0 || $idSede <= 0) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos de sesión (id_empresa / id_sede)']);
        exit;
    }

    // 1) Serie activa por sede/tipo (exacta)
    $serieInfo = $this->venta->obtenerSerieActivaPorSede($idEmpresa, $idSede, $tipo);
    if (!$serieInfo || empty($serieInfo['serie'])) {
        // No inventamos nada: devolvemos error exacto
        echo json_encode(['success' => false, 'message' => 'No hay serie activa configurada para esta sede/tipo']);
        exit;
    }
    $serie = $serieInfo['serie'];
    $corrSerieDoc = isset($serieInfo['correlativo_actual']) ? (int)$serieInfo['correlativo_actual'] : null;

    // 2) Último correlativo emitido exacto
    $maxCorr = $this->venta->obtenerUltimoCorrelativoVenta($idEmpresa, $idSede, $tipo, $serie);

    // Reglas estrictas: si no podemos saber el correlativo exacto, devolvemos error.
    if ($maxCorr === null && $corrSerieDoc === null) {
        echo json_encode(['success' => false, 'message' => 'No se pudo determinar correlativo (sin histórico ni correlativo_actual)']);
        exit;
    }

    $next = ($maxCorr !== null) ? ((int)$maxCorr + 1) : ((int)$corrSerieDoc + 1);
    $next = max(1, (int)$next); // por sanidad, nunca 0 o negativo

    echo json_encode([
        'success' => true,
        'data' => [
            'tipo'            => $tipo,
            'serie'           => $serie,
            'correlativo'     => $next,
            'correlativo_fmt' => str_pad((string)$next, 6, '0', STR_PAD_LEFT),
        ]
    ]);
    exit;
}
}