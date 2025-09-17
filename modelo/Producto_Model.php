<?php
require_once "BaseModel.php";

class Producto extends BaseModel {

    /* =========================
       HELPERS DE PRECIOS VIGENTES
       ========================= */
    private function subqueryPrecioVigente($idSede) {
    // Precio vigente = fila con hasta IS NULL para esa sede
    $idSede = (int)$idSede;
    return "
      LEFT JOIN precios_por_sede pps
        ON pps.id_producto_variante = pv.id_producto_variante
       AND pps.id_sede = {$idSede}
       AND pps.hasta IS NULL
    ";
}

    /** Inserta una vigencia de precio por sede y cierra la anterior si corresponde */
   private function setPrecioPorSede(
    $idSede,
    $idVariante,
    $monedaCompra,
    $precioCompra,
    $monedaVenta,
    $precioVenta,
    $utilidadPct = null,
    $tipoIGV = null,
    $desde = null
) {
    $idSede      = (int)$idSede;
    $idVariante  = (int)$idVariante;
    $monedaCompra= trim((string)$monedaCompra);
    $monedaVenta = trim((string)$monedaVenta);
    $precioCompra = ($precioCompra === null || $precioCompra === '') ? null : (float)$precioCompra;
    $precioVenta = (float)$precioVenta;
    $utilidadPct  = ($utilidadPct  === null || $utilidadPct  === '') ? null : (float)$utilidadPct;
    $tipoIGV     = $tipoIGV !== null ? (int)$tipoIGV : 10;
    $desde       = $desde ? $desde : date('Y-m-d H:i:s');

    if ($idSede<=0 || $idVariante<=0 || $monedaVenta==='' || $precioVenta<=0) {
        return false;
    }

    // --- TRANSACCIÓN ---
    mysqli_begin_transaction($this->cn);
    try {
        // 1) Cerrar la vigencia previa si se solapa con "desde"
        $sqlCerrar = "
            UPDATE precios_por_sede
               SET hasta = DATE_SUB(?, INTERVAL 1 SECOND)
             WHERE id_sede = ?
               AND id_producto_variante = ?
               AND desde <= ?
               AND (hasta IS NULL OR hasta >= ?)
        ";
        // params: desde, id_sede, id_variante, desde, desde
        $paramsCerrar = [ $desde, $idSede, $idVariante, $desde, $desde ];
        $typesCerrar  = "sii ss";
        // ojo: sin espacios en el string de tipos
        $typesCerrar  = "siiss";

        $this->actualizarPreparado($sqlCerrar, $paramsCerrar, $typesCerrar);

        // 2) Insertar la nueva vigencia
        $sqlIns = "
            INSERT INTO precios_por_sede
                (id_sede, id_producto_variante, moneda_compra, precio_compra, utilidad_pct, moneda_venta, precio_venta, tipoIGV, desde, hasta)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, NULL)
        ";
        // Tipos: i (sede), i (var), s (mon_compra), d (precio_compra),
        //        d (utilidad_pct, puede ser NULL), s (mon_venta),
        //        d (precio_venta), i (tipoIGV, puede ser NULL), s (desde)
        $paramsIns = [
            $idSede,
            $idVariante,
            $monedaCompra,
            $precioCompra,
            $utilidadPct,
            $monedaVenta,
            $precioVenta,
            $tipoIGV,
            $desde
        ];
        $typesIns = "iisddsdis";

        $insertId = $this->insertarPreparado($sqlIns, $paramsIns, $typesIns);

        mysqli_commit($this->cn);
        return $insertId;
    } catch (\Throwable $e) {
        mysqli_rollback($this->cn);
        Conexion::logError($e);
        return false;
    }
}


