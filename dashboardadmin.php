<?php
session_start();
require_once("dbconfig.php");

if (!isset($_SESSION['adminid'])) {
    header("Location: adminlogin.php");
    exit();
}

// ── Counts ──────────────────────────────────────────────────────────────────
$total_applicants   = $con->query("SELECT COUNT(*) FROM applications_form")->fetch_row()[0] ?? 0;
$total_scholarships = $con->query("SELECT COUNT(*) FROM scholarship")->fetch_row()[0] ?? 0;
$total_pending      = $con->query("SELECT COUNT(*) FROM applications_form WHERE status='Pending'")->fetch_row()[0] ?? 0;
$total_approved     = $con->query("SELECT COUNT(*) FROM applications_form WHERE status='Approved'")->fetch_row()[0] ?? 0;
$approval_rate      = $total_applicants > 0 ? round(($total_approved / $total_applicants) * 100) : 0;

// ── Applicant list (client-side pagination, search, and filtering) ──────────
$applicants_sql = "
    SELECT
        a.aid,
        CONCAT(a.first_name, ' ', a.last_name) AS full_name,
        s.scholarship_name,
        a.status,
        a.date_applied AS created_at
    FROM applications_form a
    INNER JOIN scholarship s ON a.sid = s.sid
    ORDER BY a.date_applied DESC
    LIMIT 300
";
$applicants_result = $con->query($applicants_sql);

