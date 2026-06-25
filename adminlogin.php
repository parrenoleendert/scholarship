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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --blue:          #1A3FA8;
    --blue-mid:      #2B57F5;
    --blue-light:    #EEF1FE;
    --blue-hover:    #1430A0;
    --border:        rgba(0,0,0,0.10);
    --border-focus:  #2B57F5;
    --text-primary:  #111318;
    --text-secondary:#565C72;
    --text-muted:    #9197AD;
    --surface:       #FFFFFF;
    --bg:            #F0F3FB;
    --red:           #B91C1C;
    --red-bg:        #FEE2E2;
    --amber:         #B45309;
    --amber-bg:      #FEF3C7;
    --radius-md:     10px;
    --radius-lg:     18px;
    --shadow:        0 4px 6px rgba(0,0,0,0.05), 0 16px 40px rgba(26,63,168,0.10);
    --transition:    0.16s ease;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background-image:
        linear-gradient(rgba(43,87,245,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(43,87,245,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
}

/* ===== CARD ===== */
.login-card {
    width: 100%;
    max-width: 420px;
    background: var(--surface);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

/* ===== HEADER BAND ===== */

   /* ===== BACK BUTTON ===== */
    .back-btn{
        position: fixed;
        top: 20px;
        left: 20px;
        width: 45px;
        height: 45px;
        background: #ffffff;
        color: var(--blue);
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.10);
        transition: all 0.2s ease;
        z-index: 1000;
    }

    .back-btn i{
        font-size: 22px;
    }

    .back-btn:hover{
        background: var(--black);
        color: #fff;
        transform: translateX(-2px);
    }
    
.card-header {
    background: var(--blue);
    padding: 32px 36px 28px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.card-header::before,
.card-header::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.05);
}
.card-header::before { width: 180px; height: 180px; top: -60px; right: -40px; }
.card-header::after  { width: 120px; height: 120px; bottom: -50px; left: -30px; }

/* ===== LOGO ===== */
.logo-wrap {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: #fff;
    padding: 6px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.20);
    position: relative;
    z-index: 1;
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
    text-align: center;
    position: relative;
    z-index: 1;
}

.header-title {
    font-family: 'Syne', sans-serif;
    font-size: 17px;
    font-weight: 700;
    color: #fff;
    line-height: 1.3;
    margin-bottom: 4px;
}

.header-sub {
    font-size: 12.5px;
    color: rgba(255,255,255,0.65);
    letter-spacing: 0.03em;
}

/* ===== ADMIN BADGE ===== */
.admin-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: #fff;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    position: relative;
    z-index: 1;
}

.admin-badge i { font-size: 13px; }

/* ===== FORM BODY ===== */
.card-body {
    padding: 30px 36px 34px;
}

.form-heading {
    font-family: 'Syne', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.form-subheading {
    font-size: 13.5px;
    color: var(--text-secondary);
    margin-bottom: 24px;
}

/* ===== ERROR ALERT ===== */
.alert-error {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid rgba(185,28,28,0.18);
    border-radius: var(--radius-md);
    padding: 10px 14px;
    font-size: 13.5px;
    font-weight: 500;
    margin-bottom: 20px;
}

.alert-error i { font-size: 16px; flex-shrink: 0; }

/* ===== FIELD ===== */
.field { margin-bottom: 18px; }

.field label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--text-secondary);
    margin-bottom: 7px;
    letter-spacing: 0.02em;
}

.input-wrap { position: relative; }

.input-icon {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 17px;
    pointer-events: none;
    transition: color var(--transition);
}

.input-wrap input {
    width: 100%;
    padding: 11px 14px 11px 40px;
    border: 1.5px solid var(--border);
    border-radius: var(--radius-md);
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    color: var(--text-primary);
    background: #FAFBFF;
    outline: none;
    transition: border-color var(--transition), box-shadow var(--transition), background var(--transition);
}

.input-wrap input::placeholder { color: var(--text-muted); }

.input-wrap input:focus {
    border-color: var(--border-focus);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(43,87,245,0.11);
}

.input-wrap:focus-within .input-icon { color: var(--blue-mid); }

/* Toggle password */
.toggle-pw {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-muted);
    font-size: 17px;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color var(--transition);
}

.toggle-pw:hover { color: var(--blue-mid); }

/* ===== SUBMIT BUTTON ===== */
.btn-login {
    width: 100%;
    padding: 12px;
    background: var(--blue);
    color: #fff;
    border: none;
    border-radius: var(--radius-md);
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 6px;
    letter-spacing: 0.02em;
    transition: background var(--transition), transform 0.14s, box-shadow var(--transition);
    box-shadow: 0 2px 8px rgba(26,63,168,0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-login i { font-size: 18px; }
.btn-login:hover { background: var(--blue-hover); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(26,63,168,0.30); }
.btn-login:active { transform: translateY(0); }

/* ===== DIVIDER ===== */
.divider {
    height: 1px;
    background: var(--border);
    margin: 0 36px 20px;
}

/* ===== FOOTER NOTE ===== */
.card-footer {
    text-align: center;
    padding: 0 36px 26px;
    font-size: 12.5px;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.card-footer i { font-size: 14px; }

</style>
</head>
<body>

<div class="login-card">

    <!-- HEADER WITH LOGO -->
    <div class="card-header">
         <a href="home.php" class="back-btn">
          <i class="ti ti-arrow-left"></i>
             </a>
        <div class="logo-wrap">
            <img src="uploads/sass.jpg" alt="SASS University of Antique Logo">
        </div>
        <div class="header-text">
            <p class="header-title">Student Affairs and Services Division</p>
            <p class="header-sub">University of Antique</p>
        </div>
        <div class="admin-badge">
            <i class="ti ti-shield-lock"></i>
            Administrator Portal
        </div>
    </div>

    <!-- FORM BODY -->
    <div class="card-body">

        <h2 class="form-heading">Admin Login</h2>
        <p class="form-subheading">Restricted access. Authorized personnel only.</p>

        <?php if(isset($error)): ?>
        <div class="alert-error">
            <i class="ti ti-alert-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <!-- USERNAME -->
            <div class="field">
                <label for="username">Admin Username</label>
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

            <!-- PASSWORD -->
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
                Sign In as Admin
            </button>

        </form>

    </div>

    <div class="divider"></div>

    <!-- FOOTER NOTE -->
    <div class="card-footer">
        <i class="ti ti-shield"></i>
        This page is for authorized administrators only.
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