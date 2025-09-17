/* ========================= CARGA INICIAL: SEDES + TIPOS DE DOCUMENTO ========================= */
document.addEventListener("DOMContentLoaded", () => {
  const selectSede = document.querySelector('select[name="id_sede"]');
  const selectDocumentosVenta = document.querySelector(
    'select[name="tipoDocumento_compra"]'
  );

  const cargarSedes = () => {
    const URL = "assets/ajax/sede_ajax.php?action=listarSedes";
    fetch(URL, { cache: "no-store" })
      .then((r) => {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then((json) => {
        const sedeSesion = String(window.__SEDE_ACTUAL_ID || "");
        const lista = Array.isArray(json?.sedes)
          ? json.sedes
          : Array.isArray(json?.data)
          ? json.data
          : Array.isArray(json)
          ? json
          : [];
        selectSede.innerHTML = '<option value="">Seleccione...</option>';
        lista.forEach((s) => {
          const id =
            s.id_sede ?? s.idSede ?? s.sede_id ?? s.id ?? s.codigo_sede;
          const nombre =
            s.nombre_sede ??
            s.nombreSede ??
            s.nombre ??
            s.descripcion ??
            s.alias ??
            `Sede ${id ?? ""}`.trim();
          if (id != null) {
            const opt = document.createElement("option");
            opt.value = id;
            opt.textContent = nombre;
            selectSede.appendChild(opt);
          }
        });
        if (sedeSesion) selectSede.value = sedeSesion;
      })
      .catch((err) => {
        console.error("Error cargando sedes:", err);
        selectSede.innerHTML =
          '<option value="">Error al cargar sedes</option>';
      });
  };

  const cargarDocumentosVenta = () => {
    const URL = "assets/ajax/tipos_documentos_ajax.php?action=listar";
    fetch(URL, { cache: "no-store" })
      .then((r) => {
        if (!r.ok) throw new Error("HTTP " + r.status);
        return r.json();
      })
      .then((json) => {
        const lista = Array.isArray(json?.documentosVenta)
          ? json.documentosVenta
          : Array.isArray(json?.data)
          ? json.data
          : Array.isArray(json)
          ? json
          : [];
        selectDocumentosVenta.innerHTML =
          '<option value="">Seleccione...</option>';
        lista.forEach((s) => {
          const id = s.CodigoSunat_TipoDocumentoVenta ?? s.id ?? s.codigo ?? "";
          const nombre =
            s.Descripcion ?? s.descripcion ?? s.nombre ?? `Doc ${id}`;
          if (id !== "") {
            const opt = document.createElement("option");
            opt.value = id;
            opt.textContent = nombre;
            selectDocumentosVenta.appendChild(opt);
          }
        });
      })
      .catch((err) => {
        console.error("Error cargando Documentos:", err);
        selectDocumentosVenta.innerHTML =
          '<option value="">Error al cargar documentos</option>';
      });
  };

  cargarDocumentosVenta();
  cargarSedes();
});

/* ========================= Fix de viewport/altura modales ========================= */
(function () {
  const setVH = () => {
    const vh = window.innerHeight * 0.01;
    document.documentElement.style.setProperty("--vh", `${vh}px`);
  };
  setVH();
  window.addEventListener("resize", setVH);

  const tuneModal = (modal) => {
    if (!modal) return;
    const hdr = modal.querySelector(".modal-header");
    const ftr = modal.querySelector(".modal-footer");
    const h = hdr ? hdr.offsetHeight : 56;
    const f = ftr ? ftr.offsetHeight : 64;
    modal.style.setProperty("--modal-header-h", `${h}px`);
    modal.style.setProperty("--modal-footer-h", `${f}px`);
    modal.setAttribute("data-vh-ready", "1");
  };

  const applyWhenShown = (id) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener("shown.bs.modal", () => {
      tuneModal(el);
      const onResize = () => tuneModal(el);
      window.addEventListener("resize", onResize, { passive: true });
      const onceHide = () => {
        window.removeEventListener("resize", onResize);
        el.removeEventListener("hide.bs.modal", onceHide);
      };
      el.addEventListener("hide.bs.modal", onceHide);
    });
  };

  applyWhenShown("modalNuevoProducto");
  applyWhenShown("modalBuscarProducto");
})();

/* ========================= Carrito Detalle de Compra (memoria + render + totales) ========================= */
(() => {
  const TASA_IGV = 0.18; // 18%
  const $tbody = document.getElementById("detalleCompraBody");
  const $subtotal = document.getElementById("subtotal_compra");
  const $igv = document.getElementById("igv_compra");
  const $total = document.getElementById("total_compra");
  const $info = document.getElementById("detalleCompraInfo");
  const $btnPrev = document.getElementById("detallePrev");
  const $btnNext = document.getElementById("detalleNext");

  let page = 1;
  const pageSize = 10;

  const totalPages = () => Math.max(1, Math.ceil(cart.length / pageSize));
  const clampPage = () => (page = Math.min(Math.max(1, page), totalPages()));
  const firstIndex = () => (page - 1) * pageSize;
  const lastIndex = () => Math.min(page * pageSize, cart.length);

  function refreshPaginator() {
    clampPage();
    if ($info)
      $info.textContent = cart.length
        ? `Mostrando ${firstIndex() + 1}-${lastIndex()} de ${cart.length}`
        : `Sin items`;
    if ($btnPrev) $btnPrev.disabled = page <= 1;
    if ($btnNext) $btnNext.disabled = page >= totalPages();
  }

  $tbody.addEventListener(
    "blur",
    (e) => {
      if (e.target.classList.contains("input-cant")) {
        e.target.value = Number(e.target.value || 0).toFixed(2);
      }
      if (e.target.classList.contains("input-precio")) {
        e.target.value = Number(e.target.value || 0).toFixed(3);
      }
    },
    true
  );

  $btnPrev?.addEventListener("click", () => {
    page = Math.max(1, page - 1);
    render();
  });
  $btnNext?.addEventListener("click", () => {
    page = Math.min(totalPages(), page + 1);
    render();
  });
  // estado del carrito en memoria
  const cart = []; // items: {id, sku, nombre, cantidad, precioCompra}

  function fmt2(n) {
    return Number(n).toFixed(2);
  }
  function fmt3(n) {
    return Number(n).toFixed(3);
  }

  const formatMoney = (n, decimals = 2) =>
    (Number(n) || 0).toLocaleString("en-US", {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals,
    });

  const $selMoneda = document.querySelector('select[name="tipo_moneda"]');
  const getMonedaSimbolo = () => {
    const v = ($selMoneda?.value || "PEN").toUpperCase();
    return v === "USD" ? "$" : "S/";
  };

  const emitCartChanged = () => {
    document.dispatchEvent(
      new CustomEvent("compra:cartChanged", { detail: { count: cart.length } })
    );
  };

  function recalcTotals() {
    // total con IGV incluido (suma líneas)
    const total = cart.reduce(
      (acc, it) => acc + Number(it.cantidad) * Number(it.precioCompra),
      0
    );
    // base imponible (subtotal) e IGV cuando los precios YA incluyen IGV
    const base = total / (1 + TASA_IGV);
    const igv = total - base;

    $subtotal.textContent = formatMoney(base, 2);
    $igv.textContent = formatMoney(igv, 2);
    $total.textContent = formatMoney(total, 2);

    const sym = getMonedaSimbolo();
    $subtotal.textContent = sym + " " + formatMoney(base, 2);
    $igv.textContent = sym + " " + formatMoney(igv, 2);
    $total.textContent = sym + " " + formatMoney(total, 2);
  }

  $selMoneda?.addEventListener("change", () => {
    // reimprime totales y filas con el símbolo actualizado
    // (no toques cantidades ni precios)
    // Solo vuelve a pintar subtotales visibles + totales
    document.querySelectorAll("#detalleCompraBody tr").forEach((tr) => {
      const idx = Number(tr.dataset.index || -1);
      if (idx >= 0) {
        const $sub = tr.querySelector(".celda-subtotal");
        const cant = Number(cart[idx].cantidad) || 0;
        const prec = Number(cart[idx].precioCompra) || 0;
        if ($sub)
          $sub.textContent = `${getMonedaSimbolo()} ${fmt2(cant * prec)}`;
      }
    });
    recalcTotals();
  });

  function render() {
    if (!cart.length) {
      $tbody.innerHTML = `
      <tr class="text-center">
        <td colspan="6" class="empty-state">
          <i class="fa fa-inbox"></i> Sin productos añadidos. Use <strong>Agregar desde catálogo</strong>.
        </td>
      </tr>`;
      recalcTotals();
      refreshPaginator(); // 👈 actualizado
      emitCartChanged();
      return;
    }
    clampPage();
    const start = firstIndex();
    const end = lastIndex();
    const slice = cart.slice(start, end); // solo la página actual
    const sym = getMonedaSimbolo();
    $tbody.innerHTML = slice
      .map((it, i) => {
        const absIdx = start + i; // índice real en cart
        const subtotalLinea = Number(it.cantidad) * Number(it.precioCompra);
        return `
      <tr data-index="${absIdx}">
        <td class="text-center">
          <button class="btn btn-sm btn-outline-danger btn-pill btn-del"><i class="fa fa-trash"></i></button>
        </td>
        <td>
          <div class="product-mini fw-semibold">${it.nombre}</div>
          <small class="text-muted">SKU: ${it.sku || "-"}</small>
        </td>
        <td class="text-end" style="max-width:160px;">
          <input type="number" min="0.01" step="0.01" class="form-control form-control-sm text-end input-cant btn-pill" value="${fmt2(
            it.cantidad
          )}">
        </td>
        <td class="text-end" style="max-width:180px;">
          <input type="number" min="0" step="0.001" class="form-control form-control-sm text-end input-precio btn-pill" value="${fmt3(
            it.precioCompra
          )}">
        </td>
        <td class="text-end fw-bold celda-subtotal">${sym} ${fmt2(
          subtotalLinea
        )}</td>
      </tr>`;
      })
      .join("");

    recalcTotals();
    refreshPaginator(); // 👈 actualizado
    emitCartChanged();
  }

  function addItem({ id, sku, nombre, cantidad, precioCompra }) {
    // si ya existe, suma cantidad; sobreescribe precio si viene informado
    const found = cart.find((x) => String(x.id) === String(id));
    if (found) {
      found.cantidad = Number(found.cantidad) + Number(cantidad || 0);
      if (precioCompra != null && precioCompra !== "")
        found.precioCompra = Number(precioCompra);
    } else {
      cart.push({
        id,
        sku: sku || "",
        nombre: nombre || "",
        cantidad: Number(cantidad || 1),
        precioCompra: Number(precioCompra || 0),
      });
    }
    page = totalPages(); // salta a la última página
    render();
  }

  // Delegación: borrar / editar cantidad / editar precio
  $tbody.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-del");
    if (!btn) return;
    const tr = btn.closest("tr");
    const idx = Number(tr?.dataset.index || -1);
    if (idx >= 0) {
      cart.splice(idx, 1);
      render();
    }
  });

  $tbody.addEventListener("input", (e) => {
    const tr = e.target.closest("tr");
    if (!tr) return;
    const idx = Number(tr.dataset.index || -1);
    if (idx < 0) return;

    // Sólo permitir números, punto, coma:
    if (
      e.target.classList.contains("input-cant") ||
      e.target.classList.contains("input-precio")
    ) {
      e.target.value = e.target.value.replace(/[^0-9.,]/g, "");
    }

    const toNum = (v) => {
      const n = Number(String(v).replace(/,/g, "."));
      return isFinite(n) ? n : 0;
    };

    if (e.target.classList.contains("input-cant")) {
      cart[idx].cantidad = Math.max(0, toNum(e.target.value || 0));
    } else if (e.target.classList.contains("input-precio")) {
      cart[idx].precioCompra = Math.max(0, toNum(e.target.value || 0));
    }

    // Actualiza SOLO el subtotal de la fila + totales (sin repintar todo)
    const $sub = tr.querySelector(".celda-subtotal");
    if ($sub) {
      const sym = getMonedaSimbolo();
      const subtotalLinea =
        Number(cart[idx].cantidad) * Number(cart[idx].precioCompra) || 0;
      $sub.textContent = `${sym} ${fmt2(subtotalLinea)}`;
    }
    recalcTotals();
  });

  $tbody.addEventListener("keydown", (e) => {
    const isNumInput =
      e.target.classList.contains("input-cant") ||
      e.target.classList.contains("input-precio");
    if (!isNumInput) return;

    const allowedKeys = [
      "Backspace",
      "Delete",
      "Tab",
      "ArrowLeft",
      "ArrowRight",
      "ArrowUp",
      "ArrowDown",
      "Home",
      "End",
      "Enter",
    ];
    const isCtrlCmd = e.ctrlKey || e.metaKey;

    // Permitir: ctrl/cmd combos para copiar/pegar/seleccionar todo
    if (isCtrlCmd) return;

    // Dígitos y separadores
    const isDigit = e.key >= "0" && e.key <= "9";
    const isSep = e.key === "." || e.key === ",";

    if (isDigit || isSep || allowedKeys.includes(e.key)) return;

    // Bloquear cualquier otra cosa (letras, etc.)
    e.preventDefault();
  });

  // Exponer API global mínima
  // Al final del bloque del carrito:
  window.DetalleCompra = {
    addItem,
    getItems: () =>
      cart.map((x) => ({
        id: x.id,
        sku: x.sku,
        nombre: x.nombre,
        cantidad: Number(x.cantidad || 0),
        precioCompra: Number(x.precioCompra || 0),
        subtotal: Number(x.cantidad || 0) * Number(x.precioCompra || 0),
      })),
    _debug: { cart, render, recalcTotals },
  };
})();

