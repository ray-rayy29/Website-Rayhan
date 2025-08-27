<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../admin/index.php");
    exit();
}

include '../koneksi.php';

// Ambil tahun filter
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date("Y");

// Pencarian nama
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data
$sql_count = "SELECT COUNT(*) as total FROM tamu WHERE YEAR(tanggal) = $tahun_filter";
if (!empty($search)) {
    $sql_count .= " AND nama LIKE '%$search%'";
}
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_data = $row_count['total'];
$total_pages = ceil($total_data / $limit);

// Ambil data
$sql_data = "SELECT * FROM tamu WHERE YEAR(tanggal) = $tahun_filter";
if (!empty($search)) {
    $sql_data .= " AND nama LIKE '%$search%'";
}
$sql_data .= " ORDER BY tanggal DESC LIMIT $limit OFFSET $offset";
$result_data = mysqli_query($conn, $sql_data);

// Download CSV
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="kehadiran_'.$tahun_filter.'.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['No', 'Nama', 'Tanggal', 'Waktu']);

    $no = 1;
    $sql_all = "SELECT * FROM tamu WHERE YEAR(tanggal) = $tahun_filter";
    if (!empty($search)) {
        $sql_all .= " AND nama LIKE '%$search%'";
    }
    $sql_all .= " ORDER BY tanggal DESC";
    $result_all = mysqli_query($conn, $sql_all);

    while ($row = mysqli_fetch_assoc($result_all)) {
        fputcsv($output, [$no++, $row['nama'], $row['tanggal'], $row['waktu']]);
    }
    fclose($output);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Kehadiran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      background: linear-gradient(to right, #83a4d4, #b6fbff);
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
      padding-top: 60px;
    }

    .sidebar.active ~ .content {
      margin-left: 250px;
    }

    .container {
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      padding: 25px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .header {
      text-align: center;
      margin-bottom: 30px;
      color: #2c3e50;
    }
    
    .header h2 {
      font-size: 28px;
      margin: 0;
      padding: 10px 0;
      text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
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
      <li><a href="../admin/dashboard.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="../admin/t_admin.php"><i class="fas fa-user"></i> Admin</a></li>
      <li><a href="tamu.php"><i class="fas fa-users"></i> Tamu</a></li>
      <li><a href="kehadiran.php"><i class="fas fa-user-check"></i> Kehadiran</a></li>
      <li><a href="../laporan/laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
      <li><a href="../admin/index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="container">
      <div class="header">
        <h2>Rekap Kehadiran Tamu <?= $tahun_filter ?></h2>
      </div>

      <!-- Filter tahun & pencarian -->
      <form method="get" class="row mb-3">
        <div class="col-md-3">
          <label for="tahun" class="form-label">Pilih Tahun:</label>
          <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
            <?php for ($tahun = 2000; $tahun <= date("Y"); $tahun++): ?>
            <option value="<?= $tahun ?>" <?= $tahun == $tahun_filter ? 'selected' : '' ?>><?= $tahun ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="col-md-5">
          <label for="search" class="form-label">Cari Nama:</label>
          <input type="text" name="search" id="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Cari nama...">
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary">Tampilkan</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <a href="?tahun=<?= $tahun_filter ?>&search=<?= urlencode($search) ?>&download=csv" class="btn btn-success w-100">
            &#128190; Download CSV
          </a>
        </div>
      </form>

      <!-- Tabel -->
      <div class="table-responsive">
        <table class="table table-bordered bg-white shadow-sm">
          <thead class="table-dark">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Tanggal</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
          <?php if (mysqli_num_rows($result_data) > 0): ?>
            <?php $no = $offset + 1; ?>
            <?php while ($row = mysqli_fetch_assoc($result_data)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td><?= $row['tanggal'] ?></td>
              <td><?= $row['waktu'] ?></td>
            </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="4" class="text-center">Data tidak ditemukan.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <nav>
        <ul class="pagination">
          <?php for ($p = 1; $p <= $total_pages; $p++): ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?tahun=<?= $tahun_filter ?>&search=<?= urlencode($search) ?>&page=<?= $p ?>">
              <?= $p ?>
            </a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
  </script>
</body>
</html>