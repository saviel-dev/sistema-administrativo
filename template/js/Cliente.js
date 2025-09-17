document.addEventListener("DOMContentLoaded", () => {
  const docInput = document.querySelector("#nro_documento");
  const tipoSelect = document.querySelector(
    "select[name='id_doc_identificacion']"
  );

  docInput.addEventListener("input", () => {
    const value = docInput.value.trim();
    if (value.length === 8) {
      tipoSelect.value = "1"; // DNI
    } else if (value.length === 11) {
      tipoSelect.value = "6"; // RUC
    } else {
      tipoSelect.value = "";
    }
  });
});

$("#btnBuscarSunat").on("click", function () {
  let nro = $("#nro_documento").val().trim();
  const tipo = $("select[name='id_doc_identificacion']").val();

  if (!tipo || !nro) {
    Swal.fire(
      "Faltan datos",
      "Debe ingresar un tipo y número de documento",
      "warning"
    );
    return;
  }

  if (tipo === "1") {
    // Solo primeros 8 dígitos para DNI
    nro = nro.substring(0, 8);
  } else {
    nro = nro.replace("-", ""); // quitar guión si lo hubiera
  }

  // Verificar si ya existe el cliente
  $.get("assets/ajax/cliente_ajax.php", {
    action: "buscarPorDocumento",
    id_doc_identificacion: tipo,
    nro_documento: nro,
  }).done(function (res) {
    if (res.success && res.data) {
      // Cliente duplicado
      Swal.fire({
        icon: "question",
        title: "Cliente ya registrado",
        text: "¿Deseas registrarlo como parte de una corporación?",
        showCancelButton: true,
        confirmButtonText: "Sí, registrar como nuevo",
        cancelButtonText: "No, solo ver datos",
      }).then((r) => {
        if (r.isConfirmed) {
          rellenarFormularioCliente(res.data);
          $("[name='direccion']").val("");
          $("[name='email']").val("");
          $("[name='telefono_fijo']").val("");
          $("[name='celular_1']").val("");
          $("[name='celular_2']").val("");
          $("[name='representante']").val("");
          $("textarea[name='notas']").val("");
          $("select[name='departamento']").val("").trigger("change");
          $("input[name='id_cliente']").val("");
          $(".btn-guardar").show();

          obtenerNuevoIdCliente().then((id) => {
            $("input[name='codigo_cliente']").val(id);
          });
        } else {
          rellenarFormularioCliente(res.data, true);
          $("input[name='codigo_cliente']").val(res.data.id_cliente ?? "");
        }
      });
    } else {
      // No existe → consulta externa
      consultarDocumentoExterno(tipo, nro);
    }
  });
});

function validarCamposObligatorios() {
  let errores = [];

  const requiredFields = [
    "id_doc_identificacion",
    "nro_documento",
    "razon_social",
    "direccion",
    "distrito",
  ];

  requiredFields.forEach((name) => {
    const el = $(`[name='${name}']`);
    if (!el.val()) {
      el.addClass("is-invalid");
      errores.push(name);
    } else {
      el.removeClass("is-invalid");
    }
  });

  return errores.length === 0;
}

