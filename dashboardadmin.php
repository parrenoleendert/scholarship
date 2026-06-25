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

// ── Recent applications (last 5) ─────────────────────────────────────────────
$recent_sql = "
    SELECT
        CONCAT(a.first_name, ' ', a.last_name) AS full_name,
        s.scholarship_name,
        a.status,
        a.date_applied AS created_at
    FROM applications_form a
    INNER JOIN scholarship s ON a.sid = s.sid
    ORDER BY a.date_applied DESC
    LIMIT 5
";
$recent_result = $con->query($recent_sql);

// ── Applicant list (client-side search/filter) ────────────────────────────────
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
    LIMIT 100
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
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f4f6f9;
      color: #1a1a2e;
    }

    .main {
      flex: 1;
      padding: 32px 36px;
      max-width: 1400px;
      margin-left: 260px;
    }

    /* ── Header ── */
    .dash-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 18px;
      border-bottom: 1px solid #e2e8f0;
    }
    .dash-title   { font-size: 20px; font-weight: 600; color: #1a1a2e; }
    .dash-subtitle { font-size: 12px; color: #64748b; margin-top: 3px; }

    .badge-live {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 500;
      color: #16a34a;
      background: #dcfce7;
      padding: 4px 10px;
      border-radius: 20px;
      border: 1px solid #bbf7d0;
    }
    .badge-live .dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #16a34a;
      animation: pulse 2s infinite;
    }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.3} }

    /* ── Stat cards ── */
    .cards-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
      gap: 12px;
      margin-bottom: 28px;
    }

    a.stat-card { text-decoration: none; }

    .stat-card {
      display: block;
      background: #ffffff;
      border-radius: 14px;
      padding: 18px 16px;
      border: 1px solid #e2e8f0;
      transition: transform .15s, box-shadow .15s, border-color .15s;
      cursor: pointer;
    }
    .stat-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 24px rgba(0,0,0,.07);
      border-color: #cbd5e1;
    }

    .stat-icon {
      width: 36px; height: 36px;
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 17px;
      margin-bottom: 12px;
    }
    .icon-blue  { background: #dbeafe; color: #1d4ed8; }
    .icon-teal  { background: #ccfbf1; color: #0f766e; }
    .icon-amber { background: #fef3c7; color: #b45309; }
    .icon-green { background: #dcfce7; color: #15803d; }

    .stat-label {
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #94a3b8;
      margin-bottom: 5px;
    }
    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #1a1a2e;
      line-height: 1;
    }
    .stat-note {
      font-size: 11px;
      color: #64748b;
      margin-top: 6px;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    /* ── Two-column panels ── */
    .panels-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .panel {
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
    }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 18px;
      border-bottom: 1px solid #f1f5f9;
    }
    .panel-title {
      font-size: 13px;
      font-weight: 600;
      color: #1a1a2e;
    }
    .view-all-link {
      font-size: 11px;
      color: #3b82f6;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 3px;
    }
    .view-all-link:hover { text-decoration: underline; }

    /* ── Search + filter bar ── */
    .search-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border-bottom: 1px solid #f1f5f9;
      background: #f8fafc;
    }
    .search-wrap {
      position: relative;
      flex: 1;
    }
    .search-wrap i {
      position: absolute;
      left: 9px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 14px;
      color: #94a3b8;
      pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      padding: 6px 10px 6px 30px;
      font-size: 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      color: #1a1a2e;
      outline: none;
      transition: border-color .15s;
    }
    .search-wrap input:focus { border-color: #93c5fd; }

    .filter-btn {
      font-size: 11px;
      font-weight: 500;
      padding: 5px 10px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      color: #64748b;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: background .1s, color .1s, border-color .1s;
      white-space: nowrap;
    }
    .filter-btn:hover { background: #f1f5f9; }
    .filter-btn.active {
      background: #eff6ff;
      color: #1d4ed8;
      border-color: #bfdbfe;
    }
    .count-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 9px;
      font-weight: 600;
      min-width: 16px;
      height: 16px;
      padding: 0 4px;
      border-radius: 20px;
      background: #f1f5f9;
      color: #64748b;
    }
    .filter-btn.active .count-badge {
      background: #dbeafe;
      color: #1d4ed8;
    }

    /* ── Shared table styles ── */
    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left;
      font-size: 10px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: #94a3b8;
      padding: 10px 18px;
      background: #f8fafc;
      border-bottom: 1px solid #f1f5f9;
    }
    tbody tr {
      border-bottom: 1px solid #f8fafc;
      transition: background .1s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }
    td {
      padding: 10px 18px;
      font-size: 12px;
      color: #1a1a2e;
      vertical-align: middle;
    }

    /* ── Avatar ── */
    .avatar-wrap { display: flex; align-items: center; gap: 9px; }
    .avatar {
      width: 30px; height: 30px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 10px; font-weight: 600; flex-shrink: 0;
    }
    .av-blue   { background: #dbeafe; color: #1d4ed8; }
    .av-teal   { background: #ccfbf1; color: #0f766e; }
    .av-amber  { background: #fef3c7; color: #b45309; }
    .av-coral  { background: #fee2e2; color: #b91c1c; }
    .av-purple { background: #ede9fe; color: #6d28d9; }
    .av-name   { font-weight: 600; font-size: 12px; }
    .av-date   { font-size: 11px; color: #94a3b8; margin-top: 1px; }

    /* ── Status pills ── */
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 10px;
      font-weight: 600;
      padding: 3px 8px;
      border-radius: 20px;
      white-space: nowrap;
    }
    .pill i { font-size: 10px; }
    .pill-approved { background: #dcfce7; color: #15803d; }
    .pill-pending  { background: #fef3c7; color: #b45309; }
    .pill-rejected { background: #fee2e2; color: #b91c1c; }
    .pill-review   { background: #f1f5f9; color: #64748b; }

    .scholarship-name { font-size: 12px; color: #64748b; }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 32px 20px;
      color: #94a3b8;
      font-size: 13px;
    }
    .empty-state i { font-size: 32px; margin-bottom: 8px; display: block; }

    /* ── Responsive ── */
    @media (max-width: 960px) {
      .panels-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
      .main { padding: 20px 16px; margin-left: 0; }
      .dash-header { flex-direction: column; gap: 10px; }
      .cards-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
      .cards-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>
<main class="main">

  <!-- Header -->
  <div class="dash-header">
    <div>
      <h2 class="dash-title">Dashboard</h2>
      <p class="dash-subtitle">Scholarship Management System</p>
    </div>
    <span class="badge-live">
      <span class="dot"></span>
      Live
    </span>
  </div>

  <!-- Stat Cards -->
  <div class="cards-grid">

    <a href="newapplication.php" class="stat-card">
      <div class="stat-icon icon-blue">
        <i class="ti ti-users"></i>
      </div>
      <p class="stat-label">Total applicants</p>
      <p class="stat-value"><?= number_format($total_applicants) ?></p>
      <p class="stat-note">
        <i class="ti ti-user-plus" style="font-size:12px"></i>
        Registered
      </p>
    </a>

    <a href="scholarship.php" class="stat-card">
      <div class="stat-icon icon-teal">
        <i class="ti ti-award"></i>
      </div>
      <p class="stat-label">Scholarships</p>
      <p class="stat-value"><?= number_format($total_scholarships) ?></p>
      <p class="stat-note">
        <i class="ti ti-briefcase" style="font-size:12px"></i>
        Active programs
      </p>
    </a>

    <div class="stat-card" style="cursor:default;">
      <div class="stat-icon icon-amber">
        <i class="ti ti-clock"></i>
      </div>
      <p class="stat-label">Pending review</p>
      <p class="stat-value"><?= number_format($total_pending) ?></p>
      <p class="stat-note">
        <i class="ti ti-alert-circle" style="font-size:12px"></i>
        Needs attention
      </p>
    </div>

    <a href="scholars_list.php" class="stat-card">
      <div class="stat-icon icon-green">
        <i class="ti ti-circle-check"></i>
      </div>
      <p class="stat-label">Approved</p>
      <p class="stat-value"><?= number_format($total_approved) ?></p>
      <p class="stat-note">
        <i class="ti ti-trending-up" style="font-size:12px"></i>
        <?= $approval_rate ?>% approval rate
      </p>
    </a>

  </div>



    <!-- ── Applicant List ── -->
    <div class="panel">
      <div class="panel-header">
        <p class="panel-title">Applicant list</p>
        <a href="scholars_list.php" class="view-all-link">
          Manage <i class="ti ti-arrow-right" style="font-size:12px"></i>
        </a>
      </div>

      <!-- Search + filter -->
      <div class="search-bar">
        <div class="search-wrap">
          <i class="ti ti-search"></i>
          <input type="text" id="appl-search" placeholder="Search by name or scholarship…" oninput="filterApplicants()">
        </div>
        <button class="filter-btn active" onclick="setFilter('all')"     id="fb-all">All     <span class="count-badge" id="cnt-all">0</span></button>
        <button class="filter-btn"        onclick="setFilter('Pending')" id="fb-Pending">Pending <span class="count-badge" id="cnt-Pending">0</span></button>
        <button class="filter-btn"        onclick="setFilter('Approved')" id="fb-Approved">Approved <span class="count-badge" id="cnt-Approved">0</span></button>
      </div>

      <table>
        <thead>
          <tr>
            <th>Applicant</th>
            <th>Scholarship</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="appl-tbody">
          <!-- Populated by JS -->
        </tbody>
      </table>
    </div>

  </div>

</main>

<?php
// Build JSON payload for applicant list (client-side search/filter)
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

function setFilter(f) {
  activeFilter = f;
  ['all', 'Pending', 'Approved'].forEach(k => {
    document.getElementById('fb-' + k).classList.toggle('active', k === f);
  });
  filterApplicants();
}

function filterApplicants() {
  const q    = document.getElementById('appl-search').value.toLowerCase().trim();
  const tbody = document.getElementById('appl-tbody');

  const matched = APPLICANTS.filter(a => {
    const matchQ = !q || a.name.toLowerCase().includes(q) || a.scholarship_name.toLowerCase().includes(q);
    const matchF = activeFilter === 'all' || a.status === activeFilter;
    return matchQ && matchF;
  });

  // Update count badges
  document.getElementById('cnt-all').textContent     = APPLICANTS.length;
  document.getElementById('cnt-Pending').textContent  = APPLICANTS.filter(a => a.status === 'Pending').length;
  document.getElementById('cnt-Approved').textContent = APPLICANTS.filter(a => a.status === 'Approved').length;

  if (!matched.length) {
    tbody.innerHTML = `
      <tr><td colspan="3">
        <div class="empty-state">
          <i class="ti ti-mood-empty"></i>
          No applicants found.
        </div>
      </td></tr>`;
    return;
  }

  tbody.innerHTML = matched.map(a => {
    const [pillClass, pillIcon] = PILL_MAP[a.status] ?? ['pill-review', 'ti-clock'];
    const name = a.name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    const sch  = a.scholarship_name.replace(/</g, '&lt;').replace(/>/g, '&gt;');
    return `
      <tr>
        <td>
          <div class="avatar-wrap">
            <div class="avatar ${a.av}">${a.initials}</div>
            <div>
              <p class="av-name">${name}</p>
              <p class="av-date">${a.date}</p>
            </div>
          </div>
        </td>
        <td class="scholarship-name">${sch}</td>
        <td>
          <span class="pill ${pillClass}">
            <i class="ti ${pillIcon}"></i>
            ${a.status}
          </span>
        </td>
      </tr>`;
  }).join('');
}

// Init on load
filterApplicants();
</script>

</body>
</html>