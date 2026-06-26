<?php
session_start();
require_once("dbconfig.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

$query = "SELECT a.*, s.scholarship_name 
          FROM applications_form a
          JOIN scholarship s ON a.sid = s.sid
          WHERE a.id = ?
          ORDER BY a.date_applied DESC";

$stmt = $con->prepare($query);

if(!$stmt){
    die("SQL Error: " . $con->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Collect rows + counts
$all_rows = [];
$counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while($row = $result->fetch_assoc()){
    $all_rows[] = $row;
    $s = strtolower($row['status']);
    if(isset($counts[$s])) $counts[$s]++;
}
$stmt->close();

require_once("headers.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application History</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #1e293b;
    }

    /* ── MAIN CONTAINER (Pushed down 140px lower to clear top header overlay) ── */
    .main {
      flex: 1;
      padding: 140px 40px 40px 40px; 
      width: calc(100% - 260px);
      margin-left: 260px;
      margin-right: auto;
      transition: all 0.3s ease;
    }

    /* ── Sidebar Selection Active State ── */
    .sidebar a:hover, .sidebar a.active,
    .nav-sidebar a:hover, .nav-sidebar a.active,
    .aside a:hover, .aside a.active,
    #sidebar a:hover, #sidebar a.active {
      background-color: #eff6ff !important;
      color: #0d6efd !important;
      font-weight: 600 !important;
      border-radius: 8px;
    }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 32px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e2e8f0;
    }
    .page-header h2 {
      font-size: 26px;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.5px;
    }
    .page-header p {
      font-size: 14px;
      color: #64748b;
      margin-top: 4px;
      font-weight: 400;
    }

    /* ── Mini stat chips (Synced with Schemes Style) ── */
    .stat-chips {
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 50px;
    }
    .chip i { font-size: 13px; }
    .chip-blue   { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .chip-yellow { background: #fef9c3; color: #ca8a04; border: 1px solid #fef08a; }
    .chip-green  { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .chip-red    { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
    }
    .search-wrap {
      position: relative;
      flex: 1;
      max-width: 320px;
    }
    .search-wrap i {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
      color: #94a3b8;
      pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      padding: 10px 16px 10px 42px;
      font-size: 14px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      background: #ffffff;
      outline: none;
      transition: all .2s;
    }
    .search-wrap input:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    .search-wrap input::placeholder { color: #94a3b8; }

    /* ── Table card panel ── */
    .table-card {
      background: #ffffff;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
      overflow: hidden;
      margin-bottom: 40px;
      width: 100%;
    }

    .panel-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 24px;
      border-bottom: 1px solid #e2e8f0;
      background: #f8fafc;
    }
    .panel-title {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
    }
    .result-count {
      font-size: 12px;
      color: #64748b;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 3px 11px;
      font-weight: 500;
    }

    .table-responsive {
      width: 100%;
      overflow-x: auto;
    }

    table { width: 100%; border-collapse: collapse; text-align: left; }

    thead th {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      color: #64748b;
      padding: 14px 24px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid #f1f5f9;
      transition: background .15s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }

    td {
      padding: 16px 24px;
      font-size: 14px;
      color: #334155;
      vertical-align: middle;
    }

    .id-badge {
      font-family: monospace;
      font-size: 12.5px;
      color: #2563eb;
      font-weight: 600;
      background: #eff6ff;
      border: 1px solid #bfdbfe;
      border-radius: 6px;
      padding: 3px 8px;
    }

    .schol-name { font-weight: 600; font-size: 14px; color: #0f172a; }

    /* ── Status pills ── */
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 50px;
      white-space: nowrap;
    }
    .pill i { font-size: 12px; }
    .pill-pending  { background: #fef9c3; color: #ca8a04; }
    .pill-approved { background: #dcfce7; color: #16a34a; }
    .pill-rejected { background: #fee2e2; color: #dc2626; }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 48px 24px;
      color: #94a3b8;
    }
    .empty-state i { font-size: 40px; display: block; color: #cbd5e1; margin-bottom: 12px; }
    .empty-state strong { font-size: 16px; color: #64748b; display: block; }
    .empty-state p { font-size: 13px; margin-top: 6px; }

    /* ── Responsive ── */
    @media (max-width: 992px) {
      .main { margin-left: 0 !important; width: 100% !important; padding: 24px; padding-top: 120px; }
    }
    @media (max-width: 768px) {
      .page-header { flex-direction: column; gap: 12px; align-items: flex-start; }
      .stat-chips { flex-wrap: wrap; }
    }
  </style>
</head>

<body>
<main class="main">

  <div class="page-header">
    <div>
      <h2>Application History</h2>
      <p>Review and monitor your submitted application statuses</p>
    </div>

    <div class="stat-chips">
      <span class="chip chip-blue"><i class="ti ti-layer-group"></i> Total: <?= count($all_rows) ?></span>
      <span class="chip chip-yellow"><i class="ti ti-clock"></i> Pending: <?= $counts['pending'] ?></span>
      <span class="chip chip-green"><i class="ti ti-circle-check"></i> Approved: <?= $counts['approved'] ?></span>
      <span class="chip chip-red"><i class="ti ti-circle-x"></i> Rejected: <?= $counts['rejected'] ?></span>
    </div>
  </div>

  <div class="toolbar">
    <div class="search-wrap">
      <i class="ti ti-search"></i>
      <input type="text" id="searchInput" placeholder="Search application listings…" onkeyup="filterTable()">
    </div>
  </div>

  <div class="table-card">
    <div class="panel-head">
      <div class="panel-title">My Application History Log</div>
      <span class="result-count" id="rowCount"><?= count($all_rows) ?> results</span>
    </div>

    <div class="table-responsive">
      <table id="historyTable">
        <thead>
          <tr>
            <th>School ID</th>
            <th>Applicant Name</th>
            <th>Scholarship Scheme</th>
            <th>Date Submitted</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_rows)): ?>
          <tr>
            <td colspan="5">
              <div class="empty-state">
                <i class="ti ti-folder-off"></i>
                <strong>No Applications Found</strong>
                <p>You have not submitted any scholarship application records yet.</p>
              </div>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($all_rows as $row): 
              $status = strtolower($row['status']);
              $pillClass = match($status) {
                  'approved' => 'pill-approved',
                  'rejected' => 'pill-rejected',
                  default    => 'pill-pending'
              };
              $iconClass = match($status) {
                  'approved' => 'ti-circle-check',
                  'rejected' => 'ti-circle-x',
                  default    => 'ti-clock'
              };
          ?>
          <tr>
            <td>
              <span class="id-badge"><?= htmlspecialchars($row['school_id']) ?></span>
            </td>

            <td>
              <span style="font-weight: 500; color: #0f172a;">
                <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
              </span>
            </td>

            <td>
              <div class="schol-name"><?= htmlspecialchars($row['scholarship_name']) ?></div>
            </td>

            <td>
              <span style="color: #64748b; font-size: 13.5px; white-space: nowrap;">
                <i class="ti ti-calendar" style="font-size:13px; color:#3b82f6; margin-right:4px;"></i><?= date("M d, Y", strtotime($row['date_applied'])) ?>
              </span>
            </td>

            <td>
              <span class="pill <?= $pillClass ?>">
                <i class="ti <?= $iconClass ?>"></i>
                <?= htmlspecialchars($row['status']) ?>
              </span>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase().trim();
    const rows  = document.querySelectorAll('#historyTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        if(row.querySelector('.empty-state')) return;
        const text = row.textContent.toLowerCase();
        const show = text.includes(input);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('rowCount').textContent = visible + ' results';
}

/* ── Dynamic Sidebar Class Active Script ── */
document.addEventListener('DOMContentLoaded', function() {
  const currentFilename = window.location.pathname.split('/').pop() || 'applicationhistory.php';
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