/* ========================= Typeahead Proveedores ========================= */
(() => {
  const $input = document.getElementById("proveedor_search");
  const $hidden = document.getElementById("id_proveedor");
  const $menu = document.getElementById("prov_suggestions");
  if (!$input || !$hidden || !$menu) return;

  const ENDPOINT = "assets/ajax/Proveedor_ajax.php";
  const ACTION_SEARCH = "buscarProveedores";
  const ACTION_LIST = "listarProveedores";

  const debounce = (fn, ms = 250) => {
    let t;
    return (...a) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...a), ms);
    };
  };
  const normalizeLista = (json) => {
    const lista = Array.isArray(json?.data)
      ? json.data
      : Array.isArray(json?.proveedores)
      ? json.proveedores
      : Array.isArray(json)
      ? json
      : [];
    return lista
      .map((p) => ({
        id: p.id_proveedor ?? p.id ?? p.proveedor_id,
        rs: p.razon_social ?? p.nombre ?? p.nombre_comercial ?? "",
        nd: p.numero_documento ?? p.doc ?? "",
        rep: p.representante ?? "",
      }))
      .filter((x) => x.id != null && (x.rs || x.nd));
  };

  const fetchServer = async (q) => {
    const r = await fetch(
      `${ENDPOINT}?action=${ACTION_SEARCH}&q=${encodeURIComponent(q)}`,
      { cache: "no-store" }
    );
    if (!r.ok) throw new Error("HTTP " + r.status);
    return normalizeLista(await r.json());
  };
  const fetchAllFallback = async () => {
    const r = await fetch(`${ENDPOINT}?action=${ACTION_LIST}`, {
      cache: "no-store",
    });
    if (!r.ok) throw new Error("HTTP " + r.status);
    return normalizeLista(await r.json());
  };
  const filterClient = (arr, q) => {
    const t = q.trim().toLowerCase();
    if (!t) return [];
    return arr.filter(
      (x) =>
        (x.rs && x.rs.toLowerCase().includes(t)) ||
        (x.nd && x.nd.toLowerCase().includes(t))
    );
  };

  let items = [];
  let activeIndex = -1;

  const renderMenu = (arr) => {
    items = arr;
    activeIndex = -1;
    if (!arr.length) {
      $menu.style.display = "none";
      $menu.innerHTML = "";
      return;
    }
    $menu.innerHTML = arr
      .map(
        (x, i) => `
      <button type="button" class="list-group-item list-group-item-action" data-index="${i}">
        <div class="fw-semibold text-truncate">${x.rs || "Sin razón social"}${
          x.nd ? ` (${x.nd})` : ""
        }</div>
        ${x.rep ? `<small class="text-muted d-block">${x.rep}</small>` : ""}
      </button>`
      )
      .join("");
    $menu.style.display = "block";
  };

  const pick = (i) => {
    const it = items[i];
    if (!it) return;
    $hidden.value = it.id;
    $input.value = `${it.rs || "Sin razón social"}${
      it.nd ? ` (${it.nd})` : ""
    }`;
    $menu.style.display = "none";
    $menu.innerHTML = "";
  };
  const moveActive = (dir) => {
    if (!items.length) return;
    activeIndex += dir;
    if (activeIndex < 0) activeIndex = items.length - 1;
    if (activeIndex >= items.length) activeIndex = 0;
    [...$menu.querySelectorAll(".list-group-item")].forEach((el, i) =>
      el.classList.toggle("active", i === activeIndex)
    );
  };

  const search = async (q) => {
    if (q.trim().length < 2) {
      renderMenu([]);
      $hidden.value = "";
      return;
    }
    try {
      let arr = await fetchServer(q);
      if (!arr.length) {
        const all = await fetchAllFallback();
        arr = filterClient(all, q);
      }
      renderMenu(arr.slice(0, 20));
    } catch (e) {
      console.error("Typeahead proveedores error:", e);
      renderMenu([]);
    }
  };

  $input.addEventListener(
    "input",
    debounce((e) => {
      $hidden.value = "";
      search(e.target.value);
    }, 250)
  );
  $input.addEventListener("keydown", (e) => {
    if ($menu.style.display === "block") {
      if (e.key === "ArrowDown") {
        e.preventDefault();
        moveActive(1);
      } else if (e.key === "ArrowUp") {
        e.preventDefault();
        moveActive(-1);
      } else if (e.key === "Enter" && activeIndex >= 0) {
        e.preventDefault();
        pick(activeIndex);
      } else if (e.key === "Escape") {
        $menu.style.display = "none";
      }
    }
  });
  $menu.addEventListener("mousedown", (e) => {
    const btn = e.target.closest(".list-group-item");
    if (!btn) return;
    pick(Number(btn.dataset.index));
  });
  $input.addEventListener("blur", () =>
    setTimeout(() => {
      $menu.style.display = "none";
    }, 100)
  );

  (async () => {
    if (!$hidden.value) return;
    try {
      const r = await fetch(
        `${ENDPOINT}?action=obtenerProveedor&id=${encodeURIComponent(
          $hidden.value
        )}`,
        { cache: "no-store" }
      );
      if (!r.ok) return;
      const [it] = normalizeLista(await r.json());
      if (it)
        $input.value = `${it.rs || "Sin razón social"}${
          it.nd ? ` (${it.nd})` : ""
        }`;
    } catch {}
  })();
})();

