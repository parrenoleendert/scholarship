<?php
require_once("dbconfig.php");
require_once("header.php");

/* ===== CHECK ID ===== */
if(!isset($_GET['id'])){
    header("Location: scholars_list.php");
    exit();
}

$aid = $_GET['id'];

/* ===== FETCH DATA ===== */
$stmt = $con->prepare("
    SELECT a.*, s.scholarship_name
    FROM applications_form a
    INNER JOIN scholarship s ON a.sid = s.sid
    WHERE a.aid = ?
");

$stmt->bind_param("i", $aid);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    echo "No record found";
    exit();
}

$row = $result->fetch_assoc();

/* ===== UPDATE ===== */
if(isset($_POST['update'])){

    $first_name   = $_POST['first_name'];
    $last_name    = $_POST['last_name'];
    $course       = $_POST['course'];
    $year_section = $_POST['year_section'];
    $status       = $_POST['status'];

    $update = $con->prepare("
        UPDATE applications_form
        SET first_name=?,
            last_name=?,
            course=?,
            year_section=?,
            status=?
        WHERE aid=?
    ");

    $update->bind_param(
        "sssssi",
        $first_name,
        $last_name,
        $course,
        $year_section,
        $status,
        $aid
    );

    if($update->execute()){

        echo "
        <script>
            alert('Applicant updated successfully!');
            window.location='scholars_list.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Update failed!');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Applicant</title>

<style>

/* ===== PAGE ===== */

body{
    margin:0;
    font-family:"Segoe UI",Tahoma,sans-serif;
    background:#f1f4f9;
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

/* ===== MODAL ===== */

.modal-box{
    background:#fff;

    width:90%;
    max-width:500px;

    padding:30px;

    border-radius:16px;

    position:relative;

    animation:popup 0.3s ease;
}

/* ===== ANIMATION ===== */

@keyframes popup{

    from{
        opacity:0;
        transform:scale(0.95);
    }

    to{
        opacity:1;
        transform:scale(1);
    }

}

/* ===== CLOSE BUTTON ===== */

.close-btn{
    position:absolute;

    top:15px;
    right:15px;

    width:35px;
    height:35px;

    border-radius:50%;

    background:#f8d7da;
    color:#dc3545;

    text-decoration:none;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:20px;
    font-weight:bold;
}

.close-btn:hover{
    background:#dc3545;
    color:#fff;
}

/* ===== TITLE ===== */

h2{
    margin-bottom:25px;
    color:#141516;
}

/* ===== FORM GROUP ===== */

.form-group{
    margin-bottom:18px;
}

/* ===== LABEL ===== */

label{
    display:block;
    margin-bottom:6px;
    font-weight:600;
    color:#333;
}

/* ===== INPUT ===== */

input,
select{
    width:100%;
    padding:12px;

    border:1px solid #ccc;
    border-radius:8px;

    font-size:14px;

    box-sizing:border-box;
}

/* ===== BUTTON ===== */

.update-btn{
    background:#0d6efd;
    color:#fff;

    border:none;

    padding:12px 18px;

    border-radius:8px;

    cursor:pointer;

    font-size:14px;
    font-weight:600;
}

.update-btn:hover{
    background:#0b5ed7;
}

/* ===== RESPONSIVE ===== */

@media(max-width:768px){

    .modal-box{
        width:95%;
        padding:25px;
    }

}
</style>

</head>

<body>

<div class="overlay">

    <div class="modal-box">

        <!-- CLOSE -->
        <a href="scholars_list.php" class="close-btn">×</a>

        <h2>Edit Applicant</h2>

        <form method="POST">

            <div class="form-group">

                <label>First Name</label>

                <input
                    type="text"
                    name="first_name"
                    value="<?php echo htmlspecialchars($row['first_name']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Last Name</label>

                <input
                    type="text"
                    name="last_name"
                    value="<?php echo htmlspecialchars($row['last_name']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Course</label>

                <input
                    type="text"
                    name="course"
                    value="<?php echo htmlspecialchars($row['course']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Year / Section</label>

                <input
                    type="text"
                    name="year_section"
                    value="<?php echo htmlspecialchars($row['year_section']); ?>"
                    required
                >

            </div>

            <div class="form-group">

                <label>Status</label>

                <select name="status" required>

                    <option value="Approved"
                        <?php if($row['status']=="Approved") echo "selected"; ?>>
                        Approved
                    </option>

                    <option value="Rejected"
                        <?php if($row['status']=="Rejected") echo "selected"; ?>>
                        Rejected
                    </option>

                </select>

            </div>

            <button type="submit" name="update" class="update-btn">
                Update Applicant
            </button>

        </form>

    </div>

</div>

</body>
</html>