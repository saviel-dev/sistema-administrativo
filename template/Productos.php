<?php include('partial/header.php'); ?>

<?php include('partial/loader.php'); ?>

<link rel="stylesheet" type="text/css" href="assets/css/vendors/photoswipe.css">
<style>
    /* Responsive toolbar for product actions */
    .toolbar-productos {
        flex-direction: row;
        flex-wrap: wrap;
        gap: 1rem;
    }

    @media (max-width: 1200px) {
        .toolbar-productos {
            flex-direction: column;
            align-items: flex-start;
        }
    }

    @media (max-width: 768px) {
        .info-block {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .info-block>.d-flex {
            width: 100%;
            justify-content: space-between;
            margin-top: 10px;
        }

        .search-info {
            flex-wrap: wrap;
            gap: 6px;
        }

        .search-info li {
            display: inline-block;
        }

        .search-info-top {
            flex-wrap: wrap;
        }
    }

    @media (max-width: 640px) {
        .toolbar-productos {
            gap: 0.5rem;
        }

        .btn-pill {
            width: 100%;
            text-align: center;
        }

        .search-info,
        .search-info-top {
            flex-direction: column;
            gap: 6px;
        }

    }

    .list-group-item {
        border-left: 3px solid transparent;
    }

    .list-group-item:hover {
        background: #fafbfc;
        border-left-color: #0d6efd22;
    }
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid search-page">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="toolbar-productos d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <button type="button" class="btn btn-success btn-pill" onclick="abrirModalAgregar()">
                                        <i class="fa fa-plus me-1"></i> Agregar producto
                                    </button>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button type="button" class="btn btn-outline-success btn-pill" onclick="exportarExcel()">
                                            <i class="icofont icofont-file-excel me-1"></i> Exportar Excel
                                        </button>
                                        <!-- <button type="button" class="btn btn-outline-danger btn-pill" onclick="exportarPDF()">
                                            <i class="icofont icofont-file-pdf me-1"></i> Exportar PDF
                                        </button> -->
                                    </div>
                                </div>
                                <form class="theme-form">
                                    <div class="input-group m-0 theme-form">
                                        <input id="searchInput" class="form-control-plaintext btn-pill input-air-primary" type="search"
                                            placeholder="Buscar productos...">
                                        <button type="button" class="btn btn-primary btn-pill px-3" title="Buscar productos">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="card-body">
                                <!-- <div class="row mb-3">
    <div class="theme-form col-md-6 offset-md-3">
        <input type="text" class="form-control" id="searchInput" placeholder="Buscar productos...">
    </div>
</div> -->
                                <div class="row mb-3">
                                    <div class="col-12 d-flex justify-content-center mt-4">
                                        <div id="pagination"></div>
                                    </div>
                                </div>

                                <div id="resultContainer"
                                    class="row g-3 px-4"
                                    data-sede-id="<?= isset($_SESSION['id_sede']) ? (int)$_SESSION['id_sede'] : 0 ?>">
                                </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>

<!-- MODAL DETALLE / EDITAR PRODUCTO (UX 2025) -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-labelledby="modalVerLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg">

            <!-- Header sticky -->
            <div class="modal-header border-0 pb-0 position-sticky top-0 bg-body z-1">
                <div>
                    <h5 class="modal-title d-flex align-items-center gap-2" id="modalVerLabel">
                        <i class="fa fa-cube text-primary"></i>
                        <span id="detTitulo">Detalle del Producto</span>
                    </h5>
                    <small class="text-muted" id="detSubtitulo">SKU — Categoría — Marca</small>
                </div>
            </div>

            <div class="modal-body pt-2">

                <!-- Toolbar acciones -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div class="d-flex flex-wrap gap-2" id="detBadges"></div>

                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-pill" id="btnToggleEdit">
                            <i class="fa fa-pencil me-1"></i> Editar
                        </button>
                        <button class="btn btn-success btn-pill d-none" id="btnAplicarCambios">
                            <span class="me-1"><i class="fa fa-save"></i></span> Aplicar cambios
                        </button>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Imagen -->
                    <div class="col-12 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="rounded-3 border bg-light-subtle p-2 d-flex align-items-center justify-content-center mx-auto w-100">
                                    <!-- Mantener proporción aproximada usando ratio 1x1 (más flexible) -->
                                    <div class="ratio ratio-1x1 w-100 rounded-3 bg-body-secondary overflow-hidden d-flex align-items-center justify-content-center">
                                        <img
                                            id="detImagen"
                                            alt="Imagen Producto"
                                            class="img-fluid w-100 h-100 object-fit-contain"
                                            src="data:image/svg+xml;utf8,<?php
                                                                            echo rawurlencode('<svg xmlns=&quot;http://www.w3.org/2000/svg&quot; width=&quot;386&quot; height=&quot;400&quot; viewBox=&quot;0 0 386 400&quot;><rect width=&quot;386&quot; height=&quot;400&quot; fill=&quot;#f1f3f5&quot;/><g fill=&quot;#adb5bd&quot; font-family=&quot;Arial,Helvetica,sans-serif&quot; font-size=&quot;14&quot;><text x=&quot;50%&quot; y=&quot;50%&quot; dominant-baseline=&quot;middle&quot; text-anchor=&quot;middle&quot;>Sin imagen</text></g></svg>');
                                                                            ?>" />
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-2 text-center text-truncate">Click en la imagen para ver con zoom</small>
                                <small class="text-muted d-block mt-2 text-center text-truncate">Vista 386×400 px (ajustada)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="col-12 col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">

                                <!-- Precio / Stock resumen -->
                                <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                                    <div>
                                        <div class="text-muted small">Precio vigente</div>
                                        <div class="fs-5 fw-semibold" id="detPrecio">—</div>
                                    </div>
                                    <div class="vr d-none d-sm-block"></div>
                                    <div>
                                        <div class="text-muted small">Stock en sede</div>
                                        <div class="fs-5 fw-semibold text-center" id="detStock">0</div>
                                    </div>
                                </div>

                                <!-- Tabs modernas -->
                                <ul class="nav nav-pills mb-3 flex-wrap gap-2" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="tab-general" data-bs-toggle="pill" data-bs-target="#pane-general" type="button" role="tab">
                                            <i class="fa fa-info-circle me-1"></i> General
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-variante" data-bs-toggle="pill" data-bs-target="#pane-variante" type="button" role="tab">
                                            <i class="fa fa-sliders me-1"></i> Variante
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="tab-precio" data-bs-toggle="pill" data-bs-target="#pane-precio" type="button" role="tab">
                                            <i class="fa fa-usd me-1"></i> Precio
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <!-- PANE GENERAL -->
                                    <div class="tab-pane fade show active" id="pane-general" role="tabpanel" aria-labelledby="tab-general">
                                        <form class="row g-3" id="formDetGeneral" data-mode="view">
                                            <input type="hidden" id="detIdBase">
                                            <div class="col-12">
                                                <label class="form-label"><i class="fa fa-file-text-o me-1"></i> Descripción completa</label>
                                                <textarea class="form-control form-control-sm input-air-primary btn-pill" id="detDescripcionCompleta" rows="2" disabled></textarea>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="fa fa-industry me-1"></i> OEM</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detOEM" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="fa fa-folder-open me-1"></i> Categoría</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detCategoria" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-car me-1"></i> Marca vehículo</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detMarcaVehiculo" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-car me-1"></i> Modelo</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detModeloVehiculo" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-tachometer me-1"></i> Cilindrada</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detCilindrada" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="fa fa-cogs me-1"></i> Motor</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detMotor" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><i class="fa fa-calendar me-1"></i> Año inicial</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detAnioIni" disabled>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label"><i class="fa fa-calendar-o me-1"></i> Año final</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detAnioFin" disabled>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- PANE VARIANTE -->
                                    <div class="tab-pane fade" id="pane-variante" role="tabpanel" aria-labelledby="tab-variante">
                                        <form class="row g-3" id="formDetVariante" data-mode="view">
                                            <input type="hidden" id="detIdVariante">
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-barcode me-1"></i> SKU</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detSKU" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-truck me-1"></i> Código Proveedor</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detCodProv" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label"><i class="fa fa-archive me-1"></i> Unidad Medida</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detUM" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="fa fa-industry me-1"></i> Marca producto</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detMarcaProd" disabled>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label"><i class="fa fa-globe me-1"></i> Origen</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detOrigen" disabled>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label"><i class="fa fa-file-text-o me-1"></i> Descripción variante</label>
                                                <input type="text" class="form-control form-control-sm input-air-primary btn-pill" id="detDescVar" disabled>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label"><i class="fa fa-sticky-note me-1"></i> Observaciones</label>
                                                <textarea class="form-control form-control-sm input-air-primary btn-pill" id="detObs" rows="2" disabled></textarea>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check form-switch mt-2">
                                                    <input class="form-check-input" type="checkbox" id="detICBPER" disabled>
                                                    <label class="form-check-label" for="detICBPER">ICBPER</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- PANE PRECIO -->
                                    <div class="tab-pane fade" id="pane-precio" role="tabpanel" aria-labelledby="tab-precio">
                                        <form class="row g-3" id="formDetPrecio" data-mode="view">
                                            <div class="col-md-4">
                                                <label class="form-label">Divisa</label>
                                                <select class="form-select form-select-sm input-air-primary btn-pill" id="detDivisa" disabled>
  <option value="" disabled>Seleccione…</option>
</select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Precio venta</label>
                                                <input type="number" step="0.001" class="form-control form-control-sm input-air-primary btn-pill" id="detPrecioVenta" disabled>
                                                <small id="detPrecioVentaHint" class="text-danger small d-none">
  Cambiar el precio de venta puede alterar el cálculo de utilidad.
</small>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Utilidad %</label>
                                                <input type="number" step="0.01" class="form-control form-control-sm input-air-primary btn-pill" id="detUtilidad" disabled>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Tipo IGV</label>
                                                <input type="number" class="form-control form-control-sm input-air-primary btn-pill" id="detTipoIGV" disabled placeholder="10 por defecto">
                                            </div>
                                        </form>
                                        <small class="text-muted">La actualización de precio crea una nueva vigencia en la sede actual.</small>
                                    </div>
                                </div>

                                <!-- Otras sedes -->
                                <div class="mt-3">
                                    <div class="text-primary small mb-1">
                                        <i class="fa fa-building me-1"></i>Stock en otras sedes:
                                    </div>
                                    <div id="detOtrasSedes" class="d-flex flex-wrap gap-1"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div> <!-- row -->

            </div>

            <!-- Footer sticky -->
            <div class="modal-footer border-0 pt-0 position-sticky bottom-0 bg-body z-1">
                <div class="d-flex w-100 justify-content-between align-items-center flex-wrap gap-2">
                    <div class="text-muted small">Última actualización de precio: <span id="detPrecioDesde">—</span></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-outline-secondary btn-pill" data-bs-dismiss="modal">Cerrar</button>
                        <button class="btn btn-success btn-pill d-none" id="btnGuardarCambiosFooter">
                            <i class="fa fa-save me-1"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalAgregarProducto" tabindex="-1" aria-labelledby="modalAgregarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header formal y limpio -->
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title d-flex align-items-center gap-2" id="modalAgregarProductoLabel">
                        <i class="fa fa-cubes text-primary"></i>
                        Agregar nuevo producto
                    </h5>
                    <small class="text-muted">Completa la información general y la variante.</small>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <form id="formAgregarProducto" method="post" enctype="multipart/form-data" autocomplete="off">
                    <!-- Aviso -->
                    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-start gap-3 small rounded-3 mb-4" role="alert">
                        <i class="fa fa-exclamation-triangle fa-lg mt-1 text-dark"></i>
                        <div>
                            <p class="mb-1 text-dark">
                                Al registrar un producto mediante este formulario, <strong>no se generará automáticamente un ingreso de compra ni stock</strong>.
                            </p>
                            <p class="mb-0 text-dark">Para un registro completo, se recomienda realizar el ingreso a través del módulo de compras.</p>
                        </div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>

                    <!-- SECCIÓN 1: INF. GENERAL -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa fa-info-circle text-primary"></i>
                        <h6 class="text-primary fw-bold mb-0">Información general del producto</h6>
                    </div>

                    <div class="border rounded-3 p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fa fa-file-text-o text-muted me-1"></i> Descripción completa
                                </label>
                                <textarea class="form-control btn-pill input-air-primary" name="DescripcionCompleta" rows="2" readonly></textarea>
                            </div>

                            <div class="col-md-6 col-lg-5">
                                <label class="form-label">
                                    <i class="fa fa-folder-open text-muted me-1"></i> Categoría
                                </label>
                                <input id="inputCategoria" class="typeahead form-control btn-pill input-air-primary" type="text" placeholder="Seleccione una Categoría">
                                <input type="hidden" name="id_categoriaProductos" id="id_categoriaProductos">
                            </div>

                            <div class="col-md-2 col-lg-1 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-pill w-100" id="btnAgregarCategoria" title="Agregar categoría">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>

                            <!-- Nuevo registro de categoría -->
                            <div class="col-12 d-none" id="nuevoCategoriaContainer">
                                <div class="row g-2">
                                    <div class="col-md-6 col-lg-5">
                                        <label class="form-label">
                                            <i class="fa fa-tags text-muted me-1"></i> Clasificación General
                                        </label>
                                        <input id="inputClasificacionGeneral" type="text" class="form-control btn-pill input-air-primary" placeholder="Ej. MOTOR, SUSPENSIÓN">
                                    </div>
                                    <div class="col-md-6 col-lg-5">
                                        <label class="form-label">
                                            <i class="fa fa-tag text-muted me-1"></i> Nombre Específico
                                        </label>
                                        <input id="inputNombreEspecifico" type="text" class="form-control btn-pill input-air-primary" placeholder="Ej. FILTRO DE ACEITE">
                                    </div>
                                    <div class="col-lg-2 d-flex align-items-end">
                                        <button id="btnGuardarCategoria" type="button" class="btn btn-success btn-pill w-100" title="Guardar categoría">
                                            <i class="fa fa-check me-1"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-industry text-muted me-1"></i> OEM Código Fabricante de equipo original
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="OEM_productos" placeholder="Ej. 17220-R1A-A01">
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <i class="fa fa-car text-muted me-1"></i> Marca Vehículo
                                </label>
                                <input id="inputMarcaVehiculo" name="MarcaVehiculo" class="typeahead form-control btn-pill input-air-primary" type="text" placeholder="Seleccione la Marca Vehículo">
                                <input type="hidden" name="id_categoriaProductos" id="id_categoriaProductos">
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <i class="fa fa-car text-muted me-1"></i> Modelo Vehículo
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="ModeloVehiculo" placeholder="Ej. CIVIC">
                            </div>

                            <div class="col-md-6 col-lg-4">
                                <label class="form-label">
                                    <i class="fa fa-tachometer text-muted me-1"></i> Cilindrada
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="CilindradaVehiculo" placeholder="Ej. 1.8">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-cogs text-muted me-1"></i> Motor
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="MotorVehiculo" placeholder="Ej. R18A">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa fa-calendar text-muted me-1"></i> Año Inicial
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="AñoInicialVehiculo" placeholder="Ej. 2008">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa fa-calendar-o text-muted me-1"></i> Año Final
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="AñoFinVehiculo" placeholder="Ej. 2012">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa fa-money text-muted me-1"></i> Divisa
                                </label>
                                <select class="form-select btn-pill input-air-primary" name="TipoDivisa" required>
                                    <option disabled selected>Cargando...</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa fa-usd text-muted me-1"></i> Precio Unitario (Incluido IGV)
                                </label>
                                <input type="number" step="0.001" class="form-control btn-pill input-air-primary" name="PrecioUnitario" placeholder="0.00">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">
                                    <i class="fa fa-archive text-muted me-1"></i> Unidad medida
                                </label>
                                <select class="form-select btn-pill input-air-primary" name="unidad_medida" required>
                                    <option disabled selected>Cargando...</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">
                                    <i class="fa fa-leaf text-muted me-1"></i> Impuesto a las bolsas plásticas
                                </label>
                                <div class="text-center icon-state switch-outline">
                                    <label class="switch">
                                        <input type="checkbox" name="icbper" id="icbper">
                                        <span class="switch-state"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: VARIANTE -->
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa fa-sliders text-primary"></i>
                        <h6 class="text-primary fw-bold mb-0">Detalle de variante</h6>
                    </div>

                    <div class="border rounded-3 p-3">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-barcode text-muted me-1"></i> SKU
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="SKU_Productos" required readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-truck text-muted me-1"></i> Código proveedor
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="CodigoReferencia_proveedor">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-industry text-muted me-1"></i> Marca producto
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="MarcaProductos">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fa fa-globe text-muted me-1"></i> Origen
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="OrigenProductos">
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fa fa-file-text-o text-muted me-1"></i> Descripción variante
                                </label>
                                <input type="text" class="form-control btn-pill input-air-primary" name="descripcion_variante" placeholder="Detalles distintivos de esta variante">
                            </div>

                            <div class="col-md-11">
                                <label class="form-label">
                                    <i class="fa fa-image text-muted me-1"></i> Imagen (URL)
                                </label>
                                <input type="file" class="form-control btn-pill input-air-primary" name="ImagenProducto">
                                <small class="text-muted">* Solo JPG, JPEG, PNG o WEBP. Máx: <strong>3 MB</strong>.</small>
                                <!-- hidden para guardar el nombre devuelto por la subida AJAX -->
                                <input type="hidden" name="ImagenProductoNombre" id="ImagenProductoNombre">

                                <!-- barra de progreso -->
                                <div id="wrapUploadProgress" class="mt-2 d-none">
                                    <div class="progress" style="height: 10px;">
                                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                            role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">0%</div>
                                    </div>
                                    <small id="uploadStatus" class="text-muted d-block mt-1"></small>
                                </div>
                            </div>

                            <div class="col-md-1 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-pill w-100" id="btnVistaPrevia" title="Vista Previa" disabled>
                                    <i class="fa fa-eye"></i>
                                </button>
                            </div>

                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fa fa-sticky-note text-muted me-1"></i> Observaciones
                                </label>
                                <textarea class="form-control btn-pill input-air-primary" name="Observaciones" rows="2" placeholder="Notas internas o consideraciones"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-primary btn-pill">
                            <i class="fa fa-save me-1"></i> Guardar producto
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-pill" data-bs-dismiss="modal">
                            <i class="fa fa-times me-1"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalVistaPreviaImagen" tabindex="-1" aria-labelledby="vistaPreviaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="vistaPreviaLabel">Vista Previa de Imagen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body d-flex justify-content-center align-items-center" style="background-color: #f8f9fa;">
                <div style="width: 386px; height: 400px; background-color: #e0e0e0; display: flex; justify-content: center; align-items: center; overflow: hidden;">
                    <img id="previewImagenProducto" src="" alt="Vista Previa" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PhotoSwipe root (una sola vez en todo el sitio) -->
<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="pswp__bg"></div>
  <div class="pswp__scroll-wrap">
    <div class="pswp__container">
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
      <div class="pswp__item"></div>
    </div>
    <div class="pswp__ui pswp__ui--hidden">
      <div class="pswp__top-bar">
        <div class="pswp__counter"></div>
        <button class="pswp__button pswp__button--close" title="Cerrar (Esc)"></button>
        <button class="pswp__button pswp__button--share" title="Compartir"></button>
        <button class="pswp__button pswp__button--fs" title="Pantalla completa"></button>
        <button class="pswp__button pswp__button--zoom" title="Zoom +/-"></button>
        <div class="pswp__preloader">
          <div class="pswp__preloader__icn">
            <div class="pswp__preloader__cut">
              <div class="pswp__preloader__donut"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
        <div class="pswp__share-tooltip"></div>
      </div>
      <button class="pswp__button pswp__button--arrow--left" title="Anterior"></button>
      <button class="pswp__button pswp__button--arrow--right" title="Siguiente"></button>
      <div class="pswp__caption"><div class="pswp__caption__center"></div></div>
    </div>
  </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/scrollbar/custom.js"></script>
<script src="assets/js/photoswipe/photoswipe.min.js"></script>
<script src="assets/js/photoswipe/photoswipe-ui-default.min.js"></script>
<script src="assets/js/photoswipe/photoswipe.js"></script>
<script src="assets/js/typeahead/handlebars.js"></script>
<script src="assets/js/typeahead/typeahead.bundle.js"></script>
<script src="assets/js/typeahead/typeahead.custom.js"></script>
<script src="assets/js/tooltip-init.js"></script>

<script src="js/Productos.js"></script>
<?php include('partial/footer-end.php'); ?>