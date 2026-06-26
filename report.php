<?php
session_start();
require_once("dbconfig.php");

if(!isset($_SESSION['adminid'])){
    header("Location: adminlogin.php");
    exit();
}

$applications = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form");
$users        = mysqli_query($con, "SELECT COUNT(*) AS total FROM students");
$scholarships = mysqli_query($con, "SELECT COUNT(*) AS total FROM scholarship");
$approved     = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Approved'");
$rejected     = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Rejected'");
$pending      = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Pending'");

$applications = mysqli_fetch_assoc($applications);
$users        = mysqli_fetch_assoc($users);
$scholarships = mysqli_fetch_assoc($scholarships);
$approved     = mysqli_fetch_assoc($approved);
$rejected     = mysqli_fetch_assoc($rejected);
$pending      = mysqli_fetch_assoc($pending);

$total        = (int)$applications['total'];
$app_approved = (int)$approved['total'];
$app_rejected = (int)$rejected['total'];
$app_pending  = (int)$pending['total'];

$pct_approved = $total > 0 ? round(($app_approved / $total) * 100) : 0;
$pct_rejected = $total > 0 ? round(($app_rejected / $total) * 100) : 0;
$pct_pending  = $total > 0 ? round(($app_pending / $total) * 100) : 0;

date_default_timezone_set('Asia/Manila');
$now = date('F d, Y h:i A');

require_once("header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Reports – Scholarship System</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
/* ===== GLOBAL RESET ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background: #f8fafc;
    color: #1e293b;
    min-height: 100vh;
}

/* ===== APP LAYOUT CONFIGURATION ===== */
.page-wrapper {
    flex: 1;
    padding: 40px;
    width: calc(100% - 260px);
    margin-left: 260px;
    margin-right: auto;
    transition: all 0.3s ease;
}

/* ── Sidebar Active Class Accent Rule ── */
.sidebar a:hover, .sidebar a.active,
.nav-sidebar a:hover, .nav-sidebar a.active,
.aside a:hover, .aside a.active,
#sidebar a:hover, #sidebar a.active {
    background-color: #eff6ff !important;
    color: #0d6efd !important;
    font-weight: 600 !important;
    border-radius: 8px;
}

/* ===== PAGE HEADER ===== */
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.page-title-group h1 {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.5px;
}

.page-subtitle {
    font-size: 14px;
    color: #64748b;
    margin-top: 4px;
    font-weight: 400;
}

