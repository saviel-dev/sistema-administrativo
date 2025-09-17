(() => {
  // === Configuración general ===
  const IVA = 0.18; // IGV 18%
  const cart = new Map(); // {id, nombre, sku, precioBruto, qty, desc, afect, um}
  const nowISO = new Date(Date.now() - new Date().getTimezoneOffset() * 60000)
    .toISOString()
    .slice(0, 16);
  document.getElementById("cpeFecha").value = nowISO;

  // Muestra input de días de crédito si aplica
  document.getElementById("fpCredito").addEventListener("change", (e) => {
    document.getElementById("fpPlazo").style.display = e.target.checked
      ? "block"
      : "none";
  });
  document
    .getElementById("fpContado")
    .addEventListener(
      "change",
      () => (document.getElementById("fpPlazo").style.display = "none")
    );

  // === Moneda / tipo de cambio ===
  // Precios del catálogo están en PEN (base). Si moneda=USD, se convierten con TC.
  const symbols = { PEN: "S/", USD: "$" };
  const getCurrency = () => document.getElementById("cpeMoneda").value || "PEN";
  const getRate = () => {
    const m = getCurrency();
    if (m === "USD") {
      const tc = Number(document.getElementById("cpeTipoCambio").value || 0);
      return tc > 0 ? 1 / tc : 1 / 3.8; // PEN→USD (dividir por TC)
    }
    return 1; // PEN
  };
  const getSymbol = () => symbols[getCurrency()] || "S/";

  const els = {
    grid: document.getElementById("gridProductos"),
    listaCarrito: document.getElementById("listaCarrito"),
    listaCarritoMobile: document.getElementById("listaCarritoMobile"),
    lblOpGravada: document.getElementById("lblOpGravada"),
    lblOpExonerada: document.getElementById("lblOpExonerada"),
    lblOpInafecta: document.getElementById("lblOpInafecta"),
    lblImpuesto: document.getElementById("lblImpuesto"),
    lblImpuestoM: document.getElementById("lblImpuestoM"),
    lblTotal: document.getElementById("lblTotal"),
    lblTotalM: document.getElementById("lblTotalM"),
    lblMontoLetras: document.getElementById("lblMontoLetras"),
    cartItemsCount: document.getElementById("cartItemsCount"),
    badgeCartCount: document.getElementById("badgeCartCount"),
    inputBuscar: document.getElementById("inputBuscar"),
    inputBarcode: document.getElementById("inputBarcode"),
    btnLimpiar: document.getElementById("btnLimpiar"),
    btnVaciar: document.getElementById("btnVaciar"),
    modalPago: document.getElementById("modalPago"),
    pagoTotal: document.getElementById("pagoTotal"),
    inputRecibido: document.getElementById("inputRecibido"),
    lblVuelto: document.getElementById("lblVuelto"),
  };

  // === Bootstrap alias + helpers (compat 5.0 - 5.3) ===
  const B = window.bootstrap || null;

  // Evita el warning ARIA al cerrar modales con la X (desenfocar si el foco está dentro)
  document.querySelectorAll(".modal").forEach((m) => {
    m.addEventListener("hide.bs.modal", () => {
      const act = document.activeElement;
      if (act && m.contains(act)) act.blur();
    });
    m.addEventListener("hidden.bs.modal", () => {
      document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
    });
  });

  // crea instancia compatible con cualquier versión
  function createOffcanvasCompat(el, opts) {
    if (!B || !el) return null;
    if (B.Offcanvas?.getOrCreateInstance) {
      return B.Offcanvas.getOrCreateInstance(el, opts);
    }
    // Bootstrap < 5.2
    return new B.Offcanvas(el, opts);
  }

  function showModalCompat(el) {
    if (!B || !el) return;
    if (B.Modal?.getOrCreateInstance) {
      B.Modal.getOrCreateInstance(el).show();
    } else {
      new B.Modal(el).show();
    }
  }

  function hideModalCompat(el) {
    if (!B || !el) return;
    // evitar warning de aria-hidden con foco dentro del modal
    if (document.activeElement) document.activeElement.blur();

    let inst = B.Modal?.getInstance ? B.Modal.getInstance(el) : null;
    if (!inst) {
      inst = B.Modal?.getOrCreateInstance
        ? B.Modal.getOrCreateInstance(el)
        : new B.Modal(el);
    }
    inst?.hide?.();

    // al terminar, limpiar cosas colgadas y devolver foco a un elemento seguro
    el.addEventListener(
      "hidden.bs.modal",
      () => {
        document.querySelectorAll(".modal-backdrop").forEach((b) => b.remove());
        document.getElementById("btnCobrar")?.focus();
      },
      { once: true }
    );
  }

  // === OFFCANVAS RESPONSIVE ===
  const offcanvasEl = document.getElementById("offcanvasCarrito");
  const offcanvasInst = createOffcanvasCompat(offcanvasEl, {
    backdrop: true,
    scroll: true,
  });

  function closeOffcanvasAndCleanup() {
    try {
      offcanvasInst?.hide?.();
    } catch (_) {}
    offcanvasEl?.classList.remove("show");
    document.querySelectorAll(".offcanvas-backdrop").forEach((b) => b.remove());

    // No limpiar el body si hay algún modal abierto
    const anyModalOpen = document.querySelector(".modal.show");
    if (!anyModalOpen) {
      document.body.classList.remove("offcanvas-backdrop", "modal-open");
      document.body.style.removeProperty("overflow");
    }
  }

  function handleResizeOffcanvas() {
    const isDesktop = window.matchMedia("(min-width: 992px)").matches;
    if (isDesktop) closeOffcanvasAndCleanup();
  }

  // Mostrar/ocultar tipo de cambio
  const wrapTC = document.getElementById("wrapTipoCambio");
  const cpeMoneda = document.getElementById("cpeMoneda");
  const cpeTipoCambio = document.getElementById("cpeTipoCambio");

  function toggleTipoCambio() {
    const isUSD = getCurrency() === "USD";
    if (wrapTC) wrapTC.style.display = isUSD ? "" : "none";
    syncTotalsUI();
  }

  // listeners
  cpeMoneda?.addEventListener("change", toggleTipoCambio);
  cpeTipoCambio?.addEventListener("input", syncTotalsUI);
  // Inicial: espera al siguiente frame para asegurar que las funciones ya están listas
  window.requestAnimationFrame(toggleTipoCambio);

  // Impide abrir en ≥ lg
  offcanvasEl?.addEventListener("show.bs.offcanvas", (e) => {
    if (window.matchMedia("(min-width: 992px)").matches) e.preventDefault();
  });

  window.addEventListener("resize", handleResizeOffcanvas);
  window.addEventListener("orientationchange", handleResizeOffcanvas);
  handleResizeOffcanvas();
  // ⬆️⬆️⬆️  FIN OFFCANVAS RESPONSIVE FIX  ⬆️⬆️⬆️

  const money = (n, symbol = getSymbol()) =>
    symbol + " " + Number(n || 0).toFixed(2);
  const round2 = (n) => Math.round((n + Number.EPSILON) * 100) / 100;

  // === Totales SUNAT por afectación ===
  function sumCart() {
    let opGravada = 0,
      opExonerada = 0,
      opInafecta = 0,
      igv = 0,
      total = 0;
    for (const item of cart.values()) {
      const brutoLinea = item.precioBruto * item.qty - (item.desc || 0);
      if (item.afect === "10") {
        const base = round2(brutoLinea / (1 + IVA));
        const igvLinea = round2(brutoLinea - base);
        opGravada += base;
        igv += igvLinea;
        total += base + igvLinea;
      } else if (item.afect === "20") {
        opExonerada += brutoLinea;
        total += brutoLinea;
      } else {
        opInafecta += brutoLinea;
        total += brutoLinea;
      }
    }
    return {
      opGravada: round2(opGravada),
      opExonerada: round2(opExonerada),
      opInafecta: round2(opInafecta),
      igv: round2(igv),
      total: round2(total),
    };
  }

  const numALetras = (valor, currency = "SOLES") => {
    // Conversión rápida a letras ES-PE (simplificada)
    const entero = Math.floor(valor);
    const cent = Math.round((valor - entero) * 100);
    const unidades = [
      "",
      "UNO",
      "DOS",
      "TRES",
      "CUATRO",
      "CINCO",
      "SEIS",
      "SIETE",
      "OCHO",
      "NUEVE",
      "DIEZ",
      "ONCE",
      "DOCE",
      "TRECE",
      "CATORCE",
      "QUINCE",
      "DIECISÉIS",
      "DIECISIETE",
      "DIECIOCHO",
      "DIECINUEVE",
      "VEINTE",
    ];
    const decenasT = [
      "",
      "DIEZ",
      "VEINTE",
      "TREINTA",
      "CUARENTA",
      "CINCUENTA",
      "SESENTA",
      "SETENTA",
      "OCHENTA",
      "NOVENTA",
    ];
    const centenasT = [
      "",
      "CIENTO",
      "DOSCIENTOS",
      "TRESCIENTOS",
      "CUATROCIENTOS",
      "QUINIENTOS",
      "SEISCIENTOS",
      "SETECIENTOS",
      "OCHOCIENTOS",
      "NOVECIENTOS",
    ];

    function cientos(n) {
      if (n === 0) return "";
      if (n === 100) return "CIEN";
      const c = Math.floor(n / 100),
        r = n % 100;
      let out = c ? centenasT[c] + (r ? " " : "") : "";
      return out + decenas(r);
    }

    function decenas(n) {
      if (n <= 20) return unidades[n];
      const d = Math.floor(n / 10),
        u = n % 10;
      if (u === 0) return decenasT[d];
      if (d === 2)
        return (
          "VEINTI" +
          (u === 2
            ? "DÓS"
            : u === 3
            ? "TRÉS"
            : u === 6
            ? "SÉIS"
            : unidades[u].toLowerCase())
        );
      return decenasT[d] + " Y " + unidades[u];
    }

    function miles(n) {
      if (n < 1000) return cientos(n);
      const m = Math.floor(n / 1000),
        r = n % 1000;
      const mtxt = m === 1 ? "MIL" : cientos(m) + " MIL";
      return mtxt + (r ? " " + cientos(r) : "");
    }

    function millones(n) {
      if (n < 1000000) return miles(n);
      const mm = Math.floor(n / 1000000),
        r = n % 1000000;
      const mtxt = mm === 1 ? "UN MILLÓN" : miles(mm) + " MILLONES";
      return mtxt + (r ? " " + miles(r) : "");
    }
    const letras = millones(entero) || "CERO";
    const cents = cent < 10 ? "0" + cent : cent;
    return `SON: ${letras} Y ${cents}/100 ${currency}`;
  };

  function syncTotalsUI() {
    // Totales en PEN (base)
    const t = sumCart();
    const rate = getRate(); // PEN -> moneda seleccionada
    const sym = getSymbol();
    const toMon = (x) => Math.round((x * rate + Number.EPSILON) * 100) / 100;

    els.lblOpGravada.textContent = money(toMon(t.opGravada), sym);
    els.lblOpExonerada.textContent = money(toMon(t.opExonerada), sym);
    els.lblOpInafecta.textContent = money(toMon(t.opInafecta), sym);
    els.lblImpuesto.textContent = money(toMon(t.igv), sym);
    els.lblImpuestoM.textContent = money(toMon(t.igv), sym);
    els.lblTotal.textContent = money(toMon(t.total), sym);
    els.lblTotalM.textContent = money(toMon(t.total), sym);
    els.pagoTotal.textContent = money(toMon(t.total), sym);

    els.lblMontoLetras.textContent = numALetras(
      toMon(t.total),
      getCurrency() === "USD" ? "DÓLARES AMERICANOS" : "SOLES"
    );

    els.cartItemsCount.textContent = [...cart.values()].reduce(
      (a, c) => a + c.qty,
      0
    );
    els.badgeCartCount.textContent = els.cartItemsCount.textContent;

    document
      .querySelectorAll(".currency-symbol")
      .forEach((el) => (el.textContent = sym));

    document.querySelectorAll(".btnMontoRapido").forEach((btn) => {
      const m = Number(btn.dataset.monto || 0);
      btn.textContent = `${sym}${m}`;
    });

    calcVuelto();
  }

  const renderCart = () => {
    const buildList = (target) => {
      target.innerHTML = "";

      for (const item of cart.values()) {
        const brutoLinea = item.precioBruto * item.qty - (item.desc || 0);
        const base =
          item.afect === "10" ? round2(brutoLinea / (1 + IVA)) : brutoLinea;
        const igvLinea = item.afect === "10" ? round2(base * IVA) : 0;
        const totalLinea = round2(base + igvLinea);

        const show = (x) =>
          money(Math.round((x * getRate() + Number.EPSILON) * 100) / 100);
        const sym = getSymbol();

        const li = document.createElement("li");
        li.className = "list-group-item";

        li.innerHTML = `
  <div class="card border-0 shadow-sm rounded-3 cart-item" data-id="${item.id}">
    <div class="card-body py-3">
      <div class="row g-3 align-items-center">

        <div class="col-12 col-md">
          <h6 class="mb-1 text-truncate" title="${item.nombre}">
            ${item.nombre}
          </h6>
          <div class="d-flex flex-wrap gap-2 mt-1">
            <span class="badge rounded-pill bg-light text-dark border" title="Código SKU">
              <i class="fa fa-barcode me-1"></i> SKU: ${item.sku || "-"}
            </span>
            <span class="badge rounded-pill bg-light text-dark border" title="Unidad de medida">
              <i class="fa fa-cubes me-1"></i> UM: ${item.um}
            </span>
            <span class="badge rounded-pill bg-light text-dark border" title="Afectación">
              <i class="fa fa-info-circle me-1"></i>
              Afect: ${
                item.afect === "10"
                  ? "Gravado"
                  : item.afect === "20"
                  ? "Exonerado"
                  : "Inafecto"
              }
            </span>
          </div>
        </div>


        <div class="col-sm-auto">
          <div class="input-group pill-input-group mb-2">
          <span type="button" class="input-group-text btnQtyMinus" data-id="${
            item.id
          }" aria-label="Disminuir cantidad de ${
          item.nombre
        }"><i class="fa fa-minus"></i></span>
            <input type="number"
                   min="1"
                   class="form-control text-center qty-input"
                   value="${item.qty}"
                   data-id="${item.id}"
                   aria-label="Cantidad de ${item.nombre}">
                   <span type="button" class="input-group-text btnQtyPlus" data-id="${
                     item.id
                   }" aria-label="Disminuir cantidad de ${
          item.nombre
        }"><i class="fa fa-plus"></i></span>
          </div>
        </div>

        <!-- Precio total + acciones -->
        <div class="col-12 col-sm-auto ms-sm-auto order-2 order-sm-3 d-flex align-items-center gap-2">
          <span class="badge bg-primary fs-6 px-3" aria-label="Total de la línea">
            ${show(totalLinea)}
          </span>
          <button class="btn btn-outline-danger btn-sm btnRemove" data-id="${
            item.id
          }" title="Quitar del carrito"
                  aria-label="Quitar ${item.nombre}">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </div>

      <hr class="my-3">

      <!-- Ajustes de línea -->
      <div class="row g-2 align-items-center">
        <!-- Descuento -->
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1" for="desc-${item.id}">
            <i class="fa fa-tag me-1"></i> Descuento
          </label>
          <div class="input-group input-group-sm">
            <span class="input-group-text currency-symbol">${sym}</span>
            <input id="desc-${
              item.id
            }" type="number" class="form-control inpDesc"
                   placeholder="0.00" step="0.01" min="0"
                   value="${item.desc || 0}" data-id="${item.id}"
                   aria-describedby="help-desc-${item.id}">
          </div>
          <div id="help-desc-${item.id}" class="form-text small text-muted">
            Aplica descuento a esta línea.
          </div>
        </div>

        <!-- Afectación -->
        <div class="col-12 col-md-4">
          <label class="form-label small mb-1" for="afect-${item.id}">
            <i class="fa fa-balance-scale me-1"></i> Afectación
          </label>
          <select id="afect-${
            item.id
          }" class="form-select form-select-sm selAfect" data-id="${item.id}">
            <option value="10" ${
              item.afect === "10" ? "selected" : ""
            }>10 Gravado</option>
            <option value="20" ${
              item.afect === "20" ? "selected" : ""
            }>20 Exonerado</option>
            <option value="30" ${
              item.afect === "30" ? "selected" : ""
            }>30 Inafecto</option>
          </select>
        </div>

        <!-- Resumen Base / IGV -->
        <div class="col-12 col-md-4 text-md-end">
          <div class="small text-muted">
            Base: <strong>${show(base)}</strong>
            ${
              item.afect === "10"
                ? ` • IGV: <strong>${show(igvLinea)}</strong>`
                : ""
            }
          </div>
        </div>
      </div>
    </div>
  </div>
`;

        target.appendChild(li);
      }
    };

    buildList(els.listaCarrito);
    buildList(els.listaCarritoMobile);
    syncTotalsUI();
  };

  const addToCart = (prod) => {
    const id = prod.dataset.id;
    const precio = Number(prod.dataset.precio); // bruto (con IGV si afect=10)
    const nombre = prod.dataset.nombre;
    const sku = prod.dataset.sku;
    const afect = prod.dataset.afect || "10";
    const um = prod.dataset.um || "NIU";
    if (cart.has(id)) {
      cart.get(id).qty += 1;
    } else {
      cart.set(id, {
        id,
        nombre,
        sku,
        precioBruto: precio,
        qty: 1,
        desc: 0,
        afect,
        um,
      });
    }
    renderCart();
  };

  // Eventos catálogo
  els.grid.addEventListener("click", (e) => {
    const btn = e.target.closest(".btnAdd");
    if (!btn) return;
    const card = btn.closest(".product-card");
    addToCart(card);
  });

  // Filtros categoría
  document.querySelectorAll(".category-scroller .btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".category-scroller .btn")
        .forEach((b) =>
          b.classList.replace("btn-primary", "btn-outline-primary")
        );
      btn.classList.replace("btn-outline-primary", "btn-primary");
      const cat = btn.dataset.cat;
      document
        .querySelectorAll("#gridProductos .product-card")
        .forEach((card) => {
          const show = cat === "all" || card.dataset.cat === cat;
          card.style.display = show ? "" : "none";
        });
    });
  });

  // Buscar por texto
  els.inputBuscar.addEventListener("input", () => {
    const q = els.inputBuscar.value.trim().toLowerCase();
    document
      .querySelectorAll("#gridProductos .product-card")
      .forEach((card) => {
        const name = (card.dataset.nombre || "").toLowerCase();
        const sku = (card.dataset.sku || "").toLowerCase();
        card.parentElement.style.display =
          name.includes(q) || sku.includes(q) ? "" : "none";
      });
  });

  // Acciones carrito
  const handleCartClick = (e) => {
    const id = e.target.closest("[data-id]")?.dataset.id;
    if (!id) return;

    if (e.target.closest(".btnRemove")) {
      cart.delete(id);
      renderCart();
      return;
    }
    if (e.target.closest(".btnQtyPlus")) {
      if (cart.has(id)) {
        cart.get(id).qty += 1;
        renderCart();
      }
      return;
    }
    if (e.target.closest(".btnQtyMinus")) {
      if (cart.has(id)) {
        cart.get(id).qty = Math.max(1, (cart.get(id).qty || 1) - 1);
        renderCart();
      }
      return;
    }
  };

  const handleQtyChange = (e) => {
    if (e.target.classList.contains("qty-input")) {
      const id = e.target.dataset.id;
      const qty = Math.max(1, Number(e.target.value) || 1);
      if (cart.has(id)) {
        cart.get(id).qty = qty;
        renderCart();
      }
    }
    if (e.target.classList.contains("inpDesc")) {
      const id = e.target.dataset.id;
      const desc = Math.max(0, Number(e.target.value) || 0);
      if (cart.has(id)) {
        cart.get(id).desc = desc;
        renderCart();
      }
    }
    if (e.target.classList.contains("selAfect")) {
      // <-- mover aquí
      const id = e.target.dataset.id;
      if (cart.has(id)) {
        cart.get(id).afect = e.target.value;
        renderCart();
      }
    }
  };

  els.listaCarrito.addEventListener("click", handleCartClick);
  els.listaCarritoMobile.addEventListener("click", handleCartClick);
  els.listaCarrito.addEventListener("change", handleQtyChange);
  els.listaCarritoMobile.addEventListener("change", handleQtyChange);

  els.btnVaciar.addEventListener("click", () => {
    if (confirm("¿Vaciar carrito?")) {
      cart.clear();
      renderCart();
    }
  });

  // Atajos
  document.addEventListener("keydown", (e) => {
    // Evitar repetición cuando se mantiene la tecla
    if (e.repeat) return;

    switch (e.code) {
      case "F1":
        e.preventDefault();
        document.getElementById("inputBuscar")?.focus();
        break;

      case "F2":
        e.preventDefault();
        showModalCompat(els.modalPago);
        break;

      case "F4":
        e.preventDefault();
        els.btnVaciar?.click();
        break;

      case "Escape":
        // Cerrar cualquier modal u offcanvas abierto
        const openModal = document.querySelector(".modal.show");
        const openCanvas = document.querySelector(".offcanvas.show");
        if (openModal && B?.Modal) {
          B.Modal.getOrCreateInstance(openModal).hide();
          e.preventDefault();
        } else if (openCanvas && B?.Offcanvas) {
          B.Offcanvas.getOrCreateInstance(openCanvas).hide();
          e.preventDefault();
        }
        break;
    }
  });

  function calcVuelto() {
    const activeMetodo =
      document.querySelector("#pagoTabs .nav-link.active")?.dataset.metodo ||
      "efectivo";
    const totalMon =
      Math.round((sumCart().total * getRate() + Number.EPSILON) * 100) / 100;
    const sym = getSymbol();
    if (activeMetodo !== "efectivo") {
      els.lblVuelto.textContent = money(0, sym);
      return;
    }
    const recibido = Number(els.inputRecibido.value || 0);
    const vuelto = Math.max(0, recibido - totalMon);
    els.lblVuelto.textContent = money(vuelto, sym);
  }

  // 2) detectar método activo por TAB (3A)
  const pagoTabs = document.getElementById("pagoTabs");
  pagoTabs?.addEventListener("shown.bs.tab", () => {
    calcVuelto();
  });
  els.modalPago?.addEventListener("shown.bs.modal", calcVuelto);

  // 3) listeners de montos / botones rápidos (3D)
  document
    .querySelectorAll(
      "#inputRecibido, #inputMontoTarjeta, #inputMixtoEfectivo, #inputMixtoTarjeta"
    )
    .forEach((i) => i?.addEventListener("input", calcVuelto));

  document.querySelectorAll(".btnMontoRapido").forEach((b) =>
    b.addEventListener("click", () => {
      els.inputRecibido.value = Number(b.dataset.monto || 0);
      calcVuelto();
    })
  );

  // === Confirmar pago -> armar payload para tu endpoint PHP de emisión ===
  document.getElementById("btnConfirmarPago").addEventListener("click", () => {
    if (cart.size === 0) {
      alert("El carrito está vacío.");
      return;
    }
    // Validaciones mínimas SUNAT
    const tipoCPE = document.getElementById("cpeTipo").value; // 01/03
    const serie = document.getElementById("cpeSerie").value.trim();
    const correlativo = document.getElementById("cpeCorrelativo").value.trim();
    const tipoDocCli = document.getElementById("cliTipoDoc").value;
    const numDocCli = document.getElementById("cliNumDoc").value.trim();
    const razonCli = document.getElementById("cliRazon").value.trim();

    if (!serie || !correlativo) {
      alert("Serie y correlativo son obligatorios.");
      return;
    }
    if (tipoCPE === "01" && tipoDocCli !== "6") {
      alert("Para FACTURA (01) el cliente debe tener RUC (tipo 6).");
      return;
    }
    if (!numDocCli || !razonCli) {
      alert("Completa documento y nombre/razón social del cliente.");
      return;
    }

    const moneda = getCurrency();
    const tipoCambio =
      moneda === "USD"
        ? Number(document.getElementById("cpeTipoCambio").value || 0)
        : null;

    const t = sumCart(); // en PEN
    const rate = getRate();
    const toMon = (x) => Math.round((x * rate + Number.EPSILON) * 100) / 100;

    // Calcular datos de pago; si hay inconsistencia, getPagoData dispara alert y lanzaba error.
    // Aquí atrapamos el error para NO ensuciar la consola y simplemente abortar.
    let pagoData;
    try {
      pagoData = getPagoData();
    } catch (e) {
      return; // ya se mostró el alert dentro de getPagoData
    }

    // Ítems SUNAT-friendly (valores en PEN; tu backend puede convertir)
    const items = [...cart.values()].map((it, idx) => {
      const brutoLinea = round2(it.precioBruto * it.qty - (it.desc || 0));
      let valorUnitario = it.precioBruto; // con IGV si 10; se recalcula
      let base = brutoLinea,
        igvLinea = 0,
        precioUnitario = it.precioBruto;
      if (it.afect === "10") {
        const unitBase = round2(it.precioBruto / (1 + IVA));
        valorUnitario = unitBase; // sin IGV
        base = round2(brutoLinea / (1 + IVA));
        igvLinea = round2(base * IVA);
        precioUnitario = round2(unitBase * (1 + IVA)); // con IGV
      }
      return {
        item: idx + 1,
        producto_id: it.id,
        descripcion: it.nombre,
        sku: it.sku,
        um: it.um,
        cantidad: it.qty,
        afectacion_igv: it.afect,
        valor_unitario_pen: valorUnitario,
        precio_unitario_pen: precioUnitario,
        descuento_pen: it.desc || 0,
        valor_venta_pen: base,
        igv_pen: igvLinea,
        total_linea_pen: round2(base + igvLinea),

        // opcional: valores en moneda de operación
        valor_unitario_mon: toMon(valorUnitario),
        precio_unitario_mon: toMon(precioUnitario),
        descuento_mon: toMon(it.desc || 0),
        valor_venta_mon: toMon(base),
        igv_mon: toMon(igvLinea),
        total_linea_mon: toMon(base + igvLinea),
      };
    });

    const payload = {
      cabecera: {
        tipo_comprobante: document.getElementById("cpeTipo").value,
        serie: document.getElementById("cpeSerie").value.trim(),
        correlativo: document.getElementById("cpeCorrelativo").value.trim(),
        fecha_emision: document.getElementById("cpeFecha").value,
        moneda: moneda, // PEN / USD
        tipo_cambio: tipoCambio, // null si PEN
        forma_pago:
          document.querySelector('input[name="fpago"]:checked')?.value ||
          "Contado",
        plazo_credito_dias: document.getElementById("fpPlazo").value || null,
        cliente: {
          tipo_doc: document.getElementById("cliTipoDoc").value,
          num_doc: document.getElementById("cliNumDoc").value.trim(),
          razon_social: document.getElementById("cliRazon").value.trim(),
          direccion: document.getElementById("cliDireccion").value || null,
        },
      },
      // Totales en PEN (base) y en moneda de operación
      totales_pen: {
        op_gravada: t.opGravada,
        op_exonerada: t.opExonerada,
        op_inafecta: t.opInafecta,
        igv: t.igv,
        total: t.total,
      },
      totales_mon: {
        op_gravada: toMon(t.opGravada),
        op_exonerada: toMon(t.opExonerada),
        op_inafecta: toMon(t.opInafecta),
        igv: toMon(t.igv),
        total: toMon(t.total),
        monto_letras: document.getElementById("lblMontoLetras").textContent,
      },
      items,
      pago: pagoData, // <-- usa la variable capturada
    };

    console.log("> Payload CPE listo:", payload);
    alert(
      "Simulación: payload listo en consola. Enviar a tu endpoint PHP para registrar y emitir."
    );
    cart.clear();
    renderCart();
    hideModalCompat(els.modalPago);
  });

  function getPagoData() {
    const metodo =
      document.querySelector("#pagoTabs .nav-link.active")?.dataset.metodo ||
      "efectivo";
    const sym = getSymbol();
    const totalMon =
      Math.round((sumCart().total * getRate() + Number.EPSILON) * 100) / 100;
    const val = (id) => Number(document.getElementById(id)?.value || 0);

    if (metodo === "efectivo") {
      const recibido = val("inputRecibido");
      return {
        metodo,
        recibido,
        vuelto: round2(recibido - totalMon),
        moneda: getCurrency(),
      };
    }
    if (metodo === "tarjeta") {
      return {
        metodo,
        referencia: document.getElementById("inputRefTarjeta").value || null,
        monto: val("inputMontoTarjeta"),
        moneda: getCurrency(),
      };
    }
    if (metodo === "mixto") {
      const efectivo = val("inputMixtoEfectivo");
      const tarjeta = val("inputMixtoTarjeta");
      const suma = round2(efectivo + tarjeta);
      if (suma !== round2(totalMon)) {
        alert(
          `En pago mixto, la suma (${money(
            suma,
            sym
          )}) debe igualar el total (${money(totalMon, sym)}).`
        );
        throw new Error("Pago mixto no cuadra");
      }
      return { metodo, efectivo, tarjeta, moneda: getCurrency() };
    }
    if (metodo === "nota_credito") {
      const ref = document.getElementById("inputNCRef").value.trim();
      const monto = val("inputNCMonto");
      if (!ref) {
        alert("Ingresa el N° de la nota de crédito.");
        throw new Error("NC sin referencia");
      }
      if (monto <= 0 || monto > totalMon) {
        alert("Monto de nota de crédito inválido.");
        throw new Error("NC monto inválido");
      }
      return { metodo, referencia: ref, monto, moneda: getCurrency() };
    }
    if (metodo === "vale_saldo") {
      const ref = document.getElementById("inputValeRef").value.trim();
      const monto = val("inputValeMonto");
      if (!ref) {
        alert("Ingresa el código de vale / ID de saldo.");
        throw new Error("Vale sin referencia");
      }
      if (monto <= 0 || monto > totalMon) {
        alert("Monto de vale inválido.");
        throw new Error("Vale monto inválido");
      }
      return { metodo, referencia: ref, monto, moneda: getCurrency() };
    }
    // fallback
    return {
      metodo: "efectivo",
      recibido: 0,
      vuelto: 0,
      moneda: getCurrency(),
    };
  }

  // Inicial
  document.querySelector('[data-cat="all"]')?.click();
  renderCart();
})();

