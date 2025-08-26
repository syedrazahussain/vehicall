<?php
include("connection.php");
session_start();


$userprofile = $_SESSION['email'];
if (!$userprofile) {
  header('location:signup.php');
  exit();
}

if (!isset($_SESSION['current_vehicle_id'])) {
    echo json_encode(["status" => "error", "message" => "No vehicle selected"]);
    exit;
}

$vehicle_id = $_SESSION['current_vehicle_id'];


if (!isset($_SESSION['verified'][$vehicle_id]) || $_SESSION['verified'][$vehicle_id]['status'] !== true || time() > $_SESSION['verified'][$vehicle_id]['until']) {
    echo json_encode(["status" => "error", "message" => "Not verified"]);
    exit;
}

$stmt = $conn->prepare("SELECT mobile, emergency_contact FROM vehicle_details WHERE id = ?");
$stmt->bind_param("s", $vehicle_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode(["status" => "success", "mobile" => $data['mobile'], "emergency" => $data['emergency_contact']]);