/* ===== EXPORT ACTION BUTTON ===== */
.btn-export {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #0d6efd;
    color: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.btn-export:hover {
    background: #376ca1;
    border-color: #94a3b8;
    transform: translateY(-1px);
}

/* ===== SIX BOX METRIC CARDS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 32px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.01);
}

.stat-info .stat-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 6px;
}

.stat-info .stat-val {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.1;
}

.stat-info .stat-subtext {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 6px;
    font-weight: 500;
}

.stat-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

/* ── Six Box Color Variants ── */
.color-blue { background: #eff6ff; color: #1d4ed8; }
.color-amber { background: #fffbeb; color: #b45309; }
.color-emerald { background: #ecfdf5; color: #047857; }
.color-rose { background: #fff1f2; color: #be123c; }
.color-purple { background: #f5f3ff; color: #6d28d9; }
.color-teal { background: #f0fdfa; color: #0f766e; }

/* ===== REPORT TABLE CARD COMPONENT ===== */
.table-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    overflow: hidden;
}

.table-scroll { width: 100%; overflow-x: auto; }
table { width: 100%; border-collapse: collapse; text-align: left; }

thead tr { background: #f8fafc; border-bottom: 1px solid #e2e8f0; }

th {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    padding: 14px 24px;
    white-space: nowrap;
}

td {
    padding: 16px 24px;
    font-size: 14px;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover { background: #f8fafc; }

/* ── Inline Row Decorators ── */
.row-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    color: #0f172a;
}

.row-icon-wrap {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

/* ── Custom Micro Pill Chips ── */
.status-chip {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.chip-blue    { background: #eff6ff; color: #1e40af; }
.chip-amber   { background: #fffbeb; color: #92400e; }
.chip-emerald { background: #ecfdf5; color: #065f46; }
.chip-rose    { background: #fff1f2; color: #9f1239; }
.chip-purple  { background: #f5f3ff; color: #5b21b6; }
.chip-teal    { background: #f0fdfa; color: #115e59; }

.count-badge {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.table-footer {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 16px 24px;
    font-size: 13px;
    color: #64748b;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    font-weight: 500;
}

/* ===== METRICS RESPONSIVE BREAKPOINTS ===== */
@media (max-width: 1200px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 992px) {
    .page-wrapper { margin-left: 0 !important; width: 100% !important; padding: 24px; }
}

@media (max-width: 680px) {
    .stats-grid { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
    .btn-export { width: 100%; justify-content: center; }
}
</style>
</head>
<body>

<div class="page-wrapper">

    <div class="page-header">
        <div class="page-title-group">
            <h1>System Reports</h1>
            <p class="page-subtitle">Real-time status analysis matrices and summary distributions.</p>
        </div>
        <button class="btn-export" onclick="window.print()">
            <i class="ti ti-download"></i> Export Data
        </button>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Applications</div>
                <div class="stat-val"><?= number_format($total); ?></div>
                <div class="stat-subtext">Cumulative files handled</div>
            </div>
            <div class="stat-icon-box color-blue">
                <i class="ti ti-folders"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Pending Applications</div>
                <div class="stat-val"><?= number_format($app_pending); ?></div>
                <div class="stat-subtext"><?= $pct_pending; ?>% of aggregate volume</div>
            </div>
            <div class="stat-icon-box color-amber">
                <i class="ti ti-clock-hour-4"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Approved Scholars</div>
                <div class="stat-val"><?= number_format($app_approved); ?></div>
                <div class="stat-subtext"><?= $pct_approved; ?>% confirmation yield</div>
            </div>
            <div class="stat-icon-box color-emerald">
                <i class="ti ti-circle-check"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Rejected Files</div>
                <div class="stat-val"><?= number_format($app_rejected); ?></div>
                <div class="stat-subtext"><?= $pct_rejected; ?>% denied accounts</div>
            </div>
            <div class="stat-icon-box color-rose">
                <i class="ti ti-circle-x"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Users</div>
                <div class="stat-val"><?= number_format($users['total']); ?></div>
                <div class="stat-subtext">Active account profiles</div>
            </div>
            <div class="stat-icon-box color-purple">
                <i class="ti ti-users"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Available Scholarships</div>
                <div class="stat-val"><?= number_format($scholarships['total']); ?></div>
                <div class="stat-subtext">Active system grant rules</div>
            </div>
            <div class="stat-icon-box color-teal">
                <i class="ti ti-award"></i>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Report Classification Label</th>
                    <th>Classification Type</th>
                    <th>Data Summary Total Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-blue"><i class="ti ti-folders"></i></div>
                            Total Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-blue">Applications</span></td>
                    <td><span class="count-badge"><?= number_format($total); ?></span></td>
                </tr>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-amber"><i class="ti ti-clock-hour-4"></i></div>
                            Pending Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-amber">Pending</span></td>
                    <td><span class="count-badge"><?= number_format($app_pending); ?></span></td>
                </tr>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-emerald"><i class="ti ti-circle-check"></i></div>
                            Approved Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-emerald">Approved</span></td>
                    <td><span class="count-badge"><?= number_format($app_approved); ?></span></td>
                </tr>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-rose"><i class="ti ti-circle-x"></i></div>
                            Rejected Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-rose">Rejected</span></td>
                    <td><span class="count-badge"><?= number_format($app_rejected); ?></span></td>
                </tr>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-purple"><i class="ti ti-users"></i></div>
                            Total Users
                        </div>
                    </td>
                    <td><span class="status-chip chip-purple">Users</span></td>
                    <td><span class="count-badge"><?= number_format($users['total']); ?></span></td>
                </tr>
                <tr>
                    <td>
                        <div class="row-item">
                            <div class="row-icon-wrap color-teal"><i class="ti ti-award"></i></div>
                            Total Scholarships
                        </div>
                    </td>
                    <td><span class="status-chip chip-teal">Scholarships</span></td>
                    <td><span class="count-badge"><?= number_format($scholarships['total']); ?></span></td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="table-footer">
            <i class="ti ti-info-circle"></i>
            Data reflects current database state &mdash; <?= $now; ?>
        </div>
    </div>

</div>

<script>
  /* ── Auto Active-State Class Selection Script ── */
  document.addEventListener('DOMContentLoaded', function() {
    const currentFilename = window.location.pathname.split('/').pop() || 'report.php';
    const sidebarLinks = document.querySelectorAll('.sidebar a, .nav-sidebar a, .aside a, #sidebar a');
    
    sidebarLinks.forEach(link => {
      const hrefFile = link.getAttribute('href');
      if (hrefFile && currentFilename.includes(hrefFile)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  });
</script>

</body>
</html>