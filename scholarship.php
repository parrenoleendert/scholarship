<?php
require_once("dbconfig.php");
require_once("header.php");

$search = "";

/* SEARCH */
if(isset($_GET['search']) && $_GET['search'] != ""){

    $search = trim($_GET['search']);
    $search_param = "%".$search."%";

    $stmt = $con->prepare("
        SELECT * FROM scholarship
        WHERE scholarship_name LIKE ?
        OR provider LIKE ?
        ORDER BY sid DESC
    ");

    $stmt->bind_param("ss", $search_param, $search_param);

}else{

    $stmt = $con->prepare("
        SELECT * FROM scholarship
        ORDER BY sid DESC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Scholarship Management</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>

/* ===== RESET ===== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ===== VARIABLES ===== */
:root {
    --bg:            #F6F7FB;
    --surface:       #FFFFFF;
    --surface-2:     #F0F2F8;
    --border:        rgba(0,0,0,0.08);
    --border-strong: rgba(0,0,0,0.14);
    --text-primary:  #12131A;
    --text-secondary:#5A5E72;
    --text-muted:    #9196AB;
    --accent:        #2B57F5;
    --accent-light:  #EEF1FE;
    --accent-hover:  #1E44D8;
    --green:         #16A34A;
    --green-bg:      #DCFCE7;
    --red:           #B91C1C;
    --red-bg:        #FEE2E2;
    --amber:         #B45309;
    --amber-bg:      #FEF3C7;
    --radius-sm:     6px;
    --radius-md:     10px;
    --radius-lg:     16px;
    --shadow-card:   0 1px 3px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.05);
    --shadow-btn:    0 1px 2px rgba(0,0,0,0.12);
    --transition:    0.18s ease;
}

/* ===== BASE ===== */
body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    color: var(--text-primary);
    min-height: 100vh;
}

/* ===== MAIN LAYOUT ===== */
.page-wrapper {
    flex: 1;
      padding: 32px 36px;
      max-width: 1400px;
      margin-left: 260px;
}

/* ===== PAGE HEADER ===== */
.page-header {
    margin-bottom: 32px;
}

.page-header-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.page-title-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.page-eyebrow {
    font-size: 12px;
    font-weight: 500;
    letter-spacing: 0.08em;
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

/* ===== ADD BUTTON ===== */
.btn-add {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--accent);
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    padding: 10px 18px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-btn);
    transition: background var(--transition), transform var(--transition);
    white-space: nowrap;
}

.btn-add:hover {
    background: var(--accent-hover);
    transform: translateY(-1px);
}

.btn-add i {
    font-size: 17px;
}

/* ===== TOOLBAR ===== */
.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.toolbar-left {
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-count {
    font-size: 13px;
    color: var(--text-secondary);
    background: var(--surface-2);
    padding: 5px 12px;
    border-radius: 20px;
    font-weight: 500;
}

/* ===== SEARCH ===== */
.search-wrapper {
    position: relative;
}

.search-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 16px;
    pointer-events: none;
}

.search-input {
    padding: 10px 40px 10px 38px;
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
    box-shadow: 0 0 0 3px rgba(43,87,245,0.12);
}

.search-input::placeholder {
    color: var(--text-muted);
}

.search-btn {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--accent);
    border: none;
    color: #fff;
    padding: 5px 10px;
    border-radius: var(--radius-sm);
    font-size: 13px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    transition: background var(--transition);
}

.search-btn:hover { background: var(--accent-hover); }

/* ===== TABLE CARD ===== */
.table-card {
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-card);
    border: 1px solid var(--border);
    overflow: hidden;
}

.table-scroll {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

/* ===== TABLE HEAD ===== */
thead tr {
    background: var(--surface-2);
    border-bottom: 1.5px solid var(--border-strong);
}

th {
    padding: 13px 18px;
    text-align: left;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    color: var(--text-secondary);
    white-space: nowrap;
}

/* ===== TABLE BODY ===== */
td {
    padding: 16px 18px;
    font-size: 14px;
    color: var(--text-primary);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody tr {
    transition: background var(--transition);
}

tbody tr:hover {
    background: #FAFBFF;
}

/* ===== SCHOLARSHIP NAME ===== */
.scholarship-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text-primary);
    line-height: 1.4;
}

/* ===== PROVIDER ===== */
.provider-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}