function consultarDocumentoExterno(tipo, nro) {
  if (tipo === "6") {
    // RUC (SUNAT)
    if (nro.length !== 11) {
      Swal.fire("RUC inválido", "Debe tener 11 dígitos", "warning");
      return;
    }

    $.get(
      `assets/ajax/cliente_ajax.php?action=consultarSunatRUC&ruc=${nro}`,
      function (res) {
        if (!res.success) {
          Swal.fire(
            "Error",
            res.message || "No se pudo consultar en SUNAT",
            "error"
          );
          return;
        }

        const empresa = res.data;

        $("input[name='razon_social']").val(empresa.razonSocial || "");
        $("input[name='nombre_comercial']").val(empresa.nombreComercial || "");
        $("input[name='direccion']").val(empresa.direccion || "");
        $("input[name='representante']").val(empresa.representanteLegal || "");

        $("select[name='estadoSunat']").val(
          empresa.estado?.toUpperCase() || "ACTIVO"
        );
        $("select[name='condicionSunat']").val(
          empresa.condicion?.toUpperCase() || "HABIDO"
        );

        if (empresa.departamento && empresa.provincia && empresa.distrito) {
          $("select[name='departamento']")
            .val(empresa.departamento)
            .trigger("change");

          setTimeout(() => {
            $("select[name='provincia']")
              .val(empresa.provincia)
              .trigger("change");
            setTimeout(() => {
              $("select[name='distrito'] option").each(function () {
                if (
                  $(this).text().trim().toUpperCase() ===
                  empresa.distrito.toUpperCase()
                ) {
                  $("select[name='distrito']")
                    .val($(this).val())
                    .trigger("change");
                  return false;
                }
              });
            }, 300);
          }, 300);
        }
      }
    );
  } else if (tipo === "1" && nro.length === 8) {
    // DNI (RENIEC)
    $.get(
      `assets/ajax/cliente_ajax.php?action=consultarReniecDNI&dni=${nro}`,
      function (res) {
        if (!res.success) {
          Swal.fire(
            "Error",
            res.message || "No se pudo consultar en RENIEC",
            "error"
          );
          return;
        }

        const persona = res.data;
        const razon = `${persona.apellidoPaterno} ${persona.apellidoMaterno} ${persona.nombres}`;
        const nroDniCompleto = `${persona.numeroDocumento}-${persona.digitoVerificador}`;

        $("input[name='razon_social']").val(razon);
        $("input[name='nro_documento']").val(nroDniCompleto);
        $("select[name='estadoSunat']").val("ACTIVO");
        $("select[name='condicionSunat']").val("HABIDO");
      }
    );
  } else {
    // Otros tipos de documento
    $("select[name='estadoSunat']").val("ACTIVO");
    $("select[name='condicionSunat']").val("HABIDO");

    Swal.fire({
      icon: "info",
      title: "Documento no consultable",
      text: "No se puede consultar este tipo de documento. Estado marcado como ACTIVO y condición como HABIDO.",
    });
  }
  setTimeout(() => {
    actualizarCodigoUbigeoDesdeSelects();
  }, 700);
  obtenerNuevoIdCliente().then((id) => {
    $("input[name='codigo_cliente']").val(id);
  });
}

function limpiarFormularioCliente() {
  // Resetea el formulario completo
  $("#formCliente")[0].reset();

  // Limpia estilos de error
  $("#formCliente .is-invalid").removeClass("is-invalid");

  // Restablece selects dependientes
  $("select[name='departamento']").val("").trigger("change");
  $("select[name='provincia']").html('<option value="">Provincia</option>');
  $("select[name='distrito']").html('<option value="">Distrito</option>');

  // Código interno
  $("input[name='id_cliente']").val("");
  $("input[name='codigo_cliente']").val("");

  // Botón de guardar visible
  $(".btn-guardar").show();
}

function rellenarFormularioCliente(data, modoLectura = false) {
  for (const campo in data) {
    const $el = $(`[name='${campo}']`);
    if ($el.length) {
      if ($el.is("select")) {
        $el.val(data[campo]?.toUpperCase() || "").trigger("change");
      } else {
        $el.val(data[campo]);
      }
    }
  }

  // Mostrar el código de cliente desde id_cliente
  if (data.id_cliente) {
    $("input[name='codigo_cliente']").val(data.id_cliente);
    $("input[name='id_cliente']").val(data.id_cliente);
  }

  if (data.codigo_ubigeo) {
    cargarUbigeoDesdeCodigo(data.codigo_ubigeo);
  }

  // Limpiar estilos de validación e interactividad previa
  $("#formCliente input, #formCliente select, #formCliente textarea")
    .removeAttr("disabled")
    .removeClass("is-invalid")
    .removeAttr("data-bs-toggle")
    .removeAttr("title");

  if (modoLectura) {
    const camposBloqueados = [
      "nro_documento",
      "id_doc_identificacion",
      "razon_social",
    ];

    camposBloqueados.forEach((name) => {
      const $el = $(`[name='${name}']`);
      $el
        .prop("disabled", true)
        .addClass("is-invalid") // esto respeta tu estilo Bootstrap
        .attr("data-bs-toggle", "tooltip")
        .attr("title", "Campo no editable por seguridad");
    });

    // Activar tooltips Bootstrap
    $('[data-bs-toggle="tooltip"]').tooltip();
  }
}

function actualizarCodigoUbigeoDesdeSelects() {
  const codigo = $("select[name='distrito']").val();
  $("#codigo_ubigeo").val(codigo ? codigo : "");
}

