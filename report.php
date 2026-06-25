<?php
session_start();
require_once("dbconfig.php");
require_once("header.php");

$applications = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form");
$users        = mysqli_query($con, "SELECT COUNT(*) AS total FROM students");
$scholarships = mysqli_query($con, "SELECT COUNT(*) AS total FROM scholarship");
$approved     = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Approved'");
$rejected     = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Rejected'");
$pending      = mysqli_query($con, "SELECT COUNT(*) AS total FROM applications_form WHERE status='Pending'");

$applications = mysqli_fetch_assoc($applications);
$users        = mysqli_fetch_assoc($users);
$scholarships = mysqli_fetch_assoc($scholarships);
$approved     = mysqli_fetch_assoc($approved);
$rejected     = mysqli_fetch_assoc($rejected);
$pending      = mysqli_fetch_assoc($pending);

$total     = (int)$applications['total'];
$app_approved = (int)$approved['total'];
$app_rejected = (int)$rejected['total'];
$app_pending  = (int)$pending['total'];
$pct_approved = $total > 0 ? round(($app_approved / $total) * 100) : 0;
$pct_rejected = $total > 0 ? round(($app_rejected / $total) * 100) : 0;
$pct_pending  = $total > 0 ? round(($app_pending  / $total) * 100) : 0;

