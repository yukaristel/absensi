<?php
include "koneksi.php";

// Cek apakah guru sudah login
if (!isset($_SESSION['id_guru'])) {
  header("Location: sign-in.php");
  exit();
}

$id_guru = $_SESSION['id_guru'];

// Ambil data guru dengan prepared statement
$stmt = mysqli_prepare($coneksi, "SELECT id_sekolah, nama_guru FROM guru WHERE id_guru = ?");
mysqli_stmt_bind_param($stmt, "i", $id_guru);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dataGuru = mysqli_fetch_assoc($result);

if (!$dataGuru) {
  // Jika data guru tidak ditemukan, arahkan ke login
  header("Location: sign-in.php");
  exit();
}

$id_sekolah = $dataGuru['id_sekolah'];
$nama_guru = $dataGuru['nama_guru'];

// Ambil nama sekolah
$query_sekolah = mysqli_query($coneksi, "SELECT nama_sekolah FROM sekolah WHERE id_sekolah = '$id_sekolah'");
$sekolah = mysqli_fetch_assoc($query_sekolah);
$nama_sekolah = $sekolah['nama_sekolah'] ?? 'Sekolah';

// Ambil jumlah data siswa
$query_siswa = mysqli_query($coneksi, "SELECT COUNT(*) as jumlah FROM siswa WHERE id_sekolah = '$id_sekolah'");
$siswaData = mysqli_fetch_assoc($query_siswa);
$jumlah_siswa = $siswaData['jumlah'] ?? 0;

// Ambil jumlah sekolah
$tanggal = date('Y-m-d');

$sql = "SELECT COUNT(DISTINCT a.id_siswa) AS jumlah_absen 
        FROM absen a 
        JOIN siswa s ON a.id_siswa = s.id_siswa 
        WHERE s.id_sekolah = ? AND a.tanggal = ?";

$stmt = mysqli_prepare($coneksi, $sql);
mysqli_stmt_bind_param($stmt, "is", $id_sekolah, $tanggal);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
$jumlah_siswa_absen = $row['jumlah_absen'] ?? 0;

$tanggal_hari_ini = date('Y-m-d');

