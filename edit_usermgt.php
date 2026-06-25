<?php
session_start();
require_once("dbconfig.php");


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
$stmt = $con->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "User not found";
    exit();
}

$row = $result->fetch_assoc(); // ✅ ONLY ONCE

/* ===== UPDATE USER ===== */
if(isset($_POST['update'])){

    $first_name   = $_POST['first_name'];
    $last_name    = $_POST['last_name'];
    $course       = $_POST['course'];
    $year_section = $_POST['year_section'];
    $address      = $_POST['address'];
    $email        = $_POST['email'];
    $phone        = $_POST['phone'];

    $update = $con->prepare("
        UPDATE students
        SET first_name = ?,
            last_name = ?,
            course = ?,
            year_section = ?,
            address = ?,
            email = ?,
            phone = ?
        WHERE student_id = ?
    ");

    $update->bind_param(
        "ssssssss",
        $first_name,
        $last_name,
        $course,
        $year_section,
        $address,
        $email,
        $phone,
        $id
    );

    if($update->execute()){

        echo "
        <script>
            alert('User updated successfully');
            window.location='users_mgt.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Update failed');
        </script>
        ";
    }
}
?>
<?php require_once("header.php"); ?>

<!DOCTYPE html>
<html>
<head>

<title>Edit User</title>

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
    width:100%;
    max-width:500px;

    height:70vh;
    overflow-y:auto; 

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

/* ===== FORM GROUP ===== */
.form-group{
    margin-bottom:18px;
}

/* ===== LABEL ===== */
label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#333;
    font-size:14px;
}

/* ===== INPUT ===== */
input{
    width:100%;
    padding:12px;

    border:1px solid #dbe0e6;
    border-radius:10px;

    font-size:14px;

    background:#fff;

    transition:0.3s;

    box-sizing:border-box;
}

input:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 4px rgba(13,110,253,0.10);
}

/* ===== BUTTON ===== */
.btn-submit{
    width:100%;
    padding:15px;
    

    background:#0d6efd;
    color:#fff;

    border:none;
    border-radius:10px;

    font-size:15px;
    font-weight:600;

    cursor:pointer;

    transition:0.3s;
}

.btn-submit:hover{
    background:#0b5ed7;
    
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

        <h2>Edit User</h2>

        <form method="POST">

            <div class="form-group">
                <label>First Name</label>

                <input type="text"
                       name="first_name"
                       value="<?php echo htmlspecialchars($row['first_name']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Last Name</label>

                <input type="text"
                       name="last_name"
                       value="<?php echo htmlspecialchars($row['last_name']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Course</label>

                <input type="text"
                       name="course"
                       value="<?php echo htmlspecialchars($row['course']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Year & Section</label>

                <input type="text"
                       name="year_section"
                       value="<?php echo htmlspecialchars($row['year_section']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Address</label>

                <input type="text"
                       name="address"
                       value="<?php echo htmlspecialchars($row['address']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Email</label>

                <input type="email"
                       name="email"
                       value="<?php echo htmlspecialchars($row['email']); ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Contact Number</label>

                <input type="text"
                       name="phone"
                       value="<?php echo htmlspecialchars($row['phone']); ?>"
                       required>
            </div>

            <button type="submit"
                    name="update"
                    class="btn-submit">

                Update User

            </button>

        </form>

    </div>

</div>

</body>
</html>