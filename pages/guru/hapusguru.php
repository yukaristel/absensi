<?php
include('koneksi.php');
if (isset($_GET['id_guru'])) {
    $id_guru = $_GET['id_guru'];
    $hapus = mysqli_query($coneksi, "DELETE FROM guru WHERE id_guru='$id_guru'");
    if ($hapus) {
        $_SESSION['flash_hapus'] = 'sukses';
    }
    echo '<script>window.location.href = "index.php?page=guru";</script>';
    exit();
} else {
    echo '<script>window.location.href = "index.php?page=guru";</script>';
    exit();
}
?>