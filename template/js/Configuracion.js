// Vista previa del logo en modal
document
  .getElementById("btnPreviewLogo")
  .addEventListener("click", function () {
    const input = document.getElementById("logoInput");
    const modalImg = document.getElementById("logoPreviewModal");

    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function (e) {
        modalImg.src = e.target.result;
      };
      reader.readAsDataURL(input.files[0]);
    } else {
      modalImg.src = "#";
      alert("Primero selecciona una imagen.");
    }
  });

document.addEventListener("DOMContentLoaded", function () {
  fetch("assets/ajax/empresa_ajax.php?action=obtenerPerfil")
    .then((res) => res.json())
    .then((json) => {
      if (json.success && json.data) {
        const d = json.data;
        document.querySelector('input[name="razon_social"]').value =
          d.razon_social ?? "";
        document.querySelector('input[name="nombre_comercial"]').value =
          d.nombre_comercial ?? "";
        document.querySelector('select[name="id_doc_identificacion"]').value =
          d.id_doc_identificacion ?? "";
        document.querySelector('input[name="nro_documento"]').value =
          d.nro_documento ?? "";
        document.querySelector('input[name="representante"]').value =
          d.representante ?? "";
        document.querySelector('input[name="direccion_fiscal"]').value =
          d.direccion_fiscal ?? "";
        document.querySelector('input[name="telefono"]').value =
          d.telefono ?? "";
        document.querySelector('input[name="email"]').value = d.email ?? "";
        document.querySelector('input[name="web"]').value = d.web ?? "";
        document.querySelector('input[name="logo_path"]').dataset.filename =
          d.logo_path ?? "";
        // ID oculto para update
        if (!document.querySelector('input[name="id_empresa"]')) {
          const hidden = document.createElement("input");
          hidden.type = "hidden";
          hidden.name = "id_empresa";
          hidden.value = d.id_empresa;
          document.getElementById("form_empresa").appendChild(hidden);
        } else {
          document.querySelector('input[name="id_empresa"]').value =
            d.id_empresa;
        }

        // Mostrar logo si existe
        if (d.logo_path) {
          const logoModal = document.getElementById("logoPreviewModal");
          logoModal.src = "uploads/logos/" + d.logo_path;
        }
      }
    });

  cargarSedes();
  cargarTiposComprobante();
});

