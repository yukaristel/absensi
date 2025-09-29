<?php
// Pastikan tidak ada output sebelum ini
ob_start();

include 'koneksi.php';

// Proses AJAX untuk mencari data siswa berdasarkan NISN dan cek/input absensi
if (isset($_POST['nisn'])) {
    ob_clean();
    header('Content-Type: application/json');
    
    $nisn = trim($_POST['nisn']);
    $tanggal_hari_ini = date('Y-m-d');
    $jam_sekarang = date('H:i:s');
    
    try {
        // Cari data siswa
        $query = "SELECT id_siswa, nis, nisn, nama_siswa FROM siswa WHERE nisn = ?";
        $stmt = $coneksi->prepare($query);
        
        if (!$stmt) {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Query error: ' . $coneksi->error
            ]);
            exit;
        }
        
        $stmt->bind_param("s", $nisn);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $siswa = $result->fetch_assoc();
            $id_siswa = $siswa['id_siswa'];
            
            // Cek apakah sudah absen hari ini
            $query_absen = "SELECT id_absen, jam_masuk, keterangan FROM absen WHERE id_siswa = ? AND tanggal = ?";
            $stmt_absen = $coneksi->prepare($query_absen);
            $stmt_absen->bind_param("is", $id_siswa, $tanggal_hari_ini);
            $stmt_absen->execute();
            $result_absen = $stmt_absen->get_result();
            
            if ($result_absen->num_rows > 0) {
                // Sudah absen hari ini - tampilkan data absen
                $absen = $result_absen->fetch_assoc();
                echo json_encode([
                    'status' => 'success',
                    'already_absent' => true,
                    'data' => [
                        'id_siswa' => $siswa['id_siswa'],
                        'nama_siswa' => $siswa['nama_siswa'],
                        'jam_masuk' => $absen['jam_masuk'],
                        'keterangan' => $absen['keterangan']
                    ],
                    'message' => 'Siswa sudah absen hari ini'
                ]);
            } else {
                // Belum absen - insert data absen baru
                $ip_address = $_SERVER['REMOTE_ADDR'];
                $keterangan = 'Hadir';
                
                // Ambil lokasi/koordinat (contoh static, bisa diganti dengan geolocation)
                $lokasi = 'Lokasi Scan';
                $kota = 'Kota';
                $isp = 'ISP Tidak Diketahui';
                $koordinat = '0,0';
                
                $query_insert = "INSERT INTO absen (id_siswa, jam_masuk, tanggal, keterangan, ip_address, lokasi, kota, isp, koordinat) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt_insert = $coneksi->prepare($query_insert);
                $stmt_insert->bind_param("issssssss", $id_siswa, $jam_sekarang, $tanggal_hari_ini, $keterangan, $ip_address, $lokasi, $kota, $isp, $koordinat);
                
                if ($stmt_insert->execute()) {
                    echo json_encode([
                        'status' => 'success',
                        'already_absent' => false,
                        'data' => [
                            'id_siswa' => $siswa['id_siswa'],
                            'nama_siswa' => $siswa['nama_siswa'],
                            'jam_masuk' => $jam_sekarang,
                            'keterangan' => $keterangan
                        ],
                        'message' => 'Absensi berhasil dicatat'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Gagal menyimpan absensi: ' . $coneksi->error
                    ]);
                }
                
                $stmt_insert->close();
            }
            
            $stmt_absen->close();
        } else {
            echo json_encode([
                'status' => 'error', 
                'message' => 'Data siswa dengan NISN "' . $nisn . '" tidak ditemukan'
            ]);
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
    
    $coneksi->close();
    exit;
}

