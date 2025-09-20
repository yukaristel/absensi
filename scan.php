<?php
// jangan ada spasi/echo sebelum <?php biar header aman
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Scan Barcode / QR Siswa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- style sederhana -->
  <style>
    body { font-family: Arial, sans-serif; margin:20px; text-align:center; }
    #reader { width:300px; margin:20px auto; }
    #result { margin-top:20px; padding:10px; border:1px solid #ccc; border-radius:6px; }
    .error { color:red; }
    .success { color:green; }
  </style>
  <!-- html5-qrcode -->
  <script src="https://unpkg.com/html5-qrcode"></script>
</head>
<body>
  <h2>📷 Scan Barcode / QR Siswa</h2>
  <p>Izin kamera akan diminta jika belum diberikan oleh browser.<br>
     Arahkan kode siswa ke area kamera.</p>

  <!-- Area kamera -->
  <div id="reader"></div>

  <!-- Hasil scan -->
  <div id="result">Belum ada hasil scan.</div>

<script>
function onScanSuccess(decodedText, decodedResult) {
  // tampilkan hasil ke user
  document.getElementById("result").innerHTML = 
    "Kode terbaca: <b>" + decodedText + "</b><br>Memuat data...";

  // kirim ke get_siswa.php
  fetch("get_siswa.php?id=" + encodeURIComponent(decodedText))
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById("result").innerHTML = 
          "<div class='success'>✅ Data Siswa ditemukan:</div>" +
          "<pre>" + JSON.stringify(data.data, null, 2) + "</pre>";
      } else {
        document.getElementById("result").innerHTML = 
          "<div class='error'>❌ Error: " + data.error + "</div>";
      }
    })
    .catch(err => {
      document.getElementById("result").innerHTML = 
        "<div class='error'>Gagal memuat data: " + err + "</div>";
    });
}

// start scanner
let html5QrcodeScanner = new Html5QrcodeScanner(
  "reader", 
  { fps: 10, qrbox: 250 },
  /* verbose= */ false
);
html5QrcodeScanner.render(onScanSuccess);
</script>
</body>
</html>
