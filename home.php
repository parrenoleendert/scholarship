<?php
require_once("dbconfig.php"); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Scholarship Management System</title>

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', 'Open Sans', sans-serif;
      background-color: #f8fafc;
      color: #334155;
    }
    
    /* Header Styling */
    .header {
      background: rgba(255, 255, 255, 0.95) !important;
      backdrop-filter: blur(10px);
      border-bottom: 1px solid #e2e8f0;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
      padding: 20px 0 !important; 
      transition: all 0.3s ease;
    }

    /* Larger Brand/Logo Title */
    .sitename {
      font-weight: 800;
      color: #1e293b !important;
      letter-spacing: -0.5px;
      font-size: 1.5rem !important; 
    }

    /* Larger Navigation Links */
    .navmenu a {
      color: #64748b !important;
      font-weight: 600;            
      font-size: 1.05rem;        
      transition: color 0.2s ease;
    }

    .navmenu a:hover, .navmenu .active {
      color: #0d6efd !important;
    }

    /* Scaled up CTA Button inside Nav */
    .navmenu .btn-primary {
      padding: 10px 24px !important; /* Larger button padding */
      font-size: 1rem;
      font-weight: 600;
    }

    .navmenu .btn-primary, 
    .navmenu a.btn-primary, 
    .navmenu a.btn-primary:hover,
    .navmenu a.btn-primary:focus {
      color: #ffffff !important;
    }

    /* Hero Sectione */
    .hero-modern {
      padding: 170px 0 100px 0;
      background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
      position: relative;
      overflow: hidden;
    }
    .hero-modern::before {
      content: '';
      position: absolute;
      top: -20%;
      right: -10%;
      width: 600px;
      height: 600px;
      background: radial-gradient(circle, rgba(13,110,253,0.08) 0%, rgba(255,255,255,0) 70%);
      border-radius: 50%;
    }
    .hero-title {
      font-weight: 800;
      color: #1e293b;
      font-size: 2.75rem;
      letter-spacing: -1px;
      line-height: 1.2;
    }
    .hero-subtitle {
      color: #475569;
      font-size: 1.15rem;
      max-width: 600px;
      margin: 20px auto 30px auto;
    }
    .btn-hero-primary {
      padding: 12px 30px;
      font-weight: 600;
      border-radius: 8px;
      box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-hero-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 15px -3px rgba(13, 110, 253, 0.3);
    }

    /* Sections */
    .section-padding {
      padding: 80px 0;
    }
    .section-tag {
      display: inline-block;
      background-color: #e0f2fe;
      color: #0369a1;
      padding: 6px 16px;
      border-radius: 50px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .section-heading {
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 15px;
    }

    .custom-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 30px;
      height: 100%;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .custom-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
      border-color: #cbd5e1;
    }
    .card-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 50px;
      font-size: 0.75rem;
      font-weight: 600;
      background-color: #dcfce7;
      color: #15803d;
      margin-bottom: 15px;
    }
    .custom-card h5 {
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 12px;
    }
    .custom-card p {
      color: #64748b;
      font-size: 0.95rem;
      line-height: 1.6;
    }

    /* Footer styling */
    .footer-modern {
      background: #ffffff;
      border-top: 1px solid #e2e8f0;
      color: #64748b;
      padding: 30px 0;
      font-size: 0.9rem;
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header fixed-top d-flex align-items-center">
    <div class="container-fluid px-4 position-relative d-flex align-items-center justify-content-between">
      <a href="index.php" class="logo d-flex align-items-center text-decoration-none">
        <h4 class="sitename mb-0">Scholarship Portal</h4>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul class="d-flex align-items-center list-unstyled mb-0 gap-4">
          <li><a href="#hero" class="active text-decoration-none">Home</a></li>
          <li><a href="#about" class="text-decoration-none">About</a></li>
          <li><a href="#schemes" class="text-decoration-none">Programs</a></li>
          <li><a href="adminlogin.php" class="text-decoration-none">Admin</a></li>
          <li><a href="login.php" class="btn btn-primary btn-sm px-3 text-white fw-600 text-decoration-none rounded-2">Apply Now</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="main">

    <section id="hero" class="hero-modern d-flex align-items-center">
      <div class="container text-center" data-aos="fade-up">
        <span class="section-tag">University of Antique</span>
        <h1 class="hero-title">Empowering Your Academic<br>Journey with SAS</h1>
        <p class="hero-subtitle">
          Welcome to the Scholarship Management System. Discover, apply for, and track your institutional or government financial grants effortlessly in one clean space.
        </p>
        <div class="d-flex justify-content-center gap-3">
          <a href="login.php" class="btn btn-primary btn-hero-primary">Get Started</a>
          <a href="#schemes" class="btn btn-outline-secondary px-4 rounded-3 d-flex align-items-center font-weight-600">View Programs</a>
        </div>
      </div>
    </section>

    <section id="about" class="section-padding">
      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center text-center mb-5">
          <div class="col-lg-8">
            <span class="section-tag">About the System</span>
            <h2 class="section-heading">Streamlining Academic Opportunities</h2>
            <p class="text-muted lead fs-6">
              The SAS Scholarship Management System is designed to streamline application collection, multi-tier evaluations, and transparency. Moving beyond manual processing, we connect deserving talents directly with critical support frameworks.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section id="schemes" class="section-padding bg-white" style="border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
      <div class="container" data-aos="fade-up">
        <div class="text-center mb-5">
          <span class="section-tag">Scholarship Schemes</span>
          <h2 class="section-heading">Available Programs</h2>
          <p class="text-muted">Explore verified government and institutional pathways currently accepting applications.</p>
        </div>

        <div class="row g-4">
          <div class="col-md-4" data-aos="zoom-in" data-aos-delay="100">
            <div class="custom-card">
              <span class="card-badge" style="background-color: #eff6ff; color: #1d4ed8;">Science & Tech</span>
              <h5>DOST Scholarship</h5>
              <p>Supports promising applicants focused on engineering, mathematics, and complex sciences. Directing youth to evolve into future pioneers for national innovation.</p>
            </div>
          </div>

          <div class="col-md-4" data-aos="zoom-in" data-aos-delay="200">
            <div class="custom-card">
              <span class="card-badge" style="background-color: #fef3c7; color: #b45309;">Financial Grant</span>
              <h5>Tulong Dunong Program (TDP)</h5>
              <p>Tailored financial grant assistance targeted at lower-income bracket students aiming to smoothly unlock their target programmatic options completely overhead-free.</p>
            </div>
          </div>

          <div class="col-md-4" data-aos="zoom-in" data-aos-delay="300">
            <div class="custom-card">
              <span class="card-badge">Active Program</span>
              <h5>TES Scholarship</h5>
              <p>The Tertiary Education Subsidy (TES) constitutes a highly integrated public resource engine via UniFAST (RA 10931) helping students meet vital degree demands seamlessly.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer class="footer-modern text-center">
    <div class="container">
      <p class="mb-0">&copy; 2026 University of Antique — Scholarship Administration System. All rights reserved.</p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center btn btn-primary rounded-circle p-0" style="width: 40px; height: 40px; position: fixed; bottom: 20px; right: 20px;">
    <i class="bi bi-arrow-up-short text-white fs-4"></i>
  </a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize AOS Animations
      AOS.init({
        duration: 600,
        easing: 'ease-in-out',
        once: true
      });
    });
  </script>

</body>
</html>