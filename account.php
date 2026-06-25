<?php
session_start();
require_once("dbconfig.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];

$stmt = $con->prepare("SELECT first_name, last_name, email, course, address
FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>
<?php require_once("headers.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scholar Account</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{
    --primary:#2563eb;
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
    border:4px solid #dbeafe;
}

.name{
    font-size:20px;
    font-weight:700;
    color:var(--text);
}

.course{
    color:var(--muted);
    margin-top:5px;
    font-size:14px;
}

.badge{
    display:inline-block;
    margin-top:15px;
    background:#dcfce7;
    color:#166534;
    padding:7px 18px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
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
    background:#1d4ed8;
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
            <a href="dashboardusers.php" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

        <!-- MODAL CONTENT -->
        <div class="modal-inner">

            <div class="page-header">
                <div>
                    <div class="page-title">My Account</div>
                    <div class="page-sub">View and manage your profile information</div>
                </div>
            </div>

            <div class="profile-card">

                <!-- LEFT -->
                <div class="left-panel">

                    <img class="avatar"
                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['first_name'].' '.$student['last_name']); ?>&background=2563eb&color=fff&size=120">

                    <div class="name">
                        <?php echo htmlspecialchars($student['first_name'].' '.$student['last_name']); ?>
                    </div>

                    <div class="course">
                        <?php echo htmlspecialchars($student['course']); ?>
                    </div>

                    <span class="badge">
                        <i class="fa-solid fa-circle-check"></i> Active Scholar
                    </span>

                </div>

                <!-- RIGHT -->
                <div class="right-panel">

                    <div class="info-title">Scholar Information</div>

                    <div class="info-grid">

                        <div class="info-box">
                            <div class="label">First Name</div>
                            <div class="value"><?php echo htmlspecialchars($student['first_name']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="label">Last Name</div>
                            <div class="value"><?php echo htmlspecialchars($student['last_name']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="label">Email Address</div>
                            <div class="value"><?php echo htmlspecialchars($student['email']); ?></div>
                        </div>

                        <div class="info-box">
                            <div class="label">Course</div>
                            <div class="value"><?php echo htmlspecialchars($student['course']); ?></div>
                        </div>

                        <div class="info-box full">
                            <div class="label">Address</div>
                            <div class="value"><?php echo htmlspecialchars($student['address']); ?></div>
                        </div>

                    </div>

                    <div class="actions">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="fa-solid fa-pen"></i> Edit Profile
                        </a>
                    </div>

                </div>

            </div>

        </div>
        <!-- END MODAL CONTENT -->

    </div>

</div>

<script>
    // Click on the dark backdrop (outside the modal) to go back
    function handleBackdropClick(e) {
        if (e.target === document.getElementById('overlay')) {
            history.back();
        }
    }
</script>

</body>
</html>