require_once("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard – Scholarship Management System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #1e293b;
    }

    /* ── Seamless Layout Alignment Overrides ── */
    /* Forces the header top bar and main grid to align uniformly with the sidebar */
    #header, .top-navbar, .header-panel {
      left: 260px !important;
      width: calc(100% - 260px) !important;
      background: #ffffff !important;
      border-bottom: 1px solid #e2e8f0 !important;
      padding: 16px 40px !important;
    }

    /* Target global profile image wrapper to make it larger and premium */
    .profile-img, .user-avatar, .header i.ti-user, [class*="profile"] img {
      width: 42px !important;
      height: 42px !important;
      font-size: 20px !important;
      border-radius: 50% !important;
      object-fit: cover;
    }

    /* Fluid Layout Adaptive Main Workspace Workspace Container */
    .main {
      flex: 1;
      padding: 40px;
      width: calc(100% - 260px);
      margin-left: 260px;
      margin-right: auto;
      transition: all 0.3s ease;
    }

    /* Sidebar Selection State Interactive Styling Rules */
    .sidebar a, .nav-sidebar a, .aside a, #sidebar menu a {
      transition: all 0.2s ease;
      position: relative;
    }
    .sidebar a:hover, .sidebar a.active,
    .nav-sidebar a:hover, .nav-sidebar a.active {
      background-color: #eff6ff !important;
      color: #0d6efd !important;
      font-weight: 600 !important;
      border-radius: 8px;
    }

    /* ── Dashboard Top Header Row ── */
    .dash-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e2e8f0;
    }
    .dash-title   { font-size: 26px; font-weight: 700; color: #0f172a; letter-spacing: -0.5px; }
    .dash-subtitle { font-size: 14px; color: #64748b; margin-top: 4px; font-weight: 400; }

    .badge-live {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
      color: #16a34a;
      background: #dcfce7;
      padding: 6px 14px;
      border-radius: 50px;
      border: 1px solid #bbf7d0;
    }
    .badge-live .dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #16a34a;
      animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* ── Premium Uniform Fluid Stat Cards Grid ── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 24px;
      margin-bottom: 32px;
      width: 100%;
    }

    a.stat-card { text-decoration: none; }

    .stat-card {
      display: block;
      background: #ffffff;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
      transition: transform .2s cubic-bezier(0.4, 0, 0.2, 1), box-shadow .2s;
    }
    .stat-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
      border-color: #cbd5e1;
    }

    .stat-icon {
      width: 44px; height: 44px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
      margin-bottom: 16px;
    }
    .icon-blue  { background: #eff6ff; color: #1d4ed8; }
    .icon-teal  { background: #e0f2fe; color: #0369a1; }
    .icon-amber { background: #fef3c7; color: #b45309; }
    .icon-green { background: #dcfce7; color: #15803d; }

    .stat-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: #64748b;
      margin-bottom: 6px;
    }
    .stat-value {
      font-size: 32px;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.1;
    }
    .stat-note {
      font-size: 12px;
      color: #64748b;
      margin-top: 10px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* ── Main Data Panel Table Structure ── */
    .panel {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
      overflow: hidden;
      margin-bottom: 40px;
      width: 100%;
    }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 20px 24px;
      border-bottom: 1px solid #e2e8f0;
    }
    .panel-title { font-size: 16px; font-weight: 700; color: #0f172a; }
    .view-all-link {
      font-size: 13px; color: #0d6efd; text-decoration: none;
      display: inline-flex; align-items: center; gap: 4px; font-weight: 600;
    }
    .view-all-link:hover { text-decoration: underline; }

    .search-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 16px 24px;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }
    .search-wrap { position: relative; flex: 1; }
    .search-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #94a3b8; }
    .search-wrap input {
      width: 100%; padding: 10px 16px 10px 42px; font-size: 14px;
      border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;
      outline: none; transition: all .2s;
    }
    .search-wrap input:focus { border-color: #3b82f6; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }

    .filter-group { display: flex; align-items: center; gap: 8px; }
    .filter-btn {
      font-size: 13px; font-weight: 600; padding: 8px 14px; border: 1px solid #e2e8f0;
      border-radius: 8px; background: #ffffff; color: #64748b; cursor: pointer;
      display: flex; align-items: center; gap: 6px; white-space: nowrap; transition: all 0.15s;
    }
    .filter-btn:hover { background: #f1f5f9; color: #0f172a; }
    .filter-btn.active { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
    .count-badge {
      display: inline-flex; align-items: center; justify-content: center;
      font-size: 11px; font-weight: 700; min-width: 20px; height: 20px;
      padding: 0 6px; border-radius: 20px; background: #f1f5f9; color: #64748b;
    }
    .filter-btn.active .count-badge { background: #dbeafe; color: #1d4ed8; }

    /* ── Precise Table Presentation ── */
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    thead th {
      font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
      color: #64748b; padding: 14px 24px; background: #f8fafc; border-bottom: 1px solid #e2e8f0;
    }
    tbody tr { border-bottom: 1px solid #f1f5f9; transition: background .15s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }
    td { padding: 16px 24px; font-size: 14px; color: #334155; vertical-align: middle; }

    /* Avatar Branding Layouts */
    .avatar-wrap { display: flex; align-items: center; gap: 12px; }
    .avatar {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .av-blue   { background: #eff6ff; color: #1d4ed8; }
    .av-teal   { background: #e0f2fe; color: #0369a1; }
    .av-amber  { background: #fef3c7; color: #b45309; }
    .av-coral  { background: #fee2e2; color: #b91c1c; }
    .av-purple { background: #f5f3ff; color: #5b21b6; }
    .av-name   { font-weight: 600; font-size: 14px; color: #0f172a; }
    .av-date   { font-size: 12px; color: #64748b; margin-top: 2px; }

    /* Status Pills */
    .pill { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 50px; }
    .pill-approved { background: #dcfce7; color: #16a34a; }
    .pill-pending  { background: #fef3c7; color: #d97706; }
    .pill-rejected { background: #fee2e2; color: #dc2626; }
    .pill-review   { background: #f1f5f9; color: #475569; }
    .scholarship-name { font-size: 14px; color: #475569; font-weight: 500; }

    /* Pagination controls */
    .pagination-footer {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 24px; border-top: 1px solid #e2e8f0; background: #ffffff;
    }
    .page-info { font-size: 13px; color: #64748b; font-weight: 500; }
    .pagination-buttons { display: flex; align-items: center; gap: 6px; }
    .page-btn {
      display: inline-flex; align-items: center; justify-content: center; padding: 8px 14px;
      font-size: 13px; font-weight: 600; background-color: #ffffff; border: 1px solid #e2e8f0;
      color: #334155; border-radius: 8px; cursor: pointer; transition: all 0.15s; gap: 4px;
    }
    .page-btn:hover:not(:disabled) { background-color: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
    .page-btn:disabled { opacity: 0.5; cursor: not-allowed; background-color: #f1f5f9; }
    .page-btn.active-num { background-color: #3b82f6; color: #ffffff; border-color: #3b82f6; }

    .empty-state { text-align: center; padding: 48px 24px; color: #94a3b8; }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }

    /* ── Seamless Fluid Responsive Breakpoints ── */
    @media (max-width: 1200px) {
      .cards-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    }
    @media (max-width: 992px) {
      .main, #header, .top-navbar, .header-panel { margin-left: 0 !important; left: 0 !important; width: 100% !important; }
      .main { padding: 24px; }
    }
    @media (max-width: 650px) {
      .cards-grid { grid-template-columns: 1fr; }
      .search-bar { flex-direction: column; align-items: stretch; }
      .pagination-footer { flex-direction: column; gap: 12px; text-align: center; }
    }
  </style>
</head>

<body>
<main class="main">

  <div class="dash-header">
    <div>
      <h2 class="dash-title">Dashboard</h2>
      <p class="dash-subtitle">Scholarship Administration Management</p>
    </div>
  </div>

  <div class="cards-grid">

    <a href="newapplication.php" class="stat-card">
      <div class="stat-icon icon-blue">
        <i class="ti ti-users"></i>
      </div>
      <p class="stat-label">Total applicants</p>
      <p class="stat-value"><?= number_format($total_applicants) ?></p>
      <p class="stat-note"><i class="ti ti-user-plus"></i> System Registered</p>
    </a>

    <a href="scholarship.php" class="stat-card">
      <div class="stat-icon icon-teal">
        <i class="ti ti-award"></i>
      </div>
      <p class="stat-label">Scholarships</p>
      <p class="stat-value"><?= number_format($total_scholarships) ?></p>
      <p class="stat-note"><i class="ti ti-briefcase"></i> Active programs</p>
    </a>

    <div class="stat-card" style="cursor:default;">
      <div class="stat-icon icon-amber">
        <i class="ti ti-clock"></i>
      </div>
      <p class="stat-label">Pending review</p>
      <p class="stat-value"><?= number_format($total_pending) ?></p>
      <p class="stat-note"><i class="ti ti-alert-circle"></i> Requires Action</p>
    </div>

    <a href="scholars_list.php" class="stat-card">
      <div class="stat-icon icon-green">
        <i class="ti ti-circle-check"></i>
      </div>
      <p class="stat-label">Approved Scholars</p>
      <p class="stat-value"><?= number_format($total_approved) ?></p>
      <p class="stat-note"><i class="ti ti-trending-up"></i> <?= $approval_rate ?>% Selection Rate</p>
    </a>

  </div>

  <div class="panel">
    <div class="panel-header">
      <p class="panel-title">Applications List</p>
      <a href="scholars_list.php" class="view-all-link">
        Advanced Management <i class="ti ti-arrow-right"></i>
      </a>
    </div>

    <div class="search-bar">
      <div class="search-wrap">
        <i class="ti ti-search"></i>
        <input type="text" id="appl-search" placeholder="Search applicant names or programs..." oninput="resetToFirstPageAndFilter()">
      </div>
      <div class="filter-group">
        <button class="filter-btn active" onclick="setFilter('all')" id="fb-all">All <span class="count-badge" id="cnt-all">0</span></button>
        <button class="filter-btn" onclick="setFilter('Pending')" id="fb-Pending">Pending <span class="count-badge" id="cnt-Pending">0</span></button>
        <button class="filter-btn" onclick="setFilter('Approved')" id="fb-Approved">Approved <span class="count-badge" id="cnt-Approved">0</span></button>
      </div>
    </div>

    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>Applicant Profile</th>
            <th>Scholarship Program</th>
            <th>Verification Status</th>
          </tr>
        </thead>
        <tbody id="appl-tbody">
          </tbody>
      </table>
    </div>

    <div class="pagination-footer">
      <div class="page-info" id="page-info-text">Showing 0 to 0 of 0 entries</div>
      <div class="pagination-buttons" id="pagination-controls"></div>
    </div>

  </div>

</main>

<?php
// Build JSON structured data transfer mapping matrix
$appl_data = [];
$av_colors = ['av-blue', 'av-teal', 'av-amber', 'av-coral', 'av-purple'];
$j = 0;
if ($applicants_result && $applicants_result->num_rows > 0) {
    while ($row = $applicants_result->fetch_assoc()) {
        $initials = '';
        $parts = explode(' ', trim($row['full_name']));
        foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
        $initials = substr($initials, 0, 2);

        $appl_data[] = [
            'initials'         => $initials,
            'av'               => $av_colors[$j % count($av_colors)],
            'name'             => $row['full_name'],
            'scholarship_name' => $row['scholarship_name'],
            'status'           => $row['status'],
            'date'             => !empty($row['created_at']) ? date('M j, Y', strtotime($row['created_at'])) : '—',
        ];
        $j++;
    }
}
?>

<script>
const APPLICANTS = <?= json_encode($appl_data, JSON_HEX_TAG | JSON_HEX_AMP) ?>;

const PILL_MAP = {
  'Approved':     ['pill-approved', 'ti-check'],
  'Pending':      ['pill-pending',  'ti-clock'],
  'Rejected':     ['pill-rejected', 'ti-x'],
  'Under Review': ['pill-review',   'ti-eye'],
};

let activeFilter = 'all';
let currentPage = 1;
const itemsPerPage = 5; // Enforces exactly 5 listings per page cleanly

function setFilter(f) {
  activeFilter = f;
  ['all', 'Pending', 'Approved'].forEach(k => {
    const el = document.getElementById('fb-' + k);
    if (el) el.classList.toggle('active', k === f);
  });
  currentPage = 1;
  filterApplicants();
}

function resetToFirstPageAndFilter() {
  currentPage = 1;
  filterApplicants();
}

function filterApplicants() {
  const q = document.getElementById('appl-search').value.toLowerCase().trim();
  const tbody = document.getElementById('appl-tbody');

  const matched = APPLICANTS.filter(a => {
    const matchQ = !q || a.name.toLowerCase().includes(q) || a.scholarship_name.toLowerCase().includes(q);
    const matchF = activeFilter === 'all' || a.status === activeFilter;
    return matchQ && matchF;
  });

  document.getElementById('cnt-all').textContent     = APPLICANTS.length;
  document.getElementById('cnt-Pending').textContent  = APPLICANTS.filter(a => a.status === 'Pending').length;
  document.getElementById('cnt-Approved').textContent = APPLICANTS.filter(a => a.status === 'Approved').length;

  if (!matched.length) {
    tbody.innerHTML = `<tr><td colspan="3"><div class="empty-state"><i class="ti ti-mood-empty"></i>No results found.</div></td></tr>`;
    document.getElementById('page-info-text').textContent = "Showing 0 to 0 of 0 entries";
    document.getElementById('pagination-controls').innerHTML = '';
    return;
  }

  const totalItems = matched.length;
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  
  if (currentPage > totalPages) currentPage = totalPages;
  if (currentPage < 1) currentPage = 1;

  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
  const paginatedItems = matched.slice(startIndex, endIndex);

  tbody.innerHTML = paginatedItems.map(a => {
    const [pillClass, pillIcon] = PILL_MAP[a.status] ?? ['pill-review', 'ti-clock'];
    return `
      <tr>
        <td>
          <div class="avatar-wrap">
            <div class="avatar ${a.av}">${a.initials}</div>
            <div>
              <p class="av-name">${a.name}</p>
              <p class="av-date">${a.date}</p>
            </div>
          </div>
        </td>
        <td class="scholarship-name">${a.scholarship_name}</td>
        <td><span class="pill ${pillClass}"><i class="ti ${pillIcon}"></i> ${a.status}</span></td>
      </tr>`;
  }).join('');

  document.getElementById('page-info-text').textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`;
  renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
  const container = document.getElementById('pagination-controls');
  let html = `<button class="page-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})"><i class="ti ti-chevron-left"></i> Prev</button>`;

  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
      html += `<button class="page-btn ${currentPage === i ? 'active-num' : ''}" onclick="changePage(${i})">${i}</button>`;
    } else if (i === currentPage - 2 || i === currentPage + 2) {
      html += `<span style="color:#94a3b8; padding:0 4px;">...</span>`;
    }
  }

  html += `<button class="page-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">Next <i class="ti ti-chevron-right"></i></button>`;
  container.innerHTML = html;
}

function changePage(p) {
  currentPage = p;
  filterApplicants();
}

// ── Auto Active-State Class Selection Script ──
document.addEventListener('DOMContentLoaded', function() {
  const currentFilename = window.location.pathname.split('/').pop() || 'dashboardadmin.php';
  const sidebarLinks = document.querySelectorAll('.sidebar a, .nav-sidebar a, .aside a, #sidebar a');
  
  sidebarLinks.forEach(link => {
    const hrefFile = link.getAttribute('href');
    if (hrefFile && currentFilename.includes(hrefFile)) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
  filterApplicants();
});
</script>
</body>
</html>