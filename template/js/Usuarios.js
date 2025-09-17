// Mapeos dinámicos para asignar colores únicos por valor
const colorPool = [
  "primary",
  "secondary",
  "success",
  "danger",
  "warning",
  "info",
  "dark",
  "light",
];
let docTypeColorMap = {};
let sedeColorMap = {};
let docColorIndex = 0;
let sedeColorIndex = 0;

function asignarColorUnico(mapa, clave, indexRef) {
  if (!mapa[clave]) {
    const color = colorPool[indexRef % colorPool.length];
    mapa[clave] = color;
    indexRef++;
  }
  return mapa[clave];
}

let rolesDisponibles = [];
let sedesDisponibles = [];

function cargarDatosSelect() {
  // Obtener SEDES
  $.getJSON("assets/ajax/sede_ajax.php?action=listarSedes", function (res) {
    sedesDisponibles = res.data ?? [];
  });

  // Obtener ROLES
  $.getJSON("assets/ajax/rol_ajax.php?action=listarRoles", function (res) {
    rolesDisponibles = res.data ?? [];
  });
}

cargarDatosSelect(); // Llamar antes de renderizar el DataTable

const tabla = $("#tabla-usuarios").DataTable({
  language: {
    url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
  },
  responsive: true,
  deferRender: true,
  scrollCollapse: true,
  scroller: true,
  autoWidth: false,
  ajax: {
    url: "assets/ajax/usuario_ajax.php?action=listarUsuarios",
    dataSrc: function (json) {
      if (!json || !Array.isArray(json.data)) {
        console.error("JSON inválido para DataTables:", json);
        return [];
      }
      return json.data;
    },
  },
  columns: [
    {
      data: "id_usuario",
      render: function (id) {
        return `<strong>${id}</strong>`;
      },
      className: "text-center",
    },
    {
      data: null,
      defaultContent: "",
      render: function (d) {
        const nombre = d.nombres_apellidos_usuario ?? "";
        const tipo = d.tipoDocumento ?? "SIN DOCUMENTO";
        const numero = d.numeroDocumento_usuario ?? "";
        const color = asignarColorUnico(docTypeColorMap, tipo, docColorIndex++);

        return `
        ${nombre}<br>
        <span class="badge rounded-pill badge-light-${color} me-1">${tipo}</span>
        <span>${numero}</span>
      `;
      },
    },
    {
      data: "numeroCelular",
      render: function (cel) {
        return cel || "-";
      },
      className: "text-center",
    },
    {
      data: "id_sede",
      render: function (idSede, type, row) {
        const currentId = idSede ?? "";
        const selectOptions = sedesDisponibles
          .map((sede) => {
            const selected = sede.id_sede == currentId ? "selected" : "";
            return `<option value="${sede.id_sede}" ${selected}>${sede.nombre}</option>`;
          })
          .join("");

        return `
      <select class="form-select form-select-sm btn-pill w-auto cambiar-sede" data-id="${row.id_usuario}">
        ${selectOptions}
      </select>
    `;
      },
      className: "text-center",
    },
    {
      data: "id_rol",
      render: function (idRol, type, row) {
        const currentId = idRol ?? "";
        const selectOptions = rolesDisponibles
          .map((rol) => {
            const selected = rol.id_rol == currentId ? "selected" : "";
            return `<option value="${rol.id_rol}" ${selected}>${rol.nombre_rol}</option>`;
          })
          .join("");

        return `
      <select class="form-select form-select-sm btn-pill w-auto cambiar-rol" data-id="${row.id_usuario}">
        ${selectOptions}
      </select>
    `;
      },
      className: "text-center",
    },
    {
      data: "activo",
      render: function (activo, type, row) {
        const isChecked = activo == 1 ? "checked" : "";
        const switchId = `switch-${row.id_usuario}`;

        return `
        <div class="media-body icon-state switch-outline">
        <label class="switch">
          <input type="checkbox" class="toggle-usuario" id="${switchId}" data-id="${row.id_usuario}" ${isChecked}>
          <span class="switch-state bg-primary"></span>
        </label>
        </div>
      `;
      },
      className: "text-center",
    },
    {
      data: null,
      render: function (d) {
        return `
        <a href="#" class="btn btn-outline-primary btn-icon btn-sm btn-pill editar-usuario"
          data-id="${d.id_usuario}" title="Ver / Editar">
          <i class="icon-pencil fs-5"></i>
        </a>
      `;
      },
      className: "text-center",
    },
  ],
});

