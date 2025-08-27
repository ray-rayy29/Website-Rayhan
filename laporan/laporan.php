<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../admin/index.php");
    exit();
}

include '../koneksi.php';
$username = $_SESSION['username'];

// --- Tahun sekarang ---
$tahun_sekarang = date("Y");

// --- Ambil filter tahun ---
$tahun_filter = isset($_GET['tahun']) ? (int)$_GET['tahun'] : $tahun_sekarang;

// --- Ambil data tamu terakhir ---
$tamu_terakhir = mysqli_query($conn, "SELECT * FROM tamu ORDER BY id DESC LIMIT 1");
$tamu = mysqli_fetch_assoc($tamu_terakhir);

// --- Ambil data grafik per bulan ---
$grafik = mysqli_query($conn, "
    SELECT MONTH(tanggal) AS bulan, COUNT(*) AS jumlah
    FROM tamu
    WHERE YEAR(tanggal) = '$tahun_filter'
    GROUP BY MONTH(tanggal)
    ORDER BY MONTH(tanggal)
");

$data_grafik = [];
while ($row = mysqli_fetch_assoc($grafik)) {
    $data_grafik[] = $row;
}

// Nama bulan
$nama_bulan = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tamu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
          max-width: 900px;
          margin: 0 auto;
        }

        h2, h5 { text-align: center; margin-bottom: 20px; }
        .card { border-radius: 12px; box-shadow: 0px 2px 6px rgba(0,0,0,0.1); }
        .btn-custom { border-radius: 8px; }
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
      <li><a href="../tamu/tamu.php"><i class="fas fa-users"></i> Tamu</a></li>
      <li><a href="../tamu/kehadiran.php"><i class="fas fa-user-check"></i> Kehadiran</a></li>
      <li><a href="laporan.php"><i class="fas fa-file-alt"></i> Laporan</a></li>
      <li><a href="../admin/index.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
  </div>

  <div class="content">
    <div class="container">
      <h2>Laporan Tamu</h2>

      <!-- Filter Tahun -->
      <div class="text-center mb-4">
          <form method="GET" action="" class="d-inline-block">
              <div class="input-group">
                  <label class="input-group-text bg-primary text-white fw-bold" for="tahun">Tahun</label>
                  <select name="tahun" id="tahun" class="form-select" onchange="this.form.submit()">
                      <?php for ($t = 2019; $t <= $tahun_sekarang; $t++): ?>
                          <option value="<?= $t ?>" <?= ($t == $tahun_filter) ? 'selected' : '' ?>>
                              <?= $t ?>
                          </option>
                      <?php endfor; ?>
                  </select>
              </div>
          </form>
      </div>

      <!-- Tamu Terakhir -->
      <h5 class="fw-bold">👤 Tamu Terakhir Datang</h5>
      <?php if ($tamu): ?>
          <p><strong>Nama:</strong> <?= $tamu['nama'] ?></p>
          <p><strong>Tanggal:</strong> <?= $tamu['tanggal'] ?></p>
      <?php else: ?>
          <p class="text-muted">Belum ada tamu.</p>
      <?php endif; ?>

      <!-- Grafik -->
      <div class="card p-3 mb-4">
          <h5 class="fw-bold">📊 Grafik Tamu per Bulan Tahun <?= $tahun_filter ?></h5>
          <canvas id="grafikTamu"></canvas>
      </div>

      <!-- Tombol Aksi -->
      <div class="text-center">
          <button class="btn btn-success btn-custom" onclick="downloadChart()">⬇ Download Grafik</button>
      </div>
    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }

    const dataGrafik = <?= json_encode($data_grafik) ?>;
    const namaBulan = <?= json_encode($nama_bulan) ?>;

    const labels = [];
    const dataJumlah = [];

    for (let i = 1; i <= 12; i++) {
        labels.push(namaBulan[i]);
        const found = dataGrafik.find(item => item.bulan == i);
        dataJumlah.push(found ? parseInt(found.jumlah) : 0);
    }

    const ctx = document.getElementById('grafikTamu').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Jumlah Tamu',
                data: dataJumlah,
                backgroundColor: 'rgba(0,123,255,0.7)',
                borderColor: '#0056b3',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false }},
            scales: { y: { beginAtZero: true }}
        }
    });

    // Fungsi download grafik sebagai PNG
    function downloadChart() {
        const link = document.createElement('a');
        link.href = chart.toBase64Image();
        link.download = "laporan_tamu_<?= $tahun_filter ?>.png";
        link.click();
    }
  </script>
</body>
</html>