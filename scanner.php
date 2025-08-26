<?php
include("connection.php");
session_start();
$userprofile = $_SESSION['email'] ?? null;
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
  <title>Vehicall • QR Scanner</title>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="scanner.css">

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

    <div class="hero-inner">
      <div>
        <h1>Scan a Vehicle QR</h1>
        <p>Use your camera to scan a code, or upload an image. We’ll parse it and take you to the vehicle profile.</p>
      </div>
      <div class="panel card">
        <div class="card-header">
          <div class="card-title">Session</div>
          <span class="badge"><i
              class="fa-regular fa-circle-user"></i><?php echo htmlspecialchars($userprofile); ?></span>
        </div>
        <div class="card" style="padding:16px; color:#c6ceea;">
          <div style="display:flex; gap:8px; align-items:center; margin-bottom:6px"><i
              class="fa-regular fa-circle-check" style="color:#22c55e"></i> Secure & private</div>
          <div style="display:flex; gap:8px; align-items:center;"><i class="fa-solid fa-bolt"></i> Instant decoding
          </div>
        </div>
      </div>
    </div>
  </section>


  <main class="shell">
    <div class="panel card">
      <div class="card-header">
        <div class="card-title"><i class="fa-solid fa-qrcode"></i>&nbsp;QR Scanner</div>
        <button class="btn danger" id="stopScan"><i class="fa-solid fa-circle-stop"></i>&nbsp;Stop</button>
      </div>
      <div class="card">
        <div class="grid">

          <section aria-label="Live camera" class="card" style="padding:0;">
            <div class="preview-wrap">
              <video id="preview" class="hidden" playsinline></video>

              <div class="preview-overlay"></div>
            </div>
            <div class="actions" style="padding:16px;">
              <button class="btn" id="startCam"><i class="fa-solid fa-camera-retro"></i>&nbsp;Start Camera</button>
              <button class="btn secondary" id="switchCam"><i class="fa-solid fa-repeat"></i>&nbsp;Switch
                Camera</button>
            </div>
          </section>


          <section aria-label="Upload image" class="card" style="padding:16px;">
            <input type="file" id="fileinput" accept="image/*" class="hidden">
            <div class="actions">
              <button class="btn" id="uploadBtn"><i class="fa-solid fa-upload"></i>&nbsp;Upload QR Image</button>
              <span class="badge"><i class="fa-regular fa-image"></i> JPG/PNG</span>
            </div>
            <img id="uploaded" alt="Uploaded preview" class="hidden" />
            <div id="resultCard" class="result hidden" style="margin-top:16px;">
              <p>Result:</p>
              <span id="qrResult"></span>
            </div>
          </section>
        </div>
      </div>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> Vehicall • Developed By Syed Raza Hussain</p>
  </main>


  <script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
  <script src="scanner.js"></script>

</body>

</html>