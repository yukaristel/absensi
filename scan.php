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

        .scan-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0 !important;
            text-align: center;
            padding: 20px;
            border: none;
        }

        .camera-dropdown {
            margin-bottom: 20px;
        }

        .camera-select {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .camera-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .video-container {
            position: relative;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        #video {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }

        .scan-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid #fff;
            border-radius: 15px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3);
            animation: scan-pulse 2s infinite;
        }

        .scan-overlay::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid #667eea;
            border-radius: 15px;
            animation: scan-rotate 3s linear infinite;
        }

        .scan-text {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
        }

        @keyframes scan-pulse {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }

        @keyframes scan-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .result-card {
            background: linear-gradient(135deg, #48CAE4 0%, #0096C7 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            display: none;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .data-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .data-label {
            font-weight: 600;
            opacity: 0.9;
        }

        .data-value {
            font-weight: 500;
        }

        .btn-reset {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 15px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
            color: white;
        }

        .loading {
            text-align: center;
            padding: 20px;
        }

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .status-message {
            text-align: center;
            padding: 15px;
            border-radius: 10px;
            margin: 10px 0;
        }

        .status-success {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .status-error {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.2);
        }

        @media (max-width: 576px) {
            .scan-container {
                padding: 15px;
            }
            
            #video {
                height: 250px;
            }
            
            .scan-overlay {
                width: 150px;
                height: 150px;
            }
        }
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
                                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">qr_code_scanner</i>
                                Scan QR Code Siswa
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Dropdown Pilihan Kamera -->
                            <div class="camera-dropdown">
                                <label for="cameraSelect" class="form-label fw-bold">Pilih Kamera:</label>
                                <select id="cameraSelect" class="form-select camera-select">
                                    <option value="">Memuat kamera...</option>
                                </select>
                            </div>

                            <!-- Video Container -->
                            <div class="video-container">
                                <video id="video" autoplay muted playsinline></video>
                                <div class="scan-overlay">
                                    <div class="scan-text">Arahkan QR Code di sini</div>
                                </div>
                                <canvas id="canvas" style="display: none;"></canvas>
                            </div>

                            <!-- Status Message -->
                            <div id="statusMessage" class="status-message" style="display: none;"></div>

                            <!-- Loading -->
                            <div id="loading" class="loading" style="display: none;">
                                <div class="loading-spinner"></div>
                                <p>Mencari data siswa...</p>
                            </div>

                            <!-- Hasil Scan -->
                            <div id="resultCard" class="result-card">
                                <h5 class="mb-3">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">person</i>
                                    Data Siswa
                                </h5>
                                <div id="studentData"></div>
                                <button id="resetBtn" class="btn btn-reset w-100">
                                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">refresh</i>
                                    Scan Lagi
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let video, canvas, context, currentStream;
        let scanning = true;
        
        document.addEventListener('DOMContentLoaded', function() {
            video = document.getElementById('video');
            canvas = document.getElementById('canvas');
            context = canvas.getContext('2d');
            
            initCamera();
            
            // Event listener untuk reset button
            document.getElementById('resetBtn').addEventListener('click', resetScan);
            
            // Event listener untuk perubahan kamera
            document.getElementById('cameraSelect').addEventListener('change', function() {
                if (this.value) {
                    switchCamera(this.value);
                }
            });
        });

        // Inisialisasi kamera dan daftar perangkat
        async function initCamera() {
            try {
                // Minta izin akses kamera
                await navigator.mediaDevices.getUserMedia({ video: true });
                
                // Dapatkan daftar perangkat kamera
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(device => device.kind === 'videoinput');
                
                const select = document.getElementById('cameraSelect');
                select.innerHTML = '';
                
                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Kamera ${index + 1}`;
                    select.appendChild(option);
                });
                
                // Gunakan kamera pertama sebagai default
                if (videoDevices.length > 0) {
                    select.value = videoDevices[0].deviceId;
                    switchCamera(videoDevices[0].deviceId);
                }
                
            } catch (error) {
                console.error('Error accessing camera:', error);
                showPermissionError();
            }
        }

        // Switch kamera
        async function switchCamera(deviceId) {
            try {
                // Stop stream sebelumnya
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }
                
                const constraints = {
                    video: {
                        deviceId: { exact: deviceId },
                        width: { ideal: 1280 },
                        height: { ideal: 720 }
                    }
                };
                
                currentStream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = currentStream;
                
                video.onloadedmetadata = function() {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    scanning = true;
                    scanForQR();
                };
                
            } catch (error) {
                console.error('Error switching camera:', error);
                showStatus('Gagal mengganti kamera.', 'error');
            }
        }

        // Scan QR Code
        function scanForQR() {
            if (!scanning || video.readyState !== video.HAVE_ENOUGH_DATA) {
                requestAnimationFrame(scanForQR);
                return;
            }
            
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = jsQR(imageData.data, imageData.width, imageData.height);
            
            if (code) {
                scanning = false;
                processQRCode(code.data);
            } else {
                requestAnimationFrame(scanForQR);
            }
        }

        // Proses hasil QR Code
        function processQRCode(qrData) {
            showStatus('QR Code terdeteksi: ' + qrData, 'success');
            showLoading(true);
            
            // Stop kamera
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
            
            // Kirim AJAX request untuk mencari data siswa
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'nisn=' + encodeURIComponent(qrData)
            })
            .then(response => response.json())
            .then(data => {
                showLoading(false);
                
                if (data.status === 'success') {
                    showStudentData(data.data);
                } else {
                    showStatus(data.message, 'error');
                    setTimeout(resetScan, 3000); // Auto reset setelah 3 detik
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showLoading(false);
                showStatus('Terjadi kesalahan saat mencari data siswa.', 'error');
                setTimeout(resetScan, 3000);
            });
        }

        // Tampilkan data siswa
        function showStudentData(student) {
            const studentDataDiv = document.getElementById('studentData');
            studentDataDiv.innerHTML = `
                <div class="data-item">
                    <span class="data-label">ID Siswa:</span>
                    <span class="data-value">${student.id_siswa}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">NIS:</span>
                    <span class="data-value">${student.nis}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">NISN:</span>
                    <span class="data-value">${student.nisn}</span>
                </div>
                <div class="data-item">
                    <span class="data-label">Nama Siswa:</span>
                    <span class="data-value">${student.nama_siswa}</span>
                </div>
            `;
            
            document.getElementById('resultCard').style.display = 'block';
            hideStatus();
        }

        // Reset scan
        function resetScan() {
            scanning = true;
            document.getElementById('resultCard').style.display = 'none';
            hideStatus();
            
            const deviceId = document.getElementById('cameraSelect').value;
            if (deviceId) {
                switchCamera(deviceId);
            } else {
                initCamera();
            }
        }

        // Show loading
        function showLoading(show) {
            document.getElementById('loading').style.display = show ? 'block' : 'none';
        }

        // Show status message
        function showStatus(message, type) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.textContent = message;
            statusDiv.className = `status-message status-${type}`;
            statusDiv.style.display = 'block';
        }

        // Hide status message
        function hideStatus() {
            document.getElementById('statusMessage').style.display = 'none';
        }

        // Show permission error with clickable link
        function showPermissionError() {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.innerHTML = 'Tidak dapat mengakses kamera. <a href="javascript:void(0)" onclick="requestCameraPermission()" style="color: #dc3545; text-decoration: underline; font-weight: bold;">klik disini untuk perizinan.</a>';
            statusDiv.className = 'status-message status-error';
            statusDiv.style.display = 'block';
        }

        // Request camera permission manually
        async function requestCameraPermission() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                stream.getTracks().forEach(track => track.stop()); // Stop immediately
                hideStatus();
                initCamera(); // Retry initialization
            } catch (error) {
                console.error('Permission denied:', error);
                showStatus('Akses kamera ditolak. Silakan refresh halaman dan berikan izin akses kamera.', 'error');
            }
        }

        // Cleanup saat halaman ditutup
        window.addEventListener('beforeunload', function() {
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
</body>
</html>