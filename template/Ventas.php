<?php include('partial/header.php'); ?>
<?php include('partial/loader.php'); ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
    :root {
        --ui-bg: #0e1014;
        --ui-card: #ffffff;
        --ui-soft: #f6f7fb;
        --ui-primary: #3b82f6;
        --ui-success: #22c55e;
        --ui-danger: #ef4444;
        --ui-warning: #f59e0b;
        --ui-muted: #6b7280;
        --radius-2: 12px;
        --radius-pill: 50rem;
    }

    .btn-pill {
        border-radius: var(--radius-pill) !important;
    }

    body,
    html {
        height: 100%;
        background: var(--ui-soft);
    }

    .page-body .card {
        border: 0;
        border-radius: var(--radius-2);
        box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
    }

    .pos-container {
        height: calc(100vh - 220px);
    }

    /* ajusta si tu header es más alto */
    .scroll-y {
        overflow-y: auto;
    }

    .product-card {
        border-radius: 14px;
        border: 1px solid #eef2f7;
        transition: .15s;
    }

    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 34px rgba(2, 6, 23, .08);
    }

    .category-scroller {
        overflow-x: auto;
        white-space: nowrap;
    }

    .category-scroller .btn {
        margin-right: .5rem;
    }

    .kbd {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        border: 1px solid #e1e1e1;
        border-bottom-width: 3px;
        padding: .1rem .35rem;
        border-radius: .4rem;
        background: #fafafa;
    }

    .price {
        font-weight: 700;
    }

    .sticky-totals {
        position: sticky;
        bottom: 0;
        background: #fff;
        z-index: 2;
        border-top: 1px solid #eee;
        border-bottom-left-radius: var(--radius-2);
        border-bottom-right-radius: var(--radius-2);
    }


    .bg-soft {
        background: #f8fafc;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .section-title {
        font-weight: 700;
        font-size: .95rem;
        color: #111827;
    }

    .subtle {
        color: var(--ui-muted);
    }

    .badge-chip {
        background: #eef2ff;
        color: #3730a3;
        border-radius: 20px;
        padding: .35rem .6rem;
        font-weight: 600;
    }

    .grid-gap {
        gap: .75rem;
    }

    

    .mini-hint {
        font-size: .75rem;
        color: var(--ui-muted);
    }

    .sunat-card {
        border: 1px dashed #e5e7eb;
        background: #ffffff;
    }

    .totals-table td {
        padding: .25rem 0;
    }

    .totals-table .label {
        color: var(--ui-muted);
    }

    .totals-table .value {
        font-weight: 600;
    }

    .totals-table .grand {
        font-size: 1.15rem;
        font-weight: 800;
    }

    /* Offcanvas: solo en móvil/tablet. En ≥992px (lg) se oculta por completo */
    @media (min-width: 992px) {
        #offcanvasCarrito {
            display: none !important;
            visibility: hidden !important;
        }
    }

    /* Asegura ancho adecuado y que no “empuje” el viewport en tablets */
    #offcanvasCarrito.offcanvas-end {
        width: min(420px, 100vw);
    }

    /* iOS safe area fix para que no se “desplace” de la derecha */
    @supports (padding: max(0px)) {
        #offcanvasCarrito .offcanvas-body {
            padding-right: max(1rem, env(safe-area-inset-right));
            padding-left: max(1rem, env(safe-area-inset-left));
        }
    }


    /* ===== Catálogo en filas (compacto y responsive) ===== */
    .catalog-list {
        display: grid;
        grid-template-rows: auto 1fr;
        gap: .5rem;
    }

    

    .product-row {
        display: grid;
        grid-template-columns: 1.2fr .7fr .5fr .5fr .5fr .4fr;
        gap: .75rem;
        align-items: center;
        padding: .6rem .75rem;
        border: 1px solid #eef2f7;
        border-radius: 12px;
        background: #fff;
        transition: background .15s, box-shadow .15s, border-color .15s;
    }

    .product-row:hover {
        background: #f9fafb;
        border-color: #e5e7eb;
        box-shadow: 0 4px 14px rgba(2, 6, 23, .05);
    }

    .product-row .name {
        display: flex;
        flex-direction: column;
    }

    .product-row .name .title {
        font-weight: 600;
        line-height: 1.2;
    }

    .product-row .name .meta {
        font-size: .78rem;
        color: var(--ui-muted);
    }

    .badge-price {
        font-weight: 700;
    }

    .product-row .btnAdd {
        justify-self: end;
    }

    /* Responsive breakpoints: colapsa a 2-3 columnas en pantallas chicas */
    @media (max-width: 1199.98px) {

       

        .product-row {
            grid-template-columns: 1fr auto;
            gap: .5rem .75rem;
        }

        .product-row .sku,
        .product-row .stock,
        .product-row .um {
            display: none;
            /* Quedarte solo con nombre + precio + botón en tablets */
        }

        .product-row .price {
            justify-self: start;
        }
    }

    @media (max-width: 576px) {

        /* móvil chico */
        .product-row {
            grid-template-columns: 1fr auto;
        }

        .product-row .price {
            font-size: .95rem;
        }
    }

    /* Mantiene tu look & feel de “chip” con btn-pill */
    .product-row .btnAdd {
        border-radius: var(--radius-pill);
    }

    /* Usamos la misma clase product-card para no romper JS */
    .product-card.product-row {
        border: 0;
    }

    /* ===== Cards por fila (sin tabla) ===== */