public function actualizarProductoBase($d) {
    $sql = "
      UPDATE `productos_base`
         SET `OEM_productos`=?,
             `MarcaVehiculo`=?,
             `ModeloVehiculo`=?,
             `CilindradaVehiculo`=?,
             `MotorVehiculo`=?,
             `AñoInicialVehiculo`=?,
             `AñoFinVehiculo`=?
       WHERE `id_producto_base`=?
       LIMIT 1
    ";
    $params = [
        (string)($d['OEM_productos'] ?? ''),
        (string)($d['MarcaVehiculo'] ?? ''),
        (string)($d['ModeloVehiculo'] ?? ''),
        (string)($d['CilindradaVehiculo'] ?? ''),
        (string)($d['MotorVehiculo'] ?? ''),
        (string)($d['AñoInicialVehiculo'] ?? ''),
        (string)($d['AñoFinVehiculo'] ?? ''),
        (int)($d['id_producto_base'] ?? 0),
    ];
    $types = "sssssssi";
    return $this->actualizarPreparado($sql, $params, $types);
}

    public function tienePrecioVigente($idSede, $idVariante) {
    $idSede = (int)$idSede;
    $idVariante = (int)$idVariante;
    $sql = "
        SELECT 1 
        FROM precios_por_sede 
        WHERE id_sede = $idSede 
          AND id_producto_variante = $idVariante 
          AND hasta IS NULL
        LIMIT 1
    ";
    $res = $this->consultar($sql);
    return !empty($res);
}

public function obtenerStockEnSede($idSede, $idVariante) {
    $idSede = (int)$idSede;
    $idVariante = (int)$idVariante;
    $sql = "
        SELECT IFNULL(stock_actual, 0) AS stock_actual
        FROM stock_por_sede
        WHERE id_sede = $idSede
          AND id_producto_variante = $idVariante
        LIMIT 1
    ";
    $res = $this->consultar($sql);
    return (float)($res[0]['stock_actual'] ?? 0);
}

public function obtenerParaExport(?int $idSedeFilter, string $search = ''): array
{
    // WHERE de estado y (opcional) búsqueda
    $where = "pv.Estado = 1";
    $params = [];
    $types  = "";

    if ($search !== '') {
        $like = "%{$search}%";
        $where .= " AND (
            pv.DescripcionCompleta LIKE ?
            OR pv.descripcion_variante LIKE ?
            OR pv.Observaciones LIKE ?
            OR pv.SKU_Productos LIKE ?
            OR pv.CodigoReferencia_proveedor LIKE ?
            OR pb.OEM_productos LIKE ?
            OR pb.MarcaVehiculo LIKE ?
            OR pb.ModeloVehiculo LIKE ?
        )";
        // misms cantidad de ? que campos arriba
        for ($i = 0; $i < 8; $i++) {
            $params[] = $like;
            $types   .= "s";
        }
    }

    // Para ADMIN: exporta TODAS las sedes -> CROSS JOIN sede s
    // Para otros roles: solo su sede -> INNER JOIN sede s ON s.id_sede = ?
    $sedeJoin = "CROSS JOIN sede s";
    if (!is_null($idSedeFilter) && $idSedeFilter > 0) {
        $sedeJoin = "INNER JOIN sede s ON s.id_sede = ?";
        $params[] = (int)$idSedeFilter;
        $types   .= "i";
    }

    // Traemos stock por sede y el precio vigente (pps.hasta IS NULL) por sede
    $sql = "
        SELECT
            s.id_sede,
            s.nombre AS nombre_sede,

            pb.id_producto_base,
            pb.OEM_productos,
            pb.MarcaVehiculo, pb.ModeloVehiculo,
            pb.CilindradaVehiculo, pb.MotorVehiculo,
            pb.AñoInicialVehiculo, pb.AñoFinVehiculo,

            pv.id_producto_variante,
            pv.SKU_Productos,
            pv.descripcion_variante,
            pv.MarcaProductos,
            pv.Observaciones,
            pv.DescripcionCompleta,
            pv.ImagenProducto,
            pv.OrigenProductos,
            pv.icbper,
            pv.unidadMedida,

            IFNULL(ss.stock_actual, 0)   AS stock_actual,
            IFNULL(ss.stock_reservado,0) AS stock_reservado,
            IFNULL(ss.stock_min, 0)      AS stock_min,

            pps.moneda_compra,
            pps.precio_compra,
            pps.moneda_venta,
            pps.precio_venta,
            pps.utilidad_pct,
            pps.tipoIGV,
            pps.desde AS precio_desde
        FROM productos_base pb
        INNER JOIN productos_variantes pv
                ON pb.id_producto_base = pv.id_producto_base
        {$sedeJoin}
        LEFT JOIN stock_por_sede ss
               ON ss.id_sede = s.id_sede
              AND ss.id_producto_variante = pv.id_producto_variante
        LEFT JOIN precios_por_sede pps
               ON pps.id_sede = s.id_sede
              AND pps.id_producto_variante = pv.id_producto_variante
              AND pps.hasta IS NULL
        WHERE {$where}
        ORDER BY s.nombre ASC, pv.DescripcionCompleta ASC
    ";

    return $this->consultaPreparadaMultiple($sql, $params, $types);
}