function cargarSedes() {
  fetch("assets/ajax/sede_ajax.php?action=listarSedes")
    .then((res) => res.json())
    .then((res) => {
      if (res.data && Array.isArray(res.data)) {
        const tbody = document.querySelector("#tabla_sedes table tbody");
        tbody.innerHTML = "";

        res.data.forEach((sede) => {
          let estadoColor = "bg-secondary";
          if (sede.estado_sede === "Activo") estadoColor = "bg-success";
          else if (sede.estado_sede === "Inactivo") estadoColor = "bg-danger";
          else if (sede.estado_sede === "Suspendido")
            estadoColor = "bg-warning";

          const fila = `
<tr>
  <td>${sede.nombre}</td>
  <td>${sede.direccion}</td>
  <td>${sede.telefono}</td>
  <td>${sede.seudonimo_sede}</td>
  <td>
    <select class="form-select form-select-sm btn-pill cambiar-estado" data-id="${
      sede.id_sede
    }">
      <option value="Activo" ${
        sede.estado_sede === "Activo" ? "selected" : ""
      }>Activo</option>
      <option value="Suspendido" ${
        sede.estado_sede === "Suspendido" ? "selected" : ""
      }>Suspendido</option>
      <option value="Inactivo" ${
        sede.estado_sede === "Inactivo" ? "selected" : ""
      }>Inactivo</option>
    </select>
  </td>
  <td>
    <button class="btn btn-sm btn-info btn-air-info btn-pill ver-series" data-id="${
      sede.id_sede
    }">
      <i class="fa fa-list"></i> Ver Series
    </button>
  </td>
  <td>
    <button class="btn btn-sm btn-pill btn-air-primary btn-outline-primary-2x  editar-sede" data-id="${
      sede.id_sede
    }">
      <i class="fa fa-edit"></i>
    </button>
  </td>
</tr>
<tr class="collapse-row" style="display:none;" id="series-row-${sede.id_sede}">
  <td colspan="7">
    <div class="p-3 bg-light border rounded shadow-sm">
      <div class="d-flex justify-content-between mb-2">
        <h6 class="mb-0"><i class="fa fa-file-text me-1"></i> Series y Correlativos de <strong>${
          sede.nombre
        }</strong></h6>
        <button class="btn btn-sm btn-success btn-air-success btn-pill agregar-serie" data-id="${
          sede.id_sede
        }">
          <i class="fa fa-plus"></i> Nueva Serie
        </button>
      </div>
      <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0">
          <thead class="table-light">
            <tr>
              <th>Tipo Comprobante</th>
              <th>Serie</th>
              <th>Correlativo</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="series-body-${sede.id_sede}">
            <!-- Cargado dinámicamente por JS -->
          </tbody>
        </table>
      </div>
    </div>
  </td>
</tr>
`;
          tbody.insertAdjacentHTML("beforeend", fila);
        });

        // Event Listener para cambio de estado
        document.querySelectorAll(".cambiar-estado").forEach((select) => {
          select.addEventListener("change", function () {
            const id = this.dataset.id;
            const estado = this.value;

            const formData = new FormData();
            formData.append("id_sede", id);
            formData.append("estado", estado);

            fetch("assets/ajax/sede_ajax.php?action=cambiarEstado", {
              method: "POST",
              body: formData,
            })
              .then((res) => res.json())
              .then((json) => {
                if (json.success) {
                  alert("Estado actualizado");
                } else {
                  alert("Error al cambiar estado");
                }
              });
          });
        });

        // Placeholder para editar (puedes mejorarlo luego)
        document.querySelectorAll(".editar-sede").forEach((btn) => {
          btn.addEventListener("click", function () {
            const id = this.dataset.id;

            fetch("assets/ajax/sede_ajax.php?action=obtenerSede&id=" + id)
              .then((res) => res.json())
              .then((json) => {
                if (json.success) {
                  const sede = json.data;

                  document.querySelector('input[name="nombre_sede[]"]').value =
                    sede.nombre;
                  document.querySelector(
                    'input[name="seudonimo_sede[]"]'
                  ).value = sede.seudonimo_sede;
                  document.querySelector(
                    'input[name="direccion_sede[]"]'
                  ).value = sede.direccion;
                  document.querySelector(
                    'input[name="telefono_sede[]"]'
                  ).value = sede.telefono;

                  // Insertar input oculto con ID de sede
                  let hiddenInput = document.querySelector(
                    'input[name="id_sede_edit"]'
                  );
                  if (!hiddenInput) {
                    hiddenInput = document.createElement("input");
                    hiddenInput.type = "hidden";
                    hiddenInput.name = "id_sede_edit";
                    document
                      .querySelector("#formulario_sede .card-body")
                      .appendChild(hiddenInput);
                  }
                  hiddenInput.value = sede.id_sede;

                  // Mostrar botón de modificar y ocultar agregar
                  document.getElementById("btnModificarSede").style.display =
                    "inline-block";
                  document.getElementById("btnAgregarSede").style.display =
                    "none";
                } else {
                  alert("Error al obtener la sede.");
                }
              });
          });
        });
      }
    });

  document.addEventListener("click", function (e) {
    if (e.target.closest(".agregar-serie")) {
      const idSede = e.target.closest(".agregar-serie").dataset.id;
      const modal = new bootstrap.Modal(document.getElementById("modalSerie"));
      const form = document.getElementById("formSerie");

      // Limpiar form y setear sede
      form.reset();
      // Copiar el id_empresa desde form_empresa
      const empresaId = document.querySelector('input[name="id_empresa"]');
      if (empresaId) {
        form.id_empresa.value = empresaId.value;
      } else {
        alert(
          "Falta el ID de la empresa. Asegúrate de haber guardado los datos de empresa primero."
        );
        return;
      }
      form.id_sede.value = idSede;
      form.id_serie_documento.value = "";

      modal.show();
    }
  });
}

