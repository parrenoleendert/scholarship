<?php
session_start();
require_once("dbconfig.php");
require_once("header.php");

// Protect page
if (!isset($_SESSION['adminid'])) {
    header("Location: adminlogin.php");
    exit();
}

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$id = (int)$_GET['id'];

// Fetch data
$stmt = $con->prepare("SELECT * FROM students WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Record not found");
}

$data = $result->fetch_assoc();

// Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name    = trim($_POST['full_name']);
    $course       = trim($_POST['course']);
    $year_section = trim($_POST['year_section']);
    $address      = trim($_POST['address']);

    if (empty($full_name) || empty($course)) {
        $error = "Please fill required fields.";
    } else {
        $update = $con->prepare("UPDATE students SET full_name=?, course=?, year_section=?, address=? WHERE id=?");
        $update->bind_param("ssssi", $full_name, $course, $year_section, $address, $id);

        if ($update->execute()) {
            header("Location: users.php?updated=1");
            exit();
        } else {
            $error = "Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Student</title>

<style>

/* ===== MATCH DASHBOARD ===== */
.main{
    padding:40px;
    background:#f1f4f9;
    min-height:100vh;
    font-family: "Segoe UI", Tahoma, sans-serif;
}

/* ===== CARD STYLE (same as table-container) ===== */
.form-container{
    background:#ffffff;
    padding:30px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
    border:1px solid #eef1f5;

    max-width:700px;
    margin:0 auto;
}

/* TITLE */
h2{
    margin-bottom:20px;
    color:#141516;
}

/* FORM */
label{
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

input{
    width:100%;
    padding:10px;
    margin-bottom:15px;
    border:1px solid #ced4da;
    border-radius:6px;
}

/* BUTTONS */
.btn{
    padding:10px 16px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:500;
}

.btn-primary{
    background:#0d6efd;
    color:white;
}

.btn-primary:hover{
    background:#0b5ed7;
}

.btn-secondary{
    background:#6c757d;
    color:white;
    text-decoration:none;
    padding:10px 16px;
    border-radius:6px;
}

.btn-secondary:hover{
    background:#5a6268;
}

/* ERROR */
.error{
    color:red;
    margin-bottom:15px;
}

/* RESPONSIVE */
@media(max-width:768px){
    .main{
        padding:20px;
    }
}

</style>
</head>

<body>

<main class="main">

<div class="form-container">

<h2>Edit Student</h2>

<?php if(isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">

<label>Full Name</label>
<input type="text" name="full_name" value="<?= htmlspecialchars($data['full_name']) ?>" required>

<label>Course</label>
<input type="text" name="course" value="<?= htmlspecialchars($data['course']) ?>" required>

<label>Year / Section</label>
<input type="text" name="year_section" value="<?= htmlspecialchars($data['year_section']) ?>">

<label>Address</label>
<input type="text" name="address" value="<?= htmlspecialchars($data['address']) ?>">

<button type="submit" class="btn btn-primary">Update</button>
<a href="users.php" class="btn-secondary">Cancel</a>

</form>

</div>

</main>

</body>
</html>