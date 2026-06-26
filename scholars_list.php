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
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #f8fafc;
      color: #1e293b;
    }

    .main {
      flex: 1;
      padding: 40px;
      width: calc(100% - 260px);
      margin-left: 260px;
      margin-right: auto;
      transition: all 0.3s ease;
    }

    /* ── Sidebar Selection Active State (Synced with Dashboard) ── */
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
    .chip-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
    .chip-red   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }

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

    .filter-select {
      padding: 10px 16px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 14px;
      background: #ffffff;
      color: #334155;
      outline: none;
      cursor: pointer;
      transition: border-color .2s;
    }
    .filter-select:focus { border-color: #3b82f6; }

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

    /* ── Avatar cell ── */
    .avatar-wrap { display: flex; align-items: center; gap: 12px; }
    .avatar {
      width: 36px; height: 36px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 12px; font-weight: 700; flex-shrink: 0;
    }
    .av-0 { background: #eff6ff; color: #1d4ed8; }
    .av-1 { background: #e0f2fe; color: #0369a1; }
    .av-2 { background: #fef3c7; color: #b45309; }
    .av-3 { background: #fee2e2; color: #b91c1c; }
    .av-4 { background: #f5f3ff; color: #5b21b6; }
    .student-name { font-weight: 600; font-size: 14px; color: #0f172a; }
    .student-id   { font-size: 12px; color: #64748b; margin-top: 2px; }

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
      padding: 48px 24px;
      color: #94a3b8;
    }
    .empty-state i { font-size: 40px; display: block; color: #cbd5e1; margin-bottom: 12px; }
    .empty-state strong { font-size: 16px; color: #64748b; display: block; }
    .empty-state p { font-size: 13px; margin-top: 6px; }

    /* ── Pagination Footer (Synced with Dashboard) ── */
    .pagination-footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 24px;
      border-top: 1px solid #e2e8f0;
      background: #ffffff;
    }
    .page-info { font-size: 13px; color: #64748b; font-weight: 500; }
    .pagination-buttons { display: flex; align-items: center; gap: 6px; }
    .page-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 600;
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      color: #334155;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.15s;
      gap: 4px;
    }
    .page-btn:hover:not(:disabled) { background-color: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
    .page-btn:disabled { opacity: 0.5; cursor: not-allowed; background-color: #f1f5f9; }
    .page-btn.active-num { background-color: #3b82f6; color: #ffffff; border-color: #3b82f6; }

    /* ── Responsive ── */
    @media (max-width: 992px) {
      .main { margin-left: 0 !important; width: 100% !important; padding: 24px; }
    }
    @media (max-width: 768px) {
      .page-header { flex-direction: column; gap: 12px; align-items: flex-start; }
      .stat-chips { flex-wrap: wrap; }
      thead th:nth-child(3), td:nth-child(3) { display: none; }
      .pagination-footer { flex-direction: column; gap: 12px; text-align: center; }
    }

    /* ===== DELETE CONFIRMATION MODAL ===== */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.45);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      opacity: 0;
      pointer-events: none;
      transition: opacity .2s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: all; }

    .modal-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(15, 23, 42, .18), 0 4px 16px rgba(15, 23, 42, .08);
      width: 100%;
      max-width: 400px;
      padding: 32px 28px 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transform: translateY(10px) scale(.97);
      transition: transform .22s ease, opacity .22s ease;
      opacity: 0;
    }
    .modal-overlay.active .modal-card { transform: translateY(0) scale(1); opacity: 1; }
    .modal-icon-wrap {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: #fff1f2;
      border: 1.5px solid #fecdd3;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
    }
    .modal-icon-wrap i { font-size: 28px; color: #be123c; }
    .modal-title { font-size: 17px; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
    .modal-body { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
    .modal-body strong { color: #1a1a2e; font-weight: 600; }
    .modal-actions { display: flex; gap: 10px; width: 100%; }
    .modal-btn {
      flex: 1; padding: 10px 0; border-radius: 9px; font-size: 13px; font-weight: 600;
      border: 1px solid transparent; cursor: pointer; font-family: inherit; transition: all .15s;
    }
    .modal-btn:active { transform: scale(.97); }
    .modal-btn-cancel { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .modal-btn-cancel:hover { background: #f1f5f9; border-color: #cbd5e1; }
    .modal-btn-confirm { background: #be123c; color: #fff; border-color: #be123c; }
    .modal-btn-confirm:hover { background: #9f1239; box-shadow: 0 4px 14px rgba(190,18,60,.30); }
  </style>
</head>

<body>
<main class="main">

  <div class="page-header">
    <div>
      <h2>Applicant Management</h2>
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

  <div class="table-card">
    <div class="table-responsive">
      <table id="appTable">
        <thead>
          <tr>
            <th>Applicant Profile</th>
            <th>Course</th>
            <th>Year / Section</th>
            <th>Scholarship Program</th>
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
            <td>
              <div class="avatar-wrap">
                <div class="avatar <?= $av_class ?>"><?= $initials ?></div>
                <div>
                  <div class="student-name"><?= $full_name ?></div>
                  <div class="student-id"><?= $school_id ?></div>
                </div>
              </div>
            </td>

            <td><span class="tag"><?= $course ?></span></td>

            <td><span class="tag"><?= $year_sec ?></span></td>

            <td style="font-weight: 500; color: #475569;"><?= $scholarship ?></td>

            <td>
              <span class="pill <?= $pill_class ?>">
                <i class="ti <?= $pill_icon ?>"></i>
                <?= htmlspecialchars($status) ?>
              </span>
            </td>

            <td>
              <div class="actions">
                <a href="view_applicantlist.php?id=<?= (int)$row['id'] ?>" class="btn-action btn-view">
                  <i class="ti ti-eye"></i> View
                </a>
                <a href="edit_applicantlist.php?id=<?= (int)$row['aid'] ?>" class="btn-action btn-edit">
                  <i class="ti ti-pencil"></i> Edit
                </a>
                <button
                  class="btn-action btn-delete"
                  onclick="openDeleteModal('delete_applicantlist.php?id=<?= (int)$row['id'] ?>', '<?= addslashes($full_name) ?>')"
                >
                  <i class="ti ti-trash"></i> Delete
                </button>
              </div>
            </td>
          </tr>
          <?php endwhile; endif; ?>
          
          <tr class="empty-row-state" style="display: none;">
            <td colspan="6">
              <div class="empty-state">
                <i class="ti ti-inbox"></i>
                <strong>No records found</strong>
                <p>Try adjusting your search or filter options.</p>
              </div>
            </td>
          </tr>

        </tbody>
      </table>
    </div>

    <div class="pagination-footer" id="paginationWrapper">
      <div class="page-info" id="pageInfoText">Showing 0 to 0 of 0 entries</div>
      <div class="pagination-buttons" id="paginationControls"></div>
    </div>
  </div>

</main>

<div class="modal-overlay" id="deleteModal">
  <div class="modal-card">
    <div class="modal-icon-wrap">
      <i class="ti ti-trash"></i>
    </div>
    <div class="modal-title">Delete Record</div>
    <div class="modal-body">
      Are you sure you want to delete <strong id="modalName"></strong>?
      <br>This action cannot be undone.
    </div>
    <div class="modal-actions">
      <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
      <a href="#" id="modalConfirmBtn" class="modal-btn modal-btn-confirm">Delete</a>
    </div>
  </div>
</div>

<script>
  /* ── Delete modal ── */
  const deleteModal     = document.getElementById('deleteModal');
  const modalName       = document.getElementById('modalName');
  const modalConfirmBtn = document.getElementById('modalConfirmBtn');

  function openDeleteModal(url, name) {
    modalName.textContent       = name;
    modalConfirmBtn.href        = url;
    deleteModal.classList.add('active');
  }

  function closeDeleteModal() {
    deleteModal.classList.remove('active');
  }

  deleteModal.addEventListener('click', function(e) {
    if (e.target === deleteModal) closeDeleteModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
  });

  /* ── Client-Side Pagination & Dynamic Filter Logic (Dashboard Port) ── */
  let currentPage = 1;
  const itemsPerPage = 5; 
  let filteredRows = [];

  const tbody = document.querySelector('#appTable tbody');
  const totalOriginalRows = Array.from(tbody.querySelectorAll('tr:not(.empty-row-state)'));
  const emptyRowTemplate = tbody.querySelector('.empty-row-state');

  function filterAndPaginate() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();
    const statusFilterValue = document.getElementById('statusFilter').value.toLowerCase();

    // Perform Combo filtering logic (Text Search + Dropdown Selection)
    filteredRows = totalOriginalRows.filter(row => {
      const textMatch = !q || row.textContent.toLowerCase().includes(q);
      const rowStatus = row.getAttribute('data-status') || '';
      const statusMatch = !statusFilterValue || rowStatus === statusFilterValue;
      return textMatch && statusMatch;
    });

    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Toggle Empty State row block visibility
    if (totalItems === 0) {
      totalOriginalRows.forEach(r => r.style.display = 'none');
      if (emptyRowTemplate) emptyRowTemplate.style.display = '';
      document.getElementById('pageInfoText').textContent = "Showing 0 to 0 of 0 entries";
      document.getElementById('paginationControls').innerHTML = '';
      return;
    }

    if (emptyRowTemplate) emptyRowTemplate.style.display = 'none';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

    // Toggle visible row elements
    totalOriginalRows.forEach(row => row.style.display = 'none');
    filteredRows.slice(startIndex, endIndex).forEach(row => row.style.display = '');

    // Update entries metadata description text
    document.getElementById('pageInfoText').textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalItems} entries`;
    renderPaginationFooterControls(totalPages);
  }

  function renderPaginationFooterControls(totalPages) {
    const container = document.getElementById('paginationControls');
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

  window.changePage = function(p) {
    currentPage = p;
    filterAndPaginate();
  };

  document.getElementById('searchInput').addEventListener('input', function() {
    currentPage = 1;
    filterAndPaginate();
  });

  document.getElementById('statusFilter').addEventListener('change', function() {
    currentPage = 1;
    filterAndPaginate();
  });

  /* ── Dynamic Sidebar Class Active Script (Dashboard Copy) ── */
  document.addEventListener('DOMContentLoaded', function() {
    const currentFilename = window.location.pathname.split('/').pop() || 'scholars_list.php';
    const sidebarLinks = document.querySelectorAll('.sidebar a, .nav-sidebar a, .aside a, #sidebar a');
    
    sidebarLinks.forEach(link => {
      const hrefFile = link.getAttribute('href');
      if (hrefFile && currentFilename.includes(hrefFile)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });

    // Run pagination initialization layout routine
    filterAndPaginate();
  });
</script>

</body>
</html>