<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Details</title>

<style>
*{
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", Tahoma, sans-serif;
    }

    body {
      background-color: #f4f6f9;
      color: #333;
    }

   .header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 70px;
  background: linear-gradient(135deg, #0d6efd, #084298);
  color: #fff;
  padding: 0 30px;
  display: flex;
  align-items: center;
  font-size: 20px;
  font-weight: 600;
  z-index: 1000;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* ===== SIDEBAR ===== */
.sidebar {
  position: fixed;
  top: 70px; /* below header */
  left: 0;
  width: 240px;
  height: calc(100vh - 70px);
  background-color: #10375e;
  padding-top: 20px;
  overflow-y: auto;
}

/* Sidebar links */
.sidebar ul li a {
  display: block;
  padding: 14px 22px;
  color: #ffffff;
  text-decoration: none;
  font-size: 15px;
  transition: all 0.3s ease;
}

.sidebar ul li a:hover,
.sidebar ul li a.active {
  background-color: #0d6efd;
  padding-left: 30px;
}

  .container {
  position: relative;
  margin-left: 240px; /* account for sidebar */
  margin-top: 70px;  /* account for header */
  padding: 40px 20px;
  height: calc(100vh - 70px);
  display: flex;
  justify-content: center; /* center horizontally */
  align-items: center;     /* center vertically */
  overflow-y: auto;        /* scroll if content is too long */
  background-color: #f2f4f7;
}

/* ===== CARD ===== */
.card {
  background: #fff;
  border-radius: 16px;
  padding: 35px 40px;
  max-width: 900px;
  width: 100%;
  box-shadow: 0 8px 25px rgba(0,0,0,.08);
  animation: fadeIn .4s ease;
}

/* ===== TITLE ===== */
.title {
  font-size: 26px;
  font-weight: 600;
  margin-bottom: 25px;
  color: #0d6efd;
  border-bottom: 2px solid #eef1f5;
  padding-bottom: 10px;
  text-align: center;
}

/* ===== SECTION ===== */
.section {
  margin-bottom: 22px;
}

.label {
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: .6px;
  color: #6c757d;
  margin-bottom: 6px;
  font-weight: 600;
}

.value {
  font-size: 16px;
  line-height: 1.6;
  color: #2d3748;
}

.amount {
  font-weight: 600;
  color: #198754;
  background: #f1fbf6;
  padding: 10px 14px;
  border-radius: 6px;
  display: inline-block;
}

/* ===== BUTTON ===== */
.actions {
  margin-top: 30px;
  text-align: center;
}

.actions .btn {
  display: inline-block;
  padding: 10px 20px;
  background: #0d6efd;
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
}

.actions .btn:hover {
  background: #0b5ed7;
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(13,110,253,0.3);
}

/* ===== ANIMATION ===== */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .container {
    margin-left: 0;
    padding: 20px 15px;
    align-items: flex-start; /* top align on small screens */
  }

  .card {
    padding: 25px 20px;
  }

  .title {
    font-size: 22px;
  }

  .value {
    font-size: 15px;
  }
}


</style>
</head>

<body>
      <!-- Header -->
  <header class="header">
    <h4>WELCOME SCHOLARS</h4>
  </header>

  <!-- Dashboard Container -->
  <div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
      <ul>
        <li><a href="dashboardusers.php">Dashboard</a></li>
        <li><a href="schemes.php">Scholarship</a></li>
        <li><a href="applicationhistory.php">Application History</a></li>
        <li><a href="account.php">Account</a></li>
        <li><a href="log_out.php" class="logout">Logout</a></li>
      </ul>
    </aside>

<div class="container">
    <div class="card">

        <!-- Scholarship Name -->
        <div class="title">View Scholaship Details</div>

        <div class="section">
            <div class="label">scholarships</div>
            <div class="value">Tulong Dulong program (TDP)</div>
        </div>

        <!-- Amount Per Year -->
        <div class="section">
            <div class="label">Amount Per Year</div>
            <div class="value"> ₱7,500.00 per semester or ₱15,000.00 per academic year, which covers school fees and daily allowances.</div>
        </div>

        <!-- Eligibility -->
        <div class="section">
            <div class="label">Eligibility</div>
            <div class="value">Must be enrolled in any first undergraduate degree program
            </div>
        </div>

        <!-- Required Documents -->
        <div class="section">
            <div class="label">Required Documents</div>
            <div class="value">
                 Registration Form ,grade
            </div>
        </div>

         <div class="section">
            <div class="label"> scholar Description</div>
            <div class="value">
                 The Tulong Dunong Program (TDP) is a Commission on Higher Education (CHED) managed grant-in-aid program providing financial assistance of ₱15,000 annually (₱7,500 per semester) to qualified Filipino college students. It aids low-income, underprivileged students, including those under special groups (e.g., PWDs, solo parents), enrolled in accredited public universities (SUCs/LUCs) or private institutions
            </div>
        </div>

        <!-- Back Button -->
        <div class="actions">
            <a href="dashboardusers.php" class="btn">← Back to List</a>
        </div>

    </div>
</div>

</body>
</html>