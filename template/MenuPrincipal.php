<?php 
include('partial/header.php');
include('partial/loader.php');
?>
<!-- page-wrapper Start-->
<div class="page-wrapper compact-wrapper" id="pageWrapper">
  <!-- Page Header Start-->
  <?php include('partial/topbar.php') ?>
  <!-- Page Header Ends-->
  <!-- Page Body Start-->
  <div class="page-body-wrapper">
    <!-- Page Sidebar Start-->
    <?php include('partial/sidebar.php') ?>
    <!-- Page Sidebar Ends-->
    <div class="page-body">
      <?php include('partial/breadcrumb.php') ?>
      <!-- Container-fluid starts-->
      <div class="container-fluid">
        <div class="row widget-grid">
          <div class="col-xxl-4 col-sm-6 box-col-6">
            <div class="card profile-box" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
              <div class="card-body">
                <div class="media">
                  <div class="media-body">
                    <div class="greeting-user">
                      <h4 class="f-w-600">Bienvenido a Nazca</h4>
                      <p>Aquí está lo que sucede en tu cuenta hoy</p>
                      <div class="whatsnew-btn"><a class="btn btn-outline-white">¡Novedades!</a></div>
                    </div>
                  </div>
                  <div>
                    <div class="clockbox">
                      <svg id="clock" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                        <g id="face">
                          <circle class="circle" cx="300" cy="300" r="253.9"></circle>
                          <path class="hour-marks" d="M300.5 94V61M506 300.5h32M300.5 506v33M94 300.5H60M411.3 107.8l7.9-13.8M493 190.2l13-7.4M492.1 411.4l16.5 9.5M411 492.3l8.9 15.3M189 492.3l-9.2 15.9M107.7 411L93 419.5M107.5 189.3l-17.1-9.9M188.1 108.2l-9-15.6"></path>
                          <circle class="mid-circle" cx="300" cy="300" r="16.2"></circle>
                        </g>
                        <g id="hour">
                          <path class="hour-hand" d="M300.5 298V142"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                        </g>
                        <g id="minute">
                          <path class="minute-hand" d="M300.5 298V67"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                        </g>
                        <g id="second">
                          <path class="second-hand" d="M300.5 350V55"></path>
                          <circle class="sizing-box" cx="300" cy="300" r="253.9"> </circle>
                        </g>
                      </svg>
                    </div>
                    <div class="badge f-10 p-0" id="txt"></div>
                  </div>
                </div>
                <div class="cartoon"><img class="img-fluid" src="assets/images/dashboard/cartoon.svg" alt="vector women with leptop"></div>
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xl-12">
                <div class="card widget-1" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                  <div class="card-body">
                    <div class="widget-content">
                      <div class="widget-round secondary" style="background: transparent; box-shadow: none;">
                        <div class="bg-round" style="background: transparent;">
                          <svg class="svg-fill">
                            <use href="assets/svg/icon-sprite.svg#cart"> </use>
                          </svg>
                          <svg class="half-circle svg-fill">
                            <use href="assets/svg/icon-sprite.svg#halfcircle"></use>
                          </svg>
                        </div>
                      </div>
                      <div>
                        <h4>10,000</h4><span class="f-light">Compras</span>
                      </div>
                    </div>
                    <div class="font-secondary f-w-500"><i class="icon-arrow-up icon-rotate me-1"></i><span>+50%</span></div>
                  </div>
                </div>
                <div class="col-xl-12">
                  <div class="card widget-1" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                    <div class="card-body">
                      <div class="widget-content">
                        <div class="widget-round primary" style="background: transparent; box-shadow: none;">
                        <div class="bg-round" style="background: transparent;">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#tag"> </use>
                            </svg>
                            <svg class="half-circle svg-fill">
                              <use href="assets/svg/icon-sprite.svg#halfcircle"></use>
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4>4,200</h4><span class="f-light">Ventas</span>
                        </div>
                      </div>
                      <div class="font-primary f-w-500"><i class="icon-arrow-up icon-rotate me-1"></i><span>+70%</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-3 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xl-12">
                <div class="card widget-1" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                  <div class="card-body">
                    <div class="widget-content">
                      <div class="widget-round warning" style="background: transparent; box-shadow: none;">
                        <div class="bg-round" style="background: transparent;">
                          <svg class="svg-fill">
                            <use href="assets/svg/icon-sprite.svg#return-box"> </use>
                          </svg>
                          <svg class="half-circle svg-fill">
                            <use href="assets/svg/icon-sprite.svg#halfcircle"></use>
                          </svg>
                        </div>
                      </div>
                      <div>
                        <h4>7,000</h4><span class="f-light">Devoluciones</span>
                      </div>
                    </div>
                    <div class="font-warning f-w-500"><i class="icon-arrow-down icon-rotate me-1"></i><span>-20%</span></div>
                  </div>
                </div>
                <div class="col-xl-12">
                  <div class="card widget-1" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                    <div class="card-body">
                      <div class="widget-content">
                        <div class="widget-round success" style="background: transparent; box-shadow: none;">
                        <div class="bg-round" style="background: transparent;">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#rate"> </use>
                            </svg>
                            <svg class="half-circle svg-fill">
                              <use href="assets/svg/icon-sprite.svg#halfcircle"></use>
                            </svg>
                          </div>
                        </div>
                        <div>
                          <h4>2,000</h4><span class="f-light">Ventas</span>
                        </div>
                      </div>
                      <div class="font-success f-w-500"><i class="icon-arrow-up icon-rotate me-1"></i><span>+70%</span></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-auto col-xl-12 col-sm-6 box-col-6">
            <div class="row">
              <div class="col-xxl-12 col-xl-6 box-col-12">
                <div class="card widget-1 widget-with-chart" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                  <div class="card-body">
                    <div>
                      <h4 class="mb-1">1,80k</h4><span class="f-light">Pedidos</span>
                    </div>
                    <div class="order-chart">
                      <div id="orderchart"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-xxl-12 col-xl-6 box-col-12">
                <div class="card widget-1 widget-with-chart" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left: 4px solid #3b82f6; border-radius: 12px; box-shadow: inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03); transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); position: relative; overflow: hidden; margin-bottom: 15px;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 8px 20px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='inset 0 1px 3px rgba(255,255,255,0.8), 0 2px 8px rgba(0,0,0,0.03)';">
                  <div class="card-body">
                    <div>
                      <h4 class="mb-1">6,90k</h4><span class="f-light">Ganancias</span>
                    </div>
                    <div class="profit-chart">
                      <div id="profitchart"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-8 col-lg-12 box-col-12">
            <div class="card" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
              <div class="card-header card-no-border">
                <h5>Balance General</h5>
              </div>
              <div class="card-body pt-0">
                <div class="row m-0 overall-card">
                  <div class="col-xl-9 col-md-12 col-sm-7 p-0">
                    <div class="chart-right">
                      <div class="row">
                        <div class="col-xl-12">
                          <div class="card-body p-0">
                            <ul class="balance-data">
                              <li><span class="circle bg-warning"> </span><span class="f-light ms-1">Ingresos</span></li>
                              <li><span class="circle bg-primary"> </span><span class="f-light ms-1">Gastos</span></li>
                            </ul>
                            <div class="current-sale-container">
                              <div id="chart-currently"></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-xl-3 col-md-12 col-sm-5 p-0">
                    <div class="row g-sm-4 g-2">
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#income"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Ingresos</span>
                            <h6 class="mt-1 mb-0">$22,678</h6>
                          </div>
                          <div class="ms-auto text-end">
                            <div class="dropdown icon-dropdown">
                              <button class="btn dropdown-toggle" id="incomedropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="incomedropdown"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer </a></div>
                            </div><span class="font-success">+$456</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#expense"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Gastos</span>
                            <h6 class="mt-1 mb-0">$12,057</h6>
                          </div>
                          <div class="ms-auto text-end">
                            <div class="dropdown icon-dropdown">
                              <button class="btn dropdown-toggle" id="expensedropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="expensedropdown"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer </a></div>
                            </div><span class="font-danger">+$256</span>
                          </div>
                        </div>
                      </div>
                      <div class="col-xl-12 col-md-4">
                        <div class="light-card balance-card widget-hover">
                          <div class="svg-box">
                            <svg class="svg-fill">
                              <use href="assets/svg/icon-sprite.svg#doller-return"></use>
                            </svg>
                          </div>
                          <div> <span class="f-light">Reembolso</span>
                            <h6 class="mt-1 mb-0">8,475</h6>
                          </div>
                          <div class="ms-auto text-end">
                            <div class="dropdown icon-dropdown">
                              <button class="btn dropdown-toggle" id="cashbackdropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                              <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cashbackdropdown"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer </a></div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-4 col-xl-7 col-md-6 col-sm-5 box-col-6">
            <div class="card height-equal" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
              <div class="card-header card-no-border">
                <div class="header-top">
                  <h5>Órdenes Recientes</h5>
                  <div class="card-header-right-icon">
                    <div class="dropdown icon-dropdown">
                      <button class="btn dropdown-toggle" id="recentdropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentdropdown"><a class="dropdown-item" href="#">Semanal</a><a class="dropdown-item" href="#">Mensual</a><a class="dropdown-item" href="#">Anual</a></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body pt-0">
                <div class="row recent-wrapper">
                  <div class="col-xl-6">
                    <div class="recent-chart">
                      <div id="recentchart"></div>
                    </div>
                  </div>
                  <div class="col-xl-6">
                    <ul class="order-content">
                      <li> <span class="recent-circle bg-primary"> </span>
                        <div> <span class="f-light f-w-500">Cancelados </span>
                          <h4 class="mt-1 mb-0">2,302<span class="f-light f-14 f-w-400 ms-1">(Últimos 6 meses) </span></h4>
                        </div>
                      </li>
                      <li> <span class="recent-circle bg-info"></span>
                        <div> <span class="f-light f-w-500">Entregados</span>
                          <h4 class="mt-1 mb-0">9,302<span class="f-light f-14 f-w-400 ms-1">(Últimos 6 meses) </span></h4>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-4 col-xl-5 col-md-6 col-sm-7 notification box-col-6">
            <div class="card height-equal" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
              <div class="card-header card-no-border">
                <div class="header-top">
                  <h5 class="m-0">Actividad</h5>
                  <div class="card-header-right-icon">
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" id="dropdownMenuButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">Hoy</button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer </a></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body pt-0">
                <ul>
                  <li class="d-flex">
                    <div class="activity-dot-primary"></div>
                    <div class="w-100 ms-3">
                      <p class="d-flex justify-content-between mb-2"><span class="date-content light-background">8 de Marzo, 2022 </span><span>Hace 1 día</span></p>
                      <h6>Producto Actualizado<span class="dot-notification"></span></h6>
                      <p class="f-light">Se ha actualizado la información del producto...</p>
                    </div>
                  </li>
                  <li class="d-flex">
                    <div class="activity-dot-warning"></div>
                    <div class="w-100 ms-3">
                      <p class="d-flex justify-content-between mb-2"><span class="date-content light-background">15th Oct, 2022 </span><span>Hoy</span></p>
                      <h6>Mensaje sobre tu producto<span class="dot-notification"></span></h6>
                      <p>Se ha realizado una actualización en el sistema... </p>
                    </div>
                  </li>
                  <li class="d-flex">
                    <div class="activity-dot-secondary"></div>
                    <div class="w-100 ms-3">
                      <p class="d-flex justify-content-between mb-2"><span class="date-content light-background">20th Sep, 2022 </span><span>12:00 PM</span></p>
                      <h6>Mensaje sobre tu producto<span class="dot-notification"></span></h6>
                      <p>Se ha realizado una actualización en el sistema... </p>
                    </div>
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="col-xxl-4 col-md-6 appointment-sec box-col-6">
            <div class="appointment">
              <div class="card" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
                <div class="card-header card-no-border">
                  <div class="header-top">
                    <h5 class="m-0">Ventas Recientes</h5>
                    <div class="card-header-right-icon">
                      <div class="dropdown">
                        <button class="btn dropdown-toggle" id="recentButton" type="button" data-bs-toggle="dropdown" aria-expanded="false">Hoy</button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="recentButton"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer</a></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="card-body pt-0">
                  <div class="appointment-table table-responsive">
                    <table class="table table-bordernone">
                      <tbody>
                        <tr>
                          <td><img class="img-fluid img-40 rounded-circle" src="assets/images/dashboard/user/1.jpg" alt="user"></td>
                          <td class="img-content-box"><a class="d-block f-w-500" href="user-profile.html">Jane Cooper</a><span class="f-light">Hace 10 minutos</span></td>
                          <td class="text-end">
                            <p class="m-0 font-success">$200.00</p>
                          </td>
                        </tr>
                        <tr>
                          <td><img class="img-fluid img-40 rounded-circle" src="assets/images/dashboard/user/2.jpg" alt="user"></td>
                          <td class="img-content-box"><a class="d-block f-w-500" href="user-profile.html">Brooklyn Simmons</a><span class="f-light">Hace 19 minutos</span></td>
                          <td class="text-end">
                            <p class="m-0 font-success">$970.00</p>
                          </td>
                        </tr>
                        <tr>
                          <td><img class="img-fluid img-40 rounded-circle" src="assets/images/dashboard/user/3.jpg" alt="user"></td>
                          <td class="img-content-box"><a class="d-block f-w-500" href="user-profile.html">Leslie Alexander</a><span class="f-light">Hace 2 horas</span></td>
                          <td class="text-end">
                            <p class="m-0 font-success">$300.00</p>
                          </td>
                        </tr>
                        <tr>
                          <td><img class="img-fluid img-40 rounded-circle" src="assets/images/dashboard/user/4.jpg" alt="user"></td>
                          <td class="img-content-box"><a class="d-block f-w-500" href="user-profile.html">Travis Wright</a><span class="f-light">Hace 8 horas</span></td>
                          <td class="text-end">
                            <p class="m-0 font-success">$450.00</p>
                          </td>
                        </tr>
                        <tr>
                          <td><img class="img-fluid img-40 rounded-circle" src="assets/images/dashboard/user/5.jpg" alt="user"></td>
                          <td class="img-content-box"><a class="d-block f-w-500" href="user-profile.html">Mark Green</a><span class="f-light">Hace 1 día</span></td>
                          <td class="text-end">
                            <p class="m-0 font-success">$768.00</p>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-xxl-4 col-md-6 box-col-6">
            <div class="card" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
              <div class="card-header card-no-border">
                <div class="header-top">
                  <h5 class="m-0">Cronologia</h5>
                  <div class="card-header-right-icon">
                    <div class="dropdown">
                      <button class="btn dropdown-toggle" id="dropdownschedules" type="button" data-bs-toggle="dropdown" aria-expanded="false">Hoy</button>
                      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownschedules"><a class="dropdown-item" href="#">Hoy</a><a class="dropdown-item" href="#">Mañana</a><a class="dropdown-item" href="#">Ayer</a></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-body pt-0">
                <div class="schedule-container">
                  <div id="schedulechart"></div>
            <div class="row">
              <div class="col-xl-12">
                <div class="card" style="background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.03); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.05)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';">
                  <div class="card-header card-no-border">
                    <div class="header-top">
                      <h5>Usuarios Totales</h5>
                      <div class="dropdown icon-dropdown">
                        <button class="btn dropdown-toggle" id="userdropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userdropdown"><a class="dropdown-item" href="#">Semanal</a><a class="dropdown-item" href="#">Mensual</a><a class="dropdown-item" href="#">Anual</a></div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body pt-0">
                    <ul class="user-list">
                      <li>
                        <div class="user-icon primary">
                          <div class="user-box"><i class="font-primary" data-feather="user-plus"></i></div>
                        </div>
                        <div>
                          <h5 class="mb-1">178,098</h5><span class="font-primary d-flex align-items-center"><i class="icon-arrow-up icon-rotate me-1"> </i><span class="f-w-500">+30.89</span></span>
                        </div>
                      </li>
                      <li>
                        <div class="user-icon success">
                          <div class="user-box"><i class="font-success" data-feather="user-minus"></i></div>
                        </div>
                        <div>
                          <h5 class="mb-1">178,098</h5><span class="font-danger d-flex align-items-center"><i class="icon-arrow-down icon-rotate me-1"></i><span class="f-w-500">-08.89</span></span>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
              <div class="col-xl-12">
                <div class="card growth-wrap">
                  <div class="card-header card-no-border">
                    <div class="header-top">
                      <h5>Seguidores totales</h5>
                      <div class="dropdown icon-dropdown">
                        <button class="btn dropdown-toggle" id="growthdropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="icon-more-alt"></i></button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="growthdropdown"><a class="dropdown-item" href="#">Semanal</a><a class="dropdown-item" href="#">Mensual</a><a class="dropdown-item" href="#">Anual</a></div>
                      </div>
                    </div>
                  </div>
                  <div class="card-body pt-0">
                    <div class="growth-wrapper">
                      <div id="growthchart"></div>
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
    <!-- footer start-->
    <?php include('partial/footer.php') ?>
  </div>
</div>
<?php include('partial/scripts.php') ?>
<!-- Plugins JS start-->
<script src="assets/js/clock.js"></script>
<script src="assets/js/chart/apex-chart/moment.min.js"></script>
<script src="assets/js/notify/bootstrap-notify.min.js"></script>
<script src="assets/js/dashboard/default.js"></script>
<script src="assets/js/notify/index.js"></script>
<script src="assets/js/typeahead/handlebars.js"></script>
<script src="assets/js/typeahead/typeahead.bundle.js"></script>
<script src="assets/js/typeahead/typeahead.custom.js"></script>
<script src="assets/js/typeahead-search/handlebars.js"></script>
<script src="assets/js/typeahead-search/typeahead-custom.js"></script>
<script src="assets/js/height-equal.js"></script>
<script src="assets/js/animation/wow/wow.min.js"></script>
<!-- Plugins JS Ends-->
<script>
  new WOW().init();
</script>
<?php include('partial/footer-end.php') ?>