.provider-icon {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    background: var(--accent-light);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.provider-icon i {
    color: var(--accent);
    font-size: 15px;
}

.provider-name {
    font-size: 14px;
    color: var(--text-secondary);
}

/* ===== AMOUNT ===== */
.amount-cell {
    font-size: 15px;
    font-weight: 600;
    color: var(--text-primary);
    font-variant-numeric: tabular-nums;
}

/* ===== DEADLINE ===== */
.deadline-cell {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13.5px;
    color: var(--text-secondary);
    white-space: nowrap;
}

.deadline-cell i {
    color: var(--text-muted);
    font-size: 15px;
    flex-shrink: 0;
}

.no-deadline {
    font-size: 13px;
    color: var(--text-muted);
    font-style: italic;
}

/* ===== STATUS BADGE ===== */
.badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

.badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.badge-open {
    background: var(--green-bg);
    color: var(--green);
}

.badge-open .badge-dot { background: var(--green); }

.badge-close {
    background: var(--red-bg);
    color: var(--red);
}

.badge-close .badge-dot { background: var(--red); }

/* ===== ACTION BUTTONS ===== */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 6px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: var(--radius-sm);
    font-size: 12.5px;
    font-weight: 500;
    text-decoration: none;
    transition: all var(--transition);
    white-space: nowrap;
    border: 1.5px solid transparent;
    cursor: pointer;
    background: none;
    font-family: 'DM Sans', sans-serif;
}

.action-btn i { font-size: 14px; }

.btn-file {
    background: var(--accent-light);
    color: var(--accent);
    border-color: rgba(43,87,245,0.15);
}
.btn-file:hover {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
}

.btn-edit {
    background: var(--amber-bg);
    color: var(--amber);
    border-color: rgba(180,83,9,0.15);
}
.btn-edit:hover {
    background: var(--amber);
    color: #fff;
    border-color: var(--amber);
}

.btn-delete {
    background: var(--red-bg);
    color: var(--red);
    border-color: rgba(185,28,28,0.15);
}
.btn-delete:hover {
    background: var(--red);
    color: #fff;
    border-color: var(--red);
}