/* ========================= BUSCADOR DE PRODUCTOS (Modal) ========================= */
(() => {
  const $modal = document.getElementById("modalBuscarProducto");
  if (!$modal) return;

  const $inputSearch = document.getElementById("buscadorProductos");
  const $selCategoria = document.getElementById("filtroCategoria");
  const $selMarca = document.getElementById("filtroMarca");
  const $resultados = document.getElementById("resultadosProductos");
  const $placeholder = document.getElementById("placeholderResultados");
  const $wrapCargarMas = document.getElementById("wrapCargarMas");
  const $btnCargarMas = document.getElementById("btnCargarMas");
  const $estadoBusqueda = document.getElementById("estadoBusqueda");
  const $sinResultados = document.getElementById("sinResultados");

  const $panelVacio = document.getElementById("panelVacio");
  const $formFichaRapida = document.getElementById("formFichaRapida");
  const $fr_id = document.getElementById("fr_id_producto_variante");
  const $fr_nombre = document.getElementById("fr_nombre");
  const $fr_sku = document.getElementById("fr_sku");
  const $fr_categoria = document.getElementById("fr_categoria");
  const $fr_marca = document.getElementById("fr_marca");
  const $fr_cantidad = document.getElementById("fr_cantidad");

  const API = {
    buscar: "assets/ajax/productos_ajax.php?action=buscarProductosCompras",
    filtros: "assets/ajax/productos_ajax.php?action=listarFiltros", // si no existe endpoint, mostramos selects vacíos
    actualizarPV:
      "assets/ajax/productos_ajax.php?action=actualizarPrecioVentaVariante",
  };

  const pageSize = 10;
  let state = {
    q: "",
    categoria: "",
    marca: "",
    page: 1,
    hasMore: false,
    isLoading: false,
    abortCtrl: null,
  };

  const debounce = (fn, ms = 300) => {
    let t;
    return (...a) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...a), ms);
    };
  };
  const setLoading = (f) => {
    state.isLoading = f;
    $estadoBusqueda?.classList.toggle("d-none", !f);
  };
  const resetResultadosUI = () => {
    $resultados.innerHTML = "";
    $placeholder.style.display = "block";
    $sinResultados.classList.add("d-none");
    $wrapCargarMas.classList.add("d-none");
  };

  const normalizeProductos = (json) => {
    const arr =
      (json?.data &&
        Array.isArray(json.data.productos) &&
        json.data.productos) ||
      [];
    const idSedeSesion = Number(window.__SEDE_ACTUAL_ID || 0);

    const toNum = (v) => Number(String(v ?? "0").replace(/[, ]/g, ""));

    const items = arr.map((p) => {
      const idSedeActual =
        p.id_sede_actual != null ? Number(p.id_sede_actual) : idSedeSesion;

      const sedes = Array.isArray(p.sedes)
        ? p.sedes.map((s) => ({
            id_sede: Number(s.id_sede),
            nombre_sede: String(s.nombre_sede || ""),
            stock_actual: toNum(s.stock_actual),
          }))
        : [];

      sedes.sort((a, b) =>
        a.id_sede === idSedeActual && b.id_sede !== idSedeActual
          ? -1
          : b.id_sede === idSedeActual && a.id_sede !== idSedeActual
          ? 1
          : a.nombre_sede.localeCompare(b.nombre_sede)
      );

      // soporta ambos contratos de API
      const precioVentaRaw = p.precio_venta ?? p.PrecioUnitario_Venta;
      const precioCompraRaw = p.precio_compra ?? p.PrecioUnitario_Compra;
      const utilidadRaw = p.utilidad_pct ?? p.Utilidad;
      const divisaRaw = p.moneda_venta ?? p.TipoDivisa;

      return {
        id:
          p.id_producto_variante ?? p.id_variante ?? p.id ?? p.id_producto_base,
        titulo: p.DescripcionCompleta || "(Sin título)",
        sub: p.descripcion_variante || "",
        sku: p.SKU_Productos || "",
        codProv: p.CodigoReferencia_proveedor || "",
        marca: p.MarcaProductos || "",
        oem: p.OEM_productos || "",
        precioVenta: toNum(precioVentaRaw),
        divisa: String(divisaRaw || "").toUpperCase(),
        utilidad:
          utilidadRaw != null && utilidadRaw !== ""
            ? Number(utilidadRaw)
            : null,
        precioCompra: toNum(precioCompraRaw),
        idSedeActual,
        sedes,
      };
    });

    const currentPage =
      json?.data?.current_page != null ? Number(json.data.current_page) : 1;
    const totalPages =
      json?.data?.total_pages != null ? Number(json.data.total_pages) : 1;

    return { items, currentPage, totalPages };
  };

  // --- reemplaza ESTA función ---
  const renderItem = (it) => {
    // ids únicos para colapso
    const cid = `prod_${it.id}`;

    // badges compactos (arriba del título)
    const topBadges = [
      it.sku
        ? `<span class="badge bg-warning btn-pill text-dark me-1">SKU: ${it.sku}</span>`
        : "",
      it.codProv
        ? `<span class="badge bg-info btn-pill text-dark">Ref. Prov: ${it.codProv}</span>`
        : "",
    ].join("");

    // chips de stock por sede (en detalle)
    const chipsSedes = (it.sedes || [])
      .map((s) => {
        const isActual = Number(s.id_sede) === Number(it.idSedeActual);
        return `<span class="badge ${
          isActual ? "bg-danger" : "bg-dark"
        } me-1 mb-1">${s.nombre_sede}: ${s.stock_actual.toFixed(2)}${
          isActual ? " (Actual)" : ""
        }</span>`;
      })
      .join("");

    return `
  <div class="card mb-2 shadow-sm border-0">
    <div class="card-header bg-light py-2">
      <div class="d-flex align-items-center">
        <!-- Botón para colapsar/expandir (flecha + texto truncado) -->
        <button class="btn btn-link text-decoration-none text-start flex-grow-1 overflow-hidden"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#${cid}"
                aria-expanded="false"
                aria-controls="${cid}">
          <div class="d-flex align-items-center gap-2">
            <div class="w-100">
              ${
                topBadges
                  ? `<div class="small text-muted mb-1">${topBadges}</div>`
                  : ""
              }
              <div class="fw-bold text-truncate" title="${it.titulo}">${
      it.titulo
    }</div>
              ${
                it.sub
                  ? `<div class="text-muted small text-truncate" title="${it.sub}">${it.sub}</div>`
                  : ""
              }
            </div>
          </div>
        </button>

        <button type="button"
        class="btn btn-primary btn-sm btn-pill d-inline-flex justify-content-center align-items-center lh-1 btn-seleccionar"
        title="Seleccionar este producto"
        data-id="${it.id}"
        data-sku="${it.sku || ""}"
        data-nombre="${it.titulo || ""}"
        data-marca="${it.marca || ""}"
        data-oem="${it.oem || ""}"
        data-precio-venta="${it.precioVenta || 0}"
        data-divisa="${(it.divisa || "").toUpperCase()}"
        data-utilidad="${it.utilidad ?? ""}"
        data-precio-compra="${it.precioCompra || 0}">
  <i class="fa fa-plus-circle fa-fw"></i>
  <span class="visually-hidden">Seleccionar</span>
</button>
        
      </div>
    </div>

    <div id="${cid}" class="collapse" data-bs-parent="#resultadosProductos">
      <div class="card-body">
        <!-- Aquí mostramos título y subtítulo completos -->
        <div class="mb-2">
          <div class="fw-bold">${it.titulo}</div>
          ${it.sub ? `<div class="text-muted">${it.sub}</div>` : ""}
        </div>

        <div class="d-flex flex-wrap gap-2 mb-2">
          ${
            it.marca
              ? `<span class="badge btn-pill bg-success">Marca: ${it.marca}</span>`
              : ""
          }
          ${
            it.oem
              ? `<span class="badge btn-pill bg-primary">OEM: ${it.oem}</span>`
              : ""
          }
          ${
            (it.divisa || "").toUpperCase()
              ? `<span class="badge btn-pill bg-secondary">Divisa: ${(
                  it.divisa || ""
                ).toUpperCase()}</span>`
              : ""
          }
          ${
            Number(it.precioVenta) > 0
              ? `<span class="badge bg-info btn-pill text-dark">P. Venta: ${Number(
                  it.precioVenta
                ).toFixed(2)}</span>`
              : ""
          }
        </div>

        <div class="d-flex flex-wrap">
          ${chipsSedes}
        </div>
      </div>
    </div>
  </div>`;
  };

  const appendResultados = (items) => {
    if (!items.length && state.page === 1) {
      $placeholder.style.display = "none";
      $sinResultados.classList.remove("d-none");
      return;
    }
    $placeholder.style.display = "none";
    $sinResultados.classList.add("d-none");
    $resultados.insertAdjacentHTML("beforeend", items.map(renderItem).join(""));
  };

  const updateCargarMas = (currentPage, totalPages) => {
    state.hasMore = currentPage < totalPages;
    $wrapCargarMas.classList.toggle("d-none", !state.hasMore);
  };

  const fetchProductos = async ({ append = false } = {}) => {
    if (state.isLoading) return;
    setLoading(true);
    if (state.abortCtrl) state.abortCtrl.abort();
    const ctrl = new AbortController();
    state.abortCtrl = ctrl;

    const idSedeSelect = document.querySelector('select[name="id_sede"]');
    const id_sede = idSedeSelect ? idSedeSelect.value || "" : "";

    const params = new URLSearchParams({
      search: state.q || "",
      categoria: state.categoria || "",
      marca: state.marca || "",
      page: String(state.page),
      limit: String(pageSize),
      id_sede: id_sede || window.__SEDE_ACTUAL_ID || "",
    });

    try {
      const r = await fetch(`${API.buscar}&${params.toString()}`, {
        cache: "no-store",
        signal: ctrl.signal,
      });
      if (!r.ok) throw new Error("HTTP " + r.status);
      const json = await r.json();
      const { items, currentPage, totalPages } = normalizeProductos(json);
      if (!append) $resultados.innerHTML = "";
      appendResultados(items);
      updateCargarMas(currentPage, totalPages);
    } catch (e) {
      if (e.name !== "AbortError") {
        console.error("Error buscando productos:", e);
        if (!append) {
          $resultados.innerHTML = "";
          $placeholder.style.display = "none";
          $sinResultados.classList.remove("d-none");
        }
        $wrapCargarMas.classList.add("d-none");
      }
    } finally {
      setLoading(false);
    }
  };

  const renderOptions = (selectEl, arr, valueKey, labelKey, placeholder) => {
    selectEl.innerHTML = `<option value="">${placeholder}</option>`;
    const seen = new Set();
    arr.forEach((it) => {
      const id = it[valueKey] ?? it.id ?? it.codigo ?? it[valueKey];
      const txt = it[labelKey] ?? it.nombre ?? it.descripcion ?? it[labelKey];
      const key = `${id}|${txt}`;
      if (id != null && txt && !seen.has(key)) {
        seen.add(key);
        const op = document.createElement("option");
        op.value = id;
        op.textContent = txt;
        selectEl.appendChild(op);
      }
    });
  };

  const normalizeFiltros = (json) => {
    const categorias = json?.categorias || json?.data?.categorias || [];
    const marcas = json?.marcas || json?.data?.marcas || [];
    return { categorias, marcas };
  };

  const cargarFiltros = async () => {
    try {
      // Si no tienes listarFiltros, esto fallará y caerá al catch (dejamos selects vacíos)
      const r = await fetch(API.filtros, { cache: "no-store" });
      if (!r.ok) throw new Error("HTTP " + r.status);
      const json = await r.json();
      const { categorias, marcas } = normalizeFiltros(json);
      renderOptions(
        $selCategoria,
        categorias,
        "id_categoria",
        "nombre",
        "Categoría"
      );
      renderOptions($selMarca, marcas, "id_marca", "nombre", "Marca");
    } catch (e) {
      console.warn("No se pudo cargar filtros (listarFiltros).", e);
      $selCategoria.innerHTML = `<option value="">Categoría</option>`;
      $selMarca.innerHTML = `<option value="">Marca</option>`;
    }
  };

  const doSearch = () => {
    state.page = 1;
    resetResultadosUI();
    fetchProductos({ append: false });
  };

  document.addEventListener("producto:creado", () => {
    if ($modal.classList.contains("show")) {
      state.page = 1;
      resetResultadosUI();
      fetchProductos({ append: false });
    }
  });

  const debouncedInput = debounce(() => {
    state.q = ($inputSearch.value || "").trim();
    doSearch();
  }, 300);

  $inputSearch.addEventListener("input", debouncedInput);
  $selCategoria.addEventListener("change", () => {
    state.categoria = $selCategoria.value || "";
    doSearch();
  });
  $selMarca.addEventListener("change", () => {
    state.marca = $selMarca.value || "";
    doSearch();
  });
  $btnCargarMas.addEventListener("click", () => {
    if (!state.hasMore || state.isLoading) return;
    state.page += 1;
    fetchProductos({ append: true });
  });

  document.addEventListener("keydown", (e) => {
    if (!$modal.classList.contains("show")) return;
    if (e.key === "/") {
      e.preventDefault();
      $inputSearch.focus();
      $inputSearch.select();
    }
  });

  // Confirm helper
  const confirmar = async (titulo, texto) => {
    if (window.Swal && typeof window.Swal.fire === "function") {
      const r = await Swal.fire({
        title: titulo,
        html: texto,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, actualizar",
        cancelButtonText: "Cancelar",
      });
      return r.isConfirmed;
    }
    return window.confirm(`${titulo}\n\n${texto.replace(/<[^>]+>/g, "")}`);
  };

  // Click en "Seleccionar" (al costado de la flecha)
  // Click en "Seleccionar" (al costado de la flecha)
  $resultados.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-seleccionar");
    if (!btn) return;

    const id = btn.dataset.id || "";
    if (!id) return;

    const sku = btn.dataset.sku || "";
    const theName = btn.dataset.nombre || "";
    const marca = btn.dataset.marca || "";

    const venta = Number(btn.dataset.precioVenta || 0);
    const divisa = (btn.dataset.divisa || "").toUpperCase();
    const utilFromData =
      btn.dataset.utilidad !== "" ? Number(btn.dataset.utilidad) : null;
    const compraFromData = Number(btn.dataset.precioCompra || 0);

    const $panelVacio = document.getElementById("panelVacio");
    const $formFichaRapida = document.getElementById("formFichaRapida");
    const $fr_id = document.getElementById("fr_id_producto_variante");
    const $fr_nombre = document.getElementById("fr_nombre");
    const $fr_sku = document.getElementById("fr_sku");
    const $fr_marca = document.getElementById("fr_marca");
    const $divisa = document.getElementById("fr_divisa");
    const $venta = document.getElementById("fr_precio_venta");
    const $comp = document.getElementById("fr_precio_compra");
    const $util = document.getElementById("fr_utilidad");
    const $switch = document.getElementById("fr_edit_switch");

    // Mostrar ficha
    $panelVacio.classList.add("d-none");
    $formFichaRapida.classList.remove("d-none");

    // Cargar datos base
    $fr_id.value = id;
    $fr_nombre.textContent = theName || "(Sin nombre)";
    $fr_sku.textContent = sku || "-";
    $fr_marca.textContent = marca || "Sin marca";

    // Divisa + precios
    $divisa.value = divisa || "USD";
    $venta.value = venta > 0 ? venta.toFixed(2) : "";
    if (compraFromData > 0) $comp.value = compraFromData.toFixed(3);
    else if (!$comp.value) $comp.value = "";

    // Bloqueo edición por defecto (como ya estaba)
    $util.disabled = true;
    $venta.disabled = true;
    $divisa.disabled = true;
    $switch.checked = false;

    // Helper: calcular utilidad a partir de compra/venta
    const calcUtil = () => {
      const c = Number($comp.value) || 0;
      const v = Number($venta.value) || 0;
      return c > 0 ? (v / c - 1) * 100 : null;
    };

    // Rellenar utilidad:
    // 1) Si viene desde el dataset, úsala y muéstrala
    // 2) Si no, calcula con compra/venta actuales (comportamiento previo)
    if (utilFromData != null && isFinite(utilFromData)) {
      $util.value = utilFromData.toFixed(2);

      // Si no hay precio de venta pero sí utilidad y compra -> derivar venta
      // (no dispara recálculos porque no tocamos el switch ni eventos)
      if (!$venta.value && compraFromData > 0) {
        $venta.value = (compraFromData * (1 + utilFromData / 100)).toFixed(2);
      }
    } else {
      const u = calcUtil();
      $util.value = u != null ? u.toFixed(2) : "";
    }
  });

  // Reglas de recálculo venta/utilidad
  // Reglas de recálculo venta/utilidad
  (() => {
    const $comp = document.getElementById("fr_precio_compra");
    const $util = document.getElementById("fr_utilidad");
    const $venta = document.getElementById("fr_precio_venta");
    const $switch = document.getElementById("fr_edit_switch");

    let lock = false;
    const toNum = (v) => {
      const n = Number(String(v).replace(/,/g, "."));
      return isFinite(n) ? n : 0;
    };
    const hasValue = (el) => String(el?.value ?? "").trim() !== "";

    const recalcVenta = () => {
      const compra = toNum($comp.value);
      const utilStr = String($util.value).trim();
      const canEdit = !!$switch?.checked;
      const utilTieneValor = utilStr !== "" && isFinite(Number(utilStr));
      const ventaYaTieneValor = hasValue($venta);
      if (compra <= 0) return;
      if (ventaYaTieneValor && !canEdit) return;
      if (!utilTieneValor) return;
      const util = Number(utilStr);
      $venta.value = (compra * (1 + util / 100)).toFixed(2);
    };

    const recalcUtil = () => {
      const compra = toNum($comp.value);
      const venta = toNum($venta.value);
      if (compra > 0 && venta >= 0) {
        $util.value = ((venta / compra - 1) * 100).toFixed(2);
      } else {
        $util.value = "";
      }
    };

    // Regla clave: cuando cambia compra, SIEMPRE recalcular utilidad
    const onCompraChange = () => {
      if (lock) return;
      lock = true;
      recalcUtil(); // utilidad se deriva de compra/venta siempre
      lock = false;
    };

    const onUtilChange = () => {
      if (lock) return;
      lock = true;
      // Si edición activada o venta vacía -> recalcular venta
      const canEdit = !!$switch?.checked;
      if (canEdit || !hasValue($venta)) recalcVenta();
      lock = false;
    };

    const onVentaChange = () => {
      if (lock) return;
      lock = true;
      recalcUtil();
      lock = false;
    };

    ["input", "change"].forEach((evt) => {
      $comp.addEventListener(evt, onCompraChange);
      $util.addEventListener(evt, onUtilChange);
      $venta.addEventListener(evt, onVentaChange);
    });
  })();

  $modal.addEventListener("shown.bs.modal", () => {
    if (!$selCategoria.options.length || !$selMarca.options.length)
      cargarFiltros();
    state.q = "";
    state.categoria = "";
    state.marca = "";
    state.page = 1;
    $inputSearch.value = "";
    $selCategoria.value = "";
    $selMarca.value = "";
    resetResultadosUI();
    fetchProductos({ append: false });
    setTimeout(() => $inputSearch.focus(), 150);
  });

  $modal.addEventListener("hide.bs.modal", () => {
    if (state.abortCtrl) state.abortCtrl.abort();
  });

  // ===== Habilitar/Deshabilitar edición + botón Guardar PV =====
  (function wireEditSwitch() {
    const $form = document.getElementById("formFichaRapida");
    const $divisa = document.getElementById("fr_divisa");
    const $util = document.getElementById("fr_utilidad");
    const $venta = document.getElementById("fr_precio_venta");
    const $switch = document.getElementById("fr_edit_switch");
    const $idVar = document.getElementById("fr_id_producto_variante");

    if (!$form || !$switch || !$divisa || !$util || !$venta || !$idVar) return;

    // crea (si no existe) el botón "Guardar cambios"
    let $btnSave = document.getElementById("fr_guardar_pv");
    if (!$btnSave) {
      $btnSave = document.createElement("button");
      $btnSave.type = "button";
      $btnSave.id = "fr_guardar_pv";
      $btnSave.className = "btn btn-outline-primary btn-sm btn-pill";
      $btnSave.innerHTML = '<i class="fa fa-save fa-lg"></i> Guardar cambios';

      // lo insertamos junto al botón "Agregar al detalle"
      const $footer = $form.querySelector("#fr_agregar")?.parentElement;
      if ($footer)
        $footer.insertBefore($btnSave, $footer.querySelector("#fr_agregar"));
    }

    const applySwitchState = () => {
      const on = !!$switch.checked;
      $divisa.disabled = !on;
      $util.disabled = !on;
      $venta.disabled = !on;
      $btnSave.style.display = on ? "inline-flex" : "none";
    };

    $switch.addEventListener("change", applySwitchState);
    // estado inicial (oculto)
    applySwitchState();

    // Guardar PV en servidor
    $btnSave.addEventListener("click", async () => {
      const id = ($idVar.value || "").trim();
      if (!id) return;

      const $comp = document.getElementById("fr_precio_compra");
      const $venta = document.getElementById("fr_precio_venta");
      const $util = document.getElementById("fr_utilidad");
      const $divisa = document.getElementById("fr_divisa");

      const divisa = ($divisa.value || "").toUpperCase();
      const util = String($util.value || "");
      const venta = String($venta.value || "");
      const compraNum = Number(($comp.value || "0").replace(/,/g, "."));

      // 👇 Validación requerida: debe existir precio de compra > 0
      if (!(compraNum > 0)) {
        if (window.Swal) {
          await Swal.fire({
            icon: "warning",
            title: "Precio de compra requerido",
            text: "Debes registrar un precio de compra mayor a 0 antes de guardar.",
          });
        } else {
          alert("Debes registrar un precio de compra > 0 antes de guardar.");
        }
        return;
      }

      const ok = await confirmar(
        "¿Actualizar precio de venta?",
        `<div class="text-start">
      <div>Divisa: <b>${divisa || "—"}</b></div>
      <div>Precio compra: <b>${compraNum.toFixed(3)}</b></div>
      <div>Utilidad: <b>${util || "—"}%</b></div>
      <div>Precio venta: <b>${venta || "—"}</b></div>
    </div>`
      );
      if (!ok) return;

      const fd = new FormData();
      // claves para compatibilidad
      fd.append("id_producto_variante", id);
      fd.append("id_variante", id);
      fd.append("precio_venta", venta);
      fd.append("PrecioUnitario_Venta", venta);
      fd.append("utilidad_pct", util);
      fd.append("Utilidad", util);
      fd.append("moneda_venta", divisa);
      fd.append("TipoDivisa", divisa);

      // 👇 Enviar precio de compra + moneda de compra al servidor
      fd.append("precio_compra", compraNum.toFixed(3));
      fd.append("moneda_compra", divisa);

      try {
        const r = await fetch(API.actualizarPV, { method: "POST", body: fd });
        const j = await r.json();
        if (!j?.success) throw new Error(j?.message || "No se pudo actualizar");
        if (window.Swal)
          Swal.fire({
            icon: "success",
            title: "Actualizado",
            timer: 1100,
            showConfirmButton: false,
          });
      } catch (e) {
        console.error(e);
        alert("Error al actualizar el precio de venta.");
      }
    });
  })();
})();

