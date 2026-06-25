<?php
session_start();
require_once("dbconfig.php");

if (!isset($_SESSION['adminid'])) {
    header("Location: adminlogin.php");
    exit();
}

$admin_id = $_SESSION['adminid'];

// Fetch current admin data
$stmt = $con->prepare("SELECT * FROM admin WHERE adminid = ?");
if (!$stmt) die("Prepare failed: " . $con->error);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
if (!$admin) die("Administrator account not found.");

$success_profile  = "";
$error_profile    = "";
$success_password = "";
$error_password   = "";

// ── HANDLE PROFILE UPDATE ────────────────────────────────────────────────────
if (isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $phone      = trim($_POST['phone']);
    $address    = trim($_POST['address']);

    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_profile = "First name, last name, and email are required.";
    } else {
        // Check if email is taken by another admin
        $chk = $con->prepare("SELECT adminid FROM admin WHERE email = ? AND adminid != ?");
        $chk->bind_param("si", $email, $admin_id);
        $chk->execute();
        $chk->store_result();

        if ($chk->num_rows > 0) {
            $error_profile = "That email address is already in use.";
        } else {
            $upd = $con->prepare("UPDATE admin SET first_name=?, last_name=?, email=?, phone=?, address=? WHERE adminid=?");
            $upd->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $admin_id);
            if ($upd->execute()) {
                $success_profile = "Profile updated successfully.";
                // Refresh data
                $stmt2 = $con->prepare("SELECT * FROM admin WHERE adminid = ?");
                $stmt2->bind_param("i", $admin_id);
                $stmt2->execute();
                $admin = $stmt2->get_result()->fetch_assoc();
            } else {
                $error_profile = "Failed to update profile. Please try again.";
            }
        }
    }
}

