<?php
require_once __DIR__ . '/../modelo/Producto_Model.php';

class ProductoController {
    private $producto;

    public function __construct() {
        $this->producto = new Producto();
    }

    public function buscarProductos() {
        $search = $_GET['search'] ?? '';
        $page   = (int) ($_GET['page'] ?? 1);
        $idSede = (int) ($_GET['id_sede'] ?? 0);
        $limit  = 10;
        $offset = ($page - 1) * $limit;

        $productos = $this->producto->buscarProductos($idSede, $search, $limit, $offset);
        $total = $this->producto->contarTotal($idSede, $search);
        $totalPages = ceil($total / $limit);

        foreach ($productos as &$prod) {
            // Ahora el precio viene de la subconsulta de precios_por_sede como pps.*
            $precio = isset($prod['precio_venta']) ? (float)$prod['precio_venta'] : 0.0;
            $divisa = $prod['moneda_venta'] ?? '';
            $descCompleta = $prod['DescripcionCompleta'] ?? '';
            $descVar = $prod['descripcion_variante'] ?? '';
            $marca = $prod['MarcaProductos'] ?? '';
            $obs = $prod['Observaciones'] ?? '';

            $prod['otras_sedes'] = $this->producto->obtenerStockOtrasSedes($prod['id_producto_variante'], $idSede);
            $prod['precio'] = ($precio > 0 ? number_format($precio, 2) : '0.00') . ($divisa ? " $divisa" : '');
            $titulo = trim($descCompleta . ' - ' . $descVar, " -");
            $prod['titulo'] = $titulo;
            $prod['descripcion'] = trim($marca . ' ' . $obs);
        }

        echo json_encode([
            "success" => true,
            "data" => [
                "productos" => $productos,
                "total_pages" => $totalPages,
                "current_page" => $page
            ]
        ]);
        exit;
    }

    public function buscarProductosCompras() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $search  = $_GET['search'] ?? '';
        $page    = (int)($_GET['page'] ?? 1);
        $limit   = (int)($_GET['limit'] ?? 20);
        $offset  = ($page - 1) * $limit;
        $cat     = $_GET['categoria'] ?? '';
        $marca   = $_GET['marca'] ?? '';

        // id_sede desde sesión (fallback a GET)
        $idSedeQS     = (int)($_GET['id_sede'] ?? 0);
        $idSedeSesion = (int)($_SESSION['id_sede'] ?? 0);
        $idSedeQS     = $idSedeSesion ?: $idSedeQS;

        $nombreSedeSes = $_SESSION['nombre_sede'] ?? '';

        $productos = $this->producto->buscarProductosCompras($idSedeQS, $search, $limit, $offset, $cat, $marca);
        $total     = $this->producto->contarTotalCompras($idSedeQS, $search, $cat, $marca);
        $totalPages = max(1, (int)ceil($total / $limit));

        foreach ($productos as &$p) {
            $todas = $this->producto->obtenerStockTodasSedes($p['id_producto_variante']);
            foreach ($todas as &$s) {
                $s['id_sede'] = (int)$s['id_sede'];
                $s['stock_actual'] = (float)($s['stock_actual'] ?? 0);
                $s['nombre_sede'] = $s['nombre_sede'] ?? '';
            }
            $p['sedes'] = $todas;
            $p['id_sede_actual']     = $idSedeQS;
            $p['nombre_sede_actual'] = $nombreSedeSes;

            // Formato de precio compra/venta (vigente) si están disponibles
            $p['precio_compra_fmt'] = isset($p['precio_compra']) && $p['precio_compra'] !== null 
                                        ? number_format((float)$p['precio_compra'], 2).' '.($p['moneda_compra'] ?? '')
                                        : '';
            $p['precio_venta_fmt']  = isset($p['precio_venta']) && $p['precio_venta'] !== null
                                        ? number_format((float)$p['precio_venta'], 2).' '.($p['moneda_venta'] ?? '')
                                        : '';
        }

