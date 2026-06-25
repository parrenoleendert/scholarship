<?php
session_start();
require_once("dbconfig.php");

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['id'];
$success = "";
$errors  = [];

// Fetch current student data
$stmt = $con->prepare("SELECT first_name, last_name, email, course, year_section, phone, address, username FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result  = $stmt->get_result();
$student = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── PROFILE UPDATE ──────────────────────────────────
    if (isset($_POST['update_profile'])) {

        $first_name   = trim($_POST['first_name']);
        $last_name    = trim($_POST['last_name']);
        $email        = trim($_POST['email']);
        $course       = trim($_POST['course']);
        $year_section = trim($_POST['year_section']);
        $phone        = trim($_POST['phone']);
        $address      = trim($_POST['address']);

        if (empty($first_name) || empty($last_name) || empty($email)) {
            $errors[] = "First name, last name, and email are required.";
        } else {
            $stmt = $con->prepare("UPDATE students SET first_name=?, last_name=?, email=?, course=?, year_section=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param("sssssssi", $first_name, $last_name, $email, $course, $year_section, $phone, $address, $id);
            if ($stmt->execute()) {
                $success = "Profile updated successfully.";
                // Refresh data
                $student['first_name']   = $first_name;
                $student['last_name']    = $last_name;
                $student['email']        = $email;
                $student['course']       = $course;
                $student['year_section'] = $year_section;
                $student['phone']        = $phone;
                $student['address']      = $address;
            } else {
                $errors[] = "Failed to update profile. Please try again.";
            }
        }
    }

    // ── USERNAME UPDATE ──────────────────────────────────
    if (isset($_POST['update_username'])) {

        $new_username = trim($_POST['new_username']);

        if (empty($new_username)) {
            $errors[] = "Username cannot be empty.";
        } else {
            // Check if username is already taken
            $check = $con->prepare("SELECT id FROM students WHERE username = ? AND id != ?");
            $check->bind_param("si", $new_username, $id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errors[] = "Username is already taken. Please choose another.";
            } else {
                $stmt = $con->prepare("UPDATE students SET username=? WHERE id=?");
                $stmt->bind_param("si", $new_username, $id);
                if ($stmt->execute()) {
                    $success = "Username updated successfully.";
                    $student['username'] = $new_username;
                } else {
                    $errors[] = "Failed to update username.";
                }
            }
        }
    }

    // ── PASSWORD UPDATE ──────────────────────────────────
    if (isset($_POST['update_password'])) {

        $current_pw  = $_POST['current_password'];
        $new_pw      = $_POST['new_password'];
        $confirm_pw  = $_POST['confirm_password'];

        // Fetch current hashed password
        $stmt = $con->prepare("SELECT password FROM students WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!password_verify($current_pw, $row['password'])) {
            $errors[] = "Current password is incorrect.";
        } elseif (strlen($new_pw) < 8 || strlen($new_pw) > 20) {
            $errors[] = "New password must be 8–20 characters.";
        } elseif ($new_pw !== $confirm_pw) {
            $errors[] = "New password and confirmation do not match.";
        } else {
            $hashed = password_hash($new_pw, PASSWORD_DEFAULT);
            $stmt   = $con->prepare("UPDATE students SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $id);
            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $errors[] = "Failed to change password.";
            }
        }
    }
}
?>
<?php require_once("headers.php"); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
*, *::before, *::after {
    margin: 0; padding: 0; box-sizing: border-box;
}

:root {
    --primary:   #2563eb;
    --primary-h: #1d4ed8;
    --bg:        #f4f7fb;
    --card:      #ffffff;
    --text:      #1f2937;
    --muted:     #6b7280;
    --border:    #e5e7eb;
    --red:       #dc2626;
    --red-bg:    #fee2e2;
    --green:     #166534;
    --green-bg:  #dcfce7;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: var(--bg);
    min-height: 100vh;
}

/* ── OVERLAY ─────────────────────────────── */
.overlay-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15,23,42,0.55);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    animation: fadeIn .2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px) scale(.98); }
    to   { opacity: 1; transform: translateY(0)    scale(1);   }
}

.overlay-modal {
    position: relative;
    background: var(--card);
    border-radius: 22px;
    width: 100%;
    max-width: 780px;
    max-height: 92vh;
    overflow-y: auto;
    box-shadow: 0 25px 60px rgba(0,0,0,.2);
    animation: slideUp .3s ease;
}

/* ── CLOSE ──────────────────────────────── */
.close-btn {
    position: sticky;
    top: 0;
    z-index: 10;
    display: flex;
    justify-content: flex-end;
    padding: 16px 20px 0;
    background: var(--card);
}

.close-btn a {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: #f1f5f9;
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--muted);
    text-decoration: none;
    font-size: 16px;
    transition: .2s;
}

.close-btn a:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: var(--red);
}

/* ── MODAL INNER ────────────────────────── */
.modal-inner {
    padding: 10px 32px 34px;
}

.page-title {
    font-size: 22px;
    font-weight: 700;
    color: var(--text);
}

