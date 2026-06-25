<?php
session_start();
require_once("dbconfig.php");

$id = $_SESSION['id'] ?? 0;

if(!$id){
    header("Location: login.php");
    exit();
}

/* ===== FETCH NOTIFICATIONS ===== */
$query = $con->prepare("
    SELECT * FROM notifications
    WHERE student_id = ?
    ORDER BY date_created DESC
    LIMIT 10
");

$query->bind_param("i", $id);
$query->execute();

$result = $query->get_result();

$notifications = $result
    ? $result->fetch_all(MYSQLI_ASSOC)
    : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
 <title>Notifications</title>

 <style>

    /* ===== PAGE ===== */
    body{
        margin:0;
        background:#f1f4f9;
        font-family:"Segoe UI",Tahoma,sans-serif;
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

        z-index:999;
    }

    /* ===== POPUP CONTAINER ===== */
    .container{
        width:90%;
        max-width:650px;

        background:#fff;

        border-radius:16px;

        box-shadow:0 15px 35px rgba(0,0,0,0.25);

        padding:30px;

        position:relative;

        animation:fadeIn 0.3s ease;

        max-height:85vh;
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

    /* ===== NOTIFICATION ITEM ===== */
    .notification-item{
        padding:18px;
        border-bottom:1px solid #eef1f5;
        transition:0.3s;
    }

    .notification-item:last-child{
        border-bottom:none;
    }

    .notification-item:hover{
        background:#f8f9ff;
        border-radius:10px;
    }

    /* ===== MESSAGE ===== */
    .notification-message{
        font-size:15px;
        color:#333;
        font-weight:500;
        line-height:1.5;
    }

    /* ===== DATE ===== */
    .notification-date{
        margin-top:8px;
        font-size:12px;
        color:#6c757d;
    }

    /* ===== EMPTY ===== */
    .empty-state{
        text-align:center;
        padding:40px 20px;
        color:#6c757d;
        font-size:15px;
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
        <a href="dashboardusers.php" class="close-btn">×</a>

        <h2>Your Notifications</h2>

        <?php if(count($notifications) > 0): ?>

            <?php foreach($notifications as $notif): ?>

                <div class="notification-item">

                    <div class="notification-message">
                        <?php echo htmlspecialchars($notif['message']); ?>
                    </div>

                    <div class="notification-date">
                        <?php echo date("F d, Y h:i A", strtotime($notif['date_created'])); ?>
                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">
                No notifications yet.
            </div>

        <?php endif; ?>

    </div>

</div>

</body>
</html>