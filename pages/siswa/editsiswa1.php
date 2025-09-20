<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include('koneksi.php');
// Hapus data session setelah digunakan
unset($_SESSION['username_error']);
unset($_SESSION['password_error']);
unset($_SESSION['success']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Siswa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        h2 {
            margin-bottom: 20px;
            color: #007bff;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .form-control {
            border: none;
            border-bottom: 2px solid #007bff;
            border-radius: 0;
            box-shadow: none;
        }

        .form-control:focus {
            border-color: #0056b3;
            box-shadow: none;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .hapusSiswa {
            color: white;
            background-color: #344767;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .hapusSiswa:hover {
            background-color: #5a6268;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }

        .form-row {
            margin-bottom: 15px;
        }

        .error-message {
            color: red;
            font-size: 0.875rem;
            margin-top: 5px;
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
<h2 class="text-left">Data Siswa</h2>
    <div class="main-container container-custom">
        <?php
        if (isset($_GET['id_siswa'])) {
            $id_siswa = $_GET['id_siswa'];
            $select = mysqli_query($coneksi, "SELECT * FROM siswa WHERE id_siswa='$id_siswa'");
            if (mysqli_num_rows($select) == 0) {
                // Jika user akses langsung atau klik back ke halaman ini setelah hapus, redirect ke siswa.php
                echo '<script>window.location.replace("index.php?page=siswa");</script>';
                exit();
            } else {
                $data = mysqli_fetch_assoc($select);
            }
        } else {
            // Redirect jika tidak ada id_siswa
            echo '<script>window.location.replace("index.php?page=siswa");</script>';
            exit();
        }
        
        // Notifikasi update dari proses_editsiswa.php
        if (isset($_GET['pesan'])) {
            echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
            echo '<script>document.addEventListener("DOMContentLoaded",function(){';
            if ($_GET['pesan'] == 'sukses') {
                echo 'Swal.fire({icon:"success",title:"Sukses!",text:"Data siswa berhasil diupdate",position:"top",showConfirmButton:false,timer:2000,toast:true});';
            } elseif ($_GET['pesan'] == 'gagal') {
                $err = isset($_GET['error']) ? htmlspecialchars(urldecode($_GET['error']), ENT_QUOTES) : 'Terjadi kesalahan';
                echo 'Swal.fire({icon:"error",title:"Gagal!",text:"' . $err . '",position:"top",showConfirmButton:false,timer:3000,toast:true});';
            } elseif ($_GET['pesan'] == 'duplikat') {
                echo 'Swal.fire({icon:"warning",title:"Peringatan!",text:"ID siswa atau Username sudah terdaftar",position:"top",showConfirmButton:false,timer:3000,toast:true});';
            }
            echo '});</script>';
        }
        ?>

        <form action="pages/siswa/proses_editsiswa.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_siswa" value="<?php echo $id_siswa; ?>">

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>NIS</label>
                    <input type="text" name="nis" class="form-control"
                        value="<?php echo htmlspecialchars($data['nis']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>NISN</label>
                    <input type="text" name="nisn" class="form-control"
                        value="<?php echo htmlspecialchars($data['nisn']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Nama Siswa</label>
                    <input type="text" name="nama_siswa" class="form-control"
                        value="<?php echo htmlspecialchars($data['nama_siswa']); ?>" required>
                </div>
                <div class="form-group col-md-3">
                    <label>Program Keahlian</label>
                    <select name="pro_keahlian" class="form-control" required>
                        <option value="<?php echo htmlspecialchars($data['pro_keahlian']); ?>" selected>
                            <?php echo htmlspecialchars($data['pro_keahlian']); ?>
                        </option>
                        <option value="Multimedia">Multimedia</option>
                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                        <option value="Perkantoran">Perkantoran</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Sekolah</label>
                    <select name="id_sekolah" class="form-control" required>
                        <?php
                        $data_sekolah = mysqli_query($coneksi, "SELECT * FROM sekolah");
                        while ($row = mysqli_fetch_array($data_sekolah)) {
                        ?>
                            <option value="<?php echo htmlspecialchars($row['id_sekolah']); ?>" <?php echo ($row['id_sekolah'] == $data['id_sekolah']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama_sekolah']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Perusahaan</label>
                    <select name="id_perusahaan" class="form-control" required>
                        <?php
                        $data_perusahaan = mysqli_query($coneksi, "SELECT * FROM perusahaan");
                        while ($row = mysqli_fetch_array($data_perusahaan)) {
                        ?>
                            <option value="<?php echo htmlspecialchars($row['id_perusahaan']); ?>" <?php echo ($row['id_perusahaan'] == $data['id_perusahaan']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama_perusahaan']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Pembimbing</label>
                    <select name="id_pembimbing" class="form-control" required>
                        <?php
                        $data_pembimbing = mysqli_query($coneksi, "SELECT * FROM pembimbing");
                        while ($row = mysqli_fetch_array($data_pembimbing)) {
                        ?>
                            <option value="<?php echo htmlspecialchars($row['id_pembimbing']); ?>" <?php echo ($row['id_pembimbing'] == $data['id_pembimbing']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama_pembimbing']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Guru</label>
                    <select name="id_guru" class="form-control" required>
                        <?php
                        $data_guru = mysqli_query($coneksi, "SELECT * FROM guru");
                        while ($row = mysqli_fetch_array($data_guru)) {
                        ?>
                            <option value="<?php echo htmlspecialchars($row['id_guru']); ?>" <?php echo ($row['id_guru'] == $data['id_guru']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['nama_guru']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <!-- Tombol Hapus di kiri -->
                    <button type="button" class="btn btn-secondary"
                        id="btnHapus" data-id="<?php echo $data['id_siswa']; ?>">
                        HAPUS
                    </button>

                    <!-- Tombol Kembali dan Simpan di kanan (tapi berdampingan) -->
                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="index.php?page=siswa" class="btn btn-warning mr-2">KEMBALI</a>
                        <input type="submit" name="submit" class="btn btn-primary" value="UPDATE">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // SweetAlert untuk konfirmasi hapus
        document.addEventListener('DOMContentLoaded', function() {
            const deleteBtn = document.getElementById('btnHapus');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    Swal.fire({
                        title: "Apakah Anda yakin?",
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#6c757d",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Ya, hapus!",
                        cancelButtonText: "Batal"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `index.php?page=hapussiswa&id_siswa=${id}`;
                        }
                    });
                });
            }
        });
    </script>

    <!-- Script lainnya tetap sama -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>