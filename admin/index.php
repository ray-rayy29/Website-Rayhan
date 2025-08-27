<?php
session_start();
include "../koneksi.php"; // Pastikan file koneksi ke database tersedia

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek user di database
    $query = "SELECT * FROM admin WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("location: dashboard.php");
        exit();
    } else {
        echo "<script>alert('Email atau password salah!'); window.location.href='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        height: 100vh;
        background: linear-gradient(to right, #46bec7ff, #957af3ff);
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-container {
        width: 400px;
        background: white;
        border-radius: 10px;
        padding: 40px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        color: #0072ff;;
    }

    .login-form input {
        border-radius: 5px;
    }

    .text-center {
        color: #0072ff;
    }

    .si {
        color: #0072ff;
    }

    .btn {
        background-color: #0072ff;
        color: white;
        width: 100%;
    }

    .btn:hover {
        background: white;
        border: 1px solid #0072ff;
    }
</style>
</head>
<body>
<div class="login-container">
    <h2 class="mb-4 text-center"><strong>Login</strong></h2>
    <p class="text-center">Lupa Password Klik <a href="lupa_pass.php" class="si">Disini</a></p>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" name="email" id="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" name="password" id="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>
</body>
</html>