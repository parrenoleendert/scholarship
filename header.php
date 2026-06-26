<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scholarship Management System - Admin</title>

  <!-- Font Awesome CDN for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">

  <style>
    /* ===== RESET ===== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', Tahoma, sans-serif;
    }

    body {
      background-color: #f1f4fb;
      color: #111d2e;
    }

    /* ===== HEADER ===== */
    .header {
      position: fixed;
      top: 0;
      left: 280px;
      right: 0;
      height: 70px;
      background: #f1f4fb;
      color: #0c0c0c;
      padding: 0 30px;
      display: flex;
      align-items: center;
      justify-content: flex-end;
      z-index: 1000;
      box-shadow: 0 4px 20px rgba(13, 110, 253, 0.15);
    }

    .header h4 {
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .header-icons {
      display: flex;
      align-items: center;
      gap: 25px;
    }

    .header a {
      color: #495057;
      text-decoration: none;
      position: relative;
      font-size: 22px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .header a:hover {
      color: #0d6efd;
      transform: scale(1.15);
    }

    .notif-count {
      position: absolute;
      top: -8px;
      right: -12px;
      background: #ff4757;
      color: white;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 6px;
      border-radius: 50%;
      min-width: 20px;
      text-align: center;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.1); }
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: 280px;
      height: 100vh;
      background: linear-gradient(180deg, #ffffff 0%, #f8fafb 100%);
      padding: 0;
      overflow-y: auto;
      border-right: 1px solid #e4e9f4;
      box-shadow: 2px 0 12px rgba(0, 0, 0, 0.05);
      z-index: 1001;
    }

    /* ===== BRAND SECTION ===== */
    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 22px 20px;
      margin: 12px 0 20px 0;
      border-bottom: 1px solid #e4e9f4;
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.05) 0%, rgba(13, 202, 240, 0.05) 100%);
    }

    .brand-icon {
      width: 52px;
      height: 52px;
      border-radius: 13px;
      background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 26px;
      flex-shrink: 0;
      box-shadow: 0 6px 20px rgba(13, 110, 253, 0.35);
      transition: all 0.3s ease;
    }

    .sidebar-brand:hover .brand-icon {
      transform: translateY(-3px);
      box-shadow: 0 8px 25px rgba(13, 110, 253, 0.4);
    }

    .brand-text {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .brand-name {
      font-size: 17px;
      font-weight: 800;
      color: #121213;
      letter-spacing: 1px;
      font-family: 'Sora', sans-serif;
      line-height: 1.2;
    }

    .brand-subtitle {
      font-size: 11px;
      color: #535455;
      font-weight: 500;
      letter-spacing: 0.3px;
      margin-top: 2px;
    }

    .brand-logo {
      width: 52px;
      height: 52px;
      border-radius: 27px; /* remove if you want square */
      object-fit: cover;
      flex-shrink: 0;
      box-shadow: 0 6px 18px rgba(0,0,0,0.15);
    }

    /* ===== SIDEBAR LINKS ===== */
    .sidebar ul {
      list-style: none;
      padding: 8px 12px;
    }

    .sidebar ul li {
      margin-bottom: 6px;
    }

    .sidebar ul li a {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      color: #495057;
      text-decoration: none;
      font-size: 14px;
      font-weight: 500;
      border-radius: 10px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }

    .sidebar ul li a i {
      margin-right: 12px;
      font-size: 16px;
      width: 20px;
      text-align: center;
      transition: all 0.3s ease;
    }

    .sidebar ul li a:hover {
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 202, 240, 0.05) 100%);
      color: #0d6efd;
      padding-left: 22px;
    }

    .sidebar ul li a:hover i {
      transform: translateX(3px);
      color: #0d6efd;
    }

    .sidebar ul li a.active {
      background: linear-gradient(135deg, rgba(13, 110, 253, 0.15) 0%, rgba(13, 202, 240, 0.1) 100%);
      color: #0d6efd;
      font-weight: 600;
      border-left: 3px solid #0d6efd;
      padding-left: 17px;
      box-shadow: inset 0 2px 8px rgba(13, 110, 253, 0.1);
    }

    .sidebar ul li a.active i {
      color: #0d6efd;
      transform: translateX(3px);
    }

    /* ===== LOGOUT SECTION ===== */
    .sidebar ul li:last-child {
      margin-top: 20px;
      padding-top: 12px;
      border-top: 1px solid #e4e9f4;
    }

    .sidebar ul li a.logout {
      color: #dc3545;
    }

    .sidebar ul li a.logout:hover {
      background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
      color: #dc3545;
      padding-left: 22px;
    }

    .sidebar ul li a.logout:hover i {
      transform: translateX(3px);
      color: #dc3545;
    }

    .sidebar ul li a.logout i {
      color: #dc3545;
    }

    /* ===== SCROLLBAR ===== */
    .sidebar::-webkit-scrollbar { width: 6px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb { background: #d0d7de; border-radius: 3px; }
    .sidebar::-webkit-scrollbar-thumb:hover { background: #0d6efd; }

    /* ===== MAIN CONTENT ===== */
    .dashboard {
      margin-top: 60px;
       padding: 15px;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      .sidebar.active {
        transform: translateX(0);
      }
      .header {
        left: 0;
      }
      .dashboard {
        margin-left: 0;
      }
    }
  </style>
</head>

<body>

  <!-- Header -->
  <header class="header">
    <div class="header-icons">
      <a href="adminaccount.php" title="Account">
        <i class="fas fa-user-circle"></i>
      </a>
    </div>
  </header>

  <!-- Sidebar -->
  <aside class="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
      <img src="uploads/sass.jpg" alt="Logo" class="brand-logo">
      <div class="brand-text">
        <div class="brand-name">SAS</div>
        <div class="brand-subtitle">Scholarship Administrator</div>
      </div>
    </div>

    <!-- Navigation -->
    <ul>
      <li><a href="dashboardadmin.php" class=""><i class="fas fa-house"></i> Dashboard</a></li>
      <li><a href="newapplication.php"><i class="fas fa-file-lines"></i> New Application</a></li>
      <li><a href="scholars_list.php"><i class="fas fa-user-group"></i> Applicant Management</a></li>
      <li><a href="scholarship.php"><i class="fas fa-graduation-cap"></i> Scholarship Management</a></li>
      <li><a href="users_mgt.php"><i class="fas fa-user-gear"></i> User Management</a></li>
      <li><a href="report.php"><i class="fas fa-chart-simple"></i> System Reports</a></li>
      <li><a href="adminlog_out.php" class="logout"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
    </ul>

  </aside>

  <!-- Main Content -->
  <div class="dashboard">
    <!-- Your page content goes here -->
  </div>

</body>
</html>