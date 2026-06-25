<?php
require_once("dbconfig.php");
require_once("header.php");

$id = $_GET['id'] ?? 0;

if(!$id){
    header("Location: scholars_list.php");
    exit();
}

/* FETCH CURRENT DATA */
$stmt = $con->prepare("SELECT * FROM applications_form WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

/* FETCH SCHOLARSHIPS (for dropdown) */
$scholarships = $con->query("SELECT * FROM scholarship");

/* UPDATE DATA */
if(isset($_POST['update'])){

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $course = $_POST['course'];
    $year_section = $_POST['year_section'];
    $status = $_POST['status'];
    $sid = $_POST['sid'];

    $stmt = $con->prepare("UPDATE applications_form 
        SET first_name=?, last_name=?, course=?, year_section=?, status=?, sid=? 
        WHERE id=?");

    $stmt->bind_param("ssssssi", 
        $first_name, 
        $last_name, 
        $course, 
        $year_section, 
        $status, 
        $sid, 
        $id
    );

    if($stmt->execute()){
        header("Location: scholars_list.php?updated=1");
        exit();
    }else{
        echo "Update failed!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Scholar</title>

<style>
      /* RESET (removes blue line / spacing issues) */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, sans-serif;
    background-color: #f4f6f9;
}

/* REMOVE TOP BLUE LINE (from header/nav) */
header, nav {
    border-top: none !important;
    box-shadow: none !important;
}

/* MAIN LAYOUT */
.main {
    margin-left: 260px; /* FIXED (was margin-center ❌) */
    padding: 40px;
    min-height: 100vh;
}

/* CARD */
.card {
    max-width: 700px;
    margin: 0 auto;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 30px 40px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: transform 0.3s, box-shadow 0.3s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    font-size: 28px;
    font-weight: 600;
    color: #333333;
    margin-bottom: 30px;
}

/* FORM LAYOUT */
.info-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.info-label {
    font-weight: 600;
    color: #555555;
}

/* INPUT STYLE */
.info-value input,
.info-value select {
    width: 250px;
    padding: 8px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* BUTTONS */
.button-group {
    margin-top: 30px;
    text-align: center;
}

.button-group a,
.button-group button {
    display: inline-block;
    margin: 10px;
    padding: 10px 25px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    border: none;
    cursor: pointer;
}

/* BUTTON COLORS */
.edit-btn {
    background-color: #28a745;
    color: #fff;
}
.edit-btn:hover {
    background-color: #218838;
}

.back-btn {
    background-color: #6c757d;
    color: #fff;
}
.back-btn:hover {
    background-color: #5a6268;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .main {
        margin-left: 0;
        padding: 20px;
    }

    .info-group {
        flex-direction: column;
        align-items: flex-start;
    }

    .info-value input,
    .info-value select {
        width: 100%;
        margin-top: 5px;
    }
}
</style>
</head>

<body>

<main class="main">
    <div class="card">
        <h2>Edit Applicant</h2>

        <form method="POST">

        <div class="info-group">
            <div class="info-label">First Name:</div>
            <div class="info-value">
                <input type="text" name="first_name" 
                value="<?php echo htmlspecialchars($data['first_name']); ?>" required>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Last Name:</div>
            <div class="info-value">
                <input type="text" name="last_name" 
                value="<?php echo htmlspecialchars($data['last_name']); ?>" required>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Course:</div>
            <div class="info-value">
                <input type="text" name="course" 
                value="<?php echo htmlspecialchars($data['course']); ?>" required>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Year/Section:</div>
            <div class="info-value">
                <input type="text" name="year_section" 
                value="<?php echo htmlspecialchars($data['year_section']); ?>" required>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Scholarship:</div>
            <div class="info-value">
                <select name="sid" required>
                    <?php while($s = $scholarships->fetch_assoc()){ ?>
                        <option value="<?php echo $s['sid']; ?>"
                        <?php if($s['sid'] == $data['sid']) echo "selected"; ?>>
                        <?php echo $s['scholarship_name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="info-group">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <select name="status" required>
                    <option value="Pending" <?php if($data['status']=="Pending") echo "selected"; ?>>Pending</option>
                    <option value="Approved" <?php if($data['status']=="Approved") echo "selected"; ?>>Approved</option>
                    <option value="Rejected" <?php if($data['status']=="Rejected") echo "selected"; ?>>Rejected</option>
                </select>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" name="update" class="edit-btn">Update</button>
            <a href="scholars_list.php" class="back-btn">Cancel</a>
        </div>

        </form>
    </div>
</main>

</body>
</html>