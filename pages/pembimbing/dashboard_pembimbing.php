<?php
include "koneksi.php";

if (!isset($_SESSION['id_pembimbing'])) {
  header("Location: sign-in.php");
  exit();
}

$id_pembimbing = $_SESSION['id_pembimbing'];

$stmt = mysqli_prepare($coneksi, "SELECT p.nama_pembimbing, p.id_perusahaan, pr.nama_perusahaan 
                                FROM pembimbing p
                                LEFT JOIN perusahaan pr ON p.id_perusahaan = pr.id_perusahaan
                                WHERE p.id_pembimbing = ?");
mysqli_stmt_bind_param($stmt, "i", $id_pembimbing);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pembimbing = mysqli_fetch_assoc($result);

if (!$pembimbing) {
  session_destroy();
  header("Location: sign-in.php");
  exit();
}

$nama_pembimbing = $pembimbing['nama_pembimbing'];
$id_perusahaan = $pembimbing['id_perusahaan'];
$nama_perusahaan = $pembimbing['nama_perusahaan'];

// Jumlah siswa di perusahaan ini
$query_siswa = mysqli_query($coneksi, "SELECT COUNT(*) as total FROM siswa WHERE id_perusahaan = '$id_perusahaan'");
$jumlah_siswa = mysqli_fetch_assoc($query_siswa)['total'];

// Jumlah absen siswa hari ini di perusahaan ini
$tanggal_hari_ini = date('Y-m-d');
$query_absen_hari_ini = mysqli_query($coneksi, "SELECT COUNT(DISTINCT a.id_siswa) as total 
                                              FROM absen a
                                              JOIN siswa s ON a.id_siswa = s.id_siswa
                                              WHERE s.id_perusahaan = '$id_perusahaan' 
                                              AND a.tanggal = '$tanggal_hari_ini'");
$jumlah_absen_hari_ini = mysqli_fetch_assoc($query_absen_hari_ini)['total'];

// Jumlah jurnal siswa hari ini di perusahaan ini
$query_jurnal_hari_ini = mysqli_query($coneksi, "SELECT COUNT(*) as total 
                                               FROM jurnal j
                                               JOIN siswa s ON j.id_siswa = s.id_siswa
                                               WHERE s.id_perusahaan = '$id_perusahaan' 
                                               AND j.tanggal = '$tanggal_hari_ini'");
$jumlah_jurnal_hari_ini = mysqli_fetch_assoc($query_jurnal_hari_ini)['total'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard Pembimbing</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body {
      padding-left: 270px;
      transition: padding-left 0.3s;
      background-color: #f8f9fa;
    }

    .main-container {
      margin-top: 20px;
      margin-right: 20px;
      margin-left: 0;
      max-width: none;
    }

    .container-custom {
      background-color: #ffffff;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
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
    @media (max-width: 991px) {
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

      .body-card {
        background-color: #fff;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgb(0 0 0 / 0.1);
        margin-bottom: 20px;
      }

      .chart {
        height: 170px;
        position: relative;
      }
    }
  </style>


</head>

<body>
  <div class="main-container container">
    <div class="row">
      <!-- Kartu Siswa -->
      <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-header p-3 pt-2">
            <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
              <i class="material-icons opacity-10">group</i>
            </div>
            <div class="text-end pt-1">
              <p class="text-sm mb-0 text-capitalize">Total Siswa</p>
              <h4 class="mb-0"><?= $jumlah_siswa ?></h4>
              <p class="date-info">Di perusahaan <?= $nama_perusahaan ?></p>
            </div>
          </div>
          <hr class="dark horizontal my-0" />
        </div>
      </div>

      <!-- Kartu Absen Hari Ini (menggantikan Pembimbing) -->
      <div class="col-xl-4 col-sm-6 mb-xl-0 mb-4">
        <div class="card">
          <div class="card-header p-3 pt-2">
            <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
              <i class="material-icons opacity-10">how_to_reg</i>
            </div>
            <div class="text-end pt-1">
              <p class="text-sm mb-0 text-capitalize">Absen Hari Ini</p>
              <h4 class="mb-0"><?= $jumlah_absen_hari_ini ?> / <?= $jumlah_siswa ?></h4>
              <p class="date-info"><?= date('d M Y') ?></p>
            </div>
          </div>
          <hr class="dark horizontal my-0" />
        </div>
      </div>

      <!-- Kartu Jurnal Hari Ini -->
      <div class="col-xl-4 col-sm-6">
        <div class="card">
          <div class="card-header p-3 pt-2">
            <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
              <i class="material-icons opacity-10">book</i>
            </div>
            <div class="text-end pt-1">
              <p class="text-sm mb-0 text-capitalize">Jurnal Hari Ini</p>
              <h4 class="mb-0"><?= $jumlah_jurnal_hari_ini ?></h4>
              <p class="date-info"><?= date('d M Y') ?></p>
            </div>
          </div>
          <hr class="dark horizontal my-0" />
        </div>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-center" style="margin-top:25px;">
            <div class="demo-item">
                <div class="variant-title">Vibrant Red</div>
                <div class="scan-container">
                    <a href="scan.php" class="scan-btn scan-btn-variant-2" title="Scan QR / Barcode">
                        <span class="material-icons scan-icon">qr_code</span>
                        <div class="scan-label">Scan Code</div>
                    </a>
                </div>
            </div>
    </div>

    <style>
        .scan-container {
            position: relative;
            margin: 50px 0;
        }

        /* Main scan button */
        .scan-btn {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
            position: relative;
            z-index: 10;
            cursor: pointer;
            border: none;
            outline: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 
                0 10px 30px rgba(102, 126, 234, 0.4),
                0 0 0 0 rgba(102, 126, 234, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: 
                floating 3s ease-in-out infinite,
                pulse-ring 2s infinite;
        }

        .scan-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 
                0 20px 40px rgba(102, 126, 234, 0.6),
                0 0 0 0 rgba(102, 126, 234, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            color: #fff;
            text-decoration: none;
        }

        .scan-btn:active {
            transform: translateY(-2px) scale(0.98);
        }

        /* Scan icon with rotation */
        .scan-icon {
            font-size: 36px;
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .scan-btn:hover .scan-icon {
            transform: rotate(90deg) scale(1.1);
        }

        /* Animated rings around button */
        .scan-btn::before,
        .scan-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 50%;
            animation: ripple 2s infinite;
        }

        .scan-btn::before {
            width: 100px;
            height: 100px;
            animation-delay: 0s;
        }

        .scan-btn::after {
            width: 120px;
            height: 120px;
            animation-delay: 0.7s;
        }

        /* Floating animation */
        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Ripple effect */
        @keyframes ripple {
            0% {
                opacity: 0.8;
                transform: translate(-50%, -50%) scale(0.8);
            }
            100% {
                opacity: 0;
                transform: translate(-50%, -50%) scale(1.4);
            }
        }

        /* Pulse ring animation */
        @keyframes pulse-ring {
            0% {
                box-shadow: 
                    0 10px 30px rgba(102, 126, 234, 0.4),
                    0 0 0 0 rgba(102, 126, 234, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }
            50% {
                box-shadow: 
                    0 15px 40px rgba(102, 126, 234, 0.5),
                    0 0 0 15px rgba(102, 126, 234, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
            }
            100% {
                box-shadow: 
                    0 10px 30px rgba(102, 126, 234, 0.4),
                    0 0 0 0 rgba(102, 126, 234, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }
        }

        /* Scan text label */
        .scan-label {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.5px;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .scan-container:hover .scan-label {
            opacity: 1;
            bottom: -35px;
        }

        /* Alternative design variations */
        .scan-btn-variant-2 {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            box-shadow: 
                0 10px 30px rgba(255, 107, 107, 0.4),
                0 0 0 0 rgba(255, 107, 107, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .scan-btn-variant-2::before,
        .scan-btn-variant-2::after {
            border-color: rgba(255, 107, 107, 0.3);
        }

        .scan-btn-variant-3 {
            background: linear-gradient(135deg, #48CAE4 0%, #0096C7 100%);
            box-shadow: 
                0 10px 30px rgba(72, 202, 228, 0.4),
                0 0 0 0 rgba(72, 202, 228, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }

        .scan-btn-variant-3::before,
        .scan-btn-variant-3::after {
            border-color: rgba(72, 202, 228, 0.3);
        }

        /* Glassmorphism variant */
        .scan-btn-glass {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.3),
                0 0 0 0 rgba(255, 255, 255, 0.2);
        }

        .scan-btn-glass::before,
        .scan-btn-glass::after {
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Responsive design */
        @media (max-width: 576px) {
            .scan-btn {
                width: 65px;
                height: 65px;
            }
            
            .scan-icon {
                font-size: 28px;
            }
            
            .scan-btn::before {
                width: 85px;
                height: 85px;
            }
            
            .scan-btn::after {
                width: 105px;
                height: 105px;
            }
        }

        /* Demo container */
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 60px;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .demo-item {
            text-align: center;
        }

        .variant-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
    </style>
  </div>
</body>

</html>