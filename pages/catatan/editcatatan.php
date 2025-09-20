<?php
include('koneksi.php');

$id = isset($_GET['id_catatan']) ? mysqli_real_escape_string($coneksi, $_GET['id_catatan']) : '';

// Ambil data jurnal berdasarkan ID
$query = "SELECT * FROM catatan WHERE id_catatan = '$id'";
$result = mysqli_query($coneksi, $query);
$data = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_catatan = mysqli_real_escape_string($coneksi, $_POST['id_catatan']);
    $id_pembimbing = mysqli_real_escape_string($coneksi, $_POST['id_pembimbing']);
    $catatan = mysqli_real_escape_string($coneksi, $_POST['catatan']);
    $id_jurnal = mysqli_real_escape_string($coneksi, $_POST['id_jurnal']);

    $update_query = "UPDATE catatan SET judul='$judul', deskripsi='$deskripsi', tanggal='$tanggal' WHERE id='$id'";
    if (mysqli_query($coneksi, $update_query)) {
        header("Location: catatan.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($coneksi);
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Catatan</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        /* Penyesuaian posisi */
        body {
            padding-left: 270px;
            transition: padding-left 0.3s;
            background-color: #f8f9fa;
        }

        .main-container {
            margin-top: 20px;
            margin-right: 20px;
            margin-left: 0;
            width: auto;
            max-width: none;
        }

        /* Style asli */
        .container-custom {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 991px) {
            body {
                padding-left: 0;
            }

            .main-container {
                margin-right: 15px;
                margin-left: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container container-custom" style="margin-top:20px">
        <h2>Edit Catatan</h2>
        <hr>
        <form action="pages/catatan/proses_editcatatan.php" method="post">
            <input type="hidden" name="id_catatan" value="<?php echo $data['id_catatan']; ?>">
            <div class="form-group">
                <label>Pembimbing</label>
                <input type="text" name="id_pembimbing" class="form-control" value="<?php echo htmlspecialchars($data['id_pembimbing']); ?>" required>
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <textarea name="catatan" class="form-control" rows="4" required><?php echo htmlspecialchars($data['catatan']); ?></textarea>
            </div>
            <div class="form-group">
                <label>ID Jurnal</label>
                <input type="text" name="id_jurnal" class="form-control" value="<?php echo htmlspecialchars($data['id_jurnal']); ?>" required>
            </div>
            <div class="form-group data">
                <label class="col-sm-2 col-form-label">&nbsp;</label>
                <div class="col-sm-10">
                    <br>
                    <input type="submit" name="submit" class="btn btn-primary" value="SIMPAN">
                    <a href="index.php?page=catatan" class="btn btn-warning">KEMBALI</a>
                </div>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body