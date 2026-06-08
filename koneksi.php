<?php
$host = "sql203.infinityfree.com";
$user = "if0_42131761";
$pass = "PYmuq4KDUmei7";
$db = "if0_42131761_warungrizky";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
