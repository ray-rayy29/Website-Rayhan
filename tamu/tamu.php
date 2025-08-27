<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../admin/index.php");
    exit();
}

include '../koneksi.php';
$username = $_SESSION['username'];

// Proses hapus data
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    
    // Validasi ID harus numerik
    if (is_numeric($id_hapus)) {
        // Cek apakah data ada sebelum menghapus
        $cek_sql = "SELECT * FROM tamu WHERE id = $id_hapus";
        $cek_result = mysqli_query($conn, $cek_sql);
        
        if (mysqli_num_rows($cek_result) > 0) {
            // Hapus dari tabel tamu (bukan admin)
            $sql_hapus = "DELETE FROM tamu WHERE id = $id_hapus";
            
            if (mysqli_query($conn, $sql_hapus)) {
                $pesan_berhasil = "Data tamu berhasil dihapus.";
            } else {
                $pesan_error = "Error: " . mysqli_error($conn);
            }
        } else {
            $pesan_error = "Data tidak ditemukan.";
        }
    } else {
        $pesan_error = "ID tidak valid.";
    }
}

// Ambil input filter tanggal
$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : '';
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : '';

// Pagination
$batas = 10;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$mulai = ($halaman > 1) ? ($halaman * $batas) - $batas : 0;

// Filter query
$filter_sql = "";
if (!empty($tanggal_awal) && !empty($tanggal_akhir)) {
    $filter_sql = " WHERE tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
}

// Query data tamu
$sql = "SELECT * FROM tamu $filter_sql ORDER BY id DESC LIMIT $mulai, $batas";
$data = mysqli_query($conn, $sql);

