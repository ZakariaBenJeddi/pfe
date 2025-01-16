<?php
session_start();

include('../includes/DatabaseConnexion.php');

if (isset($_POST['login'])) {
  $username = $_POST['username'];
  $password = $_POST['password'];
  $sql = "SELECT * FROM administrateur WHERE user_name_admin=:username ";
  $query = $dbh->prepare($sql);
  $query->bindParam(':username', $username, PDO::PARAM_STR);
  $query->execute();
  $results = $query->fetchAll(PDO::FETCH_OBJ);
  echo "<script>alert('Votre compte a contacter l'administrateur');document.location ='index.php';</script>";

  if ($query->rowCount() > 0) {
    foreach ($results as $result) {
      $motDePasseHacheBD = $result->mot_de_passe;
      $_SESSION['user'] = $result->id_admin;
      $_SESSION['nom_admin'] = $result->nom_admin;
      $_SESSION['prenom_admin'] = $result->prenom_admin;
      // $_SESSION['permission'] = $result->permission;
      $_SESSION['email_admin'] = $result->email_admin;
      $_SESSION['service'] = $result->service;
      $_SESSION['admin_image'] = $result->admin_image;
    }

    if (!empty($_POST["remember"])) {
      setcookie("user_login", $_POST["username"], time() + (10 * 365 * 24 * 60 * 60));
      setcookie("userpassword", $_POST["password"], time() + (10 * 365 * 24 * 60 * 60));
    } else {
      if (isset($_COOKIE["user_login"])) {
        setcookie("user_login", "");
        if (isset($_COOKIE["userpassword"])) {
          setcookie("userpassword", "");
        }
      }
    }
    $aa = $_SESSION['user'];
    $sql = "SELECT * from administrateur  where id_admin=:aa";
    $query = $dbh->prepare($sql);
    $query->bindParam(':aa', $aa, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    $cnt = 1;
    if ($query->rowCount() > 0) {
      foreach ($results as $row) {
        if ($row->status == "1") {
          $extra = "dashboard.php";
          $username = $_POST['username'];
          $email = $_SESSION['email_admin'];
          $name = $_SESSION['nom_admin'];
          $lastname = $_SESSION['prenom_admin'];
          $_SESSION['login'] = $_POST['username'];
          // $_SESSION['id'] = $num['id'];
          // $_SESSION['username'] = $num['name'];

          $uip = $_SERVER['REMOTE_ADDR'];
          $status = 1;
          $sql = "INSERT INTO userlog(userEmail,userip,status,username,name,lastname)values(:email,:uip,:status,:username,:name,:lastname)";
          $query = $dbh->prepare($sql);
          $query->bindParam(':username', $username, PDO::PARAM_STR);
          $query->bindParam(':name', $name, PDO::PARAM_STR);
          $query->bindParam(':lastname', $lastname, PDO::PARAM_STR);
          $query->bindParam(':email', $email, PDO::PARAM_STR);
          $query->bindParam(':uip', $uip, PDO::PARAM_STR);
          $query->bindParam(':status', $status, PDO::PARAM_STR);
          $query->execute();
          $host = $_SERVER['HTTP_HOST'];
          $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
          header("location:dashboard.php");
          exit();
        } else {
          echo "<script>alert('Votre compte a été bloqué, veuillez contacter l'administrateur');document.location ='index.php';</script>";
        }
      }
    }
  } else {
    $extra = "sign-in.php";
    $username = $_POST['username'];
    $uip = $_SERVER['REMOTE_ADDR'];
    $status = 0;
    $email = 'Not registered in system';
    $name = 'Potential Hacker';
    $sql = "INSERT INTO userlog(userEmail,userip,status,username,name)values(:email,:uip,:status,:username,:name)";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':uip', $uip, PDO::PARAM_STR);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->execute();
    $host  = $_SERVER['HTTP_HOST'];
    $uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    echo "<script>alert('Username or Password is incorrect');document.location ='http://$host$uri/$extra';</script>";
  }
}
?>

<?php



?>
<!DOCTYPE html>
<html lang="en">

<!-- HEAD -->
<?php include '../includes/head.php' ?>



<body class="">
  <main class="main-content  mt-0">
    <section>
      <div class="page-header min-vh-100">
        <div class="container">
          <div class="row">
            <div class="col-xl-4 col-lg-5 col-md-7 d-flex flex-column mx-lg-0 mx-auto">
              <div class="card card-plain">
                <div class="card-header pb-0 text-start">
                  <h4 class="font-weight-bolder">Sign In</h4>
                  <p class="mb-0">Enter your email and password to sign in</p>
                </div>
                <div class="card-body">
                  <form role="form" method="post">
                    <div class="mb-3">
                      <input type="text" name="username" class="form-control form-control-lg" placeholder="Username">
                    </div>
                    <div class="mb-3">
                      <input type="password" name="password" class="form-control form-control-lg" placeholder="Password">
                    </div>
                    <div class="form-check form-switch">
                      <input class="form-check-input" type="checkbox" id="remember" name="remember">
                      <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <div class="text-center">
                      <input type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0" value="Login" name="login">
                      <!-- <button type="submit" class="btn btn-lg btn-primary btn-lg w-100 mt-4 mb-0">Login</button> -->
                    </div>
                  </form>
                </div>
                <div class="card-footer text-center pt-0 px-lg-2 px-1">
                  <p class="mb-4 text-sm mx-auto">
                    Don't have an account?
                    <a href="javascript:;" class="text-primary text-gradient font-weight-bold">Sign up</a>
                  </p>
                </div>
              </div>
            </div>
            <div class="col-6 d-lg-flex d-none h-100 my-auto pe-0 position-absolute top-0 end-0 text-center justify-content-center flex-column">
              <div class="position-relative bg-gradient-primary h-100 m-3 px-7 border-radius-lg d-flex flex-column justify-content-center overflow-hidden" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signin-ill.jpg');
          background-size: cover;">
                <span class="mask bg-gradient-primary opacity-6"></span>
                <h4 class="mt-5 text-white font-weight-bolder position-relative">"Attention is the new currency"</h4>
                <p class="text-white position-relative">The more effortless the writing looks, the more effort the writer actually put into the process.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </main>
  <!--   Core JS Files   -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="../assets/js/argon-dashboard.min.js?v=2.0.4"></script>

</body>

</html>