.page-sub {
    font-size: 13px;
    color: var(--muted);
    margin-top: 4px;
    margin-bottom: 24px;
}

/* ── TABS ───────────────────────────────── */
.tabs {
    display: flex;
    gap: 6px;
    border-bottom: 2px solid var(--border);
    margin-bottom: 26px;
}

.tab-btn {
    padding: 9px 18px;
    border: none;
    background: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    border-radius: 6px 6px 0 0;
    transition: .2s;
    display: flex;
    align-items: center;
    gap: 7px;
}

.tab-btn:hover { color: var(--primary); background: #eff6ff; }

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
    background: #eff6ff;
}

/* ── TAB PANELS ─────────────────────────── */
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── ALERT ──────────────────────────────── */
.alert {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 11px 15px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    margin-bottom: 20px;
}

.alert-success {
    background: var(--green-bg);
    color: var(--green);
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: var(--red-bg);
    color: var(--red);
    border: 1px solid #fca5a5;
}

/* ── FORM GRID ──────────────────────────── */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full { grid-column: 1 / -1; }

.form-group label {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: .04em;
}

.input-wrap { position: relative; }

.input-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
    font-size: 15px;
    pointer-events: none;
}

.input-wrap input,
.input-wrap textarea {
    width: 100%;
    padding: 10px 12px 10px 36px;
    border: 1.5px solid var(--border);
    border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    color: var(--text);
    background: #fafbff;
    outline: none;
    transition: .2s;
}

.input-wrap textarea {
    height: 70px;
    resize: none;
    padding-top: 10px;
}

.input-wrap input:focus,
.input-wrap textarea:focus {
    border-color: var(--primary);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}


/* Password strength bar */
.strength-bar {
    height: 4px;
    border-radius: 4px;
    background: var(--border);
    margin-top: 6px;
    overflow: hidden;
}

.strength-fill {
    height: 100%;
    border-radius: 4px;
    width: 0%;
    transition: width .3s, background .3s;
}

.strength-label {
    font-size: 11px;
    color: var(--muted);
    margin-top: 4px;
}

/* ── SECTION DIVIDER ────────────────────── */
.section-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: var(--muted);
    margin: 22px 0 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-label::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
}

/* ── SUBMIT BTN ─────────────────────────── */
.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 22px;
    padding: 11px 24px;
    background: var(--primary);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s;
    box-shadow: 0 2px 8px rgba(37,99,235,.25);
}

.btn-save:hover {
    background: var(--primary-h);
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(37,99,235,.3);
}

.btn-save:active { transform: translateY(0); }

/* ── HINT TEXT ──────────────────────────── */
.hint {
    font-size: 11.5px;
    color: var(--muted);
    margin-top: 4px;
}

/* ── RESPONSIVE ─────────────────────────── */
@media (max-width: 600px) {
    .form-grid { grid-template-columns: 1fr; }
    .modal-inner { padding: 10px 18px 28px; }
    .tabs { overflow-x: auto; }
}
</style>
</head>

<body>