/* ========================= Agregar al detalle (carrito en memoria) ========================= */
(() => {
  const resetFichaRapida = () => {
    document.getElementById("fr_id_producto_variante").value = "";
    document.getElementById("fr_nombre").textContent = "";
    document.getElementById("fr_sku").textContent = "";
    document.getElementById("fr_marca").textContent = "";
    document.getElementById("fr_categoria").textContent = "";
    document.getElementById("fr_cantidad").value = "1.00";
    document.getElementById("fr_precio_compra").value = "";
    document.getElementById("fr_precio_venta").value = "";
    document.getElementById("fr_utilidad").value = "";
    document.getElementById("fr_divisa").value = "PEN";
    // Opcional: volver a mostrar el panel vacío
    // $formFichaRapida.classList.add("d-none"); $panelVacio.classList.remove("d-none");
    // Foco al buscador para flujo rápido:
    document.getElementById("buscadorProductos")?.focus();
  };

  const btn = document.getElementById("fr_agregar");
  if (!btn) return;
  btn.addEventListener("click", () => {
    const id = (
      document.getElementById("fr_id_producto_variante").value || ""
    ).trim();
    if (!id) return;

    const nombre = (
      document.getElementById("fr_nombre").textContent || ""
    ).trim();
    const sku = (document.getElementById("fr_sku").textContent || "").trim();
    const cantidad = Number(document.getElementById("fr_cantidad").value || 0);
    const precioCompra = Number(
      document.getElementById("fr_precio_compra").value || 0
    );

    if (cantidad <= 0 || precioCompra < 0) {
      alert("Cantidad y precio de compra deben ser válidos.");
      return;
    }

    window.DetalleCompra?.addItem({
      id,
      sku,
      nombre,
      cantidad,
      precioCompra,
    });

    if (window.Swal) {
      Swal.fire({
        icon: "success",
        title: "Agregado",
        timer: 900,
        showConfirmButton: false,
      });
    }
    resetFichaRapida();
  });
})();