.card-row {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .7rem .85rem;
  border: 1px solid #eef2f7;
  border-radius: 12px;
  background: #fff;
  transition: background .15s, box-shadow .15s, border-color .15s;
}
.card-row:hover {
  background: #f9fafb;
  border-color: #e5e7eb;
  box-shadow: 0 4px 14px rgba(2,6,23,.05);
}
.card-row .col-left {
  flex: 1 1 auto; /* crece */
  min-width: 180px;
}
.card-row .title {
  font-weight: 600;
  line-height: 1.2;
}
.card-row .meta {
  font-size: .78rem;
  color: var(--ui-muted);
}
.card-row .chips {
  display: flex;
  flex-wrap: wrap;
  gap: .4rem;
}
.card-row .chip {
  background: #f8fafc;
  color: #374151;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  padding: .15rem .55rem;
  font-size: .78rem;
}
.card-row .price {
  font-weight: 700;
}
.card-row .col-right {
  display: flex;
  align-items: center;
  gap: .5rem;
}


/* === Fix de desbordes y truncados (responsive) === */

/* El contenedor de la lista NO debe forzar ancho extra */
.catalog-list,
#gridProductos {
  min-width: 0;
}

/* #gridProductos como columna con gap (sin generar overflow) */
#gridProductos {
  display: flex;
  flex-direction: column;
  gap: .75rem;
}

/* Card en fila debe respetar el ancho del grid */
.card-row {
  width: 100%;
  overflow: hidden; /* evita que los hijos empujen */
}

/* En flex, para que el texto pueda encogerse y truncar */
.card-row .col-left {
  flex: 1 1 auto;
  min-width: 0;          /* 🔑 permite truncado */
}

