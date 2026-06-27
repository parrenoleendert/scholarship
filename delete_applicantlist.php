<?php
require_once("dbconfig.php");

if(!isset($_GET['id'])){
    header("Location: application_list.php");
    exit();
}

$id = (int)$_GET['id'];

$stmt = $con->prepare("DELETE FROM applications_form WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: scholars_list.php?deleted=1");
exit();
?>
<!DOCTYPE html>
<html>
<head>

<title>Delete Applicant</title>

<style>

/* ===== PAGE ===== */

body{
    margin:0;
    font-family:"Segoe UI",Tahoma,sans-serif;
    background:#f1f4f9;
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

/* ===== MODAL ===== */

.modal-box{
    background:#fff;

    width:90%;
    max-width:600px;

    padding:30px;

    border-radius:16px;

    position:relative;

    animation:popup 0.3s ease;

    text-align:center;
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
    margin-bottom:15px;
    color:#dc3545;
}

/* ===== MESSAGE ===== */

.message{
    font-size:16px;
    color:#444;
    margin-bottom:25px;
    line-height:1.6;
}

/* ===== APPLICANT INFO ===== */

.info-box{
    background:#f8f9fa;
    border-radius:10px;
    padding:15px;
    margin-bottom:25px;

    text-align:left;
}

.info{
    margin-bottom:10px;
}

.label{
    font-weight:600;
    color:#333;
}

/* ===== BUTTONS ===== */

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

    .modal-box{
        width:95%;
        padding:25px;
    }

}
</style>

</head>

<body>
    <?php if(isset($_GET['deleted'])): ?>
        <div id="toastMsg" style="
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background:#16a34a; color:#fff;
        padding:12px 20px; border-radius:10px;
        font-size:14px; font-weight:600;
        box-shadow: 0 4px 14px rgba(0,0,0,.15);
        ">
        ✓ Applicant deleted successfully.
        </div>
        <script>
        setTimeout(() => document.getElementById('toastMsg').remove(), 3000);
        </script>
    <?php endif; ?>

<div class="overlay">

    <div class="modal-box">

        <!-- CLOSE -->
        

        <h2>Delete Applicant</h2>

        <div class="message">
            Are you sure you want to delete this applicant?
        </div>

        <!-- INFO -->
        <div class="info-box">

            <div class="info">
                <span class="label">School ID:</span>
                <?php echo htmlspecialchars($row['school_id']); ?>
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
                <span class="label">Scholarship:</span>
                <?php echo htmlspecialchars($row['scholarship_name']); ?>
            </div>

        </div>

        <?php if($deleteSuccess === true): ?>
            <div class="message" style="margin-bottom:18px;background:#d4edda;color:#155724;border-radius:10px;padding:12px;">
                Applicant deleted successfully.
            </div>
            <a href="scholars_list.php" class="btn cancel-btn" style="margin-left:0;">Go to Scholarships</a>
        <?php elseif($deleteSuccess === false): ?>
            <div class="message" style="margin-bottom:18px;background:#f8d7da;color:#721c24;border-radius:10px;padding:12px;">
                Delete failed.
            </div>
            <form method="POST" style="margin-top:12px;">
                <button type="submit" name="delete" class="btn delete-btn">Try Again</button>
                <a href="scholars_list.php" class="btn cancel-btn">Cancel</a>
            </form>
        <?php else: ?>

        <!-- FORM -->
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

