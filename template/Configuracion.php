<?php include('partial/header.php'); ?>
<?php include('partial/loader.php'); ?>


<style>
  .card-header h5 {
    font-size: 1rem;
    margin-bottom: 0;
  }

  .preview-logo {
    max-height: 200px;
    max-width: 100%;
    object-fit: contain;
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

          <!-- PERFIL DE EMPRESA -->
          <div class="col-md-12">
            <div class="card shadow-sm border-0">
              <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5><i class="fa fa-bank me-2"></i>Perfil de la Empresa</h5>
              </div>
              <div class="card-body">
                <form id="form_empresa">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label"><i class="fa fa-industry me-1"></i> Razón Social</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="razon_social">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label"><i class="fa fa-shopping-bag me-1"></i> Nombre Comercial</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="nombre_comercial">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label"><i class="fa fa-id-card me-1"></i> Tipo Documento</label>
                      <select class="form-select btn-pill input-air-primary" name="id_doc_identificacion">
                        <option value="6">RUC - REG. UNICO DE CONTRIBUYENTES</option>
                        <option value="1">DNI - DOC. NACIONAL DE IDENTIDAD</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label"><i class="fa fa-hashtag me-1"></i> Número Documento</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="nro_documento">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label"><i class="fa fa-user me-1"></i> Representante Legal</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="representante">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label"><i class="fa fa-map-marker me-1"></i> Dirección Fiscal</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="direccion_fiscal">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label"><i class="fa fa-phone me-1"></i> Teléfono</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="telefono">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label"><i class="fa fa-envelope me-1"></i> Email</label>
                      <input type="email" class="form-control btn-pill input-air-primary" name="email">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label"><i class="fa fa-globe me-1"></i> Web</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="web">
                    </div>
                    <div class="col-md-8">
                      <label class="form-label"><i class="fa fa-image me-1"></i> Logo</label>
                      <div class="input-group">
                        <input type="file" class="form-control btn-pill input-air-primary" name="logo_path" id="logoInput">
                        <button class="btn btn-outline-secondary btn-sm" type="button" id="btnPreviewLogo" data-bs-toggle="modal" data-bs-target="#modalLogoPreview">
                          <i class="fa fa-eye"></i> Vista previa
                        </button>
                      </div>
                    </div>
                    <div class="col-md-12 mt-3">
                      <button class="btn btn-primary btn-pill w-100">
                        <i class="fa fa-save me-1"></i> Guardar Empresa
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- SECCIÓN DE SEDES -->
<div class="col-md-12">
  <div class="card shadow-sm border-0 mt-4">
    <div class="card-header d-flex justify-content-between align-items-center bg-secondary text-white">
      <h5><i class="fa fa-map-pin me-2"></i>Sedes de la Empresa</h5>
      <button type="button" class="btn btn-success btn-pill btn-sm" id="btnAgregarSede">
  <i class="fa fa-plus me-1"></i>Agregar Sede
</button>
    </div>
    <div class="card-body">

      <!-- Formulario para agregar sede manualmente -->
      <div id="formulario_sede">
        <div class="card mb-3 border-light shadow-sm">
          <div class="card-body row g-3">
            <div class="col-md-3">
              <label><i class="fa fa-tag me-1"></i> Nombre</label>
              <input type="text" class="form-control btn-pill input-air-primary" name="nombre_sede[]">
            </div>
            <div class="col-md-3">
              <label><i class="fa fa-pencil-square-o me-1"></i> Alias</label>
              <input type="text" class="form-control btn-pill input-air-primary" name="seudonimo_sede[]">
            </div>
            <div class="col-md-4">
              <label><i class="fa fa-map me-1"></i> Dirección</label>
              <input type="text" class="form-control btn-pill input-air-primary" name="direccion_sede[]">
            </div>
            <div class="col-md-2">
              <label><i class="fa fa-phone-square me-1"></i> Teléfono</label>
              <input type="text" class="form-control btn-pill input-air-primary" name="telefono_sede[]">
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12 text-end">
  <button type="button" id="btnModificarSede" class="btn btn-warning btn-pill mt-2" style="display:none;">
    <i class="fa fa-save me-1"></i> Modificar Sede
  </button>
</div>

      <hr class="my-4" />

      <!-- Tabla dinámica de sedes -->
     <h6 class="text-muted"><i class="fa fa-file-alt me-1"></i>Sedes Registradas</h6>
<div id="tabla_sedes">
  <div class="table-responsive">
    <table class="table table-bordered mt-2 align-middle">
      <thead class="table-light">
        <tr>
          <th>Nombre</th>
          <th>Dirección</th>
          <th>Teléfono</th>
          <th>Alias</th>
          <th>Estado</th>
          <th>Series</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody id="tablaSedesBody">
        <!-- Rellenado dinámicamente -->
      </tbody>
    </table>
  </div>
</div>

    </div>
  </div>
</div>
          

          <!-- CONFIGURACIÓN FE Y SERIES -->
          <div class="col-md-12">
            <div class="card shadow-sm border-0 mt-4">
              <div class="card-header bg-dark text-white">
                <h5><i class="fa fa-cogs me-2"></i>Configuración SUNAT y Comprobantes</h5>
              </div>
              <div class="card-body">
                <form id="form_config_sunat">
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label><i class="fa fa-toggle-on me-1"></i> Modo de Envío</label>
                      <select class="form-select btn-pill input-air-primary" name="modo_envio">
                        <option value="DESARROLLO">Desarrollo</option>
                        <option value="PRODUCCION">Producción</option>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label><i class="fa fa-user me-1"></i> Usuario SOL</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="usuario_sol">
                    </div>
                    <div class="col-md-4">
                      <label><i class="fa fa-key me-1"></i> Clave SOL</label>
                      <input type="password" class="form-control btn-pill input-air-primary" name="clave_sol">
                    </div>
                    <div class="col-md-4">
                      <label><i class="fa fa-certificate me-1"></i> Certificado Digital</label>
                      <input type="text" class="form-control btn-pill input-air-primary" name="nombre_certificado">
                    </div>
                    <div class="col-md-4">
                      <label><i class="fa fa-lock me-1"></i> Clave del Certificado</label>
                      <input type="password" class="form-control btn-pill input-air-primary" name="clave_certificado">
                    </div>
                    <div class="col-md-2">
                      <label><i class="fa fa-calendar me-1"></i> Fecha Inicio</label>
                      <input type="date" class="form-control btn-pill input-air-primary" name="fecha_inicio_cert">
                    </div>
                    <div class="col-md-2">
                      <label><i class="fa fa-calendar-check-o me-1"></i> Fecha Fin</label>
                      <input type="date" class="form-control btn-pill input-air-primary" name="fecha_fin_cert">
                    </div>
                    <div class="col-md-6">
                      <label><i class="fa fa-flask me-1"></i> WSDL Pruebas</label>
                      <textarea class="form-control btn-pill input-air-primary" name="wsdl_pruebas" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                      <label><i class="fa fa-sitemap me-1"></i> WSDL Producción</label>
                      <textarea class="form-control btn-pill input-air-primary" name="wsdl_produccion" rows="2"></textarea>
                    </div>
                    <div class="col-md-12 mt-3">
                      <button class="btn btn-primary btn-pill w-100">
                        <i class="fa fa-save me-1"></i> Guardar Configuración
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>

        </div> <!-- end row -->
      </div>
    </div>
    <?php include('partial/footer.php'); ?>
  </div>
</div>

<!-- MODAL VISTA PREVIA LOGO -->
<div class="modal fade" id="modalLogoPreview" tabindex="-1" aria-labelledby="modalLogoPreviewLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalLogoPreviewLabel"><i class="fa fa-image me-1"></i> Vista Previa del Logo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="logoPreviewModal" class="preview-logo" src="#" alt="Vista previa del logo">
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSerie" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content" id="formSerie">
      <input type="hidden" name="id_empresa" id="inputEmpresaHidden">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-plus-circle me-1"></i> Serie y Correlativo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body row g-3">
        <input type="hidden" name="id_sede">
        <input type="hidden" name="id_serie_documento"> <!-- ✅ CORRECTO -->
        <div class="col-md-6">
          <label>Tipo Comprobante</label>
          <select class="form-select btn-pill" name="tipo_comprobante">
  <!-- Esto se carga dinámicamente desde JS -->
</select>
<div id="infoExtraSerie"></div>
          <div id="infoExtraSerie"></div>
        </div>
        <div class="col-md-3">
          <label>Serie</label>
          <input type="text" class="form-control btn-pill" name="serie">
        </div>
        <div class="col-md-3">
          <label>Correlativo</label>
          <input type="number" class="form-control btn-pill" name="correlativo_actual">
          <input type="hidden" name="estado" value="1">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary btn-pill" type="submit"><i class="fa fa-save me-1"></i>Guardar</button>
      </div>
    </form>
  </div>
</div>



<?php include('partial/scripts.php'); ?>
<script src="assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php'); ?>
<script src="js/Configuracion.js"></script>