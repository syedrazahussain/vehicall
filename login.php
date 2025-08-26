<?php

include("connection.php");
session_start();


$toast = null;
$toastMessage = '';


if (isset($_POST['submit'])) {
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';


  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    $toast = 'error';
    $toastMessage = 'Please enter a valid email and password.';
  } else {

    $stmt = $conn->prepare("SELECT id, username, email, password FROM signup WHERE email = ? LIMIT 1");
    if ($stmt) {
      $stmt->bind_param("s", $email);
      $stmt->execute();
      $res = $stmt->get_result();

      if ($res && $res->num_rows === 1) {
        $row = $res->fetch_assoc();

        if (password_verify($password, $row['password'])) {

          $_SESSION['user_id'] = $row['id'];
          $_SESSION['username'] = $row['username'];
          $_SESSION['email'] = $row['email'];

          $toast = 'success';
          $toastMessage = 'Login successful — redirecting...';


          header("Refresh:1; url=home.php");
        } else {
          $toast = 'error';
          $toastMessage = 'Incorrect email or password.';
        }
      } else {
        $toast = 'error';
        $toastMessage = 'Incorrect email or password.';
      }

      $stmt->close();
    } else {
      $toast = 'error';
      $toastMessage = 'Server error. Try again later.';
    }
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login • Vehicall</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="login.css">
</head>

<body>

  <div class="bg" aria-hidden="true">
    <div class="bg-gradients"></div>
    <div class="orb one"></div>
    <div class="orb two"></div>
  </div>

  <main class="card" role="main" aria-labelledby="loginTitle">
    <div class="brand" aria-hidden="false">
      <div class="logo">V.C</div>
      <div>
        <h1 id="loginTitle">Vehicall</h1>
        <p>Sign in to manage records securely</p>
      </div>
    </div>

    <form method="POST" novalidate>
      <label class="input" for="emailInput">
        <i class="fa-regular fa-envelope"></i>
        <input id="emailInput" name="email" type="email" autocomplete="email" placeholder="Email" required />
      </label>

      <label class="input" for="passwordInput">
        <i class="fa-solid fa-lock"></i>
        <input id="passwordInput" name="password" type="password" autocomplete="current-password" placeholder="Password"
          required />
      </label>

      <div class="actions">
        <button type="submit" name="submit" class="btn" id="loginBtn">
          Login
        </button>

        <button type="button" class="btn ghost small" onclick="location.href='signup.php'">
          Create account
        </button>
      </div>

      <div class="links">
        <a href="forgot-password.php">Forgot password?</a>
        <a href="privacy.php">Privacy</a>
      </div>

      <div class="meta">By logging in you agree to our terms and privacy policy.</div>
    </form>
  </main>


  <div id="toast" role="status" class="toast" aria-live="polite" style="display:none;">
    <div class="icon"><i class="fa-regular fa-circle-check"></i></div>
    <div id="toast-text">Login successful</div>
  </div>

  <script>

    (function () {
      const btn = document.getElementById('loginBtn');
      btn.addEventListener('click', function (e) {
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
        ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
        btn.appendChild(ripple);
        ripple.addEventListener('animationend', () => ripple.remove());
      });
    })();


    function showToast(type, message) {
      const toast = document.getElementById('toast');
      toast.className = 'toast ' + (type === 'success' ? 'success' : 'error');
      toast.querySelector('#toast-text').textContent = message;
      toast.style.display = 'flex';

      setTimeout(() => toast.classList.add('show'), 20);

      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.style.display = 'none', 300);
      }, 3000);
    }


    <?php if ($toast): ?>
      window.addEventListener('DOMContentLoaded', function () {
        showToast(<?php echo json_encode($toast); ?>, <?php echo json_encode($toastMessage); ?>);
        <?php if ($toast === 'success'): ?>

          setTimeout(function () { window.location.href = 'home.php'; }, 1000);
        <?php endif; ?>
      });
    <?php endif; ?>
  </script>
</body>

</html>