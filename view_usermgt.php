<?php
session_start();
require_once("dbconfig.php");
require_once("header.php");

/* ===== CHECK ADMIN LOGIN ===== */
if(!isset($_SESSION['adminid'])){
    header("Location: adminlogin.php");
    exit();
}

/* ===== CHECK ID ===== */
if(!isset($_GET['id'])){
    header("Location: users_mgt.php");
    exit();
}

$id = $_GET['id'];

/* ===== FETCH USER ===== */
$stmt = $con->prepare("
    SELECT * FROM students
    WHERE student_id = ?
");

$stmt->bind_param("s", $id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "User not found";
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>

<title>View User</title>

<style>

/* ===== PAGE ===== */
body{
    margin:0;
    background:#f1f4f9;
    font-family:"Segoe UI",Tahoma,sans-serif;
}

/* ===== OVERLAY ===== */
.overlay {
      position: fixed;
      inset: 0;                          /* top:0 left:0 right:0 bottom:0 */
      background: rgba(15, 23, 42, 0.55);
      backdrop-filter: blur(3px);
      -webkit-backdrop-filter: blur(3px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;                     /* above sidebar & header */
      padding: 24px;
    }

/* ===== POPUP CONTAINER ===== */
.container{
    width:90%;
    max-width:500px;

    background:#fff;

    border-radius:16px;

    box-shadow:0 15px 35px rgba(0,0,0,0.25);

    padding:35px;

    position:relative;

    animation:fadeIn 0.3s ease;
}

/* ===== ANIMATION ===== */
@keyframes fadeIn{
    from{
        opacity:0;
        transform:translateY(10px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }
}

/* ===== CLOSE BUTTON ===== */
.close-btn{
    position:absolute;
    top:18px;
    right:18px;

    width:38px;
    height:38px;

    border-radius:50%;

    background:#f8d7da;
    color:#dc3545;

    text-decoration:none;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:20px;
    font-weight:bold;

    transition:0.3s;
}

.close-btn:hover{
    background:#dc3545;
    color:#fff;
}

/* ===== TITLE ===== */
h2{
    margin-bottom:25px;
    color:#141516;
    font-size:24px;
}

/* ===== INFO ===== */
.info{
    padding:14px 0;
    border-bottom:1px solid #eee;
    font-size:15px;
    color:#444;
}

/* ===== LABEL ===== */
.label{
    font-weight:600;
    display:inline-block;
    width:170px;
    color:#333;
}

/* ===== RESPONSIVE ===== */
@media(max-width:768px){

    .container{
        width:95%;
        padding:25px;
    }

    .info{
        display:flex;
        flex-direction:column;
        gap:5px;
    }

    .label{
        width:auto;
    }

}

</style>

</head>

<body>

<!-- ===== OVERLAY ===== -->
<div class="overlay">

    <!-- ===== POPUP ===== -->
    <div class="container">

        <!-- ===== CLOSE BUTTON ===== -->
        <a href="users_mgt.php" class="close-btn">×</a>

        <h2>User Details</h2>

        <div class="info">
            <span class="label">School ID:</span>
            <?php echo htmlspecialchars($row['student_id']); ?>
        </div>

        <div class="info">
            <span class="label">First Name:</span>
            <?php echo htmlspecialchars($row['first_name']); ?>
        </div>

        <div class="info">
            <span class="label">Last Name:</span>
            <?php echo htmlspecialchars($row['last_name']); ?>
        </div>

        <div class="info">
            <span class="label">Course:</span>
            <?php echo htmlspecialchars($row['course']); ?>
        </div>

        <div class="info">
            <span class="label">Year & Section:</span>
            <?php echo htmlspecialchars($row['year_section']); ?>
        </div>

        <div class="info">
            <span class="label">Address:</span>
            <?php echo htmlspecialchars($row['address']); ?>
        </div>

        <div class="info">
            <span class="label">Email:</span>
            <?php echo htmlspecialchars($row['email']); ?>
        </div>

        <div class="info">
            <span class="label">Contact Number:</span>
            <?php echo htmlspecialchars($row['phone']); ?>
        </div>

    </div>

</div>

</body>
</html>