// ======== CPE: tipos, serie y correlativo (auto) ========
(() => {
  const $cpeTipo = document.getElementById("cpeTipo");
  const $cpeSerie = document.getElementById("cpeSerie");
  const $cpeCorr = document.getElementById("cpeCorrelativo");

  const safeSwal = (opts = {}) => {
    if (window.Swal && typeof Swal.fire === "function") {
      return Swal.fire({
        icon: opts.icon || "error",
        title: opts.title || "No se ha podido conectar",
        text: opts.text || "",
        confirmButtonText: opts.confirmButtonText || "Entendido",
      });
    }
    alert(
      (opts.title || "No se ha podido conectar") +
        (opts.text ? ": " + opts.text : "")
    );
  };

  async function apiVentas(url) {
    const ctrl = new AbortController();
    const t = setTimeout(() => ctrl.abort(), 10000); // 10s
    try {
      const r = await fetch(
        url + (url.includes("?") ? "&" : "?") + "_=" + Date.now(),
        { signal: ctrl.signal, headers: { Accept: "application/json" } }
      );
      if (!r.ok) throw new Error("HTTP " + r.status);
      return await r.json();
    } finally {
      clearTimeout(t);
    }
  }

  // 1) Cargar tipos CPE solo si el select está vacío
  async function cargarTiposCPE() {
    if (!$cpeTipo) return false;

    // Si ya tienes opciones server-side (p. ej. “Boleta (03)”), NO tocar nada.
    if ($cpeTipo.options.length > 0 && $cpeTipo.value) {
      return true;
    }

    try {
      const r = await apiVentas(
        "assets/ajax/Ventas_ajax.php?action=listarTiposCPE"
      );
      if (
        !r ||
        r.success !== true ||
        !Array.isArray(r.data) ||
        r.data.length === 0
      ) {
        console.error("listarTiposCPE respuesta cruda:", r);
        safeSwal({
          title: "No se ha podido conectar",
          text: r?.message
            ? String(r.message)
            : "No fue posible obtener los tipos de CPE.",
        });
        // No vaciar opciones existentes
        return $cpeTipo.options.length > 0 && !!$cpeTipo.value;
      }

      // Limpiar solo si realmente llegó data válida y vamos a poblar
      $cpeTipo.innerHTML = "";
      r.data.forEach((t) => {
        if (!t || !t.codigo || !t.Descripcion) return;
        const opt = document.createElement("option");
        opt.value = String(t.codigo).trim(); // '01','03','07','08'
        opt.textContent = `${t.Descripcion} (${opt.value})`;
        $cpeTipo.appendChild(opt);
      });

      if ($cpeTipo.options.length === 0) {
        safeSwal({
          title: "No se ha podido conectar",
          text: "La lista de tipos CPE llegó vacía.",
        });
        return false;
      }

      // Forzar Boleta (03) si existe
      const has03 = [...$cpeTipo.options].some((o) => o.value === "03");
      $cpeTipo.value = has03 ? "03" : $cpeTipo.options[0].value;
      return true;
    } catch (e) {
      console.error("Error listarTiposCPE:", e);
      safeSwal({
        title: "No se ha podido conectar",
        text: "Error de red al obtener los tipos de CPE.",
      });
      // Mantener lo que hubiera en el select
      return $cpeTipo.options.length > 0 && !!$cpeTipo.value;
    }
  }

  // 2) Obtener serie y correlativo exactos (no inventar)
  async function sugerirSerieYCorrelativo(tipo) {
    if (!$cpeSerie || !$cpeCorr || !tipo) return false;
    try {
      const r = await apiVentas(
        `assets/ajax/Ventas_ajax.php?action=sugerirSerieYCorrelativo&tipo=${encodeURIComponent(
          tipo
        )}`
      );

      if (!r || r.success !== true || !r.data) {
        // Silenciamos el console.error para no “ensuciar” mientras se escribe DNI
        // console.error("sugerirSerieYCorrelativo respuesta cruda:", r);
        safeSwal({
          title: "No se pudo obtener la serie/correlativo",
          text: r?.message ? String(r.message) : "Inténtalo al finalizar.",
        });
        return false;
      }

      const d = r.data;
      const serieOk = typeof d.serie === "string" && d.serie.trim() !== "";
      const corrNum = Number.isInteger(d.correlativo) ? d.correlativo : null;
      const corrFmt =
        typeof d.correlativo_fmt === "string" && d.correlativo_fmt.trim() !== ""
          ? d.correlativo_fmt
          : null;

      if (!serieOk || (!corrNum && !corrFmt)) {
        console.error("Datos incompletos de serie/correlativo:", d);
        safeSwal({
          title: "No se ha podido conectar",
          text: "La respuesta no contiene serie/correlativo válidos.",
        });
        return false;
      }

      $cpeSerie.value = d.serie;
      $cpeCorr.value = corrFmt || String(corrNum);
      return true;
    } catch (e) {
      console.error("Error sugerirSerieYCorrelativo:", e);
      safeSwal({
        title: "No se ha podido conectar",
        text: "Error de red al obtener la serie y el correlativo.",
      });
      return false;
    }
  }

  // 3) Cambio de tipo -> pedir serie/correlativo exactos. Si falla, limpiar inputs.
  if ($cpeTipo) {
    $cpeTipo.addEventListener("change", async (ev) => {
      const val = ev.target.value || "";
      const ok = await sugerirSerieYCorrelativo(val);
      if (!ok) {
        $cpeSerie && ($cpeSerie.value = "");
        $cpeCorr && ($cpeCorr.value = "");
      }
    });
  }

  // 4) Al cargar: si hay opciones ya (Boleta por defecto), no pisarlas;
  //    luego, independientemente, intenta traer serie/correlativo exactos.
  const modalPago = document.getElementById("modalPago");
  modalPago?.addEventListener("shown.bs.modal", async () => {
    const tiposOk = await cargarTiposCPE();
    if ($cpeTipo && $cpeTipo.value) {
      const ok = await sugerirSerieYCorrelativo($cpeTipo.value);
      if (!ok) {
        $cpeSerie && ($cpeSerie.value = "");
        $cpeCorr && ($cpeCorr.value = "");
      }
    }
  });
})();

