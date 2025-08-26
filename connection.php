<?php

$servername = "localhost";
$username="root";
$password ="";
$dbname="vehicle";

$conn=mysqli_connect($servername,$username,$password,$dbname);
$conn->query("SET time_zone = '+05:30'");


if(!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
