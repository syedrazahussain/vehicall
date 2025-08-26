<?php
date_default_timezone_set("Asia/Kolkata");
include("connection.php");
session_start();

$userprofile = $_SESSION['email'];
if (!$userprofile) {
  header('location:signup.php');
  exit();
}

if (isset($_POST['submit'])) {
  $name = $_POST['name'];
  $rc_no = $_POST['rc_no'];
  $type = $_POST['type'];
  $fuel = $_POST['fuel'];
  $mobile = $_POST['mobile'];
  $emergency = $_POST['emergency'];
  $country = $_POST['country'];
  $state = $_POST['state'];
  $city = $_POST['city'];

  $email = $_SESSION['email'];
 

   $created_at = date("Y-m-d H:i:s");  

$sql = "INSERT INTO vehicle_details (id, owner_name, email, rc_no, vehicle_type, fuel_type, mobile, emergency_contact, country, state, city, created_at) 
        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssss", $name, $email, $rc_no, $type, $fuel, $mobile, $emergency, $country, $state, $city, $created_at);
  if ($stmt->execute()) {
    $result = $conn->query("SELECT id FROM vehicle_details ORDER BY created_at DESC LIMIT 1");
    $row = $result->fetch_assoc();
    $last_id = $row['id'];

    $qrData = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'] . '/vehicle/fetch_vehicle.php?id=' . $last_id;
    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrData);

     
       


    $conn->query("UPDATE vehicle_details SET qr_code='$qrImageUrl' WHERE id='$last_id'");

    header("Location: qr-code.php?id=" . $last_id);
    exit();
  } else {
    echo "Error inserting: " . $stmt->error;
  }

  $stmt->close();
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Vehicall • Registration</title>


  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="register-form.css">

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>


  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>


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
    <div class="hero-canvas" role="img" aria-label="Sports car silhouette"></div>
    <div class="hero-overlay"></div>
    <div class="hero-inner">
      <div>
        <h1>Vehicle Registration</h1>
        <p>Fast, secure and verified access information. Fill the form to generate a scannable QR profile for your
          vehicle.</p>
      </div>
      <div class="panel card">
        <div class="card-header">
          <div class="card-title">Session</div>
          <i class="fa-regular fa-circle-check" title="Verified session"></i>
        </div>
        <div class="card-body">
          <ul style="margin:0; padding-left:18px; color:#c6ceea; line-height:1.5">
            <li><strong><?php echo htmlentities($userprofile ?? ''); ?></strong></li>

          </ul>
        </div>
      </div>
    </div>
  </section>


  <main class="shell">
    <div class="panel card" style="overflow:hidden">
      <div class="card-header">
        <div class="card-title"><i class="fa-regular fa-id-badge"></i>&nbsp;Fill the form</div>
        <span style="color:#a7b0cd; font-size:13px">All fields are required</span>
      </div>
      <div class="card-body">
        <form method="POST" action="register-form.php" id="reg-form" novalidate>
          <div class="grid">
            <div class="col-6">
              <label for="name">Vehicle Owner Name</label>
              <div class="control">
                <input id="name" name="name" class="input" type="text" placeholder="e.g., PRIYA SHARMA"
                  autocomplete="name" required style="text-transform:uppercase" />
                <p class="hint">Enter full legal name as on RC.</p>
              </div>
            </div>

            <div class="col-6">
              <label for="rc_no">Registration Certificate No</label>
              <div class="control">
                <input id="rc_no" name="rc_no" class="input" type="text" placeholder="e.g., KA01AB1234" minlength="6"
                  maxlength="20" required />
              </div>
            </div>

            <div class="col-6">
              <label for="type">Class of Vehicle</label>
              <div class="control">
                <select id="type" name="type" required>
                  <option value="" disabled selected>Select vehicle type</option>
                  <option value="car">Car</option>
                  <option value="auto">Auto</option>
                  <option value="bike">Bike</option>
                </select>
                <i class="fa-solid fa-chevron-down select-caret" aria-hidden="true"></i>
              </div>
            </div>

            <div class="col-6">
              <label for="fuel">Fuel Used</label>
              <div class="control">
                <select id="fuel" name="fuel" required>
                  <option value="" disabled selected>Select fuel</option>
                  <option value="petrol">Petrol</option>
                  <option value="diesel">Diesel</option>
                </select>
                <i class="fa-solid fa-chevron-down select-caret" aria-hidden="true"></i>
              </div>
            </div>

            <div class="col-6">
              <label for="mobile">Owner Mobile No</label>
              <div class="control">
                <input id="mobile" name="mobile" class="input" type="tel" inputmode="numeric" pattern="[0-9]{10}"
                  placeholder="10‑digit number" required />
                <p class="hint">Digits only (e.g., 9876543210).</p>
              </div>
            </div>

            <div class="col-6">
              <label for="emergency">Emergency Contact</label>
              <div class="control">
                <input id="emergency" name="emergency" class="input" type="tel" inputmode="numeric" pattern="[0-9]{10}"
                  placeholder="Alternate 10‑digit number" required />
              </div>
            </div>

            <div class="col-6">
              <label for="country">Country</label>
              <div class="control">
                <select id="country" name="country" required>
                  <option value="" disabled selected>Select Country</option>
                </select>
                <i class="fa-solid fa-chevron-down select-caret" aria-hidden="true"></i>
              </div>
            </div>

            <div class="col-6">
              <label for="state">State</label>
              <div class="control">
                <select id="state" name="state" required>
                  <option value="" disabled selected>Select State</option>
                </select>
                <i class="fa-solid fa-chevron-down select-caret" aria-hidden="true"></i>
              </div>
            </div>

            <div class="col-12">
              <label for="city">City</label>
              <div class="control">
                <select id="city" name="city" required>
                  <option value="" disabled selected>Select City</option>
                </select>
                <i class="fa-solid fa-chevron-down select-caret" aria-hidden="true"></i>
              </div>
            </div>
          </div>

          <div class="actions" style="margin-top:4px">
            <button type="reset" class="btn secondary"><i class="fa-solid fa-rotate"></i>&nbsp;Reset</button>
            <button type="submit" name="submit" class="btn"><i
                class="fa-solid fa-circle-check"></i>&nbsp;Submit</button>
            <button type="button" class="btn destructive" id="cancel-btn" style="margin-left:auto"><i
                class="fa-regular fa-circle-xmark"></i>&nbsp;Cancel</button>
          </div>
        </form>
      </div>
    </div>

    <p class="footer">© <?php echo date('Y'); ?> Vehicall • Developed By Syed Raza Hussain</p>
  </main>


  <div class="toast" id="toast" role="status" aria-live="polite">
    <i class="fa-regular fa-circle-check icon"></i>
    <div>
      <strong>Registration submitted successfully</strong>
      <div style="color:#0b3b2a; font-size:13px; margin-top:2px">Generating your vehicle QR…</div>
    </div>
    <button class="close" id="toast-close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <script src="register-form.js"></script>
</body>

</html>