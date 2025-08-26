<?php

session_start();
include("connection.php"); 


$toast = null;           
$toastMessage = '';


if (isset($_POST['submit'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $toast = 'error';
        $toastMessage = 'Please enter a valid email address.';
    } elseif ($password === '' || $confirm_password === '') {
        $toast = 'error';
        $toastMessage = 'Please provide and confirm your new password.';
    } elseif ($password !== $confirm_password) {
        $toast = 'mismatch';
        $toastMessage = 'Passwords do not match.';
    } else {
        
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        
        $stmt = $conn->prepare("UPDATE signup SET password = ? WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $hashed, $email);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $toast = 'success';
                $toastMessage = 'Password changed successfully. Redirecting to login...';
                
                
                header("Refresh:2; url=login.php");
            } else {
                
                $toast = 'error';
                $toastMessage = 'No account found with that email address.';
            }
            $stmt->close();
        } else {
            $toast = 'error';
            $toastMessage = 'Server error. Please try again later.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Reset Password • Vehicall</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
 <link rel="stylesheet" href="forgot-password.css">
</head>
<body>
<div class="bg" aria-hidden="true">
  <div class="bg-gradients"></div>
  <div class="orb one"></div>
  <div class="orb two"></div>
</div>

  <main class="card" role="main" aria-labelledby="forgotTitle">
    <div class="brand">
      <div class="logo">V.C</div>
      <div>
        <h1 id="forgotTitle">Reset Password</h1>
        <p>Enter the email linked to your account and choose a new password.</p>
      </div>
    </div>

    <form method="POST" novalidate>
      <label class="field" for="email">
        <i class="fa-regular fa-envelope"></i>
        <input id="email" name="email" type="email" placeholder="Email" required autocomplete="email" />
      </label>

      <label class="field" for="password">
        <i class="fa-solid fa-lock"></i>
        <input id="password" name="password" type="password" placeholder="New password" required autocomplete="new-password" />
        <i class="fa-solid fa-eye eye" id="togglePwd" title="Show / Hide password" aria-hidden="true"></i>
      </label>

      <label class="field" for="confirm_password">
        <i class="fa-solid fa-lock"></i>
        <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" required autocomplete="new-password" />
        <i class="fa-solid fa-eye eye" id="toggleConfirm" title="Show / Hide password" aria-hidden="true"></i>
      </label>

      <div class="actions">
        <button type="submit" name="submit" class="btn">Reset Password</button>
        <button type="button" class="btn ghost" onclick="location.href='login.php'">Back to Login</button>
      </div>

      <div class="meta">
        <small>Tip: Use a strong password with letters, numbers & symbols.</small>
      </div>
    </form>
  </main>

  
  <div id="toast" class="toast" role="status" aria-live="polite" style="display:none;">
    <div id="toast-icon" class="icon"><i class="fa-regular fa-circle-check"></i></div>
    <div id="toast-text">Message</div>
    <div class="close-toast" id="closeToast" title="Close"><i class="fa-solid fa-xmark"></i></div>
  </div>

  <script>
    
    function toggleInput(id, iconId) {
      const input = document.getElementById(id);
      const icon = document.getElementById(iconId);
      if (!input || !icon) return;
      icon.addEventListener('click', () => {
        input.type = (input.type === 'password') ? 'text' : 'password';
        icon.classList.toggle('fa-eye-slash');
      });
    }
    toggleInput('password', 'togglePwd');
    toggleInput('confirm_password', 'toggleConfirm');

    
    function showToast(type, msg) {
      const toast = document.getElementById('toast');
      const text = document.getElementById('toast-text');
      const icon = document.getElementById('toast-icon');
      toast.className = 'toast ' + (type === 'success' ? 'success' : 'error');
      text.textContent = msg;
      
      icon.innerHTML = type === 'success' ? '<i class="fa-regular fa-circle-check"></i>' : '<i class="fa-solid fa-triangle-exclamation"></i>';
      toast.style.display = 'flex';
      setTimeout(() => toast.classList.add('show'), 20);
      
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => { toast.style.display = 'none'; }, 280);
      }, 3000);
    }
    document.getElementById('closeToast').addEventListener('click', () => {
      const t = document.getElementById('toast');
      t.classList.remove('show'); setTimeout(()=> t.style.display='none', 200);
    });

    
    <?php if ($toast): ?>
      window.addEventListener('DOMContentLoaded', function () {
        showToast(<?php echo json_encode($toast === 'success' ? 'success' : ($toast === 'mismatch' ? 'error' : 'error')); ?>, <?php echo json_encode($toastMessage); ?>);
      });
    <?php endif; ?>
  </script>
</body>
</html>
