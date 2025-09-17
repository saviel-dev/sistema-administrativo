<?php include('partial/header.php'); ?>

<link rel="stylesheet" type="text/css" href="assets/css/vendors/echart.css">

<?php include('partial/loader.php'); ?>

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php'); ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper sidebar-icon">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php'); ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php'); ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Basic Area Chart </h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="area-echart"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Area pie chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="echart-pie"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar polar chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="echart-polar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer-large" id="echart-bar"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Bar chart 2</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer-large" id="echart-bar2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Boxplot chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer boxplot-cotainer" id="echart-boxplot"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>lines clip chart</h5>
                            </div>
                            <div class="card-body clip-chart-wrapper">
                                <div class="apache-cotainer" id="lines-clip"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>line visual chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="line-visual"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>angle chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="texture-bar-by-clipPath"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>gauge chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="echart-gauge"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>line visual chart 2</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="line-visulH"> </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>pictorial repeat chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="dynamic-data"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 xl-100 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>radar chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-cotainer" id="echart-radar"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6 xl-100 box-col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>polar line chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-container polar-line" id="main0"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>pie rich text chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-container" id="echart-pierich"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>pictorial single chart</h5>
                            </div>
                            <div class="card-body">
                                <div class="apache-container" id="dotted"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12">
                        <div class="card note p-20 d-inline-block w-100"> Echarts for more options please refer <a href="https://echarts.apache.org/examples/en/index.php" target="_blank">echart
                                Official site </a>And <a href="https://github.com/apache/echarts" target="_blank">githup
                                link</a></div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        <?php include('partial/footer.php'); ?>
    </div>
</div>

<?php include('partial/scripts.php'); ?>

<script src="assets/js/chart/echart/esl.js"></script>
<script src="assets/js/chart/echart/config.js"></script>
<script src="assets/js/chart/echart/pie-chart/facePrint.js"></script>
<script src="assets/js/chart/echart/pie-chart/testHelper.js"></script>
<script src="assets/js/chart/echart/pie-chart/custom-transition-texture.js"></script>
<script src="assets/js/chart/echart/data/symbols.js"></script>
<script src="assets/js/chart/echart/custom.js"></script>
<script src="assets/js/tooltip-init.js"></script>



<?php include('partial/footer-end.php'); ?>