<?php
session_start();
include '../koneksi.php';

$admin_id_login = $_SESSION['admin_id']; // id admin yang login

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Ambil data admin kecuali yang login
$query = "SELECT * FROM admin WHERE id != $admin_id_login LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Hitung total data kecuali yang login
$total_result = mysqli_query($conn, "SELECT COUNT(*) as total FROM admin WHERE id != $admin_id_login");
$total_row = mysqli_fetch_assoc($total_result);
$total_data = $total_row['total'];
$total_page = ceil($total_data / $limit);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      background: linear-gradient(to right, #83a4d4, #b6fbff);
      padding: 30px;
      font-family: Arial, sans-serif;
    }
    .table-container {
      background: #ffffff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    table thead {
      background: #007bff;
      color: white;
    }
    .pagination .page-item.active .page-link {
      background-color: #007bff;
      border-color: #007bff;
    }
   
    .sidebar {
      position: fixed;
      left: -250px;
      top: 0;
      width: 250px;
      height: 100%;
      background: #2c3e50;
      color: white;
      transition: left 0.3s ease;
      z-index: 1000;
    }

    .sidebar.active {
      left: 0;
    }

    .sidebar h2 {
      text-align: center;
      padding: 1rem;
      background: #1a252f;
      margin: 0;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
    }

    .sidebar ul li {
      padding: 15px 20px;
      border-bottom: 1px solid #34495e;
    }

    .sidebar ul li a {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      margin-right: 10px;
      width: 20px;
    }

    .sidebar ul li a i {
      margin-right: 10px;
      width: 20px;
    }
    
    .sidebar ul li:hover {
      background: #34495e;
      cursor: pointer;
    }

    .menu-toggle {
      position: fixed;
      top: 15px;
      left: 15px;
      font-size: 24px;
      color: #73945bff;
      cursor: pointer;
      z-index: 1100;
    }

    .content {
      padding: 2rem;
      margin-left: 0;
      transition: margin-left 0.3s ease;
    }

    .sidebar.active ~ .content {
      margin-left: 250px;
    }

    .card {
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 2px 10px 10px rgba(0, 0, 0, 0.1);
    }

    .si {
      text-align: right;
    }

  </style>
</head>
<body>

<div class="menu-toggle" onclick="toggleSidebar()">
  <i class="fas fa-bars"></i>
</div>

<div class="sidebar" id="sidebar">
  <h2>Menu</h2>
  <ul>
    <li><a href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
    <li><a href="t_admin.php"><i class="fas fa-user"></i> Admin</a></li>
    <li><a href="../tamu/tamu.php"><i class="fas fa-users"></i> Tamu</a></li>
    <li><a href="../tamu/kehadiran.php"><i class="fas fa-user-check"></i> Kehadiran</a></li>
    <li><a href="../laporan/laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
    <li><a href="index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>

<div class="content">
  <div class="container table-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Data Admin</h4>
      <a href="tambah.php" class="btn btn-success">+ Tambah Admin</a>
    </div>

    <table class="table table-striped table-hover text-center">
      <thead>
        <tr>
          <th>No</th>
          <th>Email</th>
          <th>Username</th>
          <th>Password</th>
          <th>Level</th>
        </tr>
      </thead>
      <tbody>

        <?php
      $no = $start + 1;
      while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
            <td>$no</td>
            <td>{$row['email']}</td>
            <td>{$row['username']}</td>
            <td>{$row['password']}</td>
            <td>
              <form action='aktivasi.php' method='POST' style='display:inline;'>
                <input type='hidden' name='id' value='{$row['id']}'>";
                
    $btnClass = $row['level'] === 'on' ? 'btn-success' : 'btn-danger';
$btnText  = $row['level'] === 'on' ? 'Aktif' : 'Nonaktif';


    echo       "<button type='submit' class='btn btn-sm $btnClass'>$btnText</button>
              </form>
            </td>
          </tr>";
    $no++;
}


      if (mysqli_num_rows($result) == 0) {
        echo "<tr><td colspan='4'>Tidak ada data admin.</td></tr>";
      }
      ?>
    </tbody>
  </table>

  <!-- Pagination -->
  <nav>
    <ul class="pagination justify-content-center">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page - 1 ?>">&laquo;</a>
        </li>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_page; $i++): ?>
        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <?php if ($page < $total_page): ?>
        <li class="page-item">
          <a class="page-link" href="?page=<?= $page + 1 ?>">&raquo;</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<script>

  function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
  setTimeout(() => {
    console.log("Menjalankan auto-delete admin level off...");

    fetch('delete.php')
      .then(response => response.text())
      .then(data => {
        console.log("Respon dari server:", data);
        // reload hanya jika ada respon "berhasil"
        if (data.includes("berhasil")) {
          location.reload();
        }
      })
      .catch(error => console.error("Gagal menjalankan auto-delete:", error));
  }, 10000); // 10 detik
</script>

</body>
</html>