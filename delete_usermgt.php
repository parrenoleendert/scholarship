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

/* ===== DELETE USER ===== */
$deleteSuccess = null;
if(isset($_POST['delete'])){

    $delete = $con->prepare("
        DELETE FROM students
        WHERE student_id = ?
    ");

    $delete->bind_param("s", $id);

    if($delete->execute()){

        $deleteSuccess = true;

    }else{

        $deleteSuccess = false;

    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Delete User</title>

<style>

/* ===== PAGE ===== */
body{
    margin:0;
    background:#f1f4f9;
    font-family:"Segoe UI",Tahoma,sans-serif;
}

/* ===== OVERLAY ===== */
.overlay{
    position:fixed;
    top:0;
    left:0;

    width:100%;
    height:100vh;

    background:rgba(0,0,0,0.45);

    display:flex;
    justify-content:center;
    align-items:center;

    z-index:9999;
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
}

.label{
    font-weight:600;
    display:inline-block;
    width:150px;
    color:#333;
}

/* ===== WARNING ===== */
.warning{
    margin-top:25px;
    padding:15px;
    background:#fff3cd;
    color:#856404;
    border-radius:10px;
    font-size:14px;
}


.btn{
    display:inline-block;

    padding:12px 18px;

    border:none;
    border-radius:8px;

    font-size:14px;
    font-weight:600;

    cursor:pointer;

    text-decoration:none;

    transition:0.3s ease;
}

/* DELETE */

.delete-btn{
    background:#dc3545;
    color:#fff;
}

.delete-btn:hover{
    background:#bb2d3b;
}

/* CANCEL */

.cancel-btn{
    background:#6c757d;
    color:#fff;
    margin-left:10px;
}

.cancel-btn:hover{
    background:#5c636a;
}

/* ===== RESPONSIVE ===== */
@media(max-width:768px){

    .container{
        width:95%;
        padding:25px;
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

        <h2>Delete User</h2>

        <div class="info">
            <span class="label">School ID:</span>
            <?php echo htmlspecialchars($row['student_id']); ?>
        </div>

        <div class="info">
            <span class="label">Name:</span>
            <?php echo htmlspecialchars($row['first_name']." ".$row['last_name']); ?>
        </div>

        <div class="info">
            <span class="label">Course:</span>
            <?php echo htmlspecialchars($row['course']); ?>
        </div>

        <div class="info">
            <span class="label">Year/Section:</span>
            <?php echo htmlspecialchars($row['year_section']); ?>
        </div>

        <div class="info">
            <span class="label">Address:</span>
            <?php echo htmlspecialchars($row['address']); ?>
        </div>

        <div class="warning">
            Are you sure you want to delete this user?
        </div>

<?php if($deleteSuccess === true): ?>

        <div class="warning" style="background:#d4edda;color:#155724;border-radius:10px;">
            User deleted successfully.
        </div>

        <div style="margin-top:18px;">
            <a href="users_mgt.php" class="btn cancel-btn" style="margin-left:0;">Go to Users</a>
        </div>

    <?php elseif($deleteSuccess === false): ?>

        <div class="warning" style="background:#f8d7da;color:#721c24;border-radius:10px;">
            Delete failed.
        </div>

        <form method="POST" style="margin-top:18px;">
            <button type="submit" name="delete" class="btn delete-btn">Try Again</button>
            <a href="users_mgt.php" class="btn cancel-btn">Cancel</a>
        </form>

    <?php else: ?>

      <form method="POST">

            <button type="submit"
                    name="delete"
                    class="btn delete-btn">
                Yes, Delete
            </button>

            <a href="scholars_list.php"
               class="btn cancel-btn">
               Cancel
            </a>

        </form>

    <?php endif; ?>

    </div>


</div>

</body>
</html>

