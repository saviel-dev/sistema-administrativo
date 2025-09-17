<?php include('partial/header.php');?>         

<?php include('partial/loader.php');?>

<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <!-- Page Header Start-->
  <?php include('partial/topbar.php');?>
  <!-- Page Header Ends -->
  <!-- Page Body Start-->
  <div class="page-body-wrapper">
    <!-- Page Sidebar Start-->
    <?php include('partial/sidebar.php');?>
    <!-- Page Sidebar Ends-->
    <div class="page-body">
      <?php include('partial/breadcrumb.php'); ?>
      <!-- Container-fluid starts-->
      <div class="form-builder">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <div class="card">
                <div class="card-header">
                  <h5>Drag & Drop components</h5><span>Copy the html code from View HTML tab </span>
                </div>
                <div class="card-body">
                  <div class="row clearfix form-builder">
                    <div class="col-lg-12 col-xl-6">
                      <div class="form-builder-header-1">
                        <ul class="nav nav-primary" id="formtabs"></ul>
                      </div>
                      <form class="form-horizontal theme-form" id="components">
                        <fieldset>
                          <div class="tab-content"></div>
                        </fieldset>
                      </form>
                    </div>
                    <div class="col-lg-12 col-xl-6 lg-mt-col">
                      <div class="form-builder-header-1">
                        <h6 class="mt-0">Drag Elements Here</h6>
                      </div>
                      <div id="build">
                        <form class="form-horizontal drag-box" id="target"></form>
                      </div>
                    </div>
                  </div>
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
    
<?php include('partial/scripts.php'); ?>

<script src="assets/js/counter/jquery.waypoints.min.js"></script>
<script src="assets/js/counter/jquery.counterup.min.js"></script>
<script src="assets/js/counter/counter-custom.js"></script>
<script src="assets/js/tooltip-init.js"></script>
<script src="assets/js/form-builder/form-builder-1/require.js"></script>
<script src="assets/js/form-builder/form-builder-1/main-built.js"></script>
<?php include('partial/footer-end.php'); ?>