<?php
require_once("dbconfig.php");
// Do NOT require header.php here, because header.php outputs HTML/text,
// which would cause "headers already sent" when we redirect.


// Validate sid
if (!isset($_GET['sid']) || $_GET['sid'] === '') {
    header("Location: scholarship.php?error=missing_sid");
    exit();
}

$sid = (int)$_GET['sid'];

// Optional: confirm existence first (gives nicer error)
$check = $con->prepare("SELECT sid FROM scholarship WHERE sid = ?");
$check->bind_param("i", $sid);
$check->execute();
$checkRes = $check->get_result();
if ($checkRes->num_rows === 0) {
    header("Location: scholarship.php?error=not_found");
    exit();
}

// Delete
$stmt = $con->prepare("DELETE FROM scholarship WHERE sid = ?");
$stmt->bind_param("i", $sid);

if ($stmt->execute()) {
    header("Location: scholarship.php?deleted=1");
    exit();
}

header("Location: scholarship.php?error=delete_failed");
exit();
?>


