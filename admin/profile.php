<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

include '../koneksi.php'; // pastikan path ini benar

$admin_id = (int) $_SESSION['admin_id'];
$success = '';
$error = '';

// Ambil data awal
$stmt = $conn->prepare("SELECT email, username, password, level FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($email, $username, $password_db, $level);
$stmt->fetch();
$stmt->close();

// Proses update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'] ?? '';
    $new_username = $_POST['username'] ?? '';
    $new_password = $_POST['password'] ?? '';

    if ($new_email === '' || $new_username === '') {
        $error = "Email dan Username tidak boleh kosong untuk mengganti!";
    } else {
        // Cek apakah email sudah terdaftar
        $check_email_stmt = $conn->prepare("SELECT COUNT(*) FROM admin WHERE email = ? AND id != ?");
        $check_email_stmt->bind_param("si", $new_email, $admin_id);
        $check_email_stmt->execute();
        $check_email_stmt->bind_result($email_count);
        $check_email_stmt->fetch();
        $check_email_stmt->close();

        if ($email_count > 0) {
            $error = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            if ($new_password === '') {
                $up = $conn->prepare("UPDATE admin SET email = ?, username = ? WHERE id = ?");
                $up->bind_param("ssi", $new_email, $new_username, $admin_id);
            } else {
                $up = $conn->prepare("UPDATE admin SET email = ?, username = ?, password = ? WHERE id = ?");
                $up->bind_param("sssi", $new_email, $new_username, $new_password, $admin_id);
            }

            if ($up->execute()) {
                $success = "Profil berhasil diperbarui.";
                $_SESSION['username'] = $new_username;
                $email = $new_email;
                $username = $new_username;
                if ($new_password !== '') {
                    $password_db = $new_password;
                }
            } else {
                $error = "Gagal memperbarui profil. " . $conn->error;
            }

            $up->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(115deg, #a5dce4 0%, #3f2bd6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', sans-serif;
        }

        .profile-card {
            width: 100%;
            max-width: 820px;
            border: none;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .profile-cover {
            background: blue;
                        linear-gradient(135deg, #4fa4fc 0%, #3f2bd6 100%);
            height: 140px;
        }

        .avatar {
            display: grid;
            place-items: center;
            font-size: 42px;
            font-weight: 700;
            color: #3f2bd6;
            margin-top: -55px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            border: 6px solid #fff;
            width: 110px;
            height: 110px;
            background: white;
            border-radius: 50%;
        }

        .level-badge {
            font-size: 0.8rem;
        }

        .form-control:focus {
            box-shadow: 0 0 2rem rgba(79, 172, 254, .3);
            border-color: #4fa4fc;
        }

    </style>
</head>
<body>
<div class="card profile-card">
    <div class="profile-cover"></div>
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar" aria-label="Avatar">
                <?php
                $initial = strtoupper(substr($username, 0, 1));
                echo htmlspecialchars($initial);
                ?>
            </div>
            <div class="flex-grow-1">
                <h3 class="mb-0"><?php echo htmlspecialchars($username); ?>
                    <?php if (!empty($level)) : ?>
                        <span class="badge bg-<?php echo $level == 'admin' ? 'success' : 'secondary'; ?> level-badge">
                            Level: <?php echo htmlspecialchars($level); ?>
                        </span>
                    <?php endif; ?>
                </h3>
                <div class="text-muted">ID: <?php echo $admin_id; ?></div>
            </div>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary mt-3">Kembali ke Dashboard</a>
        <hr class="my-4">

        <?php if ($success): ?>
            <div class="alert alert-success mt-4" role="alert"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger mt-4" role="alert"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Password Baru (opsional)</label>
                    <div class="d-flex">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Kosongkan jika tidak ingin mengganti">
                        <button class="btn btn-outline-secondary" type="button" id="togglePass" aria-label="Tampilkan/Sembunyikan Password">👁</button>
                    </div>
                    <div class="form-text">Biarkan kosong bila tidak ingin mengubah password.</div>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>

<script>
const toggleBtn = document.getElementById('togglePass');
const passInput = document.getElementById('password');
toggleBtn.addEventListener('click', () => {
    passInput.type = passInput.type === 'password' ? 'text' : 'password';
    toggleBtn.textContent = passInput.type === 'password' ? '👁' : '🙈';
});
</script>
</body>
</html>