        echo json_encode([
            "success" => true,
            "data" => [
                "productos" => $productos,
                "total_pages" => $totalPages,
                "current_page" => $page
            ]
        ]);
        exit;
    }

    public function obtenerCategorias() {
        $categorias = $this->producto->listarCategorias();
        echo json_encode(['success' => true, 'data' => $categorias]);
        exit;
    }

    public function obtenerCategoriasClasificacionGeneral() {
        $categoriasClasificacionGeneral = $this->producto->listarCategoriasClasificacionGeneral();
        echo json_encode(['success' => true, 'data' => $categoriasClasificacionGeneral]);
        exit;
    }

    public function listarUnidadMedidas() {
        $UnidadesMedidas = $this->producto->listarUnidadMedidas();
        echo json_encode(['success' => true, 'data' => $UnidadesMedidas]);
        exit;
    }

    public function guardarProducto() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Sede actual para precios
    $idSede = (int)($_SESSION['id_sede'] ?? ($_POST['id_sede'] ?? 0));

    // Campos base
    $idCategoria = (int)($_POST['id_categoriaProductos'] ?? 0);
    $OEM         = trim($_POST['OEM_productos'] ?? '');
    // IMPORTANTE: asegúrate de enviar 'MarcaVehiculo' desde el frontend (FormData)
    $marcaVeh    = trim($_POST['MarcaVehiculo'] ?? '');
    $modeloVeh   = trim($_POST['ModeloVehiculo'] ?? '');
    $cilindrada  = trim($_POST['CilindradaVehiculo'] ?? '');
    $motor       = trim($_POST['MotorVehiculo'] ?? '');
    $anioIni     = trim($_POST['AñoInicialVehiculo'] ?? '');
    $anioFin     = trim($_POST['AñoFinVehiculo'] ?? '');

    // Campos variante (sin precios)
    $SKU         = trim($_POST['SKU_Productos'] ?? '');
    $origen      = trim($_POST['OrigenProductos'] ?? '');
    $codProv     = trim($_POST['CodigoReferencia_proveedor'] ?? '');
    $marcaProd   = trim($_POST['MarcaProductos'] ?? '');
    $descVar     = trim($_POST['descripcion_variante'] ?? '');
    $descComp    = trim($_POST['DescripcionCompleta'] ?? '');
    $icbper      = (isset($_POST['icbper']) && $_POST['icbper'] == '1') ? 1 : 0;
    $um          = trim($_POST['unidadMedida'] ?? ($_POST['unidad_medida'] ?? 'NIU'));
    $obs         = trim($_POST['Observaciones'] ?? '');

    // Campos de precio (pasarán a precios_por_sede)
    $tipoIGV     = isset($_POST['TipoIGV']) ? (int)$_POST['TipoIGV'] : 10;
    // soporta ambos nombres; si sólo usas PrecioUnitario (venta), lo tomamos
    $precioC     = isset($_POST['PrecioUnitario_Compra']) && $_POST['PrecioUnitario_Compra'] !== '' ? (float)$_POST['PrecioUnitario_Compra'] : 0.0;
    $precioV     = isset($_POST['PrecioUnitario_Venta'])  && $_POST['PrecioUnitario_Venta']  !== '' ? (float)$_POST['PrecioUnitario_Venta']
                  : (isset($_POST['PrecioUnitario']) ? (float)$_POST['PrecioUnitario'] : 0.0);
    $divisa      = trim($_POST['TipoDivisa'] ?? ''); // usamos como moneda_venta (y compra si no llega otra)
    $utilidad    = isset($_POST['Utilidad']) && $_POST['Utilidad'] !== '' ? (float)$_POST['Utilidad'] : null;

    // Validación mínima
    if ($idCategoria === 0 || $SKU === '' || $um === '' || $codProv === '' || $marcaProd === '' || $origen === '') {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    /* ============================================
       IMAGEN: guardar en /image/products con nombre SKU.ext
       ============================================ */
    /* ============================================
   IMAGEN: guardar en /image/products con nombre SKU.ext (+ sufijo si existe)
   ============================================ */
/* ============================================
   IMAGEN: guardar en /image/products con nombre SKU.ext (+ sufijo si existe)
   ============================================ */
$nombreImagen = null;
if (isset($_FILES['ImagenProducto']) && is_array($_FILES['ImagenProducto']) && !empty($_FILES['ImagenProducto']['name'])) {

    // 0) Errores de upload
    if (!isset($_FILES['ImagenProducto']['error']) || $_FILES['ImagenProducto']['error'] !== UPLOAD_ERR_OK) {
        $code = $_FILES['ImagenProducto']['error'] ?? -1;
        echo json_encode(['success'=>false,'message'=>"Error al subir archivo (código $code). Revisa post_max_size / upload_max_filesize y el tamaño máx. (3MB)."]);
        exit;
    }
    if (!is_uploaded_file($_FILES['ImagenProducto']['tmp_name'])) {
        echo json_encode(['success'=>false,'message'=>'El archivo subido no es válido (tmp_name no encontrado).']);
        exit;
    }

    // 1) Carpeta (raíz del proyecto)
    $rootDir   = dirname(__DIR__, 2);  // assets/ajax -> raíz
    $uploadDir = $rootDir . '/image/products/';
    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            echo json_encode(['success'=>false,'message'=>'No se pudo crear el directorio /image/products. Revisa permisos.']);
            exit;
        }
        @chmod($uploadDir, 0777);
    }

    // 2) Extensión por MIME/filename
    $original = $_FILES['ImagenProducto']['name'];
    $mime     = $_FILES['ImagenProducto']['type'] ?? '';
    $ext      = strtolower(pathinfo($original, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, ['jpg','jpeg','png','webp'])) {
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/webp') $ext = 'webp';
        else $ext = 'jpg';
    }
    if ($ext === 'jpeg') $ext = 'jpg';

    // 3) Nombre = SKU.ext (limpio)
    $skuSafe  = preg_replace('/[^A-Z0-9_-]/i', '', $SKU);
    $base     = $skuSafe !== '' ? $skuSafe : 'IMG';
    $nombreImagen = $base . '.' . $ext;
    $destPath = $uploadDir . $nombreImagen;

    // 4) Evitar sobrescribir
    $i = 1;
    while (file_exists($destPath)) {
        $nombreImagen = $base . '-' . $i . '.' . $ext;
        $destPath = $uploadDir . $nombreImagen;
        $i++;
    }

    // 5) Mover archivo
    if (!@move_uploaded_file($_FILES['ImagenProducto']['tmp_name'], $destPath)) {
        // Traza para log del servidor
        error_log("move_uploaded_file fallo hacia $destPath");
        echo json_encode(['success'=>false,'message'=>'No se pudo mover el archivo subido. Revisa permisos de /image/products y open_basedir.']);
        exit;
    }
}

    // 1) Crear (o reutilizar) producto_base
    $idBase = $this->producto->buscarProductoBaseSimilar(
        $idCategoria, $OEM, $marcaVeh, $modeloVeh, $cilindrada, $motor, $anioIni, $anioFin
    );
    if (!$idBase) {
        $idBase = $this->producto->insertarProductoBase([
            'id_categoriaProductos' => $idCategoria,
            'OEM_productos'         => $OEM,
            'MarcaVehiculo'         => $marcaVeh,
            'ModeloVehiculo'        => $modeloVeh,
            'CilindradaVehiculo'    => $cilindrada,
            'MotorVehiculo'         => $motor,
            'AñoInicialVehiculo'    => $anioIni,
            'AñoFinVehiculo'        => $anioFin,
        ]);
    }
    if (!$idBase) {
        echo json_encode(['success' => false, 'message' => 'No se pudo crear producto base']);
        exit;
    }

    // 2) Crear variante (sin columnas de precio)
    $idVar = $this->producto->insertarProductoVariante([
        'id_producto_base'          => $idBase,
        'SKU_Productos'             => $SKU,
        'OrigenProductos'           => $origen,
        'CodigoReferencia_proveedor'=> $codProv,
        'MarcaProductos'            => $marcaProd,
        'descripcion_variante'      => $descVar,
        'DescripcionCompleta'       => $descComp,
        'ImagenProducto'            => $nombreImagen, // guardamos el nombre final (SKU.ext o SKU-n.ext)
        'icbper'                    => $icbper,
        'unidadMedida'              => $um,
        'Observaciones'             => $obs,
        'Estado'                    => 1,
    ]);
    if (!$idVar) {
        echo json_encode(['success' => false, 'message' => 'No se pudo crear variante']);
        exit;
    }

    
    // 3) Si se ha enviado info de precio y hay sede, crear vigencia de precio (sin cortar el flujo)