$query_jurnal = mysqli_query($coneksi, "
    SELECT COUNT(DISTINCT id_jurnal) AS jumlah 
    FROM jurnal
    WHERE tanggal = '$tanggal_hari_ini'
");
$jurnalData = mysqli_fetch_assoc($query_jurnal);
$jumlah_jurnal = $jurnalData['jumlah'] ?? 0;

?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Guru - <?php echo htmlspecialchars($nama_guru); ?></title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      padding-left: 270px;
      /* tetap untuk desktop */
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      /* pastikan margin default hilang */
    }

    .main-container {
      margin: 20px;
    }

    h3 {
      color: #007bff;
      font-weight: 600;
      margin-bottom: 15px;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-4px);
    }

    .icon-circle {
      width: 50px;
      height: 50px;
      background: #007bff;
      border-radius: 50%;
      color: white;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 28px;
      margin-right: 15px;
    }

    .card-title {
      font-weight: 700;
      font-size: 1.2rem;
    }

    .card-number {
      font-weight: 600;
      font-size: 1.5rem;
      color: #333;
    }

    /* === Media Query untuk layar kecil (mobile) === */
    @media (max-width: 768px) {
      body {
        padding-left: 0;
        /* hilangkan padding kiri agar sidebar tidak mengganggu */
      }

      .main-container {
        margin: 10px;
        /* kurangi margin agar muat */
      }

      .icon-circle {
        width: 40px;
        height: 40px;
        font-size: 22px;
        margin-right: 10px;
      }

      .card-title {
        font-size: 1rem;
      }

      .card-number {
        font-size: 1.2rem;
      }

      .chart {
        height: 170px;
        position: relative;
      }

      .radio-label {
        margin-bottom: 10px;
      }

      input[type="radio"] {
        transform: scale(1.1);
      }
    }

    .body-card {
      background-color: #fff;
      padding: 15px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    /* Refresh Indicator */
    .refresh-indicator {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: var(--primary-color);
      color: white;
      padding: 10px 15px;
      border-radius: 50px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
      display: flex;
      align-items: center;
      z-index: 1000;
    }

    .refresh-indicator i {
      margin-right: 8px;
      animation: spin 2s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    h2 {
      color: #007bff;
      font-weight: 550
    }

    .table td,
    .table th {
      border: 1px solid #dee2e6 !important;
      vertical-align: middle;
    }

    .status-badge {
  display: inline-block;
  padding: 5px 15px;
  border-radius: 20px; /* bikin oval/pill */
  font-weight: bold;
  font-size: 14px;
}

.status-hadir {
  background-color: #d4edda; /* hijau muda */
  color: #155724;           /* hijau tua */
}

.status-sakit {
  background-color: #fff3cd; /* kuning muda */
  color: #856404;            /* kuning tua */
}

.status-izin {
  background-color: #d1ecf1; /* biru muda */
  color: #0c5460;            /* biru tua */
}

.status-alpa {
  background-color: #f8d7da; /* merah muda */
  color: #721c24;            /* merah tua */
}

.status-default {
  background-color: #e2e3e5;
  color: #383d41;
}

  </style>
</head>

<body>
  <div class="main-container">
    <div class="container-fluid">
      <div class="row">
        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">group</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Siswa</p>
                <h4 class="mb-0"><?php echo $jumlah_siswa; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
          </div>
        </div>

        <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">assignment</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Siswa Absen</p>
                <h4 class="mb-0"><?php echo $jumlah_siswa_absen; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
          </div>
        </div>

        <div class="col-xl-4 col-sm-6">
          <div class="card">
            <div class="card-header p-3 pt-2">
              <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                <i class="material-icons opacity-10">note_add</i>
              </div>
              <div class="text-end pt-1">
                <p class="text-sm mb-0 text-capitalize">Jurnal Siswa</p>
                <h4 class="mb-0"><?php echo $jumlah_jurnal; ?></h4>
              </div>
            </div>
            <hr class="dark horizontal my-0">
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="main-container mt-4">
    <div class="body">
      <div class="body-card">
        <div class="container-fluid my-4">
          <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead class="thead-primary bg-primary text-white">
                <tr>
                  <th>No</th>
                  <th>Nama Siswa</th>
                  <th>Status</th>
                  <th>Sakit</th>
                  <th>Izin</th>
                  <th>Alpa</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $index = 1;
                // Ambil ulang data siswa
                $query_siswa = mysqli_query($coneksi, "SELECT * FROM siswa WHERE id_guru = '$id_guru' ORDER BY id_siswa ASC");

                while ($siswa = mysqli_fetch_assoc($query_siswa)) {
                  $tanggal = date('Y-m-d');
                  $query_absen = mysqli_query(
                    $coneksi,
                    "SELECT keterangan FROM absen 
                 WHERE id_siswa = '" . $siswa['id_siswa'] . "' 
                 AND tanggal = '$tanggal'"
                  );
                  $absen = mysqli_fetch_assoc($query_absen);
                  $keterangan = $absen['keterangan'] ?? null;
                  
                  $badgeClass = 'badge-secondary';
                  $statusText = 'Belum Absen';
                  if ($keterangan) {
                    switch (strtolower($keterangan)) {
                      case 'hadir':
                        $badgeClass = 'badge-success';
                        $statusText = 'Hadir';
                        break;
                      case 'sakit':
                        $badgeClass = 'badge-warning';
                        $statusText = 'Sakit';
                        break;
                      case 'izin':
                        $badgeClass = 'badge-info';
                        $statusText = 'Izin';
                        break;
                      case 'alpa':
                        $badgeClass = 'badge-danger';
                        $statusText = 'Alpa';
                        break;
                    }
                  }
                  ?>
                  <tr>
                    <td><?= $index++; ?></td>
                    <td><?= htmlspecialchars($siswa['nama_siswa']); ?></td>
                    <td><span class="status-badge <?= $badgeClass ?>"><?= $statusText ?></span></td>  
                    <td><input type="radio" name="absen_<?= $siswa['id_siswa']; ?>" value="sakit" <?= ($keterangan === 'sakit') ? 'checked' : ''; ?> disabled></td>
                    <td><input type="radio" name="absen_<?= $siswa['id_siswa']; ?>" value="izin" <?= ($keterangan === 'izin') ? 'checked' : ''; ?> disabled></td>
                    <td><input type="radio" name="absen_<?= $siswa['id_siswa']; ?>" value="alpa" <?= ($keterangan === 'alpa') ? 'checked' : ''; ?> disabled></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      if (!localStorage.getItem('guruWelcomeShown')) {
        const namaGuru = "<?php echo !empty($nama_guru) ? htmlspecialchars($nama_guru, ENT_QUOTES) : 'Guru'; ?>";

        setTimeout(() => {
          Swal.fire({
            title: `Selamat datang ${namaGuru}!`,
            text: "Anda berhasil login ke sistem",
            icon: 'success',
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            toast: true,
            background: '#f8f9fa',
            didOpen: (toast) => {
              toast.addEventListener('mouseenter', Swal.stopTimer);
              toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
          });
        }, 300);

        localStorage.setItem('guruWelcomeShown', 'true');
      }
    });
  </script>
</body>

</html>