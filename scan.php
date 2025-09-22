<?php
include 'koneksi.php';

// Proses AJAX untuk mencari data siswa berdasarkan NISN
if (isset($_POST['nisn'])) {
    $nisn = $_POST['nisn'];
    
    $query = "SELECT id_siswa, nis, nisn, nama_siswa FROM siswa WHERE nisn = ?";
    $stmt = $koneksi->prepare($query);
    $stmt->bind_param("s", $nisn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $siswa = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $siswa]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data siswa tidak ditemukan']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Code - Data Siswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .scan-container { max-width: 500px; margin: 0 auto; padding: 20px; }
        .card { border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); backdrop-filter: blur(10px); background: rgba(255, 255, 255, 0.95); }
        .card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px 20px 0 0 !important; text-align: center; padding: 20px; border: none; }
        .video-container { position: relative; background: #000; border-radius: 15px; overflow: hidden; margin-bottom: 20px; }
        #video { width: 100%; height: 300px; object-fit: cover; }
        .scan-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; border: 3px solid #fff; border-radius: 15px; box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3); }
        .scan-text { position: absolute; bottom: -40px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 14px; font-weight: 500; }
        .result-card { background: linear-gradient(135deg, #48CAE4 0%, #0096C7 100%); color: white; border-radius: 15px; padding: 20px; margin-top: 20px; display: none; }
        .data-item { display: flex; justify-content: space-between; margin-bottom: 10px; padding: 10px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; }
        .btn-reset { background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); border: none; color: white; padding: 12px 25px; border-radius: 25px; font-weight: 500; margin-top: 15px; }
        /* Debug Panel */
        #debug { background:#000; color:#0f0; padding:10px; font-size:12px; max-height:200px; overflow:auto; margin-top:15px; border-radius:8px; }
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
                                <i class="material-icons">qr_code_scanner</i> Scan QR Code Siswa
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Dropdown Pilihan Kamera -->
                            <div class="camera-dropdown">
                                <label for="cameraSelect" class="form-label fw-bold">Pilih Kamera:</label>
                                <select id="cameraSelect" class="form-select">
                                    <option value="">Memuat kamera...</option>
                                </select>
                            </div>
                            <!-- Video Container -->
                            <div class="video-container">
                                <video id="video" autoplay muted playsinline></video>
                                <div class="scan-overlay"><div class="scan-text">Arahkan QR Code di sini</div></div>
                                <canvas id="canvas" style="display:none;"></canvas>
                            </div>
                            <!-- Status Message -->
                            <div id="statusMessage" class="alert" style="display:none;"></div>
                            <!-- Hasil Scan -->
                            <div id="resultCard" class="result-card">
                                <h5><i class="material-icons">person</i> Data Siswa</h5>
                                <div id="studentData"></div>
                                <button id="resetBtn" class="btn btn-reset w-100">
                                    <i class="material-icons">refresh</i> Scan Lagi
                                </button>
                            </div>
                            <!-- Debug Panel -->
                            <div id="debug">[DEBUG LOG]</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
let video, canvas, context, currentStream;
let scanning = true;

function debugLog(msg) {
    let debugDiv = document.getElementById('debug');
    debugDiv.innerHTML += "<br>" + msg;
    debugDiv.scrollTop = debugDiv.scrollHeight;
}

document.addEventListener('DOMContentLoaded', function() {
    video = document.getElementById('video');
    canvas = document.getElementById('canvas');
    context = canvas.getContext('2d');

    initCamera();

    document.getElementById('resetBtn').addEventListener('click', resetScan);
    document.getElementById('cameraSelect').addEventListener('change', function() {
        if (this.value) switchCamera(this.value);
    });
});

// Inisialisasi kamera
async function initCamera() {
    try {
        await navigator.mediaDevices.getUserMedia({ video: true });
        const devices = await navigator.mediaDevices.enumerateDevices();
        const videoDevices = devices.filter(d => d.kind === 'videoinput');
        const select = document.getElementById('cameraSelect');
        select.innerHTML = '';
        videoDevices.forEach((device, index) => {
            const option = document.createElement('option');
            option.value = device.deviceId;
            option.text = device.label || `Kamera ${index+1}`;
            select.appendChild(option);
        });
        if (videoDevices.length > 0) {
            select.value = videoDevices[0].deviceId;
            switchCamera(videoDevices[0].deviceId);
        }
    } catch (e) {
        debugLog("Error akses kamera: " + e);
        showStatus("Tidak dapat mengakses kamera", "danger");
    }
}

// Ganti kamera
async function switchCamera(deviceId) {
    try {
        if (currentStream) currentStream.getTracks().forEach(t => t.stop());
        const constraints = {
            video: { deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } }
        };
        currentStream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = currentStream;
        video.onloadedmetadata = function() {
            debugLog("Video loaded: " + video.videoWidth + "x" + video.videoHeight);
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            scanning = true;
            debugLog("Start scanning...");
            scanForQR();
        };
    } catch (e) {
        debugLog("Error switchCamera: " + e);
        showStatus("Gagal ganti kamera", "danger");
    }
}

// Scan QR
function scanForQR() {
    if (!scanning) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height);
        if (code) {
            debugLog("QR Detected: " + code.data);
            scanning = false;
            processQRCode(code.data);
            return;
        }
    }
    debugLog("Scanning...");
    requestAnimationFrame(scanForQR);
}

// Proses hasil QR
function processQRCode(qrData) {
    showStatus("QR Terdeteksi: " + qrData, "success");
    if (currentStream) currentStream.getTracks().forEach(t => t.stop());
    fetch("", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "nisn=" + encodeURIComponent(qrData)
    })
    .then(r => r.json())
    .then(data => {
        if (data.status === "success") showStudentData(data.data);
        else showStatus(data.message, "danger");
    })
    .catch(e => {
        debugLog("Fetch error: " + e);
        showStatus("Error mencari data siswa", "danger");
    });
}

// Tampilkan data siswa
function showStudentData(s) {
    document.getElementById("studentData").innerHTML = `
        <div class="data-item"><b>ID:</b> ${s.id_siswa}</div>
        <div class="data-item"><b>NIS:</b> ${s.nis}</div>
        <div class="data-item"><b>NISN:</b> ${s.nisn}</div>
        <div class="data-item"><b>Nama:</b> ${s.nama_siswa}</div>
    `;
    document.getElementById("resultCard").style.display = "block";
}

// Reset scan
function resetScan() {
    scanning = true;
    document.getElementById("resultCard").style.display = "none";
    const deviceId = document.getElementById("cameraSelect").value;
    if (deviceId) switchCamera(deviceId); else initCamera();
}

// Status message
function showStatus(msg, type) {
    const el = document.getElementById("statusMessage");
    el.textContent = msg;
    el.className = "alert alert-" + type;
    el.style.display = "block";
}
</script>
</body>
</html>