// Validación previa por tipo y número de documento
// $("#nro_documento").on("blur", function () {
//   const tipo = $("[name='id_doc_identificacion']").val();
//   const nro = $(this).val().trim();

//   if (!tipo || !nro) return;

//   $.get("assets/ajax/cliente_ajax.php", {
//     action: "buscarPorDocumento",
//     id_doc_identificacion: tipo,
//     nro_documento: nro,
//   }).done(function (res) {
//     if (res.success && res.data) {
//       Swal.fire({
//         icon: "question",
//         title: "Cliente ya registrado",
//         text: "¿Es parte de una corporación (varias sedes)?",
//         showCancelButton: true,
//         confirmButtonText: "Sí, es corporación",
//         cancelButtonText: "No, solo ver",
//       }).then((r) => {
//         if (r.isConfirmed) {
//           rellenarFormularioCliente(res.data);
//           $("[name='direccion']").val("");
//           $("[name='departamento']").val("").trigger("change");
//           $("[name='provincia']").html('<option value="">Provincia</option>');
//           $("[name='distrito']").html('<option value="">Distrito</option>');
//           $("input[name='id_cliente']").val("");
//           obtenerNuevoIdCliente().then((id) => {
//             $("input[name='codigo_cliente']").val(id);
//           });
//         } else {
//           rellenarFormularioCliente(res.data, true);
//           $("input[name='codigo_cliente']").val(res.data.id_cliente ?? "");
//         }
//       });
//     } else {
//       // No se encontró → buscar externo
//       consultarDocumentoExterno(tipo, nro);
//     }
//   });
// });

function cargarUbigeoDesdeCodigo(codigo) {
  $.get(
    `assets/ajax/ubigeo_ajax.php?action=buscarUbigeo&codigo=${codigo}`,
    function (res) {
      if (res.success && res.data) {
        const ubigeo = res.data;
        $("select[name='departamento']")
          .val(ubigeo.departamento)
          .trigger("change");

        setTimeout(() => {
          $("select[name='provincia']").val(ubigeo.provincia).trigger("change");

          setTimeout(() => {
            $("select[name='distrito']").val(codigo); // valor exacto del ubigeo
          }, 300);
        }, 300);
      }
    }
  );
}

// $("#btnBuscarSunat").on("click", function () {
//   const nro = $("#nro_documento").val().trim().replace("-", "");
//   const tipo = $("select[name='id_doc_identificacion']").val();

//   if (tipo === "6") {
//     if (nro.length !== 11) {
//       Swal.fire({
//         icon: "warning",
//         title: "RUC inválido",
//         text: "El RUC debe tener 11 dígitos.",
//       });
//       return;
//     }
//     $.get(
//       `assets/ajax/cliente_ajax.php?action=consultarSunatRUC&ruc=${nro}`,
//       function (res) {
//         if (!res.success) {
//           Swal.fire({
//             icon: "error",
//             title: "Error",
//             text: res.message || "No se pudo obtener información desde SUNAT.",
//           });
//           return;
//         }

//         const empresa = res.data;
//         $("input[name='razon_social']").val(empresa.razonSocial || "");
//         $("input[name='nombre_comercial']").val(empresa.nombreComercial || "");
//         $("input[name='direccion']").val(empresa.direccion || "");
//         $("input[name='representante']").val(empresa.representanteLegal || "");

//         $("select[name='estado_sunat'] option").each(function () {
//           if (
//             $(this).text().trim().toUpperCase() ===
//             (empresa.estado || "").trim().toUpperCase()
//           ) {
//             $(this).prop("selected", true);
//             return false;
//           }
//         });

//         $("select[name='condicion_sunat'] option").each(function () {
//           if (
//             $(this).text().trim().toUpperCase() ===
//             (empresa.condicion || "").trim().toUpperCase()
//           ) {
//             $(this).prop("selected", true);
//             return false;
//           }
//         });

//         if (empresa.departamento && empresa.provincia && empresa.distrito) {
//           const $dep = $("select[name='departamento']");
//           const $prov = $("select[name='provincia']");
//           const $dist = $("select[name='distrito']");