$now = date("F d, Y  h:i A");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Reports</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

 <style>

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:           #F5F7FB;
        --surface:      #FFFFFF;
        --surface-2:    #EFF2F8;
        --border:       rgba(0,0,0,0.07);
        --border-md:    rgba(0,0,0,0.11);
        --text-primary: #111318;
        --text-secondary:#565C72;
        --text-muted:   #9197AD;

        --blue:         #2B57F5;
        --blue-bg:      #EEF1FE;
        --blue-dark:    #1E44D8;

        --green:        #15803D;
        --green-bg:     #DCFCE7;
        --green-mid:    #16A34A;

        --red:          #B91C1C;
        --red-bg:       #FEE2E2;

        --amber:        #B45309;
        --amber-bg:     #FEF3C7;

        --purple:       #6D28D9;
        --purple-bg:    #EDE9FE;

        --teal:         #0F766E;
        --teal-bg:      #CCFBF1;

        --radius-sm:    6px;
        --radius-md:    10px;
        --radius-lg:    16px;
        --shadow:       0 1px 3px rgba(0,0,0,0.05), 0 6px 20px rgba(0,0,0,0.05);
        --transition:   0.16s ease;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        color: var(--text-primary);
        min-height: 100vh;
    }

    /* ===== LAYOUT ===== */
    .page-wrapper {
        flex: 1;
        padding: 32px 36px;
        max-width: 1400px;
        margin-left: 260px;
    }

    /* ===== PAGE HEADER ===== */
    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 36px;
    }

    .page-title-group { display: flex; flex-direction: column; gap: 4px; }

    .page-eyebrow {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--blue);
    }

    .page-title {
        font-family: 'Syne', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .page-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .page-meta i { font-size: 14px; }

    /* ===== PRINT BUTTON ===== */
    .btn-print {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--blue);
        color: #fff;
        border: none;
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 500;
        padding: 10px 18px;
        border-radius: var(--radius-md);
        cursor: pointer;
        text-decoration: none;
        transition: background var(--transition), transform var(--transition);
        box-shadow: 0 1px 3px rgba(0,0,0,0.14);
        white-space: nowrap;
    }

    .btn-print i { font-size: 17px; }
    .btn-print:hover { background: var(--blue-dark); transform: translateY(-1px); }

    /* ===== SECTION LABEL ===== */
    .section-label {
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-md);
    }

    /* ===== METRIC CARDS GRID ===== */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 36px;
    }

    .metric-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 22px 22px 18px;
        box-shadow: var(--shadow);
        position: relative;
        overflow: hidden;
        transition: transform var(--transition), box-shadow var(--transition);
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 24px rgba(0,0,0,0.09);
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: var(--radius-lg) var(--radius-lg) 0 0;
    }

    .metric-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    .metric-icon i { font-size: 19px; }

    .c-blue   .metric-icon { background: var(--blue-bg);   color: var(--blue); }
    .c-green  .metric-icon { background: var(--green-bg);  color: var(--green); }
    .c-red    .metric-icon { background: var(--red-bg);    color: var(--red); }
    .c-amber  .metric-icon { background: var(--amber-bg);  color: var(--amber); }
    .c-purple .metric-icon { background: var(--purple-bg); color: var(--purple); }
    .c-teal   .metric-icon { background: var(--teal-bg);   color: var(--teal); }

    .metric-value {
        font-family: 'Syne', sans-serif;
        font-size: 32px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1;
        margin-bottom: 5px;
        font-variant-numeric: tabular-nums;
    }

    .metric-label {
        font-size: 13px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .metric-sub {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid var(--border);
        font-size: 12px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .metric-sub i { font-size: 13px; }

    /* ===== BAR PROGRESS ===== */
    .progress-section {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        padding: 26px 28px;
        margin-bottom: 36px;
    }

    .progress-section h3 {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 22px;
        color: var(--text-primary);
    }

    .progress-row {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }

    .progress-row:last-child { margin-bottom: 0; }

    .progress-label {
        width: 160px;
        font-size: 13.5px;
        color: var(--text-secondary);
        font-weight: 500;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .progress-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .progress-dot.green  { background: var(--green-mid); }
    .progress-dot.red    { background: var(--red); }
    .progress-dot.amber  { background: var(--amber); }

    .progress-bar-track {
        flex: 1;
        height: 8px;
        background: var(--surface-2);
        border-radius: 20px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 20px;
        transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .progress-bar-fill.green  { background: var(--green-mid); }
    .progress-bar-fill.red    { background: var(--red); }
    .progress-bar-fill.amber  { background: #F59E0B; }

    .progress-pct {
        width: 38px;
        text-align: right;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-primary);
        font-variant-numeric: tabular-nums;
        flex-shrink: 0;
    }

    .progress-count {
        width: 50px;
        text-align: right;
        font-size: 12.5px;
        color: var(--text-muted);
        flex-shrink: 0;
    }

    /* ===== SUMMARY TABLE ===== */
    .report-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .report-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 26px;
        border-bottom: 1px solid var(--border-md);
        background: var(--surface-2);
    }

    .report-card-title {
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .report-card-title i { font-size: 17px; color: var(--blue); }

    .generated-badge {
        font-size: 12px;
        color: var(--text-muted);
        background: var(--surface);
        border: 1px solid var(--border-md);
        padding: 4px 10px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .generated-badge i { font-size: 13px; }

    table { width: 100%; border-collapse: collapse; }

    thead tr { background: var(--surface-2); }

    th {
        padding: 13px 22px;
        font-size: 11.5px;
        font-weight: 600;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        color: var(--text-secondary);
        text-align: left;
        border-bottom: 1.5px solid var(--border-md);
    }

    td {
        padding: 15px 22px;
        font-size: 14px;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }

    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #FAFBFF; }

    .row-icon {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .row-icon-wrap {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .row-icon-wrap i { font-size: 15px; }

    .count-badge {
        font-size: 15px;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .chip-blue   { background: var(--blue-bg);   color: var(--blue); }
    .chip-green  { background: var(--green-bg);  color: var(--green); }
    .chip-red    { background: var(--red-bg);    color: var(--red); }
    .chip-amber  { background: var(--amber-bg);  color: var(--amber); }
    .chip-purple { background: var(--purple-bg); color: var(--purple); }
    .chip-teal   { background: var(--teal-bg);   color: var(--teal); }

    .table-footer {
        padding: 13px 22px;
        background: var(--surface-2);
        border-top: 1px solid var(--border);
        font-size: 12.5px;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ===== PRINT STYLES ===== */
    @media print {

        body { background: #fff; }

        .page-wrapper { padding: 20px; max-width: 100%; }

        .btn-print,
        .metric-card:hover { display: none; transform: none; }

        .metrics-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; }

        .metric-card { box-shadow: none; border: 1px solid #ddd; break-inside: avoid; }

        .progress-section,
        .report-card { box-shadow: none; border: 1px solid #ddd; }

        .page-header { margin-bottom: 24px; }

        @page { margin: 1.5cm; }
    }

    @media (max-width: 768px) {
        .page-wrapper { padding: 24px 16px 40px; }
        .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        .progress-label { width: 120px; font-size: 12.5px; }
        .page-title { font-size: 22px; }
 }

</style>
 </head>
<body>

<div class="page-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title-group">
            <span class="page-eyebrow">Overview</span>
            <h1 class="page-title">System Reports</h1>
            <p class="page-meta">
                <i class="ti ti-clock"></i>
                Generated <?php echo $now; ?>
            </p>
        </div>
        <button class="btn-print" onclick="window.print()">
            <i class="ti ti-printer"></i>
            export  Report
        </button>
    </div>

    <!-- METRIC CARDS -->
    <p class="section-label">Key Metrics</p>
    <div class="metrics-grid">

        <div class="metric-card c-blue">
            <div class="metric-icon"><i class="ti ti-files"></i></div>
            <div class="metric-value"><?php echo number_format($total); ?></div>
            <div class="metric-label">Total Applications</div>
            <div class="metric-sub">
                <i class="ti ti-chart-bar"></i>
                All-time submissions
            </div>
        </div>

        <div class="metric-card c-green">
            <div class="metric-icon"><i class="ti ti-circle-check"></i></div>
            <div class="metric-value"><?php echo number_format($app_approved); ?></div>
            <div class="metric-label">Approved</div>
            <div class="metric-sub">
                <i class="ti ti-percentage"></i>
                <?php echo $pct_approved; ?>% of total
            </div>
        </div>

        <div class="metric-card c-red">
            <div class="metric-icon"><i class="ti ti-circle-x"></i></div>
            <div class="metric-value"><?php echo number_format($app_rejected); ?></div>
            <div class="metric-label">Rejected</div>
            <div class="metric-sub">
                <i class="ti ti-percentage"></i>
                <?php echo $pct_rejected; ?>% of total
            </div>
        </div>

        <div class="metric-card c-amber">
            <div class="metric-icon"><i class="ti ti-clock-hour-4"></i></div>
            <div class="metric-value"><?php echo number_format($app_pending); ?></div>
            <div class="metric-label">Pending</div>
            <div class="metric-sub">
                <i class="ti ti-percentage"></i>
                <?php echo $pct_pending; ?>% of total
            </div>
        </div>

        <div class="metric-card c-purple">
            <div class="metric-icon"><i class="ti ti-users"></i></div>
            <div class="metric-value"><?php echo number_format($users['total']); ?></div>
            <div class="metric-label">Total Users</div>
            <div class="metric-sub">
                <i class="ti ti-school"></i>
                Enrolled students
            </div>
        </div>

        <div class="metric-card c-teal">
            <div class="metric-icon"><i class="ti ti-award"></i></div>
            <div class="metric-value"><?php echo number_format($scholarships['total']); ?></div>
            <div class="metric-label">Scholarships</div>
            <div class="metric-sub">
                <i class="ti ti-list"></i>
                Active programs
            </div>
        </div>

    </div>

    <!-- APPLICATION BREAKDOWN BAR -->
    <p class="section-label">Application Breakdown</p>
    <div class="progress-section">
        <h3>Application Status Distribution</h3>

        <div class="progress-row">
            <div class="progress-label">
                <span class="progress-dot green"></span> Approved
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill green" style="width:<?php echo $pct_approved; ?>%"></div>
            </div>
            <span class="progress-pct"><?php echo $pct_approved; ?>%</span>
            <span class="progress-count"><?php echo $app_approved; ?></span>
        </div>

        <div class="progress-row">
            <div class="progress-label">
                <span class="progress-dot red"></span> Rejected
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill red" style="width:<?php echo $pct_rejected; ?>%"></div>
            </div>
            <span class="progress-pct"><?php echo $pct_rejected; ?>%</span>
            <span class="progress-count"><?php echo $app_rejected; ?></span>
        </div>

        <div class="progress-row">
            <div class="progress-label">
                <span class="progress-dot amber"></span> Pending
            </div>
            <div class="progress-bar-track">
                <div class="progress-bar-fill amber" style="width:<?php echo $pct_pending; ?>%"></div>
            </div>
            <span class="progress-pct"><?php echo $pct_pending; ?>%</span>
            <span class="progress-count"><?php echo $app_pending; ?></span>
        </div>

    </div>

    <!-- SUMMARY TABLE -->
    <p class="section-label">Full Summary</p>
    <div class="report-card">

        <div class="report-card-header">
            <div class="report-card-title">
                <i class="ti ti-report-analytics"></i>
                Scholarship Management Report
            </div>
            <div class="generated-badge">
                <i class="ti ti-calendar-event"></i>
                <?php echo date("M d, Y"); ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Category</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--blue-bg);">
                                <i class="ti ti-files" style="color:var(--blue);"></i>
                            </div>
                            Total Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-blue">Applications</span></td>
                    <td><span class="count-badge"><?php echo number_format($total); ?></span></td>
                </tr>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--green-bg);">
                                <i class="ti ti-circle-check" style="color:var(--green);"></i>
                            </div>
                            Approved Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-green">Approved</span></td>
                    <td><span class="count-badge"><?php echo number_format($app_approved); ?></span></td>
                </tr>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--red-bg);">
                                <i class="ti ti-circle-x" style="color:var(--red);"></i>
                            </div>
                            Rejected Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-red">Rejected</span></td>
                    <td><span class="count-badge"><?php echo number_format($app_rejected); ?></span></td>
                </tr>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--amber-bg);">
                                <i class="ti ti-clock-hour-4" style="color:var(--amber);"></i>
                            </div>
                            Pending Applications
                        </div>
                    </td>
                    <td><span class="status-chip chip-amber">Pending</span></td>
                    <td><span class="count-badge"><?php echo number_format($app_pending); ?></span></td>
                </tr>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--purple-bg);">
                                <i class="ti ti-users" style="color:var(--purple);"></i>
                            </div>
                            Total Users
                        </div>
                    </td>
                    <td><span class="status-chip chip-purple">Users</span></td>
                    <td><span class="count-badge"><?php echo number_format($users['total']); ?></span></td>
                </tr>

                <tr>
                    <td>
                        <div class="row-icon">
                            <div class="row-icon-wrap" style="background:var(--teal-bg);">
                                <i class="ti ti-award" style="color:var(--teal);"></i>
                            </div>
                            Total Scholarships
                        </div>
                    </td>
                    <td><span class="status-chip chip-teal">Scholarships</span></td>
                    <td><span class="count-badge"><?php echo number_format($scholarships['total']); ?></span></td>
                </tr>

            </tbody>
        </table>

        <div class="table-footer">
            <i class="ti ti-info-circle" style="font-size:14px;"></i>
            Data reflects current database state &mdash; <?php echo $now; ?>
        </div>

    </div>

</div>
</body>
</html>