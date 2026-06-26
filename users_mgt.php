<?php
session_start();
require_once("dbconfig.php");

if(!isset($_SESSION['adminid'])){
    header("Location: adminlogin.php");
    exit();
}

$search = '';

if(isset($_GET['search'])){
    $search = trim($_GET['search']);
    $query = $con->prepare("
        SELECT * FROM students 
        WHERE CONCAT(first_name, ' ', last_name) LIKE ?
        OR student_id LIKE ?
        ORDER BY student_id ASC
    ");
    $search_param = "%".$search."%";
    $query->bind_param("ss", $search_param, $search_param);
} else {
    $query = $con->prepare("SELECT * FROM students ORDER BY student_id ASC");
}

$query->execute();
$result = $query->get_result();
$total_rows = $result->num_rows;
?>

<?php require_once("header.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Management – Scholarship System</title>
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

/* ===== BADGE COUNT INDICATOR ===== */
.result-count {
    font-size: 12px;
    font-weight: 600;
    color: #d97706;
    background: #fef3c7;
    padding: 6px 14px;
    border-radius: 50px;
    border: 1px solid #fde68a;
}

/* ===== FILTER TOOLBAR ===== */
.toolbar {
    display: flex;
    align-items: center;
    margin-bottom: 16px;
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
    padding: 10px 16px 10px 42px;
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

/* ===== COMPACT DATA CARD PANEL ===== */
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
.student-id   { font-size: 13px; font-weight: 600; color: #475569; }

/* ── Pills and Metadata Tags ── */
.tag {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 500;
    padding: 3px 9px;
    border-radius: 6px;
    white-space: nowrap;
}

.address-text {
    font-size: 14px;
    color: #475569;
    max-width: 240px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

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
    font-family: inherit;
}
.action-btn i { font-size: 13px; }

.btn-view {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}
.btn-view:hover {
    background: #1d4ed8;
    color: #fff;
    border-color: #1d4ed8;
    box-shadow: 0 4px 12px rgba(29, 78, 216, 0.22);
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

/* ===== INTERACTIVE EMPTY FALLBACK UNIT ===== */
.empty-state { text-align: center; padding: 48px 24px; color: #94a3b8; }
.empty-icon { display: block; font-size: 40px; color: #cbd5e1; margin-bottom: 12px; }
.empty-title { font-size: 16px; font-weight: 600; color: #64748b; display: block; }
.empty-desc { font-size: 13px; margin-top: 6px; }

/* ===== PAGINATION INTERACTION PANEL ===== */
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
    thead th:nth-child(5), td:nth-child(5) { display: none; }
    .pagination-footer { flex-direction: column; gap: 12px; text-align: center; }
}

/* ===== MODAL CONFIGURATION OVERLAY ===== */
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
        <div class="modal-title">Delete Student Account</div>
        <div class="modal-body">
            Are you sure you want to delete the record of <strong id="modalName"></strong>?
            <br/>This action will erase their record permanently.
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
            <h1>User Management</h1>
            <p class="page-subtitle">Manage system user access, parameters, and applicant profiles.</p>
        </div>
        <span class="result-count" id="countBadge">
            <?= $total_rows ?> <?= $total_rows == 1 ? 'student' : 'students' ?>
        </span>
    </div>

    <div class="toolbar">
        <div class="search-wrapper">
            <i class="ti ti-search"></i>
            <input
                class="search-input"
                type="text"
                id="searchInput"
                placeholder="Search by ID or name…"
                value="<?= htmlspecialchars($search); ?>"
                autocomplete="off">
        </div>
    </div>

    <div class="table-card">
        <div class="table-scroll">
        <table id="userTable">
            <thead>
                <tr>
                    <th>School ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year & Section</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if($total_rows > 0): 
                $av_classes = ['av-0','av-1','av-2','av-3','av-4'];
                $i = 0;
                while($row = $result->fetch_assoc()): 
                    $full_name = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                    $school_id = htmlspecialchars($row['student_id']);
                    $course    = htmlspecialchars($row['course']);
                    $yr_sec    = htmlspecialchars($row['year_section']);
                    $address   = htmlspecialchars($row['address']);

                    $parts    = explode(' ', trim($full_name));
                    $initials = '';
                    foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
                    $initials = substr($initials, 0, 2);
                    $av_class = $av_classes[$i % count($av_classes)];
                    $i++;
            ?>
            <tr>
                <td><span class="student-id"><?= $school_id; ?></span></td>

                <td>
                    <div class="avatar-wrap">
                        <div class="avatar <?= $av_class; ?>"><?= $initials; ?></div>
                        <div class="student-name"><?= $full_name; ?></div>
                    </div>
                </td>

                <td><span class="tag"><?= $course; ?></span></td>

                <td><span class="tag"><?= $yr_sec; ?></span></td>

                <td><div class="address-text" title="<?= $address; ?>"><?= $address; ?></div></td>

                <td>
                    <div class="actions-cell">
                        <a href="view_usermgt.php?id=<?= urlencode($row['student_id']); ?>" class="action-btn btn-view">
                            <i class="ti ti-eye"></i> View
                        </a>
                        <button 
                            class="action-btn btn-delete" 
                            onclick="openDeleteModal('delete_usermgt.php?id=<?= $row['student_id']; ?>', '<?= addslashes($full_name); ?>')">
                            <i class="ti ti-trash"></i> Delete
                        </button>
                    </div>
                </td>
            </tr>
            <?php endwhile; endif; ?>

            <tr class="empty-row-state" style="display: none;">
                <td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon"><i class="ti ti-user-off"></i></div>
                        <p class="empty-title">No matching students found</p>
                        <p class="empty-desc">Try altering your structural parameters or input query text.</p>
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
  /* ── Delete Modal Box Core Engines ── */
  const deleteModal = document.getElementById('deleteModal');
  const modalName = document.getElementById('modalName');
  const modalConfirmBtn = document.getElementById('modalConfirmBtn');

  function openDeleteModal(url, name) {
    modalName.textContent = name;
    modalConfirmBtn.href = url;
    deleteModal.classList.add('active');
  }

  function closeDeleteModal() { deleteModal.classList.remove('active'); }

  deleteModal.addEventListener('click', function(e) { if(e.target === deleteModal) closeDeleteModal(); });
  document.addEventListener('keydown', function(e) { if(e.key === 'Escape') closeDeleteModal(); });

  /* ── Client Side Filtering & Dynamic Pagination Routing ── */
  let currentPage = 1;
  const itemsPerPage = 5; 
  let filteredRows = [];

  const tbody = document.querySelector('#userTable tbody');
  const totalOriginalRows = Array.from(tbody.querySelectorAll('tr:not(.empty-row-state)'));
  const emptyRowTemplate = tbody.querySelector('.empty-row-state');

  function filterAndPaginate() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();

    filteredRows = totalOriginalRows.filter(row => {
      return !q || row.textContent.toLowerCase().includes(q);
    });

    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    document.getElementById('countBadge').textContent = `${totalItems} ${totalItems === 1 ? 'student' : 'students'}`;

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

  document.getElementById('searchInput').addEventListener('input', function() {
    currentPage = 1;
    filterAndPaginate();
  });

  /* ── Dynamic Automated Navigation Highlight Trigger Routine ── */
  document.addEventListener('DOMContentLoaded', function() {
    const currentFilename = window.location.pathname.split('/').pop() || 'users_mgt.php';
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