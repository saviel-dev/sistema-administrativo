<?php include('partial/header.php'); ?>

<?php include('partial/loader.php'); ?>

<!-- page-wrapper Start-->
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
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Horizontal Borders</h5><span>Example of <code>horizontal</code> table borders. This
                                    is a default table border style attached to <code> .table</code> class.All borders
                                    have the same grey color and style, table itself doesn't have a border, but you can
                                    add this border using<code> .table-border-horizontal</code>class added to the table
                                    with <code>.table</code>class.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-border-horizontal">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Country</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Alexander</td>
                                            <td>Orton</td>
                                            <td>@mdorton</td>
                                            <td>Admin</td>
                                            <td>USA</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>John Deo</td>
                                            <td>Deo</td>
                                            <td>@johndeo</td>
                                            <td>User</td>
                                            <td>USA</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Randy Orton</td>
                                            <td>the Bird</td>
                                            <td>@twitter</td>
                                            <td>admin</td>
                                            <td>UK</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">4</th>
                                            <td>Randy Mark</td>
                                            <td>Ottandy</td>
                                            <td>@mdothe</td>
                                            <td>user</td>
                                            <td>AUS</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">5</th>
                                            <td>Ram Jacob</td>
                                            <td>Thornton</td>
                                            <td>@twitter</td>
                                            <td>admin</td>
                                            <td>IND</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Vertical Borders</h5><span>Example of <code>Vertical </code> table borders. This is
                                    a default table border style attached to <code> .table</code> class.All borders have
                                    the same grey color and style, table itself doesn't have a border, but you can add
                                    this border using<code> .table-border-vertical</code>class added to the table with
                                    <code>.table</code>class.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-border-vertical">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                            <th scope="col">Role</th>
                                            <th scope="col">Country</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Alexander</td>
                                            <td>Orton</td>
                                            <td>@mdorton</td>
                                            <td>Admin</td>
                                            <td>USA</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>John Deo</td>
                                            <td>Deo</td>
                                            <td>@johndeo</td>
                                            <td>User</td>
                                            <td>USA</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Randy Orton</td>
                                            <td>the Bird</td>
                                            <td>@twitter</td>
                                            <td>admin</td>
                                            <td>UK</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">4</th>
                                            <td>Randy Mark</td>
                                            <td>Ottandy</td>
                                            <td>@mdothe</td>
                                            <td>user</td>
                                            <td>AUS</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">5</th>
                                            <td>Ram Jacob</td>
                                            <td>Thornton</td>
                                            <td>@twitter</td>
                                            <td>admin</td>
                                            <td>IND</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Both Borders</h5><span>Example of <code>horizontal</code> table borders. This is a
                                    default table border style attached to <code> .table</code> class.All borders have
                                    the same grey color and style, table itself doesn't have a border, but you can add
                                    this border using<code> .table-bordered</code>class added to the table with
                                    <code>.table</code>class.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Borderless Table</h5><span>Example of <code>horizontal</code> table borders. This is
                                    a default table border style attached to <code> .table</code> class.All borders have
                                    the same grey color and style, table itself doesn't have a border, but you can add
                                    this border using<code> .table-bordernone</code>class added to the table with
                                    <code>.table</code>class.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordernone">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th class="border-bottom-0" scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th class="border-bottom-0" scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th class="border-bottom-0" scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Default Table Border</h5><span>Example of <code>Default Table Border</code>.This is
                                    a default table border style attached to <code>.table</code> class.All borders have
                                    the same grey color and style, table itself doesn't have a border, but you can add
                                    this border using<code> .table</code>class added to the table with
                                    <code>.table</code>class.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Solid Border</h5><span>Example of a <code>solid</code> border inside table
                                    <code>thead</code>. This border inherits all styling options from the default border
                                    style, the only difference is it has <code>2px</code> width. Sometimes it could be
                                    useful for visual separation and addition highlight. To use this border add
                                    <code>.border-solid</code> to the table head row. This border style works only with
                                    row borders.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr class="border-solid">
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Double Border</h5><span>Example of a <code>double</code> border inside table head.
                                    This border has <code>4px</code> width, Just add<code>table-double</code> with table
                                    for this default table.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-double">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Dotted Border</h5><span>Example of a <code>dotted</code> border inside table head.
                                    This border has <code>2px</code> width, Just add<code>table-dotted</code> with table
                                    for this default table.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dotted">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Dashed Border</h5><span>Example of a <code>dashed</code> border inside table head.
                                    This border has <code>2px</code> width, Just add<code>table-dashed</code> with table
                                    for this default table.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-dashed">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@mdo</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Mark</td>
                                            <td>Otto</td>
                                            <td>@TwBootstrap</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header">
                                <h5>Border Bottom Color </h5><span>EExample of a table head border
                                    with<code>custom </code> color. According to the custom color system options, you
                                    can set any of predefined colors by adding <code>.border-bottom-*</code> class to
                                    the table head row. This technique works with all border styles demonstrated
                                    above.</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr class="border-bottom-primary">
                                            <th scope="col">#</th>
                                            <th scope="col">First Name</th>
                                            <th scope="col">Last Name</th>
                                            <th scope="col">Username</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="border-bottom-secondary">
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                        <tr class="border-bottom-success">
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                        <tr class="border-bottom-info">
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                        <tr class="border-bottom-warning">
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                        <tr class="border-bottom-danger">
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Jacob</td>
                                            <td>Thornton</td>
                                            <td>@fat</td>
                                        </tr>
                                    </tbody>
                                </table>
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

<script src="assets/js/tooltip-init.js"></script>

<?php include('partial/footer-end.php'); ?>