/* Título en una sola línea con “…” */
.card-row .title {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Descripción en 2 líneas máximo (con “…”), sin romper diseño */
.card-row .meta {
  display: -webkit-box;
  -webkit-line-clamp: 2; /* máximo 2 líneas */
  -webkit-box-orient: vertical;
  overflow: hidden;
  max-height: 2.8em;     /* aprox 2 líneas */
}

/* Chips: que no estiren el contenedor */
.card-row .chips {
  gap: .4rem;
  overflow: hidden;
  flex-wrap: wrap;
}
.card-row .chip {
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Columna derecha compacta y sin desbordes */
.card-row .col-right {
  flex: 0 0 auto;
  min-width: 0;
}
.card-row .badge-price {
  white-space: nowrap;
}

/* Tooltip: no permitir que rompa el layout */
.tooltip .tooltip-inner {
  max-width: 260px; /* ajusta si deseas */
  text-align: left;
  white-space: normal;
}

/* Botón “Mostrar más” dentro de su grid sin desbordar */
.catalog-list .d-grid {
  min-width: 0;
}
#btnMas {
  width: 100%;
  max-width: 100%;
}

/* Skeletons alineados al nuevo layout */
.product-card.card-row .placeholder {
  display: inline-block;
}

/* En pantallas medianas/chicas la derecha baja: ya lo haces, añadimos que todo pueda truncar */
@media (max-width: 768px) {
  .card-row {
    flex-direction: column;
    align-items: stretch;
  }
  .card-row .col-right {
    justify-content: space-between;
  }
}


/* Badge precio no se rompe */
.badge-price { white-space: nowrap; font-weight: 700; }


#listaCarrito .list-group-item,
#listaCarritoMobile .list-group-item {
  background: transparent; border: 0; padding: .35rem .5rem;
}
#listaCarrito .cart-item-row,
#listaCarritoMobile .cart-item-row { margin-bottom: .25rem; }
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <?php include('partial/topbar.php'); ?>
    <div class="page-body-wrapper">
        <?php include('partial/sidebar.php'); ?>
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>

            <div class="container-fluid search-page">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge-chip"><i class="bi bi-bag-check-fill me-1"></i> POS Ventas</span>
                                        <span class="badge bg-success rounded-pill">Caja Aperturada</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-light btn-pill" data-bs-toggle="modal" data-bs-target="#modalAtajos">
                                            <i class="bi bi-keyboard"></i> Atajos
                                        </button>
                                        <button class="btn btn-outline-secondary btn-pill d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#offcanvasCarrito">
                                            <i class="bi bi-cart3"></i>
                                            <span class="badge bg-danger rounded-pill" id="badgeCartCount">0</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <!-- IZQUIERDA: Catálogo -->
                                    <div class="col-lg-7 border-end">
                                        <div class="p-3 border rounded-3 mb-3 bg-white">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-md-11">
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                                                        <input type="text" class="form-control" id="inputBuscar" placeholder="Buscar producto (nombre, SKU)">
                                                    </div>
                                                    <div class="mini-hint mt-1">Tip: usa lector de barras en el campo “Código” o escribe y presiona Enter.</div>
                                                    <input type="hidden" id="id_sede" value="<?= (int)($_SESSION['id_sede'] ?? 0) ?>">
                                                </div>
                                                <div class="col-md-1 m-b-20">
                                                    <button class="btn btn-outline-secondary btn-sm btn-pill" id="btnLimpiar">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Catálogo -->
                                        <!-- Catálogo en filas -->
                                        <div class="p-3 scroll-y bg-white rounded-3" style="height: calc(100vh - 430px);">
                                            <div class="catalog-list">
                                               

                                                <div id="gridProductos" class="grid-gap"></div>
                                                <div class="d-grid mt-3">
                                                    <button class="btn btn-outline-primary btn-pill" id="btnMas" style="display:none;">
                                                        <i class="bi bi-chevron-down"></i> Mostrar más
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- DERECHA: Carrito + Datos SUNAT -->
                                    <div class="col-lg-5 d-flex flex-column">
                                        
                                        <!-- Carrito -->
                                        <div class="p-0 bg-white rounded-3 d-none d-lg-flex flex-column">
                                            <div class="p-3 border-bottom">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <i class="bi bi-cart3 fs-5"></i>
                                                        <span class="fw-semibold">Carrito</span>
                                                        <span class="badge bg-secondary rounded-pill" id="cartItemsCount">0</span>
                                                    </div>
                                                    <div class="d-flex gap-2">
                                                        <button class="btn btn-outline-danger btn-sm btn-pill" id="btnVaciar"><i class="bi bi-trash"></i> Vaciar</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="scroll-y" style="max-height: 36vh;">
                                                <ul class="list-group list-group-flush" id="listaCarrito"></ul>
                                            </div>

                                            <div class="p-3">
                                                <!-- Totales SUNAT -->
                                                <table class="w-100 totals-table">
                                                    <tr>
                                                        <td class="label">Op. Gravada</td>
                                                        <td class="value text-end" id="lblOpGravada">S/ 0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Op. Exonerada</td>
                                                        <td class="value text-end" id="lblOpExonerada">S/ 0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">Op. Inafecta</td>
                                                        <td class="value text-end" id="lblOpInafecta">S/ 0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="label">IGV (18%)</td>
                                                        <td class="value text-end" id="lblImpuesto">S/ 0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="grand">TOTAL</td>
                                                        <td class="grand text-end" id="lblTotal">S/ 0.00</td>
                                                    </tr>
                                                </table>
                                                <div class="mini-hint mt-1" id="lblMontoLetras">SON: CERO Y 00/100 SOLES</div>
                                                <div class="d-grid mt-2">
                                                    <button class="btn btn-success btn-lg btn-pill" id="btnCobrar" data-bs-toggle="modal" data-bs-target="#modalPago">
                                                        <i class="bi bi-cash-coin me-1"></i> Cobrar <span class="small text-white-50">(F2)</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>



                                    </div><!-- /col-lg-4 -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include('partial/footer.php'); ?>
        </div>
    </div>
