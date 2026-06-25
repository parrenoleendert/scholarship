<?php
session_start();
require_once("dbconfig.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

$query = "SELECT a.*, s.scholarship_name 
          FROM applications_form a
          JOIN scholarship s ON a.sid = s.sid
          WHERE a.id = ?
          ORDER BY a.date_applied DESC";

$stmt = $con->prepare($query);

if(!$stmt){
    die("SQL Error: " . $con->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// collect rows + counts
$all_rows = [];
$counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while($row = $result->fetch_assoc()){
    $all_rows[] = $row;
    $s = strtolower($row['status']);
    if(isset($counts[$s])) $counts[$s]++;
}
?>

<?php require_once("headers.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application History</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w:     248px;
            --topbar-h:      64px;
            --accent:        #20d296;
            --accent2:       #3b7cf4;
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
            padding: 30px 32px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 22px;
            flex-wrap: wrap;
            gap: 14px;
        }

        .page-eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.3px;
            text-transform: uppercase;
            color: var(--accent);
            margin-bottom: 4px;
        }

        .page-heading {
            font-family: 'Sora', sans-serif;
            font-size: 22px;
            color: var(--text);
        }

        .page-sub {
            font-size: 13px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ── SUMMARY CHIPS ── */
        .summary-strip {
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .strip-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 11px;
            padding: 11px 18px;
        }

        .chip-icon {
            width: 34px; height: 34px;
            border-radius: 9px;
            display: grid; place-items: center;
            font-size: 13px;
            flex-shrink: 0;
        }

        .chip-icon.total    { background: rgba(59,124,244,0.10); color: var(--accent2); }
        .chip-icon.pending  { background: var(--pending-bg);  color: var(--pending-txt); }
        .chip-icon.approved { background: var(--approved-bg); color: var(--approved-txt); }
        .chip-icon.rejected { background: var(--rejected-bg); color: var(--rejected-txt); }

        .chip-label { font-size: 11.5px; color: var(--muted); }
        .chip-val   { font-size: 15px; font-weight: 700; color: var(--text); line-height: 1.2; }

        /* ── PANEL ── */
        .panel {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            animation: fadeUp 0.35s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
            background: #fafbfe;
            flex-wrap: wrap;
            gap: 10px;
        }

        .panel-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .panel-title i { color: var(--accent); }

        .result-count {
            font-size: 12px;
            color: var(--muted);
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 3px 12px;
        }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 660px;
        }

        thead tr {
            background: #f6f8fe;
            border-bottom: 1px solid var(--border);
        }

        thead th {
            padding: 11px 18px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.9px;
            text-transform: uppercase;
            color: var(--muted);
            white-space: nowrap;
        }

        tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }

        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #f5f8ff; }

        td {
            padding: 14px 18px;
            font-size: 13.5px;
            color: var(--text);
            vertical-align: middle;
        }

        /* School ID */
        .id-cell {
            font-family: monospace;
            font-size: 13px;
            color: var(--accent2);
            font-weight: 600;
            background: rgba(59,124,244,0.07);
            border-radius: 6px;
            padding: 3px 8px;
            display: inline-block;
        }

        /* Name cell */
        .name-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .name-avatar {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--accent), #13a574);
            display: grid; place-items: center;
            font-size: 12px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }

        .name-full { font-weight: 600; font-size: 13.5px; }

        /* Scholarship */
        .schol-cell {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 13px;
        }

        .schol-cell i { color: var(--accent); font-size: 12px; }

        /* Date */
        .date-cell {
            font-size: 13px;
            color: var(--muted);
            white-space: nowrap;
        }

        .date-cell i { margin-right: 5px; font-size: 11px; }

        /* Status badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 700;
            white-space: nowrap;
        }

        .status-badge::before {
            content: '';
            width: 6px; height: 6px;
            border-radius: 50%;
        }

        .status-badge.pending  { background: var(--pending-bg);  color: var(--pending-txt); }
        .status-badge.pending::before  { background: var(--pending-txt); }
        .status-badge.approved { background: var(--approved-bg); color: var(--approved-txt); }
        .status-badge.approved::before { background: var(--approved-txt); }
        .status-badge.rejected { background: var(--rejected-bg); color: var(--rejected-txt); }
        .status-badge.rejected::before { background: var(--rejected-txt); }

        /* Empty state */
        .empty-state {
            padding: 56px 20px;
            text-align: center;
        }

        .empty-icon {
            width: 64px; height: 64px;
            border-radius: 16px;
            background: var(--bg);
            display: grid; place-items: center;
            font-size: 26px;
            color: var(--border);
            margin: 0 auto 14px;
        }

        .empty-state p { color: var(--muted); font-size: 14px; }
        .empty-state small { font-size: 12.5px; color: var(--border); }

        @media (max-width: 768px) {
            .main { margin-left: 0; }
            .page-body { padding: 20px 16px; }
        }
    </style>
</head>
<body>

<div class="main">
    <div class="page-body">

        <!-- Page header -->
        <div class="page-header">
            <div>
                
                <div class="page-heading">Application History</div>
                <div class="page-sub">Track the status of all your submitted scholarship applications</div>
            </div>
        </div>

        <!-- Summary chips -->
        <div class="summary-strip">
            <div class="strip-chip">
                <div class="chip-icon total"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="chip-label">Total</div>
                    <div class="chip-val"><?= count($all_rows) ?></div>
                </div>
            </div>
            <div class="strip-chip">
                <div class="chip-icon pending"><i class="fa-solid fa-clock"></i></div>
                <div>
                    <div class="chip-label">Pending</div>
                    <div class="chip-val"><?= $counts['pending'] ?></div>
                </div>
            </div>
            <div class="strip-chip">
                <div class="chip-icon approved"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="chip-label">Approved</div>
                    <div class="chip-val"><?= $counts['approved'] ?></div>
                </div>
            </div>
            <div class="strip-chip">
                <div class="chip-icon rejected"><i class="fa-solid fa-circle-xmark"></i></div>
                <div>
                    <div class="chip-label">Rejected</div>
                    <div class="chip-val"><?= $counts['rejected'] ?></div>
                </div>
            </div>
        </div>

        <!-- Table panel -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Submitted Applications
                </div>
                <span class="result-count"><?= count($all_rows) ?> record<?= count($all_rows) !== 1 ? 's' : '' ?></span>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>School ID</th>
                            <th>Name</th>
                            <th>Scholarship</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($all_rows)): ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-folder-open"></i></div>
                                    <p>No applications submitted yet.</p>
                                    <small>Your application history will appear here once you apply.</small>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach($all_rows as $row):
                            $status = strtolower($row['status']);
                            $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1));
                        ?>
                        <tr>
                            <td><span class="id-cell"><?= htmlspecialchars($row['school_id']) ?></span></td>

                            <td>
                                <div class="name-cell">
                                    <div class="name-full">
                                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="schol-cell">
                                   
                                    <?= htmlspecialchars($row['scholarship_name']) ?>
                                </div>
                            </td>

                            <td>
                                <div class="date-cell">
                                    
                                    <?= date("F d, Y", strtotime($row['date_applied'])) ?>
                                </div>
                            </td>

                            <td>
                                <span class="status-badge <?= $status ?>">
                                    <?= htmlspecialchars($row['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

</body>
</html>