// === Monedas dinámicas ===
const $cpeMoneda = document.getElementById("cpeMoneda");
const currencySymbols = {
  PEN: "S/",
  USD: "$",
  EUR: "€",
  CLP: "$",
  MXN: "$",
  COP: "$",
  BRL: "R$",
  ARS: "$",
}; // fallback

async function cargarMonedas() {
  try {
    const r = await fetch(
      "assets/ajax/tipo_moneda_ajax.php?action=obtenerMonedas",
      { headers: { Accept: "application/json" } }
    );
    const j = await r.json();
    if (!j.success || !Array.isArray(j.data) || j.data.length === 0)
      throw new Error("Lista vacía");

    // Limpiar y poblar
    $cpeMoneda.innerHTML = "";
    // PEN primero si existe
    const ordenadas = [...j.data].sort((a, b) =>
      a.Abreviatura_TipoMoneda === "PEN"
        ? -1
        : b.Abreviatura_TipoMoneda === "PEN"
        ? 1
        : 0
    );
    for (const m of ordenadas) {
      const code = (m.Abreviatura_TipoMoneda || "").trim().toUpperCase();
      const text = `${code} - ${m.Moneda || ""}`.trim();
      if (!code) continue;
      const opt = document.createElement("option");
      opt.value = code;
      opt.textContent = text;
      $cpeMoneda.appendChild(opt);
    }
    // Default: PEN si existe
    if ([...$cpeMoneda.options].some((o) => o.value === "PEN"))
      $cpeMoneda.value = "PEN";

    // Actualiza símbolos
    const val = $cpeMoneda.value;
    if (val && currencySymbols[val]) {
      document
        .querySelectorAll(".currency-symbol")
        .forEach((el) => (el.textContent = currencySymbols[val]));
    }
  } catch (e) {
    console.warn("No se pudo cargar monedas:", e);
    // Fallback mínimo a PEN si todo falla
    if (!$cpeMoneda.value) {
      $cpeMoneda.innerHTML = '<option value="PEN">PEN - Soles</option>';
      $cpeMoneda.value = "PEN";
    }
  } finally {
    // Re-sincroniza totales/símbolos
    window.requestAnimationFrame(() => {
      window.syncTotalsUIPOS?.();
    });
  }
}

