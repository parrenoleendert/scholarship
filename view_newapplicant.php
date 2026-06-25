```php
<?php
require_once("dbconfig.php");
require_once("header.php");

/* ===== CHECK ID ===== */
if(!isset($_GET['id'])){
    header("Location: newapplication.php");
    exit();
}

$aid = $_GET['id'];

/* ===== FETCH APPLICANT ===== */
$stmt = $con->prepare("
    SELECT a.*, s.scholarship_name
    FROM applications_form a
    LEFT JOIN scholarship s ON a.sid = s.sid
    WHERE a.aid = ?
");

$stmt->bind_param("i", $aid);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Applicant not found.");
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
<title>View Applicant</title>

<style>

body{
    margin:0;
    font-family:'Segoe UI',sans-serif;
    background:#f4f6f9;
}

/* OVERLAY */
.overlay{
    position:fixed;
    inset:0;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(3px);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    z-index:9999;
}

/* MODAL */
.modal{
    width:100%;
    max-width:1000px;
    max-height:90vh;
    overflow-y:auto;
    background:#fff;
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.2);
    padding:30px;
    animation:show .25s ease;
}

@keyframes show{
    from{
        opacity:0;
        transform:translateY(20px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}

.close-btn{
    float:right;
    width:38px;
    height:38px;
    border-radius:50%;
    background:#fee2e2;
    color:#dc2626;
    text-decoration:none;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:22px;
    font-weight:bold;
}

.close-btn:hover{
    background:#dc2626;
    color:#fff;
}

.title{
    font-size:28px;
    font-weight:700;
    color:#111827;
}

.subtitle{
    color:#6b7280;
    margin-bottom:25px;
}

.section{
    margin-top:25px;
}

.section-title{
    font-size:14px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.5px;
    color:#2563eb;
    margin-bottom:15px;
}

.info-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:15px;
}

.info-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:15px;
}

.full{
    grid-column:1/-1;
}

.label{
    font-size:11px;
    text-transform:uppercase;
    color:#6b7280;
    margin-bottom:6px;
}

.value{
    font-size:15px;
    font-weight:600;
    color:#111827;
}

.badge{
    display:inline-block;
    padding:6px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.pending{
    background:#fef3c7;
    color:#92400e;
}

.approved{
    background:#dcfce7;
    color:#166534;
}

.rejected{
    background:#fee2e2;
    color:#991b1b;
}

.btn-doc{
    display:inline-block;
    background:#2563eb;
    color:#fff;
    text-decoration:none;
    padding:10px 18px;
    border-radius:8px;
    margin-top:5px;
}

.btn-doc:hover{
    background:#1d4ed8;
}

@media(max-width:768px){

    .info-grid{
        grid-template-columns:1fr;
    }

    .modal{
        padding:20px;
    }
}

</style>
</head>

<body>

<div class="overlay">

<div class="modal">

<a href="newapplication.php" class="close-btn">&times;</a>

<h2 class="title">Applicant Information</h2>
<p class="subtitle">Complete scholarship application details.</p>

<!-- STUDENT INFORMATION -->
<div class="section">
    <div class="section-title">Student Information</div>

    <div class="info-grid">

        <div class="info-box">
            <div class="label">First Name</div>
            <div class="value"><?php echo htmlspecialchars($row['first_name']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Last Name</div>
            <div class="value"><?php echo htmlspecialchars($row['last_name']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Course</div>
            <div class="value"><?php echo htmlspecialchars($row['course']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Year & Section</div>
            <div class="value"><?php echo htmlspecialchars($row['year_section']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">School ID</div>
            <div class="value"><?php echo htmlspecialchars($row['school_id']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Age</div>
            <div class="value"><?php echo htmlspecialchars($row['age']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Sex</div>
            <div class="value"><?php echo htmlspecialchars($row['sex']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Civil Status</div>
            <div class="value"><?php echo htmlspecialchars($row['civil_status']); ?></div>
        </div>

        <div class="info-box full">
            <div class="label">Address</div>
            <div class="value"><?php echo htmlspecialchars($row['address']); ?></div>
        </div>

    </div>
</div>

<!-- SCHOLARSHIP -->
<div class="section">

    <div class="section-title">Scholarship Information</div>

    <div class="info-grid">

        <div class="info-box">
            <div class="label">Scholarship</div>
            <div class="value"><?php echo htmlspecialchars($row['scholarship_name']); ?></div>
        </div>

        <div class="info-box">
            <div class="label">Date Applied</div>
            <div class="value"><?php echo date('F d, Y',strtotime($row['date_applied'])); ?></div>
        </div>

        <div class="info-box full">
            <div class="label">Status</div>

            <div class="value">
                <span class="badge <?php echo strtolower($row['status']); ?>">
                    <?php echo htmlspecialchars($row['status']); ?>
                </span>
            </div>

        </div>

    </div>

</div>

<!-- FATHER -->
<div class="section">

<div class="section-title">Father's Information</div>

<div class="info-grid">

    <div class="info-box">
        <div class="label">First Name</div>
        <div class="value"><?php echo htmlspecialchars($row['father_first_name']); ?></div>
    </div>

    <div class="info-box">
        <div class="label">Last Name</div>
        <div class="value"><?php echo htmlspecialchars($row['father_last_name']); ?></div>
    </div>

    <div class="info-box full">
        <div class="label">Occupation</div>
        <div class="value"><?php echo htmlspecialchars($row['father_occupation']); ?></div>
    </div>

</div>

</div>

<!-- MOTHER -->
<div class="section">

<div class="section-title">Mother's Information</div>

<div class="info-grid">

    <div class="info-box">
        <div class="label">First Name</div>
        <div class="value"><?php echo htmlspecialchars($row['mother_first_name']); ?></div>
    </div>

    <div class="info-box">
        <div class="label">Last Name</div>
        <div class="value"><?php echo htmlspecialchars($row['mother_last_name']); ?></div>
    </div>

    <div class="info-box full">
        <div class="label">Occupation</div>
        <div class="value"><?php echo htmlspecialchars($row['mother_occupation']); ?></div>
    </div>

</div>

</div>

<!-- DOCUMENT -->
<div class="section">

<div class="section-title">Submitted Requirement</div>

<div class="info-box">

    <div class="label">Uploaded Document</div>

    <?php if(!empty($row['document'])): ?>

        <a
            href="uploads/<?php echo htmlspecialchars($row['document']); ?>"
            target="_blank"
            class="btn-doc">
            View Document
        </a>

    <?php else: ?>

        <div class="value">No document uploaded.</div>

    <?php endif; ?>

</div>

</div>

</div>
</div>

</body>
</html>
```