ob_end_clean();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Barcode - Data Siswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .scan-container { max-width: 500px; margin: 0 auto; padding: 20px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; text-align: center; padding: 20px; border: none; }
        #reader { border-radius: 15px; overflow: hidden; margin-bottom: 20px; }
        .result-card { background: linear-gradient(135deg, #48CAE4 0%, #0096C7 100%); color: white; border-radius: 15px; padding: 20px; margin-top: 20px; display: none; }
        .result-card.already { background: linear-gradient(135deg, #ffa500 0%, #ff8c00 100%); }
        .data-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 10px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; }
        .data-item strong { margin-right: 10px; }
        .status-badge { display: inline-block; padding: 8px 16px; background: rgba(255, 255, 255, 0.2); border-radius: 20px; font-size: 14px; font-weight: bold; margin-bottom: 15px; }
        .btn-reset { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: 500; margin-top: 15px; width: 100%; }
        .btn-reset:hover { opacity: 0.9; }
        .btn-start { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 500; }
        .btn-start:hover { opacity: 0.9; }
        .btn-stop { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); border: none; color: white; padding: 10px 20px; border-radius: 10px; font-weight: 500; }
        .btn-stop:hover { opacity: 0.9; }
        #reader__scan_region { border: 3px solid #667eea !important; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12">
                <div class="scan-container">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">
                                <i class="material-icons">qr_code_scanner</i> Scan Barcode Siswa
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Pilihan Kamera -->
                            <div class="mb-3">
                                <label for="cameraSelect" class="form-label fw-bold">Pilih Kamera:</label>
                                <select id="cameraSelect" class="form-select mb-2">
                                    <option value="">Memuat kamera...</option>
                                </select>
                            </div>

                            <!-- Control Buttons -->
                            <div class="d-grid gap-2 mb-3">
                                <button id="startBtn" class="btn btn-start">
                                    <i class="material-icons" style="vertical-align: middle;">play_arrow</i>
                                    Mulai Scan
                                </button>
                                <button id="stopBtn" class="btn btn-stop" style="display:none;">
                                    <i class="material-icons" style="vertical-align: middle;">stop</i>
                                    Stop Scan
                                </button>
                            </div>

                            <!-- Scanner Area -->
                            <div id="reader"></div>

                            <!-- Status Message -->
                            <div id="statusMessage" class="alert" style="display:none;"></div>

                            <!-- Hasil Scan -->
                            <div id="resultCard" class="result-card">
                                <h5 class="mb-3">
                                    <i class="material-icons" style="vertical-align: middle;">person</i> 
                                    Data Siswa
                                </h5>
                                <div id="studentData"></div>
                                <button id="resetBtn" class="btn btn-reset">
                                    <i class="material-icons" style="vertical-align: middle;">refresh</i> Scan Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
let html5QrcodeScanner = null;
let isScanning = false;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize scanner object
    html5QrcodeScanner = new Html5Qrcode("reader");
    
    // Load cameras
    loadCameras();

    // Event listeners
    document.getElementById('startBtn').addEventListener('click', startScanning);
    document.getElementById('stopBtn').addEventListener('click', stopScanning);
    document.getElementById('resetBtn').addEventListener('click', resetScan);
});