// ── HANDLE PASSWORD CHANGE ───────────────────────────────────────────────────
if (isset($_POST['change_password'])) {
    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if (empty($current) || empty($new_pass) || empty($confirm)) {
        $error_password = "All password fields are required.";
    } elseif ($new_pass !== $confirm) {
        $error_password = "New password and confirmation do not match.";
    } elseif (strlen($new_pass) < 8) {
        $error_password = "New password must be at least 8 characters.";
    } else {
        // Verify current password (supports both plain and hashed)
        $valid = false;
        if (password_verify($current, $admin['password'])) {
            $valid = true;
        } elseif ($current === $admin['password']) {
            $valid = true; // plain-text fallback (legacy)
        }

        if (!$valid) {
            $error_password = "Current password is incorrect.";
        } else {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $upd = $con->prepare("UPDATE admin SET password=? WHERE adminid=?");
            $upd->bind_param("si", $hashed, $admin_id);
            if ($upd->execute()) {
                $success_password = "Password changed successfully.";
            } else {
                $error_password = "Failed to update password. Please try again.";
            }
        }
    }
}
?>
<?php require_once("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Administrator Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

*{ margin:0; padding:0; box-sizing:border-box; }

:root{
    --primary:#7c3aed;
    --primary-hover:#6d28d9;
    --bg:#f4f7fb;
    --card:#ffffff;
    --text:#1f2937;
    --muted:#6b7280;
    --border:#e5e7eb;
    --danger:#dc2626;
    --success:#16a34a;
}

body{
    font-family:'Plus Jakarta Sans', sans-serif;
    background:var(--bg);
    min-height:100vh;
}

/* ── OVERLAY ─────────────────────────────────────── */
.overlay-backdrop{
    position:fixed; inset:0;
    background:rgba(15,23,42,.55);
    backdrop-filter:blur(4px);
    -webkit-backdrop-filter:blur(4px);
    z-index:9999;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:20px;
    animation:fadeIn .2s ease;
}

@keyframes fadeIn{ from{opacity:0} to{opacity:1} }
@keyframes slideUp{
    from{opacity:0;transform:translateY(30px) scale(.98)}
    to  {opacity:1;transform:translateY(0)    scale(1) }
}

.overlay-modal{
    position:relative;
    background:var(--card);
    border-radius:22px;
    width:100%;
    max-width:780px;
    max-height:90vh;
    overflow-y:auto;
    box-shadow:0 25px 60px rgba(0,0,0,.2);
    animation:slideUp .3s ease;
}

/* ── CLOSE ───────────────────────────────────────── */
.close-btn{
    position:sticky; top:0; z-index:10;
    display:flex; justify-content:flex-end;
    padding:16px 20px 0;
    background:var(--card);
}

.close-btn a{
    width:36px; height:36px;
    border-radius:50%;
    background:#f1f5f9;
    border:1px solid var(--border);
    display:flex; align-items:center; justify-content:center;
    color:var(--muted);
    text-decoration:none;
    font-size:16px;
    transition:.2s;
}

.close-btn a:hover{
    background:#fee2e2;
    border-color:#fca5a5;
    color:var(--danger);
}

/* ── INNER ───────────────────────────────────────── */
.modal-inner{ padding:10px 30px 30px; }

.page-header{ margin-bottom:6px; }

.page-title{ font-size:24px; font-weight:700; color:var(--text); }
.page-sub  { color:var(--muted); margin-top:4px; font-size:13px; }

/* ── TABS ────────────────────────────────────────── */
.tabs{
    display:flex;
    gap:4px;
    margin:20px 0 24px;
    background:#f1f5f9;
    border-radius:12px;
    padding:4px;
}

.tab-btn{
    flex:1;
    padding:10px 16px;
    border:none;
    border-radius:9px;
    background:transparent;
    color:var(--muted);
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:7px;
}

.tab-btn.active{
    background:var(--card);
    color:var(--primary);
    box-shadow:0 1px 4px rgba(0,0,0,.08);
}

.tab-btn:hover:not(.active){
    color:var(--text);
}

/* ── TAB PANELS ──────────────────────────────────── */
.tab-panel{ display:none; }
.tab-panel.active{ display:block; }

/* ── AVATAR PREVIEW ──────────────────────────────── */
.avatar-row{
    display:flex;
    align-items:center;
    gap:18px;
    margin-bottom:26px;
    padding:18px;
    background:#f9fafb;
    border:1px solid var(--border);
    border-radius:14px;
}

.avatar-preview{
    width:72px; height:72px;
    border-radius:50%;
    border:3px solid #ede9fe;
    object-fit:cover;
    flex-shrink:0;
}

.avatar-info .av-name{ font-size:15px; font-weight:700; color:var(--text); }
.avatar-info .av-sub { font-size:12px; color:var(--muted); margin-top:3px; }

/* ── FORM ────────────────────────────────────────── */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
}

.form-group{ display:flex; flex-direction:column; gap:6px; }
.form-group.full{ grid-column:1 / -1; }

label{
    font-size:11px;
    font-weight:700;
    color:var(--muted);
    text-transform:uppercase;
    letter-spacing:.05em;
}

input[type="text"],
input[type="email"],
input[type="tel"],
input[type="password"],
textarea{
    padding:11px 14px;
    border:1px solid var(--border);
    border-radius:10px;
    font-size:14px;
    color:var(--text);
    background:#fff;
    font-family:inherit;
    transition:.2s;
    outline:none;
    width:100%;
}

input:focus, textarea:focus{
    border-color:var(--primary);
    box-shadow:0 0 0 3px rgba(124,58,237,.1);
}

textarea{ resize:vertical; min-height:80px; }

/* ── PASSWORD FIELD ──────────────────────────────── */
.pass-wrap{ position:relative; }
.pass-wrap input{ padding-right:44px; }
.toggle-pw{
    position:absolute;
    right:13px; top:50%;
    transform:translateY(-50%);
    background:none; border:none;
    color:var(--muted);
    cursor:pointer;
    font-size:14px;
    padding:0;
    transition:.15s;
}
.toggle-pw:hover{ color:var(--text); }