//           $dep.val(empresa.departamento).trigger("change");
//           setTimeout(() => {
//             $prov.val(empresa.provincia).trigger("change");
//             setTimeout(() => {
//               $dist.find("option").each(function () {
//                 if (
//                   $(this).text().trim().toUpperCase() ===
//                   empresa.distrito.toUpperCase()
//                 ) {
//                   $dist.val($(this).val());
//                   return false;
//                 }
//               });
//             }, 300);
//           }, 300);
//         }
//       }
//     );
//   } else if (tipo === "1" && nro.length === 8) {
//     $.get(
//       `assets/ajax/cliente_ajax.php?action=consultarReniecDNI&dni=${nro}`,
//       function (res) {
//         if (!res.success) {
//           Swal.fire({
//             icon: "error",
//             title: "Error",
//             text: res.message || "No se pudo obtener información desde RENIEC.",
//           });
//           return;
//         }

//         const persona = res.data;
//         const razon = `${persona.apellidoPaterno} ${persona.apellidoMaterno} ${persona.nombres}`;
//         const nroDniCompleto = `${persona.numeroDocumento}-${persona.digitoVerificador}`;

//         $("input[name='razon_social']").val(razon);
//         $("input[name='nro_documento']").val(nroDniCompleto);
//         $("select[name='estado_sunat']").val("Activo");
//         $("select[name='condicion_sunat']").val("Habido");
//       }
//     );
//   } else {
//     $("select[name='estado_sunat']").val("Activo");
//     $("select[name='condicion_sunat']").val("Habido");

//     Swal.fire({
//       icon: "info",
//       title: "Información",
//       text: "No se puede consultar en SUNAT. Estado y condición marcados como ACTIVO y HABIDO.",
//     });
//   }
// });