// Hook inicial
document.addEventListener("DOMContentLoaded", cargarMonedas);

// === Buscar cliente por código interno ===
const $cliCodigo = document.getElementById("cliCodigo");
const $btnBuscarCliente = document.getElementById("btnBuscarCliente");

async function cargarClientePorId(id) {
  if (!id) return;
  try {
    const r = await fetch(
      `assets/ajax/cliente_ajax.php?action=buscar&id_cliente=${encodeURIComponent(
        id
      )}`,
      { headers: { Accept: "application/json" } }
    );
    const j = await r.json();
    if (!j.success || !j.data) {
      Swal?.fire({ icon: "warning", title: "Cliente no encontrado" }) ||
        alert("Cliente no encontrado");
      return;
    }
    document.getElementById("cliTipoDoc").value =
      (j.data.id_doc_identificacion || "").toString() || "1";
    document.getElementById("cliNumDoc").value =
      j.data.nro_documento || j.data.ruc || "";
    document.getElementById("cliRazon").value =
      j.data.razon_social || j.data.nombre_comercial || "";
    document.getElementById("cliDireccion").value = j.data.direccion || "";
    // ✅ reafirmamos el id en el campo código
    document.getElementById("cliCodigo").value =
      j.data.id_cliente || j.data.id || id || "";
  } catch (e) {
    Swal?.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo cargar el cliente.",
    }) || alert("No se pudo cargar el cliente.");
  }
}