$warning = null;
if ($idSede > 0 && $divisa !== '' && $precioV > 0) {
    // permitimos compra NULL/0 según tu nueva estructura
    $okPrecio = $this->producto->actualizarPrecioVentaVariante(
        $idSede, $idVar, $divisa, $precioV, $utilidad, $tipoIGV, null, $divisa ?: null, ($precioC !== 0.0 ? $precioC : null)
    );
    if (!$okPrecio) {
        $warning = 'No se pudo registrar la vigencia de precio.';
    }
}

    echo json_encode([
        'success' => true,
        'data' => [
            'id_producto_base'     => $idBase,
            'id_producto_variante' => $idVar,
            'SKU_Productos'        => $SKU,
            'ImagenProducto'       => $nombreImagen,
            'DescripcionCompleta'  => $descComp,
            'warning' => $warning
        ]
    ]);
    exit;
}

    public function listarMarcasVehiculo() {
        $MarcasVehiculos = $this->producto->listarMarcasVehiculo();
        echo json_encode(['success' => true, 'data' => $MarcasVehiculos]);
        exit;
    }

    public function subirImagenProducto() {
    // Tamaño máx (3MB)
    $maxBytes = 3 * 1024 * 1024;
    $nombreBase = trim($_POST['nombre_base'] ?? '');

    if (!isset($_FILES['ImagenProducto']) || empty($_FILES['ImagenProducto']['name'])) {
        echo json_encode(['success' => false, 'message' => 'No se recibió archivo.']);
        exit;
    }
    if ($_FILES['ImagenProducto']['error'] !== UPLOAD_ERR_OK) {
        $code = $_FILES['ImagenProducto']['error'];
        echo json_encode(['success' => false, 'message' => "Error al subir archivo (código $code)."]);
        exit;
    }
    if ($_FILES['ImagenProducto']['size'] > $maxBytes) {
        echo json_encode(['success' => false, 'message' => 'El archivo excede 3MB.']);
        exit;
    }
    if (!is_uploaded_file($_FILES['ImagenProducto']['tmp_name'])) {
        echo json_encode(['success' => false, 'message' => 'Upload inválido (tmp_name).']);
        exit;
    }

    // Extensión permitida
    $mime = $_FILES['ImagenProducto']['type'] ?? '';
    $ext  = strtolower(pathinfo($_FILES['ImagenProducto']['name'], PATHINFO_EXTENSION));
    if (!$ext || !in_array($ext, ['jpg','jpeg','png','webp'])) {
        if ($mime === 'image/jpeg') $ext = 'jpg';
        elseif ($mime === 'image/png') $ext = 'png';
        elseif ($mime === 'image/webp') $ext = 'webp';
        else $ext = 'jpg';
    }
    if ($ext === 'jpeg') $ext = 'jpg';

    // Carpeta destino (RAÍZ/imagen/products)
    $rootDir   = dirname(__DIR__, 1); // controller -> raíz del proyecto (ajústalo si tu árbol difiere)
    $uploadDir = $rootDir . '/image/products/';

    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo crear /imagen/products.']);
            exit;
        }
        @chmod($uploadDir, 0777);
    }

    // Nombre final: si llega nombre_base (SKU), lo usamos; si no, generamos temporal
    $base = preg_replace('/[^A-Z0-9_-]/i', '', $nombreBase ?: '');
    if ($base === '') {
        // temporal único
        $base = 'IMG_' . date('Ymd_His') . '_' . substr(sha1(uniqid('', true)), 0, 6);
    }
    $nombreArchivo = $base . '.' . $ext;
    $destPath = $uploadDir . $nombreArchivo;

    // Evitar sobrescribir
    $i = 1;
    while (file_exists($destPath)) {
        $nombreArchivo = $base . '-' . $i . '.' . $ext;
        $destPath = $uploadDir . $nombreArchivo;
        $i++;
    }

    if (!@move_uploaded_file($_FILES['ImagenProducto']['tmp_name'], $destPath)) {
        error_log("move_uploaded_file fallo hacia $destPath");
        echo json_encode(['success' => false, 'message' => 'No se pudo mover el archivo. Revisa permisos.']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'nombre_archivo' => $nombreArchivo,
            'ruta_relativa'  => 'imagen/products/' . $nombreArchivo
        ]
    ]);
    exit;
}

