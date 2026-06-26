<?php
session_start();
require_once("dbconfig.php");

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT * FROM admin WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $_SESSION['adminid'] = $row['adminid'];
        header("Location: dashboardadmin.php");
        exit();
    } else {
        $error = "Invalid admin credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — SASS UA</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    /* Dashboard-aligned color palette */
    --blue:          #0d6efd;
    --blue-hover:    #0b5ed7;
    --blue-light:    #eff6ff;
    --border:        #e2e8f0;
    --border-focus:  #3b82f6;
    --text-primary:  #1e293b;
    --text-secondary:#475569;
    --text-muted:    #94a3b8;
    --surface:       #FFFFFF;
    --bg:            #f8fafc;
    --red:           #ef4444;
    --red-bg:        #ffeeee;
    --radius-md:     12px;
    --radius-lg:     20px;
    --shadow:        0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    --transition:    0.2s ease;
}

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
}

/* ===== ENLARGED LOGIN CARD ===== */
.login-card {
    width: 100%;
    max-width: 480px; /* Made wider for better visual balance */
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

/* ===== INTEGRATED HEADER ===== */
.card-header {
    background: #ffffff;
    padding: 40px 40px 20px 40px;
    display: flex;
    flex-direction: column;
    position: relative;
}

/* ===== INTERNAL BACK BUTTON ===== */
.back-btn-container {
    margin-bottom: 24px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 30px;
    background: var(--bg);
    border: 1px solid var(--border);
    transition: all var(--transition);
    align-self: flex-start;
}

.back-btn i {
    font-size: 16px;
}

.back-btn:hover {
    color: var(--blue);
    background: var(--blue-light);
    border-color: rgba(13, 110, 253, 0.2);
    transform: translateX(-2px);
}

/* ===== LOGO & HEADER ROW ===== */
.header-brand-row {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
}

.logo-wrap {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: #fff;
    padding: 2px;
    border: 1px solid var(--border);
    flex-shrink: 0;
}

.logo-wrap img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

.header-text {
    text-align: left;
}

.header-title {
    font-size: 18px;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.3;
    letter-spacing: -0.3px;
}

.header-sub {
    font-size: 13px;
    color: var(--text-secondary);
    font-weight: 500;
}

/* ===== FORM BODY ===== */
.card-body {
    padding: 10px 40px 40px 40px;
}

.form-heading {
    font-size: 24px;
    font-weight: 800;
    color: var(--text-primary);
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.form-subheading {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 28px;
}

/* ===== ERROR ALERT ===== */
.alert-error {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid rgba(239, 68, 68, 0.15);
    border-radius: var(--radius-md);
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 24px;
}

.alert-error i { font-size: 18px; flex-shrink: 0; }

/* ===== FIELDS ===== */
.field { margin-bottom: 22px; }

.field label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 8px;
}

.input-wrap { position: relative; }

.input-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 18px;
    pointer-events: none;
    transition: color var(--transition);
}

.input-wrap input {
    width: 100%;
    padding: 13px 16px 13px 46px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-family: 'Inter', sans-serif;
    font-size: 14.5px;
    color: var(--text-primary);
    background: #ffffff;
    outline: none;
    transition: all var(--transition);
}

.input-wrap input::placeholder { color: var(--text-muted); }

.input-wrap input:focus {
    border-color: var(--border-focus);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.input-wrap:focus-within .input-icon { color: var(--border-focus); }

/* Toggle password button inside field */
.toggle-pw {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 18px;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color var(--transition);
}

.toggle-pw:hover { color: var(--text-primary); }

/* ===== SUBMIT ACTION ===== */
.btn-login {
    width: 100%;
    padding: 14px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-family: 'Inter', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 10px;
    transition: all var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-login i { font-size: 18px; }
.btn-login:hover { background: var(--blue-hover); box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2); }

/* ===== FOOTER DIVIDER & TEXT ===== */
.divider {
    height: 1px;
    background: var(--border);
    margin: 0 40px 24px;
}

.card-footer {
    text-align: center;
    padding: 0 40px 32px;
    font-size: 13px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-weight: 500;
}

.card-footer i { font-size: 15px; }
</style>
</head>
<body>

<div class="login-card">

    <div class="card-header">
        <div class="back-btn-container">
            <a href="home.php" class="back-btn">
                <i class="ti ti-arrow-left"></i>
                Back to Portal
            </a>
        </div>
        
        <div class="header-brand-row">
            <div class="logo-wrap">
                <img src="uploads/sass.jpg" alt="SASS Logo">
            </div>
            <div class="header-text">
                <p class="header-title">Student Affairs Services</p>
                <p class="header-sub">University of Antique</p>
            </div>
        </div>
    </div>

    <div class="card-body">

        <h2 class="form-heading">Admin Login</h2>
        <p class="form-subheading">Authorized portal access personnel only.</p>

        <?php if(isset($error)): ?>
        <div class="alert-error">
            <i class="ti ti-alert-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <div class="field">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter admin username"
                        required
                        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <i class="ti ti-user-shield input-icon"></i>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required>
                    <i class="ti ti-lock input-icon"></i>
                    <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Toggle password visibility">
                        <i class="ti ti-eye" id="eye-icon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">
                <i class="ti ti-login"></i>
                Sign In to Dashboard
            </button>

        </form>

    </div>

    <div class="divider"></div>

    <div class="card-footer">
        <i class="ti ti-shield"></i>
        Secure administrative checkpoint.
    </div>

</div>

<script>
function togglePassword() {
    const pw   = document.getElementById('password');
    const icon = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        icon.className = 'ti ti-eye-off';
    } else {
        pw.type = 'password';
        icon.className = 'ti ti-eye';
    }
}
</script>

</body>
</html>