/* ========================================================================
   MODAL "NUEVO PRODUCTO" (todo unificado + typeahead funcionando)
   ======================================================================== */
(() => {
  if (typeof Bloodhound === "undefined" || !window.jQuery) {
    console.warn("Typeahead no disponible (falta jQuery o Bloodhound).");
    return;
  }

  const API = {
    PRODUCTOS: "assets/ajax/productos_ajax.php",
    MONEDAS: "assets/ajax/tipo_moneda_ajax.php",
  };

  const SEL = {
    modalNuevo: "#modalNuevoProducto",
    formNuevo: "#formNuevoProducto",

    inputCategoria: "#inputCategoria",
    hiddenCategoriaId: "#id_categoriaProductos",
    btnAgregarCategoria: "#btnAgregarCategoria",
    contNuevaCategoria: "#nuevoCategoriaContainer",
    inputClasificacionGeneral: "#inputClasificacionGeneral",
    inputNombreEspecifico: "#inputNombreEspecifico",
    btnGuardarCategoria: "#btnGuardarCategoria",

    inputOEM: '[name="OEM_productos"]',
    inputMarcaVehiculo: "#inputMarcaVehiculo",
    hiddenMarcaVehiculoId: "#id_marcaVehiculo",
    inputModeloVehiculo: '[name="ModeloVehiculo"]',
    inputCilindrada: '[name="CilindradaVehiculo"]',
    inputMotor: '[name="MotorVehiculo"]',
    inputAnioIni: '[name="AñoInicialVehiculo"]',
    inputAnioFin: '[name="AñoFinVehiculo"]',

    selectDivisa: '[name="TipoDivisa"]',
    inputPrecioUnitario: '[name="PrecioUnitario"]', // (no se usa; en HTML es PrecioUnitario_Venta)
    selectUM: '[name="unidad_medida"]',

    inputSKU: "#SKU_Productos",
    inputCodProveedor: '[name="CodigoReferencia_proveedor"]',
    inputMarcaProducto: '[name="MarcaProductos"]',
    inputOrigenProducto: '[name="OrigenProductos"]',
    inputDescripcionVar: '[name="descripcion_variante"]',
    textDescripcionCompleta: 'textarea[name="DescripcionCompleta"]',

    inputImagen: "#ImagenProducto",
    btnVistaPrevia: "#btnVistaPrevia",

    btnSubmit: '#formNuevoProducto button[type="submit"]',
  };

  const IMAGEN_MAX_BYTES = 3 * 1024 * 1024;
  const IMAGEN_FORMATOS_PERMITIDOS = [
    "image/jpeg",
    "image/png",
    "image/jpg",
    "image/webp",
  ];

  const $ = (s) => document.querySelector(s);
  const on = (el, evt, fn) => el && el.addEventListener(evt, fn);
  const setVal = (sel, val) => {
    const el = $(sel);
    if (el) el.value = val;
  };
  const getVal = (sel) => ($(sel)?.value ?? "").trim();

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

  async function initTypeaheadCategorias() {
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
        setVal(SEL.hiddenCategoriaId, String(s.id));
        lastSelected = { id: String(s.id), nombre: String(s.nombre) };
        generarSKU();
        actualizarDescripcion();
      })
      .on("input", function () {
        const current = String(this.value || "").trim();
        if (!current || current !== lastSelected.nombre)
          setVal(SEL.hiddenCategoriaId, "");
      });
  }

  /* ---------- TYPEAHEAD: Clasificación General ---------- */
  async function initTypeaheadClasificacionGeneral() {
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
      console.error("initTypeaheadClasificacionGeneral:", e);
    }
  }

  /* ---------- TYPEAHEAD: Marca de Vehículo ---------- */
  async function initTypeaheadMarcaVehiculo() {
    try {
      const r = await fetch(`${API.PRODUCTOS}?action=listarMarcasVehiculos`, {
        cache: "no-store",
      });
      const data = await r.json();
      if (!data?.success) return;
      const marcas = [...new Set((data.data || []).map((m) => m.nombre_marca))];

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
          setVal(SEL.hiddenMarcaVehiculoId, "");
          generarSKU();
          actualizarDescripcion();
        })
        .on("input", function () {
          setVal(SEL.hiddenMarcaVehiculoId, "");
        });
    } catch (e) {
      console.error("initTypeaheadMarcaVehiculo:", e);
    }
  }

  /* ---------- CATÁLOGOS: Monedas y Unidades ---------- */
  async function cargarMonedas() {
    const sel = $(SEL.selectDivisa);
    if (!sel) return;
    try {
      const r = await fetch(`${API.MONEDAS}?action=obtenerMonedas`, {
        cache: "no-store",
      });
      const data = await r.json();
      if (!data?.success) return;
      sel.innerHTML = '<option value="" disabled selected>Seleccione…</option>';
      (data.data || []).forEach((m) => {
        const op = document.createElement("option");
        op.value = m.CodigoSunat_tipoMoneda;
        op.textContent = m.Abreviatura_TipoMoneda;
        sel.appendChild(op);
      });
    } catch (e) {
      console.error("cargarMonedas:", e);
    }
  }

  async function cargarUnidadesMedida() {
    const sel = $(SEL.selectUM);
    if (!sel) return;
    try {
      const r = await fetch(`${API.PRODUCTOS}?action=listarUnidadMedidas`, {
        cache: "no-store",
      });
      const data = await r.json();
      if (!data?.success) return;
      sel.innerHTML = '<option value="" disabled selected>Seleccione…</option>';
      (data.data || []).forEach((u) => {
        const op = document.createElement("option");
        op.value = u.cod_uni_med_sunat;
        op.textContent = `${u.nombre} - ${u.simbolo}`;
        sel.appendChild(op);
      });
    } catch (e) {
      console.error("cargarUnidadesMedida:", e);
    }
  }

  /* ---------- DESCRIPCIÓN AUTOMÁTICA ---------- */
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

  /* ---------- Uppercase ---------- */
  function ucOnInput(selectors) {
    selectors.forEach((s) => {
      const el = $(s);
      el &&
        el.addEventListener("input", () => {
          el.value = (el.value || "").toUpperCase();
        });
    });
  }
  function wireUppercase() {
    ucOnInput([
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
    ]);
  }

  /* ---------- SKU (prioriza Marca Vehículo; fallback Marca Producto) ---------- */
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
  function wireSKU() {
    const marcaProd = document.querySelector(SEL.inputMarcaProducto);
    const marcaVeh = document.querySelector(SEL.inputMarcaVehiculo);
    const cat = document.querySelector(SEL.inputCategoria);
    const trigger = () => generarSKU();
    marcaProd && marcaProd.addEventListener("blur", trigger);
    marcaVeh && marcaVeh.addEventListener("blur", trigger);
    cat && cat.addEventListener("blur", trigger);
  }

  /* ---------- IMAGEN: validación + preview (3MB) ---------- */
  function initImagenPreview() {
    const input = $(SEL.inputImagen);
    const btn = $(SEL.btnVistaPrevia);
    if (!input || !btn) return;

    let dataURL = "";
    input.addEventListener("change", function () {
      const file = this.files?.[0];
      if (!file) {
        dataURL = "";
        btn.disabled = true;
        return;
      }
      if (!IMAGEN_FORMATOS_PERMITIDOS.includes(file.type)) {
        this.value = "";
        dataURL = "";
        btn.disabled = true;
        alert("Formato no permitido. Solo JPG, JPEG, PNG o WEBP.");
        return;
      }
      if (file.size > IMAGEN_MAX_BYTES) {
        this.value = "";
        dataURL = "";
        btn.disabled = true;
        alert("El archivo excede el tamaño permitido de 3 MB.");
        return;
      }
      const reader = new FileReader();
      reader.onload = (e) => {
        dataURL = e.target?.result || "";
        btn.disabled = !dataURL;
      };
      reader.readAsDataURL(file);
    });

    btn.addEventListener("click", function () {
      if (!dataURL) return;
      const w = window.open();
      if (w) {
        w.document.title = "Vista previa de imagen";
        w.document.body.style.margin = "0";
        w.document.body.style.background = "#111";
        const img = w.document.createElement("img");
        img.src = dataURL;
        img.style.maxWidth = "100vw";
        img.style.maxHeight = "100vh";
        img.style.display = "block";
        img.style.margin = "0 auto";
        w.document.body.appendChild(img);
      }
    });
  }

  /* ---------- Nueva Categoría inline ---------- */
  function wireNuevaCategoria() {
    const $btnAdd = $(SEL.btnAgregarCategoria);
    const $wrap = $(SEL.contNuevaCategoria);
    const $btnSave = $(SEL.btnGuardarCategoria);

    on($btnAdd, "click", () => {
      if ($wrap.classList.contains("d-none")) {
        $wrap.classList.remove("d-none");
        initTypeaheadClasificacionGeneral();
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
        $(SEL.contNuevaCategoria)?.classList.add("d-none");

        initTypeaheadCategorias();
        generarSKU();
        actualizarDescripcion();
      } catch (e) {
        console.error("Guardar categoría:", e);
        alert("Error inesperado.");
      }
    });
  }

  /* ---------- Descripción auto: wiring ---------- */
  function wireDescripcionAuto() {
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
  }

  /* ---------- Validación UX/UI 2025 ---------- */
  function validateNuevoProducto() {
    // Limpia estados previos
    [...document.querySelectorAll(".invalid-hint")].forEach((n) => n.remove());
    [...document.querySelectorAll(".is-invalid")].forEach((n) => {
      n.classList.remove("is-invalid");
      n.removeAttribute("aria-invalid");
    });

    const alertBox =
      document.getElementById("nuevoProdAlert") ||
      (() => {
        const d = document.createElement("div");
        d.id = "nuevoProdAlert";
        d.className = "alert alert-warning d-none";
        const form = document.querySelector("#formNuevoProducto");
        form?.insertAdjacentElement("afterbegin", d);
        return d;
      })();

    const SEL = {
      inputCategoria: "#inputCategoria",
      hiddenCategoriaId: "#id_categoriaProductos",
      inputMarcaVehiculo: "#inputMarcaVehiculo",
      inputCodProveedor: '[name="CodigoReferencia_proveedor"]',
      inputMarcaProducto: '[name="MarcaProductos"]',
      inputOrigenProducto: '[name="OrigenProductos"]',
      inputSKU: "#SKU_Productos",
      selectDivisa: '[name="TipoDivisa"]',
      selectUM: '[name="unidad_medida"]',
    };
    const $ = (s) => document.querySelector(s);
    const getVal = (s) => ($(s)?.value ?? "").trim();

    const rules = [
      {
        el: $(SEL.inputCategoria),
        value: getVal(SEL.hiddenCategoriaId),
        msg: "Categoría es requerida",
      },
      {
        el: $(SEL.inputMarcaVehiculo),
        value: getVal(SEL.inputMarcaVehiculo),
        msg: "Marca de vehículo es requerida",
      },
      {
        el: $(SEL.inputCodProveedor),
        value: getVal(SEL.inputCodProveedor),
        msg: "Código del proveedor es requerido",
      },
      {
        el: $(SEL.inputMarcaProducto),
        value: getVal(SEL.inputMarcaProducto),
        msg: "Marca del producto es requerida",
      },
      {
        el: $(SEL.inputOrigenProducto),
        value: getVal(SEL.inputOrigenProducto),
        msg: "Origen del producto es requerido",
      },
      {
        el: $(SEL.inputSKU),
        value: getVal(SEL.inputSKU),
        msg: "SKU no generado",
      },
      {
        el: $(SEL.selectDivisa),
        value: getVal(SEL.selectDivisa),
        msg: "Divisa es requerida",
      },
      {
        el: $(SEL.selectUM),
        value: getVal(SEL.selectUM),
        msg: "Unidad de medida es requerida",
      },
    ];

    const invalids = [];
    let firstInvalid = null;

    const markInvalid = (el, msg) => {
      if (!el) return;
      el.classList.add("is-invalid");
      el.setAttribute("aria-invalid", "true");
      const hint = document.createElement("div");
      hint.className = "invalid-hint text-danger mt-1 small";
      hint.textContent = msg;
      (
        el.closest(
          ".col-md-6, .col-md-3, .col-md-4, .col-12, .input-group, .form-group"
        ) || el.parentElement
      )?.appendChild(hint);
      if (!firstInvalid) firstInvalid = el;
      invalids.push({ el, msg });
    };

    rules.forEach(({ el, value, msg }) => {
      if (!String(value || "").trim()) markInvalid(el, msg);
    });

    // Construye y muestra el card de alertas (si hay)
    if (invalids.length) {
      // activa la pestaña del primer inválido
      const pane = firstInvalid.closest(".tab-pane");
      if (pane?.id) {
        const trigger = document.querySelector(
          `[data-bs-toggle="tab"][data-bs-target="#${pane.id}"], [data-bs-toggle="tab"][href="#${pane.id}"]`
        );
        if (trigger) new bootstrap.Tab(trigger).show();
      }

      // lista clickable que activa pestaña y hace focus
      alertBox.innerHTML = `
      <div class="d-flex align-items-start">
        <i class="fa fa-exclamation-triangle me-2 mt-1"></i>
        <div>
          <strong>Faltan campos por completar:</strong>
          <ul class="mb-0 mt-2" style="padding-left:18px">
            ${invalids
              .map(
                (inv, i) => `
              <li>
                <a href="#" data-inv-idx="${i}" class="text-decoration-underline">
                  ${inv.msg}
                </a>
              </li>`
              )
              .join("")}
          </ul>
        </div>
      </div>
    `;
      alertBox.classList.remove("d-none");

      // wiring de cada item
      alertBox.querySelectorAll("a[data-inv-idx]").forEach((a) => {
        a.addEventListener("click", (e) => {
          e.preventDefault();
          const idx = Number(a.getAttribute("data-inv-idx"));
          const { el } = invalids[idx];
          const pane = el.closest(".tab-pane");
          if (pane?.id) {
            const trigger = document.querySelector(
              `[data-bs-toggle="tab"][data-bs-target="#${pane.id}"], [data-bs-toggle="tab"][href="#${pane.id}"]`
            );
            if (trigger) new bootstrap.Tab(trigger).show();
          }
          el.focus({ preventScroll: false });
          el.scrollIntoView({ behavior: "smooth", block: "center" });
        });
      });

      // scroll al inicio del formulario
      alertBox.scrollIntoView({ behavior: "smooth", block: "start" });
      return false;
    } else {
      alertBox.classList.add("d-none");
    }

    return true;
  }

  /* ---------- Guardar con spinner + notificar catálogo ---------- */
  async function guardarNuevoProducto() {
    if (!validateNuevoProducto()) return;

    const form = document.querySelector(SEL.formNuevo);
    const btn = document.querySelector(SEL.btnSubmit);
    if (!form || !btn) return;

    // Spinner + disable
    const oldHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML =
      '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando…';

    // FormData
    const fd = new FormData(form);
    const chkBolsas = document.getElementById("chkBolsas");
    fd.set("icbper", chkBolsas && chkBolsas.checked ? "1" : "0");
    fd.set("TipoIGV", "10"); // SUNAT: Gravado - Onerosa
    if (fd.has("unidad_medida"))
      fd.set("unidadMedida", fd.get("unidad_medida") || "NIU");

    // Opción A: NO registrar PV en el alta (se actualizará luego desde la ficha rápida)
    // Si el form viene con PrecioUnitario_Venta/Utilidad, los neutralizamos para que el backend no intente crear la vigencia.
    fd.delete("PrecioUnitario_Venta");
    fd.delete("Utilidad");

    // (opcional) si crees que el form trae algo en compra, también neutralízalo para evitar confusiones:
    fd.delete("PrecioUnitario_Compra");

    try {
      const r = await fetch(`${API.PRODUCTOS}?action=guardarProducto`, {
        method: "POST",
        body: fd,
      });
      const json = await r.json();
      if (!json?.success)
        throw new Error(json?.message || "No se pudo guardar");

      resetFormNuevoProducto();

      // cerrar modal (como ya lo hacías)
      const modalNuevo = document.querySelector(SEL.modalNuevo);
      const bsModal =
        bootstrap.Modal.getInstance(modalNuevo) ||
        new bootstrap.Modal(modalNuevo);
      bsModal.hide();

      // notificar (por si quieres refrescar otro listado externo)
      document.dispatchEvent(
        new CustomEvent("producto:creado", { detail: json.data })
      );
    } catch (e) {
      console.error(e);
      btn.innerHTML =
        '<i class="fa fa-exclamation-circle me-1"></i> Reintentar guardar';
    } finally {
      btn.disabled = false;
      if (!btn.innerHTML.includes("Reintentar")) btn.innerHTML = oldHTML;
    }
  }

  // --- LIMPIAR FORMULARIO "Nuevo Producto" ---
  function resetFormNuevoProducto() {
    // 1) Reset general del form
    const form = document.querySelector(SEL.formNuevo);
    if (form) form.reset();

    // 2) Borrar estados de validación y hints
    document
      .querySelectorAll(".is-invalid")
      .forEach((n) => n.classList.remove("is-invalid"));
    document.querySelectorAll(".invalid-hint").forEach((n) => n.remove());

    // 3) Limpiar inputs específicos / typeaheads / ocultos
    // Categoría
    setVal(SEL.inputCategoria, "");
    setVal(SEL.hiddenCategoriaId, "");

    // Marca de vehículo
    setVal(SEL.inputMarcaVehiculo, "");
    setVal(SEL.hiddenMarcaVehiculoId, "");

    // Campos de fitment / comerciales / variante
    setVal(SEL.inputModeloVehiculo, "");
    setVal(SEL.inputCilindrada, "");
    setVal(SEL.inputMotor, "");
    setVal(SEL.inputAnioIni, "");
    setVal(SEL.inputAnioFin, "");
    setVal(SEL.inputSKU, "");
    setVal(SEL.inputCodProveedor, "");
    setVal(SEL.inputMarcaProducto, "");
    setVal(SEL.inputOrigenProducto, "");
    setVal(SEL.inputDescripcionVar, "");
    setVal(SEL.textDescripcionCompleta, "");

    // Selects (divisa y unidad de medida) a placeholder
    const selDivisa = document.querySelector(SEL.selectDivisa);
    if (selDivisa) selDivisa.value = "";
    const selUM = document.querySelector(SEL.selectUM);
    if (selUM) selUM.value = "";

    // Check bolsas plásticas
    const chkBolsas = document.getElementById("chkBolsas");
    if (chkBolsas) chkBolsas.checked = false;

    // 4) Imagen: limpiar archivo y desactivar "Vista previa"
    const inputImg = document.querySelector(SEL.inputImagen);
    if (inputImg) inputImg.value = "";
    const btnPrev = document.querySelector(SEL.btnVistaPrevia);
    if (btnPrev) btnPrev.disabled = true;

    // 5) Ocultar el bloque de "nueva categoría" si quedó abierto
    const wrapNuevaCat = document.querySelector(SEL.contNuevaCategoria);
    if (wrapNuevaCat) wrapNuevaCat.classList.add("d-none");

    // 6) Asegurar que la descripción quede recalculada (vacía)
    actualizarDescripcion();
  }

  /* ---------- Submit del formulario ---------- */
  function wireFormSubmit() {
    const form = document.querySelector(SEL.formNuevo);
    if (!form) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      guardarNuevoProducto();
    });
  }

  /* ---------- Init al abrir el modal ---------- */
  function initOnModalShow() {
    const modalEl = document.querySelector(SEL.modalNuevo);
    if (!modalEl) return;

    modalEl.addEventListener("shown.bs.modal", () => {
      initTypeaheadCategorias();
      initTypeaheadMarcaVehiculo();
      cargarMonedas();
      cargarUnidadesMedida();
      actualizarDescripcion();
    });
  }

  // ---- INIT (en orden correcto) ----
  (function initOnce() {
    wireNuevaCategoria();
    wireDescripcionAuto();
    wireUppercase();
    wireSKU();
    wireFormSubmit();
    initImagenPreview();
    initOnModalShow();
  })();

  // (Opcional) helpers para depurar
  window.NuevoProducto = {
    validar: validateNuevoProducto,
    guardar: guardarNuevoProducto,
    reset: resetFormNuevoProducto,
  };
})();