public function actualizarProducto() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']); exit;
    }

    if (session_status() === PHP_SESSION_NONE) session_start();
    $idSede = (int)($_SESSION['id_sede'] ?? ($_POST['id_sede'] ?? 0));
    $nombreSede = $_SESSION['nombre_sede'] ?? '';

    $idBase = (int)($_POST['id_producto_base'] ?? 0);
    $idVar  = (int)($_POST['id_producto_variante'] ?? 0);
    if ($idBase<=0 || $idVar<=0) {
        echo json_encode(['success'=>false,'message'=>'IDs inválidos']); exit;
    }

    // Datos de producto base
    $dataBase = [
        'id_producto_base'   => $idBase,
        'OEM_productos'      => trim($_POST['OEM_productos'] ?? ''),
        'MarcaVehiculo'      => trim($_POST['MarcaVehiculo'] ?? ''),
        'ModeloVehiculo'     => trim($_POST['ModeloVehiculo'] ?? ''),
        'CilindradaVehiculo' => trim($_POST['CilindradaVehiculo'] ?? ''),
        'MotorVehiculo'      => trim($_POST['MotorVehiculo'] ?? ''),
        'AñoInicialVehiculo' => trim($_POST['AñoInicialVehiculo'] ?? ''),
        'AñoFinVehiculo'     => trim($_POST['AñoFinVehiculo'] ?? ''),
    ];

    // Datos de variante
    $dataVar = [
        'id_producto_variante'       => $idVar,
        'SKU_Productos'              => trim($_POST['SKU_Productos'] ?? ''),
        'CodigoReferencia_proveedor' => trim($_POST['CodigoReferencia_proveedor'] ?? ''),
        'unidadMedida'               => trim($_POST['unidadMedida'] ?? ''),
        'MarcaProductos'             => trim($_POST['MarcaProductos'] ?? ''),
        'OrigenProductos'            => trim($_POST['OrigenProductos'] ?? ''),
        'descripcion_variante'       => trim($_POST['descripcion_variante'] ?? ''),
        'DescripcionCompleta'        => trim($_POST['DescripcionCompleta'] ?? ''),
        'Observaciones'              => trim($_POST['Observaciones'] ?? ''),
        'icbper'                     => (isset($_POST['icbper']) && $_POST['icbper']=='1') ? 1 : 0,
    ];

    // Validación mínima
    if ($dataVar['SKU_Productos'] === '' || $dataVar['MarcaProductos'] === '' || $dataVar['OrigenProductos'] === '') {
        echo json_encode(['success'=>false,'message'=>'SKU, Marca producto y Origen son obligatorios']); exit;
    }

  // 1) Actualizar base/variante
