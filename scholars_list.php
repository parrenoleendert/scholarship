<?php
require_once("dbconfig.php");
require_once("header.php");

$search = "";

/* ===== BASE QUERY ===== */
$sql = "SELECT a.*, s.scholarship_name
        FROM applications_form a
        INNER JOIN scholarship s ON a.sid = s.sid
        WHERE a.status IN ('Approved','Rejected')";

$params = [];
$types  = "";

/* ===== SEARCH ===== */
if (!empty($_GET['search'])) {
    $search  = trim($_GET['search']);
    $sql    .= " AND CONCAT(a.first_name,' ',a.last_name) LIKE ?";
    $params[] = "%" . $search . "%";
    $types   .= "s";
}

$sql .= " ORDER BY a.date_applied DESC";

$stmt = $con->prepare($sql);
if (!$stmt) die("SQL Error: " . $con->error);
if (!empty($params)) $stmt->bind_param($types, ...$params);

$stmt->execute();
$result      = $stmt->get_result();
$total_rows  = $result->num_rows;

/* ===== COUNT PER STATUS ===== */
$count_sql  = "SELECT status, COUNT(*) as cnt FROM applications_form
               WHERE status IN ('Approved','Rejected') GROUP BY status";
$count_res  = $con->query($count_sql);
$counts     = ['Approved' => 0, 'Rejected' => 0];
while ($cr = $count_res->fetch_assoc()) $counts[$cr['status']] = $cr['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application List – Scholarship Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f4f6f9;
      color: #1a1a2e;
    }

    .main {
      margin-left: 260px;
      padding: 32px 36px;
      min-height: 100vh;
    }

    /* ── Page header ── */
    .page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e2e8f0;
    }
    .page-header h2 {
      font-size: 22px;
      font-weight: 600;
      color: #1a1a2e;
    }
    .page-header p {
      font-size: 13px;
      color: #64748b;
      margin-top: 4px;
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
      border-radius: 20px;
    }
    .chip i { font-size: 13px; }
    .chip-green { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .chip-red   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    /* ── Toolbar ── */
    .toolbar {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }
    .search-wrap {
      position: relative;
      flex: 1;
      max-width: 340px;
    }
    .search-wrap i {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
      color: #94a3b8;
      pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      padding: 9px 12px 9px 36px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 13px;
      background: #fff;
      color: #1a1a2e;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .search-wrap input:focus {
      border-color: #93c5fd;
      box-shadow: 0 0 0 3px rgba(147,197,253,.25);
    }
    .search-wrap input::placeholder { color: #94a3b8; }

    .filter-select {
      padding: 9px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      font-size: 13px;
      background: #fff;
      color: #334155;
      outline: none;
      cursor: pointer;
      transition: border-color .2s;
    }
    .filter-select:focus { border-color: #93c5fd; }

    /* ── Table card ── */
    .table-card {
      background: #ffffff;
      border-radius: 14px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
    }

    table { width: 100%; border-collapse: collapse; }

    thead th {
      text-align: left;
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .6px;
      color: #94a3b8;
      padding: 13px 18px;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      white-space: nowrap;
    }

    tbody tr {
      border-bottom: 1px solid #f1f5f9;
      transition: background .12s;
    }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #f8fafc; }

    td {
      padding: 14px 18px;
      font-size: 13px;
      color: #334155;
      vertical-align: middle;
    }

    /* ── Avatar cell ── */
    .avatar-wrap { display: flex; align-items: center; gap: 10px; }
    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 11px; font-weight: 700; flex-shrink: 0;
    }
    .av-0 { background: #dbeafe; color: #1d4ed8; }
    .av-1 { background: #ccfbf1; color: #0f766e; }
    .av-2 { background: #fef3c7; color: #b45309; }
    .av-3 { background: #fee2e2; color: #b91c1c; }
    .av-4 { background: #ede9fe; color: #6d28d9; }
    .student-name { font-weight: 500; color: #1a1a2e; }
    .student-id   { font-size: 11px; color: #94a3b8; margin-top: 2px; }

    /* ── Tags ── */
    .tag {
      display: inline-block;
      background: #f1f5f9;
      color: #475569;
      font-size: 11px;
      font-weight: 500;
      padding: 3px 9px;
      border-radius: 6px;
    }

    /* ── Status pills ── */
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 20px;
      white-space: nowrap;
    }
    .pill i { font-size: 12px; }
    .pill-approved { background: #dcfce7; color: #15803d; }
    .pill-rejected { background: #fee2e2; color: #b91c1c; }

    /* ── Action buttons ── */
    .actions { display: flex; gap: 7px; align-items: center; }

    .btn-action {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 11px;
      border-radius: 7px;
      text-decoration: none;
      transition: transform .15s, box-shadow .15s, background .15s;
      white-space: nowrap;
      border: 1px solid transparent;
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

    .btn-edit {
      background: #fefce8;
      color: #a16207;
      border-color: #fde68a;
    }
    .btn-edit:hover {
      background: #ca8a04;
      color: #fff;
      border-color: #ca8a04;
      box-shadow: 0 4px 12px rgba(202,138,4,.22);
      transform: translateY(-1px);
    }

    .btn-delete {
      background: #fff1f2;
      color: #be123c;
      border-color: #fecdd3;
    }
    .btn-delete:hover {
      background: #be123c;
      color: #fff;
      border-color: #be123c;
      box-shadow: 0 4px 12px rgba(190,18,60,.22);
      transform: translateY(-1px);
    }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #94a3b8;
    }
    .empty-state i { font-size: 44px; display: block; color: #cbd5e1; margin-bottom: 12px; }
    .empty-state strong { font-size: 16px; color: #64748b; display: block; }
    .empty-state p { font-size: 13px; margin-top: 6px; }

    /* ── Table footer ── */
    .table-footer {
      padding: 12px 18px;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      font-size: 12px;
      color: #94a3b8;
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      .main { margin-left: 0; padding: 20px 16px; }
      .page-header { flex-direction: column; gap: 12px; }
      .stat-chips { flex-wrap: wrap; }
      thead th:nth-child(4), td:nth-child(4) { display: none; }
    }
  </style>
</head>

<body>
<main class="main">

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h2>Application List</h2>
      <p>Approved and rejected scholarship applications</p>
    </div>
    <div class="stat-chips">
      <span class="chip chip-green">
        <i class="ti ti-circle-check"></i>
        <?= $counts['Approved'] ?> Approved
      </span>
      <span class="chip chip-red">
        <i class="ti ti-circle-x"></i>
        <?= $counts['Rejected'] ?> Rejected
      </span>
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <i class="ti ti-search"></i>
      <input
        type="text"
        id="searchInput"
        placeholder="Search by name, ID, or scholarship…"
        value="<?= htmlspecialchars($search) ?>"
      >
    </div>
    <select class="filter-select" id="statusFilter">
      <option value="">All statuses</option>
      <option value="approved">Approved</option>
      <option value="rejected">Rejected</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <table id="appTable">
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Course</th>
          <th>Year / Section</th>
          <th>Scholarship</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>

        <?php if ($total_rows > 0):
          $av_classes = ['av-0','av-1','av-2','av-3','av-4'];
          $i = 0;
          while ($row = $result->fetch_assoc()):
            $full_name   = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
            $school_id   = htmlspecialchars($row['school_id']);
            $course      = htmlspecialchars($row['course']);
            $year_sec    = htmlspecialchars($row['year_section']);
            $scholarship = htmlspecialchars($row['scholarship_name']);
            $status      = $row['status'];
            $status_low  = strtolower($status);
            $pill_class  = $status_low === 'approved' ? 'pill-approved' : 'pill-rejected';
            $pill_icon   = $status_low === 'approved' ? 'ti-circle-check' : 'ti-circle-x';

            $parts    = explode(' ', trim($full_name));
            $initials = '';
            foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
            $initials = substr($initials, 0, 2);
            $av_class = $av_classes[$i % count($av_classes)];
            $i++;
        ?>
        <tr data-status="<?= $status_low ?>">

          <!-- Applicant -->
          <td>
            <div class="avatar-wrap">
              <div class="avatar <?= $av_class ?>"><?= $initials ?></div>
              <div>
                <div class="student-name"><?= $full_name ?></div>
                <div class="student-id"><?= $school_id ?></div>
              </div>
            </div>
          </td>

          <!-- Course -->
          <td><span class="tag"><?= $course ?></span></td>

          <!-- Year / Section -->
          <td><span class="tag"><?= $year_sec ?></span></td>

          <!-- Scholarship -->
          <td><?= $scholarship ?></td>

          <!-- Status -->
          <td>
            <span class="pill <?= $pill_class ?>">
              <i class="ti <?= $pill_icon ?>"></i>
              <?= htmlspecialchars($status) ?>
            </span>
          </td>

          <!-- Actions -->
          <td>
            <div class="actions">
              <a href="view_applicantlist.php?id=<?= (int)$row['id'] ?>" class="btn-action btn-view">
                <i class="ti ti-eye"></i> View
              </a>
              <a href="edit_applicantlist.php?id=<?= (int)$row['aid'] ?>" class="btn-action btn-edit">
                <i class="ti ti-pencil"></i> Edit
              </a>
              <a href="delete_applicantlist.php?id=<?= (int)$row['id'] ?>"
                 class="btn-action btn-delete"
                 onclick="return confirm('Are you sure you want to delete this record?')">
                <i class="ti ti-trash"></i> Delete
              </a>
            </div>
          </td>

        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="6">
            <div class="empty-state">
              <i class="ti ti-inbox"></i>
              <strong>No records found</strong>
              <p>Try adjusting your search or filter.</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>

      </tbody>
    </table>

    <?php if ($total_rows > 0): ?>
    <div class="table-footer" id="rowCount">
      Showing <?= $total_rows ?> record<?= $total_rows !== 1 ? 's' : '' ?>
    </div>
    <?php endif; ?>
  </div>

</main>

<script>
  const searchInput  = document.getElementById('searchInput');
  const statusFilter = document.getElementById('statusFilter');
  const rows         = document.querySelectorAll('#appTable tbody tr[data-status]');
  const rowCount     = document.getElementById('rowCount');

  function filterRows() {
    const q      = searchInput.value.toLowerCase();
    const status = statusFilter.value.toLowerCase();
    let visible  = 0;

    rows.forEach(row => {
      const text     = row.textContent.toLowerCase();
      const rowStatus = row.dataset.status;
      const matchQ   = !q || text.includes(q);
      const matchS   = !status || rowStatus === status;
      const show     = matchQ && matchS;
      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (rowCount) {
      rowCount.textContent = `Showing ${visible} record${visible !== 1 ? 's' : ''}`;
    }
  }

  searchInput.addEventListener('input',  filterRows);
  statusFilter.addEventListener('change', filterRows);
</script>

</body>
</html>