$btnBuscarCliente?.addEventListener("click", () => {
  const id = ($cliCodigo.value || "").trim();
  cargarClientePorId(id);
});
$cliCodigo?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    $btnBuscarCliente?.click();
  }
});

const $cliTipoDoc = document.getElementById("cliTipoDoc");
const $cliNumDoc = document.getElementById("cliNumDoc");
const $btnConsultarDoc = document.getElementById("btnConsultarDoc");

const API_CLIENTES = "assets/ajax/cliente_ajax.php"; // reutiliza tu router
async function consultarDocumento() {
  const tipo = document.getElementById("cliTipoDoc").value; // "6"=RUC, "1"=DNI
  const nro = (document.getElementById("cliNumDoc").value || "").trim();

  // Validaciones mínimas por tipo
  if (tipo === "6" && nro.length !== 11) {
    return (
      Swal?.fire({ icon: "warning", title: "RUC inválido" }) ||
      alert("RUC inválido")
    );
  }
  if (tipo === "1" && nro.length !== 8) {
    return (
      Swal?.fire({ icon: "warning", title: "DNI inválido" }) ||
      alert("DNI inválido")
    );
  }

  // Oculta “guardar” por si ya existía
  hideGuardarClienteBtn();

  try {
    // 1) Buscar primero en tu BD por tipo + número
    const rLocal = await fetch(
      `assets/ajax/cliente_ajax.php?action=buscarPorDocumento&id_doc_identificacion=${encodeURIComponent(
        tipo
      )}&nro_documento=${encodeURIComponent(nro)}`,
      { headers: { Accept: "application/json" } }
    );
    const jLocal = await rLocal.json();

    if (jLocal?.success && jLocal?.data) {
      // Si viene de BD:
      // - Para RUC, valida ACTIVO (si tu back lo trae); para DNI normalmente no hay estado SUNAT
      if (tipo === "6") {
        const estado = String(
          jLocal.data.estadoSunat || jLocal.data.estado || ""
        ).toUpperCase();
        if (estado && estado !== "ACTIVO") {
          return (
            Swal?.fire({
              icon: "warning",
              title: `Estado SUNAT: ${estado}`,
              text: "No es ACTIVO. Intenta otro RUC.",
            }) || alert(`Estado SUNAT: ${estado}`)
          );
        }
      }
      fillClienteForm(jLocal.data);
      document.getElementById("cliNumDoc").value = nro; // no borrar lo digitado
      document.getElementById("cliCodigo").value =
        jLocal.data.id_cliente || jLocal.data.id || jLocal.data.codigo || "";
      // al venir de BD, no mostramos botón guardar
      hideGuardarClienteBtn();
      return;
    }

    // 2) Si no existe en BD → consultar API externa por tipo
    if (tipo === "6") {
      // === RUC: SUNAT ===
      const rApi = await fetch(
        `assets/ajax/cliente_ajax.php?action=consultarSunatRUC&ruc=${encodeURIComponent(
          nro
        )}`,
        { headers: { Accept: "application/json" } }
      );
      const jApi = await rApi.json();

      if (!jApi?.success || !jApi?.data) {
        // limpiar campos principales al no encontrar nada
        document.getElementById("cliRazon").value = "";
        document.getElementById("cliDireccion").value = "";
        return (
          Swal?.fire({
            icon: "warning",
            title: "No se encontró información de RUC",
          }) || alert("No se encontró información de RUC")
        );
      }

      const estadoApi = String(
        jApi.data.estado || jApi.data.estadoSunat || ""
      ).toUpperCase();
      if (estadoApi && estadoApi !== "ACTIVO") {
        return (
          Swal?.fire({
            icon: "warning",
            title: `Estado SUNAT: ${estadoApi}`,
            text: "No es ACTIVO. Intenta otro RUC.",
          }) || alert(`Estado SUNAT: ${estadoApi}`)
        );
      }

      fillClienteForm(jApi.data);
      document.getElementById("cliNumDoc").value = nro;

      const idFromApi =
        jApi.data.id_cliente || jApi.data.id || jApi.data.codigo || "";
      document.getElementById("cliCodigo").value = idFromApi;

      // si NO tiene id (no está en tu BD), ofrece guardarlo:
      if (!idFromApi) showGuardarClienteBtn(jApi.data);
      else hideGuardarClienteBtn();
      return;
    } else if (tipo === "1") {
      // === DNI: RENIEC === (misma lógica: BD → API → completar → guardar si no existe)
      const rApi = await fetch(
        `assets/ajax/cliente_ajax.php?action=consultarReniecDNI&dni=${encodeURIComponent(
          nro
        )}`,
        { headers: { Accept: "application/json" } }
      );
      const jApi = await rApi.json();

      if (!jApi?.success || !jApi?.data) {
        // limpiar mínimos si no hay
        document.getElementById("cliRazon").value = "";
        // dirección suele no venir en DNI; la dejamos tal cual
        return (
          Swal?.fire({
            icon: "warning",
            title: "No se encontró información del DNI",
          }) || alert("No se encontró información del DNI")
        );
      }

      // Armar “razón social / nombres” a partir de RENIEC
      const nombres = [
        jApi.data.apellidoPaterno,
        jApi.data.apellidoMaterno,
        jApi.data.nombres,
      ]
        .filter(Boolean)
        .join(" ");

      // Completar formulario (DNI normalmente no trae dirección)
      document.getElementById("cliTipoDoc").value = "1";
      document.getElementById("cliNumDoc").value = nro; // mantener lo digitado
      document.getElementById("cliRazon").value = nombres;
      // no forzar dirección para DNI; si tu UI la pide, quedará vacía para editarla

      // Si tu API de DNI llegara a retornar algún id de BD (poco común), úsalo:
      const idFromApi =
        jApi.data.id_cliente || jApi.data.id || jApi.data.codigo || "";
      document.getElementById("cliCodigo").value = idFromApi;

      // Si no existe en BD ⇒ mostrar “Guardar cliente”
      if (!idFromApi) {
        // Pasamos un objeto mínimo por si tu guardado quiere “estado” u otros campos
        showGuardarClienteBtn({
          id_doc_identificacion: "1",
          nro_documento: nro,
          razon_social: nombres,
          direccion: "",
          estado: "ACTIVO", // opcional; tu back puede ignorarlo
        });
      } else {
        hideGuardarClienteBtn();
      }
      return;
    }

    // Otros tipos no soportados
    Swal?.fire({
      icon: "info",
      title: "Tipo doc no soportado para consulta",
    }) || alert("Tipo doc no soportado para consulta");
  } catch (e) {
    console.error(e);
    Swal?.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo consultar el documento.",
    }) || alert("No se pudo consultar el documento.");
  }
}