$okBaseRaw = $this->producto->actualizarProductoBase($dataBase);
$okVarRaw  = $this->producto->actualizarProductoVariante($dataVar);

// Tratar 0 filas afectadas como OK (solo consideramos error si devuelve FALSE real)
$okBase = ($okBaseRaw !== false);
$okVar  = ($okVarRaw  !== false);

error_log('[actualizarProducto] updBase='.var_export($okBaseRaw,true).' updVar='.var_export($okVarRaw,true));

// NO hacemos exit aquí; intentamos también crear/actualizar el precio
$huboCambios = $okBase || $okVar;

// 2) ====== Precio por sede (opcional) ======
$divisa       = trim($_POST['moneda_venta'] ?? '');
$pvStr        = $_POST['precio_venta'] ?? '';
$precioResp   = null;
$info         = [];

if ($idSede>0 && $divisa!=='' && $pvStr!=='') {
    $precioVenta = (float)$pvStr;
    $utilidadPct = (isset($_POST['utilidad_pct']) && $_POST['utilidad_pct']!=='') ? (float)$_POST['utilidad_pct'] : null;
    $tipoIGV     = (isset($_POST['tipoIGV']) && $_POST['tipoIGV']!=='') ? (int)$_POST['tipoIGV'] : 10;

    $hadPrev = $this->producto->tienePrecioVigente($idSede, $idVar);
    $okPrecio = $this->producto->actualizarPrecioVentaVariante(
        $idSede, $idVar, $divisa, $precioVenta, $utilidadPct, $tipoIGV, null, $divisa, null
    );

    if ($okPrecio) {
        $huboCambios = true; // <<<<<< MARCAR CAMBIO
        $precioResp = [
            'precio_venta_fmt' => number_format($precioVenta, 2).' '.$divisa,
            'precio_desde'     => date('Y-m-d H:i:s')
        ];
        $stock = $this->producto->obtenerStockEnSede($idSede, $idVar);
        $info = [
            'precio_creado' => !$hadPrev,
            'sede'          => $nombreSede,
            'sin_stock'     => ($stock <= 0),
            'mensaje'       => (!$hadPrev
                ? "Se registró precio para la sede '{$nombreSede}'." . (($stock<=0) ? " No hay stock ni ingresos registrados." : "")
                : ""
            ),
        ];
    }
}

// 3) Si no hubo cambios en nada, igual devolvemos success (para no bloquear el flujo)
echo json_encode([
    'success' => true,
    'data' => [
        'precio_venta_fmt' => $precioResp['precio_venta_fmt'] ?? null,
        'precio_desde'     => $precioResp['precio_desde'] ?? null,
        'info'             => $info,
        'sin_cambios'      => !$huboCambios ? 1 : 0
    ]
]);
exit;
}

