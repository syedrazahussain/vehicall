<?php

include("connection.php");
session_start();

$userprofile = $_SESSION['email'] ?? null;
if (!$userprofile) {
  header('location:signup.php');
  exit();
}

$qrUrl = "";
$message = "";


if (isset($_POST['submit'])) {
  $name = trim($_POST['name']);
  $rc_no = trim($_POST['rc_no']);
  $type = trim($_POST['type']);

  $sql = "SELECT id, owner_name, rc_no, vehicle_type, fuel_type, mobile, emergency_contact, country, state, city, created_at, qr_code 
            FROM vehicle_details 
            WHERE owner_name=? AND rc_no=? AND vehicle_type=? LIMIT 1";

  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("sss", $name, $rc_no, $type);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
      $message = "❌ No record found. Please check the details and try again.";
    } else {
      $data = $result->fetch_assoc();
      $vehicle_id = $data['id'];


      if (!empty($data['qr_code'])) {
        $qrUrl = $data['qr_code'];
      } else {
        $qrContent = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'] . '/vehicle/fetch_vehicle.php?id=' . urlencode($data['id']);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($qrContent);

        if ($stmt2 = $conn->prepare("UPDATE vehicle_details SET qr_code=? WHERE id=?")) {
          $stmt2->bind_param("ss", $qrUrl, $data['id']);
          $stmt2->execute();
          $stmt2->close();
        }
      }


      $_SESSION['qrUrl'] = $qrUrl;
      header("Location: regenerate_qr.php");
      exit();
    }
    $stmt->close();
  } else {
    $message = "❌ Database error. Please try again later.";
  }
}


if (!empty($_SESSION['qrUrl'])) {
  $qrUrl = $_SESSION['qrUrl'];
  unset($_SESSION['qrUrl']);
}
$type = isset($_POST['type']) ? $_POST['type'] : "";

?>