/* ===== NO FILE LABEL ===== */
.no-file {
    font-size: 13px;
    color: var(--text-muted);
    font-style: italic;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    width: 60px;
    height: 60px;
    background: var(--surface-2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
}

.empty-icon i {
    font-size: 28px;
    color: var(--text-muted);
}

.empty-title {
    font-family: 'Syne', sans-serif;
    font-size: 17px;
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
    padding: 14px 20px;
    background: var(--surface-2);
    border-top: 1px solid var(--border);
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}


/* ===== DELETE MODAL (copy style from scholars_list) ===== */
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
.modal-overlay.active .modal-card {
    transform: translateY(0) scale(1);
    opacity: 1;
}
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
    flex: 1;
    padding: 10px 0;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid transparent;
    cursor: pointer;
    font-family: inherit;
    transition: background .15s, box-shadow .15s, transform .12s;
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
        <div class="modal-title">Delete Scholarship</div>
        <div class="modal-body">
            Are you sure you want to delete <strong id="modalName"></strong>?
            <br/>This action cannot be undone.
        </div>
        <div class="modal-actions">
            <button class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <a href="#" id="modalConfirmBtn" class="modal-btn modal-btn-confirm">Delete</a>
        </div>
    </div>
</div>


<div class="page-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-header-top">
            <div class="page-title-group">
                <span class="page-eyebrow">Management</span>
                <h1 class="page-title">Scholarships</h1>
                <p class="page-subtitle">Browse, search, and manage all available scholarship programs.</p>
            </div>
            <a href="add_scholarship.php" class="btn-add">
                <i class="ti ti-plus"></i>
                Add Scholarship
            </a>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="toolbar-left">
            <?php $count = $result->num_rows; ?>
            <span class="result-count">
                <?php echo $count; ?> <?php echo $count == 1 ? 'record' : 'records'; ?>
                <?php if($search != ""): ?>
                    &nbsp;&mdash;&nbsp;results for &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;
                <?php endif; ?>
            </span>
        </div>

        <form method="GET" class="search-wrapper">
            <i class="ti ti-search"></i>
            <input
                class="search-input"
                type="text"
                name="search"
                placeholder="Search by name or provider…"
                value="<?php echo htmlspecialchars($search); ?>"
                autocomplete="off">
            <button type="submit" class="search-btn">Go</button>
        </form>
    </div>

    <!-- TABLE CARD -->
    <div class="table-card">
        <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>Scholarship</th>
                    <th>Provider</th>
                    <th>Amount</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Requirement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

            <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>

            <tr>

                <!-- SCHOLARSHIP NAME -->
                <td>
                    <span class="scholarship-name">
                        <?php echo htmlspecialchars($row['scholarship_name']); ?>
                    </span>
                </td>

                <!-- PROVIDER -->
                <td>
                    <div class="provider-cell">
                   
                        <span class="provider-name">
                            <?php echo htmlspecialchars($row['provider']); ?>
                        </span>
                    </div>
                </td>

                <!-- AMOUNT -->
                <td>
                    <span class="amount-cell">
                        &#8369;<?php echo number_format($row['amount'], 2); ?>
                    </span>
                </td>

                <!-- DEADLINE -->
                <td>
                    <?php if(!empty($row['deadline'])): ?>
                        <div class="deadline-cell">
                            <i class="ti ti-calendar-event"></i>
                            <?php echo date("M d, Y", strtotime($row['deadline'])); ?>
                        </div>
                    <?php else: ?>
                        <span class="no-deadline">No deadline</span>
                    <?php endif; ?>
                </td>

                <!-- STATUS -->
                <td>
                    <?php if(strtolower($row['status']) == "open"): ?>
                        <span class="badge badge-open">
                            <span class="badge-dot"></span>
                            Open
                        </span>
                    <?php else: ?>
                        <span class="badge badge-close">
                            <span class="badge-dot"></span>
                            Closed
                        </span>
                    <?php endif; ?>
                </td>

                <!-- DOCUMENT -->
                <td>
                    <?php if(!empty($row['scholarship_file'])): ?>
                        <a href="uploads/<?php echo $row['scholarship_file']; ?>"
                           target="_blank"
                           class="action-btn btn-file">
                            <i class="ti ti-file-text"></i>
                            View File
                        </a>
                    <?php else: ?>
                        <span class="no-file">No file</span>
                    <?php endif; ?>
                </td>

                <!-- ACTIONS -->
                <td>
                    <div class="actions-cell">
                        <a href="edit_scholarship.php?sid=<?php echo $row['sid']; ?>"
                           class="action-btn btn-edit">
                            <i class="ti ti-edit"></i>
                            Edit
                        </a>
                        <a href="#"
                           class="action-btn btn-delete"
                           data-delete-url="delete_scholarship.php?sid=<?php echo $row['sid']; ?>"
                           data-delete-name="<?php echo htmlspecialchars($row['scholarship_name']); ?>"
                           onclick="return false;">
                            <i class="ti ti-trash"></i>
                            Delete
                        </a>
                    </div>
                </td>

            </tr>

            <?php endwhile; ?>
            <?php else: ?>

            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="ti ti-school"></i>
                        </div>
                        <p class="empty-title">No scholarships found</p>
                        <p class="empty-desc">
                            <?php if($search != ""): ?>
                                No results match &ldquo;<?php echo htmlspecialchars($search); ?>&rdquo;. Try a different keyword.
                            <?php else: ?>
                                There are no scholarships yet. Click &ldquo;Add Scholarship&rdquo; to get started.
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
            Showing all scholarships &mdash; sorted by most recent
        </div>
    </div>

</div>



<script>
  const deleteModal = document.getElementById('deleteModal');
  const modalName = document.getElementById('modalName');
  const modalConfirmBtn = document.getElementById('modalConfirmBtn');

  function closeDeleteModal(){
    deleteModal.classList.remove('active');
  }

  document.querySelectorAll('[data-delete-url]').forEach(btn => {
    btn.addEventListener('click', function(e){
      e.preventDefault();
      const url = this.getAttribute('data-delete-url');
      const name = this.getAttribute('data-delete-name') || 'this scholarship';
      modalName.textContent = name;
      modalConfirmBtn.href = url;
      deleteModal.classList.add('active');
    });
  });

  deleteModal.addEventListener('click', function(e){
    if(e.target === deleteModal) closeDeleteModal();
  });

  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape') closeDeleteModal();
  });
</script>

</body>
</html>
