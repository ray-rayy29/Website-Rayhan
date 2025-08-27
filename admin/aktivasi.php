<?php
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];

    // Ambil level saat ini
    $result = mysqli_query($conn, "SELECT level FROM admin WHERE id = '$id'");
    $row = mysqli_fetch_assoc($result);
    $currentLevel = $row['level'];

    // Ubah status
    $newLevel = ($currentLevel === 'on') ? 'off' : 'on';

    // Update ke database
    mysqli_query($conn, "UPDATE admin SET level = '$newLevel' WHERE id = '$id'");
}

header("Location: t_admin.php"); // Redirect balik
exit;
?>