/* ========================= Registrar Compra (POST) ========================= */
(() => {
  const btn = document.getElementById("btnGuardarCompra");
  if (!btn) return;

  const symClean = (txt) => String(txt || "").replace(/[^\d.,-]/g, "");
  const toNum = (txt) => {
    // Soporta “1,234.56” o “1.234,56” -> usa punto como decimal
    const s = String(txt || "")
      .trim()
      .replace(/\s/g, "");
    const hasCommaDecimal = /,\d{1,3}$/.test(s);
    if (hasCommaDecimal) {
      return Number(s.replace(/\./g, "").replace(",", "."));
    }
    return Number(s.replace(/,/g, ""));
  };

  btn.addEventListener("click", async () => {
    const form = document.getElementById("formCompra");
    const tokenEl = document.getElementById("csrf_token");
    const selSede = form.querySelector('select[name="id_sede"]');
    const provHidden = document.getElementById("id_proveedor");

    const items =
      (window.DetalleCompra && window.DetalleCompra.getItems()) || [];
    if (!items.length) {
      alert("El detalle de la compra está vacío.");
      return;
    }
    if (!provHidden.value) {
      alert("Selecciona un proveedor.");
      return;
    }
    if (!selSede.value) {
      alert("Selecciona la sede.");
      return;
    }
    if (!tokenEl || !tokenEl.value) {
      alert("No se encontró el token CSRF en la página.");
      return;
    }

    // Totales de la UI (ya calculados con IGV incluido según tu lógica)
    const subtotalTxt = symClean(
      document.getElementById("subtotal_compra").textContent
    );
    const igvTxt = symClean(document.getElementById("igv_compra").textContent);
    const totalTxt = symClean(
      document.getElementById("total_compra").textContent
    );

    const fd = new FormData(form);
    fd.set("csrf_token", tokenEl.value);
    fd.set("subtotal_compra", String(toNum(subtotalTxt) || 0));
    fd.set("igv_compra", String(toNum(igvTxt) || 0));
    fd.set("total_compra", String(toNum(totalTxt) || 0));

    // Mapear carrito -> contrato del backend
    const detalle = items.map((it) => ({
      id_producto_variante: Number(it.id),
      cantidad: Number(it.cantidad),
      precio_unitario: Number(it.precioCompra),
      fecha_vencimiento: null, // o añade tu fecha si la manejas
    }));
    fd.set("detalle_json", JSON.stringify(detalle));

    // (opcional) id_usuario desde sesión en servidor; si no, puedes enviar 0.
    // fd.set("id_usuario", String(window.__USER_ID__ || 0));

    try {
      const r = await fetch("assets/ajax/Compras_ajax.php?action=registrar", {
        method: "POST",
        body: fd,
        // mismo origen: fetch envía cookies por defecto; si usas subdominio, añade: credentials: "include"
      });
      const j = await r.json();
      if (!j.success) {
        throw new Error(j.message || "No se pudo registrar la compra");
      }

      if (window.Swal) {
        await Swal.fire({
          icon: "success",
          title: "Compra registrada",
          timer: 1200,
          showConfirmButton: false,
        });
        // ⏳ refrescar 5s después de mostrar el alert
        setTimeout(() => window.location.reload(), 5000);
      } else {
        alert("Compra registrada correctamente");

        setTimeout(() => window.location.reload(), 5000);
      }
    } catch (e) {
      console.error(e);
      if (
        String(e.message || "")
          .toLowerCase()
          .includes("csrf")
      ) {
        alert("Token CSRF inválido. Recarga la página e inténtalo nuevamente.");
      } else {
        alert("Error al registrar la compra: " + (e.message || "desconocido"));
      }
    }
  });
})();

