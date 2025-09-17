<?php include('partial/header.php'); ?>

<?php include('partial/loader.php'); ?>

<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
    <!-- Page Header Start-->
    <?php include('partial/topbar.php') ; ?>
    <!-- Page Header Ends -->
    <!-- Page Body Start-->
    <div class="page-body-wrapper">
        <!-- Page Sidebar Start-->
        <?php include('partial/sidebar.php') ; ?>
        <!-- Page Sidebar Ends-->
        <div class="page-body">
            <?php include('partial/breadcrumb.php') ; ?>
            <!-- Container-fluid starts-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-3 box-col-6 pe-0">
                        <div class="file-sidebar">
                            <div class="card">
                                <div class="card-body">
                                    <ul>
                                        <li>
                                            <div class="btn btn-primary"><i data-feather="home"> </i>Home </div>
                                        </li>
                                        <li>
                                            <div class="btn btn-light"><i data-feather="folder"></i>All </div>
                                        </li>
                                        <li>
                                            <div class="btn btn-light"><i data-feather="clock"></i>Recent </div>
                                        </li>
                                        <li>
                                            <div class="btn btn-light"><i data-feather="star"></i>Starred </div>
                                        </li>
                                        <li>
                                            <div class="btn btn-light"><i data-feather="alert-circle"></i>Recovery
                                            </div>
                                        </li>
                                        <li>
                                            <div class="btn btn-light"><i data-feather="trash-2"></i>Deleted </div>
                                        </li>
                                    </ul>
                                    <hr>
                                    <ul>
                                        <li>
                                            <div class="btn btn-outline-primary"><i data-feather="database"> </i>Storage
                                            </div>
                                            <div class="m-t-15">
                                                <div class="progress sm-progress-bar mb-1">
                                                    <div class="progress-bar bg-primary" role="progressbar"
                                                        style="width: 25%" aria-valuenow="25" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                                <p>25 GB of 100 GB used</p>
                                            </div>
                                        </li>
                                    </ul>
                                    <hr>
                                    <ul>
                                        <li>
                                            <div class="btn btn-outline-primary"><i data-feather="grid"> </i>Pricing
                                                plan</div>
                                        </li>
                                        <li>
                                            <div class="pricing-plan">
                                                <h6>Trial Version </h6>
                                                <h5>FREE</h5>
                                                <p> 100 GB Space</p>
                                                <div class="btn btn-outline-primary btn-xs">Selected</div><img
                                                    class="bg-img"
                                                    src="assets/images/dashboard/folder.png" alt="">
                                            </div>
                                        </li>
                                        <li>
                                            <div class="pricing-plan">
                                                <h6>Premium</h6>
                                                <h5>$5/month</h5>
                                                <p> 200 GB Space</p>
                                                <div class="btn btn-outline-primary btn-xs">Contact Us</div><img
                                                    class="bg-img"
                                                    src="assets/images/dashboard/folder1.png" alt="">
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-9 col-md-12 box-col-12">
                        <div class="file-content">
                            <div class="card">
                                <div class="card-header">
                                    <div class="media">
                                        <form class="form-inline" action="#" method="get">
                                            <div class="form-group mb-0"> <i class="fa fa-search"></i>
                                                <input class="form-control-plaintext" type="text"
                                                    placeholder="Search...">
                                            </div>
                                        </form>
                                        <div class="media-body text-end">
                                            <form class="d-inline-flex" action="#" method="POST"
                                                enctype="multipart/form-data" name="myForm">
                                                <div class="btn btn-primary" onclick="getFile()"> <i
                                                        data-feather="plus-square"></i>Add New</div>
                                                <div style="height: 0px;width: 0px; overflow:hidden;">
                                                    <input id="upfile" type="file" onchange="sub(this)">
                                                </div>
                                            </form>
                                            <div class="btn btn-outline-primary ms-2"><i data-feather="upload">
                                                </i>Upload </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body file-manager">
                                    <h4 class="mb-3">All Files</h4>
                                    <h6>Recently opened files</h6>
                                    <ul class="files">
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-image-o txt-primary"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Logo.psd </h6>
                                                <p class="mb-1">2.0 MB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-archive-o txt-secondary"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Project.zip </h6>
                                                <p class="mb-1">1.90 GB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-excel-o txt-success"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Backend.xls</h6>
                                                <p class="mb-1">2.00 GB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-text-o txt-info"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>requirements.txt </h6>
                                                <p class="mb-1">0.90 KB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                    </ul>
                                    <h6 class="mt-4">Folders</h6>
                                    <ul class="folder">
                                        <li class="folder-box">
                                            <div class="media"><i class="fa fa-file-archive-o f-36 txt-warning"></i>
                                                <div class="media-body ms-3">
                                                    <h6 class="mb-0">Endless admin</h6>
                                                    <p>204 files, 50mb</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="folder-box">
                                            <div class="media"><i class="fa fa-folder f-36 txt-warning"></i>
                                                <div class="media-body ms-3">
                                                    <h6 class="mb-0">Endless admin</h6>
                                                    <p>101 files, 10mb</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="folder-box">
                                            <div class="media"><i class="fa fa-file-archive-o f-36 txt-warning"></i>
                                                <div class="media-body ms-3">
                                                    <h6 class="mb-0">Endless admin</h6>
                                                    <p>25 files, 2mb</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="folder-box">
                                            <div class="media"><i class="fa fa-folder f-36 txt-warning"></i>
                                                <div class="media-body ms-3">
                                                    <h6 class="mb-0">Endless admin</h6>
                                                    <p>108 files, 5mb</p>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <h6 class="mt-4">Files</h6>
                                    <ul class="files">
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-archive-o txt-secondary"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Project.zip </h6>
                                                <p class="mb-1">1.90 GB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-excel-o txt-success"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Backend.xls</h6>
                                                <p class="mb-1">2.00 GB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-text-o txt-info"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>requirements.txt </h6>
                                                <p class="mb-1">0.90 KB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                        <li class="file-box">
                                            <div class="file-top"> <i class="fa fa-file-text-o txt-primary"></i><i
                                                    class="fa fa-ellipsis-v f-14 ellips"></i></div>
                                            <div class="file-bottom">
                                                <h6>Logo.psd </h6>
                                                <p class="mb-1">2.0 MB</p>
                                                <p> <b>last open : </b>1 hour ago</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Container-fluid Ends-->
        </div>
        <?php include('partial/footer.php') ; ?>
    </div>
    <div class="icon-hover-bottom p-fixed fa-fa-icon-show-div opecity-0">
        <div class="container-fluid">
            <div class="row">
                <div class="icon-popup">
                    <div class="close-icon"><i class="icofont icofont-close"></i></div>
                    <div class="icon-first"><i id="icon_main"></i></div>
                    <div class="icon-class">
                        <label class="icon-title">data-feather</label><span id="fclass1"></span>
                    </div>
                    <div class="icon-last icon-last">
                        <label class="icon-title">Markup</label>
                        <div class="form-inline">
                            <div class="form-group">
                                <input class="inp-val form-control m-r-10" id="input_copy" type="text" value=""
                                    readonly="readonly">
                                <button class="btn btn-primary notification" onclick="myFunction()">Copy text</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('partial/scripts.php') ; ?>
<script src="assets/js/icons/feather-icon/feather-icon-clipart.js"></script>
<script src="assets/js/typeahead/handlebars.js"></script>
<script src="assets/js/typeahead/typeahead.bundle.js"></script>
<script src="assets/js/typeahead/typeahead.custom.js"></script>
<script src="assets/js/typeahead-search/handlebars.js"></script>
<script src="assets/js/typeahead-search/typeahead-custom.js"></script>
<script src="assets/js/tooltip-init.js"></script>
<?php include('partial/footer-end.php') ; ?>