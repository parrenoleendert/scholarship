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
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      padding-bottom: 20px;
      border-bottom: 1px solid #e2e8f0;
    }
    .page-header-left h2 {
      font-size: 22px;
      font-weight: 600;
      color: #1a1a2e;
    }
    .page-header-left p {
      font-size: 13px;
      color: #64748b;
      margin-top: 4px;
    }
    .badge-count {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: #fef3c7;
      color: #b45309;
      border: 1px solid #fde68a;
      font-size: 12px;
      font-weight: 600;
      padding: 6px 14px;
      border-radius: 20px;
    }

    /* ── Search / filter bar ── */
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
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
      color: #94a3b8;
      pointer-events: none;
    }
    .search-wrap input {
      width: 100%;
      padding: 9px 12px 9px 34px;
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

    /* ── Date ── */
    .date-cell { color: #64748b; font-size: 12px; }

    /* ── Status badge ── */
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
    .pill-pending  { background: #fef3c7; color: #b45309; }
    .pill-approved { background: #dcfce7; color: #15803d; }
    .pill-rejected { background: #fee2e2; color: #b91c1c; }

    /* ── Document link ── */
    .doc-link {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 12px;
      font-weight: 300;
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
      color: #15803d;
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
      color: #b91c1c;
      border: 1px solid #fecaca;
    }
    .btn-reject:hover {
      background: #dc2626;
      color: #fff;
      border-color: #dc2626;
      box-shadow: 0 4px 12px rgba(220,38,38,.25);
      transform: translateY(-1px);
    }
    .btn-approve i, .btn-reject i { font-size: 13px; }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #94a3b8;
    }
    .empty-state i { font-size: 44px; margin-bottom: 12px; display: block; color: #cbd5e1; }
    .empty-state p { font-size: 14px; margin-top: 6px; }
    .empty-state strong { font-size: 16px; color: #64748b; display: block; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
      .main { margin-left: 0; padding: 20px 16px; }
      .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
      thead th:nth-child(3),
      td:nth-child(3),
      thead th:nth-child(5),
      td:nth-child(5) { display: none; }
    }
  </style>

  <style>
    /* ===== DELETE/STATUS MODAL ===== */
    .modal-overlay{
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.45);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    .modal-overlay.active{ display:flex; }
    .modal-card{
      background:#fff;
      width:90%;
      max-width:520px;
      border-radius:16px;
      box-shadow:0 15px 35px rgba(0,0,0,0.25);
      padding:28px;
      animation: fadeIn 0.2s ease;
      position: relative;
      text-align:center;
    }
    @keyframes fadeIn{
      from{ opacity:0; transform: translateY(6px); }
      to{ opacity:1; transform: translateY(0); }
    }
    .modal-title{ font-size:20px; font-weight:800; color:#dc3545; margin-bottom:10px; }
    .modal-text{ font-size:14px; color:#374151; line-height:1.6; margin-bottom:18px; }
    .modal-actions{ display:flex; gap:10px; justify-content:center; }
    .modal-btn{
      padding:12px 16px;
      border:none;
      border-radius:10px;
      font-weight:800;
      cursor:pointer;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      min-width:140px;
      transition: 0.2s;
    }
    .modal-btn-cancel{ background:#6c757d; color:#fff; }
    .modal-btn-cancel:hover{ background:#5c636a; }
    .modal-btn-confirm{ background:#dc3545; color:#fff; }
    .modal-btn-confirm:hover{ background:#bb2d3b; }
    .modal-close{
      position:absolute;
      top:14px;
      right:14px;
      width:38px;
      height:38px;
      border-radius:50%;
      background:#f8d7da;
      color:#dc3545;
      text-decoration:none;
      display:flex;
      align-items:center;
      justify-content:center;
      font-size:20px;
      font-weight:900;
    }
    .modal-close:hover{ background:#dc3545; color:#fff; }
  </style>
</head>

<body>
  <div class="modal-overlay" id="statusModal" aria-hidden="true">
    <div class="modal-card">
      <a href="#" class="modal-close" onclick="closeStatusModal(event)">×</a>
      <div class="modal-title">Confirm Status Change</div>
      <div class="modal-text"><span id="statusModalText"></span></div>
      <div class="modal-actions">
        <a href="#" class="modal-btn modal-btn-cancel" onclick="closeStatusModal(event)">Cancel</a>
        <a href="#" id="statusModalConfirm" class="modal-btn modal-btn-confirm">Confirm</a>
      </div>
    </div>
  </div>

<main class="main">

  <!-- Page Header -->
  <div class="page-header">
    <div class="page-header-left">
      <h2>New Applications</h2>
      <p>Review and manage incoming scholarship applications</p>
    </div>
    <span class="badge-count">
      <i class="ti ti-clock" style="font-size:14px"></i>
      <?= $total_pending ?> pending
    </span>
  </div>

  <!-- Search bar -->
  <div class="toolbar">
    <div class="search-wrap">
      <i class="ti ti-search"></i>
      <input type="text" id="searchInput" placeholder="Search by name, ID, or scholarship…">
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <table id="appTable">
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Course</th>
          <th>Scholarship</th>
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
            $doc         = htmlspecialchars($row['document']);
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
              'approved' => 'ti-circle-check',
              'rejected' => 'ti-circle-x',
              default    => 'ti-clock',
            };

            $date = !empty($row['date_applied'])
              ? date('M j, Y', strtotime($row['date_applied']))
              : '—';
        ?>
        <tr>
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
          <td><span class="course-tag"><?= $course ?></span></td>

          <!-- Scholarship -->
          <td><?= $scholarship ?></td>

          <!-- Date -->
          <td class="date-cell">
            <i class="ti ti-calendar" style="font-size:12px;margin-right:4px;vertical-align:-1px"></i>
            <?= $date ?>
          </td>

          <!-- Status -->
          <td>
            <span class="pill <?= $pill_class ?>">
              <i class="ti <?= $pill_icon ?>"></i>
              <?= htmlspecialchars($status_text) ?>
            </span>
          </td>

          <!-- Document -->
          <td>
            <a href="view_newapplicant.php?id=<?php echo $row['aid']; ?>" class="doc-link">
               <i class="ti ti-eye"></i> View
              </a>
          </td>

          <!-- Actions -->
          <td>
              <a href="#"
                 class="btn-approve"
                 data-action-url="update_status.php?id=<?= $aid ?>&status=Approved"
                 data-action-name="Approve application">
                <i class="ti ti-check"></i> Approve
              </a>

              <a href="#"
                 class="btn-reject"
                 data-action-url="update_status.php?id=<?= $aid ?>&status=Rejected"
                 data-action-name="Reject application">
                <i class="ti ti-x"></i> Reject
              </a>
            </div>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <i class="ti ti-inbox"></i>
              <strong>No pending applications</strong>
              <p>All applications have been reviewed.</p>
            </div>
          </td>
        </tr>
        <?php endif; ?>

      </tbody>
    </table>
  </div>

</main>

<script>
  const statusModal = document.getElementById('statusModal');
  const statusModalText = document.getElementById('statusModalText');
  const statusModalConfirm = document.getElementById('statusModalConfirm');

  function closeStatusModal(e){
    if(e) e.preventDefault();
    statusModal.classList.remove('active');
  }

  document.querySelectorAll('[data-action-url]').forEach(link => {
    link.addEventListener('click', function(e){
      e.preventDefault();

      const url = this.getAttribute('data-action-url');
      const name = this.getAttribute('data-action-name') || 'this action';

      statusModalText.textContent = name;
      statusModalConfirm.setAttribute('href', url);
      statusModal.classList.add('active');
    });
  });

  statusModalConfirm.addEventListener('click', function(e){
    // allow navigation; no confirm dialogs
  });

  statusModal.addEventListener('click', function(e){
    if(e.target === statusModal) statusModal.classList.remove('active');
  });

  document.getElementById('searchInput').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#appTable tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
</script>

</body>
</html>