<div class="overlay-backdrop" id="overlay" onclick="handleBackdropClick(event)">
<div class="overlay-modal" id="modal">

    <!-- CLOSE -->
    <div class="close-btn">
        <a href="account.php" title="Close">
            <i class="fa-solid fa-xmark"></i>
        </a>
    </div>

    <div class="modal-inner">

        <div class="page-title">Edit Profile</div>
        <div class="page-sub">Update your personal information, username, or password.</div>

        <!-- ALERTS -->
        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <div><?php foreach($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        </div>
        <?php endif; ?>

        <!-- TABS -->
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('profile', this)">
                <i class="fa-solid fa-user"></i> Profile
            </button>
            <button class="tab-btn" onclick="switchTab('username', this)">
                <i class="fa-solid fa-at"></i> Username
            </button>
            <button class="tab-btn" onclick="switchTab('password', this)">
                <i class="fa-solid fa-lock"></i> Password
            </button>
        </div>

        <!-- ════════════════════════════════
             TAB 1 — PROFILE INFO
        ════════════════════════════════ -->
        <div class="tab-panel active" id="tab-profile">
            <form method="POST">

                <div class="form-grid">

                    <div class="form-group">
                        <label>First Name</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="first_name"
                                   value="<?php echo htmlspecialchars($student['first_name']); ?>"
                                   placeholder="First Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user"></i>
                            <input type="text" name="last_name"
                                   value="<?php echo htmlspecialchars($student['last_name']); ?>"
                                   placeholder="Last Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope"></i>
                            <input type="email" name="email"
                                   value="<?php echo htmlspecialchars($student['email']); ?>"
                                   placeholder="Email" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Course</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <input type="text" name="course"
                                   value="<?php echo htmlspecialchars($student['course']); ?>"
                                   placeholder="Course">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Year / Section</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-layer-group"></i>
                            <input type="text" name="year_section"
                                   value="<?php echo htmlspecialchars($student['year_section']); ?>"
                                   placeholder="e.g. 2 - A">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Phone Number</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" name="phone"
                                   value="<?php echo htmlspecialchars($student['phone']); ?>"
                                   placeholder="Phone Number">
                        </div>
                    </div>

                    <div class="form-group full">
                        <label>Address</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-location-dot"></i>
                            <textarea name="address"
                                      placeholder="Address"><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>
                    </div>

                </div>

                <button type="submit" name="update_profile" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>

            </form>
        </div>

        <!-- ════════════════════════════════
             TAB 2 — USERNAME
        ════════════════════════════════ -->
        <div class="tab-panel" id="tab-username">
            <form method="POST">

                <div class="section-label">Current Username</div>

                <div class="form-group" style="max-width:400px;">
                    <label>Current Username</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-at"></i>
                        <input type="text" value="<?php echo htmlspecialchars($student['username']); ?>"
                               disabled style="background:#f1f5f9; color:var(--muted);">
                    </div>
                </div>

                <div class="section-label">New Username</div>

                <div class="form-group" style="max-width:400px;">
                    <label>New Username</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-pen"></i>
                        <input type="text" name="new_username"
                               placeholder="Enter new username" required
                               autocomplete="off">
                    </div>
                    <span class="hint">Username must be unique. You'll use this to sign in.</span>
                </div>

                <button type="submit" name="update_username" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Update Username
                </button>

            </form>
        </div>

        <!-- ════════════════════════════════
             TAB 3 — PASSWORD
        ════════════════════════════════ -->
        <div class="tab-panel" id="tab-password">
            <form method="POST">

                <div style="max-width: 420px; display:flex; flex-direction:column; gap:16px;">

                    <div class="form-group">
                        <label>Current Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="current_password"
                                   id="cur-pw" placeholder="Enter current password" required>
                            <button type="button" class="eye-btn" onclick="togglePw('cur-pw','eye1')">
                                
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>New Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-key"></i>
                            <input type="password" name="new_password"
                                   id="new-pw" placeholder="Enter new password"
                                   minlength="8" maxlength="20" required
                                   oninput="checkStrength(this.value)">
                            <button type="button" class="eye-btn" onclick="togglePw('new-pw','eye2')">
                                
                            </button>
                        </div>
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <span class="strength-label" id="strength-label">Password must be 8–20 characters.</span>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-key"></i>
                            <input type="password" name="confirm_password"
                                   id="conf-pw" placeholder="Re-enter new password"
                                   required oninput="checkMatch()">
                            <button type="button" class="eye-btn" onclick="togglePw('conf-pw','eye3')">
                                
                            </button>
                        </div>
                        <span class="hint" id="match-hint"></span>
                    </div>

                </div>

                <button type="submit" name="update_password" class="btn-save">
                    <i class="fa-solid fa-shield-halved"></i> Change Password
                </button>

            </form>
        </div>

    </div><!-- /modal-inner -->
</div><!-- /overlay-modal -->
</div><!-- /overlay-backdrop -->

<script>
// ── TAB SWITCHING ──────────────────────────────────────
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ── BACKDROP CLICK ─────────────────────────────────────
function handleBackdropClick(e) {
    if (e.target === document.getElementById('overlay')) {
        window.location.href = 'account.php';
    }
}

// ── TOGGLE PASSWORD VISIBILITY ─────────────────────────
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

// ── PASSWORD STRENGTH ──────────────────────────────────
function checkStrength(val) {
    const fill  = document.getElementById('strength-fill');
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 8)                     score++;
    if (val.length >= 12)                    score++;
    if (/[A-Z]/.test(val))                   score++;
    if (/[0-9]/.test(val))                   score++;
    if (/[^A-Za-z0-9]/.test(val))            score++;

    

    const l = levels[Math.min(score - 1, 4)] || { w: '0%', bg: '#e5e7eb', text: 'Password must be 8–20 characters.' };
    fill.style.width      = l.w;
    fill.style.background = l.bg;
    label.textContent     = l.text;
    label.style.color     = l.bg;
}

// ── PASSWORD MATCH HINT ────────────────────────────────
function checkMatch() {
    const nw   = document.getElementById('new-pw').value;
    const conf = document.getElementById('conf-pw').value;
    const hint = document.getElementById('match-hint');
    if (conf === '')           { hint.textContent = '';               hint.style.color = ''; return; }
    if (nw === conf)           { hint.textContent = '✓ Passwords match';    hint.style.color = '#16a34a'; }
    else                       { hint.textContent = '✗ Passwords do not match'; hint.style.color = '#dc2626'; }
}

// ── AUTO-OPEN TAB IF ERROR/SUCCESS ────────────────────
<?php if (!empty($errors) || $success): ?>
<?php if (isset($_POST['update_username'])): ?>
    switchTab('username', document.querySelectorAll('.tab-btn')[1]);
<?php elseif (isset($_POST['update_password'])): ?>
    switchTab('password', document.querySelectorAll('.tab-btn')[2]);
<?php endif; ?>
<?php endif; ?>
</script>

</body>
</html>