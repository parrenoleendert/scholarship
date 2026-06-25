<?php
session_start();
require_once("dbconfig.php");

if (!isset($_SESSION['adminid'])) {
    header("Location: adminlogin.php");
    exit();
}

$admin_id = $_SESSION['adminid'];

$stmt = $con->prepare("
    SELECT *
    FROM admin
    WHERE adminid = ?
");

if(!$stmt){
    die("Prepare failed: " . $con->error);
}

$stmt->bind_param("i", $admin_id);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if(!$admin){
    die("Administrator account not found.");
}
?>
<?php require_once("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Administrator Account</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --primary:#7c3aed;
    --secondary:#1e293b;
    --bg:#f4f7fb;
    --card:#ffffff;
    --text:#1f2937;
    --muted:#6b7280;
    --border:#e5e7eb;
}

body{
    font-family:'Plus Jakarta Sans', sans-serif;
    background:var(--bg);
    min-height:100vh;
}

/* OVERLAY BACKDROP */
.overlay-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.98); }
    to   { opacity: 1; transform: translateY(0)   scale(1);    }
}

/* OVERLAY MODAL */
.overlay-modal {
    position: relative;
    background: var(--card);
    border-radius: 22px;
    width: 100%;
    max-width: 860px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}

/* CLOSE BUTTON */
.close-btn {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    justify-content: flex-end;
    padding: 16px 20px 0;
    background: var(--card);
}

.close-btn a {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--muted);
    text-decoration: none;
    font-size: 16px;
    transition: .2s;
}

.close-btn a:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #dc2626;
}

