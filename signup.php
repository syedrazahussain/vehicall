<?php
include("connection.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup • Vehicall</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    crossorigin="anonymous">
  <link rel="stylesheet" href="signup.css">
</head>

<body>

  <div class="container">
    <form action="#" method="POST">
      <h1>Create Account</h1>
      <input type="text" name="username" placeholder="👤 Username" required>
      <input type="email" name="email" placeholder="📧 Email" required>
      <input type="text" name="mobile" placeholder="📱 Mobile Number" required>
      <input type="password" name="password" placeholder="🔒 Password" required>
      <input type="password" name="confirm_password" placeholder="🔒 Confirm Password" required>
      <button type="submit" name="submit" class="submit">Sign Up</button>
      <p>Already have an account? <a href="login.php">Login</a></p>
    </form>
  </div>

  
  <div id="toast-success" class="toast success" style="display:none;">
    <div class="icon"><i class="fa-regular fa-circle-check"></i></div>
    <div>Registration Completed Successfully</div>
    <div class="close" onclick="this.parentElement.style.display='none'"><i class="fa-solid fa-xmark"></i></div>
  </div>
  <div id="toast-error" class="toast error" style="display:none;">
    <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    <div>Registration Failed</div>
    <div class="close" onclick="this.parentElement.style.display='none'"><i class="fa-solid fa-xmark"></i></div>
  </div>
  <div id="toast-mismatch" class="toast error" style="display:none;">
    <div class="icon"><i class="fa-solid fa-xmark"></i></div>
    <div>Passwords do not match</div>
    <div class="close" onclick="this.parentElement.style.display='none'"><i class="fa-solid fa-xmark"></i></div>
  </div>
</body>

</html>

<?php
if (isset($_POST['submit'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];

  if ($password !== $confirm_password) {
    echo "<script>document.getElementById('toast-mismatch').style.display='flex';</script>";
  } else {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $query = "INSERT INTO signup (id, username, email, mobile, password) 
                  VALUES (UUID(), '$username', '$email', '$mobile', '$hashed_password')";
    $data = mysqli_query($conn, $query);

    if ($data) {
      echo "<script>document.getElementById('toast-success').style.display='flex';</script>";
      echo '<meta http-equiv="refresh" content="2; url=login.php" />';
    } else {
      echo "<script>document.getElementById('toast-error').style.display='flex';</script>";
    }
  }
}
?>