public function exportarExcel() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    @ini_set('memory_limit', '1024M');
    @ini_set('max_execution_time', '600');
    @ini_set('zlib.output_compression', '0'); // importante para streaming

    // ——— Lee lo necesario de la sesión y suelta el candado
    $idRol  = (int)($_SESSION['id_rol'] ?? 0);
    $nomRol = trim($_SESSION['nombre_rol'] ?? '');
    $idSede = (int)($_SESSION['id_sede'] ?? 0);
    $isAdmin = (strcasecmp($nomRol, 'Administrador') === 0) || ($idRol === 1);
    session_write_close();

    // ——— Limpia buffers (evita BOM/espacios que rompen el zip) 
    while (ob_get_level() > 0) { @ob_end_clean(); }

    // ——— Entrada
    $search = $_GET['search'] ?? '';

    // ——— Datos
    $rows = $this->producto->obtenerParaExport($isAdmin ? null : $idSede, $search);

    // ——— Encabezados
    $headers = [
        'Sede ID','Sede','SKU','Descripcion Variante','Descripcion Completa',
        'Marca Producto','Origen','OEM','Marca Vehiculo','Modelo',
        'Cilindrada','Motor','Año Inicial','Año Final','Unidad',
        'ICBPER','Stock Actual','Stock Reservado','Stock Minimo',
        'Moneda Venta','Precio Venta','Moneda Compra','Precio Compra',
        'Utilidad %','Tipo IGV','Precio Desde'
    ];

    // ——— Tabla (primera fila = headers)
    $table = [];
    $table[] = $headers;
    foreach ($rows as $r) {
        $table[] = [
            (int)($r['id_sede'] ?? 0),
            (string)($r['nombre_sede'] ?? ''),
            (string)($r['SKU_Productos'] ?? ''),
            (string)($r['descripcion_variante'] ?? ''),
            (string)($r['DescripcionCompleta'] ?? ''),
            (string)($r['MarcaProductos'] ?? ''),
            (string)($r['OrigenProductos'] ?? ''),
            (string)($r['OEM_productos'] ?? ''),
            (string)($r['MarcaVehiculo'] ?? ''),
            (string)($r['ModeloVehiculo'] ?? ''),
            (string)($r['CilindradaVehiculo'] ?? ''),
            (string)($r['MotorVehiculo'] ?? ''),
            (string)($r['AñoInicialVehiculo'] ?? ''),
            (string)($r['AñoFinVehiculo'] ?? ''),
            (string)($r['unidadMedida'] ?? ''),
            !empty($r['icbper']) ? 1 : 0,
            (float)($r['stock_actual'] ?? 0),
            (float)($r['stock_reservado'] ?? 0),
            (float)($r['stock_min'] ?? 0),
            (string)($r['moneda_venta'] ?? ''),
            ($r['precio_venta']!==null && $r['precio_venta']!=='') ? (float)$r['precio_venta'] : '',
            (string)($r['moneda_compra'] ?? ''),
            ($r['precio_compra']!==null && $r['precio_compra']!=='') ? (float)$r['precio_compra'] : '',
            ($r['utilidad_pct']===null || $r['utilidad_pct']==='') ? '' : (float)$r['utilidad_pct'],
            ($r['tipoIGV']===null || $r['tipoIGV']==='') ? '' : (int)$r['tipoIGV'],
            (string)($r['precio_desde'] ?? ''),
        ];
    }

    // ——— XLSX (ZIP con partes OpenXML)
    $tmpPath = tempnam(sys_get_temp_dir(), 'xlsx_');
    $zip = new ZipArchive();
    if ($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        http_response_code(500);
        echo 'No se pudo crear el ZIP temporal.'; exit;
    }

    // docProps (evita avisos de “reparado”)
    $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"
 xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>PHP</Application>