/* MODAL INNER CONTENT */
.modal-inner {
    padding: 10px 30px 30px;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.page-title{
    font-size:24px;
    font-weight:700;
    color:var(--text);
}

.page-sub{
    color:var(--muted);
    margin-top:4px;
    font-size:13px;
}

.profile-card{
    display:grid;
    grid-template-columns:260px 1fr;
    gap:30px;
}

/* LEFT PANEL */
.left-panel{
    text-align:center;
    border-right:1px solid var(--border);
    padding-right:25px;
}

.avatar{
    width:110px;
    height:110px;
    border-radius:50%;
    margin:auto;
    margin-bottom:15px;
    object-fit:cover;
    border:4px solid #ede9fe;
}

.name{
    font-size:20px;
    font-weight:700;
    color:var(--text);
}

.role{
    color:var(--muted);
    margin-top:5px;
    font-size:14px;
}

.badge{
    display:inline-block;
    margin-top:15px;
    background:#ede9fe;
    color:#5b21b6;
    padding:7px 18px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

/* DIVIDER */
.left-divider {
    margin: 20px 0;
    border: none;
    border-top: 1px solid var(--border);
}

/* QUICK STATS */
.quick-stats {
    display: flex;
    flex-direction: column;
    gap: 10px;
    text-align: left;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f9fafb;
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 10px 14px;
}

.stat-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: #ede9fe;
    color: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.stat-label {
    font-size: 11px;
    color: var(--muted);
}

.stat-val {
    font-size: 13px;
    font-weight: 600;
    color: var(--text);
}

/* RIGHT PANEL */
.info-title{
    font-size:12px;
    font-weight:700;
    color:var(--muted);
    margin-bottom:18px;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:14px;
}

.info-box{
    background:#f9fafb;
    border:1px solid var(--border);
    border-radius:12px;
    padding:14px;
}

.label{
    font-size:11px;
    color:var(--muted);
    margin-bottom:6px;
    text-transform:uppercase;
    letter-spacing:.04em;
}

.value{
    font-size:14px;
    font-weight:600;
    color:var(--text);
}

.full{
    grid-column:1 / -1;
}

/* PERMISSIONS SECTION */
.section-divider {
    margin: 22px 0 18px;
    border: none;
    border-top: 1px solid var(--border);
}

.permissions-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 4px;
}

.perm-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.perm-tag i {
    font-size: 11px;
}

.actions{
    margin-top:22px;
    display:flex;
    gap:12px;
}

.btn{
    padding:11px 20px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    text-decoration:none;
    font-weight:600;
    font-size:14px;
    transition:.3s;
    display:flex;
    align-items:center;
    gap:8px;
}

.btn-primary{
    background:var(--primary);
    color:#fff;
}

.btn-primary:hover{
    background:#6d28d9;
}

.btn-secondary{
    background:#fff;
    border:1px solid var(--border);
    color:var(--text);
}

.btn-secondary:hover{
    background:#f3f4f6;
}

/* RESPONSIVE */
@media(max-width:700px){
    .profile-card{
        grid-template-columns:1fr;
    }
    .left-panel{
        border-right:none;
        border-bottom:1px solid var(--border);
        padding-right:0;
        padding-bottom:22px;
    }
    .info-grid{
        grid-template-columns:1fr;
    }
}

</style>
</head>

<body>

<!-- OVERLAY -->
<div class="overlay-backdrop" id="overlay" onclick="handleBackdropClick(event)">

    <div class="overlay-modal" id="modal">

        <!-- CLOSE BUTTON -->
        <div class="close-btn">
            <a href="dashboardadmin.php" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

        <!-- MODAL CONTENT -->
        <div class="modal-inner">

            <div class="page-header">
                <div>
                    <div class="page-title">My Account</div>
                    <div class="page-sub">View and manage your administrator profile</div>
                </div>
            </div>

            <div class="profile-card">

                <!-- LEFT -->
                <div class="left-panel">

                    <img class="avatar"
                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['first_name'].' '.$admin['last_name']); ?>&background=7c3aed&color=fff&size=120">

                    <div class="name">
                        <?php echo htmlspecialchars($admin['first_name'].' '.$admin['last_name']); ?>
                    </div>

                    

                    <span class="badge">
                        <i class="fa-solid fa-shield-halved"></i> Administrator
                    </span>

                    <hr class="left-divider">

                    <!-- QUICK STATS -->
                    <div class="quick-stats">

                        <div class="stat-item">
                            <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                            <div>
                                <div class="stat-label">Managed Scholars</div>
                                <div class="stat-val">
                                    <?php
                                        $r = $con->query("SELECT COUNT(*) AS cnt FROM students");
                                        $row = $r->fetch_assoc();
                                        echo $row['cnt'];
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="stat-item">
                            <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                            <div>
                                <div class="stat-label">Last Login</div>
                                <div class="stat-val"><?php echo date('M d, Y'); ?></div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- RIGHT -->
                <div class="right-panel">

                    <div class="info-title">Administrator Information</div>

                    <div class="info-grid">

                        <div class="info-box">
                            <div class="label">First Name</div>
                            <div class="value"><?php echo htmlspecialchars($admin['first_name']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="label">Last Name</div>
                            <div class="value"><?php echo htmlspecialchars($admin['last_name']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="label">Email Address</div>
                            <div class="value"><?php echo htmlspecialchars($admin['email']); ?></div>
                        </div>



                        <div class="info-box full">
                            <div class="label">Address</div>
                            <div class="value"><?php echo htmlspecialchars($admin['address'] ?? '—'); ?></div>
                        </div>

                    </div>

                    <hr class="section-divider">

                    <div class="info-title">Access & Permissions</div>

                    <div class="permissions-list">
                        <span class="perm-tag"><i class="fa-solid fa-check"></i> Manage Scholars</span>
                        <span class="perm-tag"><i class="fa-solid fa-check"></i> View Reports</span>
                        <span class="perm-tag"><i class="fa-solid fa-check"></i> Manage Applications</span>
                        <span class="perm-tag"><i class="fa-solid fa-check"></i> New Applicant</span>
                        <span class="perm-tag"><i class="fa-solid fa-check"></i> Users Management</span>
                    </div>
                    <!-- RIGHT -->
                    <div class="actions">
                        <a href="edit_account.php" class="btn btn-primary">
                            <i class="fa-solid fa-pen"></i> Edit Profile
                        </a>
                        <a href="edit_account.php" class="btn btn-secondary">
                            <i class="fa-solid fa-lock"></i> Change Password
                        </a>
                    </div><!-- RIGHT -->

                </div>

            </div>

        </div>
        <!-- END MODAL CONTENT -->

    </div>

</div>

<script>
    function handleBackdropClick(e) {
        if (e.target === document.getElementById('overlay')) {
            history.back();
        }
    }
</script>

</body>
</html>