// Hitung total data
$total_data_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM tamu $filter_sql");
$total_data_row = mysqli_fetch_assoc($total_data_query);
$total_data = $total_data_row['total'];
$total_halaman = ceil($total_data / $batas);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Tamu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    
    .filter {
      margin-bottom: 25px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }
    
    .filter label {
      font-weight: 600;
      color: #2c3e50;
    }
    
    .filter input[type="date"] {
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 5px;
      font-size: 14px;
    }
    
    .filter button {
      background-color: #3498db;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    
    .filter button:hover {
      background-color: #2980b9;
    }
    
    .filter .reset-btn {
      background-color: #95a5a6;
      color: white;
      text-decoration: none;
      padding: 8px 15px;
      border-radius: 5px;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    
    .filter .reset-btn:hover {
      background-color: #7f8c8d;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    
    table th {
      background-color: #3498db;
      color: white;
      padding: 12px 15px;
      text-align: left;
    }
    
    table td {
      padding: 10px 15px;
      border-bottom: 1px solid #eee;
    }
    
    table tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    
    table tr:hover {
      background-color: #f1f7fd;
    }
    
    .pagination {
      display: flex;
      justify-content: center;
      margin: 20px 0;
      gap: 5px;
    }
    
    .pagination a {
      padding: 8px 12px;
      text-decoration: none;
      border: 1px solid #ddd;
      color: #3498db;
      border-radius: 5px;
      transition: all 0.3s;
    }
    
    .pagination a.active {
      background-color: #3498db;
      color: white;
      border-color: #3498db;
    }
    
    .pagination a:hover:not(.active) {
      background-color: #f1f1f1;
    }
    
    .btn-back {
      display: inline-block;
      background-color: #2ecc71;
      color: white;
      padding: 10px 15px;
      text-decoration: none;
      border-radius: 5px;
      transition: background-color 0.3s;
    }
    
    .btn-back:hover {
      background-color: #27ae60;
    }
    
    .btn-back i {
      margin-right: 5px;
    }
    
    /* Modal Konfirmasi Hapus */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    
    .modal-content {
      background-color: white;
      margin: 15% auto;
      padding: 20px;
      border-radius: 10px;
      width: 350px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }
    
    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
    }
    
    .modal-btn {
      padding: 8px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: 600;
    }
    
    .modal-confirm {
      background-color: #e74c3c;
      color: white;
    }
    
    .modal-cancel {
      background-color: #95a5a6;
      color: white;
    }
    
    .btn-hapus {
      background-color: #e74c3c;
      color: white;
      padding: 5px 10px;
      border-radius: 5px;
      text-decoration: none;
      transition: background-color 0.3s;
    }
    
    .btn-hapus:hover {
      background-color: #c0392b;
    }
    
    .alert {
      padding: 12px 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    
    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
      
      table {
        font-size: 14px;
      }
      
      table th, table td {
        padding: 8px 10px;
      }
      
      .filter {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .modal-content {
        width: 80%;
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
        <h2>Daftar Tamu</h2>
      </div>

      <?php if (isset($pesan_berhasil)): ?>
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i> <?php echo $pesan_berhasil; ?>
        </div>
      <?php endif; ?>
      
      <?php if (isset($pesan_error)): ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i> <?php echo $pesan_error; ?>
        </div>
      <?php endif; ?>

      <form class="filter" method="GET" action="">
        <label><i class="fas fa-calendar-alt"></i> Dari:</label>
        <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>">

        <label>Sampai:</label>
        <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>">

        <button type="submit"><i class="fas fa-search"></i> Tampilkan</button>
        <a href="tamu.php" class="reset-btn">Reset</a>
      </form>

      <table>
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Instansi</th>
          <th>Keperluan</th>
          <th>Tanggal</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
        
        <?php 
        $no = $mulai + 1;
        if (mysqli_num_rows($data) > 0) {
            while ($row = mysqli_fetch_assoc($data)) {
              echo "<tr>
                <td>$no</td>
                <td>{$row['nama']}</td>
                <td>{$row['instansi']}</td>
                <td>{$row['keperluan']}</td>
                <td>{$row['tanggal']}</td>
                <td>{$row['waktu']}</td>
                <td>
                  <a href='#' class='btn-hapus' onclick='confirmHapus({$row['id']})'>
                    <i></i> Hapus
                  </a>
                </td>
              </tr>";
              $no++;
            }
        } else {
            echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data tamu</td></tr>";
        }
        ?>
      </table>

      <?php if ($total_halaman > 1): ?>
      <div class="pagination">
        <?php for ($i = 1; $i <= $total_halaman; $i++) : ?>
          <a class="<?= ($i == $halaman) ? 'active' : '' ?>" href="?halaman=<?= $i ?>&tanggal_awal=<?= urlencode($tanggal_awal) ?>&tanggal_akhir=<?= urlencode($tanggal_akhir) ?>">
            <?= $i ?>
          </a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Modal Konfirmasi Hapus -->
  <div id="modalHapus" class="modal">
    <div class="modal-content">
      <h3>Konfirmasi Hapus</h3>
      <p>Apakah Anda yakin ingin menghapus data tamu ini?</p>
      <div class="modal-buttons">
        <button class="modal-btn modal-confirm" onclick="hapusData()">Ya, Hapus</button>
        <button class="modal-btn modal-cancel" onclick="tutupModal()">Batal</button>
      </div>
    </div>
  </div>

  <script>
    let idHapus = null;
    
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
    }
    
    function confirmHapus(id) {
      idHapus = id;
      document.getElementById('modalHapus').style.display = 'block';
    }
    
    function tutupModal() {
      document.getElementById('modalHapus').style.display = 'none';
      idHapus = null;
    }
    
    function hapusData() {
      if (idHapus) {
        // Tambahkan parameter filter tanggal jika ada
        let url = 'tamu.php?hapus=' + idHapus;
        
        // Jika ada filter tanggal, pertahankan parameter tersebut
        const urlParams = new URLSearchParams(window.location.search);
        const tanggalAwal = urlParams.get('tanggal_awal');
        const tanggalAkhir = urlParams.get('tanggal_akhir');
        const halaman = urlParams.get('halaman');
        
        if (tanggalAwal) url += '&tanggal_awal=' + encodeURIComponent(tanggalAwal);
        if (tanggalAkhir) url += '&tanggal_akhir=' + encodeURIComponent(tanggalAkhir);
        if (halaman) url += '&halaman=' + encodeURIComponent(halaman);
        
        window.location.href = url;
      }
    }
    
    // Tutup modal jika klik di luar area modal
    window.onclick = function(event) {
      const modal = document.getElementById('modalHapus');
      if (event.target == modal) {
        tutupModal();
      }
    }
  </script>
</body>
</html>