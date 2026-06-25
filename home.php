<?php
require_once("dbconfig.php"); 


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Scholarship Management System</title>

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&family=Poppins:wght@300;400;600;700;800;900&family=Raleway:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page">

  <!-- ======= Header ======= -->
  <header id="header" class="header fixed-top">
    <div class="branding d-flex align-items-center">
      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="index.html" class="logo d-flex align-items-center text-decoration-none">
          <h4 class="sitename mb-0">Scholarship Management Sytem</h4>
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="#hero" class="active">Home</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="#schemes">Scholarship</a></li>
            <li><a href="adminlogin.php">Admin</a></li>
            <li><a href="login.php">Apply</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </div>
  </header>
  <!-- End Header -->

  <main class="main">

    <!-- ======= Hero Section ======= -->
    <section id="hero" class="hero section dark-background">
      <img src="assets/img/ua-gate-1.jpg" alt="Scholarship Hero" data-aos="fade-in" class="img-fluid">

      <div class="container text-center" data-aos="fade-up">
        <h3>Welcome to the Scholarship Management System for SAS</h3>
      </div>
    </section>
    <!-- End Hero Section -->

    <!-- ======= About Section ======= -->
    <section id="about" class="about section">
      <div class="container section-title" data-aos="fade-up">
        <span class="subtitle">About Us</span>
        <h2>About the Scholarship Program</h2>
        <p>
          The SAS Scholarship Management System is designed to streamline
          scholarship applications, evaluation, and monitoring. It aims to provide transparency,
          accessibility, and efficiency in supporting deserving students to achieve their academic goals.
        </p>
      </div>
    </section>
    <!-- End About Section -->

    <!-- ======= Schemes Section ======= -->
    <section id="schemes" class="schemes section bg-light">
      <div class="container" data="fade-up">
        <div class="section-title">
          <span class="subtitle">Scholarship Schemes</span>
          <h2>Available Scholarship Programs</h2>
          <p>Explore various government and institutional scholarship programs available to students.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-4" data="zoom-in" data-aos-delay="100">
            <div class="card h-100 text-center p-3 shadow-sm">
              <h5>DOST</h5>
              <p>also offers scholarships to students who want to study science and engineering, encouraging the youth to become future scientists and innovators for national development..</p>
            </div>
          </div>

          <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="card h-100 text-center p-3 shadow-sm">
              <h5>TDP</h5>
              <p>Supports students from low-income households to pursue their studies.</p>
            </div>
          </div>

          <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="card h-100 text-center p-3 shadow-sm">
              <h5>TES  Scholarship</h5>
              <p>The Tertiary Education Subsidy (TES) is a Philippine government grant-in-aid program under
                 UniFAST (RA 10931) for Filipino students in public or private  college students.
                  </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- End Schemes Section -->

  </main>
  <!-- End Main -->

  <!-- ======= Footer ======= -->
  <footer class="footer py-4 text-center bg-dark text-light">
    <div class="container">
      <p class="mb-0">&copy; 2025 University of Antique.</p>
    </div>
  </footer>
  <!-- End Footer -->

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>
</html>