$(document).on("change", ".toggle-usuario", function () {
  const idUsuario = $(this).data("id");
  const nuevoEstado = $(this).is(":checked") ? 1 : 0;

  Swal.fire({
    title: "¿Desea cambiar el estado del usuario?",
    text: `El usuario será ${nuevoEstado ? "activado" : "desactivado"}.`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cambiar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "assets/ajax/usuario_ajax.php?action=cambiarEstado",
        { idUsuario, nuevoEstado },
        function (resp) {
          if (resp.success) {
            Swal.fire("¡Estado actualizado!", resp.message, "success");
          } else {
            Swal.fire("Error", resp.message, "error");
            tabla.ajax.reload(null, false); // revertir el switch si hay error
          }
        },
        "json"
      );
    } else {
      tabla.ajax.reload(null, false); // cancelar = revertir el switch
    }
  });
});

// Abrir modal de edición
$(document).on("click", ".editar-usuario", function (e) {
  e.preventDefault();
  const id = $(this).data("id");

  Promise.all([cargarRoles(), cargarSedes(), cargarTiposDocumento()])
    .then(() => {
      return $.ajax({
        url: "assets/ajax/usuario_ajax.php?action=obtenerUsuario",
        type: "GET",
        data: { id },
        dataType: "json",
      });
    })
    .then((res) => {
      if (!res.success) {
        Swal.fire("Error", res.message, "error");
        return;
      }

      const u = res.data;

      $("#edit_id_usuario").val(u.id_usuario);
      $("#edit_fechaNacimiento").val(u.fechaNacimiento_usuario);
      $("#edit_nombres").val(u.nombres_apellidos_usuario);
      $("#edit_telefono").val(u.numeroCelular);
      $("#edit_email").val(u.email);
      $("#edit_numero_documento").val(u.numeroDocumento_usuario);

      llenarSelect(
        "#edit_tipo_documento",
        u.id_tipoDocumento,
        u.tipoDocumento,
        tiposDocumento
      );
      llenarSelect("#edit_sede", u.id_sede, u.nombre_sede, sedesDisponibles);
      llenarSelect("#edit_rol", u.id_rol, u.nombre_rol, rolesDisponibles);

      $("#modalEditarUsuario").modal("show");
    })
    .catch((err) => {
      console.error("Error al cargar datos:", err);
      Swal.fire("Error", "Hubo un problema al cargar los datos.", "error");
    });
});

// Función para llenar select
function llenarSelect(selector, selectedId, selectedText, items) {
  const $select = $(selector);
  $select.empty();

  items.forEach((item) => {
    const id = item.id_sede || item.id_rol || item.id_docidentificacion_sunat;
    const nombre =
      item.nombre || item.nombre_rol || item.abreviatura_docIdentificacion;

    const selected = id == selectedId ? "selected" : "";
    $select.append(`<option value="${id}" ${selected}>${nombre}</option>`);
  });
}

let tiposDocumento = [];
$.getJSON(
  "assets/ajax/documentoIdentificacion_ajax.php?action=listarDocumentosRegistroUsuario",
  function (res) {
    tiposDocumento = res.data ?? [];
  }
);

