<?php
if (session_status() == PHP_SESSION_NONE) {
session_start();
}

$host = '103.112.245.8';
$user = 'sinkrone_absensi';
$pass = 'sinkrone_absensi';
$db   = 'sinkrone_absensi';

$coneksi = new mysqli($host, $user, $pass, $db);

if($coneksi->connect_error){
    die("Koneksi gagal: " . $coneksi->connect_error);
}

date_default_timezone_set('Asia/Jakarta');
 ?>