</Properties>');
    $now = htmlspecialchars(date('c'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:dcterms="http://purl.org/dc/terms/"
 xmlns:dcmitype="http://purl.org/dc/dcmitype/"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:creator>Export Productos</dc:creator>
  <cp:lastModifiedBy>Export Productos</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:modified>
</cp:coreProperties>');

    // [Content_Types].xml (incluye workbook, hoja, estilos y docProps)
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>');

    // _rels/.rels → apunta al workbook
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

    // xl/workbook.xml
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
 xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Productos" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

    // xl/_rels/workbook.xml.rels → hoja + estilos (FALTABA estilos)
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>');

    // xl/styles.xml
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><color rgb="FF222222"/><name val="Calibri"/></font>
  </fonts>
  <fills count="2">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF2F4F7"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1"><border/></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="1" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
  </cellXfs>
</styleSheet>');

    // xl/worksheets/sheet1.xml
    $colWidths = [12,18,18,36,70,20,14,16,18,18,12,14,12,12,10,10,14,14,14,12,16,12,16,12,10,22];
    $colsXml = '';
    $cIdx=1; foreach ($colWidths as $w) { $colsXml .= '<col min="'.$cIdx.'" max="'.$cIdx.'" width="'.$w.'" customWidth="1"/>'; $cIdx++; }

    $escape = function($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); };
    $toCoord = function($colIdx, $rowIdx){
        $letters=''; $n=$colIdx;
        while ($n>0) { $mod=($n-1)%26; $letters=chr(65+$mod).$letters; $n=intdiv($n-1,26); }
        return $letters.$rowIdx;
    };
    $cell = function($colIdx, $rowIdx, $val, $style = 0) use ($escape,$toCoord) {
        $r = $toCoord($colIdx, $rowIdx);
        if ($val === '' || is_string($val)) {
            return '<c r="'.$r.'" t="inlineStr" s="'.$style.'"><is><t>'.$escape((string)$val).'</t></is></c>';
        } else {
            $num = is_bool($val) ? ($val?1:0) : (0 + $val);
            return '<c r="'.$r.'" s="'.$style.'"><v>'.$num.'</v></c>';
        }
    };

    $sheetXML = '<?xml version="1.0" encoding="UTF-8"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
 xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <cols>'.$colsXml.'</cols>
  <sheetData>';

    // Header
    $rowIdx = 1;
    $sheetXML .= '<row r="'.$rowIdx.'">';
    $col=1; foreach ($headers as $h) { $sheetXML .= $cell($col,$rowIdx,(string)$h,1); $col++; }
    $sheetXML .= '</row>';
    $rowIdx++;

    // Data
    foreach ($table as $i=>$arr) {
        if ($i===0) continue;
        $sheetXML .= '<row r="'.$rowIdx.'">';
        $col=1; foreach ($arr as $v) { $sheetXML .= $cell($col,$rowIdx,$v,0); $col++; }
        $sheetXML .= '</row>';
        $rowIdx++;
    }

    $sheetXML .= '</sheetData></worksheet>';
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXML);

    $zip->close();
    clearstatcache(true, $tmpPath);

    $fileName = 'productos_'.date('Ymd_His').'.xlsx';
    $fileSize = filesize($tmpPath);

    // ——— Headers (claves para que el fetch reciba Content-Length y haga stream)
    header_remove('X-Powered-By');
    header('Content-Description: File Transfer');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="'.$fileName.'"');
    header('Content-Length: '.$fileSize);
    header('Cache-Control: private, no-transform, no-store, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('X-Accel-Buffering: no'); // evita buffering en Nginx si aplica
    // NO enviar Content-Encoding gzip (zlib desactivado arriba)

    // ——— Stream en chunks
    $chunk = 1024 * 1024;
    $fh = fopen($tmpPath, 'rb');
    while (!feof($fh)) {
        echo fread($fh, $chunk);
        @ob_flush(); flush();
    }
    fclose($fh);
    @unlink($tmpPath);
    exit;
}