$("#formEditarUsuario").on("submit", function (e) {
  e.preventDefault();

  const datos = $(this).serialize();

  Swal.fire({
    title: "¿Desea guardar los cambios?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, guardar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.post(
        "assets/ajax/usuario_ajax.php?action=modificarUsuario",
        datos,
        function (resp) {
          if (resp.success) {
            $("#modalEditarUsuario").modal("hide");
            Swal.fire("¡Modificado!", resp.message, "success");
            tabla.ajax.reload(null, false);
          } else {
            Swal.fire("Error", resp.message, "error");
          }
        },
        "json"
      );
    }
  });
});

function cargarSedes() {
  return $.getJSON(
    "assets/ajax/sede_ajax.php?action=listarSedes",
    function (res) {
      sedesDisponibles = res.data ?? [];
    }
  );
}

function cargarRoles() {
  return $.getJSON(
    "assets/ajax/rol_ajax.php?action=listarRoles",
    function (res) {
      rolesDisponibles = res.data ?? [];
    }
  );
}

function cargarTiposDocumento() {
  return $.getJSON(
    "assets/ajax/documentoIdentificacion_ajax.php?action=listarDocumentosRegistroUsuario",
    function (res) {
      tiposDocumento = res.data ?? [];
    }
  );
}

// CAMBIAR SEDE
$(document).on("change", ".cambiar-sede", function () {
  const idUsuario = $(this).data("id");
  const nuevaSedeId = $(this).val();
  const nombreSede = $(this).find("option:selected").text();

  Swal.fire({
    title: "¿Desea cambiar la sede?",
    text: `Asignar sede: ${nombreSede}`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cambiar",
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) {
      tabla.ajax.reload(null, false);
      return;
    }

    $.post(
      "assets/ajax/usuario_ajax.php?action=cambiarSede",
      { idUsuario: idUsuario, newSede: nuevaSedeId }, // <- nombres correctos
      function (resp) {
        if (resp.success) {
          Swal.fire(
            "¡Actualizado!",
            resp.message || "Sede actualizada",
            "success"
          );
        } else {
          Swal.fire("Error", resp.message || "No se pudo actualizar", "error");
        }
        tabla.ajax.reload(null, false);
      },
      "json"
    ).fail(() => {
      Swal.fire("Error", "Fallo de red", "error");
      tabla.ajax.reload(null, false);
    });
  });
});

// CAMBIAR ROL
$(document).on("change", ".cambiar-rol", function () {
  const idUsuario = $(this).data("id");
  const nuevoRolId = $(this).val();
  const nombreRol = $(this).find("option:selected").text();

  Swal.fire({
    title: "¿Desea cambiar el rol?",
    text: `Asignar rol: ${nombreRol}`,
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sí, cambiar",
    cancelButtonText: "Cancelar",
  }).then((r) => {
    if (!r.isConfirmed) {
      tabla.ajax.reload(null, false);
      return;
    }

    $.post(
      "assets/ajax/usuario_ajax.php?action=cambiarRol",
      { idUsuario: idUsuario, newRol: nuevoRolId }, // <- nombres correctos
      function (resp) {
        if (resp.success) {
          Swal.fire(
            "¡Actualizado!",
            resp.message || "Rol actualizado",
            "success"
          );
        } else {
          Swal.fire("Error", resp.message || "No se pudo actualizar", "error");
        }
        tabla.ajax.reload(null, false);
      },
      "json"
    ).fail(() => {
      Swal.fire("Error", "Fallo de red", "error");
      tabla.ajax.reload(null, false);
    });
  });
});

// Auto-focus al abrir el modal
document
  .getElementById("modalEditarUsuario")
  .addEventListener("shown.bs.modal", () => {
    const el = document.getElementById("edit_nombres");
    if (el) el.focus();
  });

// Feedback Bootstrap nativo (opcional)
(() => {
  const form = document.getElementById("formEditarUsuario");
  if (!form) return;
  form.addEventListener("submit", (e) => {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add("was-validated");
  });
})();