/* ========================= Validación para habilitar Guardar Compra ========================= */
(() => {
  const form = document.getElementById("formCompra");
  const btn = document.getElementById("btnGuardarCompra");
  if (!form || !btn) return;

  // Campos a validar
  const $provInput = document.getElementById("proveedor_search");
  const $provHidden = document.getElementById("id_proveedor");
  const $fecha = form.querySelector('input[name="fechaEmision_documento"]');
  const $tipoDoc = form.querySelector('select[name="tipoDocumento_compra"]');
  const $numDoc = form.querySelector('input[name="numeroDocumento_compra"]');
  const $moneda = form.querySelector('select[name="tipo_moneda"]');

  const clean = (v) => String(v || "").trim();

  // Marcar/desmarcar visualmente (Bootstrap)
  const markInvalid = (el, invalid) => {
    if (!el) return;
    el.classList.toggle("is-invalid", !!invalid);
  };

  const getCartCount = () => {
    try {
      return window.DetalleCompra?.getItems()?.length || 0;
    } catch {
      return 0;
    }
  };

  const validate = () => {
    const okProveedor = !!clean($provHidden.value);
    const okFecha = !!clean($fecha?.value);
    const okTipo = !!clean($tipoDoc?.value);
    const okNum = !!clean($numDoc?.value);
    const okMoneda = !!clean($moneda?.value);
    const okDetalle = getCartCount() > 0;

    // feedback visual
    markInvalid($provInput, !okProveedor);
    markInvalid($fecha, !okFecha);
    markInvalid($tipoDoc, !okTipo);
    markInvalid($numDoc, !okNum);
    markInvalid($moneda, !okMoneda);

    // habilita/deshabilita botón
    btn.disabled = !(
      okProveedor &&
      okFecha &&
      okTipo &&
      okNum &&
      okMoneda &&
      okDetalle
    );
  };

  // Estado inicial: deshabilitado
  btn.disabled = true;

  // Revalidar cuando cambien los campos
  ["input", "change", "blur"].forEach((evt) => {
    $provInput?.addEventListener(evt, validate);
    $provHidden?.addEventListener(evt, validate);
    $fecha?.addEventListener(evt, validate);
    $tipoDoc?.addEventListener(evt, validate);
    $numDoc?.addEventListener(evt, validate);
    $moneda?.addEventListener(evt, validate);
  });

  // Revalidar cuando cambie el carrito
  document.addEventListener("compra:cartChanged", validate);

  // Primera validación al cargar
  document.addEventListener("DOMContentLoaded", validate);
  validate();
})();

/* ========================= Autosize ancho modal Nuevo Producto (>=1400px) ========================= */
(function () {
  var modal = document.querySelector("#modalNuevoProducto[data-modal-auto]");
  if (!modal) return;
  var dialog = modal.querySelector(".modal-dialog");
  function applySize() {
    var w = window.innerWidth;
    if (w >= 1400) dialog.classList.add("modal-xxl-95");
    else dialog.classList.remove("modal-xxl-95");
  }
  modal.addEventListener("shown.bs.modal", applySize);
  window.addEventListener("resize", function () {
    if (modal.classList.contains("show")) applySize();
  });
})();
