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
        ORDER BY student_id ASC
    ");
    $search_param = "%".$search."%";
    $query->bind_param("s", $search_param);
} else {
    $query = $con->prepare("SELECT * FROM students ORDER BY student_id ASC");
}

$query->execute();
$result = $query->get_result();
?>

<?php require_once("header.php"); ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scholars List</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --bg:            #F5F7FB;
    --surface:       #FFFFFF;
    --surface-2:     #EFF2F8;
    --border:        rgba(0,0,0,0.07);
    --border-strong: rgba(0,0,0,0.12);
    --text-primary:  #111318;
    --text-secondary:#565C72;
    --text-muted:    #9197AD;
    --accent:        #2B57F5;
    --accent-light:  #EEF1FE;
    --accent-hover:  #1E44D8;
    --green:         #15803D;
    --green-bg:      #DCFCE7;
    --amber:         #B45309;
    --amber-bg:      #FEF3C7;
    --red:           #B91C1C;
    --red-bg:        #FEE2E2;
    --radius-sm:     6px;
    --radius-md:     10px;
    --radius-lg:     16px;
    --shadow-card:   0 1px 3px rgba(0,0,0,0.05), 0 6px 20px rgba(0,0,0,0.05);
    --transition:    0.16s ease;
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
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 32px;
}

.page-title-group {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.page-eyebrow {
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.09em;
    text-transform: uppercase;
    color: var(--accent);
}

.page-title {
    font-family: 'Syne', sans-serif;
    font-size: 28px;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
}

.page-subtitle {
    font-size: 14px;
    color: var(--text-secondary);
    margin-top: 4px;
}

/* ===== TOOLBAR ===== */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}

.record-pill {
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    background: var(--surface-2);
    padding: 5px 14px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.record-pill i { font-size: 14px; color: var(--text-muted); }

/* ===== SEARCH ===== */
.search-form {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 12px;
    color: var(--text-muted);
    font-size: 16px;
    pointer-events: none;
}

.search-input {
    padding: 10px 90px 10px 38px;
    border: 1.5px solid var(--border-strong);
    border-radius: var(--radius-md);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    background: var(--surface);
    width: 280px;
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition);
}

.search-input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(43,87,245,0.11);
}

.search-input::placeholder { color: var(--text-muted); }

.search-submit {
    position: absolute;
    right: 6px;
    background: var(--accent);
    border: none;
    color: #fff;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: background var(--transition);
}

.search-submit:hover { background: var(--accent-hover); }

/* ===== TABLE CARD ===== */
.table-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    border: 1px solid var(--border);
    overflow: hidden;
}

.table-scroll { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
}

thead tr {
    background: var(--surface-2);
    border-bottom: 1.5px solid var(--border-strong);
}

th {
    padding: 13px 18px;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-secondary);
    text-align: left;
    white-space: nowrap;
}

td {
    padding: 15px 18px;
    font-size: 14px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

tbody tr:last-child td { border-bottom: none; }

tbody tr { transition: background var(--transition); }

tbody tr:hover { background: #FAFBFF; }

/* ===== ID CELL ===== */
.id-chip {
    display: inline-block;
    font-size: 12.5px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    color: var(--black);
    background: var(--black-light);
    padding: 4px 10px;
    border-radius: var(--radius-sm);
    letter-spacing: 0.04em;
}

/* ===== AVATAR + NAME ===== */
.name-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--black-light);
    color: var(--black);
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.avatar-colors {
    --c0: #EEF1FE; --t0: #2B57F5;
    --c1: #DCFCE7; --t1: #15803D;
    --c2: #FEF3C7; --t2: #B45309;
    --c3: #FEE2E2; --t3: #B91C1C;
    --c4: #EDE9FE; --t4: #6D28D9;
}

.avatar[data-color="0"] { background: var(--c0); color: var(--t0); }
.avatar[data-color="1"] { background: var(--c1); color: var(--t1); }
.avatar[data-color="2"] { background: var(--c2); color: var(--t2); }
.avatar[data-color="3"] { background: var(--c3); color: var(--t3); }
.avatar[data-color="4"] { background: var(--c4); color: var(--t4); }

.student-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
}

/* ===== COURSE / SECTION ===== */
.course-text {
    font-size: 13.5px;
    color: var(--text-secondary);
}

.section-badge {
    display: inline-block;
    font-size: 12px;
    font-weight: 600;
    color: var(--black);
    background: var(--black-bg);
    padding: 3px 9px;
    border-radius: 20px;
}

/* ===== ADDRESS ===== */
.address-cell {
    display: flex;
    align-items: flex-start;
    gap: 5px;
    max-width: 200px;
}

.address-cell i {
    color: var(--text-muted);
    font-size: 14px;
    flex-shrink: 0;
    margin-top: 1px;
}

.address-text {
    font-size: 13.5px;
    color: var(--text-secondary);
    line-height: 1.45;
}

