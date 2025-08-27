<?php
include '../koneksi.php';

$query = "DELETE FROM admin WHERE level = 'off'";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Admin level 'off' berhasil dihapus.";
} else {
    echo "Gagal menghapus: " . mysqli_error($conn);
}
?>