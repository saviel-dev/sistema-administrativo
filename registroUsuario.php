<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Registro | Repuestos Nazca</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #dbeafe, #ffffff);
      font-family: 'Inter', sans-serif;
    }

    .glow-border {
      position: relative;
      border-radius: 28px;
      padding: 0;
    }

    .glow-border::before {
      content: '';
      position: absolute;
      top: -4px;
      left: -4px;
      right: -4px;
      bottom: -4px;
      z-index: -1;
      background: linear-gradient(135deg, #004BA8);
      border-radius: 10px;
      opacity: 0.5;
      filter: blur(16px);
      animation: pulseGradient 2s ease-in-out infinite;
    }

    @keyframes pulseGradient {
      0%, 100% {
        opacity: 0.45;
        filter: blur(14px);
      }
      50% {
        opacity: 0.7;
        filter: blur(20px);
      }
    }

    .container {
      position: relative;
      width: 100%;
      max-width: 380px;
      background: #ffffff;
      border-radius: 24px;
      padding: 20px;
      text-align: center;
      z-index: 1;
    }

    .header {
      margin-bottom: 20px;
    }

    .header img.logo {
      max-width: 200px;
    }

    .form-step {
      display: none;
    }

    .form-step.active {
      display: block;
    }

    .form .input, .form select {
      width: 100%;
      padding: 14px 18px;
      margin-bottom: 15px;
      border-radius: 12px;
      border: 1.5px solid #d1d5db;
      font-size: 14px;
      background: #fff;
      transition: border 0.3s ease;
    }

    .form .input:focus, .form select:focus {
      outline: none;
      border-color: #3b82f6;
    }

    .wizard-buttons {
      display: flex;
      justify-content: space-between;
      margin-top: 10px;
    }

    .wizard-buttons button {
      flex: 1;
      margin: 5px;
      padding: 14px;
      border-radius: 12px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      font-size: 15px;
    }

    .next-button {
      background: linear-gradient(90deg, #2563eb, #3b82f6);
      color: white;
    }

    .back-button {
      background: #e5e7eb;
      color: #374151;
    }

    .agreement {
      font-size: 12px;
      color: #6b7280;
      margin-top: 20px;
    }

    .agreement a {
      color: #3b82f6;
      text-decoration: none;
    }

    .success-message {
      font-size: 16px;
      color: green;
      display: none;
    }

    .step-indicator {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 20px;
}

.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: #e5e7eb;
  color: #374151;
  font-weight: 600;
  display: flex;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease;
}

.step-circle.active {
  background: linear-gradient(135deg, #004BA8, #3b82f6);
  color: white;
  transform: scale(1.1);
}

.step-line {
  height: 2px;
  width: 40px;
  background-color: #d1d5db;
  margin: 0 10px;
}

.input-password {
  position: relative;
  width: 100%;
  margin-bottom: 15px;
}

.input-password .input {
  padding-right: 40px;
}

.toggle-password {
  position: absolute;
  right: 14px;
  top: 40%;
  transform: translateY(-50%);
  cursor: pointer;
  font-size: 16px;
  color: #6b7280;
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
}

.pulse-animation {
  animation: pulse 1s infinite;
}
  </style>
</head>
<body>
  <div class="glow-border">
    <div class="container">
      <div class="header">
        <img src="image/logo_login.png" alt="POWERMOTOR Logo" class="logo">
        <h3 style="margin-top: 10px;">Crea tu cuenta</h3>
      </div>
      
      <div id="messageCard" class="alert alert-danger" role="alert" style="display: none; margin-bottom: 15px;"></div>
<!-- justo antes de <form class="form" id="registrationForm"> -->
<div class="step-indicator">
  <div class="step-circle active">1</div>
  <div class="step-line"></div>
  <div class="step-circle">2</div>
</div>
      <form class="form" id="registrationForm">
        <!-- Paso 1 -->
        <div class="form-step active" id="step1">
          <select name="tipoDocumento" required>
            <option value="">Tipo de documento</option>
          </select>

          <input class="input" type="text" name="numeroDocumento" placeholder="Número de documento" required />
          <input class="input" type="date" name="fechaNacimiento" required />
        </div>

        <!-- Paso 2 -->
<div class="form-step" id="step2">
  <input class="input" type="text" name="nombres" placeholder="Nombres y apellidos completos" id="nombres" required style="text-transform: uppercase;" />
  <input class="input" type="email" name="email" placeholder="Correo electrónico" required />
  <input class="input" type="tel" name="celular" id="celular" placeholder="Número de celular" required maxlength="9" pattern="\d{9}" />

  <div class="input-password">
  <input class="input" type="password" name="password" id="passwordInput" placeholder="Contraseña" required />
  <span class="toggle-password">
    <i class="fa-solid fa-eye" id="eyeIcon"></i>
  </span>
</div>
<input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
</div>

        <!-- Botones -->
        <div class="wizard-buttons">
          <button type="button" class="back-button" id="prevBtn">Atrás</button>
          <button type="button" class="next-button" id="nextBtn">Siguiente</button>
        </div>
      </form>

      <div class="agreement">
        ¿Ya tienes cuenta? <a href="index.php">Inicia sesión</a>
      </div>

      <div class="success-message" id="successMessage">
        ¡Registro completado con éxito!
      </div>
    </div>
  </div>

  <script>
  



// Forzar MAYÚSCULAS en nombres y apellidos
document.getElementById('nombres').addEventListener('input', function () {
  this.value = this.value.toUpperCase();
});

// Validación numérica exacta para celular (solo números y 8 dígitos)
document.getElementById('celular').addEventListener('input', function () {
  this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
});

fetch("template/assets/ajax/documentoIdentificacion_ajax.php?action=listarDocumentosRegistroUsuario")
  .then(res => res.json())
  .then(data => {
    const select = document.querySelector("select[name='tipoDocumento']");
    select.innerHTML = '<option value="">Seleccione</option>';
    data.data.forEach(item => {
      const option = document.createElement("option");
      option.value = item.id_docidentificacion_sunat;
      option.textContent = item.abreviatura_docIdentificacion;
      select.appendChild(option);
    });
  })
  .catch(err => console.error("Error cargando documentos:", err));

</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="template/js/NuevoUsuario.js"></script>
</body>
</html>