$btnConsultarDoc?.addEventListener("click", consultarDocumento);
$cliNumDoc?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    consultarDocumento();
  }
});

// === Catálogo paginado (10 por página) ===
let prodPage = 1;
let prodTotalPages = 1;
let prodAbortCtrl = null;
const $grid = document.getElementById("gridProductos");
const $btnMas = document.getElementById("btnMas");
const $inputBuscar = document.getElementById("inputBuscar");

function skeleton(n = 6) {
  const frag = document.createDocumentFragment();
  for (let i = 0; i < n; i++) {
    const d = document.createElement("div");
    d.className = "product-card card-row";
    d.innerHTML = `
      <div class="col-left">
        <div class="title placeholder-glow">
          <span class="placeholder col-8"></span>
        </div>
        <div class="meta placeholder-glow">
          <span class="placeholder col-10"></span>
        </div>
        <div class="chips mt-2">
          <span class="chip placeholder-glow"><span class="placeholder col-6"></span></span>
          <span class="chip placeholder-glow"><span class="placeholder col-3"></span></span>
          <span class="chip placeholder-glow"><span class="placeholder col-4"></span></span>
        </div>
      </div>
      <div class="col-right ms-auto">
        <span class="badge bg-primary"><span class="placeholder col-4"></span></span>
        <button class="btn btn-sm btn-primary btn-pill" disabled>
          <i class="bi bi-plus-lg"></i>
        </button>
      </div>
    `;
    frag.appendChild(d);
  }
  return frag;
}

