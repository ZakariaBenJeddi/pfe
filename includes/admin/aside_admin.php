<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
	<div class="sidenav-header">
		<i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
		<a class="navbar-brand m-0" href=" https://demos.creative-tim.com/argon-dashboard/dashboard.html " target="_blank">
			<img src="https://elaraki.ac.ma/images/logo2.png" class="navbar-brand-img h-100" alt="main_logo">
			<span class="ms-1 font-weight-bold">
				<?= strtoupper($_SESSION['nom_admin'] . " " . $_SESSION['prenom_admin'])  ?>
			</span>

		</a>
	</div>
	<hr class="horizontal dark mt-0">
	<div class="collapse navbar-collapse  w-auto" id="sidenav-collapse-main">
		<ul class="navbar-nav">
			<!-- Section Dashboard -->
			<li class="nav-item">
				<a class="nav-link active" href="../admin/dashboard.php">
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
				<a class="nav-link" href="../admin/eleves.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-hat-3 text-success text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Élèves</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/enseignant.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-single-02 text-primary text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Enseignants</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../administration.php">
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
				<a class="nav-link" href="../admin/niveau.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="fa-solid fa-layer-group text-warning text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Niveau</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/classes.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-building text-warning text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Classes</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/filiere.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-books text-info text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Filière</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/matiere.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-book-bookmark text-danger text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Matières</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/calendrier.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="fa-solid fa-calendar text-warning" style="opacity: 0.6; font-size: 1rem;"></i>
					</div>
					<span class="nav-link-text ms-1">Calendrier</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/timetableview.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Emplois du Temps</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link " href="../admin/TimeTableInfo.php">
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
				<a class="nav-link" href="../admin/absences.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-user-run text-danger text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Absences</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/evaluations.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-chart-bar-32 text-success text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Évaluations</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/bulletins.php">
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
				<a class="nav-link" href="../admin/salle.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-building text-info text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Salles</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/equipements.php">
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
				<a class="nav-link" href="../admin/payements.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-credit-card text-success text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Paiements</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/tarif_scolarite.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-tag text-success text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Tarifs de scolarité</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/frais_scolarite.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-money-coins text-warning text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Frais de scolarité</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/periodes_paiement.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-calendar-grid-58 text-warning text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">periodes paiement</span>
				</a>
			</li>

			<!-- Section Compte -->
			<li class="nav-item mt-3">
				<h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Mon Compte</h6>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/profile.php">
					<div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
						<i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
					</div>
					<span class="nav-link-text ms-1">Profil</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link" href="../admin/logout.php">
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
			<img class="w-50 mx-auto mt-5" src="https://elaraki.ac.ma/images/logo2.png" alt="sidebar_illustration">
			<div class="card-body text-center p-3 w-100 pt-0">
				<div class="docs-info">
					<h6 class="mb-0">ELARAKI School</h6>
					<p class="text-xs font-weight-bold mb-0">International School of Morocco</p>
				</div>
			</div>
		</div>
	</div>
</aside>