document
  .getElementById("btnAgregarSede")
  .addEventListener("click", function () {
    const nombre = document
      .querySelector('input[name="nombre_sede[]"]')
      .value.trim();
    const alias = document
      .querySelector('input[name="seudonimo_sede[]"]')
      .value.trim();
    const direccion = document
      .querySelector('input[name="direccion_sede[]"]')
      .value.trim();
    const telefono = document
      .querySelector('input[name="telefono_sede[]"]')
      .value.trim();

    if (!nombre || !direccion) {
      alert("Nombre y dirección son obligatorios.");
      return;
    }

    const formData = new FormData();
    formData.append("nombre", nombre);
    formData.append("seudonimo_sede", alias);
    formData.append("direccion", direccion);
    formData.append("telefono", telefono);

    fetch("assets/ajax/sede_ajax.php?action=agregarSede", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((json) => {
        if (json.success) {
          alert("Sede agregada correctamente.");
          cargarSedes();
          // Limpiar campos del formulario
          document.querySelector('input[name="nombre_sede[]"]').value = "";
          document.querySelector('input[name="seudonimo_sede[]"]').value = "";
          document.querySelector('input[name="direccion_sede[]"]').value = "";
          document.querySelector('input[name="telefono_sede[]"]').value = "";
        } else {
          alert("Error: " + json.message);
        }
      })
      .catch((error) => {
        console.error("Error al guardar sede:", error);
      });
  });

document
  .getElementById("btnModificarSede")
  .addEventListener("click", function () {
    const id = document.querySelector('input[name="id_sede_edit"]').value;
    const nombre = document
      .querySelector('input[name="nombre_sede[]"]')
      .value.trim();
    const alias = document
      .querySelector('input[name="seudonimo_sede[]"]')
      .value.trim();
    const direccion = document
      .querySelector('input[name="direccion_sede[]"]')
      .value.trim();
    const telefono = document
      .querySelector('input[name="telefono_sede[]"]')
      .value.trim();

    if (!id || !nombre || !direccion) {
      alert("Campos obligatorios faltantes.");
      return;
    }

    const formData = new FormData();
    formData.append("id_sede", id);
    formData.append("nombre", nombre);
    formData.append("seudonimo_sede", alias);
    formData.append("direccion", direccion);
    formData.append("telefono", telefono);

    fetch("assets/ajax/sede_ajax.php?action=editarSede", {
      method: "POST",
      body: formData,
    })
      .then((res) => res.json())
      .then((json) => {
        if (json.success) {
          alert("Sede modificada correctamente.");
          cargarSedes();

          // Resetear formulario
          document.querySelector('input[name="nombre_sede[]"]').value = "";
          document.querySelector('input[name="seudonimo_sede[]"]').value = "";
          document.querySelector('input[name="direccion_sede[]"]').value = "";
          document.querySelector('input[name="telefono_sede[]"]').value = "";
          document.querySelector('input[name="id_sede_edit"]').remove();

          document.getElementById("btnModificarSede").style.display = "none";
          document.getElementById("btnAgregarSede").style.display =
            "inline-block";
        } else {
          alert("Error al modificar sede.");
        }
      });
  });

document.addEventListener("click", function (e) {
  if (e.target.closest(".ver-series")) {
    const idSede = e.target.closest(".ver-series").dataset.id;
    const filaSeries = document.getElementById("series-row-" + idSede);
    filaSeries.style.display =
      filaSeries.style.display === "none" ? "table-row" : "none";

    // Solo cargar si está vacía
    if (!filaSeries.dataset.loaded) {
      fetch("assets/ajax/series_ajax.php?action=porSede&id=" + idSede)
        .then((res) => res.json())
        .then((json) => {
          const body = document.getElementById("series-body-" + idSede);
          if (json.success && json.data.length > 0) {
            json.data.forEach((serie) => {
              const estadoTexto = serie.estado == 1 ? "Activo" : "Inactivo";
              const estadoClase =
                serie.estado == 1 ? "bg-success" : "bg-secondary";

              body.innerHTML += `
    <tr>
      <td>${serie.nombre_comprobante}</td>
      <td>${serie.serie}</td>
      <td>${serie.correlativo_actual}</td>
      <td><span class="badge ${estadoClase}">${estadoTexto}</span></td>
      <td>
        <button class="btn btn-sm btn-warning btn-air-warning btn-pill editar-serie" data-id="${serie.id_serie_documento}">
          <i class="fa fa-pencil"></i>
        </button>
      </td>
    </tr>
  `;
            });
          } else {
            body.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Sin series registradas</td></tr>`;
          }
          filaSeries.dataset.loaded = "true";
        });
    }
  }
});

document.addEventListener("click", function (e) {
  if (e.target.closest(".editar-serie")) {
    const idSerie = e.target.closest(".editar-serie").dataset.id;

    fetch("assets/ajax/series_ajax.php?action=obtenerSerie&id=" + idSerie)
      .then((res) => res.json())
      .then((json) => {
        if (json.success) {
          const serie = json.data;
          const form = document.getElementById("formSerie");
          form.reset();

          form.id_serie_documento.value = serie.id_serie_documento;
          form.id_sede.value = serie.id_sede;
          form.serie.value = serie.serie;
          form.correlativo_actual.value = serie.correlativo_actual;
          form.estado.value = serie.estado;

          // Esperar a que el select se llene antes de asignar
          cargarTiposComprobante().then(() => {
            form.tipo_comprobante.value = serie.tipo_comprobante;

            const selected =
              form.tipo_comprobante.options[
                form.tipo_comprobante.selectedIndex
              ];
            document.getElementById("infoExtraSerie").innerHTML = `
          <small class="text-muted d-block mt-1">
            Principal: <strong>${selected.dataset.principal || "-"}</strong>,
            Alternativa: <strong>${selected.dataset.alternativa || "-"}</strong>
          </small>
        `;

            const modal = new bootstrap.Modal(
              document.getElementById("modalSerie")
            );
            modal.show();
          });
        } else {
          alert("No se pudo cargar la serie");
        }
      });
  }
});

function cargarTiposComprobante() {
  return fetch("assets/ajax/tipos_documentos_ajax.php?action=listar")
    .then((res) => res.json())
    .then((json) => {
      if (json.success && Array.isArray(json.data)) {
        const select = document.querySelector(
          'select[name="tipo_comprobante"]'
        );
        select.innerHTML = `<option value="">Seleccione...</option>`;
        json.data.forEach((t) => {
          const option = document.createElement("option");
          option.value = t.CodigoSunat_TipoDocumentoVenta;
          option.textContent = t.Descripcion;
          option.dataset.principal = t.SeriePrincipal;
          option.dataset.alternativa = t.SerieAlternativa;
          select.appendChild(option);
        });

        // Mostrar series al cambiar
        select.addEventListener("change", function () {
          const selected = select.options[select.selectedIndex];
          const span = document.getElementById("infoExtraSerie");
          span.innerHTML = `
            <small class="text-muted d-block mt-1">
              Principal: <strong>${selected.dataset.principal || "-"}</strong>,
              Alternativa: <strong>${
                selected.dataset.alternativa || "-"
              }</strong>
            </small>
          `;
        });
      }
    });
}

document.getElementById("formSerie").addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  // ✅ Línea correcta
  const isEdit = form.id_serie_documento.value !== "";

  fetch(
    `assets/ajax/series_ajax.php?action=${
      isEdit ? "editarSerie" : "registrarSerie"
    }`,
    {
      method: "POST",
      body: formData,
    }
  )
    .then((res) => res.json())
    .then((json) => {
      if (json.success) {
        alert(isEdit ? "Serie actualizada" : "Serie registrada");
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("modalSerie")
        );
        modal.hide();
        cargarSedes(); // Refresca la tabla
      } else {
        alert("Error al guardar serie.");
        console.log(json);
      }
    })
    .catch((err) => {
      alert("Error de red al enviar formulario");
      console.error(err);
    });
});
