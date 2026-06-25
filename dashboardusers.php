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
                         ORDER BY a.date_applied DESC ");
$stmtR->bind_param("i", $user_id);
$stmtR->execute();
$resR = $stmtR->get_result();
while($r = $resR->fetch_assoc()) $recent[] = $r;
$stmtR->close();

// AVAILABLE SCHOLARSHIPS (open, upcoming deadline, limit 5)
$scholarships = [];
$stmtS = $con->prepare("SELECT scholarship_name, provider, deadline, amount, status FROM scholarship WHERE status='Open' ORDER BY deadline ASC");
$stmtS->execute();
$resS = $stmtS->get_result();
while($s = $resS->fetch_assoc()) $scholarships[] = $s;
$stmtS->close();

$initials = strtoupper(substr($first_name ?? 'S', 0, 1) . substr($last_name ?? 'A', 0, 1));

require_once("headers.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholar Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w:     248px;
            --topbar-h:      64px;
            --accent:        #20d296;
            --accent2:       #3b7cf4;
            --gold:          #f5a623;
            --bg:            #f1f4fb;
            --card:          #ffffff;
            --text:          #111d2e;
            --muted:         #8494ae;
            --border:        #e4e9f4;
            --radius:        13px;
            --pending-bg:    #fff8e1;
            --pending-txt:   #a16207;
            --approved-bg:   #dcfdf2;
            --approved-txt:  #0a7c54;
            --rejected-bg:   #ffe4e8;
            --rejected-txt:  #c0103a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ── MAIN ── */
        .main {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .page-body {
            width: 100%;
            max-width: 1400px;
            padding: 28px 32px;
        }

       

        /* ── STAT CARDS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
            animation: fadeUp 0.35s ease both;
            text-decoration: none;
            color: inherit;
        }

        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.10s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.20s; }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 28px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 12px;
            display: grid; place-items: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .stat-icon.blue     { background: rgba(59,124,244,0.10); color: var(--accent2); }
        .stat-icon.green    { background: var(--approved-bg);    color: var(--approved-txt); }
        .stat-icon.yellow   { background: var(--pending-bg);     color: var(--pending-txt); }
        .stat-icon.gray     { background: #f1f4fb;               color: var(--muted); }

        .stat-info {}
        .stat-label { font-size: 11.5px; color: var(--muted); font-weight: 500; margin-bottom: 3px; }
        .stat-value { font-family: 'Sora', sans-serif; font-size: 26px; font-weight: 700; line-height: 1; color: var(--text); }

        /* ── BOTTOM GRID ── */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            animation: fadeUp 0.4s 0.2s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── PANEL ── */
        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border);
            background: #fafbfe;
        }

        .panel-title {
            font-size: 13.5px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-title i { color: var(--accent); font-size: 13px; }

        .panel-link {
            font-size: 12px;
            color: var(--accent);
            font-weight: 600;
            text-decoration: none;
        }
        .panel-link:hover { text-decoration: underline; }

        /* ── SCHOLARSHIP LIST ── */
        .schol-list { }

        .schol-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 20px;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .schol-item:last-child { border-bottom: none; }
        .schol-item:hover { background: #f7f9ff; }

        .schol-icon-wrap {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(32,210,150,0.10);
            display: grid; place-items: center;
            font-size: 15px;
            color: var(--accent);
            flex-shrink: 0;
        }

        .schol-info { flex: 1; min-width: 0; }

        .schol-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .schol-meta {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 2px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .schol-meta i { font-size: 10px; }

        .schol-right { text-align: right; flex-shrink: 0; }

        .schol-amount {
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .schol-deadline {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
        }

        .schol-deadline.soon { color: #e85d04; font-weight: 600; }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: var(--approved-txt);
        }

        .status-dot::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--accent);
        }

        /* ── HISTORY LIST ── */
        .history-list { }

        .history-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 20px;
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        .history-item:last-child { border-bottom: none; }
        .history-item:hover { background: #f7f9ff; }

        .history-icon {
            width: 36px; height: 36px;
            border-radius: 9px;
            display: grid; place-items: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .history-icon.approved { background: var(--approved-bg); color: var(--approved-txt); }
        .history-icon.pending  { background: var(--pending-bg);  color: var(--pending-txt); }
        .history-icon.rejected { background: var(--rejected-bg); color: var(--rejected-txt); }

        .history-info { flex: 1; min-width: 0; }

        .history-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .history-date {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 2px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .status-badge.approved { background: var(--approved-bg); color: var(--approved-txt); }
        .status-badge.pending  { background: var(--pending-bg);  color: var(--pending-txt); }
        .status-badge.rejected { background: var(--rejected-bg); color: var(--rejected-txt); }

        /* Empty */
        .empty-row {
            padding: 36px 20px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }

        .empty-row i { font-size: 28px; color: var(--border); display: block; margin-bottom: 10px; }

        /* Responsive */
        @media (max-width: 900px) {
            .stats-grid  { grid-template-columns: repeat(2, 1fr); }
            .bottom-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .main { margin-left: 0; }
            .page-body { padding: 16px; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>

<div class="main">
    <div class="page-body">

        

        <!-- Stat cards -->
        <div class="stats-grid">
            <a href="schemes.php" class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-medal"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Scholarships</div>
                    <div class="stat-value"><?= $total_scholarship ?></div>
                </div>
            </a>

            <a href="applicationhistory.php?status=Approved" class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Approved</div>
                    <div class="stat-value"><?= $total_approved ?></div>
                </div>
            </a>

            <a href="applicationhistory.php?status=Pending" class="stat-card">
                <div class="stat-icon yellow"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Pending</div>
                    <div class="stat-value"><?= $total_pending ?></div>
                </div>
            </a>

            <a href="applicationhistory.php" class="stat-card">
                <div class="stat-icon gray"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div class="stat-info">
                    <div class="stat-label">Total Applied</div>
                    <div class="stat-value"><?= $total_history ?></div>
                </div>
            </a>
        </div>

        <!-- Bottom grid: scholarships + history -->
        <div class="bottom-grid">

            <!-- Open Scholarships -->
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Open Scholarships
                    </div>
                    <a href="schemes.php" class="panel-link">View all →</a>
                </div>
                <div class="schol-list">
                    <?php if(empty($scholarships)): ?>
                    <div class="empty-row">
                        <i class="fa-solid fa-graduation-cap"></i>
                        No open scholarships right now.
                    </div>
                    <?php else: ?>
                    <?php foreach($scholarships as $s):
                        $daysLeft = !empty($s['deadline']) ? (strtotime($s['deadline']) - time()) / 86400 : null;
                        $deadlineText = !empty($s['deadline']) ? date("M d, Y", strtotime($s['deadline'])) : 'No deadline';
                        $deadlineClass = ($daysLeft !== null && $daysLeft <= 7) ? 'soon' : '';
                        if($daysLeft !== null && $daysLeft <= 7) $deadlineText .= ' (' . ceil($daysLeft) . 'd left)';
                    ?>
                    <div class="schol-item">
                        <div class="schol-icon-wrap"><i class="fa-solid fa-graduation-cap"></i></div>
                        <div class="schol-info">
                            <div class="schol-name"><?= htmlspecialchars($s['scholarship_name']) ?></div>
                            <div class="schol-meta">
                                <span> <?= htmlspecialchars($s['provider']) ?></span>
                                <span class="status-dot">Open</span>
                            </div>
                        </div>
                        <div class="schol-right">
                            <div class="schol-amount">₱<?= number_format($s['amount'], 0) ?></div>
                            <div class="schol-deadline <?= $deadlineClass ?>"><?= $deadlineText ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Application History -->
            <div class="panel">
                <div class="panel-head">
                    <div class="panel-title">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        Recent Applications
                    </div>
                    <a href="applicationhistory.php" class="panel-link">View all →</a>
                </div>
                <div class="history-list">
                    <?php if(empty($recent)): ?>
                    <div class="empty-row">
                        <i class="fa-solid fa-inbox"></i>
                        You haven't applied to any scholarship yet.
                    </div>
                    <?php else: ?>
                    <?php foreach($recent as $r):
                        $st = strtolower($r['status']);
                        $icon = match($st) {
                            'approved' => 'fa-circle-check',
                            'rejected' => 'fa-circle-xmark',
                            default    => 'fa-clock'
                        };
                    ?>
                    <div class="history-item">
                        <div class="history-icon <?= $st ?>">
                            <i class="fa-solid <?= $icon ?>"></i>
                        </div>
                        <div class="history-info">
                            <div class="history-name"><?= htmlspecialchars($r['scholarship_name']) ?></div>
                            <div class="history-date">
                                <i class="fa-regular fa-calendar"></i>
                                <?= date("M d, Y", strtotime($r['date_applied'])) ?>
                            </div>
                        </div>
                        <span class="status-badge <?= $st ?>"><?= ucfirst($r['status']) ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /bottom-grid -->
    </div>
</div>

</body>
</html>