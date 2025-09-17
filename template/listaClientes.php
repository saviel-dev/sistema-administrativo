<?php include('partial/header.php'); ?>

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
</style>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12 col-lg-12 col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <button class="btn btn-pill btn-primary btn-air-primary mb-3" id="AgregarCliente"><i class="fa fa-plus"></i> Nuevo cliente</button>
                                <div class="table-responsive">
                                    <table class="table table-responsive table-hover" id="tabla-clientes">
                                        <thead class="table-dark">
                                            <tr class="text-center">
                                                <th>Cód.</th>
                                                <th>Razon Social</th>
                                                <th>Documento</th>
                                                <th>Teléfonos</th>
                                                <th>
                                                    Sunat
                                                    <i class="fa fa-question-circle ms-1"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="La información SUNAT no es en tiempo real, está ACTUALIZADA en la fecha de registro."></i>
                                                </th>
                                                <th>Registro</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>

        <?php include('partial/footer.php'); ?>
    </div>
</div>

<!-- MODAL CLIENTE -->
<div class="modal fade" id="modalCliente" tabindex="-1" aria-labelledby="tituloModalCliente" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formCliente" autocomplete="off">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="tituloModalCliente">Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div class="row g-3">

                        <!-- Identificación -->
                        <div class="col-12">
                            <h6 class="text-primary fw-semibold">Identificación Sunat</h6>
                            <hr>
                        </div>

                        <!-- Código de Cliente (solo lectura) -->
                        <div class="col-md-2">
                            <label class="form-label">Código Cliente</label>
                            <input type="text" class="form-control btn-pill bg-primary text-center" name="codigo_cliente" readonly>
                        </div>

                        <!-- Nro Documento + Botón de Búsqueda -->
                        <div class="col-md-5">
                            <label class="form-label">Nro. Documento <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control btn-pill input-air-primary" name="nro_documento" id="nro_documento" required>
                                <button type="button" class="btn btn-info btn-pill px-3" id="btnBuscarSunat" title="Buscar en SUNAT o RENIEC">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                            <small class="text-muted">8 dígitos = DNI | 11 dígitos = RUC</small>
                        </div>

                        <!-- Tipo Documento -->
                        <div class="col-md-5">
                            <label class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                            <select class="form-select btn-pill input-air-primary" name="id_doc_identificacion" required>
                                <option value="">Seleccione</option>

                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado Sunat <span class="text-danger">*</span></label>
                            <select class="form-select btn-pill input-air-primary" name="estadoSunat">
                                <option value="">Seleccione</option>
                                <option value="ACTIVO">Activo</option>
                                <option value="SUSPENSIÓN TEMPORAL">Suspensión Temporal</option>
                                <option value="BAJA PROVISIONAL">Baja provisional</option>
                                <option value="BAJA DEFINITIVA">Baja definitiva</option>
                                <option value="BAJA DE OFICIO">Baja de oficio</option>
                                <option value="BAJA DEFINITIVA DE OFICIO">Baja definitiva de oficio</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Condicion Sunat</label>
                            <select class="form-select btn-pill input-air-primary" name="condicionSunat">
                                <option value="">Seleccione</option>
                                <option value="HABIDO">Habido</option>
                                <option value="NO HABIDO">No habido</option>
                            </select>
                        </div>

                        <!-- Razón social / Nombre comercial -->
                        <div class="col-md-6">
                            <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="razon_social" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="nombre_comercial">
                        </div>

                        <!-- Ubicación -->
                        <div class="col-12">
                            <h6 class="text-primary fw-semibold mt-3">Ubicación</h6>
                            <hr>
                        </div>

                        <div class="col--12">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="direccion">
                            <input type="hidden" name="codigo_ubigeo" id="codigo_ubigeo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Departamento</label>
                            <select class="form-select btn-pill input-air-primary" name="departamento"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Provincia</label>
                            <select class="form-select btn-pill input-air-primary" name="provincia"></select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distrito</label>
                            <select class="form-select btn-pill input-air-primary" name="distrito"></select>
                        </div>

                        <!-- Contacto -->
                        <div class="col-12">
                            <h6 class="text-primary fw-semibold mt-3">Contacto</h6>
                            <hr>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Representante</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="representante">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control btn-pill input-air-primary" name="email">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Teléfono Fijo</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="telefono_fijo">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Celular 1</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="celular_1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Celular 2</label>
                            <input type="text" class="form-control btn-pill input-air-primary" name="celular_2">
                        </div>

                        <!-- Clasificación -->
                        <div class="col-12">
                            <h6 class="text-primary fw-semibold mt-3">Clasificación y Notas</h6>
                            <hr>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Clasificación</label>
                            <select class="form-select btn-pill input-air-primary" name="ClasificacionCliente">
                                <option value="0">Sin clasificar</option>
                                <option value="1">Bajo</option>
                                <option value="2">Medio</option>
                                <option value="3">Alto</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Límite de Crédito</label>
                            <input type="number" class="form-control btn-pill input-air-primary" name="limite_credito" step="0.01">
                        </div>


                        <div class="col-12">
                            <label class="form-label">Notas</label>
                            <textarea class="form-control btn-pill input-air-primary" name="notas" rows="2"></textarea>
                        </div>

                        <!-- Hidden ID -->
                        <input type="hidden" name="id_cliente">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-pill" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-pill btn-guardar">Guardar</button>
                    <button type="button" class="btn btn-warning btn-pill d-none" id="btnModificar">Modificar</button>
                </div>
            </form>
        </div>
    </div>
</div>


<?php include('partial/scripts.php'); ?>
<!-- <script src="assets/js/tooltip-init.js"></script> -->

<!-- jQuery -->
<!-- <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script> -->

<!-- DataTables Core -->
<!-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script> -->

<!-- Botones -->
<!-- <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script> -->

<!-- Exportación -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script> -->


<script src="assets/js/tooltip-init.js"></script>
<script src="js/Cliente.js"></script>
<?php include('partial/footer-end.php'); ?>