<?php
require_once("dbconfig.php");

$message = "";
$messageType = ""; // success or error

if(isset($_POST['register'])){

    $student_id   = $_POST['student_id'];
    $first_name   = $_POST['first_name'];
    $last_name    = $_POST['last_name'];
    $course       = $_POST['course'];
    $year_section = $_POST['year_section'];
    $email        = $_POST['email'];
    $phone        = $_POST['phone'];
    $address      = $_POST['address'];
    $username     = $_POST['username'];
    $raw_password = $_POST['password'];

    if(strlen($raw_password) < 8 || strlen($raw_password) > 20){
        $message = "Password must be between 8 and 20 characters.";
        $messageType = "error";
        }
        elseif($_POST['password'] !== $_POST['confirm_password']){
            $message = "Passwords do not match.";
            $messageType = "error";
        }
        elseif(!preg_match('/^09\d{9}$/', $phone)){
            $message = "Please enter a valid Philippine mobile number.";
            $messageType = "error";
        }
        else{

        $password = password_hash($raw_password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO students
        (student_id, first_name, last_name, course, year_section, email, phone, address, username, password)
        VALUES
        ('$student_id','$first_name','$last_name','$course','$year_section','$email','$phone','$address','$username','$password')";

        if($con->query($sql)){
            $message = "Registration Successful!";
            $messageType = "success";
        }else{
            $message = $con->error;
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Student Registration</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: linear-gradient(135deg, #ffff, #ffff);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.container {
    width: 420px;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.container h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
}

.logo {
    text-align: center;
    font-size: 30px;
    margin-bottom: 10px;
    color: #4CAF50;
}

.input-group {
    margin-bottom: 12px;
}

.input-group input,
.input-group select,
.input-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
    font-size: 14px;
}

.input-group textarea {
    resize: none;
    height: 70px;
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {
    border-color: #1A73E8;
    box-shadow: 0 0 5px rgba(85, 67, 163, 0.4);
}

.row {
    display: flex;
    gap: 10px;
}

.row .input-group {
    flex: 1;
}

button {
    width: 100%;
    padding: 12px;
    background: #1A73E8;
    border: none;
    color: white;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
}

button:hover {
    background: #1557B0;
}

p {
    text-align: center;
    margin-top: 15px;
    font-size: 14px;
}

a {
    color: #1A73E8;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

/* Password hint text */
.hint {
    font-size: 12px;
    color: #888;
    margin-top: 4px;
    display: block;
}
.message{
    padding:12px;
    border-radius:8px;
    margin-bottom:18px;
    text-align:center;
    font-weight:600;
}

.message.success{
    background:#d4edda;
    color:#155724;
    border:1px solid #28a745;
}

.message.error{
    background:#f8d7da;
    color:#721c24;
    border:1px solid #dc3545;
}
</style>
</head>

<body>

<div class="container">
    <div class="logo"></div>
    <?php if($message != ""){ ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php } ?>
    <h2>Create Account</h2>

    <form method="POST" onsubmit="return validatePassword()">

        <div class="input-group">
            <input type="text" name="student_id" placeholder="Student ID" required>
        </div>

        <div class="row">
            <div class="input-group">
                <input type="text" name="first_name" placeholder="First Name" required>
            </div>

            <div class="input-group">
                <input type="text" name="last_name" placeholder="Last Name" required>
            </div>
        </div>

        <div class="row">
            <div class="input-group">
                <input type="text" name="course" placeholder="Course" required>
            </div>

              <div class="input-group">
                <input type="text" name="year_section" placeholder="Year/Section" required>
            </div>
        </div>

        <div class="input-group">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="row">
            <div class="input-group">
                <input type="text" name="phone" placeholder="Phone Number" maxlength="20" required>
            </div>

            <div class="input-group">
                <textarea name="address" placeholder="Address"></textarea>
            </div>
        </div>

        <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
        </div>

        <div class="input-group">
            <input type="password" name="password" id="password" placeholder="Password"
                   minlength="8" maxlength="20" required>
            <span class="hint">Password must be 8–20 characters.</span>
        </div>
        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password"
                minlength="8" maxlength="20" required>
            <span class="hint" id="matchHint" style="color:#888;">Re-enter your password.</span>
        </div>

        <button name="register">Register</button>

    </form>

    <p>Already have an account? <a href="login.php">Sign In</a></p>
</div>

<script>
const pw  = document.getElementById('password');
const cpw = document.getElementById('confirm_password');
const hint = document.getElementById('matchHint');

cpw.addEventListener('input', function() {
    if (cpw.value === '') {
        hint.style.color = '#888';
        hint.textContent = 'Re-enter your password.';
    } else if (cpw.value === pw.value) {
        hint.style.color = '#16a34a';
        hint.textContent = '✓ Passwords match.';
    } else {
        hint.style.color = '#dc2626';
        hint.textContent = '✗ Passwords do not match.';
    }
});

function validatePassword() {
    if (pw.value !== cpw.value) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}
</script>
</body>
</html>