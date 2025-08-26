<?php
ini_set('date.timezone', 'Asia/Kolkata');
date_default_timezone_set("Asia/Kolkata");
session_start();
include("connection.php");
require __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;


$otp_entered = $_POST['otp'] ?? '';
$reason = $_POST['reason'] ?? 'Other';
$latitude = $_POST['lat'] ?? null;
$longitude = $_POST['lon'] ?? null;



function getUserIP()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    $ip = $_SERVER['REMOTE_ADDR'];
    return ($ip === "::1" || $ip === "127.0.0.1") ? "157.48.114.151" : $ip;
}


function getLocationFromLatLon($lat, $lon)
{
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=18&addressdetails=1&accept-language=en";

    $opts = ["http" => ["header" => "User-Agent: VehicleApp/1.0\r\n"]];
    $context = stream_context_create($opts);
    $json = @file_get_contents($url, false, $context);

    if ($json) {
        $data = json_decode($json, true);
        $a = $data['address'] ?? [];
        return [
            'village' => $a['city'] ?? $a['village'] ?? $a['town'] ?? "Unknown",
            'city' => $a['county'] ?? $a['city'] ?? $a['village'] ?? "Unknown",
            'state' => $a['state'] ?? "Unknown",
            'district' => $a['state_district'] ?? "Unknown",
            'country' => $a['country'] ?? "Unknown",
            'pincode' => $a['postcode'] ?? "Unknown"
        ];
    }
    return ['village' => 'Unknown', 'city' => 'Unknown', 'state' => 'Unknown', 'district' => 'Unknown', 'country' => 'Unknown', 'pincode' => 'Unknown'];
}




function getLocationFromIP($ip)
{
    $json = @file_get_contents("https://ipinfo.io/{$ip}/json");
    if ($json) {
        $d = json_decode($json, true);
        return [
            'village' => $d['city'] ?? 'Unknown',
            'city' => $d['city'] ?? 'Unknown',
            'state' => $d['region'] ?? 'Unknown',
            'district' => 'Unknown',
            'country' => $d['country'] ?? 'Unknown',
            'pincode' => '',
            'isp' => $d['org'] ?? 'Unknown'
        ];
    }
    return ['village' => 'Unknown', 'city' => 'Unknown', 'state' => 'Unknown', 'district' => 'Unknown', 'country' => 'Unknown', 'pincode' => '', 'isp' => 'Unknown'];
}


if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_mobile'])) {
    echo json_encode(["status" => "failed", "message" => "No OTP requested"]);
    exit;
}
if (time() > $_SESSION['otp_expiry']) {
    echo json_encode(["status" => "failed", "message" => "OTP expired"]);
    exit;
}


$status = "failed";
$vehicle_id = $_SESSION['current_vehicle_id'] ?? null;
$user_mobile = $_SESSION['otp_mobile'] ?? null;
$ip_address = getUserIP();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$rc_no = "Unknown";
$driver_mobile = null;
if ($vehicle_id) {
    $stmt = $conn->prepare("SELECT id, rc_no, mobile FROM vehicle_details WHERE id = ?");
    $stmt->bind_param("s", $vehicle_id);
    $stmt->execute();
    $stmt->bind_result($vehicle_details_id, $rc_no, $driver_mobile);
    $stmt->fetch();
    $stmt->close();
}
if (empty($vehicle_details_id))
    $vehicle_details_id = null;


if ($latitude && $longitude) {
    $location = getLocationFromLatLon($latitude, $longitude);
    $ipDetails = getLocationFromIP($ip_address);
    $isp = $ipDetails['isp'] ?? 'Unknown';
} else {
    $location = getLocationFromIP($ip_address);
    $isp = $location['isp'] ?? 'Unknown';
}

$village = $location['village'] ?? "Unknown";
$city = $location['city'] ?? "Unknown";
$state = $location['state'] ?? "Unknown";
$district = $location['district'] ?? "Unknown";
$country = $location['country'] ?? "Unknown";
$pincode = $location['pincode'] ?? "Unknown";


if ($_SESSION['otp'] == $otp_entered && $vehicle_id) {
    $status = "success";


    $_SESSION['verified'][$vehicle_id] = [
        'status' => true,
        'until' => time() + 300
    ];


    unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['otp_mobile']);


    if ($driver_mobile) {
        $sid = "";
        $token = "";
        $twilio = new Client($sid, $token);

        $alertMsg = "⚠️ Security Alert: Mobile {$user_mobile} accessed your vehicle RC {$rc_no} at {$city}, {$state}. Reason: {$reason}.";

        try {
            $twilio->messages->create(
                "+91" . $driver_mobile,
                ["from" => "", "body" => $alertMsg]
            );
        } catch (Exception $e) {
            error_log("Driver SMS failed: " . $e->getMessage());
        }
    }
}


$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? 'Unknown';
$user_email = $_SESSION['email'] ?? 'Unknown';

$dt = new DateTime("now", new DateTimeZone("Asia/Kolkata"));
$access_time = $dt->format("Y-m-d H:i:s");

$stmt = $conn->prepare("INSERT INTO access_logs 
    (id, vehicle_details_id, rc_no, user_mobile, user_name, user_email, reason, ip_address, user_agent, 
     village, city, district, state, country, pincode, isp, otp_status, latitude, longitude, access_time) 
VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "sssssssssssssssssss",
    $vehicle_details_id,
    $rc_no,
    $user_mobile,
    $user_name,
    $user_email,
    $reason,
    $ip_address,
    $user_agent,
    $village,
    $city,
    $district,
    $state,
    $country,
    $pincode,
    $isp,
    $status,
    $latitude,
    $longitude,
    $access_time
);

$stmt->execute();
$stmt->close();


echo ($status === "success")
    ? json_encode(["status" => "success"])
    : json_encode(["status" => "failed", "message" => "Invalid OTP"]);

?>
