<?php
require_once("dbconfig.php");
session_start();
require_once("headers.php");

/* ===== CHECK LOGIN ===== */
if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit();
}

/* ===== SUBMIT APPLICATION ===== */
if(isset($_POST['submit'])){

    $first_name   = trim($_POST['first_name']);
    $last_name    = trim($_POST['last_name']);
    $course       = trim($_POST['course']);
    $year_section = trim($_POST['year_section']);
    $school_id    = trim($_POST['school_id']);
    $address      = trim($_POST['address']);
    $civil_status = trim($_POST['civil_status']);
    $age          = trim($_POST['age']);
    $sex          = trim($_POST['sex']);

    $father_first_name = trim($_POST['father_first_name']);
    $father_last_name  = trim($_POST['father_last_name']);
    $father_occupation = trim($_POST['father_occupation']);

    $mother_first_name = trim($_POST['mother_first_name']);
    $mother_last_name  = trim($_POST['mother_last_name']);
    $mother_occupation = trim($_POST['mother_occupation']);

    $date_applied = date("Y-m-d H:i:s");
    $status = "Pending";

    // Logged in student id
    $id = $_SESSION['id'];

    // Scholarship id
    $sid = $_GET['id'];

    /* ===== FILE UPLOAD ===== */
    $document = "";

    if(isset($_FILES['document']) && $_FILES['document']['error'] == 0){

        $file_name = $_FILES['document']['name'];
        $tmp_name  = $_FILES['document']['tmp_name'];

        $file_name = str_replace(" ","_",$file_name);
        $new_name = time()."_".$file_name;

        $folder = "uploads/".$new_name;

        if(move_uploaded_file($tmp_name,$folder)){
            $document = $new_name;
        }else{
            echo "<script>alert('Upload Failed');</script>";
        }
    }

    /* ===== INSERT APPLICATION ===== */
    $stmt = $con->prepare("
        INSERT INTO applications_form
        (id, sid, first_name, last_name, course, year_section, school_id, address, civil_status,age,sex,
         father_first_name,father_last_name,father_occupation, mother_first_name, mother_last_name,
         mother_occupation, document, date_applied, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if(!$stmt){
        die("SQL Error: " . $con->error);
    }

    $stmt->bind_param(
        "iisssssssissssssssss",
        $id,
        $sid,
        $first_name,
        $last_name,
        $course,
        $year_section,
        $school_id,
        $address,
        $civil_status,
        $age,
        $sex,
        $father_first_name,
        $father_last_name,
        $father_occupation,
        $mother_first_name,
        $mother_last_name,
        $mother_occupation,
        $document,
        $date_applied,
        $status
    );

    if($stmt->execute()){

        echo "
        <script>
            alert('Application Submitted Successfully');
            window.location='applicationhistory.php';
        </script>
        ";

    }else{

        echo "
        <script>
            alert('Error submitting application');
        </script>
        ";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Scholarship Application</title>

<style>

/* ===== PAGE ===== */
body{
    margin:0;
    background:#f1f4f9;
    font-family:"Segoe UI",Tahoma,sans-serif;
}

/* ===== MAIN ===== */
.main{
    margin-left:240px;
    margin-top:70px;
    padding:40px;
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
    width:100%;
    max-width:600px;

    height:70vh;
    overflow-y:auto; 

    background:#fff;

    border-radius:16px;

    box-shadow:0 15px 35px rgba(0,0,0,0.25);

    padding:35px;

    position:relative;

    animation:fadeIn 0.3s ease;
}

/* CLOSE BUTTON */
.close-btn {
   
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

/* ===== TITLE ===== */
h2{
    margin-bottom:10px;
    color:#141516;
    font-size:28px;
}

.subtitle{
    color:#6c757d;
    margin-bottom:30px;
    font-size:14px;
}

/* ===== FORM GRID ===== */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

/* ===== FORM GROUP ===== */
.form-group{
    display:flex;
    flex-direction:column;
}

.full{
    grid-column:1 / -1;
}

/* ===== LABEL ===== */
label{
    margin-bottom:8px;
    font-weight:600;
    color:#333;
    font-size:14px;
}

/* ===== INPUT ===== */
input,
textarea{
    width:100%;
    padding:12px;

    border:1px solid #dbe0e6;
    border-radius:10px;

    font-size:14px;

    background:#fff;

    transition:0.3s;

    box-sizing:border-box;
}

textarea{
    min-height:100px;
    resize:none;
}

input:focus,
textarea:focus{
    outline:none;
    border-color:#0d6efd;
    box-shadow:0 0 0 4px rgba(13,110,253,0.10);
}

/* ===== FILE INPUT ===== */
input[type="file"]{
    padding:10px;
    background:#f8f9fa;
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
    transform:translateY(-2px);
}

/* ===== RESPONSIVE ===== */
@media(max-width:768px){

    .main{
        margin-left:0;
        padding:20px;
    }

    .container{
        padding:25px;
    }

    .form-grid{
        grid-template-columns:1fr;
    }
}

</style>

</head>

<body>

<!-- ===== MAIN ===== -->
<main class="main">

<!-- ===== OVERLAY ===== -->
<div class="overlay">

    <!-- ===== POPUP ===== -->
    <div class="container">
                <!-- CLOSE BUTTON -->
        <div class="close-btn">
            <a href="dashboardusers.php" title="Close">
                <i class="fa-solid fa-xmark"></i>
            </a>
        </div>

            <h2>Application Form</h2>

            <p class="subtitle">
                Fill out all required information carefully before submitting your application.
            </p>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-grid">
                     <div class="form-group full">
                        <h3 style="margin-top:15px;">Student Information</h3>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>

                        <input type="text"
                               name="first_name"
                               placeholder="juan"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>

                        <input type="text"
                               name="last_name"
                               placeholder="Dela Cruz"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Course</label>

                        <input type="text"
                               name="course"
                               placeholder="BCcomsci etc"
                               required>
                    </div>

                    <div class="form-group">
                        <label>Year & Section</label>

                        <input type="text"
                               name="year_section"
                               placeholder="1A"
                               required>
                    </div>

                    <div class="form-group full">
                        <label>School ID</label>

                        <input type="text"
                               name="school_id"
                               placeholder="2023-0213-A"
                               required>
                    </div>

                    <div class="form-group full">
                        <label>Address</label>

                        <textarea name="address"
                                  placeholder="Barangay,Municipal,Province"
                                  required></textarea>
                    </div>

                    <div class="form-group">
                    <label>Age</label>
                    <input type="number"
                        name="age"
                        min="1"
                        required>
                </div>

                <div class="form-group">
                    <label>Sex</label>
                    <select name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>

                <div class="form-group full">
                    <label>Civil Status</label>
                    <select name="civil_status" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                    </select>
                </div>

                <div class="form-group full">
                        <h3 style="margin-top:15px;">Father's Information</h3>
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text"
                            name="father_first_name"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text"
                            name="father_last_name"
                            required>
                    </div>

                    <div class="form-group full">
                        <label>Occupation</label>
                        <input type="text"
                            name="father_occupation"
                            required>
                    </div>

                    <div class="form-group full">
                        <h3 style="margin-top:15px;">Mother's Information</h3>
                    </div>

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text"
                            name="mother_first_name"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text"
                            name="mother_last_name"
                            required>
                    </div>

                    <div class="form-group full">
                        <label>Occupation</label>
                        <input type="text"
                            name="mother_occupation"
                            required>
                    </div>
                    <div class="form-group full">
                        <label>Required Document (Grade/ requirement Form (RF))</label>

                        <input type="file"
                               name="document"
                               accept=".pdf,.jpg,.png,.jpeg"
                               required>
                    </div>

                    <div class="form-group full">
                        <button type="submit"
                                name="submit"
                                class="btn-submit">

                            Submit Application

                        </button>
                    </div>

                </div>

            </form>

        </div>

    </div>

</main>

</body>
</html>