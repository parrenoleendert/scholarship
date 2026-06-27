<?php
require_once("dbconfig.php");
require_once("header.php");

$addSuccess = null;
$errorMessage = ""; // Added to show specific error text dynamically

if(isset($_POST['submit'])){

    $scholarship_name = $_POST['scholarship_name'];
    $provider = $_POST['provider'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    $deadline = $_POST['deadline'];
    $description = $_POST['description']; 

    /* ===== BACKEND DATE VALIDATION ===== */
    // Extract year from the date input string (YYYY-MM-DD format)
    $year = date('Y', strtotime($deadline));

    if (strlen($year) > 4 || (int)$year > 9999 || (int)$year < 1000) {
        $addSuccess = false;
        $errorMessage = "Invalid date value. The deadline year must be a standard 4-digit format (up to 9999).";
    } else {

        /* FILE UPLOAD */
        $file_name = $_FILES['scholarship_file']['name'];
        $tmp_name  = $_FILES['scholarship_file']['tmp_name'];

        $new_name = time() . "_" . $file_name;
        $folder = "uploads/" . $new_name;

        if(move_uploaded_file($tmp_name, $folder)){

            $stmt = $con->prepare("
                INSERT INTO scholarship
                (scholarship_name, provider, amount, deadline, status, description, scholarship_file)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param(
                "ssdssss", 
                $scholarship_name,
                $provider,
                $amount,
                $deadline,
                $status,
                $description, 
                $new_name
            );

            if($stmt->execute()){
                $addSuccess = true;
            }else{
                $addSuccess = false;
                $errorMessage = "Failed to add scholarship due to a database error.";
            }
        }else{
            $addSuccess = false;
            $errorMessage = "Failed to upload file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Scholarship</title>
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
                inset: 0;                          
                background: rgba(15, 23, 42, 0.55);
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;                     
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
            max-height:90vh;
            overflow-y:auto;
        }

        /* ===== ANIMATION ===== */
        @keyframes fadeIn{
            from{ opacity:0; transform:translateY(10px); }
            to{ opacity:1; transform:translateY(0); }
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
            margin-top: 0;
            margin-bottom:20px;
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

        @media(max-width:768px){
            .container{
                width:95%;
                padding:25px;
            }
        }
</style>
</head>
<body>

<div class="overlay">
    <div class="container">
        <a href="scholarship.php" class="close-btn">×</a>

        <h2>Add Scholarship</h2>

        <?php if($addSuccess === true): ?>
            <div class="warning" style="margin-bottom:20px;background:#d4edda;color:#155724;border-radius:10px;padding:12px;border:1px solid #c3e6cb;">
                Scholarship Added Successfully.
            </div>
        <?php elseif($addSuccess === false): ?>
            <div class="warning" style="margin-bottom:20px;background:#f8d7da;color:#721c24;border-radius:10px;padding:12px;border:1px solid #f5c6cb;">
                <?php echo !empty($errorMessage) ? htmlspecialchars($errorMessage) : "Failed to add scholarship. Please check your inputs and try again."; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Scholarship Name</label>
                <input type="text" name="scholarship_name" required value="<?php echo isset($_POST['scholarship_name']) ? htmlspecialchars($_POST['scholarship_name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Provider</label>
                <input type="text" name="provider" required value="<?php echo isset($_POST['provider']) ? htmlspecialchars($_POST['provider']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount" required value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Deadline</label>
                <input type="date" name="deadline" max="9999-12-31" required value="<?php echo isset($_POST['deadline']) ? htmlspecialchars($_POST['deadline']) : ''; ?>">
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" required>
                    <option value="Open" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Open') ? 'selected' : ''; ?>>Open</option>
                    <option value="Close" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Close') ? 'selected' : ''; ?>>Close</option>
                </select>
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="5" required style="width:100%; padding:12px; border:1px solid #dbe0e6; border-radius:10px; font-size:14px; background:#fff; transition:0.3s; box-sizing:border-box; resize: vertical;"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label>Scholarship File</label>
                <input type="file" name="scholarship_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
            </div>

            <button type="submit" name="submit" class="btn-submit">
                Add Scholarship
            </button>

        </form>
    </div>
</div>

</body>
</html>