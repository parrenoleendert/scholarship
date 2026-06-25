<?php
require_once("dbconfig.php");
require_once("header.php");

$id = $_GET['id'] ?? 0;

if (!$id) {
    header("Location: scholars_list.php");
    exit();
}

/* FETCH DATA */
$stmt = $con->prepare("SELECT * FROM applications_form WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    header("Location: scholars_list.php?error=notfound");
    exit();
}

/* DELETE ACTION */
if (isset($_POST['confirm_delete'])) {
    $stmt = $con->prepare("DELETE FROM applications_form WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: scholars_list.php?deleted=1");
        exit();
    } else {
        echo "Delete failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Delete Scholar</title>

<style>
* { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'Segoe UI', sans-serif;
    background: #f4f6f9;
}

/* remove blue line */
header, nav {
    border-top: none !important;
    box-shadow: none !important;
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

.card {
    max-width: 600px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

h2 {
    color: #dc3545;
    margin-bottom: 20px;
}

p {
    margin: 10px 0;
    color: #333;
}

/* buttons */
.btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    border: none;
    margin: 10px;
    cursor: pointer;
}

.delete-btn {
    background: #dc3545;
    color: #fff;
}

.delete-btn:hover {
    background: #c82333;
}

.cancel-btn {
    background: #6c757d;
    color: #fff;
}

.cancel-btn:hover {
    background: #5a6268;
}

@media (max-width:768px){
    .main {
        margin-left: 0;
        padding: 20px;
    }
}
</style>

</head>
<body>

<div class="overlay">
    <div class="card">
        <h2>⚠ Confirm Delete</h2>

        <p><strong>Name:</strong> <?php echo htmlspecialchars($data['first_name'] . ' ' . $data['last_name']); ?></p>
        <p><strong>Course:</strong> <?php echo htmlspecialchars($data['course']); ?></p>

        <p style="margin-top:20px;">Are you sure you want to delete this applicant?</p>

        <form method="POST">
            <button type="submit" name="confirm_delete" class="btn delete-btn">Yes, Delete</button>
            <a href="scholars_list.php" class="btn cancel-btn">Cancel</a>
        </form>
    </div>
</div>

</body>
</html>