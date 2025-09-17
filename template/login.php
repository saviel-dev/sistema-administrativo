<?php
define('TEMPLATE_PATH', __DIR__ . '/template/');
define('TEMPLATE_WEB', 'template/'); // Para HTML (src, href, etc.)
include('partial/header.php'); ?>

<div class="container-fluid p-0">
  <div class="row m-0">
    <div class="col-12 p-0">
      <div class="login-card">
        <div>
          <div><a class="logo" href="index.php"><img class="img-fluid for-light" src="assets/images/logo/login.png" alt="looginpage"><img class="img-fluid for-dark" src="assets/images/logo/logo_dark.png" alt="looginpage"></a></div>
          <div class="login-main">
            <form class="theme-form">
              <h4>Sign in to account</h4>
              <p>Enter your email & password to login</p>
              <div class="form-group">
                <label class="col-form-label">Email Address</label>
                <input class="form-control btn-pill input-air-primary" type="text" required="" placeholder="">
              </div>
              <div class="form-group">
                <label class="col-form-label">Password</label>
                <div class="form-input position-relative">
                  <input class="form-control btn-pill input-air-primary" type="password" name="login[password]" required="" placeholder="*********">
                  <div class="show-hide"><span class="show"> </span></div>
                </div>
              </div>
              <div class="form-group mb-0">
                <div class="checkbox p-0">
                  <input id="checkbox1" type="checkbox">
                  <label class="text-muted" for="checkbox1">Remember password</label>
                </div><a class="link" href="forget-password.html">¿Recuperar contraseña?</a>
                <div class="text-end mt-3">
                  <button class="btn btn-primary btn-pill btn-block w-100" type="submit">Iniciar Sesión</button>
                </div>
              </div>
              <p class="mt-4 mb-0 text-center">Don't have account?<a class="ms-2" href="sign-up.html">Create Account</a></p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php include('partial/scripts.php'); ?>
</div>

<?php include('partial/footer-end.php'); ?>