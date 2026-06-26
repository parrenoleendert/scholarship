<?php
require_once("dbconfig.php");
session_start();

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];

// TOTAL SCHOLARSHIPS
$stmt1 = $con->prepare("SELECT COUNT(*) FROM scholarship");
$stmt1->execute();
$stmt1->bind_result($total_scholarship);
$stmt1->fetch();
$stmt1->close();

// TOTAL APPROVED
$stmt2 = $con->prepare("SELECT COUNT(*) FROM applications_form WHERE status='Approved' AND id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$stmt2->bind_result($total_approved);
$stmt2->fetch();
$stmt2->close();

// TOTAL PENDING
$stmt_p = $con->prepare("SELECT COUNT(*) FROM applications_form WHERE status='Pending' AND id = ?");
$stmt_p->bind_param("i", $user_id);
$stmt_p->execute();
$stmt_p->bind_result($total_pending);
$stmt_p->fetch();
$stmt_p->close();

// TOTAL HISTORY
$stmt3 = $con->prepare("SELECT COUNT(*) FROM applications_form WHERE id = ?");
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$stmt3->bind_result($total_history);
$stmt3->fetch();
$stmt3->close();

// STUDENT NAME
$stmtN = $con->prepare("SELECT first_name, last_name, course FROM students WHERE id = ?");
$stmtN->bind_param("i", $user_id);
$stmtN->execute();
$stmtN->bind_result($first_name, $last_name, $course);
$stmtN->fetch();
$stmtN->close();

// RECENT APPLICATIONS (last 5)
$recent = [];
$stmtR = $con->prepare("SELECT a.status, a.date_applied, s.scholarship_name 
                         FROM applications_form a 
                         JOIN scholarship s ON a.sid = s.sid 
                         WHERE a.id = ? 
                         ORDER BY a.date_applied DESC LIMIT 5");
$stmtR->bind_param("i", $user_id);
$stmtR->execute();
$resR = $stmtR->get_result();
while($r = $resR->fetch_assoc()) $recent[] = $r;
$stmtR->close();

// AVAILABLE SCHOLARSHIPS (open, upcoming deadline, limit 5)
$scholarships = [];
$stmtS = $con->prepare("SELECT scholarship_name, provider, deadline, amount, status FROM scholarship WHERE status='Open' ORDER BY deadline ASC LIMIT 5");
$stmtS->execute();
$resS = $stmtS->get_result();
while($s = $resS->fetch_assoc()) $scholarships[] = $s;
$stmtS->close();

require_once("headers.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }

        /* ── MAIN CONTAINER ALIGNED WITH ADMIN ── */
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

        /* ── Page Header ── */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .page-header h2 {
            font-family: 'Sora', sans-serif;
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

        /* ── PIXEL PERFECT METRIC CARDS GRID FROM ADMIN ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }

        .stat-details h3 {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .stat-details .number {
            font-family: 'Sora', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .icon-blue   { background: #eff6ff; color: #2563eb; }
        .icon-green  { background: #dcfce7; color: #16a34a; }
        .icon-yellow { background: #fef9c3; color: #ca8a04; }
        .icon-slate  { background: #f1f5f9; color: #475569; }

        /* ── BOTTOM GRID CONTAINER FOR PANELS ── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* ── PANEL HOUSING (ADMIN COMPONENT STANDARDS) ── */
        .panel {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            overflow: hidden;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .panel-title i { font-size: 16px; color: #3b82f6; }

        .panel-link {
            font-size: 13px;
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .panel-link:hover { text-decoration: underline; }

        /* ── ROW ITEMS COMPONENTS ── */
        .list-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            transition: background 0.15s;
        }
        .list-item:last-child { border-bottom: none; }
        .list-item:hover { background: #f8fafc; }

        .item-icon-wrap {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0;
        }
        .icon-wrap-open { background: #dcfce7; color: #16a34a; }
        
        .item-icon-status {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .item-icon-status.approved { background: #dcfce7; color: #16a34a; }
        .item-icon-status.pending  { background: #fef9c3; color: #ca8a04; }
        .item-icon-status.rejected { background: #fee2e2; color: #dc2626; }

        .item-info { flex: 1; min-width: 0; }
        .item-title {
            font-size: 14px; font-weight: 600; color: #0f172a;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .item-meta {
            font-size: 12.5px; color: #64748b; margin-top: 3px;
            display: flex; align-items: center; gap: 12px;
        }
        
        .status-dot-open {
            display: inline-flex; align-items: center; gap: 4px;
            font-size: 12px; font-weight: 600; color: #16a34a;
        }
        .status-dot-open::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%; background: #16a34a;
        }

        .item-right { text-align: right; flex-shrink: 0; }
        .item-amount { font-family: 'Sora', sans-serif; font-size: 14px; font-weight: 700; color: #0f172a; }
        .item-subtext { font-size: 12px; color: #64748b; margin-top: 3px; }
        .item-subtext.soon { color: #ea580c; font-weight: 600; }

        /* Status Badge Pills */
        .pill-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 12px; border-radius: 50px; font-size: 12px;
            font-weight: 600; white-space: nowrap; flex-shrink: 0;
        }
        .pill-badge.approved { background: #dcfce7; color: #16a34a; }
        .pill-badge.pending  { background: #fef9c3; color: #ca8a04; }
        .pill-badge.rejected { background: #fee2e2; color: #dc2626; }

        /* Empty State */
        .empty-state { padding: 48px 24px; text-align: center; color: #94a3b8; }
        .empty-state i { font-size: 36px; color: #cbd5e1; display: block; margin-bottom: 10px; }
        .empty-state p { font-size: 14px; font-weight: 600; color: #64748b; }

        /* Responsive Breakpoints */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .bottom-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 992px) {
            .main { margin-left: 0 !important; width: 100% !important; padding: 24px; padding-top: 120px; }
        }
        @media (max-width: 576px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<main class="main">
    
    <div class="page-header">
        <div>
            <h2>Welcome back, <?= htmlspecialchars($first_name . ' ' . $last_name) ?></h2>
            <p>Monitor your active scholarship profile ecosystem and dynamic updates real-time.</p>
        </div>
    </div>

    <div class="stats-grid">
        <a href="schemes.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-details">
                    <h3>Scholarships</h3>
                    <div class="number"><?= $total_scholarship ?></div>
                </div>
                <div class="stat-icon icon-blue">
                    <i class="ti ti-medal"></i>
                </div>
            </div>
        </a>

        <a href="applicationhistory.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-details">
                    <h3>Approved</h3>
                    <div class="number"><?= $total_approved ?></div>
                </div>
                <div class="stat-icon icon-green">
                    <i class="ti ti-circle-check"></i>
                </div>
            </div>
        </a>

        <a href="applicationhistory.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-details">
                    <h3>Pending</h3>
                    <div class="number"><?= $total_pending ?></div>
                </div>
                <div class="stat-icon icon-yellow">
                    <i class="ti ti-clock"></i>
                </div>
            </div>
        </a>

        <a href="applicationhistory.php" class="stat-card-link">
            <div class="stat-card">
                <div class="stat-details">
                    <h3>Total Applied</h3>
                    <div class="number"><?= $total_history ?></div>
                </div>
                <div class="stat-icon icon-slate">
                    <i class="ti ti-history"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="bottom-grid">

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ti ti-school"></i>
                    Open Scholarships
                </div>
                <a href="schemes.php" class="panel-link">View all <i class="ti ti-arrow-right"></i></a>
            </div>
            <div class="panel-content">
                <?php if(empty($scholarships)): ?>
                <div class="empty-state">
                    <i class="ti ti-folder-off"></i>
                    <p>No open scholarships available right now.</p>
                </div>
                <?php else: ?>
                <?php foreach($scholarships as $s):
                    $daysLeft = !empty($s['deadline']) ? (strtotime($s['deadline']) - time()) / 86400 : null;
                    $deadlineText = !empty($s['deadline']) ? date("M d, Y", strtotime($s['deadline'])) : 'No deadline';
                    $deadlineClass = ($daysLeft !== null && $daysLeft <= 7) ? 'soon' : '';
                    if($daysLeft !== null && $daysLeft <= 7) $deadlineText .= ' (' . ceil($daysLeft) . 'd left)';
                ?>
                <div class="list-item">
                    <div class="item-icon-wrap icon-wrap-open"><i class="ti ti-certificate"></i></div>
                    <div class="item-info">
                        <div class="item-title"><?= htmlspecialchars($s['scholarship_name']) ?></div>
                        <div class="item-meta">
                            <span><?= htmlspecialchars($s['provider']) ?></span>
                            <span class="status-dot-open">Open</span>
                        </div>
                    </div>
                    <div class="item-right">
                        <div class="item-amount">₱<?= number_format($s['amount'], 0) ?></div>
                        <div class="item-subtext <?= $deadlineClass ?>"><?= $deadlineText ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="ti ti-refresh"></i>
                    Recent Applications
                </div>
                <a href="applicationhistory.php" class="panel-link">View all <i class="ti ti-arrow-right"></i></a>
            </div>
            <div class="panel-content">
                <?php if(empty($recent)): ?>
                <div class="empty-state">
                    <i class="ti ti-inbox"></i>
                    <p>You haven't applied to any scholarship yet.</p>
                </div>
                <?php else: ?>
                <?php foreach($recent as $r):
                    $st = strtolower($r['status']);
                    $icon = match($st) {
                        'approved' => 'ti-circle-check',
                        'rejected' => 'ti-circle-x',
                        default    => 'ti-clock'
                    };
                ?>
                <div class="list-item">
                    <div class="item-icon-status <?= $st ?>">
                        <i class="ti <?= $icon ?>"></i>
                    </div>
                    <div class="item-info">
                        <div class="item-title"><?= htmlspecialchars($r['scholarship_name']) ?></div>
                        <div class="item-meta">
                            <span><i class="ti ti-calendar" style="font-size: 13px; margin-right: 2px;"></i> Applied: <?= date("M d, Y", strtotime($r['date_applied'])) ?></span>
                        </div>
                    </div>
                    <span class="pill-badge <?= $st ?>"><?= ucfirst($r['status']) ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </div>
</main>

<script>
/* ── Dynamic Sidebar Class Active Script ── */
document.addEventListener('DOMContentLoaded', function() {
  const currentFilename = window.location.pathname.split('/').pop() || 'dashboardusers.php';
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