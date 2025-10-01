<?php
include "koneksi.php";

// Authentication Check
if (!isset($_SESSION['id_pembimbing'])) {
    header("Location: sign-in.php");
    exit();
}

$id_pembimbing = $_SESSION['id_pembimbing'];

// Get Pembimbing Data
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
$tanggal_hari_ini = date('Y-m-d');

// Get Statistics using Prepared Statements
// Total Students
$stmt_siswa = mysqli_prepare($coneksi, "SELECT COUNT(*) as total FROM siswa WHERE id_perusahaan = ?");
mysqli_stmt_bind_param($stmt_siswa, "i", $id_perusahaan);
mysqli_stmt_execute($stmt_siswa);
$jumlah_siswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_siswa))['total'];

// Today's Attendance
$stmt_absen = mysqli_prepare($coneksi, "SELECT COUNT(DISTINCT a.id_siswa) as total 
                                        FROM absen a
                                        JOIN siswa s ON a.id_siswa = s.id_siswa
                                        WHERE s.id_perusahaan = ? AND a.tanggal = ?");
mysqli_stmt_bind_param($stmt_absen, "is", $id_perusahaan, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_absen);
$jumlah_absen_hari_ini = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_absen))['total'];

// Today's Journal
$stmt_jurnal = mysqli_prepare($coneksi, "SELECT COUNT(*) as total 
                                         FROM jurnal j
                                         JOIN siswa s ON j.id_siswa = s.id_siswa
                                         WHERE s.id_perusahaan = ? AND j.tanggal = ?");
mysqli_stmt_bind_param($stmt_jurnal, "is", $id_perusahaan, $tanggal_hari_ini);
mysqli_stmt_execute($stmt_jurnal);
$jumlah_jurnal_hari_ini = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_jurnal))['total'];
$jurnal=(mysqli_query($coneksi,"SELECT *
                                    FROM jurnal j
                                    JOIN siswa s ON j.id_siswa = s.id_siswa
                                    ORDER BY j.id_jurnal DESC
                                    LIMIT 6;
                                    "));
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Dashboard Pembimbing - Monitoring Siswa PKL">
    <title>Dashboard Pembimbing - <?= htmlspecialchars($nama_perusahaan) ?></title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <style>
        /* ===================== GLOBAL STYLES ===================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            padding-left: 270px;
            transition: padding-left 0.3s ease;
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* ===================== MAIN CONTAINER ===================== */
        .main-container {
            margin-top: 20px;
            margin-right: 20px;
            margin-left: 0;
            max-width: none;
        }

        /* ===================== CARD STYLES ===================== */
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: transparent;
            border: none;
        }

        /* ===================== ICON STYLES ===================== */
        .icon {
            width: 64px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .bg-gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #48CAE4 0%, #0096C7 100%);
        }

        .material-icons {
            font-size: 32px;
            color: white;
        }

        /* ===================== STAT CARD CONTENT ===================== */
        .text-end {
            text-align: right;
        }

        .text-sm {
            font-size: 0.875rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .card-header h4 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #2d3748;
            margin: 8px 0;
        }

        .date-info {
            font-size: 0.75rem;
            color: #a0aec0;
            margin-top: 4px;
        }

        /* ===================== SCAN BUTTON SECTION ===================== */
        .scan-container {
            position: relative;
            padding: 50px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

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
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            box-shadow: 
                0 10px 30px rgba(255, 107, 107, 0.4),
                0 0 0 0 rgba(255, 107, 107, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            animation: 
                floating 3s ease-in-out infinite,
                pulse-ring 2s infinite;
        }

        .scan-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 
                0 20px 40px rgba(255, 107, 107, 0.6),
                0 0 0 0 rgba(255, 107, 107, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            color: #fff;
            text-decoration: none;
        }

        .scan-btn:active {
            transform: translateY(-2px) scale(0.98);
        }

        .scan-icon {
            font-size: 36px;
            transition: all 0.3s ease;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .scan-btn:hover .scan-icon {
            transform: rotate(90deg) scale(1.1);
        }

        /* Animated rings */
        .scan-btn::before,
        .scan-btn::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            border: 2px solid rgba(255, 107, 107, 0.3);
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

        .scan-label {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: #2d3748;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
            white-space: nowrap;
        }

        .scan-container:hover .scan-label {
            opacity: 1;
            bottom: -35px;
        }

        /* ===================== JOURNAL SECTION ===================== */
        .notes-section {
            padding: 15px;
        }

        .notes-header {
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notes-body {
            max-height: 400px;
            overflow-y: auto;
        }

        .notes-body::-webkit-scrollbar {
            width: 6px;
        }

        .notes-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .notes-body::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .notes-body::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .journal-item {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            background-color: #f7fafc;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .journal-item:hover {
            border-color: #667eea;
            background-color: #fff;
            transform: translateX(5px);
        }

        .journal-item:last-child {
            margin-bottom: 0;
        }

        .journal-title {
            color: #667eea;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .journal-content {
            color: #4a5568;
            font-size: 0.875rem;
            line-height: 1.5;
            margin: 0;
        }

        /* ===================== ANIMATIONS ===================== */
        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

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

        @keyframes pulse-ring {
            0% {
                box-shadow: 
                    0 10px 30px rgba(255, 107, 107, 0.4),
                    0 0 0 0 rgba(255, 107, 107, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }
            50% {
                box-shadow: 
                    0 15px 40px rgba(255, 107, 107, 0.5),
                    0 0 0 15px rgba(255, 107, 107, 0.1),
                    inset 0 1px 0 rgba(255, 255, 255, 0.3);
            }
            100% {
                box-shadow: 
                    0 10px 30px rgba(255, 107, 107, 0.4),
                    0 0 0 0 rgba(255, 107, 107, 0.3),
                    inset 0 1px 0 rgba(255, 255, 255, 0.2);
            }
        }

        /* ===================== RESPONSIVE DESIGN ===================== */
        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }

            .main-container {
                margin: 10px;
            }

            .icon {
                width: 48px;
                height: 48px;
            }

            .material-icons {
                font-size: 24px;
            }

            .card-header h4 {
                font-size: 1.25rem;
            }

            .text-sm {
                font-size: 0.75rem;
            }
        }

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

            .section-title {
                font-size: 1rem;
            }

            .journal-item {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container container">
        <!-- Statistics Cards Row -->
        <div class="row">
            <!-- Total Students Card -->
            <div class="col-xl-4 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">group</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Total Siswa</p>
                            <h4 class="mb-0"><?= htmlspecialchars($jumlah_siswa) ?></h4>
                            <p class="date-info">Di <?= htmlspecialchars($nama_perusahaan) ?></p>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>

            <!-- Today's Attendance Card -->
            <div class="col-xl-4 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">how_to_reg</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Absen Hari Ini</p>
                            <h4 class="mb-0"><?= htmlspecialchars($jumlah_absen_hari_ini) ?> / <?= htmlspecialchars($jumlah_siswa) ?></h4>
                            <p class="date-info"><?= date('d M Y') ?></p>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>

            <!-- Today's Journal Card -->
            <div class="col-xl-4 col-sm-6 mb-4">
                <div class="card">
                    <div class="card-header p-3 pt-2">
                        <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                            <i class="material-icons opacity-10">book</i>
                        </div>
                        <div class="text-end pt-1">
                            <p class="text-sm mb-0 text-capitalize">Jurnal Hari Ini</p>
                            <h4 class="mb-0"><?= htmlspecialchars($jumlah_jurnal_hari_ini) ?></h4>
                            <p class="date-info"><?= date('d M Y') ?></p>
                        </div>
                    </div>
                    <hr class="dark horizontal my-0">
                </div>
            </div>
        </div>

        <!-- Action and Journal Row -->
        <div class="row">
            <!-- Scan QR Button -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <div class="scan-container d-flex justify-content-center align-items-center" style="height: 200px;">
                    <a href="scan.php" class="scan-btn text-center" title="Scan QR / Barcode">
                        <span class="material-icons scan-icon d-block mb-2">qr_code</span>
                        <div class="scan-label">Scan Code</div>
                    </a>
                </div>
            </div>
            <!-- Journal Notes Section -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <div class="card h-100">
                    <div class="notes-section">
                        <div class="notes-header">
                            <h2 class="section-title">
                                <i class="material-icons">assignment</i>
                                Jurnal Siswa
                            </h2>
                        </div>
                        <div class="notes-body">
                        <?php
                            while($row=mysqli_fetch_array($jurnal)){
                        ?>
                            <div class="journal-item">
                                <h6 class="journal-title"><?php echo $row['nama_siswa']?></h6><?php echo $row['tanggal']?>
                                <p class="journal-content"><?php echo $row['keterangan']?></p>
                            </div>
                        <?php
                            }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>