<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;
include("connection.php");

$user_mobile = $_POST['user_mobile'] ?? '';


if (!preg_match('/^[0-9]{10,15}$/', $user_mobile)) {
    echo json_encode(["status" => "failed", "message" => "Invalid mobile number"]);
    exit;
}

$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_mobile'] = $user_mobile;
$_SESSION['otp_expiry'] = time() + 300;


$sid = "";
$token = "";
$twilio = new Client($sid, $token);

try {
    $twilio->messages->create(
        "+91" . $user_mobile,
        [
            "from" => "",
            "body" => "Your verification code is $otp"
        ]
    );
    echo json_encode(["status" => "success", "message" => "OTP sent"]);
} catch (Exception $e) {
    echo json_encode(["status" => "failed", "message" => $e->getMessage()]);
}

