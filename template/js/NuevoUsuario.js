let currentStep = 0;
const steps = document.querySelectorAll(".form-step");
const nextBtn = document.getElementById("nextBtn");
const prevBtn = document.getElementById("prevBtn");
const indicators = document.querySelectorAll(".step-circle");

// Mostrar el paso actual
function showStep(n) {
  steps.forEach((step, index) => {
    step.classList.toggle("active", index === n);
  });

  indicators.forEach((circle, index) => {
    circle.classList.toggle("active", index === n);
  });

  prevBtn.style.display = n === 0 ? "none" : "inline-block";
  nextBtn.textContent = n === steps.length - 1 ? "Registrar" : "Siguiente";
}

function validateStep(stepIndex) {
  if (!steps[stepIndex]) return false;
  const inputs = steps[stepIndex].querySelectorAll("input, select");
  let valid = true;

  inputs.forEach((input) => {
    if (!input.checkValidity()) {
      input.style.borderColor = "red";
      valid = false;
    } else {
      input.style.borderColor = "#d1d5db";
    }
  });

  return valid;
}

function mostrarMensajeCard(mensaje, tipo = "error", permiteHTML = false) {
  const messageCard = document.getElementById("messageCard");

  const opciones = {
    success: {
      icon: "fa-circle-check",
      alertClass: "alert-success",
    },
    error: {
      icon: "fa-circle-xmark",
      alertClass: "alert-danger",
    },
    warning: {
      icon: "fa-triangle-exclamation",
      alertClass: "alert-warning",
    },
  };

  const config = opciones[tipo] || opciones.error;

  messageCard.className = `alert ${config.alertClass} alert-dismissible fade show`;
  messageCard.setAttribute("role", "alert");

  messageCard.innerHTML = `
    <i class="fa-solid ${config.icon}" style="margin-right:8px;"></i>
    <span>${permiteHTML ? mensaje : escapeHtml(mensaje)}</span>
  `;

  messageCard.style.display = "block";
}

function escapeHtml(text) {
  const div = document.createElement("div");
  div.innerText = text;
  return div.innerHTML;
}

function enviarFormulario() {
  const form = document.getElementById("registrationForm");
  const formData = new FormData(form);

  // CODIFICAR LA CONTRASEÑA EN BASE64 ANTES DE ENVIAR
  const rawPassword = form.querySelector("input[name='password']").value;
  const encodedPassword = btoa(rawPassword);
  formData.set("password", encodedPassword); // reemplazar el valor original

  console.log("Enviando datos...");
  nextBtn.innerHTML = `<i class="fa fa-spinner fa-spin me-2"></i> Cargando...`;
  nextBtn.disabled = true;
  nextBtn.classList.add("pulse-animation");

  fetch("template/assets/ajax/usuario_ajax.php?action=registrar", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        mostrarMensajeCard(
          "Tu cuenta ha sido creada con éxito. Redirigiendo al inicio <span id='dots'>.</span>",
          "success",
          true
        );

        nextBtn.innerHTML = "✔ Registrado";
        nextBtn.disabled = true;

        // Cambia icono del paso a check verde
        indicators[1].innerHTML = "✔";
        indicators[1].style.backgroundColor = "#22c55e";

        // Esperar a que el DOM actualice con #dots antes de manipularlo
        requestAnimationFrame(() => {
          const dotsEl = document.getElementById("dots");
          if (!dotsEl) return;

          let dotCount = 1;
          const dotAnim = setInterval(() => {
            dotsEl.textContent = ".".repeat(dotCount);
            dotCount = dotCount === 3 ? 1 : dotCount + 1;
          }, 500);

          setTimeout(() => {
            clearInterval(dotAnim);
            nextBtn.classList.remove("pulse-animation");
            window.location.href = "index.php";
          }, 6000);
        });
      } else {
        nextBtn.classList.remove("pulse-animation");
        mostrarMensajeCard(
          data.message || "No se pudo completar el registro.",
          "error"
        );
      }
    })
    .catch((err) => {
      nextBtn.innerHTML = "Registrar";
      nextBtn.classList.remove("pulse-animation");
      nextBtn.disabled = false;
      mostrarMensajeCard(
        "Error de red o servidor. Intenta nuevamente.",
        "error"
      );
      console.error(err);
    });
}

// EVENTOS
document.addEventListener("DOMContentLoaded", () => {
  showStep(currentStep);

  nextBtn.addEventListener("click", () => {
    if (currentStep === steps.length - 1) {
      if (validateStep(currentStep)) {
        enviarFormulario();
      }
    } else {
      if (validateStep(currentStep)) {
        currentStep++;
        showStep(currentStep);
      }
    }
  });

  prevBtn.addEventListener("click", () => {
    if (currentStep > 0) {
      currentStep--;
      showStep(currentStep);
    }
  });

  // MAYÚSCULAS EN NOMBRE
  const nombresInput = document.getElementById("nombres");
  if (nombresInput) {
    nombresInput.addEventListener("input", function () {
      this.value = this.value.toUpperCase();
    });
  }

  // CELULAR SOLO NÚMEROS
  const celularInput = document.getElementById("celular");
  if (celularInput) {
    celularInput.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "").slice(0, 9);
    });
  }

  // MOSTRAR/Ocultar PASSWORD
  const togglePassword = document.querySelector(".toggle-password");
  const passwordInput = document.getElementById("passwordInput");
  const eyeIcon = document.getElementById("eyeIcon");

  togglePassword.addEventListener("click", () => {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      eyeIcon.classList.remove("fa-eye");
      eyeIcon.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password";
      eyeIcon.classList.remove("fa-eye-slash");
      eyeIcon.classList.add("fa-eye");
    }
  });

  // CARGAR DOCUMENTOS
  fetch(
    "template/assets/ajax/documentoIdentificacion_ajax.php?action=listarDocumentosRegistroUsuario"
  )
    .then((res) => res.json())
    .then((data) => {
      const select = document.querySelector("select[name='tipoDocumento']");
      select.innerHTML = '<option value="">Seleccione</option>';
      data.data.forEach((item) => {
        const option = document.createElement("option");
        option.value = item.id_docidentificacion_sunat;
        option.textContent = item.abreviatura_docIdentificacion;
        select.appendChild(option);
      });
    })
    .catch((err) => console.error("Error cargando documentos:", err));

  const tipoDocumentoInput = document.querySelector(
    "select[name='tipoDocumento']"
  );
  const numeroDocumentoInput = document.querySelector(
    "input[name='numeroDocumento']"
  );
  const messageCard = document.getElementById("messageCard");

  numeroDocumentoInput.addEventListener("blur", validarDocumentoUsuario);

  function validarDocumentoUsuario() {
    const tipo = tipoDocumentoInput.value;
    const numero = numeroDocumentoInput.value;

    if (!tipo || !numero) return;

    fetch(
      `template/assets/ajax/usuario_ajax.php?action=validarDocumento&tipo=${tipo}&numero=${numero}`
    )
      .then((res) => res.json())
      .then((data) => {
        if (data.exists) {
          mostrarMensajeCard(
            "Ya existe un usuario con este documento. Por favor, ingrese otro.",
            "warning"
          );
          nextBtn.disabled = true;
        } else {
          messageCard.style.display = "none";
          nextBtn.disabled = false;
        }
      })
      .catch((err) => console.error("Error validando documento:", err));
  }
});
