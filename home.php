<?php
include("connection.php");
session_start();
$userprofile = $_SESSION['email'] ?? null;
$username_session = $_SESSION['username'] ?? null;
if (!$userprofile) {
  header('location:signup.php');
  exit();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vehicall • Dashboard</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="home.css">

</head>

<body>
  <header class="topbar">
    <div class="topbar-inner">
      <div class="brand">
        <i class="fa-solid fa-car-rear"></i><span>VEHICALL</span>
      </div>

      
      <button class="hamburger" id="hamburger">
        <i class="fa-solid fa-bars"></i>
      </button>

      <nav class="nav" id="nav">
        <a href="home.php"><i class="fa-solid fa-gauge"></i>&nbsp;Dashboard</a>
        <a href="register-form.php"><i class="fa-regular fa-id-card"></i>&nbsp;Registration</a>
        <a href="scanner.php"><i class="fa-solid fa-qrcode"></i>&nbsp;Fetch</a>
        <a href="#" id="career-link"><i class="fa-regular fa-lightbulb"></i>&nbsp;Career</a>
        <a class="logout" href="logout.php"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;Logout</a>
      </nav>
    </div>
  </header>


  <section class="hero" aria-label="Hero">
    <div class="hero-canvas"></div>
    <div class="hero-overlay"></div>
    <div class="hero-inner">
      <div class="hero-left">
        <h1>Welcome back — <strong><?php echo htmlspecialchars($username_session); ?></strong></h1>
        <p>Manage vehicle registrations, scan QR codes, regenerate codes and review access logs — all from one secure
          dashboard.</p>

      </div>

      <div class="panel card" style="padding:18px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <div style="font-weight:700">Session</div>
        </div>
        <div style="color:#c6ceea; font-size:14px">
          <p style="margin:6px 0"><strong><?php echo htmlspecialchars($userprofile); ?></strong></p>
         
        </div>
      </div>
    </div>
  </section>

  <main class="shell">
    <div class="panel" style="padding:12px;">
      <div class="grid-cards">

        <div class="card">
          <div class="icon-wrap"><i class="fa-solid fa-car-side"></i></div>
          <h3>Register Vehicle</h3>
          <p>Create a new vehicle profile, then generate a scannable QR code.</p>
          <a href="register-form.php">Open</a>
        </div>

        <div class="card">
          <div class="icon-wrap"><i class="fa-solid fa-qrcode"></i></div>
          <h3>Scan QR</h3>
          <p>Open the scanner to fetch vehicle details instantly via QR.</p>
          <a href="scanner.php">Open</a>
        </div>

        <div class="card">
          <div class="icon-wrap"><i class="fa-solid fa-arrows-rotate"></i></div>
          <h3>Regenerate QR</h3>
          <p>Regenerate and replace an existing vehicle QR code securely.</p>
          <a href="regenerate_qr.php">Open</a>
        </div>

        <div class="card">
          <div class="icon-wrap"><i class="fa-solid fa-clipboard-list"></i></div>
          <h3>Access Log</h3>
          <p>Review recent scans and access events for auditing.</p>
          <a href="access_log.php">Open</a>
        </div>

        <div class="card">
          <div class="icon-wrap"><i class="fa-solid fa-right-from-bracket"></i></div>
          <h3>Logout</h3>
          <p>End your secure session.</p>
          <a href="logout.php">Sign out</a>
        </div>

      </div>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> Vehicall • Developed By Syed Raza Hussain</p>
  </main>
  <script>
    const hamburger1 = document.getElementById('hamburger');
    const nav1 = document.getElementById('nav');

    hamburger1.addEventListener('click', () => {
      nav1.classList.toggle('active');
    });

    
    document.querySelectorAll('.nav a').forEach(link => {
      link.addEventListener('click', () => nav1.classList.remove('active'));
    });
  </script>

  <script>
    document.getElementById('career-link').addEventListener('click', (e) => { e.preventDefault(); alert("We're crafting opportunities — check back soon!") });
  </script>
</body>

</html>