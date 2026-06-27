<?php
require_once("dbconfig.php");
session_start();

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['lockout_time']))   $_SESSION['lockout_time']   = null;

$lockout_limit   = 5;
$lockout_seconds = 300;

$is_locked = false;
$remaining = 0;

if ($_SESSION['lockout_time'] && time() < $_SESSION['lockout_time']) {
    $is_locked = true;
    $remaining = $_SESSION['lockout_time'] - time();
} elseif ($_SESSION['lockout_time'] && time() >= $_SESSION['lockout_time']) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time']   = null;
}

if (isset($_POST['login']) && !$is_locked) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT id, first_name, last_name, password FROM students WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['lockout_time']   = null;
        session_regenerate_id(true);
        $_SESSION['id']         = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name']  = $user['last_name'];
        header("Location: dashboardusers.php");
        exit();
    } else {
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= $lockout_limit) {
            $_SESSION['lockout_time'] = time() + $lockout_seconds;
            // PRG: redirect so refresh won't resubmit
            header("Location: login.php?locked=1");
        } else {
            $left = $lockout_limit - $_SESSION['login_attempts'];
            $_SESSION['login_error'] = "Invalid username or password. {$left} attempt(s) remaining.";
            // PRG: redirect so refresh won't resubmit
            header("Location: login.php?error=1");
        }
        exit();
    }
}

// ── Read flash messages ──
$error = null;
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($_SESSION['lockout_time'] && time() < $_SESSION['lockout_time']) {
    $is_locked = true;
    $remaining = $_SESSION['lockout_time'] - time();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login — SASS UA</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Syne:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --blue-primary: #1A73E8;
        --blue-hover:   #1557B0;
        --border-color: #E0E3EB;
        --border-focus: #1A73E8;
        --text-primary: #1C1E21;
        --text-secondary:#5F6368;
        --text-muted:   #9AA0A6;
        --surface:      #FFFFFF;
        --bg:           #F8F9FA;
        --red:          #D93025;
        --red-bg:       #FCE8E6;
        --radius-md:    8px;
        --radius-lg:    16px;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--bg);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
    }

    /* ===== MAIN CARD ===== */
    .login-card {
        width: 100%;
        max-width: 440px;
        background: var(--surface);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        padding: 40px 36px;
    }

    /* ===== BACK TO PORTAL ===== */
    .back-btn-container {
        margin-bottom: 24px;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #FFFFFF;
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .back-btn i {
        font-size: 15px;
    }

    .back-btn:hover {
        background: var(--bg);
        color: var(--text-primary);
        border-color: #C3C7D0;
    }

    /* ===== BRANDING (HORIZONTAL LAYOUT) ===== */
    .brand-section {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .logo-wrap {
        width: 44px;
        height: 44px;
        flex-shrink: 0;
    }

    .logo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
    }

    .header-title {
        font-size: 15px;
        font-weight: 700;
        color: #0F172A;
        line-height: 1.2;
    }

    .header-sub {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 2px;
    }

    /* ===== FORM TYPOGRAPHY ===== */
    .form-heading {
        font-size: 22px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 6px;
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
        border: 1px solid rgba(217,48,37,0.15);
        border-radius: var(--radius-md);
        padding: 10px 14px;
        font-size: 13.5px;
        font-weight: 500;
        margin-bottom: 20px;
    }

    /* ===== INPUT FIELDS ===== */
    .field {
        margin-bottom: 18px;
    }

    .field label {
        display: block;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .input-wrap {
        position: relative;
    }

    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
        font-size: 17px;
        pointer-events: none;
    }

    .input-wrap input {
        width: 100%;
        padding: 12px 14px 12px 42px;
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        color: var(--text-primary);
        background: #FFFFFF;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .input-wrap input::placeholder { color: var(--text-muted); }

    .input-wrap input:focus {
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
    }

    /* Toggle password visibility */
    .toggle-pw {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-muted);
        font-size: 17px;
        display: flex;
        align-items: center;
        padding: 4px;
    }

    .toggle-pw:hover { color: var(--text-secondary); }

    /* ===== BUTTONS ===== */
    .btn-login {
        width: 100%;
        padding: 12px;
        background: var(--blue-primary);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        font-family: 'DM Sans', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.15s;
    }

    .btn-login:hover { background: var(--blue-hover); }

    /* ===== FOOTER LINKS ===== */
    .card-footer {
        text-align: center;
        margin-top: 24px;
        font-size: 13.5px;
        color: var(--text-secondary);
    }

    .card-footer a {
        color: var(--blue-primary);
        font-weight: 600;
        text-decoration: none;
    }

    .card-footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<div class="login-card">

    <div class="back-btn-container">
        <a href="home.php" class="back-btn">
            <i class="ti ti-arrow-left"></i> Back to Portal
        </a>
    </div>

    <div class="brand-section">
        <div class="logo-wrap">
            <img src="uploads/sass.jpg" alt="SASS University of Antique Logo">
        </div>
        <div class="brand-text">
            <span class="header-title">Student Affairs Services</span>
            <span class="header-sub">University of Antique</span>
        </div>
    </div>

    <h2 class="form-heading">Student Login</h2>
    <p class="form-subheading">Sign in to access your scholarship portal.</p>

    <?php if ($is_locked): ?>
        <div class="alert-error">
            <i class="ti ti-lock"></i>
            Too many failed attempts. Try again in
            <strong id="countdown"><?= $remaining ?></strong> seconds.
        </div>
        <script>
        let t = <?= $remaining ?>;
        const el = document.getElementById('countdown');
        const timer = setInterval(() => {
            t--;
            el.textContent = t;
            if (t <= 0) { clearInterval(timer); location.reload(); }
        }, 1000);
        </script>
        <?php elseif (isset($error)): ?>
        <div class="alert-error">
            <i class="ti ti-alert-circle"></i>
            <?= htmlspecialchars($error) ?>
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
                    placeholder="Enter your username"
                    required
                    value="<?php echo (!$is_locked && isset($_POST['username'])) ? htmlspecialchars($_POST['username']) : ''; ?>"
                >
                <i class="ti ti-user input-icon"></i>
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

        <button type="submit" name="login" class="btn-login" <?= $is_locked ? 'disabled style="opacity:.5;cursor:not-allowed;"' : '' ?>>
            <i class="ti ti-login"></i>
                Sign In
        </button>

    </form>
    <div class="card-footer">
        Don't have an account? <a href="signup.php">Create one</a>
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