$(document).ready(function () {
  $('[data-bs-toggle="tooltip"]').tooltip();

  $("#formCliente").on("submit", function (e) {
    e.preventDefault();

    const form = this;
    const formData = $(form).serializeArray();
    let valido = true;

    // Validar campos requeridos
    const obligatorios = [
      "id_doc_identificacion",
      "nro_documento",
      "razon_social",
      "direccion",
      "distrito",
    ];

    obligatorios.forEach((campo) => {
      const input = $(form).find(`[name='${campo}']`);
      if (!input.val()) {
        input.addClass("is-invalid");
        valido = false;
      } else {
        input.removeClass("is-invalid");
      }
    });

    if (!valido) {
      Swal.fire(
        "Faltan campos",
        "Completa los campos obligatorios.",
        "warning"
      );
      return;
    }

    // Ocultar botón para evitar doble click
    $(".btn-guardar")
      .prop("disabled", true)
      .html(
        `<span class="spinner-border spinner-border-sm me-1"></span> Guardando`
      );
    $(".btn-guardar").prop("disabled", false).html("Guardar");

    const id = $("input[name='id_cliente']").val();
    const accion = id ? "modificar" : "registrar";

    if (!$("#codigo_ubigeo").val()) {
      Swal.fire("Error", "Debe seleccionar un distrito válido", "warning");
      $(".btn-guardar").prop("disabled", false).text("Guardar");
      return;
    }
    $.post(
      `assets/ajax/cliente_ajax.php?action=${accion}`,
      $(form).serialize(),
      function (res) {
        if (res.success) {
          Swal.fire("Guardado", "Cliente registrado correctamente.", "success");
          $("#modalCliente").modal("hide");
          $("#tabla-clientes").DataTable().ajax.reload(null, false); // actualizar tabla
          form.reset();
        } else {
          Swal.fire(
            "Error",
            "No se pudo guardar. Intente nuevamente.",
            "error"
          );
        }
        $(".btn-guardar").prop("disabled", false).text("Guardar");
      }
    );
  });

  const tabla = $("#tabla-clientes").DataTable({
    language: {
      url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
    },
    responsive: true,
    deferRender: true,
    scrollCollapse: true,
    scroller: true,
    autoWidth: true, // ← importante para permitir ajuste de columnas
    ajax: {
      url: "assets/ajax/cliente_ajax.php?action=listar",
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
        data: null,
        defaultContent: "",
        render: function (d) {
          const id_cliente = d.id_cliente ?? "";

          return `
      <div class="text-center" >
        <strong>${id_cliente} </strong>
      </div>
    `;
        },
      },
      {
        data: null,
        defaultContent: "",
        render: function (d) {
          const razon = d.razon_social ?? "";
          const comercial = d.nombre_comercial ?? "";
          const representante = d.representante ?? "";

          const badgeComercial = comercial
            ? `<div><span class="badge badge-dark mt-1">${comercial}</span></div>`
            : "";
          const badgeRepresentante = representante
            ? `<div><span class="badge badge-light-primary mt-1">${representante}</span></div>`
            : "";

          return `
      <div >
        ${razon}
        ${badgeComercial}
        ${badgeRepresentante}
      </div>
    `;
        },
      },
      {
        data: null,
        defaultContent: "",
        render: function (d) {
          const tipo = d.abreviatura_docIdentificacion;
          const numero = d.nro_documento ?? "";

          // Mapa de colores por tipo de documento
          const badgeColors = {
            DNI: "success",
            RUC: "primary",
            Pasaporte: "info",
            "Carnet de Extranjería - C.E.": "warning",
            "Cédula Diplomática - C.D.": "dark",
            "SIN DOCUMENTO": "danger",
          };

          // Si no tiene tipo, usamos "SIN DOCUMENTO"
          const tipoFinal = tipo ?? "SIN DOCUMENTO";
          const color = badgeColors[tipoFinal] ?? "secondary"; // fallback

          const badge = `<span class="badge badge-light-${color} mb-1">${tipoFinal}</span>`;

          return `
      <div class="text-center">
        ${badge}<br>
        <span>${numero}</span>
      </div>
    `;
        },
      },
      {
        data: null,
        defaultContent: "",
        render: function (d) {
          const telefono_fijo = d.telefono_fijo
            ? `<i class="fa fa-mobile-phone-alt me-1 text-primary"></i>${d.telefono_fijo}<br>`
            : "";

          const celular_1 = d.celular_1
            ? `<i class="fa fa-mobile-phone-alt me-1 text-success"></i>${d.celular_1}<br>`
            : "";

          const celular_2 = d.celular_2
            ? `<i class="fa fa-mobile-phone-alt me-1 text-success"></i>${d.celular_2}<br>`
            : "";

          return `
      <div class="text-center">
        ${telefono_fijo}
        ${celular_1}
        ${celular_2}
      </div>
    `;
        },
      },

      // {
      //   data: "ClasificacionCliente",
      //   defaultContent: "",
      //   render: function (valor) {
      //     const labels = {
      //       0: ["Sin clasificar", "secondary"],
      //       1: ["Bajo", "danger"],
      //       2: ["Medio", "warning"],
      //       3: ["Alto", "success"],
      //     };
      //     const [texto, color] = labels[valor] || ["N/A", "light"];
      //     return `<span class="badge badge-${color} f-14">${texto}</span>`;
      //   },
      // },
      {
        data: null,
        defaultContent: "",
        render: function (d) {
          const estado = d.estadoSunat || "Sin dato";
          const condicion = d.condicionSunat || "Sin dato";

          const badgeColorEstado =
            estado === "ACTIVO"
              ? "success"
              : estado.includes("BAJA")
              ? "danger"
              : "secondary";

          const badgeColorCond =
            condicion === "HABIDO"
              ? "info"
              : condicion === "NO HABIDO"
              ? "danger"
              : "secondary";

          return `
      <div class="">
        <div>Estado: <span class="badge rounded-pill badge-light-${badgeColorEstado}">${estado}</span></div>
        
        <div class="mt-1">Condición: <span class="badge rounded-pill badge-light-${badgeColorCond}">${condicion}</span></div>
        
      </div>
    `;
        },
      },
      {
        data: "fechaRegistro_Cliente",
        defaultContent: "",
        render: function (fecha) {
          if (!fecha) return "";
          const d = new Date(fecha);
          const dia = String(d.getDate()).padStart(2, "0");
          const mes = String(d.getMonth() + 1).padStart(2, "0");
          const anio = d.getFullYear();
          return `${dia}-${mes}-${anio}`;
        },
      },
      {
        data: "estadoCliente",
        defaultContent: "",
        render: function (estado) {
          return estado == 1
            ? '<span class="badge badge-light-success rounded-pill f-14">Activo</span>'
            : '<span class="badge badge-secondary rounded-pill">Inactivo</span>';
        },
      },
      {
        data: null,
        defaultContent: "",
        render: function (d) {
          if (!d || !d.id_cliente) return "";

          return `
      <div class="d-flex flex-wrap justify-content-center gap-1 mt-1">
        <a href="#" class="ver-cliente btn btn-outline-primary btn-icon btn-sm btn-pill p-1" data-id="${d.id_cliente}"  data-bs-toggle="modal"
        data-bs-target="#modalCliente" title="Ver/Editar">
          <i class="icon-pencil fs-4"></i>
        </a>
      </div>
    `;
        },
      },
    ],
  });

  // Tipo documento
  $.get(
    "assets/ajax/documentoIdentificacion_ajax.php?action=listar",
    function (res) {
      const select = $("select[name='id_doc_identificacion']");
      select.empty().append('<option value="">Seleccione</option>');
      res.data.forEach((item) => {
        select.append(
          `<option value="${item.id_docidentificacion_sunat}">${item.abreviatura_docIdentificacion}</option>`
        );
      });
    }
  );

  // Cargar departamentos
  $.get("assets/ajax/ubigeo_ajax.php?action=departamentos", function (res) {
    const select = $("select[name='departamento']");
    select.empty().append('<option value="">Departamento</option>');
    res.data.forEach((dep) => {
      select.append(
        `<option value="${dep.departamento}">${dep.departamento}</option>`
      );
    });
  });

  // Provincias dinámicas
  $("select[name='departamento']").on("change", function () {
    const dep = $(this).val();
    $("select[name='provincia']").html('<option value="">Provincia</option>');
    $("select[name='distrito']").html('<option value="">Distrito</option>');

    if (!dep) return;
    $.get(
      `assets/ajax/ubigeo_ajax.php?action=provincias&departamento=${encodeURIComponent(
        dep
      )}`,
      function (res) {
        res.data.forEach((prov) => {
          $("select[name='provincia']").append(
            `<option value="${prov.provincia}">${prov.provincia}</option>`
          );
        });
      }
    );
  });

  // Distritos dinámicos
  $("select[name='provincia']").on("change", function () {
    const dep = $("select[name='departamento']").val();
    const prov = $(this).val();
    $("select[name='distrito']").html('<option value="">Distrito</option>');

    if (!dep || !prov) return;
    $.get(
      `assets/ajax/ubigeo_ajax.php?action=distritos&departamento=${encodeURIComponent(
        dep
      )}&provincia=${encodeURIComponent(prov)}`,
      function (res) {
        res.data.forEach((dist) => {
          $("select[name='distrito']").append(
            `<option value="${dist.codigo_ubigeo}">${dist.distrito}</option>`
          );
        });
      }
    );
  });

  $("select[name='distrito']").on("change", function () {
    actualizarCodigoUbigeoDesdeSelects();
  });

  $("#AgregarCliente").on("click", function () {
    $("#modalCliente").modal("show");
  });

  $("#modalCliente").on("hidden.bs.modal", function () {
    limpiarFormularioCliente();
    $("#tituloModalCliente").text("Nuevo Cliente");

    // Restablecer campos deshabilitados
    $("#btnBuscarSunat").removeClass("d-none");
    $("[name='nro_documento']").prop("disabled", false);
    $("[name='id_doc_identificacion']").prop("disabled", false);
    $("[name='razon_social']").prop("disabled", false);
    $("#btnModificar").addClass("d-none");
    $(".btn-confirmar-modificacion").remove(); // <-- Limpia el botón al cerrar el modal
  });

  // Forzar mayúsculas en tiempo real
  ["razon_social", "nombre_comercial", "direccion", "representante"].forEach(
    (campo) => {
      $(`input[name='${campo}']`).on("input", function () {
        const cursor = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(cursor, cursor); // mantiene posición del cursor
      });
    }
  );

  // Capitalizar solo primera letra en notas (como NOMPRO)
  $("textarea[name='notas']").on("input", function () {
    let texto = this.value;
    if (texto.length > 0) {
      this.value = texto.charAt(0).toUpperCase() + texto.slice(1);
    }
  });

  // Evento para botón Ver/Editar cliente
  $(document).on("click", ".ver-cliente", function (e) {
    e.preventDefault();
    const id = $(this).data("id");

    $.get(
      "assets/ajax/cliente_ajax.php",
      { action: "buscar", id_cliente: id },
      function (res) {
        if (res.success && res.data) {
          rellenarFormularioCliente(res.data, true); // modo lectura
          $("#modalCliente").modal("show");
          $("#tituloModalCliente").text("Ver Cliente");

          // Deshabilitar campos protegidos
          $("[name='nro_documento']").prop("disabled", true);
          $("[name='id_doc_identificacion']").prop("disabled", true);
          $("[name='razon_social']").prop("disabled", true);

          // Ocultar botón buscar
          $("#btnBuscarSunat").addClass("d-none");

          // Ocultar guardar y mostrar botón modificar
          $(".btn-guardar").hide();
          $("#btnModificar").removeClass("d-none");
        } else {
          Swal.fire("Error", "No se pudo cargar el cliente", "error");
        }
      }
    );
  });

  // Acción específica para el botón MODIFICAR (actualizar un cliente existente)
  $("#btnModificar")
    .off("click")
    .on("click", function () {
      // Habilitar campos editables
      $(
        "[name='nombre_comercial'], [name='representante'], [name='direccion'], [name='departamento'], [name='provincia'], [name='distrito'], [name='telefono_fijo'], [name='celular_1'], [name='celular_2'], [name='email'], [name='ClasificacionCliente'], [name='limite_credito'], [name='estadoSunat'], [name='condicionSunat'], [name='notas']"
      ).prop("disabled", false);

      // Ocultar el botón Modificar y mostrar botón Confirmar Modificación
      $("#btnModificar").addClass("d-none");

      // Insertar un botón temporal si quieres, o simplemente forzar el envío en otro paso
      $(".btn-confirmar-modificacion").remove(); // limpiar anterior si existe
      $(".modal-footer").append(`
    <button type="button" class="btn btn-success btn-pill btn-confirmar-modificacion">
      <span class="spinner-border spinner-border-sm d-none me-1" role="status" aria-hidden="true"></span>
      Confirmar Modificación
    </button>
  `);
    });

  // Acción al hacer clic en "Confirmar Modificación"
  $(document).on("click", ".btn-confirmar-modificacion", function () {
    const $btn = $(this);

    // Validación
    if (!validarCamposObligatorios()) {
      Swal.fire(
        "Faltan campos",
        "Completa los campos obligatorios.",
        "warning"
      );
      return;
    }

    if (!$("#codigo_ubigeo").val()) {
      Swal.fire("Error", "Debe seleccionar un distrito válido", "warning");
      return;
    }

    // Spinner ON
    $btn.prop("disabled", true);
    $btn.find(".spinner-border").removeClass("d-none");
    $btn
      .contents()
      .filter(function () {
        return this.nodeType === 3;
      })
      .first()
      .replaceWith(" Modificando...");

    // Llamada AJAX para modificar
    $.post(
      "assets/ajax/cliente_ajax.php?action=modificar",
      $("#formCliente").serialize(),
      function (res) {
        if (res.success) {
          Swal.fire(
            "Modificado",
            "Cliente actualizado correctamente.",
            "success"
          );
          $("#modalCliente").modal("hide");
          $("#tabla-clientes").DataTable().ajax.reload(null, false);
        } else {
          Swal.fire(
            "Error",
            "No se pudo modificar. Intente nuevamente.",
            "error"
          );
        }

        // Restaurar botón
        $btn.prop("disabled", false);
        $btn.find(".spinner-border").addClass("d-none");
        $btn
          .contents()
          .filter(function () {
            return this.nodeType === 3;
          })
          .first()
          .replaceWith(" Confirmar Modificación");
      }
    );
  });
});

// Esto reemplaza el label con un ícono bonito (opcional)
$("#tabla-clientes").on("init.dt", function () {
  $(".dataTables_filter label")
    .contents()
    .filter(function () {
      return this.nodeType === 3;
    })
    .first()
    .replaceWith(""); // Elimina texto "Buscar:"
  $(".dataTables_filter label").prepend(
    '<i class="fa fa-search me-2"></i> Buscar:'
  ); // ícono lupa
});

$("#tabla-clientes").on("init.dt", function () {
  $(".dataTables_filter input").attr("placeholder", "Buscar cliente...");
});

function obtenerNuevoIdCliente() {
  return $.get("assets/ajax/cliente_ajax.php?action=obtenerUltimoId").then(
    (res) => {
      if (res.success && res.id) {
        return res.id;
      } else {
        return "Pendiente";
      }
    }
  );
}
