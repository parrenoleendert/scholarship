<?php
session_start();
require_once("dbconfig.php");

$student_id = $_SESSION['id'];

// Check if the student already has an APPROVED application
// (If yes, the Apply button must be disabled for all scholarships)
$checkScholar = $con->prepare("
    SELECT 1
    FROM applications_form
    WHERE id = ?
      AND status = 'Approved'
    LIMIT 1
");
$checkScholar->bind_param("i", $student_id);
$checkScholar->execute();
$approvedScholar = $checkScholar->get_result()->num_rows > 0;

$query = "SELECT * FROM scholarship ORDER BY deadline ASC";
$result = mysqli_query($con, $query);

require_once("headers.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Scholarships</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #1e293b;
    }

    /* ── MAIN CONTAINER (Pushed down significantly lower to clear any overlapping top headers) ── */
    .main {
      flex: 1;
      padding: 140px 40px 40px 40px; /* Increased top padding significantly further down */
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

    /* ── Mini stat chips ── */
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
    .pill-approved { background: #dcfce7; color: #16a34a; }
    .pill-rejected { background: #fee2e2; color: #dc2626; }

    /* ── Action buttons ── */
    .actions { display: flex; gap: 8px; align-items: center; }

    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 12px;
      border-radius: 7px;
      text-decoration: none;
      transition: transform .15s, box-shadow .15s, background .15s;
      white-space: nowrap;
      border: 1px solid transparent;
      cursor: pointer;
      font-family: inherit;
    }
    .btn-action i { font-size: 13px; }

    .btn-view {
      background: #eff6ff;
      color: #1d4ed8;
      border-color: #bfdbfe;
    }
    .btn-view:hover {
      background: #1d4ed8;
      color: #fff;
      border-color: #1d4ed8;
      box-shadow: 0 4px 12px rgba(29,78,216,.22);
      transform: translateY(-1px);
    }

    .btn-apply {
      background: #dcfce7;
      color: #16a34a;
      border-color: #bbf7d0;
    }
    .btn-apply:hover {
      background: #16a34a;
      color: #fff;
      border-color: #16a34a;
      box-shadow: 0 4px 12px rgba(22,163,74,.22);
      transform: translateY(-1px);
    }

    .btn-disabled {
      background: #f1f5f9;
      color: #64748b;
      border-color: #e2e8f0;
      cursor: not-allowed;
      opacity: 0.75;
    }

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
      .main { margin-left: 0 !important; width: 100% !important; padding: 24px; }
    }
    @media (max-width: 768px) {
      .page-header { flex-direction: column; gap: 12px; align-items: flex-start; }
      .stat-chips { flex-wrap: wrap; }
      thead th:nth-child(3), td:nth-child(3) { display: none; }
    }
  </style>
</head>

<body>
<main class="main">

  <div class="page-header">
    <div>
      <h2>Available Scholarships</h2>
      <p>Apply for scholarships open to you</p>
    </div>

    <?php
        $all_rows = [];
        $total = 0; $open_count = 0; $closed_count = 0;
        if ($result) {
            while ($r = mysqli_fetch_assoc($result)) {
                $all_rows[] = $r;
                $total++;
                if ($r['status'] == 'Open') $open_count++;
                else $closed_count++;
            }
        }
    ?>
    <div class="stat-chips">
      <span class="chip chip-blue"><i class="ti ti-layer-group"></i> Total: <?= $total ?></span>
      <span class="chip chip-green"><i class="ti ti-circle-check"></i> Open: <?= $open_count ?></span>
      <span class="chip chip-red"><i class="ti ti-circle-x"></i> Closed: <?= $closed_count ?></span>
    </div>
  </div>

  <div class="toolbar">
    <div class="search-wrap">
      <i class="ti ti-search"></i>
      <input type="text" id="searchInput" placeholder="Search scholarships…" onkeyup="filterTable()">
    </div>
  </div>

  <div class="table-card">
    <div class="panel-head">
      <div class="panel-title">Scholarship Listings</div>
      <span class="result-count" id="rowCount"><?= $total ?> results</span>
    </div>

    <div class="table-responsive">
      <table id="scholTable">
        <thead>
          <tr>
            <th>Scholarship</th>
            <th>Provider</th>
            <th>Deadline</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($all_rows)): ?>
          <tr>
            <td colspan="6">
              <div class="empty-state">
                <i class="ti ti-school"></i>
                <strong>No scholarships available</strong>
                <p>There are no active entries at the moment.</p>
              </div>
            </td>
          </tr>
          <?php else: ?>
          <?php foreach ($all_rows as $row):
              $isOpen = $row['status'] == 'Open';

              $deadlineDisplay = '';
              if (!empty($row['deadline'])) {
                  $daysLeft = (strtotime($row['deadline']) - time()) / 86400;
                  $formatted = date("M d, Y", strtotime($row['deadline']));
                  if ($daysLeft < 0) {
                      $deadlineDisplay = $formatted . ' <span style="color:#64748b; font-size:12px;">(Expired)</span>';
                  } elseif ($daysLeft <= 7) {
                      $deadlineDisplay = $formatted . ' <span style="color:#dc2626; font-weight:600; font-size:12px;">(' . ceil($daysLeft) . ' days left)</span>';
                  } else {
                      $deadlineDisplay = $formatted;
                  }
              } else {
                  $deadlineDisplay = 'No Deadline';
              }
          ?>
          <tr>
            <td>
              <div class="schol-name"><?= htmlspecialchars($row['scholarship_name']) ?></div>
            </td>

            <td>
              <span style="color: #475569;"><i class="ti ti-building-community" style="font-size:13px; color:#3b82f6; margin-right:4px;"></i><?= htmlspecialchars($row['provider']) ?></span>
            </td>

            <td>
              <span style="font-size: 13px; font-weight: 500; white-space: nowrap;"><?= $deadlineDisplay ?></span>
            </td>

            <td style="font-weight: 700; color: #0f172a; white-space: nowrap;">
              ₱<?= number_format($row['amount'], 2) ?>
            </td>

            <td>
              <span class="pill <?= $isOpen ? 'pill-approved' : 'pill-rejected' ?>">
                <i class="ti <?= $isOpen ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
                <?= $isOpen ? 'Open' : 'Closed' ?>
              </span>
            </td>

            <td>
              <div class="actions">
                <?php if (!empty($row['scholarship_file'])): ?>
                <a href="uploads/<?= $row['scholarship_file'] ?>" target="_blank" class="btn-action btn-view">
                  <i class="ti ti-file-text"></i> Details
                </a>
                <?php else: ?>
                <button class="btn-action btn-disabled" disabled>
                  <i class="ti ti-file-off"></i> No File
                </button>
                <?php endif; ?>

                <?php if ($isOpen): ?>
                    <?php if($approvedScholar): ?>
                    <button class="btn-action btn-disabled" onclick="alert('You already have an approved scholarship. Only one scholarship is allowed per student.')">
                      <i class="ti ti-alert-triangle"></i> Already a Scholar
                    </button>
                    <?php else: ?>
                    <a href="application_form.php?id=<?= $row['sid'] ?>" class="btn-action btn-apply">
                      <i class="ti ti-send"></i> Apply
                    </a>
                    <?php endif; ?>
                <?php else: ?>
                <button class="btn-action btn-disabled" disabled>
                  <i class="ti ti-lock"></i> Closed
                </button>
                <?php endif; ?>
              </div>
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
    const rows  = document.querySelectorAll('#scholTable tbody tr');
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
  const currentFilename = window.location.pathname.split('/').pop() || 'schemes.php';
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