/* ── PASSWORD STRENGTH ───────────────────────────── */
.strength-bar{
    height:4px;
    border-radius:4px;
    background:#e5e7eb;
    margin-top:8px;
    overflow:hidden;
}

.strength-fill{
    height:100%;
    border-radius:4px;
    transition:width .3s, background .3s;
    width:0%;
}

.strength-label{
    font-size:11px;
    margin-top:4px;
    color:var(--muted);
}

/* ── ALERTS ──────────────────────────────────────── */
.alert{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 16px;
    border-radius:10px;
    font-size:13px;
    font-weight:500;
    margin-bottom:20px;
}

.alert-success{
    background:#f0fdf4;
    border:1px solid #bbf7d0;
    color:var(--success);
}

.alert-error{
    background:#fef2f2;
    border:1px solid #fecaca;
    color:var(--danger);
}

/* ── ACTIONS ─────────────────────────────────────── */
.form-actions{
    display:flex;
    gap:12px;
    margin-top:24px;
    flex-wrap:wrap;
}

.btn{
    padding:11px 22px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    font-size:14px;
    font-family:inherit;
    text-decoration:none;
    transition:.25s;
    display:inline-flex;
    align-items:center;
    gap:8px;
}

.btn-primary{ background:var(--primary); color:#fff; }
.btn-primary:hover{ background:var(--primary-hover); }

.btn-secondary{
    background:#fff;
    border:1px solid var(--border);
    color:var(--text);
}
.btn-secondary:hover{ background:#f3f4f6; }

/* ── SECTION TITLE ───────────────────────────────── */
.section-title{
    font-size:12px;
    font-weight:700;
    color:var(--muted);
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:16px;
}

/* ── HINT LIST ───────────────────────────────────── */
.hint-list{
    margin-top:20px;
    padding:16px;
    background:#f9fafb;
    border:1px solid var(--border);
    border-radius:12px;
}

.hint-list p{
    font-size:12px;
    font-weight:700;
    color:var(--muted);
    text-transform:uppercase;
    letter-spacing:.05em;
    margin-bottom:10px;
}

.hint-list ul{
    list-style:none;
    display:flex;
    flex-direction:column;
    gap:6px;
}

.hint-list li{
    font-size:12px;
    color:var(--muted);
    display:flex;
    align-items:center;
    gap:7px;
}

.hint-list li i{
    font-size:10px;
    width:16px;
    height:16px;
    border-radius:50%;
    background:#e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
}

.hint-list li.met i{ background:#dcfce7; color:#16a34a; }
.hint-list li.met  { color:var(--text); }

/* ── RESPONSIVE ──────────────────────────────────── */
@media(max-width:600px){
    .form-grid{ grid-template-columns:1fr; }
    .form-group.full{ grid-column:1; }
    .modal-inner{ padding:10px 18px 24px; }
}

</style>
</head>
<body>

<div class="overlay-backdrop" id="overlay" onclick="handleBackdropClick(event)">
<div class="overlay-modal" id="modal">



    <div class="modal-inner">

        <div class="page-header">
            <div class="page-title">Edit Profile</div>
            <div class="page-sub">Update your administrator information and security settings</div>
        </div>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('profile', this)">
                <i class="fa-solid fa-user-pen"></i> Profile Information
            </button>
            <button class="tab-btn" onclick="switchTab('password', this)">
                <i class="fa-solid fa-lock"></i> Change Password
            </button>
        </div>

        <!-- ═══════════════════════════════════════════════════
             TAB 1 — PROFILE
        ════════════════════════════════════════════════════ -->
        <div class="tab-panel active" id="tab-profile">

            <?php if ($success_profile): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($success_profile); ?>
            </div>
            <?php endif; ?>

            <?php if ($error_profile): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error_profile); ?>
            </div>
            <?php endif; ?>

            <!-- AVATAR ROW -->
            <div class="avatar-row">
                <img class="avatar-preview" id="avatarPreview"
                     src="https://ui-avatars.com/api/?name=<?php echo urlencode($admin['first_name'].' '.$admin['last_name']); ?>&background=7c3aed&color=fff&size=120"
                     alt="Avatar">
                <div class="avatar-info">
                    <div class="av-name" id="avatarName">
                        <?php echo htmlspecialchars($admin['first_name'].' '.$admin['last_name']); ?>
                    </div>
                    <div class="av-sub">Administrator &nbsp;·&nbsp; Avatar auto-generated from name</div>
                </div>
            </div>

            <div class="section-title">Personal Details</div>

            <form method="POST" action="">
                <div class="form-grid">

                    <div class="form-group">
                        <label for="first_name">First Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="first_name" name="first_name"
                               value="<?php echo htmlspecialchars($admin['first_name']); ?>"
                               placeholder="First name" required
                               oninput="updateAvatarPreview()">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="last_name" name="last_name"
                               value="<?php echo htmlspecialchars($admin['last_name']); ?>"
                               placeholder="Last name" required
                               oninput="updateAvatarPreview()">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span style="color:var(--danger)">*</span></label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($admin['email']); ?>"
                               placeholder="admin@example.com" required>
                    </div>


                    <div class="form-group full">
                        <label for="address">Address</label>
                        <textarea id="address" name="address"
                                  placeholder="Street, City, Province"><?php echo htmlspecialchars($admin['address'] ?? ''); ?></textarea>
                    </div>

                </div>

                <div class="form-actions">
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                    <a href="adminaccount.php" class="btn btn-secondary">
                      Cancel
                    </a>
                </div>
            </form>

        </div>
        <!-- END TAB 1 -->

        <!-- ═══════════════════════════════════════════════════
             TAB 2 — CHANGE PASSWORD
        ════════════════════════════════════════════════════ -->
        <div class="tab-panel" id="tab-password">

            <?php if ($success_password): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?php echo htmlspecialchars($success_password); ?>
            </div>
            <?php endif; ?>

            <?php if ($error_password): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo htmlspecialchars($error_password); ?>
            </div>
            <?php endif; ?>

            <div class="section-title">Update Password</div>

            <form method="POST" action="" onsubmit="return validatePassword()">
                <div class="form-grid">

                    <div class="form-group full">
                        <label for="current_password">Current Password <span style="color:var(--danger)">*</span></label>
                        <div class="pass-wrap">
                            <input type="password" id="current_password" name="current_password"
                                   placeholder="Enter your current password" required>
                            <button type="button" class="toggle-pw" onclick="togglePw('current_password', this)">
                             
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password <span style="color:var(--danger)">*</span></label>
                        <div class="pass-wrap">
                            <input type="password" id="new_password" name="new_password"
                                   placeholder="At least 8 characters" required
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="toggle-pw" onclick="togglePw('new_password', this)">
                               
                            </button>
                        </div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel">Enter a new password</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password <span style="color:var(--danger)">*</span></label>
                        <div class="pass-wrap">
                            <input type="password" id="confirm_password" name="confirm_password"
                                   placeholder="Re-enter new password" required
                                   oninput="checkMatch()">
                            <button type="button" class="toggle-pw" onclick="togglePw('confirm_password', this)">
                                
                            </button>
                        </div>
                        <div class="strength-label" id="matchLabel"></div>
                    </div>

                </div>

                <!-- REQUIREMENTS -->
                <div class="hint-list">
                    <p>Password Requirements</p>
                    <ul>
                        <li id="req-len"><i class="fa-solid fa-check"></i> At least 8 characters</li>
                        <li id="req-upper"><i class="fa-solid fa-check"></i> At least one uppercase letter</li>
                        <li id="req-num"><i class="fa-solid fa-check"></i> At least one number</li>
                        <li id="req-special"><i class="fa-solid fa-check"></i> At least one special character</li>
                    </ul>
                </div>

                <div class="form-actions">
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fa-solid fa-lock"></i> Update Password
                    </button>
                    <a href="adminaccount.php" class="btn btn-secondary">
                     Cancel
                    </a>
                </div>
            </form>

        </div>
        <!-- END TAB 2 -->

    </div>
    <!-- END MODAL INNER -->

</div>
</div>

<script>

// ── TAB SWITCHING ─────────────────────────────────────────────────────────────
function switchTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

// Auto-open password tab if there was a password error/success
<?php if ($error_password || $success_password): ?>
document.addEventListener('DOMContentLoaded', () => {
    switchTab('password', document.querySelectorAll('.tab-btn')[1]);
});
<?php endif; ?>

// ── AVATAR LIVE PREVIEW ───────────────────────────────────────────────────────
function updateAvatarPreview() {
    const fn   = document.getElementById('first_name').value.trim() || 'Admin';
    const ln   = document.getElementById('last_name').value.trim()  || '';
    const name = (fn + ' ' + ln).trim();
    document.getElementById('avatarPreview').src =
        'https://ui-avatars.com/api/?name=' + encodeURIComponent(name) + '&background=7c3aed&color=fff&size=120';
    document.getElementById('avatarName').textContent = name;
}

// ── TOGGLE PASSWORD VISIBILITY ────────────────────────────────────────────────
function togglePw(id, btn) {
    const inp = document.getElementById(id);
    const ico = btn.querySelector('i');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        ico.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// ── PASSWORD STRENGTH ─────────────────────────────────────────────────────────
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');

    const rules = {
        len:     val.length >= 8,
        upper:   /[A-Z]/.test(val),
        num:     /[0-9]/.test(val),
        special: /[^A-Za-z0-9]/.test(val)
    };

    // Update requirement bullets
    toggleReq('req-len',     rules.len);
    toggleReq('req-upper',   rules.upper);
    toggleReq('req-num',     rules.num);
    toggleReq('req-special', rules.special);

    const score = Object.values(rules).filter(Boolean).length;
    const map = [
        { w:'0%',   bg:'#e5e7eb', txt:'' },
        { w:'25%',  bg:'#ef4444', txt:'Weak' },
        { w:'50%',  bg:'#f97316', txt:'Fair' },
        { w:'75%',  bg:'#eab308', txt:'Good' },
        { w:'100%', bg:'#22c55e', txt:'Strong' },
    ];
    fill.style.width      = map[score].w;
    fill.style.background = map[score].bg;
    label.textContent     = map[score].txt ? 'Strength: ' + map[score].txt : 'Enter a new password';
    label.style.color     = map[score].bg;
}

function toggleReq(id, met) {
    const el = document.getElementById(id);
    if (met) {
        el.classList.add('met');
    } else {
        el.classList.remove('met');
    }
}

// ── PASSWORD MATCH ────────────────────────────────────────────────────────────
function checkMatch() {
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;
    const lbl = document.getElementById('matchLabel');
    if (!cp) { lbl.textContent = ''; return; }
    if (np === cp) {
        lbl.textContent = '✓ Passwords match';
        lbl.style.color = '#16a34a';
    } else {
        lbl.textContent = '✗ Passwords do not match';
        lbl.style.color = '#dc2626';
    }
}

// ── FORM VALIDATION ───────────────────────────────────────────────────────────
function validatePassword() {
    const np = document.getElementById('new_password').value;
    const cp = document.getElementById('confirm_password').value;
    if (np !== cp) {
        alert('New password and confirmation do not match.');
        return false;
    }
    if (np.length < 8) {
        alert('Password must be at least 8 characters.');
        return false;
    }
    return true;
}

// ── BACKDROP CLICK ────────────────────────────────────────────────────────────
function handleBackdropClick(e) {
    if (e.target === document.getElementById('overlay')) {
        history.back();
    }
}

</script>

</body>
</html>