async function fetchProductos({ search = "", page = 1, id_sede = 0 } = {}) {
  // aborta petición anterior
  prodAbortCtrl?.abort();
  prodAbortCtrl = new AbortController();

  // si no te pasan id_sede, tomamos el del DOM
  const sedeID = Number(id_sede || getSedeID() || 0);

  // ✅ tu forma “correcta” (string con encodeURIComponent)
  const url =
    `assets/ajax/productos_ajax.php?action=buscarProductos` +
    `&page=${page}` +
    `&search=${encodeURIComponent(search)}` +
    (sedeID > 0 ? `&id_sede=${sedeID}` : "");

  if (page === 1) {
    $grid.innerHTML = "";
    $grid.appendChild(skeleton(6));
  } else {
    $btnMas.disabled = true;
  }

  try {
    const r = await fetch(url, {
      signal: prodAbortCtrl.signal,
      headers: { Accept: "application/json" },
    });
    const j = await r.json();
    if (!j.success) throw new Error("Sin éxito");

    prodPage = j.data.current_page || page;
    prodTotalPages = j.data.total_pages || 1;

    if (page === 1) $grid.innerHTML = "";
    renderProductos(j.data.productos || []);

    // botón “mostrar más”
    if (prodPage < prodTotalPages) {
      $btnMas.style.display = "";
      $btnMas.disabled = false;
    } else {
      $btnMas.style.display = "none";
    }
  } catch (e) {
    if (e.name === "AbortError") return;
    console.error("Error fetchProductos:", e);
    if (page === 1)
      $grid.innerHTML =
        '<div class="text-muted">No se pudo cargar el catálogo.</div>';
  }
}

function getPrecioNumber(p) {
  // Prioriza campos que suelen venir del back
  const candidates = [
    p.precio, // "S/ 12.50" o "12.50"
    p.precio_venta,
    p.precioVenta,
    p.precio_con_igv,
    p.precioConIGV,
    p.Precio_venta_igv,
    p.Precio_venta,
  ];
  for (const c of candidates) {
    if (c === undefined || c === null) continue;
    // Soporta "S/ 12,50", "12.50", "12,50"
    const num = parseFloat(
      String(c)
        .replace(/[^\d,\.]/g, "")
        .replace(",", ".")
    );
    if (!Number.isNaN(num) && num > 0) return num;
  }
  return 0; // fallback
}