/* ===== ACTION BUTTONS ===== */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 5px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 11px;
    border-radius: var(--radius-sm);
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    border: 1.5px solid transparent;
    transition: all var(--transition);
    white-space: nowrap;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    background: none;
}

.action-btn i { font-size: 14px; }

.btn-view {
    background: var(--accent-light);
    color: var(--accent);
    border-color: rgba(43,87,245,0.14);
}
.btn-view:hover { background: var(--accent); color: #fff; border-color: var(--accent); }

.btn-edit {
    background: var(--amber-bg);
    color: var(--amber);
    border-color: rgba(180,83,9,0.14);
}
.btn-edit:hover { background: var(--amber); color: #fff; border-color: var(--amber); }

.btn-delete {
    background: var(--red-bg);
    color: var(--red);
    border-color: rgba(185,28,28,0.14);
}
.btn-delete:hover { background: var(--red); color: #fff; border-color: var(--red); }

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    padding: 64px 20px;
}

.empty-icon {
    width: 58px;
    height: 58px;
    background: var(--surface-2);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.empty-icon i { font-size: 26px; color: var(--text-muted); }

.empty-title {
    font-family: 'Syne', sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 6px;
}

.empty-desc {
    font-size: 13.5px;
    color: var(--text-secondary);
}

/* ===== TABLE FOOTER ===== */
.table-footer {
    padding: 13px 20px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
    font-size: 12.5px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .page-wrapper { padding: 24px 16px 40px; }
    .search-input { width: 220px; }
    .page-title { font-size: 22px; }
}

</style>
</head>

<body>
<div class="page-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-title-group">
            <span class="page-eyebrow">Administration</span>
            <h1 class="page-title">User Management</h1>
            <p class="page-subtitle">View, search, and manage enrolled student scholars.</p>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="record-pill">
            <i class="ti ti-users"></i>
            <?php $count = $result->num_rows; ?>
            <?php echo $count; ?> <?php echo $count == 1 ? 'student' : 'students'; ?>
            <?php if($search != ''): ?>
                &nbsp;&mdash;&nbsp;matching &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;
            <?php endif; ?>
        </div>

        <form method="GET" class="search-form">
            <i class="ti ti-search search-icon"></i>
            <input
                class="search-input"
                type="text"
                name="search"
                placeholder="Search by name…"
                value="<?php echo htmlspecialchars($search); ?>"
                autocomplete="off">
            <button type="submit" class="search-submit">Search</button>
        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>School ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Year &amp; Section</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if($result->num_rows > 0): ?>
            <?php $i = 0; while($row = mysqli_fetch_assoc($result)): $color = $i % 5; $i++; ?>

            <?php
                $initials = strtoupper(
                    substr($row['first_name'], 0, 1) .
                    substr($row['last_name'],  0, 1)
                );
            ?>

            <tr>

                <!-- ID -->
                <td>
                    <span class="id-chip"><?php echo htmlspecialchars($row['student_id']); ?></span>
                </td>

                <!-- NAME -->
                <td>
                    <div class="name-cell">
                        <div class="avatar" data-color="<?php echo $color; ?>">
                            <?php echo $initials; ?>
                        </div>
                        <span class="student-name">
                            <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                        </span>
                    </div>
                </td>

                <!-- COURSE -->
                <td>
                    <span class="course-text"><?php echo htmlspecialchars($row['course']); ?></span>
                </td>

                <!-- YEAR/SECTION -->
                <td>
                    <span class="section-badge"><?php echo htmlspecialchars($row['year_section']); ?></span>
                </td>

                <!-- ADDRESS -->
                <td>
                    <div class="address-cell">
                        <i class="ti ti-map-pin"></i>
                        <span class="address-text"><?php echo htmlspecialchars($row['address']); ?></span>
                    </div>
                </td>

                <!-- ACTIONS -->
                <td>
                    <div class="actions-cell">
                        <a href="view_usermgt.php?id=<?php echo $row['student_id']; ?>" class="action-btn btn-view">
                            <i class="ti ti-eye"></i> View
                        </a>
                        
                        <a href="delete_usermgt.php?id=<?php echo $row['student_id']; ?>"
                           class="action-btn btn-delete"
                           onclick="return confirm('Delete this student record?')">
                            <i class="ti ti-trash"></i> Delete
                        </a>
                    </div>
                </td>

            </tr>

            <?php endwhile; ?>
            <?php else: ?>

            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="ti ti-user-off"></i>
                        </div>
                        <p class="empty-title">No students found</p>
                        <p class="empty-desc">
                            <?php if($search != ''): ?>
                                No results for &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;. Try a different name.
                            <?php else: ?>
                                No student records are available yet.
                            <?php endif; ?>
                        </p>
                    </div>
                </td>
            </tr>

            <?php endif; ?>

            </tbody>
        </table>
        </div>

        <div class="table-footer">
            <i class="ti ti-info-circle" style="font-size:14px;"></i>
            Sorted by Student ID &mdash; ascending
        </div>
    </div>

</div>
</body>
</html>