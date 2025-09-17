<?php include('partial/header.php'); ?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/animate.css">
<link rel="stylesheet" type="text/css" href="assets/css/vendors/select2.css">
<?php include('partial/loader.php'); ?>

<style>
    /* Micro-ajustes visuales para esta vista (solo diseño) */
    .card-header h5 {
        font-size: 1rem;
        margin: 0;
    }

    .btn-pill {
        border-radius: 50rem;
    }

    .kpi-totales .list-group-item {
        border: 0;
        padding: .5rem 0;
    }

    .kpi-totales strong {
        font-weight: 700;
    }

    .empty-state {
        padding: 2rem 1rem;
        color: #6c757d;
    }

    .table thead th {
        white-space: nowrap;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }

    .toolbar-busqueda .form-control,
    .toolbar-busqueda .form-select {
        border-radius: 50rem;
    }

    .tag {
        display: inline-block;
        padding: .15rem .5rem;
        border-radius: 50rem;
        font-size: .75rem;
        background: #f1f3f5;
        color: #495057;
    }

    .product-mini {
        line-height: 1.1;
    }

    .product-mini small {
        color: #6c757d;
    }

    /* ====== Nuevo Producto: estilos 2025 ====== */
    @media (min-width: 1200px) {
        .modal-dialog.modal-xxl-95 {
            max-width: 95vw;
        }
    }

    .np-header {
        background: linear-gradient(135deg, #0d6efd, #6610f2);
        color: #fff;
        border-bottom: none;
    }

    .np-header .fa {
        opacity: .9;
    }

    .np-footer {
        background: #f8f9fa;
        border-top: 1px solid rgba(0, 0, 0, .05);
    }

    .np-tabs .nav-link {
        border-radius: 50rem;
        font-weight: 600;
        color: #495057;
        background: #eef2ff;
        margin-right: .5rem;
        position: relative;
    }

    .np-tabs .nav-link .fa {
        margin-right: .4rem;
    }

    .np-tabs .nav-link.active {
        color: #fff;
        background: #0d6efd;
    }

    .np-tabs .nav-link[data-tabline]::after {
        content: '';
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: -8px;
        height: 3px;
        border-radius: 3px;
        background: transparent;
        transition: background .25s ease, transform .25s ease;
        transform: scaleX(0);
    }

    .np-tabs .nav-link.active[data-tabline]::after {
        background: #0d6efd;
        transform: scaleX(1);
    }

    .input-air-primary:focus {
        box-shadow: 0 0 0 .2rem rgba(13, 110, 253, .15);
    }

    .np-help {
        color: #6c757d;
        font-size: .85rem;
    }

    .sticky-toolbar {
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .modal-dialog-scrollable .modal-body {
        overscroll-behavior: contain;
    }

    .form-label .fa {
        color: #6c757d;
        width: 16px;
        text-align: center;
        margin-right: .35rem;
    }

    /* ===== Altura responsiva real en modales (evita “cortes”) ===== */
    :root {
        --vh: 1vh;
    }

    @supports(height: 1dvh) {
        :root {
            --vh: 1dvh;
        }
    }

    .modal[data-vh-ready="1"] .modal-body {
        max-height: calc((var(--vh, 1vh) * 100) - var(--modal-header-h, 56px) - var(--modal-footer-h, 64px) - 2rem);
        overflow: auto;
    }

    #resultadosProductos.h-results {
        max-height: calc((var(--vh, 1vh) * 100) - 320px);
        overflow: auto;
    }

    @media (max-width: 575.98px) {

        .modal .modal-header,
        .modal .modal-footer {
            padding: .5rem .75rem;
        }

        .modal .modal-title {
            font-size: 1rem;
        }
    }

    /* ===== CSS mínimo para Twitter Typeahead ===== */
    .twitter-typeahead {
        width: 100%;
    }

    .tt-menu {
        width: 100%;
        background: #fff;
        border: 1px solid rgba(0, 0, 0, .1);
        border-radius: .5rem;
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .05);
        margin-top: .25rem;
        padding: .25rem 0;
        z-index: 2000;
    }

    .tt-suggestion {
        padding: .375rem .75rem;
        cursor: pointer;
    }

    .tt-suggestion:hover,
    .tt-suggestion.tt-cursor {
        background: #eef2ff;
    }

    /* Mantener input + botón en la misma línea dentro del input-group */
    .input-group .twitter-typeahead {
        flex: 1 1 auto;
        min-width: 0;
        /* evita saltos por overflow */
        display: block;
        /* más robusto que inline-block en flex */
    }

    .input-group .tt-input,
    .input-group .tt-hint {
        width: 100%;
    }

    /* ===== Estados de validación (UX 2025) ===== */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .12) !important;
    }

    .invalid-hint {
        color: #dc3545;
        font-size: .8rem;
        margin-top: .25rem;
    }
    
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include('partial/topbar.php'); ?>
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>

            <div class="container-fluid">
                <div class="row">

                    <!-- CABECERA DE COMPRA -->
                    <div class="col-md-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fa fa-file-text-o me-2"></i>Registro de Compras</h5>
                            </div>
                            <div class="card-body">
                                <form id="formCompra">
                                    <input type="hidden" name="csrf_token" id="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <div class="row g-3">

                                        <div class="col-md-4 position-relative">
                                            <label class="form-label"><i class="fa fa-user"></i> Proveedor</label>
                                            <input type="text" class="form-control btn-pill" id="proveedor_search" placeholder="Escribe razón social o RUC/DNI" autocomplete="off">
                                            <input type="hidden" name="id_proveedor" id="id_proveedor">
                                            <div id="prov_suggestions" class="list-group shadow-sm"
                                                style="position:absolute; top:100%; left:0; right:0; z-index:1051; display:none; max-height:240px; overflow:auto;">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-map-marker"></i> Sede Compra</label>
                                            <select class="form-select btn-pill" name="id_sede">
                                                <option value="">Seleccione...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-calendar"></i> Fecha Emisión de Documento</label>
                                            <input type="date" class="form-control btn-pill" name="fechaEmision_documento" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-file-text-o"></i> Tipo Documento</label>
                                            <select class="form-select btn-pill" name="tipoDocumento_compra">
                                                <option value="" selected></option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-hashtag"></i> N° Documento</label>
                                            <input type="text" class="form-control btn-pill" name="numeroDocumento_compra" placeholder="F001-000123">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-money"></i> Moneda</label>
                                            <select class="form-select btn-pill" name="tipo_moneda">
                                                <option value="PEN">PEN - (S/) Sol</option>
                                                <option value="USD">US$ - ($) Dólar EEUU</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label"><i class="fa fa-credit-card"></i> Condiciones de Pago</label>
                                            <input type="text" class="form-control btn-pill" name="condicionesPago_compra" placeholder="Contado / 15 días / 30 días...">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label"><i class="fa fa-commenting"></i> Observaciones</label>
                                            <textarea class="form-control btn-pill" name="observaciones" rows="1" placeholder="Notas internas, números de guía, etc."></textarea>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLE DE PRODUCTOS -->
                    <div class="col-md-12 mt-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-secondary text-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fa fa-cubes me-2"></i>Detalle de Productos</h5>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary btn-pill btn-sm" data-bs-toggle="modal" data-bs-target="#modalBuscarProducto">
                                        <i class="fa fa-search"></i> Agregar desde catálogo
                                    </button>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle" id="tablaDetalleCompra">
                                        <thead class="table-light">
                                            <tr class="text-center">
                                                <th style="width:12%">Acciones</th>
                                                <th style="width:40%">Producto</th>
                                                <th style="width:15%">Cantidad</th>
                                                <th style="width:16%">Precio Compra</th>
                                                <th style="width:15%">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detalleCompraBody">
                                            <tr class="text-center">
                                                <td colspan="6" class="empty-state">
                                                    <i class="fa fa-inbox"></i> Sin productos añadidos. Use <strong>Agregar desde catálogo</strong>.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-between align-items-center mt-2" id="paginadorDetalleCompra">
  <div><small id="detalleCompraInfo"></small></div>
  <div class="btn-group">
    <button class="btn btn-outline-secondary btn-sm" id="detallePrev">«</button>
    <button class="btn btn-outline-secondary btn-sm" id="detalleNext">»</button>
  </div>
