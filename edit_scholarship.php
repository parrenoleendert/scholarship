<?php
require_once("dbconfig.php");
require_once("header.php");

/* CHECK IF SID EXISTS */
if(!isset($_GET['sid'])){
    header("Location: scholarship.php");
    exit();
}

$sid = $_GET['sid'];

/* FETCH SCHOLARSHIP DATA */
$stmt = $con->prepare("SELECT * FROM scholarship WHERE sid = ?");
$stmt->bind_param("i", $sid);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

/* IF NOT FOUND */
if(!$row){
    echo "Scholarship not found!";
    exit();
}

/* UPDATE SCHOLARSHIP */
if(isset($_POST['update'])){

    $scholarship_name = $_POST['scholarship_name'];
    $provider         = $_POST['provider'];
    $amount           = $_POST['amount'];
    $deadline         = $_POST['deadline'];
    $status           = $_POST['status'];

    /* FILE UPLOAD */
    $file_name = $row['scholarship_file'];

    if(!empty($_FILES['scholarship_file']['name'])){

        $new_file = time().'_'.$_FILES['scholarship_file']['name'];
        $tmp_name = $_FILES['scholarship_file']['tmp_name'];

        move_uploaded_file($tmp_name, "uploads/".$new_file);

        $file_name = $new_file;
    }

    /* UPDATE QUERY */
    $update = $con->prepare("
        UPDATE scholarship
        SET scholarship_name = ?,
            provider = ?,
            amount = ?,
            deadline = ?,
            status = ?,
            scholarship_file = ?
        WHERE sid = ?
    ");

    $update->bind_param(
        "ssdsssi",
        $scholarship_name,
        $provider,
        $amount,
        $deadline,
        $status,
        $file_name,
        $sid
    );

    if($update->execute()){

        echo "<script>
        alert('Scholarship Updated Successfully');
        window.location='scholarship.php';
        </script>";

    }else{

        echo "<script>alert('Update Failed');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Scholarship</title>

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
    padding:30px;
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

/* ===== INPUTS ===== */
input,
select{
    width:100%;
    padding:12px;
    border:1px solid #dbe0e6;
    border-radius:10px;
    font-size:14px;
    background:#fff;
    transition:0.3s;
    box-sizing:border-box;
}

input:focus,
select:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 4px rgba(13,110,253,0.10);
}

/* ===== FILE BUTTON ===== */
.file-link{
    display:inline-block;
    margin-top:12px;
    padding:8px 14px;
    background:#0d6efd;
    color:#fff;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
}

.file-link:hover{
    background:#0b5ed7;
}

/* ===== SUBMIT BUTTON ===== */
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

    .main{
        margin-left:0;
        padding:20px;
    }

    .form-card{
        padding:25px;
    }

}

</style>
</head>

<body>

<!-- ===== DARK OVERLAY ===== -->
<div class="overlay">

    <!-- ===== POPUP CONTAINER ===== -->
    <div class="container">

<a href="scholarship.php" class="close-btn">×</a>

<h2>Edit Scholarship</h2>

<form method="POST" enctype="multipart/form-data">

<div class="form-group">
<label>Scholarship Name</label>

<input type="text"
       name="scholarship_name"
       value="<?php echo htmlspecialchars($row['scholarship_name']); ?>"
       required>
</div>

<div class="form-group">
<label>Provider</label>

<input type="text"
       name="provider"
       value="<?php echo htmlspecialchars($row['provider']); ?>"
       required>
</div>

<div class="form-group">
<label>Amount</label>

<input type="number"
       step="0.01"
       name="amount"
       value="<?php echo $row['amount']; ?>"
       required>
</div>

<div class="form-group">
<label>Deadline</label>

<input type="date"
       name="deadline"
       value="<?php echo $row['deadline']; ?>"
       required>
</div>

<div class="form-group">
<label>Status</label>

<select name="status" required>

<option value="Open"
<?php if($row['status']=="Open") echo "selected"; ?>>
Open
</option>

<option value="Close"
<?php if($row['status']=="Close") echo "selected"; ?>>
Close
</option>

</select>
</div>

<div class="form-group">
<label>Scholarship File</label>

<input type="file"
       name="scholarship_file"
       accept=".pdf,.doc,.docx">

<?php if(!empty($row['scholarship_file'])){ ?>

<a class="file-link"
   href="uploads/<?php echo $row['scholarship_file']; ?>"
   target="_blank">

View Current File

</a>

<?php } ?>

</div>

<button type="submit"
        name="update"
        class="btn-submit">

Update Scholarship

</button>

</form>

</div>

</main>

</body>
</html>