// Producto_Model.php (agregar dentro de la clase Producto)

public function existeRegistroProductoSede($idSede, $idVariante) {
    $idSede = (int)$idSede;
    $idVariante = (int)$idVariante;
    $sql = "
        SELECT 1
        FROM stock_por_sede
        WHERE id_sede = $idSede
          AND id_producto_variante = $idVariante
        LIMIT 1
    ";
    $res = $this->consultar($sql);
    return !empty($res);
}

    public function actualizarProductoVariante($v) {
    $sql = "
      UPDATE `productos_variantes`
     SET `SKU_Productos`=?,
         `CodigoReferencia_proveedor`=?,
         `unidadMedida`=?,
         `MarcaProductos`=?,
         `OrigenProductos`=?,
         `descripcion_variante`=?,
         `DescripcionCompleta`=?,
         `Observaciones`=?,
         `icbper`=?
   WHERE `id_producto_variante`=?
   LIMIT 1
    ";
    $params = [
        (string)($v['SKU_Productos'] ?? ''),
        (string)($v['CodigoReferencia_proveedor'] ?? ''),
        (string)($v['unidadMedida'] ?? 'NIU'),
        (string)($v['MarcaProductos'] ?? ''),
        (string)($v['OrigenProductos'] ?? ''),
        (string)($v['descripcion_variante'] ?? ''),
        (string)($v['DescripcionCompleta'] ?? ''),
        (string)($v['Observaciones'] ?? ''),
        (int)($v['icbper'] ?? 0),
        (int)($v['id_producto_variante'] ?? 0),
    ];
    // 👇 ESTA es la clave
    $types = "ssssssssii";
    return $this->actualizarPreparado($sql, $params, $types);
}

    /* =========================
       LISTADOS / BÚSQUEDAS
       ========================= */
    public function buscarProductos($idSede, $search = '', $limit = 10, $offset = 0) {
        $idSede = (int)$idSede;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $search = addslashes($search);

        $joinPrecio = $this->subqueryPrecioVigente($idSede);

        $sql = "
            SELECT 
                pb.*, 
                pv.id_producto_variante,
                pv.SKU_Productos,
                pv.descripcion_variante,
                pv.MarcaProductos,
                pv.Observaciones,
                pv.DescripcionCompleta,
                pv.ImagenProducto,
                pv.OrigenProductos,
                pv.CodigoReferencia_proveedor,
                pv.icbper,
                pv.unidadMedida,
                IFNULL(ss.stock_actual, 0) AS stock_actual,
                cat.clasificacion_general,
                cat.nombre_especifico,
                -- precios vigentes
                pps.moneda_venta,
                pps.precio_venta,
                pps.utilidad_pct,
                pps.moneda_compra,
                pps.precio_compra,
                pps.tipoIGV,
                pps.desde AS precio_desde,
                pps.hasta AS precio_hasta
            FROM productos_base pb
            INNER JOIN productos_variantes pv ON pb.id_producto_base = pv.id_producto_base
            LEFT JOIN stock_por_sede ss 
                   ON ss.id_producto_variante = pv.id_producto_variante 
                  AND ss.id_sede = {$idSede}
            LEFT JOIN categoriaProductos cat ON pb.id_categoriaProductos = cat.id_categoria
            {$joinPrecio}
            WHERE pv.Estado = 1
              AND (
                    pv.DescripcionCompleta LIKE '%{$search}%'
                 OR pv.descripcion_variante LIKE '%{$search}%'
                 OR pv.Observaciones LIKE '%{$search}%'
                 OR pv.SKU_Productos LIKE '%{$search}%'
              )
            ORDER BY pv.DescripcionCompleta ASC
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->consultar($sql);
    }

    public function buscarProductosCompras($idSede, $search = '', $limit = 20, $offset = 0, $categoria = '', $marca = '') {
        $idSede = (int)$idSede;
        $limit = (int)$limit;
        $offset = (int)$offset;
        $search = addslashes($search);
        $categoria = addslashes($categoria);
        $marca = addslashes($marca);

        $where = "pv.Estado = 1";
        if ($search !== '') {
            $like = "%$search%";
            $where .= " AND (
                pv.DescripcionCompleta LIKE '$like' OR
                pv.descripcion_variante LIKE '$like' OR
                pv.Observaciones LIKE '$like' OR
                pv.SKU_Productos LIKE '$like' OR
                pv.CodigoReferencia_proveedor LIKE '$like' OR
                pb.OEM_productos LIKE '$like' OR
                pb.MarcaVehiculo LIKE '$like' OR
                pb.ModeloVehiculo LIKE '$like'
            )";
        }
        if ($categoria !== '') {
            $where .= " AND pb.id_categoriaProductos = '$categoria'";
        }
        if ($marca !== '') {
            $where .= " AND pv.MarcaProductos = '$marca'";
        }

        $joinPrecio = $this->subqueryPrecioVigente($idSede);

        $sql = "
            SELECT 
                pb.id_producto_base,
                pb.OEM_productos,
                pb.MarcaVehiculo, pb.ModeloVehiculo,
                pb.CilindradaVehiculo, pb.MotorVehiculo,
                pb.AñoInicialVehiculo, pb.AñoFinVehiculo,

                pv.id_producto_variante,
                pv.SKU_Productos,
                pv.CodigoReferencia_proveedor,
                pv.descripcion_variante,
                pv.MarcaProductos,
                pv.Observaciones,
                pv.DescripcionCompleta,
                pv.ImagenProducto,
                pv.OrigenProductos,
                pv.icbper,
                pv.unidadMedida,

                IFNULL(ss.stock_actual, 0) AS stock_actual,
                cat.clasificacion_general,
                cat.nombre_especifico,

                -- precios vigentes
                pps.moneda_compra, pps.precio_compra,
                pps.moneda_venta,  pps.precio_venta,
                pps.utilidad_pct,  pps.tipoIGV,
                pps.desde AS precio_desde,
                pps.hasta AS precio_hasta
            FROM productos_base pb
            INNER JOIN productos_variantes pv ON pb.id_producto_base = pv.id_producto_base
            LEFT JOIN stock_por_sede ss ON ss.id_producto_variante = pv.id_producto_variante 
                                       AND ss.id_sede = {$idSede}
            LEFT JOIN categoriaProductos cat ON pb.id_categoriaProductos = cat.id_categoria
            {$joinPrecio}
            WHERE {$where}
            ORDER BY pv.DescripcionCompleta ASC
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->consultar($sql);
    }

    public function contarTotalCompras($idSede, $search = '', $categoria = '', $marca = '') {
        // Nota: el total no depende de precios, se mantiene.
        $search = addslashes($search);
        $categoria = addslashes($categoria);
        $marca = addslashes($marca);

        $where = "pv.Estado = 1";
        if ($search !== '') {
            $like = "%$search%";
            $where .= " AND (
                pv.DescripcionCompleta LIKE '$like' OR
                pv.descripcion_variante LIKE '$like' OR
                pv.Observaciones LIKE '$like' OR
                pv.SKU_Productos LIKE '$like' OR
                pv.CodigoReferencia_proveedor LIKE '$like' OR
                pb.OEM_productos LIKE '$like' OR
                pb.MarcaVehiculo LIKE '$like' OR
                pb.ModeloVehiculo LIKE '$like'
            )";
        }
        if ($categoria !== '') {
            $where .= " AND pb.id_categoriaProductos = '$categoria'";
        }
        if ($marca !== '') {
            $where .= " AND pv.MarcaProductos = '$marca'";
        }

        $sql = "
            SELECT COUNT(*) AS total
            FROM productos_base pb
            INNER JOIN productos_variantes pv ON pb.id_producto_base = pv.id_producto_base
            WHERE $where
        ";

        $res = $this->consultar($sql);
        return $res[0]['total'] ?? 0;
    }

    public function listarCategorias() {
        $sql = "SELECT * FROM categoriaProductos";
        return $this->consultar($sql);
    }

    public function listarMarcasVehiculo() {
        $sql = "SELECT * FROM marcas_vehiculos";
        return $this->consultar($sql);
    }

    public function listarCategoriasClasificacionGeneral() {
        $sql = "
            SELECT clasificacion_general 
            FROM categoriaProductos
            GROUP BY clasificacion_general
            ORDER BY clasificacion_general
        ";
        return $this->consultar($sql);
    }

    public function listarUnidadMedidas() {
        $sql = "
            SELECT * FROM unidades_medida_unece
            ORDER BY 
              CASE 
                WHEN id_unidad = 1 THEN 0
                WHEN id_unidad = 3 THEN 1
                WHEN id_unidad = 38 THEN 2
                ELSE 3
              END,
              nombre ASC
        ";
        return $this->consultar($sql);
    }

    public function buscarProductoBaseSimilar($idCategoria,$OEM,$marca,$modelo,$cil,$motor,$aIni,$aFin){
        $idCategoria = (int)$idCategoria;
        $OEM   = addslashes($OEM);
        $marca = addslashes($marca);
        $modelo= addslashes($modelo);
        $cil   = addslashes($cil);
        $motor = addslashes($motor);
        $aIni  = addslashes($aIni);
        $aFin  = addslashes($aFin);
        $sql = "
            SELECT id_producto_base FROM productos_base
            WHERE id_categoriaProductos = $idCategoria
              AND IFNULL(OEM_productos,'') = '$OEM'
              AND IFNULL(MarcaVehiculo,'') = '$marca'
              AND IFNULL(ModeloVehiculo,'') = '$modelo'
              AND IFNULL(CilindradaVehiculo,'') = '$cil'
              AND IFNULL(MotorVehiculo,'') = '$motor'
              AND IFNULL(AñoInicialVehiculo,'') = '$aIni'
              AND IFNULL(AñoFinVehiculo,'') = '$aFin'
            LIMIT 1
        ";
        $res = $this->consultar($sql);
        return $res[0]['id_producto_base'] ?? 0;
    }

    public function insertarProductoBase($data){
        $idCat = (int)($data['id_categoriaProductos'] ?? 0);
        $OEM   = addslashes($data['OEM_productos'] ?? '');
        $marca = addslashes($data['MarcaVehiculo'] ?? '');
        $modelo= addslashes($data['ModeloVehiculo'] ?? '');
        $cil   = addslashes($data['CilindradaVehiculo'] ?? '');
        $motor = addslashes($data['MotorVehiculo'] ?? '');
        $aIni  = addslashes($data['AñoInicialVehiculo'] ?? '');
        $aFin  = addslashes($data['AñoFinVehiculo'] ?? '');
        $sql = "
            INSERT INTO productos_base
            (id_categoriaProductos, OEM_productos, MarcaVehiculo, ModeloVehiculo, CilindradaVehiculo, MotorVehiculo, AñoInicialVehiculo, AñoFinVehiculo)
            VALUES ($idCat, '$OEM', '$marca', '$modelo', '$cil', '$motor', '$aIni', '$aFin')
        ";
        return $this->insertar($sql);
    }

    /** AHORA SIN CAMPOS DE PRECIO NI TIPOIGV EN VARIANTE */
    public function insertarProductoVariante($v){
        $idBase = (int)$v['id_producto_base'];
        $SKU    = addslashes($v['SKU_Productos']);
        $origen = addslashes($v['OrigenProductos']);
        $codProv= addslashes($v['CodigoReferencia_proveedor']);
        $marcaP = addslashes($v['MarcaProductos']);
        $descVar= addslashes($v['descripcion_variante']);
        $descC  = addslashes($v['DescripcionCompleta']);
        $img    = !empty($v['ImagenProducto']) ? addslashes($v['ImagenProducto']) : null;
        $icbper = (int)($v['icbper'] ?? 0);
        $um     = addslashes($v['unidadMedida'] ?? 'NIU');
        $obs    = addslashes($v['Observaciones'] ?? '');
        $estado = (int)($v['Estado'] ?? 1);

        $sql = "
            INSERT INTO productos_variantes
            (id_producto_base, SKU_Productos, OrigenProductos, CodigoReferencia_proveedor, MarcaProductos, descripcion_variante, DescripcionCompleta, ImagenProducto, icbper, unidadMedida, Estado, Observaciones)
            VALUES
            ($idBase, '$SKU', '$origen', '$codProv', '$marcaP', '$descVar', '$descC', ".($img?"'$img'":"NULL").", $icbper, '$um', $estado, '$obs')
        ";
        return $this->insertar($sql);
    }

    /** DEPRECADO: antes actualizaba precio en variantes; ahora inserta en precios_por_sede */
    public function actualizarPrecioVentaVariante($idSede, $idVar, $monedaVenta, $precioVenta, $utilidadPct = null, $tipoIGV = null, $desde = null, $monedaCompra = null, $precioCompra = 0) {
        return $this->setPrecioPorSede(
            $idSede, $idVar, 
            $monedaCompra ?: $monedaVenta,   // si no envían moneda_compra, igualamos a venta
            $precioCompra ?: 0,
            $monedaVenta, $precioVenta,
            $utilidadPct, $tipoIGV, $desde
        );
    }

    public function contarTotal($idSede, $search = '') {
        $search = addslashes($search);
        $sql = "
            SELECT COUNT(*) AS total
            FROM productos_base pb
            INNER JOIN productos_variantes pv ON pb.id_producto_base = pv.id_producto_base
            WHERE pv.Estado = 1
              AND (
                    pv.DescripcionCompleta LIKE '%$search%'
                    OR pv.descripcion_variante LIKE '%$search%'
                    OR pv.Observaciones LIKE '%$search%'
                  )
        ";
        $res = $this->consultar($sql);
        return $res[0]['total'] ?? 0;
    }

    public function obtenerStockTodasSedes($idProductoVariante) {
        $id = (int)$idProductoVariante;
        $sql = "
            SELECT 
                s.id_sede,
                s.nombre AS nombre_sede,
                IFNULL(ss.stock_actual, 0) AS stock_actual
            FROM sede s
            LEFT JOIN stock_por_sede ss 
                ON ss.id_sede = s.id_sede 
               AND ss.id_producto_variante = $id
            ORDER BY s.nombre ASC
        ";
        return $this->consultar($sql);
    }

    public function obtenerStockOtrasSedes($idProductoVariante, $idSedeActual) {
        $idProductoVariante = (int)$idProductoVariante;
        $idSedeActual = (int)$idSedeActual;
        $sql = "
            SELECT s.nombre as nombre_sede, ss.stock_actual
            FROM stock_por_sede ss
            INNER JOIN sede s ON s.id_sede = ss.id_sede
            WHERE ss.id_producto_variante = $idProductoVariante AND ss.id_sede != $idSedeActual
        ";
        return $this->consultar($sql);
    }

    public function insertarCategoria($data) {
        $clasificacion = trim($data['clasificacion_general'] ?? '');
        $nombre = trim($data['nombre_especifico'] ?? '');
        if ($clasificacion === '' || $nombre === '') return false;

        $sqlCheck = "
            SELECT id_categoria
            FROM categoriaProductos
            WHERE clasificacion_general = '$clasificacion'
              AND nombre_especifico = '$nombre'
            LIMIT 1
        ";
        $existe = $this->consultar($sqlCheck);
        if (!empty($existe)) return $existe[0]['id_categoria'];

        $sqlInsert = "
            INSERT INTO categoriaProductos (clasificacion_general, nombre_especifico)
            VALUES ('$clasificacion', '$nombre')
        ";
        return $this->insertar($sqlInsert);
    }

    public function obtenerIdMarcaPorNombre($nombreMarca) {
        $nombreMarca = addslashes($nombreMarca);
        $sql = "SELECT id_marcasVehiculos FROM marcas_vehiculos WHERE nombre_marca = '$nombreMarca' LIMIT 1";
        $res = $this->consultar($sql);
        return $res[0]['id_marcasVehiculos'] ?? null;
    }

    public function contarSKUsSimilares($idCategoria, $letras, $idMarca) {
        $prefix = $idCategoria . strtoupper($letras) . $idMarca;
        $sql = "SELECT COUNT(*) as total FROM productos_variantes WHERE SKU_Productos LIKE '$prefix%'";
        $res = $this->consultar($sql);
        return $res[0]['total'] ?? 0;
    }
}