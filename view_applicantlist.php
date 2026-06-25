<?php
require_once("dbconfig.php");

/* ===== GET ID ===== */
if (!isset($_GET['id'])) {
    header("Location: scholars_list.php");
    exit();
}

$id = $_GET['id'];
require_once("header.php");

/* ===== FETCH DATA ===== */
$stmt = $con->prepare("
    SELECT a.*, s.scholarship_name
    FROM applications_form a
    INNER JOIN scholarship s ON a.sid = s.sid
    WHERE a.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "No record found";
    exit();
}

$row         = $result->fetch_assoc();
$full_name   = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
$school_id   = htmlspecialchars($row['school_id']);
$course      = htmlspecialchars($row['course']);
$year_sec    = htmlspecialchars($row['year_section']);
$scholarship = htmlspecialchars($row['scholarship_name']);
$status      = $row['status'];
$status_low  = strtolower($status);
$date        = !empty($row['date_applied']) ? date("F d, Y", strtotime($row['date_applied'])) : '—';

$parts    = explode(' ', trim($full_name));
$initials = '';
foreach ($parts as $p) $initials .= strtoupper($p[0] ?? '');
$initials = substr($initials, 0, 2);

$pill_class = match($status_low) {
    'approved' => 'pill-approved',
    'rejected' => 'pill-rejected',
    default    => 'pill-pending',
};
$pill_icon = match($status_low) {
    'approved' => 'ti-circle-check',
    'rejected' => 'ti-circle-x',
    default    => 'ti-clock',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Application – Scholarship Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Segoe UI', system-ui, sans-serif;
      background: #f4f6f9;
    }

    /* ── Full-screen overlay — covers header + sidebar ── */
    .overlay {
      position: fixed;
      inset: 0;                          
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;                    
      padding: 24px;
    }

    /* ── Modal card ── */
    .modal {
      width: 100%;
      max-width: 520px;
      background: #ffffff;
      border-radius: 20px;
      border: 1px solid #e2e8f0;
      box-shadow:
        0 0 0 1px rgba(255,255,255,0.06),
        0 24px 60px rgba(0,0,0,0.22);
      overflow: hidden;
      animation: rise .3s cubic-bezier(.22,.68,0,1.2);
    }
    @keyframes rise {
      from { opacity: 0; transform: translateY(24px) scale(.96); }
      to   { opacity: 1; transform: translateY(0)    scale(1);   }
    }

    /* ── Modal header band ── */
    .modal-header {
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
      padding: 20px 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
    }
    .modal-header-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .avatar {
      width: 48px; height: 48px;
      border-radius: 50%;
      background: #dbeafe;
      color: #1d4ed8;
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; font-weight: 700;
      flex-shrink: 0;
      box-shadow: 0 0 0 3px #eff6ff;
    }
    .modal-name {
      font-size: 16px;
      font-weight: 600;
      color: #1a1a2e;
    }
    .modal-id {
      font-size: 12px;
      color: #94a3b8;
      margin-top: 3px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Close button */
    .close-btn {
      width: 34px; height: 34px;
      border-radius: 50%;
      background: #fee2e2;
      color: #b91c1c;
      display: flex; align-items: center; justify-content: center;
      text-decoration: none;
      font-size: 16px;
      flex-shrink: 0;
      transition: background .2s, color .2s, transform .15s;
    }
    .close-btn:hover {
      background: #dc2626;
      color: #fff;
      transform: scale(1.1) rotate(90deg);
    }

    /* ── Body ── */
    .modal-body { padding: 0 24px 8px; }

    .section-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .8px;
      color: #94a3b8;
      margin: 20px 0 10px;
    }

    /* Info grid */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1px;
      background: #f1f5f9;
      border-radius: 12px;
      overflow: hidden;
      border: 1px solid #e2e8f0;
    }
    .info-cell {
      background: #ffffff;
      padding: 13px 16px;
    }
    .info-cell.full { grid-column: 1 / -1; }
    .info-label {
      font-size: 11px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: .5px;
      color: #94a3b8;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .info-label i { font-size: 13px; }
    .info-value {
      font-size: 13px;
      font-weight: 500;
      color: #1a1a2e;
    }

    /* Tag */
    .tag {
      display: inline-block;
      background: #f1f5f9;
      color: #475569;
      font-size: 11px;
      font-weight: 500;
      padding: 3px 9px;
      border-radius: 6px;
    }

    /* Status pills */
    .pill {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 20px;
    }
    .pill i { font-size: 12px; }
    .pill-approved { background: #dcfce7; color: #15803d; }
    .pill-rejected { background: #fee2e2; color: #b91c1c; }
    .pill-pending  { background: #fef3c7; color: #b45309; }

    /* Doc link */
    .doc-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 600;
      color: #1d4ed8;
      text-decoration: none;
      padding: 7px 13px;
      border-radius: 8px;
      border: 1px solid #bfdbfe;
      background: #eff6ff;
      transition: background .15s, border-color .15s, transform .15s;
    }
    .doc-link:hover {
      background: #dbeafe;
      border-color: #93c5fd;
      transform: translateY(-1px);
    }
    .doc-link i { font-size: 15px; }
    .no-file { font-size: 13px; color: #94a3b8; }

    /* ── Footer ── */
    .modal-footer {
      padding: 16px 24px 22px;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 600;
      padding: 9px 18px;
      border-radius: 8px;
      text-decoration: none;
      background: #f1f5f9;
      color: #475569;
      border: 1px solid #e2e8f0;
      transition: background .15s, border-color .15s;
    }
    .btn-back:hover { background: #e2e8f0; border-color: #cbd5e1; }

    .btn-edit {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      font-weight: 600;
      padding: 9px 18px;
      border-radius: 8px;
      text-decoration: none;
      background: #fefce8;
      color: #a16207;
      border: 1px solid #fde68a;
      transition: background .15s, border-color .15s, transform .15s;
    }
    .btn-edit:hover {
      background: #ca8a04;
      color: #fff;
      border-color: #ca8a04;
      transform: translateY(-1px);
    }

    /* ── Click backdrop to close ── */
    .overlay-bg {
      position: absolute;
      inset: 0;
      z-index: -1;
      cursor: pointer;
    }

    /* ── Responsive ── */
    @media (max-width: 600px) {
      .overlay { padding: 16px; }
      .info-grid { grid-template-columns: 1fr; }
      .info-cell.full { grid-column: 1; }
    }
  </style>
</head>

<body>

<div class="overlay">

  <!-- clicking the dark area closes the modal -->
  <div class="overlay-bg" onclick="window.location='scholars_list.php'"></div>

  <div class="modal">

    <!-- Header -->
    <div class="modal-header">
      <div class="modal-header-left">
        <div class="avatar"><?= $initials ?></div>
        <div>
          <div class="modal-name"><?= $full_name ?></div>
          <div class="modal-id">
            <i class="ti ti-id-badge" style="font-size:12px"></i>
            <?= $school_id ?>
          </div>
        </div>
      </div>
      <a href="scholars_list.php" class="close-btn" title="Close">
        <i class="ti ti-x"></i>
      </a>
    </div>

    <!-- Body -->
    <div class="modal-body">

      <p class="section-label">Academic information</p>
      <div class="info-grid">

        <div class="info-cell">
          <div class="info-label"><i class="ti ti-book"></i> Course</div>
          <div class="info-value"><span class="tag"><?= $course ?></span></div>
        </div>

        <div class="info-cell">
          <div class="info-label"><i class="ti ti-calendar-stats"></i> Year / Section</div>
          <div class="info-value"><span class="tag"><?= $year_sec ?></span></div>
        </div>

        <div class="info-cell full">
          <div class="info-label"><i class="ti ti-award"></i> Scholarship</div>
          <div class="info-value"><?= $scholarship ?></div>
        </div>

      </div>

      <p class="section-label">Application details</p>
      <div class="info-grid">

        <div class="info-cell">
          <div class="info-label"><i class="ti ti-calendar"></i> Date Applied</div>
          <div class="info-value"><?= $date ?></div>
        </div>

        <div class="info-cell">
          <div class="info-label"><i class="ti ti-activity"></i> Status</div>
          <div class="info-value">
            <span class="pill <?= $pill_class ?>">
              <i class="ti <?= $pill_icon ?>"></i>
              <?= htmlspecialchars($status) ?>
            </span>
          </div>
        </div>

        <div class="info-cell full">
          <div class="info-label"><i class="ti ti-paperclip"></i> Document</div>
          <div class="info-value" style="margin-top:6px">
            <?php if (!empty($row['document'])): ?>
              <a href="uploads/<?= htmlspecialchars($row['document']) ?>" target="_blank" class="doc-link">
                <i class="ti ti-file-text"></i>
                View uploaded file
              </a>
            <?php else: ?>
              <span class="no-file">No file uploaded</span>
            <?php endif; ?>
          </div>
        </div>

      </div>

    </div>

    <!-- Footer -->
    <div class="modal-footer">
      <a href="scholars_list.php" class="btn-back">
        <i class="ti ti-arrow-left"></i> Back to list
      </a>
      <a href="edit_applicantlist.php?id=<?= (int)$row['aid'] ?>" class="btn-edit">
        <i class="ti ti-pencil"></i> Edit
      </a>
    </div>

  </div>
</div>

</body>
</html>