<?php

include("connection.php");
session_start();


if (empty($_SESSION['email'])) {
  header('location:login.php');
  exit();
}

if (!isset($_GET['id'])) {
  die("Vehicle ID not provided");
}

$vehicle_id = $_GET['id'];


$stmt = $conn->prepare("SELECT id, owner_name, rc_no, vehicle_type, fuel_type, country, state, city, created_at FROM vehicle_details WHERE id = ?");
$stmt->bind_param("s", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  die("Vehicle not found");
}

$vehicle = $result->fetch_assoc();
$stmt->close();


$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$qrTargetUrl = $scheme . "://" . $host . "/vehicle/fetch_vehicle.php?id=" . urlencode($vehicle_id);
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Vehicle QR • <?= htmlspecialchars($vehicle['rc_no']) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer">
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <style>
    :root {
      --bg: #050b1a;
      --panel: 18, 22, 40;
      --brand-a: #6c5ce7;
      --brand-b: #00d2ff;
      --accent: #ffca28;
      --muted: #9fb0d6;
      --ok: #22c55e;
      --danger: #ef4444;
      --radius: 18px;
      --radius-lg: 24px;
    }

    * {
      box-sizing: border-box
    }

    html,
    body {
      height: auto;
      min-height: 100%;
    }

    body {
      margin: 0;
      font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial;
      color: #eaf2ff;
      background:
        radial-gradient(900px 700px at -10% -10%, rgba(108, 92, 231, .25), transparent 35%),
        radial-gradient(900px 700px at 110% 10%, rgba(0, 210, 255, .15), transparent 35%),
        linear-gradient(180deg, #0a1227, var(--bg));

      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      padding: 24px;
      overflow-y: auto;
    }

    .wrap {
      width: 100%;
      max-width: 980px;
      display: grid;
      grid-template-columns: 1fr;
      gap: 18px;
    }

    .toolbar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      background: rgba(255, 255, 255, .03);
      border: 1px solid rgba(255, 255, 255, .06);
      padding: 12px;
      border-radius: 16px;
      backdrop-filter: blur(8px);
    }

    .tool-left {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap
    }

    .badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      font-weight: 800;
      background: linear-gradient(135deg, var(--brand-a), var(--brand-b));
      padding: 8px 12px;
      border-radius: 999px;
      box-shadow: 0 10px 30px rgba(108, 92, 231, .18);
    }

    .controls {
      display: flex;
      gap: 8px;
      flex-wrap: wrap
    }

    .btn {
      appearance: none;
      border: 0;
      cursor: pointer;
      font-weight: 800;
      color: #fff;
      padding: 10px 12px;
      border-radius: 12px;
      background: linear-gradient(135deg, var(--brand-a), var(--brand-b));
      box-shadow: 0 10px 24px rgba(108, 92, 231, .16);
      display: inline-flex;
      gap: 8px;
      align-items: center;
      transition: transform .12s ease;
    }

    .btn:hover {
      transform: translateY(-2px)
    }

    .btn.ghost {
      background: transparent;
      color: #d6e6ff;
      border: 1px solid rgba(255, 255, 255, .08);
      box-shadow: none;
    }

    select,
    .input {
      background: rgba(255, 255, 255, .03);
      color: #eaf2ff;
      border: 1px solid rgba(255, 255, 255, .08);
      padding: 10px 12px;
      border-radius: 12px;
      font-weight: 700;
    }

    .poster {
      display: grid;
      grid-template-columns: 1.1fr .9fr;
      gap: 18px;
      background: rgba(19, 24, 45, .6);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: var(--radius-lg);
      box-shadow: 0 30px 90px rgba(2, 6, 23, .6);
      overflow: hidden;
    }

    @media (max-width:860px) {
      .poster {
        grid-template-columns: 1fr;
      }
    }

    .left {
      padding: 28px;
      background:
        radial-gradient(600px 400px at -20% -10%, rgba(108, 92, 231, .20), transparent 30%),
        radial-gradient(600px 400px at 120% 10%, rgba(0, 210, 255, .12), transparent 30%),
        linear-gradient(180deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .02));
    }

    .right {
      padding: 28px;
      background: linear-gradient(145deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .01));
      border-left: 1px solid rgba(255, 255, 255, .06);
    }

    .brandRow {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-bottom: 18px;
    }

    .logo {
      width: 56px;
      height: 56px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 900;
      color: #fff;
      background: linear-gradient(135deg, var(--brand-a), var(--brand-b));
      box-shadow: 0 12px 30px rgba(108, 92, 231, .18);
    }

    .title h2 {
      margin: 0;
      font-size: 1.1rem;
      letter-spacing: .2px
    }

    .title small {
      color: var(--muted)
    }

    .callout {
      margin-top: 8px;
      padding: 10px 12px;
      border-radius: 12px;
      border: 1px dashed rgba(255, 255, 255, .12);
      color: #dbeafe;
      display: flex;
      gap: 10px;
      align-items: center;
      font-weight: 700;
    }

    .qrBox {
      margin-top: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(145deg, rgba(255, 255, 255, .02), rgba(255, 255, 255, .01));
      border-radius: 20px;
      padding: 6px;
      min-height: 320px;
    }

    #qr {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .facts {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
      margin-top: 8px;
    }

    .fact {
      background: rgba(255, 255, 255, .03);
      border: 1px solid rgba(255, 255, 255, .06);
      border-radius: 14px;
      padding: 12px;
    }

    .fact label {
      display: block;
      color: var(--muted);
      font-size: .82rem;
      margin-bottom: 6px
    }

    .fact div {
      font-weight: 800;
    }

    .footNote {
      margin-top: 16px;
      color: #cde4ff;
      opacity: .9;
      font-size: .9rem;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .divider {
      height: 1px;
      background: rgba(255, 255, 255, .06);
      margin: 16px 0;
    }


    .qr-card-premium {
      position: relative;
      width: 100%;
      max-width: 380px;

      padding: 28px 20px;
      border-radius: 24px;
      background: linear-gradient(145deg, #0f2027, #203a43, #2c5364);
      color: #fff;
      text-align: center;
      box-shadow: 0 18px 40px rgba(0, 0, 0, 0.4);
      overflow: hidden;
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

    .qr-box #qr canvas {
      width: 230px !important;
      height: 230px !important;
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



    @media (max-width: 600px) {
      body {
        padding: 12px;
      }

      .toolbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }

      .controls {
        width: 100%;
        justify-content: flex-start;
        flex-wrap: wrap;
        gap: 10px;
      }

      .poster {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .left,
      .right {
        padding: 20px;
      }

      .brandRow {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .logo {
        width: 48px;
        height: 48px;
        font-size: 0.9rem;
      }

      .qr-card-premium {
        max-width: 100%;
        padding: 20px 14px;
      }

      .qr-box #qr canvas {
        width: 180px !important;
        height: 180px !important;
      }

      .facts {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      .fact {
        padding: 10px;
      }

      .footNote {
        font-size: 0.8rem;
        flex-wrap: wrap;
      }
    }
  </style>
</head>

<body>
  <div class="wrap">

    <div class="toolbar">
      <div class="tool-left">
        <span class="badge"><i class="fa-solid fa-qrcode"></i> Vehicle QR Poster</span>
        <span style="color:var(--muted); font-weight:700;">RC: <?= htmlspecialchars($vehicle['rc_no']) ?></span>
      </div>
      <div class="controls">
        <label>
          <select id="size">
            <option value="200">QR 200px</option>
            <option value="256" selected>QR 256px</option>
            <option value="320">QR 320px</option>
            <option value="400">QR 400px</option>
          </select>
        </label>
        <button class="btn" id="btnDownload"><i class="fa-regular fa-image"></i> Download PNG</button>
        <button class="btn ghost" id="btnPrint"><i class="fa-solid fa-print"></i> Print</button>
        <button class="btn ghost" id="btnCopy"><i class="fa-regular fa-copy"></i> Copy Link</button>
      </div>
    </div>


    <section class="poster" id="poster">
      <div class="left">
        <div class="brandRow">
          <div class="logo">V.C</div>
          <div class="title">
            <h2>Vehicall • Quick Contact</h2>
            <small>Wrong Parking • Emergency • Owner Contact</small>
          </div>
          <div style="margin-left:auto; text-align:right">
            <div style="font-size:.85rem; color:var(--muted)">Scan to view details</div>
            <div style="font-weight:900; color:var(--accent)"><?= htmlspecialchars($vehicle['rc_no']) ?></div>
          </div>
        </div>

        <div class="callout"><i class="fa-solid fa-triangle-exclamation"></i>
          If you’re blocking or in an emergency, scan this code to reach the owner securely.
        </div>

        <div class="qrBox">
          <div id="qrCard" class="qr-card-premium">


            <div class="qr-ribbon">
              <?= strtoupper(htmlspecialchars($vehicle['vehicle_type'])) ?> PASS
            </div>


            <div class="qr-logo">🚗 Vehicall</div>


            <h3 class="qr-title">🚨 Emergency / Wrong Parking</h3>


            <div class="qr-box">
              <div id="qr" aria-label="QR code"></div>
            </div>


            <div class="qr-footer">
              <p>Scan this QR Code to know driver details</p>
              <small>Vehicall Company</small>
            </div>
          </div>
        </div>

        <div class="footNote">
          <i class="fa-regular fa-hand-pointer"></i>
          Tip: Print and place this near the windshield. Tap “Download” to save a high-res PNG poster.
        </div>
      </div>

      <div class="right">
        <div class="facts">
          <div class="fact">
            <label>Owner</label>
            <div><?= htmlspecialchars($vehicle['owner_name']) ?></div>
          </div>
          <div class="fact">
            <label>RC Number</label>
            <div><?= htmlspecialchars($vehicle['rc_no']) ?></div>
          </div>
          <div class="fact">
            <label>Vehicle</label>
            <div><?= htmlspecialchars($vehicle['vehicle_type']) ?></div>
          </div>
          <div class="fact">
            <label>Fuel</label>
            <div><?= htmlspecialchars($vehicle['fuel_type']) ?></div>
          </div>
          <div class="fact">
            <label>City</label>
            <div><?= htmlspecialchars($vehicle['city']) ?></div>
          </div>
          <div class="fact">
            <label>State</label>
            <div><?= htmlspecialchars($vehicle['state']) ?></div>
          </div>
          <div class="fact">
            <label>Country</label>
            <div><?= htmlspecialchars($vehicle['country']) ?></div>
          </div>
          <div class="fact">
            <label>Registered</label>
            <div><?= htmlspecialchars(date('d M Y', strtotime($vehicle['created_at'] ?? 'now'))) ?></div>
          </div>
        </div>

        <div class="divider"></div>
        <div class="footNote">
          <i class="fa-solid fa-shield-halved"></i>
          Your data is accessed only after OTP verification by the viewer.
        </div>
      </div>
    </section>
    <p class="footer" style="text-align:center;color:#98a2bd;padding-top:20px">© <?php echo date('Y'); ?> Vehicall •
      Developed By Syed Raza Hussain</p>
  </div>

  <script>

    const targetUrl = <?= json_encode($qrTargetUrl) ?>;


    let qrobj = null;
    function renderQR(size = 256) {
      const qrWrap = document.getElementById('qr');
      qrWrap.innerHTML = '';
      qrobj = new QRCode(qrWrap, {
        text: targetUrl,
        width: size,
        height: size,
        correctLevel: QRCode.CorrectLevel.H,
        colorDark: "#000000",
        colorLight: "#ffffff"
      });
    }


    renderQR(parseInt(document.getElementById('size').value, 10));


    document.getElementById('size').addEventListener('change', (e) => {
      const size = parseInt(e.target.value, 10);
      renderQR(size);
    });


    document.getElementById('btnCopy').addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(targetUrl);
        flashToast('Link copied to clipboard');
      } catch {
        flashToast('Unable to copy link', true);
      }
    });


    document.getElementById('btnPrint').addEventListener('click', () => {
      window.print();
    });


    document.getElementById('btnDownload').addEventListener('click', () => {
      const poster = document.getElementById('qrCard');
      html2canvas(poster, { backgroundColor: null, scale: 3, useCORS: true }).then(canvas => {
        const link = document.createElement('a');
        link.download = 'vehicle-qr-<?= htmlspecialchars($vehicle['rc_no']) ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
      }).catch(() => flashToast('Failed to export image', true));
    });


    function flashToast(msg, isError = false) {
      const t = document.createElement('div');
      t.style.position = 'fixed'; t.style.bottom = '20px'; t.style.right = '20px';
      t.style.padding = '10px 12px'; t.style.borderRadius = '12px';
      t.style.backdropFilter = 'blur(8px)';
      t.style.border = '1px solid rgba(255,255,255,.10)';
      t.style.background = isError ? 'rgba(239,68,68,.14)' : 'rgba(34,197,94,.12)';
      t.style.color = '#fff'; t.style.fontWeight = '800';
      t.style.boxShadow = '0 10px 30px rgba(2,6,23,.6)';
      t.textContent = msg;
      document.body.appendChild(t);
      setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; }, 2200);
      setTimeout(() => t.remove(), 2600);
    }
  </script>
</body>

</html>