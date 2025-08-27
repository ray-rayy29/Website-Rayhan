<?php
//detail koneksi
$servername = "localhost";
$username = "root";
$password = "";
$db = "buku";

//membuat koneksi
$conn = mysqli_connect($servername, $username, $password, $db);

//cek koneksi
if (!$conn) {
    die("connection failed: " . mysqli_connect_error());
}
?>