<?php
require_once("dbconfig.php");

if(isset($_GET['id']) && isset($_GET['status'])){

    $app_id = $_GET['id'];
    $status = $_GET['status'];

    /* ===== UPDATE STATUS ===== */
    $update = $con->prepare("
        UPDATE applications_form 
        SET status=? 
        WHERE aid=?
    ");

    if(!$update){
        die("Prepare failed: " . $con->error);
    }

    $update->bind_param("si", $status, $app_id);

    if($update->execute()){

        /* ===== IF APPROVED ===== */
        if($status == "Approved" || $status == "Rejected"){

            /* GET APPLICATION INFO */
                $getStudent = $con->prepare("
                    SELECT
                        a.aid,
                        a.id,
                        a.sid,
                        a.first_name,
                        a.last_name,
                        s.scholarship_name
                    FROM applications_form a
                    LEFT JOIN scholarship s ON a.sid = s.sid
                    WHERE a.aid = ?
                ");

            $getStudent->bind_param("i", $app_id);
            $getStudent->execute();

            $res = $getStudent->get_result();

            if($res->num_rows > 0){

                $row = $res->fetch_assoc();

                $student_id = $row['id'];
                $sid = $row['sid'];

                $name = $row['first_name'] . " " . $row['last_name'];
                $scholarship_name = $row['scholarship_name'];

                /* MESSAGE */
                if($status == "Approved"){
                    $message = " Congratulations $name! Your application for the '$scholarship_name'  has been approved. Welcome to the scholarship program.";
                }
                elseif($status == "Rejected"){
                    $message = "Dear $name, we regret to inform you that your application for the '$scholarship_name'  was not approved. Thank you for your interest, and we encourage you to apply for future scholarship opportunities.";
                }
                /* INSERT NOTIFICATION */
                $notify = $con->prepare("
                    INSERT INTO notifications(student_id, sid, message)
                    VALUES (?, ?, ?)
                ");

                $notify->bind_param("iis", $student_id, $sid, $message);

                $notify->execute();
            }
        }

        header("Location: newapplication.php");
        exit();

    }else{
        echo "Failed to update status.";
    }
}
?>