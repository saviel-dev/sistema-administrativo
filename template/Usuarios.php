<?php include('partial/header.php'); ?>
<link rel="stylesheet" type="text/css" href="assets/css/vendors/animate.css">
<?php include('partial/loader.php'); ?>

<style>
  .dataTables_filter input {
    border: 2px solid #7366FF !important;
    background-color: white;
    color: #212529;
    outline: none;
    padding: 0.375rem 1.25rem;
    box-shadow: none;
    transition: border-color 0.15s ease-in-out;
    width: 100%;
    max-width: 100%;
    border-radius: 50rem !important;
  }

  .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
  }

  @media (max-width: 768px) {
    .dataTables_filter {
      width: 100%;
    }

    .dataTables_filter label {
      flex-direction: column;
      align-items: stretch;
      width: 100%;
    }

    .dataTables_filter input {
      width: 100% !important;
      box-sizing: border-box;
    }
  }

  /* UX suaves para el modal */
.avatar-pill {
  width: 40px;
  height: 40px;
  font-size: 1.1rem;
}

.input-group-icon .input-group-text {
  background: #f6f7fb;
  border-right: 0;
}
.input-group-icon .form-control,
.input-group-icon .form-select {
  border-left: 0;
}

.nav-tabs .nav-link {
  border: 0;
  color: #6c757d;
}
.nav-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom: 2px solid #0d6efd;
  background: transparent;
}

.modal-content {
  border-radius: 12px;
}

.modal-header {
  border-top-left-radius: 12px;
  border-top-right-radius: 12px;
}
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <?php include('partial/topbar.php'); ?>
  <div class="page-body-wrapper">
    <?php include('partial/sidebar.php'); ?>
    <div class="page-body">
      <?php include('partial/breadcrumb.php'); ?>

      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card card-table">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Usuarios</h5>
                <!-- Botón para crear nuevo usuario (futuro) -->
                <!-- <button class="btn btn-primary btn-pill">Nuevo Usuario</button> -->
              </div>

              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover" id="tabla-usuarios">
                    <thead class="table-secondary text-center">
                      <tr>
                        <th>Cód.</th>
                        <th>Usuario</th>
                        <th>Teléfonos</th>
                        <th>Sede</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </div>
    <?php include('partial/footer.php'); ?>
  </div>
</div>

<!-- MODAL VER / EDITAR USUARIO -->
<!-- MODAL: Ver / Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="tituloEditarUsuario" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <form id="formEditarUsuario" autocomplete="off">
        <div class="modal-header bg-primary text-white align-items-start">
          <div class="d-flex align-items-center">
            <div class="me-3 rounded-circle bg-white text-primary d-flex align-items-center justify-content-center avatar-pill">
              <i class="fa fa-user"></i>
            </div>
            <div>
              <h5 class="modal-title mb-0" id="tituloEditarUsuario">
                Ver / Editar Usuario
              </h5>
              <small class="opacity-75">
                <i class="fa fa-id-badge"></i>
                <span id="subtitleUsuario">Actualiza datos personales, contacto y permisos</span>
              </small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_usuario" id="edit_id_usuario">

          <!-- Tabs -->
          <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
            <li class="nav-item" role="presentation">
              <a class="nav-link active" id="tab-perfil" data-bs-toggle="tab" href="#pane-perfil" role="tab" aria-controls="pane-perfil" aria-selected="true">
                <i class="fa fa-user-circle"></i> Perfil
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="tab-contacto" data-bs-toggle="tab" href="#pane-contacto" role="tab" aria-controls="pane-contacto" aria-selected="false">
                <i class="fa fa-phone"></i> Contacto
              </a>
            </li>
            <li class="nav-item" role="presentation">
              <a class="nav-link" id="tab-permisos" data-bs-toggle="tab" href="#pane-permisos" role="tab" aria-controls="pane-permisos" aria-selected="false">
                <i class="fa fa-shield"></i> Permisos
              </a>
            </li>
          </ul>

          <div class="tab-content">
            <!-- PERFIL -->
            <div class="tab-pane fade show active" id="pane-perfil" role="tabpanel" aria-labelledby="tab-perfil">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label">Nombres y Apellidos</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-user"></i></span>
                    <input type="text" class="form-control btn-pill" name="nombres_apellidos_usuario" id="edit_nombres" required>
                  </div>
                  <small class="text-muted"><i class="fa fa-info-circle"></i> Debe coincidir con el documento.</small>
                </div>

                <div class="col-md-4">
                  <label class="form-label">Fecha Nacimiento</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    <input type="date" class="form-control btn-pill" name="fechaNacimiento_usuario" id="edit_fechaNacimiento" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Tipo de Documento</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                    <select class="form-select btn-pill" id="edit_tipo_documento" name="id_tipoDocumento" required></select>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Número Documento</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                    <input type="text" class="form-control btn-pill" name="numeroDocumento_usuario" id="edit_numero_documento" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- CONTACTO -->
            <div class="tab-pane fade" id="pane-contacto" role="tabpanel" aria-labelledby="tab-contacto">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Teléfono</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-phone"></i></span>
                    <input type="text" class="form-control btn-pill" name="numeroCelular" id="edit_telefono" placeholder="+51 900 000 000">
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Correo Electrónico</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                    <input type="email" class="form-control btn-pill" name="email" id="edit_email" placeholder="correo@dominio.com">
                  </div>
                </div>
              </div>
            </div>

            <!-- PERMISOS -->
            <div class="tab-pane fade" id="pane-permisos" role="tabpanel" aria-labelledby="tab-permisos">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Sede</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-building"></i></span>
                    <select class="form-select btn-pill" id="edit_sede" name="id_sede" required></select>
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Rol</label>
                  <div class="input-group input-group-icon">
                    <span class="input-group-text"><i class="fa fa-user-secret"></i></span>
                    <select class="form-select btn-pill" id="edit_rol" name="id_rol" required></select>
                  </div>
                </div>

                <div class="col-12">
                  <div class="alert alert-light border d-flex align-items-center py-2 mb-0">
                    <i class="fa fa-info-circle text-primary me-2"></i>
                    <small class="text-muted">Los cambios de sede y rol afectan accesos y permisos en el sistema.</small>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer d-flex justify-content-between">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border"><i class="fa fa-clock-o"></i> Cambios no guardados</span>
          </div>
          <div class="d-flex gap-2">
            <button type="button" class="btn btn-light btn-pill" data-bs-dismiss="modal">
              <i class="fa fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn btn-primary btn-pill">
              <i class="fa fa-save"></i> Guardar cambios
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('partial/scripts.php'); ?>
<script src="assets/js/animation/animate-custom.js"></script>
<?php include('partial/footer-end.php'); ?>
<script src="js/Usuarios.js"></script>