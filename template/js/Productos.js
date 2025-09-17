document.addEventListener("DOMContentLoaded", () => {
  // === Setup ===
  const resultContainer = document.getElementById("resultContainer");
  const sedeID = Number(resultContainer?.dataset?.sedeId || 0);
  let currentPage = 1;
  let currentSearch = "";

  const searchInput = document.getElementById("searchInput");
  const pagination = document.getElementById("pagination");

  // === Rutas base (evitar /template/ en las imágenes) ===
  // Detecta la raiz hasta /template/ y usa esa parte para apuntar a /image/products
  const APP_ROOT = (() => {
    const path = window.location.pathname;
    const idx = path.toLowerCase().indexOf("/template/");
    return idx > -1 ? path.substring(0, idx) : path.replace(/\/$/, "");
  })();

  // Carpeta real de imágenes: /image/products/
  const IMG_BASE = `${APP_ROOT}/image/products/`;

  // === Helpers de imagen / modales ===
  // Placeholder SVG inline (evita 404)
  const PLACEHOLDER_SVG =
    "data:image/svg+xml;utf8," +
    encodeURIComponent(`
    <svg xmlns="http://www.w3.org/2000/svg" width="386" height="400" viewBox="0 0 386 400">
      <rect width="386" height="400" fill="#f1f3f5"/>
      <g fill="#adb5bd" font-family="Arial,Helvetica,sans-serif" font-size="14">
        <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle">Sin imagen</text>
      </g>
    </svg>`);

  // Intenta SKU.jpg -> .png -> .webp -> nombre del campo "ImagenProducto" -> placeholder
  function setProductImage(imgEl, sku, fallbackByField) {
    if (!imgEl) return;
    const tries = [];
    if (sku) {
      tries.push(`${IMG_BASE}${sku}.jpg`);
      tries.push(`${IMG_BASE}${sku}.png`);
      tries.push(`${IMG_BASE}${sku}.webp`);
    }
    if (fallbackByField) tries.push(`${IMG_BASE}${fallbackByField}`);

    let idx = 0;
    const next = () => {
      if (idx < tries.length) {
        imgEl.src = tries[idx++];
        imgEl.onerror = next;
      } else {
        imgEl.onerror = null;
        imgEl.src = PLACEHOLDER_SVG; // evita buscar el .webp inexistente
      }
    };
    next();
  }

  // Evita warning "Blocked aria-hidden..." (cierra otros modals y quita foco)
  function closeOtherModalsAndBlur() {
    if (document.activeElement) {
      try {
        document.activeElement.blur();
      } catch (_) {}
    }
    document.querySelectorAll(".modal.show").forEach((modalEl) => {
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
    });
  }

  // === Data fetch ===
  const fetchProductos = (page = 1, search = "") => {
    const url = `assets/ajax/productos_ajax.php?action=buscarProductos&page=${page}&search=${encodeURIComponent(
      search
    )}&id_sede=${sedeID}`;

    fetch(url)
      .then((res) => res.json())
      .then((data) => {
        if (!data.success) {
          resultContainer.innerHTML =
            '<p class="text-danger">Error al cargar productos.</p>';
          return;
        }
        renderProductos(data.data.productos);
        renderPagination(data.data.total_pages, data.data.current_page);
      })
      .catch((err) => {
        console.error(err);
        resultContainer.innerHTML =
          '<p class="text-danger">Error de conexión.</p>';
      });
  };

  // === UI render: listado en formato lista (responsive) ===
  const renderProductos = (productos) => {
    resultContainer.innerHTML = "";

    if (!productos || productos.length === 0) {
      resultContainer.innerHTML = `
        <div class="col-12">
          <div class="alert alert-light border text-center mb-0">
            <i class="fa fa-search me-1"></i> No se encontraron resultados.
          </div>
        </div>`;
      return;
    }

    const getStockBadge = (stock) => {
      const n = Number(stock || 0);
      if (n <= 0)
        return `<span class="badge rounded-pill bg-danger">SIN STOCK</span>`;
      if (n <= 10)
        return `<span class="badge rounded-pill bg-warning text-dark">${n} en sede</span>`;
      return `<span class="badge rounded-pill bg-success">${n} en sede</span>`;
    };

    const getPrecio = (p) => {
      if (p.precio && String(p.precio).trim() !== "") return p.precio; // string ya formateado desde el Controller
      const val = p.precio_venta != null ? Number(p.precio_venta) : null;
      const mon = p.moneda_venta || "";
      return val != null && !Number.isNaN(val)
        ? `${val.toFixed(2)} ${mon}`
        : "—";
    };

    const fmtOtrasSedes = (otras) => {
      const chips = (otras || [])
        .filter((s) => Number(s.stock_actual) > 0)
        .map(
          (s) =>
            `<span class="badge bg-secondary-subtle text-dark border me-1 mb-1">${s.nombre_sede}: <strong>${s.stock_actual}</strong></span>`
        )
        .join("");
      return (
        chips ||
        `<span class="text-muted small">Sin stock en otras sedes</span>`
      );
    };

    // Header “tabla”
    const header = `
      <div class="col-12">
        <div class="list-group shadow-sm">
          <div class="list-group-item py-2 bg-light sticky-top" style="z-index:0;">
            <div class="d-none d-md-grid" style="grid-template-columns: 1fr 180px 260px 140px; gap: 12px; align-items:center;">
              <div class="text-uppercase fw-semibold small text-muted">Producto</div>
              <div class="text-uppercase fw-semibold small text-muted">Precio</div>
              <div class="text-uppercase fw-semibold small text-muted">Saldos</div>
              <div class="text-uppercase fw-semibold small text-muted text-end">Acciones</div>
            </div>
            <div class="d-grid d-md-none small text-muted" style="grid-template-columns: 1fr auto; gap: 8px;">
              <div class="fw-semibold">Producto</div>
              <div class="fw-semibold">Acciones</div>
            </div>
          </div>
        </div>
      </div>`;
    resultContainer.insertAdjacentHTML("beforeend", header);

    // Contenedor de filas
    resultContainer.insertAdjacentHTML(
      "beforeend",
      `<div class="col-12"><div class="list-group">`
    );

    productos.forEach((p) => {
      const precioStr = getPrecio(p);
      const stockActual = getStockBadge(p.stock_actual); // ya basado en id_sede
      const otrasSedesHTML = fmtOtrasSedes(p.otras_sedes); // backend ya excluye sede actual
      const codigoProveedor = p.CodigoReferencia_proveedor || "";

      const titulo = p.titulo?.trim()
        ? p.titulo
        : p.DescripcionCompleta || "Producto";
      const descripcion = p.descripcion?.trim() || p.descripcion_variante || "";

      const topBadges = `
        <div class="d-flex flex-wrap gap-2 mb-1">
          <span class="badge rounded-pill bg-primary">${
            p.SKU_Productos || "SIN-SKU"
          }</span>
          ${
            p.clasificacion_general
              ? `<span class="badge rounded-pill bg-info">${p.clasificacion_general}</span>`
              : ""
          }
          ${
            p.nombre_especifico
              ? `<span class="badge rounded-pill badge-light-info">${p.nombre_especifico}</span>`
              : ""
          }
          ${
            p.MarcaProductos
              ? `<span class="badge rounded-pill bg-success"><i class="fa fa-industry me-1"></i>${p.MarcaProductos}</span>`
              : ""
          }
          ${
            p.OrigenProductos
              ? `<span class="badge rounded-pill bg-warning"><i class="fa fa-globe me-1"></i>${p.OrigenProductos}</span>`
              : ""
          }
        </div>`;

      const leftCell = `
        <div>
          ${topBadges}
          <div class="fw-semibold">${titulo}</div>
          ${
            codigoProveedor
              ? `<div class="text-muted small"><i class="fa fa-barcode me-1"></i>${codigoProveedor}</div>`
              : ""
          }

          <!-- Mobile info -->
          <div class="mt-2 d-md-none">
            <span class="badge rounded-pill badge-light-danger me-2"><i class="fa fa-tag me-2"></i>${precioStr}</span>
            ${stockActual}
          </div>
          <div class="mt-2 d-md-none">
            <div class="small text-muted mb-1"><i class="fa fa-warehouse me-1"></i>Otras sedes</div>
            <div class="d-flex flex-wrap">${otrasSedesHTML}</div>
          </div>
        </div>`;

      const rightActions = `
        <div class="d-flex gap-2 justify-content-end">
          <button class="btn btn-outline-secondary btn-sm rounded-pill"
                  data-bs-toggle="modal" data-bs-target="#modalVer"
                  title="Ver Detalles"
                  onclick='cargarDetalleProducto(${JSON.stringify(p)})'>
            <i class="fa fa-eye"></i>
          </button>
        </div>`;

      const row = `
        <div class="list-group-item py-3">
          <!-- Desktop grid -->
          <div class="d-none d-md-grid align-items-center"
               style="grid-template-columns: 1fr 180px 260px 140px; gap: 12px;">
            ${leftCell}
            <div><span class="f-14 badge rounded-pill badge-light-secondary">${precioStr}</span></div>
            <div>
              <div class="mb-1">${stockActual}</div>
              <div class="small text-muted mb-1"><i class="fa fa-warehouse me-1"></i>Otras sedes</div>
              <div class="d-flex flex-wrap">${otrasSedesHTML}</div>
            </div>
            <div class="text-end">${rightActions}</div>
          </div>

          <!-- Mobile stack -->
          <div class="d-grid d-md-none" style="grid-template-columns: 1fr auto; gap: 8px;">
            ${leftCell}
            <div class="ms-auto">${rightActions}</div>
          </div>
        </div>`;
      resultContainer.lastElementChild.insertAdjacentHTML("beforeend", row);
    });

    resultContainer.insertAdjacentHTML("beforeend", `</div></div>`);
  };

  // === Paginación ===
  const renderPagination = (totalPages, currentPage) => {
    pagination.innerHTML = "";
    if (totalPages <= 1) return;

    const prevDisabled = currentPage === 1 ? "disabled" : "";
    const nextDisabled = currentPage === totalPages ? "disabled" : "";

    const html = `
      <button class="btn btn-pill btn-primary me-2" id="prevBtn" ${prevDisabled}>Anterior</button>
      <span class="align-self-center">Página ${currentPage} de ${totalPages}</span>
      <button class="btn btn-pill btn-primary ms-2" id="nextBtn" ${nextDisabled}>Siguiente</button>`;
    pagination.innerHTML = html;

    document.getElementById("prevBtn").addEventListener("click", () => {
      if (currentPage > 1) {
        currentPage--;
        fetchProductos(currentPage, currentSearch);
      }
    });
    document.getElementById("nextBtn").addEventListener("click", () => {
      if (currentPage < totalPages) {
        currentPage++;
        fetchProductos(currentPage, currentSearch);
      }
    });
  };

  // === Búsqueda ===
  searchInput.addEventListener("input", () => {
    currentSearch = searchInput.value.trim();
    currentPage = 1;
    fetchProductos(currentPage, currentSearch);
  });

  // === Carga inicial ===
  fetchProductos(currentPage, currentSearch);
  document.addEventListener("producto:creado", () => {
    fetchProductos(currentPage, currentSearch);
  });

  // === Catálogo de divisas para el modal Detalle ===
  async function cargarDivisasDetalleYSeleccionar(valorActual) {
    const sel = document.getElementById("detDivisa");
    if (!sel) return;
    try {
      const r = await fetch(
        "assets/ajax/tipo_moneda_ajax.php?action=obtenerMonedas",
        { cache: "no-store" }
      );
      const data = await r.json();
      if (!data?.success) return;
      sel.innerHTML = '<option value="" disabled>Seleccione…</option>';
      (data.data || []).forEach((m) => {
        const op = document.createElement("option");
        op.value = m.CodigoSunat_tipoMoneda; // p.ej. "PEN" / "USD"
        op.textContent = m.Abreviatura_TipoMoneda; // p.ej. "PEN" / "USD"
        sel.appendChild(op);
      });
      if (valorActual) {
        sel.value = valorActual;
        // fallback si el catálogo no trajo esa clave
        if (sel.value !== valorActual) {
          const op = document.createElement("option");
          op.value = valorActual;
          op.textContent = valorActual;
          sel.appendChild(op);
          sel.value = valorActual;
        }
      }
    } catch (e) {
      console.error("cargarDivisasDetalleYSeleccionar:", e);
    }
  }

  // === Modal: detalle ===
  // === Modal Detalle/Editar UX 2025 ===
  window.cargarDetalleProducto = async (producto) => {
    closeOtherModalsAndBlur();

    // Helpers
    const $ = (s) => document.querySelector(s);
    const set = (sel, val) => {
      const el = $(sel);
      if (el) el.value = val ?? "";
    };
    const setText = (sel, val) => {
      const el = $(sel);
      if (el) el.textContent = val ?? "";
    };
    const enableEdit = (on) => {
      const lockedIds = new Set([
        "detDescripcionCompleta", // se autogenera
        "detCategoria",
        "detMarcaVehiculo",
        "detTipoIGV",
        "detSKU", // ya lo bloqueabas
      ]);

      ["#formDetGeneral", "#formDetVariante", "#formDetPrecio"].forEach((f) => {
        document
          .querySelector(f)
          ?.querySelectorAll("input,textarea,select")
          .forEach((el) => {
            const mustLock = lockedIds.has(el.id);
            el.disabled = mustLock ? true : !on;
          });
      });

      // Mostrar/ocultar botones de guardar
      document
        .getElementById("btnGuardarCambios")
        ?.classList.toggle("d-none", !on);
      document
        .getElementById("btnGuardarCambiosFooter")
        ?.classList.toggle("d-none", !on);

      // Cambiar estilos del botón de edición
      const btnToggle = document.getElementById("btnToggleEdit");
      btnToggle?.classList.toggle("btn-outline-primary", !on);
      btnToggle?.classList.toggle("btn-warning", on);
      if (btnToggle) {
        btnToggle.innerHTML = on
          ? '<i class="fa fa-lock-open me-1"></i> Modo edición'
          : '<i class="fa fa-pencil me-1"></i> Editar';
      }

      document
        .getElementById("formDetGeneral")
        ?.setAttribute("data-mode", on ? "edit" : "view");
      document
        .getElementById("formDetVariante")
        ?.setAttribute("data-mode", on ? "edit" : "view");
      document
        .getElementById("formDetPrecio")
        ?.setAttribute("data-mode", on ? "edit" : "view");
    };

    // Datos clave
    const precio =
      producto.precio && String(producto.precio).trim() !== ""
        ? producto.precio
        : producto.precio_venta != null
        ? `${Number(producto.precio_venta).toFixed(2)} ${
            producto.moneda_venta || ""
          }`
        : "—";

    // Header
    const titulo = producto.titulo?.trim()
      ? producto.titulo
      : producto.DescripcionCompleta || "Producto";

    setText("#detTitulo", titulo);
    setText(
      "#detSubtitulo",
      `${producto.SKU_Productos || "—"} · ${
        producto.clasificacion_general || "—"
      } / ${producto.nombre_especifico || "—"} · ${
        producto.MarcaProductos || ""
      }`
    );

    // Badges
    const badges = [];
    if (producto.SKU_Productos)
      badges.push(
        `<span class="badge rounded-pill bg-primary">${producto.SKU_Productos}</span>`
      );
    if (producto.clasificacion_general)
      badges.push(
        `<span class="badge rounded-pill bg-info">${producto.clasificacion_general}</span>`
      );
    if (producto.nombre_especifico)
      badges.push(
        `<span class="badge rounded-pill badge-light-info">${producto.nombre_especifico}</span>`
      );
    if (producto.MarcaProductos)
      badges.push(
        `<span class="badge rounded-pill bg-success"><i class="fa fa-industry me-1"></i>${producto.MarcaProductos}</span>`
      );
    if (producto.OrigenProductos)
      badges.push(
        `<span class="badge rounded-pill bg-warning"><i class="fa fa-globe me-1"></i>${producto.OrigenProductos}</span>`
      );
    if (producto.unidadMedida)
      badges.push(
        `<span class="badge rounded-pill bg-secondary-subtle text-dark border">${producto.unidadMedida}</span>`
      );
    document.getElementById("detBadges").innerHTML = badges.join(" ");

    // Imagen (ruta nueva /imagen/products/)
    const img = document.querySelector("#detImagen");
    if (img) {
      img.alt = titulo;
      setProductImage(img, producto.SKU_Productos, producto.ImagenProducto);
    }

    // Resumen precio & stock
    setText("#detPrecio", precio);
    setText(
      "#detStock",
      producto.stock_actual != null ? Number(producto.stock_actual) : 0
    );
    setText("#detPrecioDesde", producto.precio_desde || "—");

    // === Avisos informativos de sede/stock/precio ===
    (function () {
      const panePrecio = document.querySelector("#pane-precio");
      if (!panePrecio) return;

      // Limpia alert previo si reabres el modal
      const old = panePrecio.querySelector(".alert-sede-no-registrado");
      if (old) old.remove();

      const stockActual = Number(producto.stock_actual || 0);
      const noRegistrado = !(stockActual > 0); // proxy: no hay fila de stock o stock 0

      if (noRegistrado) {
        // Card alert informativo (no bloqueante)
        const alert = document.createElement("div");
        alert.className = "alert alert-info alert-sede-no-registrado rounded-3";
        alert.innerHTML = `
      <div class="d-flex align-items-start gap-2">
        <i class="fa fa-info-circle fa-lg mt-1"></i>
        <div>
          <div class="fw-semibold">Producto no registrado en esta sede</div>
          <div class="small">
            Cualquier modificación de datos <strong>no registrará movimiento de mercadería</strong> porque no existe una compra asociada en esta sede.
            Si modifica el precio de venta, <strong>se registrará la vigencia del precio</strong>, pero esta sede <strong>no tendrá saldo</strong> hasta que se registre una compra.
          </div>
        </div>
      </div>
    `;
        // Inserta arriba del formulario de precio
        const formPrecio = document.querySelector("#formDetPrecio");
        if (formPrecio && formPrecio.parentElement) {
          formPrecio.parentElement.insertBefore(alert, formPrecio);
        } else {
          panePrecio.prepend(alert);
        }

        // small adicional bajo el input de precio
        let extraSmall = document.getElementById("detPrecioVentaHintSede");
        if (!extraSmall) {
          extraSmall = document.createElement("small");
          extraSmall.id = "detPrecioVentaHintSede";
          extraSmall.className = "text-muted small d-block mt-1";
          extraSmall.textContent =
            "Se registrará el precio de venta, pero la sede no tiene saldo por falta de registro de compra.";
          const precioInput = document.getElementById("detPrecioVenta");
          if (precioInput) {
            const wrap =
              precioInput.closest(".col-md-4") || precioInput.parentElement;
            wrap && wrap.appendChild(extraSmall);
          }
        } else {
          extraSmall.classList.remove("d-none");
        }
      } else {
        // si hay stock, escondemos el small adicional si existiera
        const extraSmall = document.getElementById("detPrecioVentaHintSede");
        if (extraSmall) extraSmall.classList.add("d-none");
      }
    })();

    // Otras sedes
    const otras = (producto.otras_sedes || []).filter(
      (s) => Number(s.stock_actual) > 0
    );
    document.getElementById("detOtrasSedes").innerHTML = otras.length
      ? otras
          .map(
            (s) =>
              `<span class="badge bg-secondary-subtle text-dark border">${s.nombre_sede}: <strong>${s.stock_actual}</strong></span>`
          )
          .join(" ")
      : `<span class="text-muted small">Sin stock en otras sedes</span>`;

    // ====== Rellenar formularios ======
    // IDs
    $("#detIdBase") &&
      ($("#detIdBase").value = producto.id_producto_base || producto.id || "");
    $("#detIdVariante") &&
      ($("#detIdVariante").value = producto.id_producto_variante || "");

    // General
    set("#detDescripcionCompleta", producto.DescripcionCompleta);
    set("#detOEM", producto.OEM_productos);
    set(
      "#detCategoria",
      `${producto.clasificacion_general || ""} ${
        producto.nombre_especifico ? " - " + producto.nombre_especifico : ""
      }`.trim()
    );
    set("#detMarcaVehiculo", producto.MarcaVehiculo);
    set("#detModeloVehiculo", producto.ModeloVehiculo);
    set("#detCilindrada", producto.CilindradaVehiculo);
    set("#detMotor", producto.MotorVehiculo);
    set("#detAnioIni", producto.AñoInicialVehiculo);
    set("#detAnioFin", producto.AñoFinVehiculo);

    // Variante
    set("#detSKU", producto.SKU_Productos);
    set("#detCodProv", producto.CodigoReferencia_proveedor);
    set("#detUM", producto.unidadMedida);
    set("#detMarcaProd", producto.MarcaProductos);
    set("#detOrigen", producto.OrigenProductos);
    set("#detDescVar", producto.descripcion_variante);
    set("#detObs", producto.Observaciones);
    const chkICBPER = document.getElementById("detICBPER");
    if (chkICBPER) chkICBPER.checked = String(producto.icbper || "0") === "1";

    // Precio
    await cargarDivisasDetalleYSeleccionar(producto.moneda_venta || "");
    set(
      "#detPrecioVenta",
      producto.precio_venta != null && producto.precio_venta !== ""
        ? Number(producto.precio_venta)
        : ""
    );
    set(
      "#detUtilidad",
      producto.utilidad_pct != null && producto.utilidad_pct !== ""
        ? Number(producto.utilidad_pct)
        : ""
    );
    set(
      "#detTipoIGV",
      producto.tipoIGV != null && producto.tipoIGV !== ""
        ? Number(producto.tipoIGV)
        : 10
    );

    // === Wiring precio venta: hint + 3 decimales + recálculo utilidad ===
    const inPrecioVenta = document.getElementById("detPrecioVenta");
    const inUtilidad = document.getElementById("detUtilidad");
    const hintPV = document.getElementById("detPrecioVentaHint");

    // precio de compra de referencia para recalcular utilidad
    const precioCompra =
      Number(producto.precio_compra) ||
      Number(producto.precio_compra_unit) ||
      Number(producto.precio_compra_ult) ||
      0;

    function format3dec(num) {
      const n = Number(num);
      return Number.isFinite(n) ? n.toFixed(3) : "";
    }

    function recalcUtilidadSiAplica() {
      if (!inPrecioVenta || !inUtilidad) return;
      const pv = Number(inPrecioVenta.value || 0);
      if (precioCompra > 0 && pv > 0) {
        const util = ((pv - precioCompra) / precioCompra) * 100;
        inUtilidad.value = util.toFixed(2);
      }
    }

    // Evita duplicar listeners si abres el modal varias veces
    if (inPrecioVenta && !inPrecioVenta.dataset.wired) {
      inPrecioVenta.addEventListener("input", () => {
        // mostrar hint cada vez que cambie
        if (hintPV) hintPV.classList.remove("d-none");
        recalcUtilidadSiAplica();
      });

      inPrecioVenta.addEventListener("blur", () => {
        if (inPrecioVenta.value !== "") {
          // OJO: en type="number", el navegador podría no mostrar los ceros a la derecha,
          // pero el value quedará "12.340" y eso sí lo enviamos al backend.
          inPrecioVenta.value = format3dec(inPrecioVenta.value);
        }
      });

      inPrecioVenta.dataset.wired = "1";
    }

    // Si ya vino con valor, normaliza a 3 decimales para el envío (aunque visualmente pueda ocultar ceros)
    if (inPrecioVenta && inPrecioVenta.value !== "") {
      inPrecioVenta.value = format3dec(inPrecioVenta.value);
      recalcUtilidadSiAplica();
    }

    // Estado inicial: vista
    enableEdit(false);

    // ==== Botones ====
    const btnToggle = document.getElementById("btnToggleEdit");
    const btnSave = document.getElementById("btnGuardarCambios");
    const btnSaveFooter = document.getElementById("btnGuardarCambiosFooter");

    const doSave = async () => {
      const idVar = document.getElementById("detIdVariante")?.value || "";
      const idBase = document.getElementById("detIdBase")?.value || "";

      // Recolectar datos
      const payload = new FormData();
      payload.append("id_producto_variante", idVar);
      payload.append("id_producto_base", idBase);

      if (sedeID) payload.append("id_sede", String(sedeID));
      // General
      payload.append(
        "DescripcionCompleta",
        $("#detDescripcionCompleta")?.value?.trim() || ""
      );
      payload.append("OEM_productos", $("#detOEM")?.value?.trim() || "");
      payload.append(
        "MarcaVehiculo",
        $("#detMarcaVehiculo")?.value?.trim() || ""
      );
      payload.append(
        "ModeloVehiculo",
        $("#detModeloVehiculo")?.value?.trim() || ""
      );
      payload.append(
        "CilindradaVehiculo",
        $("#detCilindrada")?.value?.trim() || ""
      );
      payload.append("MotorVehiculo", $("#detMotor")?.value?.trim() || "");
      payload.append(
        "AñoInicialVehiculo",
        $("#detAnioIni")?.value?.trim() || ""
      );
      payload.append("AñoFinVehiculo", $("#detAnioFin")?.value?.trim() || "");

      // Variante
      payload.append("SKU_Productos", $("#detSKU")?.value?.trim() || "");
      payload.append(
        "CodigoReferencia_proveedor",
        $("#detCodProv")?.value?.trim() || ""
      );
      payload.append("unidadMedida", $("#detUM")?.value?.trim() || "");
      payload.append("MarcaProductos", $("#detMarcaProd")?.value?.trim() || "");
      payload.append("OrigenProductos", $("#detOrigen")?.value?.trim() || "");
      payload.append(
        "descripcion_variante",
        $("#detDescVar")?.value?.trim() || ""
      );
      payload.append("Observaciones", $("#detObs")?.value?.trim() || "");
      payload.append(
        "icbper",
        document.getElementById("detICBPER")?.checked ? "1" : "0"
      );

      // Precio (opcional — crea vigencia si hay datos válidos)
      const divisa = (document.getElementById("detDivisa")?.value || "").trim();
      let pv =
        parseFloat(document.getElementById("detPrecioVenta")?.value || "0") ||
        0;
      const util = document.getElementById("detUtilidad")?.value?.trim();
      const tipoIGV =
        parseInt(document.getElementById("detTipoIGV")?.value || "10", 10) ||
        10;

      if (divisa && pv > 0) {
        pv = Number(pv).toFixed(3); // ← SIEMPRE 3 decimales
        payload.append("moneda_venta", divisa);
        payload.append("precio_venta", String(pv));
        if (util !== "" && util !== null) payload.append("utilidad_pct", util);
        payload.append("tipoIGV", String(tipoIGV));
      }

      // Llamada AJAX
      const saveBtns = [btnSave, btnSaveFooter];
      const oldHTML = saveBtns.map((b) => b?.innerHTML);
      saveBtns.forEach((b) => {
        if (b) {
          b.disabled = true;
          b.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2"></span>Guardando…';
        }
      });

      try {
        const resp = await fetch(
          "assets/ajax/productos_ajax.php?action=actualizarProducto",
          {
            method: "POST",
            body: payload,
          }
        );
        const json = await resp.json();
        if (!json?.success)
          throw new Error(json?.message || "No se pudo actualizar.");

        // Refrescar datos visuales mínimos
        if (json.data?.precio_venta_fmt) {
          document.getElementById("detPrecio").textContent =
            json.data.precio_venta_fmt;
          document.getElementById("detPrecioDesde").textContent =
            json.data.precio_desde || "—";
        }

        // === Aviso si se registró precio para la sede y no hay stock ===
        const info =
          json && json.data && json.data.info ? json.data.info : null;

        if (info && info.precio_creado) {
          let infoEl = document.getElementById("detPrecioInfo");
          if (!infoEl) {
            infoEl = document.createElement("small");
            infoEl.id = "detPrecioInfo";
            infoEl.className = "text-warning d-block mt-1";
            const wrap =
              document.querySelector("#formDetPrecio") ||
              document.getElementById("modalVer");
            wrap && wrap.appendChild(infoEl);
          }
          infoEl.textContent =
            info.mensaje || `Se registró precio en la sede ${info.sede}.`;
          infoEl.classList.remove("d-none");

          if (window.Swal && Swal.fire) {
            Swal.fire({
              icon: info.sin_stock ? "info" : "success",
              title: info.sin_stock
                ? "Precio creado (sin stock)"
                : "Precio creado",
              text: info.mensaje || "",
            });
          }
        }

        // Salir de edición
        enableEdit(false);

        // feedback
        if (window.Swal && Swal.fire) {
          await Swal.fire({
            icon: "success",
            title: "Guardado",
            text: "Los cambios se aplicaron correctamente.",
          });
        } else {
          alert("Cambios guardados.");
        }

        // refrescar listado
        document.dispatchEvent(new CustomEvent("producto:creado")); // ya existente para refrescar lista
      } catch (e) {
        console.error(e);
        if (window.Swal && Swal.fire) {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: e.message || "No se pudo guardar.",
          });
        } else {
          alert(e.message || "No se pudo guardar.");
        }
      } finally {
        saveBtns.forEach((b, i) => {
          if (b) {
            b.disabled = false;
            b.innerHTML = oldHTML[i];
          }
        });
      }
    };

    if (btnToggle) {
      btnToggle.onclick = () => {
        const isView =
          (document.getElementById("formDetGeneral")?.dataset.mode ||
            "view") === "view";
        enableEdit(isView);
      };
    }
    if (btnSave) btnSave.onclick = doSave;
    if (btnSaveFooter) btnSaveFooter.onclick = doSave;

    function rebuildDescripcionFromDetail() {
      const get = (id) =>
        (document.getElementById(id)?.value || "").toUpperCase().trim();

      const categoria = get("detCategoria");
      const marcaVeh = get("detMarcaVehiculo");
      const modelo = get("detModeloVehiculo");
      const cil = get("detCilindrada");
      const motor = get("detMotor");
      const anioIni =
        document.getElementById("detAnioIni")?.value?.trim() || "";
      const anioFin =
        document.getElementById("detAnioFin")?.value?.trim() || "";
      const marcaProd = get("detMarcaProd");
      const origen = get("detOrigen");
      const oem = (document.getElementById("detOEM")?.value || "")
        .toUpperCase()
        .trim();
      const descVar = (document.getElementById("detDescVar")?.value || "")
        .toUpperCase()
        .trim();

      const partes = [];
      if (categoria) partes.push(categoria);
      if (marcaVeh) partes.push(marcaVeh);
      if (modelo) partes.push(modelo);
      if (cil) partes.push(`C.C. ${cil}`);
      if (motor) partes.push(`MOTOR: ${motor}`);
      if (anioIni || anioFin)
        partes.push(
          `AÑOS: ${anioIni}${anioIni && anioFin ? " - " : ""}${anioFin}`
        );
      if (marcaProd) partes.push(marcaProd);
      if (origen) partes.push(origen);
      if (oem) partes.push(`OEM: ${oem}`);
      if (descVar) partes.push(`— ${descVar}`);

      const full = partes.join(" ").replace(/\s+/g, " ").trim();
      const txt = document.getElementById("detDescripcionCompleta");
      if (txt) txt.value = full;
    }

    function wireDescripcionAuto() {
      const watchIds = [
        "detOEM",
        "detModeloVehiculo",
        "detCilindrada",
        "detMotor",
        "detAnioIni",
        "detAnioFin",
        "detMarcaProd",
        "detOrigen",
        "detDescVar",
      ];
      watchIds.forEach((id) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener("input", rebuildDescripcionFromDetail);
        el.addEventListener("blur", rebuildDescripcionFromDetail);
      });
    }

    // Llama una vez que llenaste los datos
    rebuildDescripcionFromDetail();
    wireDescripcionAuto();
  };

  // === Acciones varias (mantener) ===

  // === Exportar a XLSX con confirmación, spinner y descarga por iframe (robusto) ===
  // === Exportar a XLSX con progreso real (fetch + stream) ===
  window.exportarExcel = async () => {
    const btns = document.querySelectorAll('button[onclick="exportarExcel()"]');
    const oldHTML = [];
    btns.forEach((b, i) => {
      oldHTML[i] = b.innerHTML;
      b.disabled = true;
      b.innerHTML =
        '<span class="spinner-border spinner-border-sm me-2"></span>Generando…';
    });

    const restoreBtns = () => {
      btns.forEach((b, i) => {
        b.disabled = false;
        b.innerHTML = oldHTML[i];
      });
    };

    try {
      // 1) Confirmación
      if (window.Swal && Swal.fire) {
        const r = await Swal.fire({
          title: "Exportar a Excel",
          text: "Se descargará un archivo XLSX con los productos (puede tardar si hay muchos registros).",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Sí, exportar",
          cancelButtonText: "Cancelar",
        });
        if (!r.isConfirmed) {
          restoreBtns();
          return;
        }
      }

      // 2) Modal con barra de progreso
      let $bar, $pct, $msg;
      if (window.Swal && Swal.fire) {
        await Swal.fire({
          title: "Generando Excel…",
          html: `
          <div class="d-flex flex-column gap-3" style="min-width:260px">
            <div class="progress" role="progressbar" aria-label="Progreso" aria-valuemin="0" aria-valuemax="100" style="height: 18px;">
              <div id="xlxProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%">0%</div>
            </div>
            <div id="xlxProgressMsg" class="small text-muted">Preparando…</div>
          </div>`,
          allowOutsideClick: false,
          didOpen: () => {
            $bar = document.getElementById("xlxProgressBar");
            $msg = document.getElementById("xlxProgressMsg");
          },
        });
      }

      // 3) Construir URL
      const search = encodeURIComponent(
        typeof currentSearch !== "undefined" ? currentSearch : ""
      );
      const url = `assets/ajax/productos_ajax.php?action=exportarExcel&search=${search}`;

      // 4) Descarga con fetch + stream y progreso real
      const res = await fetch(url, {
        method: "GET",
        // importante para sesiones PHP
        credentials: "same-origin",
        cache: "no-store",
      });

      if (!res.ok) {
        throw new Error("El servidor respondió con un error.");
      }

      // Content-Length para poder calcular %
      const total = Number(res.headers.get("Content-Length") || 0);
      const fileNameHeader = res.headers.get("Content-Disposition") || ""; // p.ej. attachment; filename="productos_YYYYMMDD_HHMMSS.xlsx"
      const nameMatch = fileNameHeader.match(/filename="([^"]+)"/i);
      const suggestedName =
        (nameMatch && nameMatch[1]) ||
        `productos_${new Date()
          .toISOString()
          .replace(/[-:TZ]/g, "")
          .slice(0, 14)}.xlsx`;

      const reader = res.body?.getReader ? res.body.getReader() : null;

      let received = 0;
      const chunks = [];

      if (!reader) {
        // Fallback: si no hay streaming soportado, baja de golpe
        if ($msg) $msg.textContent = "Descargando…";
        const blob = await res.blob();
        if ($bar) {
          $bar.style.width = "100%";
          $bar.textContent = "100%";
        }
        // Disparar la descarga
        const a = document.createElement("a");
        const href = URL.createObjectURL(blob);
        a.href = href;
        a.download = suggestedName;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
          URL.revokeObjectURL(href);
          a.remove();
        }, 1500);

        if (window.Swal && Swal.close) {
          Swal.close();
          Swal.fire({
            icon: "success",
            title: "Descargado",
            text: "El Excel se generó correctamente.",
          });
        }
        restoreBtns();
        return;
      }

      if ($msg) $msg.textContent = "Descargando…";
      while (true) {
        const { done, value } = await reader.read();
        if (done) break;
        chunks.push(value);
        received += value.length;

        if (total > 0 && $bar) {
          const pct = Math.max(1, Math.round((received * 100) / total));
          $bar.style.width = `${pct}%`;
          $bar.textContent = `${pct}%`;
        } else if ($bar) {
          // si no tenemos Content-Length, dejamos animado sin % exacto
          $bar.classList.add("progress-bar-animated");
        }
      }

      const blob = new Blob(chunks, {
        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
      });

      if ($bar) {
        $bar.style.width = "100%";
        $bar.textContent = "100%";
      }
      if ($msg) $msg.textContent = "Finalizando…";

      // 5) Disparar la descarga
      const a = document.createElement("a");
      const href = URL.createObjectURL(blob);
      a.href = href;
      a.download = suggestedName;
      document.body.appendChild(a);
      a.click();
      setTimeout(() => {
        URL.revokeObjectURL(href);
        a.remove();
      }, 1500);

      // 6) Cierre y feedback
      if (window.Swal && Swal.close) {
        Swal.close();
        Swal.fire({
          icon: "success",
          title: "Descargado",
          text: "El Excel se generó correctamente.",
        });
      }
    } catch (e) {
      console.error(e);
      if (window.Swal && Swal.fire) {
        try {
          Swal.close();
        } catch (_) {}
        Swal.fire({
          icon: "error",
          title: "Error",
          text: e.message || "No se pudo exportar.",
        });
      } else {
        alert(e.message || "No se pudo exportar.");
      }
    } finally {
      restoreBtns();
    }
  };

  window.exportarPDF = () => {
    alert("Aquí iría la lógica para exportar a PDF");
  };

  // === Imagen: validación + vista previa (NO tocar) ===
  const inputImagen = document.querySelector('input[name="ImagenProducto"]');
  const btnVistaPrevia = document.getElementById("btnVistaPrevia");
  const imgPreview = document.getElementById("previewImagenProducto");

  inputImagen.addEventListener("change", function () {
    const file = this.files[0];
    const formatosPermitidos = [
      "image/jpeg",
      "image/png",
      "image/jpg",
      "image/webp",
    ];
    const tamañoMaximoBytes = 3 * 1024 * 1024; // 3 MB

    if (file && formatosPermitidos.includes(file.type)) {
      if (file.size > tamañoMaximoBytes) {
        alert("El archivo excede el tamaño permitido de 3 MB.");
        this.value = "";
        btnVistaPrevia.disabled = true;
        imgPreview.src = "";
        return;
      }

      const reader = new FileReader();
      reader.onload = function (e) {
        imgPreview.src = e.target.result;
      };
      reader.readAsDataURL(file);

      btnVistaPrevia.disabled = false;
    } else {
      this.value = "";
      btnVistaPrevia.disabled = true;
      imgPreview.src = "";
      alert("Formato no permitido. Solo JPG, JPEG, PNG o WEBP.");
    }
  });

  // === SUBIDA AJAX CON PROGRESO (carpeta /imagen/products) ===
  (function () {
    const inputImagen = document.querySelector('input[name="ImagenProducto"]');
    const progressWrap = document.getElementById("wrapUploadProgress");
    const progressBar = document.getElementById("uploadProgressBar");
    const statusLbl = document.getElementById("uploadStatus");
    const hNombre = document.getElementById("ImagenProductoNombre");

    function showProgress(percent, text) {
      if (!progressWrap || !progressBar) return;
      progressWrap.classList.remove("d-none");
      progressBar.style.width = `${percent}%`;
      progressBar.textContent = `${percent}%`;
      if (statusLbl && text != null) statusLbl.textContent = text;
    }
    function hideProgress() {
      if (!progressWrap) return;
      progressWrap.classList.add("d-none");
      if (progressBar) {
        progressBar.style.width = "0%";
        progressBar.textContent = "0%";
      }
      if (statusLbl) statusLbl.textContent = "";
    }

    async function subirImagenConProgreso(file) {
      // Validaciones ya las haces arriba; aquí solo subimos
      const fd = new FormData();
      fd.append("ImagenProducto", file);

      // Si ya tienes SKU generado, intentamos usarlo como nombre base en el server
      const sku = (
        document.querySelector('input[name="SKU_Productos"]')?.value || ""
      ).trim();
      if (sku) fd.append("nombre_base", sku);

      return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(
          "POST",
          "assets/ajax/productos_ajax.php?action=subirImagenProducto",
          true
        );

        xhr.upload.addEventListener("progress", (e) => {
          if (e.lengthComputable) {
            const percent = Math.round((e.loaded * 100) / e.total);
            showProgress(
              percent,
              percent < 100 ? "Subiendo..." : "Procesando..."
            );
          }
        });

        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            try {
              const res = JSON.parse(xhr.responseText || "{}");
              if (xhr.status === 200 && res?.success) {
                showProgress(100, "Carga finalizada");
                resolve(res);
              } else {
                reject(new Error(res?.message || "Fallo al subir la imagen."));
              }
            } catch (err) {
              reject(new Error("Respuesta inválida del servidor."));
            }
          }
        };

        xhr.onerror = function () {
          reject(new Error("Error de red al subir la imagen."));
        };

        xhr.send(fd);
      });
    }

    if (inputImagen) {
      inputImagen.addEventListener("change", async function () {
        const file = this.files?.[0];
        if (!file) {
          hideProgress();
          if (hNombre) hNombre.value = "";
          return;
        }

        // Muestra barra en 0
        showProgress(0, "Preparando subida...");

        try {
          const r = await subirImagenConProgreso(file);
          // Guarda el nombre devuelto (p.ej. SKU.jpg o temporal)
          if (hNombre && r?.data?.nombre_archivo) {
            hNombre.value = r.data.nombre_archivo;
          }
          // Mensaje final ya puesto, mantenemos barra visible
        } catch (e) {
          alert(e.message || "No se pudo subir la imagen.");
          hideProgress();
          // Limpia el hidden para que el backend no use un nombre incorrecto
          if (hNombre) hNombre.value = "";
          // Dejar el input de archivo en blanco si quieres forzar reintento
          // this.value = "";
        }
      });
    }
  })();

  btnVistaPrevia.addEventListener("click", function () {
    const modal = new bootstrap.Modal(
      document.getElementById("modalVistaPreviaImagen")
    );
    modal.show();
  });

  // === Abrir modal "Nuevo producto" (llamado por el botón en el header) ===
  window.abrirModalAgregar = () => {
    const modalEl = document.getElementById("modalAgregarProducto");
    if (!modalEl) return;

    // (Opcional) limpiar validaciones/hints visuales si quedaron del intento anterior
    const form = document.getElementById("formAgregarProducto");
    if (form) {
      form
        .querySelectorAll(".is-invalid")
        .forEach((n) => n.classList.remove("is-invalid"));
      form.querySelectorAll(".invalid-hint").forEach((n) => n.remove());
    }

    // Mostrar el modal (los catálogos y typeaheads se cargan en "shown.bs.modal")
    const bsModal =
      bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    bsModal.show();
  };

  /* ================================
   MODAL: NUEVO PRODUCTO (refactor 2025)
   ================================ */
  (function () {
    if (typeof Bloodhound === "undefined" || !window.jQuery) {
      console.warn("Typeahead no disponible (falta jQuery o Bloodhound).");
    }

    const API = {
      PRODUCTOS: "assets/ajax/productos_ajax.php",
      MONEDAS: "assets/ajax/tipo_moneda_ajax.php",
    };

    const SEL = {
      modal: "#modalAgregarProducto",
      form: "#formAgregarProducto",

      inputCategoria: "#inputCategoria",
      hiddenCategoriaId: "#id_categoriaProductos",
      btnAgregarCategoria: "#btnAgregarCategoria",
      contNuevaCategoria: "#nuevoCategoriaContainer",
      inputClasificacionGeneral: "#inputClasificacionGeneral",
      inputNombreEspecifico: "#inputNombreEspecifico",
      btnGuardarCategoria: "#btnGuardarCategoria",

      inputOEM: '[name="OEM_productos"]',
      inputMarcaVehiculo: "#inputMarcaVehiculo",
      inputModeloVehiculo: '[name="ModeloVehiculo"]',
      inputCilindrada: '[name="CilindradaVehiculo"]',
      inputMotor: '[name="MotorVehiculo"]',
      inputAnioIni: '[name="AñoInicialVehiculo"]',
      inputAnioFin: '[name="AñoFinVehiculo"]',

      selectDivisa: '[name="TipoDivisa"]',
      selectUM: '[name="unidad_medida"]',

      inputSKU: 'input[name="SKU_Productos"]',
      inputCodProveedor: '[name="CodigoReferencia_proveedor"]',
      inputMarcaProducto: '[name="MarcaProductos"]',
      inputOrigenProducto: '[name="OrigenProductos"]',
      inputDescripcionVar: '[name="descripcion_variante"]',
      textDescripcionCompleta: 'textarea[name="DescripcionCompleta"]',

      // Imagen: ¡NO tocar! usas tu bloque actual
      inputImagen: 'input[name="ImagenProducto"]',
      btnVistaPrevia: "#btnVistaPrevia",
    };

    // --- Helpers robustos para el hidden de categoría (hay duplicado en el HTML) ---
    function setHiddenCategoriaAll(valor) {
      document
        .querySelectorAll('[name="id_categoriaProductos"]')
        .forEach((el) => {
          el.value = valor;
        });
    }
    function getHiddenCategoriaFirst() {
      const el = document.querySelector('[name="id_categoriaProductos"]');
      return (el?.value || "").trim();
    }

    const $ = (s) => document.querySelector(s);
    const on = (el, evt, fn) => el && el.addEventListener(evt, fn);
    const setVal = (s, v) => {
      const el = $(s);
      if (el) el.value = v;
    };
    const getVal = (s) => ($(s)?.value ?? "").trim();

    /* ---------- TYPEAHEAD: Categorías ---------- */
    async function obtenerCategorias() {
      try {
        const r = await fetch(`${API.PRODUCTOS}?action=obtenerCategorias`, {
          cache: "no-store",
        });
        const data = await r.json();
        if (!data?.success) return [];
        return (data.data || []).map((cat) => ({
          id: cat.id_categoria,
          nombre: `${cat.clasificacion_general} - ${cat.nombre_especifico}`,
        }));
      } catch (e) {
        console.error("obtenerCategorias:", e);
        return [];
      }
    }

    async function inicializarTypeaheadCategorias() {
      if (!window.jQuery || typeof Bloodhound === "undefined") return;
      const arr = await obtenerCategorias();

      const bh = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace("nombre"),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        local: arr,
      });

      try {
        window.jQuery(SEL.inputCategoria).typeahead("destroy");
      } catch (_) {}

      let lastSelected = { id: "", nombre: "" };

      window
        .jQuery(SEL.inputCategoria)
        .typeahead(
          { hint: true, highlight: true, minLength: 1 },
          {
            name: "categoriasProductos",
            display: "nombre",
            source: bh.ttAdapter ? bh.ttAdapter() : bh,
          }
        )
        .on("typeahead:select", (ev, s) => {
          setHiddenCategoriaAll(String(s.id));
          lastSelected = { id: String(s.id), nombre: String(s.nombre) };
          generarSKU();
          actualizarDescripcion();
        })
        .on("input", function () {
          const current = String(this.value || "").trim();
          if (!current || current !== lastSelected.nombre)
            setHiddenCategoriaAll("");
        });
    }

    /* ---------- TYPEAHEAD: Clasificación General ---------- */
    async function inicializarTypeaheadClasificacion() {
      if (!window.jQuery || typeof Bloodhound === "undefined") return;
      try {
        const r = await fetch(
          `${API.PRODUCTOS}?action=obtenerCategoriasClasificacionGeneral`,
          { cache: "no-store" }
        );
        const data = await r.json();
        if (!data?.success) return;

        const clasifs = [
          ...new Set((data.data || []).map((x) => x.clasificacion_general)),
        ];

        const bh = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.whitespace,
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          local: clasifs,
        });

        window
          .jQuery(SEL.inputClasificacionGeneral)
          .typeahead("destroy")
          .typeahead(
            { hint: true, highlight: true, minLength: 1 },
            { name: "clasificacion_general", source: bh }
          );
      } catch (e) {
        console.error("inicializarTypeaheadClasificacion:", e);
      }
    }

    /* ---------- TYPEAHEAD: Marca de Vehículo ---------- */
    async function inicializarMarcaVehiculos() {
      if (!window.jQuery || typeof Bloodhound === "undefined") return;
      try {
        const r = await fetch(`${API.PRODUCTOS}?action=listarMarcasVehiculos`, {
          cache: "no-store",
        });
        const data = await r.json();
        if (!data?.success) return;

        const marcas = [
          ...new Set((data.data || []).map((m) => m.nombre_marca)),
        ];

        const bh = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.whitespace,
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          local: marcas,
        });

        window
          .jQuery(SEL.inputMarcaVehiculo)
          .typeahead("destroy")
          .typeahead(
            { hint: true, highlight: true, minLength: 1 },
            { name: "nombre_marca", source: bh }
          )
          .on("typeahead:select", function () {
            generarSKU();
            actualizarDescripcion();
          })
          .on("input", function () {
            // nada extra; solo mantenemos comportamiento
          });
      } catch (e) {
        console.error("inicializarMarcaVehiculos:", e);
      }
    }

    /* ---------- Catálogos ---------- */

    async function cargarTipoMonedas() {
      const sel = $(SEL.selectDivisa);
      if (!sel) return;
      try {
        const r = await fetch(`${API.MONEDAS}?action=obtenerMonedas`, {
          cache: "no-store",
        });
        const data = await r.json();
        if (!data?.success) return;
        sel.innerHTML =
          '<option value="" disabled selected>Seleccione…</option>';
        (data.data || []).forEach((m) => {
          const op = document.createElement("option");
          op.value = m.CodigoSunat_tipoMoneda;
          op.textContent = m.Abreviatura_TipoMoneda;
          sel.appendChild(op);
        });
      } catch (e) {
        console.error("cargarTipoMonedas:", e);
      }
    }

    async function cargarUnidadMedida() {
      const sel = $(SEL.selectUM);
      if (!sel) return;
      try {
        const r = await fetch(`${API.PRODUCTOS}?action=listarUnidadMedidas`, {
          cache: "no-store",
        });
        const data = await r.json();
        if (!data?.success) return;
        sel.innerHTML =
          '<option value="" disabled selected>Seleccione…</option>';
        (data.data || []).forEach((u) => {
          const op = document.createElement("option");
          op.value = u.cod_uni_med_sunat;
          op.textContent = `${u.nombre} - ${u.simbolo}`;
          sel.appendChild(op);
        });
      } catch (e) {
        console.error("cargarUnidadMedida:", e);
      }
    }

    /* ---------- Descripción automática ---------- */
    function actualizarDescripcion() {
      const categoria = getVal(SEL.inputCategoria).toUpperCase();
      const marcaVeh = getVal(SEL.inputMarcaVehiculo).toUpperCase();
      const modelo = getVal(SEL.inputModeloVehiculo).toUpperCase();
      const cil = getVal(SEL.inputCilindrada).toUpperCase();
      const motor = getVal(SEL.inputMotor).toUpperCase();
      const anioIni = getVal(SEL.inputAnioIni);
      const anioFin = getVal(SEL.inputAnioFin);
      const marcaProd = getVal(SEL.inputMarcaProducto).toUpperCase();
      const origen = getVal(SEL.inputOrigenProducto).toUpperCase();
      const oem = getVal(SEL.inputOEM).toUpperCase();
      const descVar = getVal(SEL.inputDescripcionVar).toUpperCase();

      const partes = [];
      if (categoria) partes.push(categoria);
      if (marcaVeh) partes.push(marcaVeh);
      if (modelo) partes.push(modelo);
      if (cil) partes.push(`C.C. ${cil}`);
      if (motor) partes.push(`MOTOR: ${motor}`);
      if (anioIni || anioFin)
        partes.push(
          `AÑOS: ${anioIni}${anioIni && anioFin ? " - " : ""}${anioFin}`
        );
      if (marcaProd) partes.push(marcaProd);
      if (origen) partes.push(origen);
      if (oem) partes.push(`OEM: ${oem}`);
      if (descVar) partes.push(`— ${descVar}`);

      const full = partes.join(" ").replace(/\s+/g, " ").trim();
      setVal(SEL.textDescripcionCompleta, full);
    }

    /* ---------- Uppercase en tiempo real ---------- */
    (function wireUppercase() {
      [
        SEL.inputCategoria,
        SEL.inputMarcaVehiculo,
        SEL.inputModeloVehiculo,
        SEL.inputCilindrada,
        SEL.inputMotor,
        SEL.inputCodProveedor,
        SEL.inputMarcaProducto,
        SEL.inputOrigenProducto,
        SEL.inputDescripcionVar,
        '[name="Observaciones"]',
      ].forEach((s) => {
        const el = $(s);
        el &&
          el.addEventListener("input", () => {
            el.value = (el.value || "").toUpperCase();
          });
      });
    })();

    /* ---------- SKU ---------- */
    async function generarSKU() {
      const idCat = getVal(SEL.hiddenCategoriaId);
      const nomCat = getVal(SEL.inputCategoria);
      const marcaVehiculo = getVal(SEL.inputMarcaVehiculo);
      const marcaProducto = getVal(SEL.inputMarcaProducto);
      const nombreMarca = marcaVehiculo || marcaProducto;

      if (!idCat || !nomCat || !nombreMarca) return;

      const params = new URLSearchParams({
        id_categoria: idCat,
        nombre_categoria: nomCat,
        nombre_marca: nombreMarca,
      });

      try {
        const r = await fetch(
          `${API.PRODUCTOS}?action=generarSKU&${params.toString()}`,
          { cache: "no-store" }
        );
        const json = await r.json();
        if (json?.success && json?.sku) {
          setVal(SEL.inputSKU, json.sku);
        } else {
          console.warn("No se pudo generar SKU:", json?.message);
        }
      } catch (e) {
        console.error("generarSKU:", e);
      }
    }

    // Disparadores SKU + Descripción
    (function wireSKUyDescripcion() {
      [
        SEL.inputMarcaVehiculo,
        SEL.inputMarcaProducto,
        SEL.inputCategoria,
      ].forEach((s) => {
        const el = $(s);
        el && el.addEventListener("blur", generarSKU);
      });

      [
        SEL.inputCategoria,
        SEL.inputMarcaVehiculo,
        SEL.inputModeloVehiculo,
        SEL.inputCilindrada,
        SEL.inputMotor,
        SEL.inputAnioIni,
        SEL.inputAnioFin,
        SEL.inputMarcaProducto,
        SEL.inputOrigenProducto,
        SEL.inputOEM,
        SEL.inputDescripcionVar,
      ].forEach((s) => {
        const el = $(s);
        if (!el) return;
        el.addEventListener("input", actualizarDescripcion);
        el.addEventListener("blur", actualizarDescripcion);
      });
    })();

    /* ---------- Nueva Categoría (inline) ---------- */
    (function wireNuevaCategoria() {
      const $btnAdd = $(SEL.btnAgregarCategoria);
      const $wrap = $(SEL.contNuevaCategoria);
      const $btnSave = $(SEL.btnGuardarCategoria);

      on($btnAdd, "click", () => {
        if (!$wrap) return;
        if ($wrap.classList.contains("d-none")) {
          $wrap.classList.remove("d-none");
          inicializarTypeaheadClasificacion();
          $(SEL.inputClasificacionGeneral)?.focus();
        } else {
          $wrap.classList.add("d-none");
        }
      });

      on($btnSave, "click", async () => {
        const clasificacion = getVal(SEL.inputClasificacionGeneral);
        const nombre = getVal(SEL.inputNombreEspecifico);
        if (!clasificacion || !nombre) {
          alert("Debes ingresar Clasificación General y Nombre Específico.");
          return;
        }
        try {
          const fd = new FormData();
          fd.append("clasificacion_general", clasificacion);
          fd.append("nombre_especifico", nombre);

          const r = await fetch(`${API.PRODUCTOS}?action=crearCategoria`, {
            method: "POST",
            body: fd,
          });
          const data = await r.json();
          if (!data?.success) {
            alert(data?.message || "Error al crear categoría.");
            return;
          }

          const idNew = data.data?.id_categoria;
          const nombreNew =
            data.data?.nombre_completo || `${clasificacion} - ${nombre}`;

          setVal(SEL.inputCategoria, nombreNew);
          setVal(SEL.hiddenCategoriaId, String(idNew || ""));

          setVal(SEL.inputClasificacionGeneral, "");
          setVal(SEL.inputNombreEspecifico, "");
          $wrap?.classList.add("d-none");

          inicializarTypeaheadCategorias();
          generarSKU();
          actualizarDescripcion();
        } catch (e) {
          console.error("Guardar categoría:", e);
          alert("Error inesperado.");
        }
      });
    })();

    /* ---------- VALIDACIÓN (corporativa, mínima intrusiva) ---------- */
    function clearValidation(form) {
      form
        .querySelectorAll(".is-invalid")
        .forEach((n) => n.classList.remove("is-invalid"));
      form.querySelectorAll(".invalid-hint").forEach((n) => n.remove());
    }

    function markInvalid(el, msg) {
      if (!el) return;
      el.classList.add("is-invalid");
      const hint = document.createElement("div");
      hint.className = "invalid-hint text-danger mt-1 small";
      hint.textContent = msg;
      (
        el.closest(".col-md-6, .col-md-3, .col-md-4, .col-12, .form-group") ||
        el.parentElement
      )?.appendChild(hint);
    }

    function validateForm() {
      const form = $(SEL.form);
      if (!form) return false;
      clearValidation(form);

      const reqs = [
        {
          el: $(SEL.inputCategoria),
          val: getHiddenCategoriaFirst(),
          msg: "Categoría es requerida",
        },
        {
          el: $(SEL.inputMarcaVehiculo),
          val: getVal(SEL.inputMarcaVehiculo),
          msg: "Marca de vehículo es requerida",
        },
        {
          el: $(SEL.inputCodProveedor),
          val: getVal(SEL.inputCodProveedor),
          msg: "Código del proveedor es requerido",
        },
        {
          el: $(SEL.inputMarcaProducto),
          val: getVal(SEL.inputMarcaProducto),
          msg: "Marca del producto es requerida",
        },
        {
          el: $(SEL.inputOrigenProducto),
          val: getVal(SEL.inputOrigenProducto),
          msg: "Origen del producto es requerido",
        },
        {
          el: $(SEL.inputSKU),
          val: getVal(SEL.inputSKU),
          msg: "SKU no generado",
        },
        {
          el: $(SEL.selectUM),
          val: getVal(SEL.selectUM),
          msg: "Unidad de medida es requerida",
        },
      ];

      // 👉 Divisa será obligatoria SOLO si estás registrando compra o venta
      const pv =
        parseFloat(
          getVal('[name="PrecioUnitario_Venta"]') ||
            getVal('[name="PrecioUnitario"]') ||
            "0"
        ) || 0;
      const pc =
        parseFloat(getVal('[name="PrecioUnitario_Compra"]') || "0") || 0;
      if ((pv > 0 || pc > 0) && !getVal(SEL.selectDivisa)) {
        reqs.push({
          el: $(SEL.selectDivisa),
          val: "",
          msg: "Divisa es requerida cuando ingresas precios",
        });
      }

      let ok = true;
      let first = null;
      reqs.forEach(({ el, val, msg }) => {
        if (!String(val || "").trim()) {
          ok = false;
          markInvalid(el, msg);
          if (!first) first = el;
        }
      });

      if (!ok && first) {
        first.focus({ preventScroll: false });
        first.scrollIntoView({ behavior: "smooth", block: "center" });
      }
      return ok;
    }

    /* ---------- SUBMIT (guardarProducto) ---------- */
    async function guardarProducto() {
      const form = document.querySelector("#formAgregarProducto");
      if (!form) return;
      if (!validateForm()) return;

      const btnSubmit = form.querySelector('button[type="submit"]');
      const oldHTML = btnSubmit?.innerHTML;
      if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML =
          '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Guardando…';
      }

      // FormData + normalizaciones
      const fd = new FormData(form);
      const chkIcbper = form.querySelector('[name="icbper"]');
      fd.set("icbper", chkIcbper && chkIcbper.checked ? "1" : "0");
      // === PARCHES: forzar campos base en el FormData (normalizados) ===
      // MarcaVehiculo NO tiene name en el HTML, así que la inyectamos desde su input typeahead
      fd.set(
        "MarcaVehiculo",
        document.querySelector("#inputMarcaVehiculo")?.value?.trim() || ""
      );

      // Los demás ya tienen name en el form, pero los reescribo normalizados para asegurar envío + trim
      fd.set(
        "OEM_productos",
        (document.querySelector('[name="OEM_productos"]')?.value || "").trim()
      );
      fd.set(
        "ModeloVehiculo",
        (document.querySelector('[name="ModeloVehiculo"]')?.value || "").trim()
      );
      fd.set(
        "CilindradaVehiculo",
        (
          document.querySelector('[name="CilindradaVehiculo"]')?.value || ""
        ).trim()
      );
      fd.set(
        "MotorVehiculo",
        (document.querySelector('[name="MotorVehiculo"]')?.value || "").trim()
      );
      fd.set(
        "AñoInicialVehiculo",
        (
          document.querySelector('[name="AñoInicialVehiculo"]')?.value || ""
        ).trim()
      );
      fd.set(
        "AñoFinVehiculo",
        (document.querySelector('[name="AñoFinVehiculo"]')?.value || "").trim()
      );

      // (Opcional) también normaliza estos, por si acaso
      fd.set(
        "CodigoReferencia_proveedor",
        (
          document.querySelector('[name="CodigoReferencia_proveedor"]')
            ?.value || ""
        ).trim()
      );
      fd.set(
        "MarcaProductos",
        (document.querySelector('[name="MarcaProductos"]')?.value || "").trim()
      );
      fd.set(
        "OrigenProductos",
        (document.querySelector('[name="OrigenProductos"]')?.value || "").trim()
      );
      fd.set(
        "descripcion_variante",
        (
          document.querySelector('[name="descripcion_variante"]')?.value || ""
        ).trim()
      );
      fd.set(
        "DescripcionCompleta",
        (
          document.querySelector('textarea[name="DescripcionCompleta"]')
            ?.value || ""
        ).trim()
      );
      // Backend espera 'unidadMedida' (lo mapeamos desde unidad_medida si existe)
      if (fd.has("unidad_medida"))
        fd.set("unidadMedida", fd.get("unidad_medida") || "NIU");
      // Tipo IGV por defecto (gravado onerosa 10)
      fd.set("TipoIGV", "10");

      // === Precios: si NO hay precio de compra, NO enviamos venta ni divisa ===
      // === Precios: permitir solo precio de venta (divisa + venta), compra opcional ===
      const pv =
        parseFloat(
          fd.get("PrecioUnitario_Venta") || fd.get("PrecioUnitario") || "0"
        ) || 0;
      if (pv > 0) {
        // Normaliza SIEMPRE a 3 decimales
        fd.set("PrecioUnitario_Venta", pv.toFixed(3));
        fd.delete("PrecioUnitario");

        // Si hay venta, exigir divisa (aunque ya lo validas en validateForm)
        const divisa = fd.get("TipoDivisa") || "";
        if (!divisa) {
          alert("Debes seleccionar una divisa para el precio de venta.");
          if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = oldHTML;
          }
          return;
        }
      }
      // NO tocar TipoDivisa: se mantiene aunque no haya compra

      // === PUNTO 4: Renombrar archivo de imagen a SKU.ext antes de enviar ===
      const skuActual = (
        document.querySelector('input[name="SKU_Productos"]')?.value || ""
      ).trim();
      const inputFile = document.querySelector('input[name="ImagenProducto"]');
      const file = inputFile?.files?.[0];

      if (file && skuActual) {
        const mime = file.type || "";
        let ext = "";
        if (mime === "image/jpeg") ext = "jpg";
        else if (mime === "image/png") ext = "png";
        else if (mime === "image/webp") ext = "webp";
        else {
          const nameExt = (file.name || "").split(".").pop();
          ext = nameExt ? nameExt.toLowerCase() : "jpg";
        }
        const renamed = new File([file], `${skuActual}.${ext}`, {
          type: file.type,
        });

        // Reemplazar en FormData con el nuevo nombre
        fd.delete("ImagenProducto");
        fd.append("ImagenProducto", renamed, renamed.name);
        // (opcional por si tu backend lo utiliza)
        fd.set("ImagenProductoNombre", renamed.name);
      }

      try {
        const r = await fetch(
          `assets/ajax/productos_ajax.php?action=guardarProducto`,
          {
            method: "POST",
            body: fd,
          }
        );
        const json = await r.json();
        if (!json?.success)
          throw new Error(json?.message || "No se pudo guardar el producto.");

        // Reset visual
        form.reset();
        // Limpiar campos clave (por si hay duplicados del hidden en el DOM)
        document
          .querySelectorAll('[name="id_categoriaProductos"]')
          .forEach((el) => (el.value = ""));
        const vacios = [
          "#inputCategoria",
          "#inputMarcaVehiculo",
          '[name="ModeloVehiculo"]',
          '[name="CilindradaVehiculo"]',
          '[name="MotorVehiculo"]',
          '[name="AñoInicialVehiculo"]',
          '[name="AñoFinVehiculo"]',
          'input[name="SKU_Productos"]',
          '[name="CodigoReferencia_proveedor"]',
          '[name="MarcaProductos"]',
          '[name="OrigenProductos"]',
          '[name="descripcion_variante"]',
          'textarea[name="DescripcionCompleta"]',
        ];
        vacios.forEach((s) => {
          const el = document.querySelector(s);
          if (el) el.value = "";
        });
        const selDivisa = document.querySelector('[name="TipoDivisa"]');
        if (selDivisa) selDivisa.value = "";
        const selUM = document.querySelector('[name="unidad_medida"]');
        if (selUM) selUM.value = "";
        const wrapNewCat = document.querySelector("#nuevoCategoriaContainer");
        if (wrapNewCat) wrapNewCat.classList.add("d-none");
        // Imagen: desactivar preview
        const inputImg = document.querySelector('input[name="ImagenProducto"]');
        if (inputImg) inputImg.value = "";
        const btnPrev = document.querySelector("#btnVistaPrevia");
        if (btnPrev) btnPrev.disabled = true;

        // Cerrar modal
        const modalEl = document.querySelector("#modalAgregarProducto");
        const bsModal =
          bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        bsModal.hide();

        // Disparar evento global (por si refrescas lista)
        document.dispatchEvent(
          new CustomEvent("producto:creado", { detail: json.data })
        );

        // === PUNTO 5: SweetAlert de éxito (fallback a alert) ===
        if (window.Swal && Swal.fire) {
          await Swal.fire({
            icon: "success",
            title: "¡Registrado!",
            text: "El producto se guardó correctamente.",
            confirmButtonText: "Aceptar",
          });
        } else {
          alert("El producto se guardó correctamente.");
        }
      } catch (e) {
        console.error(e);
        alert(e.message || "Error al guardar.");
      } finally {
        if (btnSubmit) {
          btnSubmit.disabled = false;
          btnSubmit.innerHTML = oldHTML;
        }
      }
    }

    // Wire submit
    (function wireFormSubmit() {
      const form = $(SEL.form);
      if (!form) return;
      form.addEventListener("submit", (e) => {
        e.preventDefault();
        guardarProducto();
      });
    })();

    // Al abrir el modal, cargar catálogos y typeaheads
    (function wireOnModalShow() {
      const modalEl = $(SEL.modal);
      if (!modalEl) return;
      modalEl.addEventListener("shown.bs.modal", () => {
        inicializarTypeaheadCategorias();
        inicializarMarcaVehiculos();
        cargarTipoMonedas();
        cargarUnidadMedida();
        actualizarDescripcion();
      });
    })();

    // Exponer para tu función existente abrirModalAgregar()
    window.inicializarTypeaheadCategorias = inicializarTypeaheadCategorias;
    window.inicializarMarcaVehiculos = inicializarMarcaVehiculos;
    window.cargarTipoMonedas = cargarTipoMonedas;
    window.cargarUnidadMedida = cargarUnidadMedida;
    window.inicializarTypeaheadClasificacion =
      inicializarTypeaheadClasificacion;
  })();

  // === Zoom de imagen (PhotoSwipe) ===
  (function () {
    function openZoomForImage(imgEl) {
      if (!imgEl) return;
      const src = imgEl.currentSrc || imgEl.src || "";
      // Si es placeholder SVG, evita abrir
      if (!src || src.startsWith("data:image/svg+xml")) return;

      // Dimensiones reales (si ya cargó)
      const w = imgEl.naturalWidth || 1200;
      const h = imgEl.naturalHeight || 1200;

      const items = [
        {
          src,
          w,
          h,
          title: imgEl.alt || "Imagen del producto",
        },
      ];

      const pswpEl = document.querySelector(".pswp");
      if (
        !pswpEl ||
        typeof PhotoSwipe === "undefined" ||
        typeof PhotoSwipeUI_Default === "undefined"
      ) {
        // Fallback: abrir en nueva pestaña si PhotoSwipe no está
        window.open(src, "_blank");
        return;
      }

      const options = {
        index: 0,
        bgOpacity: 0.9,
        showHideOpacity: true,
        history: false,
      };

      const gallery = new PhotoSwipe(
        pswpEl,
        PhotoSwipeUI_Default,
        items,
        options
      );
      gallery.init();
    }

    // Delegación para click en la imagen del modal detalle
    document.body.addEventListener("click", function (e) {
      const img =
        e.target && e.target.closest && e.target.closest("#detImagen");
      if (img) {
        e.preventDefault();
        openZoomForImage(img);
      }
    });

    // (Opcional) cursor de mano si tienes utilidades de Bootstrap >=5.3
    // const detImg = document.getElementById("detImagen");
    // if (detImg) detImg.classList.add("cursor-pointer");
  })();
});
