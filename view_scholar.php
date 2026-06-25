<?php
require_once("dbconfig.php");
require_once("header.php");

$id = $_GET['id'] ?? 0;
if (!$id) {
    header("Location: ");
    exit();
}

// Fetch applicant data
$stmt = $con->prepare("SELECT a.*, s.scholarship_name 
                       FROM applications_form a 
                       JOIN scholarship s ON a.sid = s.sid 
                       WHERE a.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$applicant = $result->fetch_assoc();

if (!$applicant) {
    echo "<p>Applicant not found.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Applicant Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .main {
            margin-center: 260px;
            padding: 40px;
            min-height: 100vh;
        }

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

        .info-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: 600;
            color: #555555;
        }

        .info-value {
            color: #222222;
            text-align: right;
        }

        .button-group {
            margin-top: 30px;
            text-align: center;
        }

        .button-group a {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.3s, color 0.3s;
        }

        .edit-btn {
            background-color: #28a745;
            color: #fff;
        }
        .edit-btn:hover {
            background-color: #218838;
        }

        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }

        .back-btn {
            background-color: #6c757d;
            color: #fff;
        }
        .back-btn:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 20px;
            }

            .info-group {
                flex-direction: column;
                text-align: left;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>

<main class="main">
    <div class="card">
        <h2>Applicant Details</h2>

        <div class="info-group">
            <div class="info-label">School ID:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['school_id']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Name:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['first_name'] . ' ' . $applicant['last_name']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Course:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['course']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Year/section:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['year_section']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Scholarship:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['scholarship_name']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Status:</div>
            <div class="info-value"><?php echo htmlspecialchars($applicant['status']); ?></div>
        </div>

        <div class="info-group">
            <div class="info-label">Date Applied:</div>
            <div class="info-value"><?php echo date("F d, Y", strtotime($applicant['date_applied'])); ?></div>
        </div>

        <div class="button-group">
            <a href="edit_scholar.php?id=<?php echo $applicant['id']; ?>" class="edit-btn">Edit</a>
            <a href="delete_scholar.php?id=<?php echo $applicant['id']; ?>" onclick="return confirm('Are you sure you want to delete this applicant?');" class="delete-btn">Delete</a>
            <a href="scholars_list.php" class="back-btn">Back to List</a>
        </div>
    </div>
</main>

</body>
</html>