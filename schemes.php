<?php
session_start();
require_once("dbconfig.php");

$student_id = $_SESSION['id'];

// Check if the student already has an APPROVED application
// (If yes, the Apply button must be disabled for all scholarships)
$checkScholar = $con->prepare("\n    SELECT 1\n    FROM applications_form\n    WHERE id = ?\n      AND status = 'Approved'\n    LIMIT 1\n");
$checkScholar->bind_param("i", $student_id);
$checkScholar->execute();
$approvedScholar = $checkScholar->get_result()->num_rows > 0;





$query = "SELECT * FROM scholarship ORDER BY deadline ASC";
$result = mysqli_query($con, $query);

require_once("headers.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --sidebar-w: 248px;
            --topbar-h:  64px;
            --accent:    #20d296;
            --accent2:   #3b7cf4;
            --bg:        #f1f4fb;
            --card:      #ffffff;
            --text:      #111d2e;
            --muted:     #8494ae;
            --border:    #e4e9f4;
            --radius:    13px;
            --open-bg:   #dcfdf2;
            --open-txt:  #0a7c54;
            --closed-bg: #ffe4e8;
            --closed-txt:#c0103a;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        /* ── MAIN AREA ── */
        .main {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }

        .page-body {
            padding: 30px 32px;
        }

        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 24px;
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

        /* Search bar */
        .search-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 9px 14px;
            font-family: inherit;
            font-size: 13.5px;
            color: var(--text);
            width: 220px;
            transition: border-color 0.2s;
        }

        .search-box:focus-within { border-color: var(--accent); }
        .search-box i { color: var(--muted); font-size: 13px; }
        .search-box input {
            border: none; outline: none;
            background: transparent;
            font-family: inherit;
            font-size: 13.5px;
            color: var(--text);
            width: 100%;
        }
        .search-box input::placeholder { color: var(--muted); }

        /* ── SUMMARY STRIPS ── */
        .summary-strip {
            display: flex;
            gap: 14px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .strip-chip {
            display: flex;
            align-items: center;
            gap: 9px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 13px;
        }

        .strip-chip .chip-icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: grid; place-items: center;
            font-size: 13px;
        }

        .chip-icon.total   { background: rgba(59,124,244,0.1); color: var(--accent2); }
        .chip-icon.open    { background: var(--open-bg);  color: var(--open-txt); }
        .chip-icon.closed  { background: var(--closed-bg); color: var(--closed-txt); }

        .chip-label { color: var(--muted); font-size: 11.5px; }
        .chip-val   { font-weight: 700; font-size: 15px; color: var(--text); }

        /* ── TABLE PANEL ── */
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
            padding: 3px 11px;
        }

        /* Table */
        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 680px;
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

        /* Scholarship name cell */
        .schol-cell {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .schol-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(32,210,150,0.1);
            display: grid; place-items: center;
            flex-shrink: 0;
            font-size: 15px;
            color: var(--accent);
        }

        .schol-name {
            font-weight: 600;
            font-size: 13.5px;
            color: var(--text);
        }

        .schol-id {
            font-size: 11.5px;
            color: var(--muted);
            margin-top: 1px;
        }

        /* Provider */
        .provider-cell {
            display: flex;
            align-items: center;
            gap: 7px;
            color: var(--muted);
            font-size: 13px;
        }

        .provider-cell i { font-size: 12px; color: var(--accent2); }

        /* Deadline */
        .deadline-cell { font-size: 13px; white-space: nowrap; }
        .deadline-cell .deadline-soon { color: #e85d04; font-weight: 600; }
        .deadline-cell .deadline-ok   { color: var(--text); }
        .deadline-cell .deadline-none { color: var(--muted); font-style: italic; }

        /* Amount */
        .amount-cell {
            font-weight: 700;
            font-size: 14px;
            color: var(--text);
            white-space: nowrap;
        }

        .amount-cell small {
            font-weight: 400;
            font-size: 11px;
            color: var(--muted);
            display: block;
        }

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

        .status-open   { background: var(--open-bg);   color: var(--open-txt); }
        .status-open::before   { background: var(--open-txt); }
        .status-closed { background: var(--closed-bg); color: var(--closed-txt); }
        .status-closed::before { background: var(--closed-txt); }

        /* Action buttons */
        .actions { display: flex; gap: 8px; align-items: center; flex-wrap: nowrap; }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.18s;
        }

        .btn-details {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
        }
        .btn-details:hover { border-color: var(--accent2); color: var(--accent2); }

        .btn-apply {
            background: var(--accent);
            color: #fff;
            box-shadow: 0 3px 10px rgba(32,210,150,0.3);
        }
        .btn-apply:hover { background: #14b87f; }

        .btn-disabled {
            background: #f1f4fb;
            border: 1px solid var(--border);
            color: var(--muted);
            cursor: not-allowed;
            opacity: 0.75;
        }

        /* Empty state */
        .empty-state {
            padding: 52px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 40px;
            color: var(--border);
            margin-bottom: 14px;
        }

        .empty-state p {
            color: var(--muted);
            font-size: 14px;
        }

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
                
                <div class="page-heading">Available Scholarships</div>
                <div class="page-sub">Apply for scholarships open to you</div>
            </div>
            <div class="search-wrap">
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="searchInput" placeholder="Search scholarships…" onkeyup="filterTable()">
                </div>
            </div>
        </div>

        <!-- Summary chips -->
        <?php
            $all_rows = [];
            $total = 0; $open_count = 0; $closed_count = 0;
            if ($result) {
                while ($r = mysqli_fetch_assoc($result)) {
                    $all_rows[] = $r;
                    $total++;
                    if ($r['status'] == 'Open') $open_count++;
                    else $closed_count++;
                }
            }
        ?>
        <div class="summary-strip">
            <div class="strip-chip">
                <div class="chip-icon total"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <div class="chip-label">Total</div>
                    <div class="chip-val"><?= $total ?></div>
                </div>
            </div>
            <div class="strip-chip">
                <div class="chip-icon open"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="chip-label">Open</div>
                    <div class="chip-val"><?= $open_count ?></div>
                </div>
            </div>
            <div class="strip-chip">
                <div class="chip-icon closed"><i class="fa-solid fa-circle-xmark"></i></div>
                <div>
                    <div class="chip-label">Closed</div>
                    <div class="chip-val"><?= $closed_count ?></div>
                </div>
            </div>
        </div>

        <!-- Table panel -->
        <div class="panel">
            <div class="panel-head">
                <div class="panel-title">
                    
                    Scholarship Listings
                </div>
                <span class="result-count" id="rowCount"><?= $total ?> results</span>
            </div>
            <div class="table-wrap">
                <table id="scholTable">
                    <thead>
                        <tr>
                            <th>Scholarship</th>
                            <th>Provider</th>
                            <th>Deadline</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_rows)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <p>No scholarships available at the moment.</p>
                                </div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($all_rows as $i => $row):
                            $isOpen = $row['status'] == 'Open';

                            // Deadline urgency
                            $deadlineDisplay = '';
                            $deadlineClass   = 'deadline-none';
                            if (!empty($row['deadline'])) {
                                $daysLeft = (strtotime($row['deadline']) - time()) / 86400;
                                $formatted = date("M d, Y", strtotime($row['deadline']));
                                if ($daysLeft < 0) {
                                    $deadlineDisplay = $formatted . ' <small style="color:var(--muted)">(Expired)</small>';
                                    $deadlineClass = 'deadline-none';
                                } elseif ($daysLeft <= 7) {
                                    $deadlineDisplay = $formatted . ' <small style="color:#e85d04">(' . ceil($daysLeft) . ' days left)</small>';
                                    $deadlineClass = 'deadline-soon';
                                } else {
                                    $deadlineDisplay = $formatted;
                                    $deadlineClass = 'deadline-ok';
                                }
                            } else {
                                $deadlineDisplay = 'No Deadline';
                            }
                        ?>
                        <tr>
                            <!-- Scholarship Name -->
                            <td>
                                <div class="schol-cell">
                                    
                                    <div>
                                        <div class="schol-name"><?= htmlspecialchars($row['scholarship_name']) ?></div>
                                       
                                    </div>
                                </div>
                            </td>

                            <!-- Provider -->
                            <td>
                                <div class="provider-cell">
                                    
                                    <?= htmlspecialchars($row['provider']) ?>
                                </div>
                            </td>

                            <!-- Deadline -->
                            <td>
                                <div class="deadline-cell <?= $deadlineClass ?>">
                                    <?= $deadlineDisplay ?>
                                </div>
                            </td>

                            <!-- Amount -->
                            <td>
                                <div class="amount-cell">
                                    ₱<?= number_format($row['amount'], 2) ?>
                                    
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="status-badge <?= $isOpen ? 'status-open' : 'status-closed' ?>">
                                    <?= $isOpen ? 'Open' : 'Closed' ?>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="actions">
                                    <?php if (!empty($row['scholarship_file'])): ?>
                                    <a href="uploads/<?= $row['scholarship_file'] ?>" target="_blank" class="btn btn-details">
                                        <i class="fa-solid fa-file-lines"></i> Details
                                    </a>
                                    <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fa-solid fa-file-slash"></i> No File
                                    </button>
                                    <?php endif; ?>

                                    <?php if ($isOpen): ?>

                                        <?php if($approvedScholar): ?>

                                            <button class="btn btn-disabled"
                                                    onclick="alert('You already have an approved scholarship. Only one scholarship is allowed per student.')">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Already a Scholar
                                            </button>

                                        <?php else: ?>

                                            <a href="application_form.php?id=<?= $row['sid'] ?>" class="btn btn-apply">
                                                <i class="fa-solid fa-paper-plane"></i> Apply
                                            </a>

                                        <?php endif; ?>

                                    <?php else: ?>

                                        <button class="btn btn-disabled" disabled>
                                            <i class="fa-solid fa-lock"></i> Closed
                                        </button>

                                    <?php endif; ?>
                                </div>
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

<script>
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows  = document.querySelectorAll('#scholTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const show = text.includes(input);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('rowCount').textContent = visible + ' results';
}
</script>

</body>
</html>