// Load available cameras
async function loadCameras() {
    try {
        const devices = await Html5Qrcode.getCameras();
        
        if (devices && devices.length > 0) {
            const select = document.getElementById('cameraSelect');
            select.innerHTML = '';
            
            devices.forEach((device, index) => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Kamera ${index + 1}`;
                select.appendChild(option);
            });
            
            // Select back camera if available
            const backCamera = devices.find(d => d.label.toLowerCase().includes('back'));
            if (backCamera) {
                select.value = backCamera.id;
            }
            
            showStatus("Pilih kamera dan klik 'Mulai Scan'", "info");
        } else {
            showStatus("Tidak ada kamera ditemukan", "danger");
        }
    } catch (err) {
        showStatus("Error memuat kamera: " + err, "danger");
    }
}

// Start scanning
async function startScanning() {
    const cameraId = document.getElementById('cameraSelect').value;
    
    if (!cameraId) {
        showStatus("Pilih kamera terlebih dahulu", "warning");
        return;
    }
    
    if (isScanning) {
        return;
    }
    
    try {
        const config = {
            fps: 10,
            qrbox: { width: 300, height: 150 },
            formatsToSupport: [
                Html5QrcodeSupportedFormats.CODE_128,
                Html5QrcodeSupportedFormats.CODE_39,
                Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,
                Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,
                Html5QrcodeSupportedFormats.CODABAR,
                Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.QR_CODE
            ]
        };
        
        await html5QrcodeScanner.start(
            cameraId,
            config,
            onScanSuccess,
            onScanError
        );
        
        isScanning = true;
        document.getElementById('startBtn').style.display = 'none';
        document.getElementById('stopBtn').style.display = 'block';
        document.getElementById('cameraSelect').disabled = true;
        
        showStatus("Scanner aktif. Arahkan barcode ke kamera.", "success");
        
    } catch (err) {
        showStatus("Error memulai scanner: " + err, "danger");
        isScanning = false;
    }
}

// Stop scanning
async function stopScanning() {
    if (!isScanning) return;
    
    try {
        await html5QrcodeScanner.stop();
        
        isScanning = false;
        document.getElementById('startBtn').style.display = 'block';
        document.getElementById('stopBtn').style.display = 'none';
        document.getElementById('cameraSelect').disabled = false;
        
        showStatus("Scanner dihentikan", "info");
        
    } catch (err) {
        console.log("Error stopping scanner: " + err);
    }
}

// On scan success
function onScanSuccess(decodedText, decodedResult) {
    // Stop scanning
    stopScanning();
    
    // Fetch student data from database
    fetchStudentData(decodedText);
}

// On scan error (ignore, happens frequently)
function onScanError(errorMessage) {
    // Ignore, ini normal saat scanning
}

// Fetch student data from database
function fetchStudentData(nisn) {
    showStatus("Memproses absensi...", "info");
    
    fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'nisn=' + encodeURIComponent(nisn)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showStudentData(data.data, data.already_absent, data.message);
        } else {
            showStatus(data.message || 'Data siswa tidak ditemukan', 'danger');
        }
    })
    .catch(error => {
        showStatus('Error: ' + error.message, 'danger');
    });
}

// Show student data
function showStudentData(siswa, alreadyAbsent, message) {
    const resultCard = document.getElementById('resultCard');
    const statusText = alreadyAbsent ? '⚠️ SUDAH ABSEN' : '✓ ABSENSI BERHASIL';
    const statusClass = alreadyAbsent ? 'already' : '';
    
    const html = `
        <div class="status-badge">${statusText}</div>
        <div class="data-item">
            <strong>ID Siswa:</strong>
            <span>${siswa.id_siswa}</span>
        </div>
        <div class="data-item">
            <strong>Nama:</strong>
            <span>${siswa.nama_siswa}</span>
        </div>
        <div class="data-item">
            <strong>Jam Masuk:</strong>
            <span>${siswa.jam_masuk}</span>
        </div>
        <div class="data-item">
            <strong>Keterangan:</strong>
            <span>${siswa.keterangan}</span>
        </div>
    `;
    
    document.getElementById('studentData').innerHTML = html;
    resultCard.className = 'result-card ' + statusClass;
    resultCard.style.display = 'block';
    document.getElementById('statusMessage').style.display = 'none';
}

// Reset scan
async function resetScan() {
    document.getElementById("resultCard").style.display = "none";
    document.getElementById("statusMessage").style.display = "none";
    
    if (isScanning) {
        await stopScanning();
    }
    
    showStatus("Pilih kamera dan klik 'Mulai Scan'", "info");
}

// Show status
function showStatus(msg, type) {
    const el = document.getElementById("statusMessage");
    el.textContent = msg;
    el.className = "alert alert-" + type;
    el.style.display = "block";
}

// Cleanup on page unload
window.addEventListener('beforeunload', async function() {
    if (isScanning) {
        await stopScanning();
    }
});
</script>
</body>
</html>