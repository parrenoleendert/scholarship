<?php
require_once("dbconfig.php");
require_once("header.php");

if(isset($_POST['submit'])){

    $scholarship_name = $_POST['scholarship_name'];
    $provider = $_POST['provider'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];
    $deadline = $_POST['deadline'];
    $description = $_POST['description']; // Add this line to get the description

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
            "ssdssss", // Added an 's' for description
            $scholarship_name,
            $provider,
            $amount,
            $deadline,
            $status,
            $description, // Add this parameter
            $new_name
        );

        if($stmt->execute()){

            echo "
            <script>
                alert('Scholarship Added Successfully');
                window.location='scholarship.php';
            </script>
            ";

        }else{

            echo "
            <script>
                alert('Database Error');
            </script>
            ";
        }

    }else{

        echo "
        <script>
            alert('File Upload Failed');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

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

            max-height:90vh;
            overflow-y:auto;
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
        <a href="scholarship.php" class="close-btn">×</a>

        <h2>Add Scholarship</h2>

        <form method="POST" enctype="multipart/form-data">

            <div class="form-group">
                <label>Scholarship Name</label>

                <input type="text"
                       name="scholarship_name"
                       required>
            </div>

            <div class="form-group">
                <label>Provider</label>

                <input type="text"
                       name="provider"
                       required>
            </div>

            <div class="form-group">
                <label>Amount</label>

                <input type="number"
                       step="0.01"
                       name="amount"
                       required>
            </div>

            <div class="form-group">
                    <label>Deadline</label>

                    <input type="date"
                        name="deadline"
                        required>
            </div>

            <div class="form-group">
                <label>Status</label>

                <select name="status" required>

                    <option value="Open">Open</option>
                    <option value="Close">Close</option>

                </select>
            </div>

            <div class="form-group">
                <label>Description</label> <!-- Add this form group for description -->
                <textarea name="description" rows="5" required style="width:100%; padding:12px; border:1px solid #dbe0e6; border-radius:10px; font-size:14px; background:#fff; transition:0.3s; box-sizing:border-box;"></textarea>
            </div>

            <div class="form-group">
                <label>Scholarship File</label>
                <input type="file"
                       name="scholarship_file"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       required>
            </div>

            <button type="submit"
                    name="submit"
                    class="btn-submit">

                Add Scholarship
            </button>

        </form>

    </div>

</div>

</body>
</html>