function renderProductos(list) {
  const MAX_ITEMS = 10;
  const current = $grid.querySelectorAll(".product-card.card-row").length;
  if (current >= MAX_ITEMS) return;

  const room = MAX_ITEMS - current;
  const slice = list.slice(0, room);

  // Lotea el render para mantener la UI fluida
  window.requestAnimationFrame(() => {
    const frag = document.createDocumentFragment();

    for (const p of slice) {
      const otras = Array.isArray(p.otras_sedes) ? p.otras_sedes : [];
      const tooltipHtml = otras.length
        ? otras
            .map(
              (s) =>
                `${s.nombre_sede || "Sede"}<br><strong>${Number(
                  s.stock_actual || 0
                ).toFixed(2)}</strong>`
            )
            .join("<br>")
        : "Sin datos de otras sedes";

      const div = document.createElement("div");
      div.className = "product-card card-row";
      div.dataset.id = p.id_producto_variante;
      div.dataset.sku = p.SKU_Productos || "";
      const precioNum = getPrecioNumber(p);
      div.dataset.precio = precioNum.toFixed(2);
      div.dataset.nombre = p.titulo || "";
      div.dataset.afect = p.tipoIGV || 10;
      div.dataset.um = p.unidadMedida || "NIU";

      div.innerHTML = `
        <div class="col-left">
          <div class="title text-truncate">${p.titulo || "-"}</div>
          <div class="meta">${(p.descripcion || "").trim()}</div>
          <div class="chips mt-2">
            <span class="chip" title="${p.SKU_Productos || "-"}">SKU: ${
        p.SKU_Productos || "-"
      }</span>
            <span class="chip">${p.unidadMedida || "NIU"}</span>
            <span class="chip" data-bs-toggle="tooltip" data-bs-title="${tooltipHtml}">
              Stock: ${Number(p.stock_actual || 0).toFixed(2)}
            </span>
          </div>
        </div>
        <div class="col-right ms-auto">
          <span class="badge bg-primary badge-price">S/ ${getPrecioNumber(
            p
          ).toFixed(2)}</span>
          <button class="btn btn-sm btn-primary btn-pill btnAdd"><i class="bi bi-plus-lg"></i></button>
        </div>
      `;
      frag.appendChild(div);
    }

    $grid.appendChild(frag);
    [].slice
      .call(frag.querySelectorAll('[data-bs-toggle="tooltip"]'))
      .map((el) => new bootstrap.Tooltip(el));
  });
}

// “Mostrar más”
$btnMas?.addEventListener("click", () => {
  if (prodPage < prodTotalPages) {
    fetchProductos({
      search: ($inputBuscar.value || "").trim(),
      page: prodPage + 1,
      id_sede: getSedeID(),
    });
  }
});

// === Búsqueda (más precisa conforme escribe) ===
let searchT = null;

function precisionMatch(q, name, sku) {
  q = (q || "").trim().toLowerCase();
  name = (name || "").toLowerCase();
  sku = (sku || "").toLowerCase();
  if (!q) return true;

  const len = q.length;
  const tokens = q.split(/\s+/).filter(Boolean);

  // <=3: incluye en name o sku
  if (len <= 3) return name.includes(q) || sku.includes(q);

  // 4-5: todos los tokens deben estar en name o sku
  if (len <= 5) {
    return tokens.every((t) => name.includes(t) || sku.includes(t));
  }

  // >=6: token inicia palabra en nombre o SKU empieza con q
  const startsWord = (t) => name.split(/\b/).some((w) => w.startsWith(t)); // aproxima "inicio de palabra"
  return sku.startsWith(q) || tokens.some(startsWord);
}

function aplicarFiltroLocal(q) {
  const cards = document.querySelectorAll("#gridProductos .product-card");
  cards.forEach((card) => {
    const name = card.dataset.nombre || "";
    const sku = card.dataset.sku || "";
    card.style.display = precisionMatch(q, name, sku) ? "" : "none";
  });
}

$inputBuscar?.addEventListener("input", () => {
  clearTimeout(searchT);
  const val = ($inputBuscar.value || "").trim();
  // Dispara fetch rápido (para refrescar catálogo) y filtrado local inmediato
  aplicarFiltroLocal(val);
  searchT = setTimeout(() => {
    fetchProductos({
      search: ($inputBuscar.value || "").trim(),
      page: 1,
      id_sede: getSedeID(),
    });
  }, 150);
});

$inputBuscar?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") {
    e.preventDefault();
    const firstVisible = Array.from(
      document.querySelectorAll("#gridProductos .product-card")
    ).find((c) => c.style.display !== "none");
    if (firstVisible) {
      addToCart(firstVisible);
    } else {
      fetchProductos({
        search: ($inputBuscar.value || "").trim(),
        page: 1,
        id_sede: getSedeID(),
      });
    }
  }
});

// Botón limpiar (si lo tienes en la UI)
document.getElementById("btnLimpiar")?.addEventListener("click", () => {
  $inputBuscar.value = "";
  fetchProductos({ page: 1, id_sede: getSedeID() });
});

// Primera carga: 10 ítems
document.addEventListener("DOMContentLoaded", () => {
  fetchProductos({ page: 1, id_sede: getSedeID() });
});
function fillClienteForm(d) {
  document.getElementById("cliTipoDoc").value = (
    d.id_doc_identificacion || "6"
  ).toString();
  document.getElementById("cliNumDoc").value = d.nro_documento || d.ruc || "";
  document.getElementById("cliRazon").value =
    d.razon_social || d.razonSocial || d.nombreComercial || "";
  document.getElementById("cliDireccion").value =
    d.direccion ||
    [d.direccion, d.departamento, d.provincia, d.distrito]
      .filter(Boolean)
      .join(" - ");

  const cod = d.id_cliente || d.id || d.codigo || "";
  if (cod) document.getElementById("cliCodigo").value = cod;
}

function showGuardarClienteBtn(dataCliente) {
  const input = document.getElementById("cliRazon");
  if (!input) return;
  if (document.getElementById("btnGuardarCliente")) return;

  const btn = document.createElement("button");
  btn.id = "btnGuardarCliente";
  btn.type = "button";
  btn.className = "btn btn-outline-success input-group-text btn-pill";
  btn.innerHTML = '<i class="bi bi-person-plus"></i>';
  btn.title = "Guardar cliente";
  btn.addEventListener("click", () =>
    guardarClienteDesdeFormulario(dataCliente)
  );

  // En tu markup el input ya está dentro de .input-group ⇒ solo insertAfter
  input.insertAdjacentElement("afterend", btn);
}

function hideGuardarClienteBtn() {
  document.getElementById("btnGuardarCliente")?.remove();
}
async function guardarClienteDesdeFormulario(dataAPI) {
  try {
    const payload = {
      id_doc_identificacion: document.getElementById("cliTipoDoc").value, // ← ahora usa el tipo seleccionado (1 o 6)
      nro_documento: document.getElementById("cliNumDoc").value.trim(),
      razon_social: document.getElementById("cliRazon").value.trim(),
      direccion: document.getElementById("cliDireccion").value.trim(),
      estadoSunat: (
        dataAPI?.estado ||
        dataAPI?.estadoSunat ||
        ""
      ).toUpperCase(),
      limite_credito: "0",
      dias_credito: "0",
      condicion_credito: "Contado",
    };

    const r = await fetch(`${API_CLIENTES}?action=registrar`, {
      method: "POST",
      headers: { Accept: "application/json" },
      body: toFormData(payload), // ← usa formdata si tu controlador espera $_POST
    });
    const j = await r.json();
    if (!j.success) throw new Error(j.message || "No se pudo guardar");
    Swal?.fire({ icon: "success", title: "Cliente guardado" }) ||
      alert("Cliente guardado");
    hideGuardarClienteBtn();
  } catch (e) {
    Swal?.fire({ icon: "error", title: "Error", text: e.message }) ||
      alert("Error al guardar");
  }
}

// helper: JSON -> FormData (para $_POST del controller)
function toFormData(obj) {
  const fd = new FormData();
  Object.entries(obj).forEach(([k, v]) => fd.append(k, v ?? ""));
  return fd;
}

function getSedeID() {
  return Number(document.getElementById("id_sede")?.value || 0);
}

document.addEventListener("DOMContentLoaded", () => {
  const tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  tooltipTriggerList.map((el) => new bootstrap.Tooltip(el));
});
