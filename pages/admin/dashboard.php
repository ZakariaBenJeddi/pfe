<?php

use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Date;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

session_start();
if (empty($_SESSION['user'])) {
  header('location:../sign-in.php');
}
require '../../includes/DatabaseConnexion.php';

//* deconnexion
require('../../includes/deconnexion_5s.php');

include '../../includes/admin/dashboard_data.php';

?>

<!DOCTYPE html>
<html lang="en">
<!-- HEAD -->
<?php include '../../includes/admin/head_admin.php' ?>
<style>
  .dropdown-item {
    padding: 0.5rem 1rem;
  }

  .dropdown-item:hover {
    background-color: rgba(13, 110, 253, 0.1);
  }

  .dropdown-item.active {
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
  }
</style>

<body class="g-sidenav-show  bg-gray-100">
  <div class="min-height-300 bg-primary position-absolute w-100"></div>
  <?php require('../../includes/admin/aside_admin.php') ?>
  <?php //require('../../includes/aside_admin.php') 
  ?>
  <main class="main-content position-relative border-radius-lg ">
    <!-- Navbar -->
    <?php require('../../includes/admin/navbar_admin.php') ?>
    <!-- End Navbar -->
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="nombre_eleve">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Nombre eleve</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_eleves ?>
                    </h5>
                    <?php if ($pourcentage !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage, 2) . "%"; ?>
                        </span>
                        l'année précédente
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage élèves
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle">
                    <i class="ni ni-single-02 text-light text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="ensaignant">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Paiement ****</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_enseignant ?>
                    </h5>
                    <?php if ($pourcentage_enseignant !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage_enseignant < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage_enseignant, 2) . "%"; ?>
                        </span>
                        l'année précédente
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <!-- <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i> -->
                    <i class="ni ni-single-02 text-light text-lg opacity-10"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6">
          <div class="card" id="abscence">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Abscence</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_abscence; ?>
                    </h5>
                    <?php if ($pourcentage_absence !== null) { ?>
                      <p class="mb-0">
                        <span class="<?= $pourcentage_absence < 0 ? 'text-danger' : 'text-success'; ?> text-sm font-weight-bolder">
                          <?= number_format($pourcentage_absence, 2) . "%"; ?>
                        </span>
                        par rapport à hier
                      </p>
                    <?php } else { ?>
                      <p class="text-danger text-sm mb-0 mb-3">
                        <i class="fas fa-exclamation-circle text-danger text-lg"></i> Error pourcentage
                      </p>
                    <?php } ?>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                    <i class="fas fa-user-slash text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card" id="nombre_salle">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Nombre Salle</p>
                    <h5 class="font-weight-bolder">
                      <?= $nbr_salle; ?>
                    </h5>
                    <p class="mb-0">
                      <span class="text-success text-sm font-weight-bolder"><?= $nbr_salle ?></span>
                      Salles actuellement
                    </p>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                    <i class="ni ni-building text-light text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <!-- Absence chart card -->
        <div class="col-lg-7 mb-lg-0 mb-5">
          <div class="card shadow-sm hover:shadow-lg transition-shadow duration-300">
            <div class="card-body">
              <!-- Header -->
              <div class="row mb-4">
                <div class="col-sm-6">
                  <div class="d-flex align-items-center mb-2 mb-sm-0">
                    <div class="me-3">
                      <span class="badge bg-primary-subtle text-primary p-2 rounded-circle">
                        <i class="fas fa-chart-bar"></i>
                      </span>
                    </div>
                    <div>
                      <p class="card-title mb-0 text-secondary">Statistiques des Absences</p>
                      <?php
                      $jour_actuel = date('D', strtotime('today'));
                      $jour_actuel = substr($jour_actuel, 0, 3);
                      $total_absences = $data['garçon'][$jour_actuel] + $data['fille'][$jour_actuel];
                      ?>
                      <span class="fw-semibold text-dark"><?= $total_absences ?> absence(s)</span>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Chart -->
              <div id="column-chart" class="mt-2"></div>
              <!-- Footer -->
              <div class="d-flex justify-content-between align-items-center mt-4">
                <!-- <div class="dropdown">
                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <span>7 derniers jours</span>
                  </button>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-clock me-2 text-secondary"></i>
                        Aujourd'hui
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar-day me-2 text-secondary"></i>
                        Hier
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center active" href="#">
                        <i class="fas fa-calendar-week me-2 text-secondary"></i>
                        7 derniers jours
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar me-2 text-secondary"></i>
                        30 derniers jours
                      </a>
                    </li>
                    <li>
                      <a class="dropdown-item d-flex align-items-center" href="#">
                        <i class="fas fa-calendar-alt me-2 text-secondary"></i>
                        90 derniers jours
                      </a>
                    </li>
                  </ul>
                </div> -->

                <a href="absence.php" class="btn btn-primary d-flex align-items-center">
                  <span>Rapport détaillé</span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right ms-2" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
                  </svg>
                </a>
              </div>

            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card card-carousel overflow-hidden h-100 p-0">
            <div id="carouselExampleCaptions" class="carousel slide h-100" data-bs-ride="carousel">
              <div class="carousel-inner border-radius-lg h-100">
                <div class="carousel-item h-100 active" style="background-image: url('https://plus.unsplash.com/premium_photo-1687128298182-6a60a37af6d4?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8ODl8fHNjaG9vbHxlbnwwfHwwfHx8MA%3D%3D');
            background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-camera-compact text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Get started with ELARAKI School</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('https://plus.unsplash.com/premium_photo-1671070290623-d6f76bdbb3db?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8c2Nob29sfGVufDB8fDB8fHww');
              background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-bulb-61 text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Get started with ELARAKI School</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('https://plus.unsplash.com/premium_photo-1680807869780-e0876a6f3cd5?w=500&auto=format&fit=crop&q=60&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8c2Nob29sfGVufDB8fDB8fHww');
                    background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Faster way to create web pages</h5>
                  </div>
                </div>
                <div class="carousel-item h-100" style="background-image: url('../../assets/img/school/projet\ presentation.png');
                    background-size: cover;">
                  <div class="carousel-caption d-none d-md-block bottom-0 text-start start-0 ms-5">
                    <div class="icon icon-shape icon-sm bg-white text-center border-radius-md mb-3">
                      <i class="ni ni-trophy text-dark opacity-10"></i>
                    </div>
                    <h5 class="text-white mb-1">Presentaion Projet</h5>
                    <!-- <p>Don’t be afraid to be wrong because you can’t learn anything from a compliment.</p> -->
                  </div>
                </div>
              </div>
              <button class="carousel-control-prev w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
              </button>
              <button class="carousel-control-next w-5 me-3" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <!-- //! Abscence chart -->
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card z-index-2 h-100">
            <div class="card-header pb-0 pt-3 bg-transparent">
              <h6 class="text-capitalize">Sales overview</h6>
              <p class="text-sm mb-0">
                <i class="fa fa-arrow-up text-success"></i>
                <span class="font-weight-bold">4% more</span> in 2021
              </p>
            </div>
            <div class="card-body p-3">
              <div class="chart">
                <canvas id="paiement-chart-line" class="chart-canvas" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="card">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">Categories</h6>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-mobile-button text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Abscence</h6>
                      <span class="text-xs">250 in stock, <span class="font-weight-bold">346+ sold</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-tag text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Evaluation</h6>
                      <span class="text-xs">123 closed, <span class="font-weight-bold">15 open</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-box-2 text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Payement</h6>
                      <span class="text-xs">1 is active, <span class="font-weight-bold">40 closed</span></span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 border-radius-lg">
                  <div class="d-flex align-items-center">
                    <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                      <i class="ni ni-satisfied text-white opacity-10"></i>
                    </div>
                    <div class="d-flex flex-column">
                      <h6 class="mb-1 text-dark text-sm">Happy users</h6>
                      <span class="text-xs font-weight-bold">+ 430</span>
                    </div>
                  </div>
                  <div class="d-flex">
                    <button class="btn btn-link btn-icon-only btn-rounded btn-sm text-dark icon-move-right my-auto"><i class="ni ni-bold-right" aria-hidden="true"></i></button>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card ">
            <div class="card-header pb-0 p-3">
              <div class="d-flex justify-content-between">
                <h6 class="mb-2">Top élèves payé</h6>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table align-items-center ">
                <tbody>
                  <?php foreach ($topEleves as $index => $eleve) : ?>
                    <tr>
                      <td class="w-30">
                        <div class="d-flex px-2 py-1 align-items-center">
                          <div>
                            <!-- Utilisation d'icônes numériques pour le classement -->
                            <div class="avatar avatar-sm bg-gradient-primary rounded-circle shadow text-center">
                              <span class="text-white text-xs font-weight-bold"><?php echo $index + 1; ?></span>
                            </div>
                          </div>
                          <div class="ms-4">
                            <p class="text-xs font-weight-bold mb-0">Élève:</p>
                            <h6 class="text-sm mb-0"><?php echo htmlspecialchars($eleve['prenom'] . ' ' . $eleve['nom']); ?></h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="text-center">
                          <p class="text-xs font-weight-bold mb-0">Paiements:</p>
                          <h6 class="text-sm mb-0"><?php echo $eleve['nb_paiements']; ?></h6>
                        </div>
                      </td>
                      <td>
                        <div class="text-center">
                          <p class="text-xs font-weight-bold mb-0">Montant total:</p>
                          <h6 class="text-sm mb-0"><?php echo number_format($eleve['total_paye'], 2, ',', ' '); ?> €</h6>
                        </div>
                      </td>
                      <td class="align-middle text-sm">
                        <div class="col text-center">
                          <p class="text-xs font-weight-bold mb-0">% du total:</p>
                          <h6 class="text-sm mb-0">
                            <?php
                            // Calcul du pourcentage par rapport au total validé
                            $pourcentage = ($totalValide > 0) ? ($eleve['total_paye'] / $totalValide * 100) : 0;
                            echo number_format($pourcentage, 2, ',', ' ') . '%';
                            ?>
                          </h6>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <figure class="highcharts-figure">
            <div id="container-donut"></div>
          </figure>

        </div>
      </div>
      <!-- FOOTER -->
      <?php include '../../includes/footer.php' ?>
    </div>
  </main>


  <!-- //TODO Highcahrt -->
  <script>
    // Animation personnalisée pour le graphique en cercle
    (function(H) {
      H.seriesTypes.pie.prototype.animate = function(init) {
        const series = this,
          chart = series.chart,
          points = series.points,
          {
            animation
          } = series.options,
          {
            startAngleRad
          } = series;

        function fanAnimate(point, startAngleRad) {
          const graphic = point.graphic,
            args = point.shapeArgs;
          if (graphic && args) {
            graphic
              // Set inital animation values
              .attr({
                start: startAngleRad,
                end: startAngleRad,
                opacity: 1
              })
              // Animate to the final position
              .animate({
                start: args.start,
                end: args.end
              }, {
                duration: animation.duration / points.length
              }, function() {
                // On complete, start animating the next point
                if (points[point.index + 1]) {
                  fanAnimate(points[point.index + 1], args.end);
                }
                // On the last point, fade in the data labels, then
                // apply the inner size
                if (point.index === series.points.length - 1) {
                  series.dataLabelsGroup.animate({
                      opacity: 1
                    },
                    void 0,
                    function() {
                      points.forEach(point => {
                        point.opacity = 1;
                      });
                      series.update({
                        enableMouseTracking: true
                      }, false);
                      chart.update({
                        plotOptions: {
                          pie: {
                            innerSize: '40%',
                            borderRadius: 8
                          }
                        }
                      });
                    });
                }
              });
          }
        }

        if (init) {
          // Hide points on init
          points.forEach(point => {
            point.opacity = 0;
          });
        } else {
          fanAnimate(points[0], startAngleRad);
        }
      };
    }(Highcharts));

    // Préparation des données à partir du PHP
    const modesPaiement = <?php echo json_encode($dataModes); ?>;

    // Transformer les données dans le format attendu par Highcharts
    const chartData = modesPaiement.map(item => ({
      name: item.mode,
      y: parseFloat(item.total),
      count: item.nombre
    }));

    // Création du graphique
    Highcharts.chart('container-donut', {
      chart: {
        type: 'pie'
      },
      title: {
        text: 'Répartition des modes de paiement'
      },
      subtitle: {
        text: 'Par montant total'
      },
      tooltip: {
        headerFormat: '',
        pointFormat: '<span style="color:{point.color}">\u25cf</span> ' +
          '<b>{point.name}</b>: {point.y} € ({point.percentage:.1f}%)<br>' +
          'Nombre de transactions: {point.count}'
      },
      accessibility: {
        point: {
          valueSuffix: '€'
        }
      },
      plotOptions: {
        pie: {
          allowPointSelect: true,
          borderWidth: 2,
          cursor: 'pointer',
          dataLabels: {
            enabled: true,
            format: '<b>{point.name}</b><br>{point.percentage:.1f}%',
            distance: 20
          }
        }
      },
      series: [{
        // Disable mouse tracking on load, enable after custom animation
        enableMouseTracking: false,
        animation: {
          duration: 2000
        },
        colorByPoint: true,
        data: chartData
      }]
    });
  </script>

  <!-- //! DRIVER JS -->
  <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
  <script src="driver.js"></script>

  <!-- FIXED PLUGIN  -->
  <?php include '../../includes/fixedplugin.php' ?>
  <!--   Core JS Files   -->
  <script src="../../assets/js/core/popper.min.js"></script>
  <script src="../../assets/js/core/bootstrap.min.js"></script>
  <script src="../../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../../assets/js/plugins/chartjs.min.js"></script>

  <!-- //! CHART JS Sales -->
  <script>
    var ctx1 = document.getElementById("chart-line").getContext("2d");

    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');
    new Chart(ctx1, {
      type: "line",
      data: {
        labels: ["Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Mobile apps",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: [50, 40, 300, 220, 500, 250, 400, 230, 500],
          maxBarThickness: 6

        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });
  </script>

  <!-- //! LINE CHART BLEU ROSE -->
  <script>
    const options = {
      colors: ["#0d6efd", "#f62459"],
      series: [{
          name: "Garçons",
          color: "#0d6efd",
          data: <?= json_encode($chartData['garçon']) ?>
        },
        {
          name: "Filles",
          color: "#f62459",
          data: <?= json_encode($chartData['fille']) ?>
        }
      ],
      chart: {
        type: "bar",
        height: 320,
        fontFamily: "system-ui, -apple-system, sans-serif",
        toolbar: {
          show: false
        }
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: "70%",
          borderRadiusApplication: "end",
          borderRadius: 4
        }
      },
      tooltip: {
        shared: true,
        intersect: false,
        style: {
          fontFamily: "system-ui, -apple-system, sans-serif"
        },
        y: {
          formatter: function(value) {
            return value + " absence(s)";
          }
        }
      },
      states: {
        hover: {
          filter: {
            type: "darken",
            value: 1
          }
        }
      },
      stroke: {
        show: true,
        width: 0,
        colors: ["transparent"]
      },
      grid: {
        show: false,
        padding: {
          left: 2,
          right: 2,
          top: -14
        }
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'left',
        offsetY: -5,
        labels: {
          colors: '#6c757d'
        }
      },
      xaxis: {
        labels: {
          style: {
            fontFamily: "system-ui, -apple-system, sans-serif",
            colors: '#6c757d'
          }
        },
        axisBorder: {
          show: false
        },
        axisTicks: {
          show: false
        }
      },
      yaxis: {
        show: true,
        labels: {
          formatter: function(value) {
            return Math.floor(value);
          },
          style: {
            colors: '#6c757d'
          }
        }
      }
    };

    if (document.getElementById("column-chart") && typeof ApexCharts !== 'undefined') {
      const chart = new ApexCharts(document.getElementById("column-chart"), options);
      chart.render();
    }
  </script>


  <!-- paiement chart -->

  <script>
    // Graphique d'évolution des paiements par mois avec le style demandé
    var ctx1 = document.getElementById("paiement-chart-line").getContext("2d");
    var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

    gradientStroke1.addColorStop(1, 'rgba(94, 114, 228, 0.2)');
    gradientStroke1.addColorStop(0.2, 'rgba(94, 114, 228, 0.0)');
    gradientStroke1.addColorStop(0, 'rgba(94, 114, 228, 0)');

    new Chart(ctx1, {
      type: "line",
      data: {
        labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        datasets: [{
          label: "Paiements mensuels",
          tension: 0.4,
          borderWidth: 0,
          pointRadius: 0,
          borderColor: "#5e72e4",
          backgroundColor: gradientStroke1,
          borderWidth: 3,
          fill: true,
          data: [
            <?= $dataGraphMois["Jan"] ?>,
            <?= $dataGraphMois["Feb"] ?>,
            <?= $dataGraphMois["Mar"] ?>,
            <?= $dataGraphMois["Apr"] ?>,
            <?= $dataGraphMois["May"] ?>,
            <?= $dataGraphMois["Jun"] ?>,
            <?= $dataGraphMois["Jul"] ?>,
            <?= $dataGraphMois["Aug"] ?>,
            <?= $dataGraphMois["Sep"] ?>,
            <?= $dataGraphMois["Oct"] ?>,
            <?= $dataGraphMois["Nov"] ?>,
            <?= $dataGraphMois["Dec"] ?>
          ],
          maxBarThickness: 6
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: 'index',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: true,
              drawOnChartArea: true,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              padding: 10,
              color: '#fbfbfb',
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
              borderDash: [5, 5]
            },
            ticks: {
              display: true,
              color: '#ccc',
              padding: 20,
              font: {
                size: 11,
                family: "Open Sans",
                style: 'normal',
                lineHeight: 2
              },
            }
          },
        },
      },
    });

    // Graphique de répartition des modes de paiement
    const ctxModes = document.getElementById('graphModes').getContext('2d');
    new Chart(ctxModes, {
      type: 'pie',
      data: {
        labels: <?= json_encode(array_column($dataModes, 'mode')) ?>,
        datasets: [{
          data: <?= json_encode(array_column($dataModes, 'total')) ?>,
          backgroundColor: [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'right'
          }
        }
      }
    });
  </script>


  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var option = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../../assets/js/argon-dashboard.min.js?v=2.0.4"></script>
</body>

</html>