<?php
require_once("dbconfig.php");

$query = "SELECT a.*, s.scholarship_name, st.email 
          FROM applications_form a
          JOIN scholarship s ON a.sid = s.sid
          JOIN students st ON a.id = st.id
          WHERE a.status = 'Pending'
          ORDER BY a.id DESC";

$result = mysqli_query($con, $query);
$total_pending = mysqli_num_rows($result);

require_once("header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Applications – Scholarship Management</title>
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
    .page-header-left h2 {
      font-size: 26px;
      font-weight: 700;
      color: #0f172a;
      letter-spacing: -0.5px;
    }
    .page-header-left p {
      font-size: 14px;
      color: #64748b;
      margin-top: 4px;
      font-weight: 400;
    }
    .badge-count {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
      color: #d97706;
      background: #fef3c7;
      padding: 6px 14px;
      border-radius: 50px;
      border: 1px solid #fde68a;
    }

    /* ── Search bar ── */
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

    /* ── Course pill ── */
    .course-tag {
      display: inline-block;
      background: #f1f5f9;
      color: #475569;
      font-size: 11px;
      font-weight: 500;
      padding: 3px 9px;
      border-radius: 6px;
    }

    /* ── Scholarship title ── */
    .scholarship-name { font-size: 14px; color: #475569; font-weight: 500; }

    /* ── Date ── */
    .date-cell { color: #64748b; font-size: 12px; }

    /* ── Status badge ── */
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
    .pill-pending  { background: #fef3c7; color: #d97706; }
    .pill-approved { background: #dcfce7; color: #16a34a; }
    .pill-rejected { background: #fee2e2; color: #dc2626; }

    /* ── Document link ── */
    .doc-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 600;
      color: #3b82f6;
      text-decoration: none;
      padding: 5px 15px;
      border-radius: 6px;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      transition: background .15s, border-color .15s;
    }
    .doc-link:hover { background: #dbeafe; border-color: #93c5fd; }
    .doc-link i { font-size: 14px; }

    /* ── Action buttons ── */
    .actions { display: flex; gap: 8px; align-items: center; }

    .btn-approve, .btn-reject {
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
    }
    .btn-approve {
      background: #dcfce7;
      color: #16a34a;
      border: 1px solid #bbf7d0;
    }
    .btn-approve:hover {
      background: #16a34a;
      color: #fff;
      border-color: #16a34a;
      box-shadow: 0 4px 12px rgba(22,163,74,.25);
      transform: translateY(-1px);
    }
    .btn-reject {
      background: #fee2e2;
      color: #dc2626;
      border: 1px solid #fecaca;
    }
    .btn-reject:hover {
      background: #dc2626;
      color: #fff;
      border-color: #dc2626;
      box-shadow: 0 4px 12px rgba(220,38,38,.25);
      transform: translateY(-1px);
    }

    /* ── Pagination controls (Synced with Dashboard) ── */
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

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 48px 24px;
      color: #94a3b8;
    }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; }

    /* ── Responsive ── */
    @media (max-width: 992px) {
      .main { margin-left: 0 !important; width: 100% !important; padding: 24px; }
    }
    @media (max-width: 768px) {
      .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
      thead th:nth-child(3), td:nth-child(3),
      thead th:nth-child(4), td:nth-child(4) { display: none; }
      .pagination-footer { flex-direction: column; gap: 12px; text-align: center; }
    }
  </style>

  <style>
    /* ===== STATUS CONFIRMATION MODAL ===== */
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
      box-shadow: 0 20px 60px rgba(15,23,42,.18), 0 4px 16px rgba(15,23,42,.08);
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
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 18px;
    }
    .modal-icon-wrap i { font-size: 28px; }
    .modal-icon-approve { background: #dcfce7; border: 1.5px solid #bbf7d0; }
    .modal-icon-approve i { color: #16a34a; }
    .modal-icon-reject  { background: #fff1f2; border: 1.5px solid #fecdd3; }
    .modal-icon-reject  i { color: #be123c; }

    .modal-title { font-size: 17px; font-weight: 700; color: #0f172a; margin-bottom: 8px; }
    .modal-body  { font-size: 13px; color: #64748b; line-height: 1.6; margin-bottom: 24px; }
    .modal-body strong { color: #0f172a; font-weight: 600; }

    .modal-actions { display: flex; gap: 10px; width: 100%; }
    .modal-btn {
      flex: 1;
      padding: 10px 0;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 600;
      border: 1px solid transparent;
      cursor: pointer;
      font-family: inherit;
      transition: all .15s;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }
    .modal-btn:active { transform: scale(.97); }

    .modal-btn-cancel { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .modal-btn-cancel:hover { background: #f1f5f9; border-color: #cbd5e1; }

    .modal-btn-approve { background: #dcfce7; color: #16a34a; border-color: #bbf7d0; }
    .modal-btn-approve:hover { background: #16a34a; color: #fff; border-color: #16a34a; box-shadow: 0 4px 14px rgba(22,163,74,.28); }

    .modal-btn-reject { background: #fff1f2; color: #be123c; border-color: #fecdd3; }
    .modal-btn-reject:hover { background: #be123c; color: #fff; border-color: #be123c; box-shadow: 0 4px 14px rgba(190,18,60,.28); }
  </style>
</head>

<body>
  <div class="modal-overlay" id="statusModal">
    <div class="modal-card">
      <div class="modal-icon-wrap modal-icon-approve" id="modalIconWrap">
        <i class="ti ti-circle-check" id="modalIcon"></i>
      </div>
      <div class="modal-title" id="modalTitle">Confirm Action</div>
      <div class="modal-body">
        Are you sure you want to <strong id="statusModalText"></strong>?
        <br>This action will update the application status.
      </div>
      <div class="modal-actions">
        <button class="modal-btn modal-btn-cancel" onclick="closeStatusModal()">Cancel</button>
        <a href="#" id="statusModalConfirm" class="modal-btn modal-btn-approve">Confirm</a>
      </div>
    </div>
  </div>
<main class="main">

  <div class="page-header">
    <div class="page-header-left">
      <h2>New Application</h2>
      <p>Review and manage incoming scholarship applications</p>
    </div>
    <span class="badge-count">
      <i class="ti ti-clock"></i>
      <?= $total_pending ?> pending
    </span>
  </div>

  <div class="toolbar">
    <div class="search-wrap">
      <i class="ti ti-search"></i>
      <input type="text" id="searchInput" placeholder="Search by name, ID, or scholarship…">
    </div>
  </div>

  <div class="table-card">
    <div class="table-responsive">
      <table id="appTable">
        <thead>
          <tr>
            <th>Applicant Profile</th>
            <th>Course</th>
            <th>Scholarship Program</th>
            <th>Date Applied</th>
            <th>Status</th>
            <th>Document</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>

          <?php if (mysqli_num_rows($result) > 0):
            $av_classes = ['av-0','av-1','av-2','av-3','av-4'];
            $i = 0;
            while ($row = mysqli_fetch_assoc($result)):
              $full_name   = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
              $school_id   = htmlspecialchars($row['school_id']);
              $course      = htmlspecialchars($row['course']);
              $scholarship = htmlspecialchars($row['scholarship_name']);
              $aid         = (int)$row['aid'];

              $parts    = explode(' ', trim($full_name));
              $initials = '';
              foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
              $initials  = substr($initials, 0, 2);
              $av_class  = $av_classes[$i % count($av_classes)];
              $i++;

              $status_text  = !empty($row['status']) ? $row['status'] : 'Pending';
              $status_lower = strtolower($status_text);
              $pill_class   = match($status_lower) {
                'approved' => 'pill-approved',
                'rejected' => 'pill-rejected',
                default    => 'pill-pending',
              };
              $pill_icon = match($status_lower) {
                'approved' => 'ti-check',
                'rejected' => 'ti-x',
                default    => 'ti-clock',
              };

              $date = !empty($row['date_applied'])
                ? date('M j, Y', strtotime($row['date_applied']))
                : '—';
          ?>
          <tr>
            <td>
              <div class="avatar-wrap">
                <div class="avatar <?= $av_class ?>"><?= $initials ?></div>
                <div>
                  <div class="student-name"><?= $full_name ?></div>
                  <div class="student-id"><?= $school_id ?></div>
                </div>
              </div>
            </td>

            <td><span class="course-tag"><?= $course ?></span></td>

            <td class="scholarship-name"><?= $scholarship ?></td>

            <td class="date-cell"><?= $date ?></td>

            <td>
              <span class="pill <?= $pill_class ?>">
                <i class="ti <?= $pill_icon ?>"></i>
                <?= htmlspecialchars($status_text) ?>
              </span>
            </td>

            <td>
              <a href="view_newapplicant.php?id=<?= $aid; ?>" class="doc-link">
                 <i class="ti ti-eye"></i> View
              </a>
            </td>

            <td>
              <div class="actions">
                <a href="#"
                   class="btn-approve"
                   data-action-url="update_status.php?id=<?= $aid ?>&status=Approved"
                   data-action-name="Approve application for <?= $full_name ?>">
                  <i class="ti ti-check"></i> Approve
                </a>

                <a href="#"
                   class="btn-reject"
                   data-action-url="update_status.php?id=<?= $aid ?>&status=Rejected"
                   data-action-name="Reject application for <?= $full_name ?>">
                  <i class="ti ti-x"></i> Reject
                </a>
              </div>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr class="empty-row-state">
            <td colspan="7">
              <div class="empty-state">
                <i class="ti ti-mood-empty"></i>
                <strong>No pending applications</strong>
                <p>All applications have been reviewed.</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>

        </tbody>
      </table>
    </div>

    <div class="pagination-footer" id="paginationWrapper">
      <div class="page-info" id="pageInfoText">Showing 0 to 0 of 0 entries</div>
      <div class="pagination-buttons" id="paginationControls"></div>
    </div>
  </div>

</main>

<script>
  // ── Client-Side Pagination & Dynamic Filter Logic (Dashboard Clone) ──
  let currentPage = 1;
  const itemsPerPage = 5; 
  let filteredRows = [];

  const tbody = document.querySelector('#appTable tbody');
  const totalOriginalRows = Array.from(tbody.querySelectorAll('tr:not(.empty-row-state)'));
  const emptyRowTemplate = tbody.querySelector('.empty-row-state');

  function filterAndPaginate() {
    const q = document.getElementById('searchInput').value.toLowerCase().trim();

    // Perform Search Filter Matching
    filteredRows = totalOriginalRows.filter(row => {
      return !q || row.textContent.toLowerCase().includes(q);
    });

    const totalItems = filteredRows.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    // Toggle Empty State Row Visibility
    if (totalItems === 0) {
      totalOriginalRows.forEach(r => r.style.display = 'none');
      if (emptyRowTemplate) {
        emptyRowTemplate.style.display = '';
      } else {
        tbody.innerHTML = `<tr class="empty-row-state"><td colspan="7"><div class="empty-state"><i class="ti ti-mood-empty"></i>No results found.</div></td></tr>`;
      }
      document.getElementById('pageInfoText').textContent = "Showing 0 to 0 of 0 entries";
      document.getElementById('paginationControls').innerHTML = '';
      return;
    }

    if (emptyRowTemplate) emptyRowTemplate.style.display = 'none';

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalItems);

    // Toggle visible segments
    totalOriginalRows.forEach(row => row.style.display = 'none');
    filteredRows.slice(startIndex, endIndex).forEach(row => row.style.display = '');

    // Set Text Values
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

  // ── Auto Active-State Class Selection Script (Exact Dashboard Port) ──
  document.addEventListener('DOMContentLoaded', function() {
    const currentFilename = window.location.pathname.split('/').pop() || 'newapplication.php';
    const sidebarLinks = document.querySelectorAll('.sidebar a, .nav-sidebar a, .aside a, #sidebar a');
    
    sidebarLinks.forEach(link => {
      const hrefFile = link.getAttribute('href');
      if (hrefFile && currentFilename.includes(hrefFile)) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });

    // Run pagination initialization routine
    filterAndPaginate();
  });

  // ── Modal Handlers ──
const statusModal        = document.getElementById('statusModal');
  const statusModalText    = document.getElementById('statusModalText');
  const statusModalConfirm = document.getElementById('statusModalConfirm');
  const modalIconWrap      = document.getElementById('modalIconWrap');
  const modalIcon          = document.getElementById('modalIcon');
  const modalTitle         = document.getElementById('modalTitle');

  function closeStatusModal() {
    statusModal.classList.remove('active');
  }

  document.querySelectorAll('[data-action-url]').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const url      = this.getAttribute('data-action-url');
      const name     = this.getAttribute('data-action-name') || 'this action';
      const isReject = url.includes('Rejected');

      // Swap icon + colors based on approve/reject
      if (isReject) {
        modalIconWrap.className = 'modal-icon-wrap modal-icon-reject';
        modalIcon.className     = 'ti ti-circle-x';
        modalTitle.textContent  = 'Reject Application';
        statusModalConfirm.className = 'modal-btn modal-btn-reject';
      } else {
        modalIconWrap.className = 'modal-icon-wrap modal-icon-approve';
        modalIcon.className     = 'ti ti-circle-check';
        modalTitle.textContent  = 'Approve Application';
        statusModalConfirm.className = 'modal-btn modal-btn-approve';
      }

      statusModalText.textContent = name;
      statusModalConfirm.href     = url;
      statusModal.classList.add('active');
    });
  });

  statusModal.addEventListener('click', function(e) {
    if (e.target === statusModal) closeStatusModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeStatusModal();
  });
</script>

</body>
</html>