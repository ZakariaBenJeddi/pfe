<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href=" https://demos.creative-tim.com/argon-dashboard/pages/dashboard.html " target="_blank">
            <img src="https://elaraki.ac.ma/images/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">
                <?= strtoupper($_SESSION['nom_admin'] . " " . $_SESSION['prenom_admin'])  ?>
            </span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <!-- <div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main"> -->
    <ul class="navbar-nav">
        <!-- Section Dashboard -->
        <li class="nav-item">
            <a class="nav-link active" href="../pages/dashboard.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Dashboard</span>
            </a>
        </li>

        <!-- Section Gestion des utilisateurs -->
        <li class="nav-item">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Utilisateurs</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/eleves.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-hat-3 text-success text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Élèves</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/enseignant.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Enseignants</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/administration.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-badge text-info text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Administration</span>
            </a>
        </li>

        <!-- Section Gestion pédagogique -->
        <li class="nav-item">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion Pédagogique</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/classes.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-building text-warning text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Classes</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/matiere.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-book-bookmark text-danger text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Matières</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/calendrier.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Emplois du Temps</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link " href="../pages/TimeTableInfo.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="fa fa-cog text-dark text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Configuration TimeTable</span>
            </a>
        </li>

        <!-- Section Suivi -->
        <li class="nav-item">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Suivi</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/absences.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-user-run text-danger text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Absences</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/evaluations.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-chart-bar-32 text-success text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Évaluations</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/bulletins.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-folder-17 text-primary text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Bulletins</span>
            </a>
        </li>

        <!-- Section Gestion des ressources -->
        <li class="nav-item">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestion des Ressources</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/salle.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-building text-info text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Salles</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/equipements.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-laptop text-primary text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Équipements</span>
            </a>
        </li>

        <!-- Section Comptabilité -->
        <li class="nav-item">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Comptabilité</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/payements.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-credit-card text-success text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Paiements</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/frais-scolarite.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Frais de scolarité</span>
            </a>
        </li>

        <!-- Section Compte -->
        <li class="nav-item mt-3">
            <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mon Compte</h6>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/profile.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Profil</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="../pages/sign-in.php">
                <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    <i class="ni ni-button-power text-danger text-sm opacity-10"></i>
                </div>
                <span class="nav-link-text ms-1">Déconnexion</span>
            </a>
        </li>
    </ul>
    </div>
    <div class="sidenav-footer mx-3 ">
        <div class="card card-plain shadow-none" id="sidenavCard">
            <!-- <img class="w-50 mx-auto" src="../assets/img/illustrations/icon-documentation.svg" alt="sidebar_illustration"> -->
            <img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
            <div class="card-body text-center p-3 w-100 pt-0">
                <div class="docs-info">
                    <h6 class="mb-0">ELARAKI School</h6>
                    <p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
                </div>
            </div>
        </div>
        <!-- <a href="https://www.creative-tim.com/learning-lab/bootstrap/license/argon-dashboard" target="_blank" class="btn btn-dark btn-sm w-100 mb-3">Documentation</a>
        <a class="btn btn-primary btn-sm mb-0 w-100" href="https://www.creative-tim.com/product/argon-dashboard-pro?ref=sidebarfree" type="button">Upgrade to pro</a> -->
    </div>
</aside>