</div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-4 offset-md-8">
                                        <ul class="list-group kpi-totales">
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Subtotal</span>
                                                <strong id="subtotal_compra">0.00</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>IGV (18%)</span>
                                                <strong id="igv_compra">0.00</strong>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between">
                                                <span>Total</span>
                                                <strong id="total_compra">0.00</strong>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- ... -->
<div class="mt-3 d-flex justify-content-end gap-2">
  <button class="btn btn-primary btn-pill" id="btnGuardarCompra">
    <i class="fa fa-save me-1"></i> Guardar Compra
  </button>
</div>
<!-- ... -->
                            </div>
                        </div>
                    </div>

                </div> <!-- /row -->
            </div> <!-- /container-fluid -->
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>

<!-- MODAL: BUSCAR / AGREGAR DESDE CATÁLOGO -->
<div class="modal fade" id="modalBuscarProducto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-sm">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-cubes"></i> Agregar productos al detalle</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-light sticky-toolbar">
                    <div class="row g-2 align-items-center toolbar-busqueda">
                        <div class="col-md-6">
                            <div class="input-group pill-input-group mt-2">
                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                <input id="buscadorProductos" type="text" class="form-control btn-pill"
                                    placeholder="Buscar por nombre, SKU o código (atajo: /)">
                            </div>
                            <small class="text-muted"><i class="fa fa-barcode"></i> También puedes escanear un código.</small>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <select class="form-select btn-pill" id="filtroCategoria">
                                    <option value="">Categoría</option>
                                </select>
                                <select class="form-select btn-pill" id="filtroMarca">
                                    <option value="">Marca</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2 text-end">
                            <button class="btn btn-success btn-pill w-100" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                                <div class="d-flex flex-column align-items-center">
    <i class="fa fa-plus fa-lg"></i>
    <span>Nuevo Producto</span>
  </div>
                               
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-lg-7 p-3">
                        <div id="resultadosProductos" class="list-group list-group-flush h-results">
                            <div class="text-center text-muted p-4" id="placeholderResultados">
                                <i class="fa fa-search fa-2x mb-2"></i>
                                <div>Escribe para buscar productos</div>
                                <small>Resultados con desplazamiento infinito o paginación.</small>
                            </div>
                        </div>
                        <div class="p-3 text-center d-none" id="wrapCargarMas">
                            <button class="btn btn-outline-secondary btn-pill" id="btnCargarMas">
                                <i class="fa fa-chevron-down"></i> Cargar más
                            </button>
                        </div>
                    </div>

                    <div class="col-lg-5 border-start p-3">
                        <h6 class="mb-3"><i class="fa fa-file-text-o"></i> Detalle rápido</h6>

                        <div id="panelVacio" class="text-muted text-center p-4">
                            <i class="fa fa-info-circle fa-2x mb-2"></i>
                            <div>Selecciona un producto de la lista para ver sus datos.</div>
                        </div>

                        <form id="formFichaRapida" class="d-none">
                            <input type="hidden" id="fr_id_producto_variante">

                            <div class="mb-2">
                                <div class="fw-bold" id="fr_nombre"></div>
                                <small class="text-muted">
                                    SKU: <span id="fr_sku"></span> ·
                                    <span class="tag" id="fr_categoria"></span>
                                    <span class="tag" id="fr_marca"></span>
                                </small>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label"><i class="fa fa-sort-numeric-asc"></i> Cantidad Comprada</label>
                                    <input type="number" min="1" step="0.01" class="form-control btn-pill text-end" id="fr_cantidad" value="1.00">
                                </div>

                                <div class="col-6">
                                    <label class="form-label"><i class="fa fa-money"></i> Precio Compra</label>
                                    <div class="input-group pill-input-group">
                                        <span class="input-group-text"><i class="fa fa-money"></i></span>
                                        <input type="number" step="0.001" class="form-control btn-pill text-end" id="fr_precio_compra">
                                    </div>
                                </div>

                                <div class="col-6">
                                    <label class="form-label"><i class="fa fa-usd"></i> Divisa de Venta</label>
                                    <select id="fr_divisa" class="form-select btn-pill" disabled>
                                        <option value="PEN">PEN - (S/) Sol</option>
                                        <option value="USD">USD - ($) Dólar EEUU</option>
                                    </select>
                                </div>

                                <div class="col-6">
                                    <label class="form-label"><i class="fa fa-line-chart"></i> Utilidad (%)</label>
                                    <div class="input-group pill-input-group">
                                        <span class="input-group-text"><i class="fa fa-line-chart"></i></span>
                                        <input type="number" min="0" step="0.01" class="form-control btn-pill text-end" id="fr_utilidad" placeholder="Ej. 25" disabled>
                                    </div>
                                    <small class="text-muted">Se recalcula el precio de venta.</small>
                                </div>

                                <div class="col-6">
                                    <label class="form-label"><i class="fa fa-tag"></i> Precio Venta</label>
                                    <div class="input-group pill-input-group">
                                        <span class="input-group-text"><i class="fa fa-tag"></i></span>
                                        <input type="number" step="0.01" class="form-control btn-pill text-end" id="fr_precio_venta" placeholder="Ej. 49.90" disabled>
                                    </div>
                                    <small class="text-muted">O ingrésalo y recalculamos la utilidad.</small>
                                </div>
                            </div>

                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="fr_edit_switch">
                                <label class="form-check-label" for="fr_edit_switch">
                                    <i class="fa fa-pencil"></i> Editar utilidad y precio de venta
                                </label>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted small">
                                    <i class="fa fa-lightbulb-o"></i> Enter para agregar rápido.
                                </div>
                                <button type="button" class="btn btn-primary btn-pill" id="fr_agregar">
                                    <i class="fa fa-plus-circle fa-lg"></i> Agregar al detalle
                                </button>
                            </div>
                        </form>

                        <div id="estadoBusqueda" class="mt-3 d-none">
                            <small class="text-muted"><i class="fa fa-spinner fa-spin"></i> Buscando…</small>
                        </div>
                        <div id="sinResultados" class="alert alert-warning d-none mt-3">
                            <i class="fa fa-exclamation-circle"></i> No se encontraron productos.
                            <button class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">Crear nuevo</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    <i class="fa fa-keyboard-o"></i> Atajos: <strong>/</strong> enfoca búsqueda, <strong>↑/↓</strong> navega, <strong>Enter</strong> agrega.
                </small>
                <button class="btn btn-light btn-pill" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: NUEVO PRODUCTO (UI 2025) -->
