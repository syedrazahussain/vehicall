<?php
include("connection.php");
session_start();

$userprofile = $_SESSION['email'] ?? null;
if (!$userprofile) {
  header('location:signup.php');
  exit();
}

if (!isset($_GET['id'])) {
  die("No vehicle ID provided.");
}

$vehicle_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM vehicle_details WHERE id = ?");
$stmt->bind_param("s", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Vehicle not found.");
}

$vehicle = $result->fetch_assoc();
$stmt->close();

$_SESSION['current_vehicle_id'] = $vehicle_id;

$isVerified = isset($_SESSION['verified'][$vehicle_id])
  && $_SESSION['verified'][$vehicle_id]['status'] === true
  && time() < $_SESSION['verified'][$vehicle_id]['until'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Vehicle Details • Vehicle Access</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous">
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
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    body {
      background: radial-gradient(1200px 800px at 10% -10%, rgba(108, 92, 231, .25), transparent 40%),
        radial-gradient(1000px 700px at 110% 10%, rgba(0, 210, 255, .18), transparent 40%),
        linear-gradient(180deg, #0a0f1e, #070a14 60%);
      color: #e7ecff;
      min-height: 100vh;
      padding-bottom: 48px;
    }


    .topbar {
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: saturate(140%) blur(10px);
      background: rgba(255, 255, 255, .03);
      border-bottom: 1px solid rgba(255, 255, 255, .06);
    }

    .topbar-inner {
      max-width: 1100px;
      margin: 0 auto;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 16px;
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
      background: rgba(var(--panel), .65);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow);
      backdrop-filter: blur(8px);
      padding: 20px;
      margin-bottom: 20px
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


    .vehicle-card {
      border-radius: var(--radius-lg);
      background: linear-gradient(145deg, #0f2027, #203a43, #2c5364);
      padding: 28px 22px;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.4);
      color: #fff;
      max-width: 700px;
      margin: auto;
    }

    .vehicle-card h2 {
      margin-bottom: 18px;
      font-size: 1.5rem;
      text-align: center;
      color: #ffca28;
      text-shadow: 0 0 8px rgba(255, 202, 40, 0.8);
    }

    .vehicle-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 12px
    }

    .vehicle-table td {
      padding: 12px 15px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 12px
    }

    .vehicle-table td:first-child {
      font-weight: 700;
      color: #cfd7ff;
      width: 40%
    }

    .blur {
      filter: blur(6px);
      cursor: pointer;
      color: #888;
      transition: all 0.3s
    }

    .blur:hover {
      filter: blur(3px);
    }


    #otpModal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      justify-content: center;
      align-items: center;
      padding: 20px;
      z-index: 999;
    }

    #otpModal .modal-content {
      background: #1a1a1a;
      border-radius: 18px;
      padding: 28px 22px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
      text-align: center;
    }

    #otpModal input,
    #otpModal select {
      width: 100%;
      padding: 12px 10px;
      margin-bottom: 12px;
      border-radius: 12px;
      border: none;
      background: #222;
      color: #fff;
      font-weight: 600;
    }

    #otpModal select option {
      color: #fff;
      background: #222;
    }

    #otpModal button {
      padding: 12px 16px;
      margin-top: 8px;
      border-radius: 12px;
      border: none;
      background: linear-gradient(135deg, var(--brand), var(--accent));
      color: #fff;
      font-weight: 700;
      cursor: pointer
    }


    @media(max-width:520px) {
      .hero {
        flex-direction: column;
        align-items: flex-start
      }

      .vehicle-card {
        padding: 20px
      }

      .vehicle-table td {
        padding: 10px
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


  <main class="shell">
    <section class="panel hero">
      <div class="hero-left">
        <h1>Vehicle/Driver Details</h1>
        <p>Click on blurred info to verify access and reveal mobile/emergency contact numbers.</p>
      </div>
      <div style="margin-left:auto">
        <div
          style="background:rgba(255,255,255,.03);padding:10px 14px;border-radius:12px;font-weight:700;color:#cfe9ff">
          Session: <?php echo htmlspecialchars($userprofile); ?>
        </div>
      </div>
    </section>

    <div class="vehicle-card">
      <h2>Driver / Vehicle Info</h2>
      <table class="vehicle-table">
        <tr>
          <td>Owner Name</td>
          <td><?= htmlspecialchars($vehicle['owner_name']); ?></td>
        </tr>
        <tr>
          <td>RC No</td>
          <td><?= htmlspecialchars($vehicle['rc_no']); ?></td>
        </tr>
        <tr>
          <td>Vehicle Type</td>
          <td><?= htmlspecialchars($vehicle['vehicle_type']); ?></td>
        </tr>
        <tr>
          <td>Fuel Type</td>
          <td><?= htmlspecialchars($vehicle['fuel_type']); ?></td>
        </tr>
        <tr>
          <td>Mobile</td>
          <td id="mobile" class="<?= $isVerified ? '' : 'blur' ?>">
            <?= $isVerified ? htmlspecialchars($vehicle['mobile']) : '**********' ?>
          </td>
        </tr>
        <tr>
          <td>Emergency Contact</td>
          <td id="emergency" class="<?= $isVerified ? '' : 'blur' ?>">
            <?= $isVerified ? htmlspecialchars($vehicle['emergency_contact']) : '**********' ?>
          </td>
        </tr>
        <tr>
          <td>Country</td>
          <td><?= htmlspecialchars($vehicle['country']); ?></td>
        </tr>
        <tr>
          <td>State</td>
          <td><?= htmlspecialchars($vehicle['state']); ?></td>
        </tr>
        <tr>
          <td>City</td>
          <td><?= htmlspecialchars($vehicle['city']); ?></td>
        </tr>
        <tr>
          <td>Registered At</td>
          <td><?= htmlspecialchars($vehicle['created_at']); ?></td>
        </tr>
      </table>
    </div>


    <?php if (!$isVerified): ?>

      <div id="otpModal">
        <div class="modal-content">
          <h3>Verify Access</h3>
          <input type="text" id="user_mobile" placeholder="Enter your mobile">
          <button onclick="sendOTP()">Send OTP</button>
          <input type="text" id="otp_code" placeholder="Enter OTP">
          <select id="reason">
            <option value="Wrong Parking">🚗 Wrong Parking</option>
            <option value="Emergency">🚨 Emergency</option>
            <option value="Other">📞 Other Reason</option>
          </select>

          <button onclick="verifyOTP()">Verify</button>
        </div>
      </div>
      <p class="footer" style="text-align:center;color:#98a2bd;padding-top:20px">© <?php echo date('Y'); ?> Vehicall •
        Developed By Syed Raza Hussain</p>
    <?php endif; ?>

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
    document.querySelectorAll(".blur").forEach(el => {
      el.addEventListener("click", () => {
        document.getElementById("otpModal").style.display = "flex";
      });
    });

    function sendOTP() {
      let mobile = document.getElementById("user_mobile").value;
      fetch("send_otp.php", { method: "POST", body: new URLSearchParams({ user_mobile: mobile }), headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
        .then(res => res.json()).then(data => alert(data.message));
    }

    function verifyOTP() {
      let verifyBtn = event.target;
      verifyBtn.disabled = true;
      verifyBtn.innerText = "Verifying...";
      if (!navigator.geolocation) { alert("Geolocation is not supported"); verifyBtn.disabled = false; verifyBtn.innerText = "Verify"; return; }
      navigator.geolocation.getCurrentPosition(function (pos) {
        let lat = pos.coords.latitude, lon = pos.coords.longitude;
        let otp = document.getElementById("otp_code").value;
        let reason = document.getElementById("reason").value;
        fetch("verify_otp.php", { method: "POST", body: new URLSearchParams({ otp: otp, reason: reason, lat: lat, lon: lon }), headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
          .then(res => res.json()).then(data => {
            if (data.status === "success") {
              alert("✅ OTP verified, numbers unlocked!");
              document.getElementById("otpModal").style.display = "none";
              fetch("get_contacts.php").then(r => r.json()).then(info => {
                if (info.status === "success") {
                  document.getElementById("mobile").innerText = info.mobile;
                  document.getElementById("emergency").innerText = info.emergency;
                  document.getElementById("mobile").classList.remove("blur");
                  document.getElementById("emergency").classList.remove("blur");
                }
              });
            } else {
              alert(data.message);
              verifyBtn.disabled = false;
              verifyBtn.innerText = "Verify";
            }
          }).catch(() => { alert("Something went wrong."); verifyBtn.disabled = false; verifyBtn.innerText = "Verify"; });
      }, function () { alert("Location permission required."); verifyBtn.disabled = false; verifyBtn.innerText = "Verify"; });
    }
  </script>
</body>

</html>