</div>

<!-- Modal Pago -->
<div class="modal fade" id="modalPago" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-md"> <!-- más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-credit-card-2-front me-2"></i>Finalizar Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

            <div class="accordion mb-3" id="accCpe">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headCpe">
      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#colCpe" aria-expanded="false" aria-controls="colCpe">
        Datos para Comprobante Electrónico (SUNAT)
      </button>
    </h2>
    <div id="colCpe" class="accordion-collapse collapse" aria-labelledby="headCpe" data-bs-parent="#accCpe">
      <div class="accordion-body">
        <div class="row g-2">
          <div class="col-6">
            <label for="cpeTipo" class="form-label subtle">Tipo CPE</label>
            <select id="cpeTipo" name="cpeTipo" class="form-select btn-air-light" autocomplete="off"></select>
          </div>
          <div class="col-3">
            <label for="cpeSerie" class="form-label subtle">Serie</label>
            <input id="cpeSerie" name="cpeSerie" class="form-control btn-air-light" autocomplete="off" readonly>
          </div>
          <div class="col-3">
            <label for="cpeCorrelativo" class="form-label subtle"># Correlativo</label>
            <input id="cpeCorrelativo" name="cpeCorrelativo" class="form-control btn-air-light" inputmode="numeric" autocomplete="off" readonly>
          </div>

          <div class="col-6">
            <label for="cpeMoneda" class="form-label subtle">Moneda</label>
            <select id="cpeMoneda" name="cpeMoneda" class="form-select btn-air-light" autocomplete="currency"></select>
          </div>
          <div class="col-6">
            <label for="cpeFecha" class="form-label subtle">Fecha de Emisión</label>
            <input id="cpeFecha" name="cpeFecha" type="datetime-local" class="form-control" autocomplete="off">
          </div>

          <div class="col-6" id="wrapTipoCambio" style="display:none;">
            <label for="cpeTipoCambio" class="form-label subtle">Tipo de cambio (PEN → USD)</label>
            <div class="input-group">
              <span class="input-group-text">TC</span>
              <input id="cpeTipoCambio" name="cpeTipoCambio" type="number" class="form-control" min="0.0001" step="0.0001" value="3.8000" autocomplete="off">
            </div>
            <div class="mini-hint mt-1">Se usa para convertir montos a USD.</div>
          </div>

          <div class="col-lg-3 col-md-12">
            <label class="form-label subtle">Código cliente</label>
            <div class="input-group">
              <input id="cliCodigo" class="form-control" placeholder="ID cliente" inputmode="numeric" autocomplete="off">
              <button class="btn btn-outline-primary input-group-text" type="button" id="btnBuscarCliente"><i class="bi bi-search"></i></button>
            </div>
            <!-- <div class="mini-hint mt-1">Permite traer al cliente por su ID interno.</div> -->
          </div>

          <div class="col-lg-3 col-md-6">
            <label for="cliTipoDoc" class="form-label subtle">Cliente - Tipo Doc</label>
            <select id="cliTipoDoc" name="cliTipoDoc" class="form-select" autocomplete="off">
              <option value="1" selected>DNI (1)</option>
              <option value="6">RUC (6)</option>
              <option value="0">Otros</option>
            </select>
          </div>
          <div class="col-lg-6 col-md-6">
            <label for="cliNumDoc" class="form-label subtle">Cliente - N° Documento</label>
            <div class="input-group">
              <input id="cliNumDoc" name="cliNumDoc" class="form-control" placeholder="DNI/RUC" maxlength="12" autocomplete="on" inputmode="numeric">
              <button class="btn btn-outline-secondary input-group-text" type="button" id="btnConsultarDoc"><i class="bi bi-cloud-download"></i></button>
            </div>
          </div>

          <div class="col-12">
            <label for="cliRazon" class="form-label subtle">Razón social / Nombres</label>
            <div class="input-group">
              <input id="cliRazon" name="cliRazon" class="form-control" placeholder="Nombre del cliente" autocomplete="name">
            </div>
          </div>

          <div class="col-12">
            <label for="cliDireccion" class="form-label subtle">Dirección</label>
            <input id="cliDireccion" name="cliDireccion" class="form-control" placeholder="Calle, N°, Distrito, Provincia, Departamento" autocomplete="address-line1">
          </div>

          <div class="col-12">
            <label class="form-label subtle">Forma de pago</label>
            <div class="d-flex gap-2 align-items-center">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="fpago" id="fpContado" value="Contado" checked>
                <label class="form-check-label" for="fpContado">Contado</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="fpago" id="fpCredito" value="Crédito">
                <label class="form-check-label" for="fpCredito">Crédito</label>
              </div>
              <input id="fpPlazo" name="fpPlazo" class="form-control form-control-sm ms-auto" placeholder="Días crédito" style="max-width:130px; display:none;" inputmode="numeric" autocomplete="off">
            </div>
            <div class="mini-hint mt-1">Si es crédito, especifica el plazo en días.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total a pagar</span>
                    <span class="fs-4 fw-bold" id="pagoTotal">S/ 0.00</span>
                </div>

                <!-- Tabs de método de pago -->
                <ul class="nav nav-tabs" id="pagoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-efectivo" data-bs-toggle="tab" data-bs-target="#pane-efectivo" type="button" role="tab" aria-controls="pane-efectivo" aria-selected="true" data-metodo="efectivo">
                            <i class="bi bi-cash me-1"></i> Efectivo
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tarjeta" data-bs-toggle="tab" data-bs-target="#pane-tarjeta" type="button" role="tab" aria-controls="pane-tarjeta" aria-selected="false" data-metodo="tarjeta">
                            <i class="bi bi-credit-card me-1"></i> Tarjeta
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-mixto" data-bs-toggle="tab" data-bs-target="#pane-mixto" type="button" role="tab" aria-controls="pane-mixto" aria-selected="false" data-metodo="mixto">
                            <i class="bi bi-shuffle me-1"></i> Mixto
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-nc" data-bs-toggle="tab" data-bs-target="#pane-nc" type="button" role="tab" aria-controls="pane-nc" aria-selected="false" data-metodo="nota_credito">
                            <i class="bi bi-receipt me-1"></i> Nota de crédito
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-vale" data-bs-toggle="tab" data-bs-target="#pane-vale" type="button" role="tab" aria-controls="pane-vale" aria-selected="false" data-metodo="vale_saldo">
                            <i class="bi bi-wallet2 me-1"></i> Vale / Saldo
                        </button>
                    </li>
                </ul>

                <div class="tab-content py-3">
                    <!-- EFECTIVO -->
                    <div class="tab-pane fade show active" id="pane-efectivo" role="tabpanel" aria-labelledby="tab-efectivo">
                        <label class="form-label">Recibido</label>
                        <div class="input-group mb-2">
                            <span class="input-group-text currency-symbol">S/</span>
                            <input type="number" class="form-control" id="inputRecibido" min="0" step="0.1" inputmode="decimal" placeholder="0.00">
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Vuelto:</span>
                            <strong id="lblVuelto">S/ 0.00</strong>
                        </div>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-secondary btn-pill btnMontoRapido" data-monto="10">S/10</button>
                            <button class="btn btn-outline-secondary btn-pill btnMontoRapido" data-monto="20">S/20</button>
                            <button class="btn btn-outline-secondary btn-pill btnMontoRapido" data-monto="50">S/50</button>
                            <button class="btn btn-outline-secondary btn-pill btnMontoRapido" data-monto="100">S/100</button>
                        </div>
                    </div>

                    <!-- TARJETA -->
                    <div class="tab-pane fade" id="pane-tarjeta" role="tabpanel" aria-labelledby="tab-tarjeta">
                        <div class="mb-2">
                            <label class="form-label">Referencia / Autorización</label>
                            <input type="text" class="form-control" id="inputRefTarjeta" placeholder="0000-0000">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text currency-symbol">S/</span>
                                <input type="number" class="form-control" id="inputMontoTarjeta" min="0" step="0.1" inputmode="decimal" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- MIXTO -->
                    <div class="tab-pane fade" id="pane-mixto" role="tabpanel" aria-labelledby="tab-mixto">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Efectivo</label>
                                <div class="input-group">
                                    <span class="input-group-text currency-symbol">S/</span>
                                    <input type="number" class="form-control" id="inputMixtoEfectivo" min="0" step="0.1" inputmode="decimal">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="form-label">Tarjeta</label>
                                <div class="input-group">
                                    <span class="input-group-text currency-symbol">S/</span>
                                    <input type="number" class="form-control" id="inputMixtoTarjeta" min="0" step="0.1" inputmode="decimal">
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-end small text-muted">Debe coincidir con el total.</div>
                    </div>

                    <!-- NOTA DE CRÉDITO -->
                    <div class="tab-pane fade" id="pane-nc" role="tabpanel" aria-labelledby="tab-nc">
                        <div class="mb-2">
                            <label class="form-label">N° Nota de crédito</label>
                            <input type="text" class="form-control" id="inputNCRef" placeholder="F001-00012345">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Monto a aplicar</label>
                            <div class="input-group">
                                <span class="input-group-text currency-symbol">S/</span>
                                <input type="number" class="form-control" id="inputNCMonto" min="0" step="0.1" inputmode="decimal" placeholder="0.00">
                            </div>
                        </div>
                        <div class="mini-hint">El monto aplicado no debe exceder el total.</div>
                    </div>

                    <!-- VALE / SALDO -->
                    <div class="tab-pane fade" id="pane-vale" role="tabpanel" aria-labelledby="tab-vale">
                        <div class="mb-2">
                            <label class="form-label">Código de vale / ID saldo</label>
                            <input type="text" class="form-control" id="inputValeRef" placeholder="VAL-00001">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Monto a usar</label>
                            <div class="input-group">
                                <span class="input-group-text currency-symbol">S/</span>
                                <input type="number" class="form-control" id="inputValeMonto" min="0" step="0.1" inputmode="decimal" placeholder="0.00">
                            </div>
                        </div>
                        <div class="mini-hint">Verifica el saldo disponible del cliente.</div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-outline-secondary btn-pill" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary btn-pill" id="btnConfirmarPago"><i class="bi bi-check2-circle me-1"></i> Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas móvil -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCarrito">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title"><i class="bi bi-cart3 me-2"></i>Carrito</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <ul class="list-group list-group-flush flex-grow-1" id="listaCarritoMobile"></ul>
        <div class="sticky-totals p-3 mt-3">
            <div class="d-flex justify-content-between">
                <span class="text-muted">IGV</span><span id="lblImpuestoM">S/ 0.00</span>
            </div>
            <div class="d-flex justify-content-between fs-5 fw-bold">
                <span>Total</span><span id="lblTotalM">S/ 0.00</span>
            </div>
            <div class="d-grid mt-2">
                <button class="btn btn-success btn-lg btn-pill" data-bs-toggle="modal" data-bs-target="#modalPago"><i class="bi bi-cash-coin me-1"></i> Cobrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Atajos -->
<div class="modal fade" id="modalAtajos" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-keyboard me-2"></i>Atajos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-unstyled small mb-0">
                    <li><span class="kbd">F1</span> Buscar producto</li>
                    <li><span class="kbd">F2</span> Cobrar</li>
                    <li><span class="kbd">F4</span> Vaciar carrito</li>
                    <li><span class="kbd">ESC</span> Cerrar modales</li>
                    <li><span class="kbd">Enter</span> Añadir producto (desde búsqueda/código)</li>
                </ul>
            </div>
        </div>
    </div>
</div>



<?php include('partial/scripts.php'); ?>
<script src="assets/js/scrollbar/custom.js"></script>
<script src="assets/js/tooltip-init.js"></script>
<script src="js/Ventas.js"></script>
<?php include('partial/footer-end.php'); ?>