public function crearPrecioSede() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $idSede = (int)($_SESSION['id_sede'] ?? ($_POST['id_sede'] ?? 0));
    $idVar  = (int)($_POST['id_producto_variante'] ?? 0);
    $divisa = trim($_POST['moneda_venta'] ?? ($_POST['TipoDivisa'] ?? ''));
    $pvStr  = $_POST['precio_venta'] ?? ($_POST['PrecioUnitario_Venta'] ?? '');
    $util   = $_POST['utilidad_pct'] ?? ($_POST['Utilidad'] ?? '');
    $tipoIGV = isset($_POST['tipoIGV']) ? (int)$_POST['tipoIGV'] : (isset($_POST['TipoIGV']) ? (int)$_POST['TipoIGV'] : 10);

    if ($idSede<=0 || $idVar<=0 || $divisa==='' || $pvStr==='') {
        echo json_encode(['success'=>false, 'message'=>'Parámetros inválidos']); exit;
    }

    // Normaliza valores
    $precioVenta = (float)$pvStr;            // vendrá con 3 decimales, MySQL (12,2) redondea
    $utilidadPct = ($util === '' ? null : (float)$util);
    $monedaCompra = $divisa;                 // igualamos a venta
    $precioCompra = null;                    // NO exigimos compra

    // Insertar nueva vigencia (cierra la anterior si corresponde)
    $ok = $this->producto->actualizarPrecioVentaVariante(
        $idSede, $idVar, $divisa, $precioVenta, $utilidadPct, $tipoIGV, null, $monedaCompra, $precioCompra
    );

    if (!$ok) {
        echo json_encode(['success'=>false,'message'=>'No se pudo registrar la vigencia de precio']); exit;
    }

    // opcional: saber si había precio vigente
    $hadPrev = $this->producto->tienePrecioVigente($idSede, $idVar); // ojo: aquí devolverá true si llamas después del insert
    $nombreSede = $_SESSION['nombre_sede'] ?? '';

    echo json_encode([
        'success' => true,
        'data' => [
            'precio_venta_fmt' => number_format($precioVenta, 2).' '.$divisa,
            'precio_desde'     => date('Y-m-d H:i:s'),
            'info' => [
                'precio_creado' => true,
                'sede'          => $nombreSede,
                'sin_stock'     => ($this->producto->obtenerStockEnSede($idSede, $idVar) <= 0),
                'mensaje'       => "Se registró precio para la sede '{$nombreSede}'."
            ]
        ]
    ]);
    exit;
}

    /** AHORA ACTUALIZA PRECIOS INSERTANDO EN precios_por_sede */
    public function actualizarPrecioVentaVariante() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $idSede = (int)($_SESSION['id_sede'] ?? ($_POST['id_sede'] ?? 0));

        $idVar   = (int)($_POST['id_producto_variante'] ?? 0);
        $pvStr   = $_POST['precio_venta'] ?? ($_POST['PrecioUnitario_Venta'] ?? '');  // compatibilidad
        $divisa  = trim($_POST['moneda_venta'] ?? ($_POST['TipoDivisa'] ?? ''));
        $utilStr = $_POST['utilidad_pct'] ?? ($_POST['Utilidad'] ?? '');
        $tipoIGV = isset($_POST['tipoIGV']) ? (int)$_POST['tipoIGV'] : (isset($_POST['TipoIGV']) ? (int)$_POST['TipoIGV'] : null);
        if ($tipoIGV === null) $tipoIGV = 10;  // 👈 default 10

        $monedaCompra = $_POST['moneda_compra'] ?? $divisa;
        $precioCompra = isset($_POST['precio_compra']) ? (float)$_POST['precio_compra'] : 0.0;
        $desde        = $_POST['desde'] ?? null;

        if ($idSede<=0 || $idVar <= 0 || $pvStr === '' || $divisa === '') {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
            exit;
        }

        $precioVenta = (float)$pvStr;
        $utilidadPct = ($utilStr === '' ? null : (float)$utilStr);

        if (!($precioCompra > 0)) {
  echo json_encode(['success' => false, 'message' => 'Debe registrar un precio de compra…']);
  exit;
}

        $ok = $this->producto->actualizarPrecioVentaVariante(
            $idSede, $idVar, $divisa, $precioVenta, $utilidadPct, $tipoIGV, $desde, $monedaCompra, $precioCompra
        );

        if ($ok) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id_producto_variante' => $idVar,
                    'precio_venta' => number_format($precioVenta, 2, '.', ''),
                    'moneda_venta' => $divisa,
                    'utilidad_pct' => $utilidadPct,
                    'tipoIGV' => $tipoIGV,
                    'desde' => $desde ?: date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo registrar la vigencia de precio']);
        }
        exit;
    }

    public function crearCategoria() {
        $data = [
            'clasificacion_general' => $_POST['clasificacion_general'] ?? '',
            'nombre_especifico' => $_POST['nombre_especifico'] ?? ''
        ];
        $id = $this->producto->insertarCategoria($data);

        if ($id) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'id_categoria' => $id,
                    'nombre_completo' => $data['clasificacion_general'] . ' - ' . $data['nombre_especifico']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo registrar la categoría']);
        }
        exit;
    }

    public function generarSKU() {
        $idCategoria = (int)($_GET['id_categoria'] ?? 0);
        $nombreCategoria = $_GET['nombre_categoria'] ?? '';
        $nombreMarca = $_GET['nombre_marca'] ?? '';

        if ($idCategoria === 0 || $nombreCategoria === '' || $nombreMarca === '') {
            echo json_encode(['success' => false, 'message' => 'Faltan datos']);
            exit;
        }

        $letras = strtoupper(substr(trim($nombreCategoria), 0, 2));
        $idMarca = $this->producto->obtenerIdMarcaPorNombre($nombreMarca);
        if (!$idMarca) {
            echo json_encode(['success' => false, 'message' => 'Marca no encontrada']);
            exit;
        }

        $count = $this->producto->contarSKUsSimilares($idCategoria, $letras, $idMarca);
        $correlativo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $sku = "{$idCategoria}{$letras}{$idMarca}{$correlativo}";
        echo json_encode(['success' => true, 'sku' => $sku]);
        exit;
    }
}