<div class="modal fade" id="modalNuevoProducto"
    tabindex="-1" aria-labelledby="tituloNuevoProducto" aria-hidden="true"
    data-modal-auto>
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-xxl-95">
        <div class="modal-content border-0 shadow-lg">

            <div class="modal-header np-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa fa-cube"></i>
                    <h5 class="modal-title" id="tituloNuevoProducto">Nuevo producto</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="formNuevoProducto" autocomplete="off" novalidate>
                <div class="border-bottom bg-white position-sticky top-0" style="z-index:5;">
                    <div class="container-fluid py-2">
                        <ul class="nav np-tabs" id="tabsProducto" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-tabline
                                    id="tab-general" data-bs-toggle="pill" data-bs-target="#panel-general"
                                    type="button" role="tab" aria-controls="panel-general" aria-selected="true">
                                    <span class="badge" style="background:#0001; color:#0d6efd; margin-right:.35rem;">1</span>
                                    <i class="fa fa-info-circle"></i> Información general
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-tabline
                                    id="tab-variante" data-bs-toggle="pill" data-bs-target="#panel-variante"
                                    type="button" role="tab" aria-controls="panel-variante" aria-selected="false">
                                    <span class="badge" style="background:#0001; color:#0d6efd; margin-right:.35rem;">2</span>
                                    <i class="fa fa-tags"></i> Variante / comercial
                                </button>
                            </li>

                            <li class="nav-item ms-auto d-none d-md-block">
                                <span class="badge" style="background:#eef2ff; color:#495057; border:1px solid #dfe6ff;">
                                    <i class="fa fa-keyboard-o"></i> Enter guarda · Esc cierra
                                </span>
                            </li>
                        </ul>
                    </div>
                    <div id="nuevoProdAlert" class="alert alert-warning d-none" role="alert"></div>
                </div>

                <!-- ====== RESUMEN SUPERIOR (Descripción completa + SKU) ====== -->
                <section id="npSummaryTop" class="bg-light border-bottom sticky-top" style="z-index:5;">
                    <div class="container-fluid" style="padding:.75rem 1rem;">
                        <div class="row g-2 align-items-center">

                            <div class="col-lg-3">
                                <label class="form-label text-dark"><i class="fa fa-barcode"></i> SKU</label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="SKU_Productos" id="SKU_Productos" placeholder="Genera el SKU" readonly required>

                                <div class="np-help">Se genera con Categoría + Marca; único por variante.</div>
                            </div>

                            <div class="col-lg-9">
                                <label class="form-label text-dark"><i class="fa fa-align-left"></i> Descripción completa</label>
                                <textarea class="form-control btn-pill input-air-primary" name="DescripcionCompleta" rows="1"
                                    placeholder="Ej. Bomba de combustible eléctrica..." readonly required></textarea>
                                <div class="np-help">Incluye modelo/motor/años para búsquedas más precisas.</div>
                            </div>


                        </div>
                    </div>
                </section>


                <div class="modal-body">
                    <div class="tab-content" id="contentProducto">

                        <!-- TAB 1 -->
                        <div class="tab-pane fade show active" id="panel-general" role="tabpanel" aria-labelledby="tab-general">
                            <h6 class="text-primary fw-bold mb-3"><i class="fa fa-info-circle"></i> Información general del producto</h6>

                            <div class="row g-3">


                                <!-- Categoría + crear rápida -->
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-folder-open"></i> Categoría</label>
                                    <div class="input-group">
                                        <input id="inputCategoria" class="typeahead form-control btn-pill input-air-primary"
                                            type="text" placeholder="Buscar o seleccionar categoría" aria-describedby="helpCategoria">
                                        <button type="button" class="btn btn-outline-primary btn-pill" id="btnAgregarCategoria" title="Crear nueva categoría">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                    <div id="helpCategoria" class="np-help">Escribe para sugerencias. Si no existe, créala.</div>
                                    <input type="hidden" name="id_categoriaProductos" id="id_categoriaProductos">
                                </div>

                                <!-- Nueva categoría inline -->
                                <div class="col-12 d-none" id="nuevoCategoriaContainer">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-th-large"></i> Clasificación General</label>
                                            <input id="inputClasificacionGeneral" type="text" class="form-control btn-pill input-air-primary" placeholder="Ej. Motor Interno">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label"><i class="fa fa-tag"></i> Nombre Específico</label>
                                            <input id="inputNombreEspecifico" type="text" class="form-control btn-pill input-air-primary" placeholder="Ej. Bomba de Combustible">
                                        </div>
                                        <div class="col-md-4">
                                            <button id="btnGuardarCategoria" type="button" class="btn btn-success btn-pill w-100">
                                                <i class="fa fa-check"></i> Guardar categoría
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Fitment -->
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-industry"></i> OEM (código fabricante)</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="OEM_productos" placeholder="Ej. OEM-12345">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fa fa-car"></i> Marca Vehículo</label>
                                    <input id="inputMarcaVehiculo"
                                        name="MarcaVehiculo"
                                        class="typeahead form-control btn-pill input-air-primary"
                                        type="text" placeholder="Ej. Toyota">
                                    <input type="hidden" name="id_marcaVehiculo" id="id_marcaVehiculo">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fa fa-car"></i> Modelo Vehículo</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="ModeloVehiculo" placeholder="Ej. Corolla">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label"><i class="fa fa-tachometer"></i> Cilindrada</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="CilindradaVehiculo" placeholder="Ej. 1.8L">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-cogs"></i> Motor</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="MotorVehiculo" placeholder="Ej. 1ZZ-FE">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><i class="fa fa-calendar-o"></i> Año inicial</label>
                                    <input type="text" inputmode="numeric" class="form-control btn-pill input-air-primary" name="AñoInicialVehiculo" placeholder="Ej. 2005">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><i class="fa fa-calendar"></i> Año final</label>
                                    <input type="text" inputmode="numeric" class="form-control btn-pill input-air-primary" name="AñoFinVehiculo" placeholder="Ej. 2008">
                                </div>

                                <!-- Comerciales base -->
                                <div class="col-md-3">
                                    <label class="form-label"><i class="fa fa-money"></i> Divisa</label>
                                    <select class="form-select btn-pill input-air-primary" name="TipoDivisa" required>
                                        <option value="" selected disabled>Seleccione…</option>
                                        <option value="PEN">PEN - (S/) Sol</option>
                                        <option value="USD">USD - ($) Dólar EEUU</option>
                                    </select>
                                    <div class="np-help">Usada para el precio de venta.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><i class="fa fa-tag"></i> Precio unitario (incluye IGV)</label>
                                    <input type="number" step="0.001" class="form-control btn-pill input-air-primary" name="PrecioUnitario_Venta" placeholder="0.000">
                                    <div class="np-help">Formato: 0.000</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label"><i class="fa fa-balance-scale"></i> Unidad de medida</label>
                                    <select class="form-select btn-pill input-air-primary" name="unidad_medida" required>
                                        <option value="" selected disabled>Seleccione…</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-block"><i class="fa fa-shopping-bag"></i> Impuesto a bolsas plásticas</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="chkBolsas">
                                        <label class="form-check-label" for="chkBolsas">Aplicar</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2 -->
                        <div class="tab-pane fade" id="panel-variante" role="tabpanel" aria-labelledby="tab-variante">
                            <h6 class="text-primary fw-bold mb-3"><i class="fa fa-tags"></i> Detalle de variante / comercial</h6>
                            <div class="row g-3">


                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-id-card-o"></i> Código del proveedor</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="CodigoReferencia_proveedor" placeholder="Opcional">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-bookmark"></i> Marca del producto</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="MarcaProductos" placeholder="Ej. Denso / Bosch / ACDelco">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label"><i class="fa fa-globe"></i> Origen</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="OrigenProductos" placeholder="Ej. Japón / Alemania / México">
                                </div>

                                <div class="col-12">
                                    <label class="form-label"><i class="fa fa-file-text-o"></i> Descripción de la variante</label>
                                    <input type="text" class="form-control btn-pill input-air-primary" name="descripcion_variante" placeholder="Ej. Bomba combustible Corolla 2005-2008">
                                </div>

                                <div class="col-md-9">
                                    <label class="form-label"><i class="fa fa-image"></i> Imagen</label>
                                    <input type="file" class="form-control btn-pill input-air-primary" name="ImagenProducto" id="ImagenProducto" accept=".jpg,.jpeg,.png,.webp">
                                    <div class="np-help">JPG/PNG/WEBP · máx. 3 MB</div>
                                </div>
                                <div class="col-md-3 d-grid">
                                    <label class="form-label invisible">Vista previa</label>
                                    <button type="button" class="btn btn-outline-primary btn-sm btn-pill" id="btnVistaPrevia" disabled>
                                        <i class="fa fa-image"></i> Vista previa
                                    </button>
                                </div>

                                <div class="col-12">
                                    <label class="form-label"><i class="fa fa-sticky-note-o"></i> Observaciones</label>
                                    <textarea class="form-control btn-pill input-air-primary" name="Observaciones" rows="2" placeholder="Notas internas, equivalencias, etc."></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer np-footer justify-content-between">
                    <div class="text-muted small">
                        <i class="fa fa-shield"></i> Validamos campos clave antes de guardar.
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-light btn-pill" type="button" data-bs-dismiss="modal">
                            <i class="fa fa-times"></i> Cancelar
                        </button>
                        <button class="btn btn-success btn-pill" type="submit">
                            <i class="fa fa-save"></i> Guardar producto
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/animation/animate-custom.js"></script>
<script src="assets/js/select2/select2.full.min.js"></script>
<script src="assets/js/typeahead/handlebars.js"></script>
<script src="assets/js/typeahead/typeahead.bundle.js"></script>
<script src="assets/js/typeahead/typeahead.custom.js"></script>
<?php include('partial/footer-end.php'); ?>
<script>
    window.__SEDE_ACTUAL_ID = <?= (int)($_SESSION['id_sede'] ?? 0) ?>;
</script>
<script src="js/Compras.js"></script>