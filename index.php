<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

require_once "template/assets/seguridad/redirigir_si_autenticado.php";
// require_once "assets/seguridad/verificar_sesion.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Repuestos Nazca</title>
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

    /* 🌈 Contenedor del borde animado degradado */
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
      background: linear-gradient(135deg, #004BA8); /* morado a azul */
      border-radius: 10px;
      opacity: 0.5; /* 🔹 sombra más opaca */
      filter: blur(16px); /* 🔹 sombra difusa */
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

    @keyframes pulse {
  0%, 100% {
    transform: scale(1.0);
  }
  50% {
    transform: scale(1.15);
  }
}

.pulse-animation {
  animation: pulse 1.5s infinite;
}

    .container {
      position: relative;
      width: 100%;
      max-width: 380px;
      background: #ffffff;
      border-radius: 24px;
      padding: 15px;
      text-align: center;
      z-index: 1;
    }

    .flag-inline {
      position: absolute;
      top: 20px;
      right: 20px;
    }

    .flag-inline img {
      max-height: 30px;
      border-radius: 4px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .header {
      margin-bottom: 1px;
    }

    .header img.logo {
      max-width: 200px;
    }

    .form .input {
      width: 100%;
      padding: 14px 18px;
      margin-bottom: 15px;
      border-radius: 12px;
      border: 1.5px solid #d1d5db;
      font-size: 14px;
      background: #fff;
      transition: border 0.3s ease;
    }

    .form .input:focus {
      outline: none;
      border-color: #3b82f6;
    }

    .forgot-password {
      text-align: right;
      font-size: 12px;
      margin-bottom: 20px;
    }

    .forgot-password a {
      color: #3b82f6;
      text-decoration: none;
    }

    .login-button {
      width: 100%;
      background: linear-gradient(90deg, #2563eb, #3b82f6);
      color: white;
      padding: 14px;
      font-weight: 600;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .login-button:hover {
      transform: scale(1.02);
    }

    .agreement {
      text-align: center;
      font-size: 13px;
      color: #6b7280;
      margin-top: 20px;
    }

    .agreement a {
      color: #3b82f6;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="glow-border">
    <div class="container">
      <div class="flag-inline">
        <img src="image/banderaPeru.png" alt="Bandera del Perú">
      </div>

      <div class="header">
        <img src="image/logo_login.png" alt="POWERMOTOR Logo" class="logo" id="logoPrincipal">
      </div>

      <form class="form" id="loginForm">
  <input class="input" type="text" name="numeroDocumento" placeholder="Número de documento" required />
  <input class="input" type="password" name="password" placeholder="Contraseña" required />
  <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
  <div class="forgot-password">
    <a href="#">¿Olvidaste tu contraseña?</a>
  </div>
  <button type="submit" class="login-button" id="loginBtn">Ingresar</button>
</form>

<!-- Mensaje de alerta universal (error o redirección) -->
<div id="messageCard" class="alert mt-3" role="alert" style="display:none;"></div>

<div id="loaderContainer" style="display: none; text-align:center; margin-top: 30px;">
  <img src="image/logo_login.png" alt="Cargando..." style="width: 120px;" class="pulse-animation">
  <p style="margin-top: 10px;">Redirigiendo al sistema...</p>
</div>

      <div class="agreement">
      ¿No tienes cuenta? <a href="registroUsuario.php">Regístrate aquí</a>
      </div>
    </div>
  </div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
function mostrarMensaje(mensaje, tipo = "danger") {
  const card = document.getElementById("messageCard");
  card.style.display = "block";
  card.className = `alert alert-${tipo} mt-3`;
  card.innerHTML = tipo === "success"
    ? `<i class="fa-solid fa-check-circle me-2"></i>${mensaje}`
    : `<i class="fa-solid fa-triangle-exclamation me-2"></i>${mensaje}`;
}

document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const form = e.target;
  const btn = document.getElementById("loginBtn");

  const numeroDocumento = form.numeroDocumento.value.trim();
  const passwordPlano = form.password.value;

  const formData = new FormData();
  formData.append("numeroDocumento", numeroDocumento);
  formData.append("password", btoa(passwordPlano)); // 👈 Codificación en Base64
  formData.append("csrf_token", form.csrf_token.value);

  btn.innerHTML = `<i class="fa fa-spinner fa-spin me-2"></i> Verificando...`;
  btn.disabled = true;

  fetch("template/assets/ajax/usuario_ajax.php?action=login", {
    method: "POST",
    body: formData,
  })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        mostrarMensaje("Autenticación exitosa. Redirigiendo al sistema...", "success");

        form.style.display = "none";
        document.getElementById("logoPrincipal").classList.add("pulse-animation");

        setTimeout(() => {
          window.location.href = "template/MenuPrincipal.php";
        }, 3000);
      } else {
        mostrarMensaje(data.message || "Error al iniciar sesión", "warning");
        btn.disabled = false;
        btn.innerHTML = "Ingresar";
      }
    })
    .catch(() => {
      mostrarMensaje("Error de red o servidor", "danger");
      btn.disabled = false;
      btn.innerHTML = "Ingresar";
    });
});
</script>
</body>
</html>