<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Regenerate QR • Vehicall</title>


  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />


  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

  <style>
    :root {
      --panel: 24, 28, 48;
      --muted: #8a93a6;
      --brand: #6c5ce7;
      --accent: #00d2ff;
      --radius: 16px;
      --radius-lg: 22px;
      --shadow: 0 20px 60px rgba(0, 0, 0, .35), inset 0 1px 0 rgba(255, 255, 255, .04);
      --glass: rgba(var(--panel), .65);
      --glass-weak: rgba(255, 255, 255, .04);
    }

    * {
      box-sizing: border-box
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
      color: #e7ecff;
      background: radial-gradient(1200px 800px at 10% -10%, rgba(108, 92, 231, .25), transparent 40%),
        radial-gradient(1000px 700px at 110% 10%, rgba(0, 210, 255, .18), transparent 40%),
        linear-gradient(180deg, #0a0f1e, #070a14 60%);
      -webkit-font-smoothing: antialiased;
      min-height: 100vh;
      padding-bottom: 48px;
    }


    .topbar {
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: saturate(140%) blur(10px);
      background: var(--glass-bg);
      border-bottom: 1px solid rgba(255, 255, 255, .06)
    }

    .topbar-inner {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      font-weight: 800
    }

    .brand i {
      font-size: 22px;
      color: var(--accent);
      filter: drop-shadow(0 0 8px rgba(0, 210, 255, .45))
    }

    .brand span {
      font-size: 18px
    }

    .nav {
      display: flex;
      gap: 10px;
      align-items: center
    }

    .nav a {
      text-decoration: none;
      color: #cfd7ff;
      font-weight: 600;
      padding: 10px 14px;
      border-radius: 12px;
      transition: all .15s
    }

    .nav a:hover {
      background: rgba(255, 255, 255, .04)
    }

    .nav .logout {
      color: #ffb4b4;
    }


    .shell {
      max-width: 1100px;
      margin: 20px auto;
      padding: 0 16px
    }

    .panel {
      background: var(--glass);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      backdrop-filter: blur(8px)
    }

    .hero {
      padding: 28px 22px;
      border-radius: var(--radius-lg);
      overflow: hidden;
      display: flex;
      align-items: center;
      gap: 18px
    }

    .hero-left h1 {
      font-size: clamp(20px, 3vw, 28px);
      margin: 0
    }

    .hero-left p {
      color: var(--muted);
      margin: 6px 0 0;
      font-size: 0.95rem
    }


    .content {
      display: grid;
      grid-template-columns: 1fr 420px;
      gap: 20px;
      padding: 20px
    }

    @media (max-width:980px) {
      .content {
        grid-template-columns: 1fr
      }

      .hero {
        flex-direction: column;
        align-items: flex-start
      }
    }


    .form-card {
      padding: 18px
    }

    .form-card h2 {
      margin: 0 0 12px;
      font-size: 1.15rem
    }

    .field {
      margin-bottom: 12px
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #e6eaff
    }

    input[type="text"],
    select {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, .08);
      background: linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .01));
      color: #fff;
      font-weight: 600;
    }

    select option {
      color: #fff;
      background-color: #2c2c2cff;
    }

    input[type="text"]::placeholder {
      color: #9aa6c8
    }

    .form-hint {
      font-size: 0.85rem;
      color: var(--muted);
      margin-top: 6px
    }

    .controls {
      display: flex;
      gap: 10px;
      margin-top: 12px;
      flex-wrap: wrap
    }

    .btn {
      padding: 10px 14px;
      border-radius: 12px;
      font-weight: 800;
      border: none;
      cursor: pointer;
      background: linear-gradient(135deg, var(--brand), var(--accent));
      color: #fff;
      box-shadow: 0 10px 24px rgba(0, 210, 255, .12)
    }

    .btn.secondary {
      background: transparent;
      border: 1px solid rgba(255, 255, 255, .06);
      color: #cfe9ff
    }

    .message {
      margin-top: 10px;
      color: #ffb4b4;
      font-weight: 700
    }


    .qr-panel {
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      align-items: center
    }

    .qr-card {
      width: 100%;
      background: linear-gradient(180deg, #fff, #f8fafc);
      color: #111827;
      border-radius: 14px;
      padding: 18px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 12px;
      box-shadow: 0 10px 30px rgba(2, 6, 23, .35)
    }

    .qr-card h3 {
      margin: 0;
      color: #111827
    }

    .qr-image {
      background: #fff;
      border-radius: 12px;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center
    }

    .qr-image img {
      max-width: 100%;
      height: auto;
      display: block;
      border-radius: 8px
    }

    .qr-meta {
      color: #475569;
      font-size: 0.95rem;
      text-align: center
    }

    .qr-actions {
      display: flex;
      gap: 10px;
      flex-wrap: wrap
    }

    .action-btn {
      padding: 10px 12px;
      border-radius: 10px;
      border: none;
      cursor: pointer;
      font-weight: 700
    }

    .action-download {
      background: linear-gradient(135deg, #22c55e, #16a34a);
      color: #fff
    }

    .action-print {
      background: linear-gradient(135deg, #4f46e5, #06b6d4);
      color: #fff
    }

    .action-close {
      background: transparent;
      border: 1px solid rgba(15, 23, 42, .08);
      color: #0f172a
    }

    .qr-card-premium {
      position: relative;
      width: 100%;
      max-width: 380px;
      margin: 30px auto;
      padding: 28px 20px;
      border-radius: 24px;
      background: linear-gradient(145deg, #0f2027, #203a43, #2c5364);
      color: #fff;
      text-align: center;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.4);
      overflow: hidden;
      cursor: pointer;
      transition: transform .3s ease, box-shadow .3s ease;
    }

    .qr-card-premium:hover {
      transform: translateY(-6px);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }


    .qr-card-premium::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image: url("https://cdn-icons-png.flaticon.com/512/743/743988.png");
      background-size: 60px;
      background-repeat: repeat;
      opacity: 0.05;
      filter: grayscale(100%);
      z-index: 0;
    }

    .qr-card-premium>* {
      position: relative;
      z-index: 1;
    }


    .qr-ribbon {
      position: absolute;
      top: 18px;
      left: -22px;
      background: #e53935;
      color: #fff;
      padding: 8px 28px;
      font-size: 0.95rem;
      font-weight: bold;
      transform: rotate(-10deg);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
      border-radius: 6px;
    }


    .qr-logo {
      font-size: 1.1rem;
      font-weight: bold;
      opacity: 0.9;
      margin-bottom: 6px;
      letter-spacing: 2px;
    }


    .qr-title {
      font-size: 1.2rem;
      margin: 10px 0 18px;
      font-weight: 700;
      text-transform: uppercase;
      color: #ffca28;
      text-shadow: 0 0 8px rgba(255, 202, 40, 0.8);
    }


    .qr-box {
      background: #fff;
      padding: 14px;
      border-radius: 16px;
      display: inline-block;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
    }

    .qr-box img {
      width: 230px;
      height: 230px;
      object-fit: contain;
    }


    .qr-footer p {
      font-size: 1rem;
      font-weight: 600;
      margin: 14px 0 6px;
    }

    .qr-footer small {
      color: #ddd;
      font-size: 0.8rem;
    }



    @media (max-width:520px) {
      .content {
        padding: 16px
      }

      .qr-card {
        padding: 12px
      }

      .hero {
        padding: 18px
      }

      .form-card {
        padding: 12px
      }

      .btn {
        padding: 10px
      }
    }


    @media (max-width:768px) {
      .qr-card-premium {
        max-width: 100%;
        margin: 20px auto;
        padding: 20px 16px;
        border-radius: 18px;
      }

      .qr-card-premium .qr-title {
        font-size: 1rem;
      }

      .qr-box img {
        width: 180px;
        height: 180px;
      }

      .qr-footer p {
        font-size: 0.9rem;
      }

      .qr-footer small {
        font-size: 0.7rem;
      }

      .hero-left h1 {
        font-size: 1.2rem;
      }

      .hero-left p {
        font-size: 0.85rem;
      }
    }

    @media (max-width:480px) {



      .qr-box img {
        width: 150px;
        height: 150px;
      }

      .qr-title {
        font-size: 0.95rem;
      }
    }

    .hamburger {
      display: none;
      background: none;
      border: none;
      color: #cfd7ff;
      font-size: 22px;
      cursor: pointer;
    }


    @media (max-width: 640px) {
      .hamburger {
        display: block;
      }

      .topbar-inner {
        display: flex;
        flex-direction: row;
      }

      .panel.hero {
        margin-bottom: 40px;
      }

      .nav {
        display: none;

        flex-direction: column;
        background: rgba(var(--panel), 0.95);
        position: absolute;
        top: 100%;
        right: 0;
        left: 0;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, .08);
      }

      .nav a {
        display: block;
        text-align: center;
        padding: 10px;
        font-size: 0.9rem;
      }

      .nav.active {
        display: flex;

      }
    }
  </style>
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


  <main class="shell" role="main">
    <section class="panel hero" aria-labelledby="hero-title">
      <div class="hero-left">
        <h1 id="hero-title">Regenerate / Fetch QR</h1>
        <p>Search by owner, RC number and vehicle type. If a QR exists we'll show it — otherwise we'll regenerate and
          store a fresh QR for the vehicle.</p>
      </div>
      <div style="margin-left:auto" aria-hidden="true">

        <div
          style="background:rgba(255,255,255,.03);padding:10px 14px;border-radius:12px;font-weight:700;color:#cfe9ff">
          Session: <?php echo htmlspecialchars($userprofile); ?></div>
      </div>
    </section>

    <div class="panel content" aria-live="polite">

      <div class="form-card" role="region" aria-label="Regenerate QR form">
        <h2>Find Vehicle</h2>

        <form method="POST" id="searchForm" novalidate>
          <div class="field">
            <label for="name">Owner Name</label>
            <input id="name" name="name" type="text" placeholder="e.g. PRIYA SHARMA" required aria-required="true" />
            <div class="form-hint">Enter exact owner name as stored (case-insensitive recommended).</div>
          </div>

          <div class="field">
            <label for="rc_no">Registration Certificate No</label>
            <input id="rc_no" name="rc_no" type="text" placeholder="e.g. KA01AB1234" required aria-required="true" />
          </div>

          <div class="field">
            <label for="type">Vehicle Type</label>
            <select id="type" name="type" required aria-required="true">
              <option value="">Select type</option>
              <option value="car">Car</option>
              <option value="bike">Bike</option>
              <option value="auto">Auto</option>
            </select>
          </div>

          <div class="controls">
            <button type="submit" name="submit" class="btn">Search & Generate QR</button>
            <button type="button" class="btn secondary"
              onclick="document.getElementById('searchForm').reset();">Reset</button>
          </div>

          <?php if (!empty($message)): ?>
            <div class="message" role="status"><?php echo htmlspecialchars($message); ?></div>
          <?php endif; ?>
        </form>
      </div>


      <?php if (!empty($qrUrl)): ?>
        <div id="qrCard" class="qr-card-premium" onclick="downloadCard()">


          <?php if (!empty($type)): ?>
            <div class="qr-ribbon">
              <?php echo strtoupper($type); ?> PASS
            </div>
          <?php endif; ?>


          <div class="qr-logo">🚗 Vehicall</div>


          <h3 class="qr-title">🚨 Emergency / Wrong Parking</h3>


          <div class="qr-box">
            <img src="<?php echo htmlspecialchars($qrUrl); ?>" alt="Vehicle QR Code">
          </div>


          <div class="qr-footer">
            <p>Scan this QR Code to know driver details</p>
            <small>Vehicall Company</small>
          </div>
        </div>
      <?php endif; ?>



    </div>


    <p class="footer" style="text-align:center;color:#98a2bd;padding-top:20px">© <?php echo date('Y'); ?> Vehicall •
      Developed By Syed Raza Hussain</p>
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
    function downloadCard() {
      const node = document.getElementById("qrCard");
      if (!node) return alert("No QR card available.");

      html2canvas(node, { useCORS: true, scale: 2 }).then(canvas => {
        const link = document.createElement("a");
        link.download = "vehicle-qr-card.png";
        link.href = canvas.toDataURL("image/png");
        link.click();
      }).catch(err => {
        console.error("Error capturing QR card:", err);
        alert("Unable to download QR card.");
      });
    }




    (function () {
      const form = document.getElementById('searchForm');
      form?.addEventListener('submit', function (e) {
        const name = document.getElementById('name').value.trim();
        const rc = document.getElementById('rc_no').value.trim();
        const type = document.getElementById('type').value;
        if (!name || !rc || !type) {
          e.preventDefault();
          alert('Please complete all fields before searching.');
        }
      });
    })();
  </script>
</body>

</html>