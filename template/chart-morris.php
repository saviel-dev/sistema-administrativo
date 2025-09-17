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
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Line Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="morris-line-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Updating Data</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="updating-data-morris-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Decimal Data</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder float-start" id="decimal-morris-chart"></div>
                                    <p class="float-end" id="choices"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Displaying X Labels Diagonally</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="x-Labels-Diagonally-morris-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar Line Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="bar-line-chart-morris"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Displaying X Labels Diagonally(Bar Chart)</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="x-lable-morris-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Stacked Bars Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="stacked-bar-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Simple Bar Charts</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="morris-simple-bar-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Area charts behaving like line Charts</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="graph123"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Donut Color Chart</h5>
                            </div>
                            <div class="card-body chart-block">
                                <div class="flot-chart-container">
                                    <div class="flot-chart-placeholder" id="donut-color-chart-morris"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        
        <?php include('partial/footer.php');?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>

<script src="assets/js/chart/morris-chart/raphael.js"></script>
<script src="assets/js/chart/morris-chart/morris.js"> </script>
<script src="assets/js/chart/morris-chart/prettify.min.js"></script>
<script src="assets/js/chart/morris-chart/morris-script.js"></script>
<script src="assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>