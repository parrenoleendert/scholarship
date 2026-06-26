<?php
require_once("dbconfig.php");
require_once("header.php");

$search = "";

/* ===== SQL QUERY PREPARATION ===== */
if(isset($_GET['search']) && $_GET['search'] != ""){
    $search = trim($_GET['search']);
    $search_param = "%".$search."%";

    $stmt = $con->prepare("
        SELECT * FROM scholarship
        WHERE scholarship_name LIKE ?
        OR provider LIKE ?
        ORDER BY sid DESC
    ");
    $stmt->bind_param("ss", $search_param, $search_param);
}else{
    $stmt = $con->prepare("
        SELECT * FROM scholarship
        ORDER BY sid DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
$total_rows = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scholarship Management</title>
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

/* ── Sidebar Active Class Accent Rule (Synced with Dashboard Overview) ── */
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

/* ===== PRIMARY ACTION CALLOUT ===== */
.btn-add {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #3b82f6;
    color: #fff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: 10px;
    transition: all 0.15s ease;
    white-space: nowrap;
    border: 1px solid #2563eb;
}

.btn-add:hover {
    background: #2563eb;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    transform: translateY(-1px);
}

.btn-add i { font-size: 16px; }

/* ===== FILTER TOOLBAR ===== */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 16px;
}

.result-count {
    font-size: 12px;
    font-weight: 600;
    color: #d97706;
    background: #fef3c7;
    padding: 6px 14px;
    border-radius: 50px;
    border: 1px solid #fde68a;
}

/* ===== SEARCH ELEMENT FORM ===== */
.search-wrapper {
    position: relative;
    max-width: 320px;
    width: 100%;
}

.search-wrapper i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 10px 75px 10px 42px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    outline: none;
    transition: all 0.2s;
}

.search-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.search-input::placeholder { color: #94a3b8; }

.search-btn {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: #3b82f6;
    border: none;
    color: #fff;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.15s;
}

.search-btn:hover { background: #2563eb; }

/* ===== COMPACT DATA CARD BLOCK ===== */
.table-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
    overflow: hidden;
    margin-bottom: 40px;
    width: 100%;
}

.table-scroll { width: 100%; overflow-x: auto; }

table { width: 100%; border-collapse: collapse; text-align: left; }

/* ===== CELL SCALING CORRECTIONS ===== */
thead tr {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

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

tbody tr { transition: background 0.15s; }
tbody tr:hover { background: #f8fafc; }

.scholarship-name {
    font-weight: 600;
    font-size: 14px;
    color: #0f172a;
}

.provider-name {
    font-size: 14px;
    color: #475569;
    font-weight: 500;
}

.amount-cell {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
}

.deadline-cell {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #64748b;
}

.deadline-cell i { font-size: 15px; color: #94a3b8; }

.no-deadline {
    font-size: 12px;
    color: #94a3b8;
    font-style: italic;
}

/* ===== BADGE STATUS STYLING ===== */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-dot { width: 6px; height: 6px; border-radius: 50%; }

.badge-open { background: #dcfce7; color: #16a34a; }
.badge-open .badge-dot { background: #16a34a; }

.badge-close { background: #fee2e2; color: #dc2626; }
.badge-close .badge-dot { background: #dc2626; }

/* ===== COMPACT ACTION UTILITIES ===== */
.actions-cell { display: flex; align-items: center; gap: 8px; }

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 7px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
    border: 1px solid transparent;
    cursor: pointer;
    background: none;
}
.action-btn i { font-size: 13px; }

.btn-file {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}
.btn-file:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(29, 78, 216, 0.22);
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
    box-shadow: 0 4px 12px rgba(202, 138, 4, 0.22);
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
    box-shadow: 0 4px 12px rgba(190, 18, 60, 0.22);
    transform: translateY(-1px);
}

.no-file { font-size: 12px; color: #94a3b8; font-style: italic; }

/* ===== INTERACTIVE EMPTY FALLBACK UNIT ===== */
.empty-state { text-align: center; padding: 48px 24px; color: #94a3b8; }
.empty-icon { display: block; font-size: 40px; color: #cbd5e1; margin-bottom: 12px; }
.empty-title { font-size: 16px; font-weight: 600; color: #64748b; display: block; }
.empty-desc { font-size: 13px; margin-top: 6px; }

/* ===== PAGINATION INTERACTION PANEL (Standard Dashboard Sync) ===== */
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

@media (max-width: 992px) {
    .page-wrapper { margin-left: 0 !important; width: 100% !important; padding: 24px; }
}
@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; gap: 14px; }
    .toolbar { flex-direction: column; align-items: flex-start; }
    .search-wrapper { max-width: 100%; }
    .pagination-footer { flex-direction: column; gap: 12px; text-align: center; }
}

/* ===== MODAL PORT CONFIRMATION DIALOG ===== */
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

<div class="modal-overlay" id="deleteModal">
    <div class="modal-card">
        <div class="modal-icon-wrap">
            <i class="ti ti-trash"></i>
        </div>
        <div class="modal-title">Delete Scholarship</div>
        <div class="modal-body">
            Are you sure you want to delete <strong id="modalName"></strong>?
            <br/>This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a href="#" id="modalConfirmBtn" class="modal-btn modal-btn-confirm">Delete</a>
        </div>
    </div>
</div>

<div class="page-wrapper">

    <div class="page-header">
        <div class="page-title-group">
            <h1>Scholarship Management</h1>
            <p class="page-subtitle">Browse, search, and manage all available scholarship programs.</p>
        </div>
        <a href="add_scholarship.php" class="btn-add">
            <i class="ti ti-plus"></i>
            Add Scholarship
        </a>
    </div>

    <div class="toolbar">
        <div>
            <span class="result-count" id="countBadge">
                <?= $total_rows ?> <?= $total_rows == 1 ? 'record' : 'records' ?>
            </span>
        </div>

        <form method="GET" class="search-wrapper" onsubmit="return false;">
            <i class="ti ti-search"></i>
            <input
                class="search-input"
                type="text"
                id="searchInput"
                placeholder="Search by name or provider…"
                value="<?= htmlspecialchars($search); ?>"
                autocomplete="off">
            <button type="button" class="search-btn" onclick="resetPageAndFilter()">Go</button>
        </form>
    </div>

    <div class="table-card">
        <div class="table-scroll">
        <table id="scholarshipTable">
            <thead>
                <tr>
                    <th>Scholarship</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Requirement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if($total_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <span class="scholarship-name">
                        <?= htmlspecialchars($row['scholarship_name']); ?>
                    </span>
                </td>

                <td><span class="provider-name"><?= htmlspecialchars($row['provider']); ?></span></td>

                <td>
                    <span class="amount-cell">
                        &#8369;<?= number_format($row['amount'], 2); ?>
                    </span>
                </td>

                <td>
                    <?php if(!empty($row['deadline'])): ?>
                        <div class="deadline-cell">
                            <i class="ti ti-calendar-event"></i>
                            <?= date("M d, Y", strtotime($row['deadline'])); ?>
                        </div>
                    <?php else: ?>
                        <span class="no-deadline">No deadline</span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(strtolower($row['status']) == "open"): ?>
                        <span class="badge badge-open">
                            <span class="badge-dot"></span> Open
                        </span>
                    <?php else: ?>
                        <span class="badge badge-close">
                            <span class="badge-dot"></span> Closed
                        </span>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if(!empty($row['scholarship_file'])): ?>
                        <a href="uploads/<?= $row['scholarship_file']; ?>" target="_blank" class="action-btn btn-file">
                            <i class="ti ti-file-text"></i> View File
                        </a>
                    <?php else: ?>
                        <span class="no-file">No file</span>
                    <?php endif; ?>
                </td>

                <td>
                    <div class="actions-cell">
                        <a href="edit_scholarship.php?sid=<?= $row['sid']; ?>" class="action-btn btn-edit">
                            <i class="ti ti-edit"></i> Edit
                        </a>
                        <a href="#"
                           class="action-btn btn-delete"
                           data-delete-url="delete_scholarship.php?sid=<?= $row['sid']; ?>"
                           data-delete-name="<?= htmlspecialchars($row['scholarship_name']); ?>">
                            <i class="ti ti-trash"></i> Delete
                        </a>
                    </div>
                </td>
            </tr>
            <?php endwhile; endif; ?>

            <tr class="empty-row-state" style="display: none;">
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="ti ti-school"></i></div>
                        <p class="empty-title">No scholarships found</p>
                        <p class="empty-desc">No entries match your filtering parameters.</p>
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
</div>

<script>
  /* ── Delete Dialogue Overlay Engine ── */
  const deleteModal = document.getElementById('deleteModal');
  const modalName = document.getElementById('modalName');
  const modalConfirmBtn = document.getElementById('modalConfirmBtn');

  function closeDeleteModal(){ deleteModal.classList.remove('active'); }

  document.querySelectorAll('[data-delete-url]').forEach(btn => {
    btn.addEventListener('click', function(e){
      e.preventDefault();
      modalName.textContent = this.getAttribute('data-delete-name') || 'this scholarship';
      modalConfirmBtn.href = this.getAttribute('data-delete-url');
      deleteModal.classList.add('active');
    });
  });

  deleteModal.addEventListener('click', function(e){ if(e.target === deleteModal) closeDeleteModal(); });
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeDeleteModal(); });

  /* ── Client Pagination Pagination & Logic Router (Dashboard Blueprint) ── */
  let currentPage = 1;
  const itemsPerPage = 5; 
  let filteredRows = [];

  const tbody = document.querySelector('#scholarshipTable tbody');
  const totalOriginalRows = Array.from(tbody.querySelectorAll('tr:not(.empty-row-state)'));
  const emptyRowTemplate = tbody.querySelector('.empty-row-state');

  function filterAndPaginate() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();

    // Query text selection evaluation loop
    filteredRows = totalOriginalRows.filter(row => {
      return !q || row.textContent.toLowerCase().includes(q);
    });

    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Direct result feedback updates
    document.getElementById('countBadge').textContent = `${totalItems} ${totalItems === 1 ? 'record' : 'records'}`;

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

    totalOriginalRows.forEach(row => row.style.display = 'none');
    filteredRows.slice(startIndex, endIndex).forEach(row => row.style.display = '');

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

  function resetPageAndFilter() {
    currentPage = 1;
    filterAndPaginate();
  }

  document.getElementById('searchInput').addEventListener('input', resetPageAndFilter);

  /* ── Dynamic Automated Navigation Highlight Trigger Routine ── */
  document.addEventListener('DOMContentLoaded', function() {
    const currentFilename = window.location.pathname.split('/').pop() || 'scholarship.php';
    const sidebarLinks = document.querySelectorAll('.sidebar a, .nav-sidebar a, .aside a, #sidebar a');
    
    sidebarLinks.forEach(link => {
      const hrefFile = link.getAttribute('href');
      if (hrefFile && currentFilename.includes(hrefFile)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });

    filterAndPaginate();
  });
</script>

</body>
</html>