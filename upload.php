<?php
// Inisialisasi status upload dan daftar pesan error
$uploadOk = 1;
$pesan = [];

// Direktori tujuan penyimpanan file
$target_dir = "galeri/";

// Semua proses hanya jalan kalau form disubmit
if (isset($_POST["submit"])) {

    // Pastikan ada file yang dikirim dan tidak ada error bawaan PHP
    if (!isset($_FILES["foto"]) || $_FILES["foto"]["error"] !== UPLOAD_ERR_OK) {
        $pesan[] = "Tidak ada file yang dipilih atau terjadi kesalahan saat upload.";
        $uploadOk = 0;
    } else {
        $namaFile      = basename($_FILES["foto"]["name"]);
        $ukuranFile    = $_FILES["foto"]["size"];
        $fileSementara = $_FILES["foto"]["tmp_name"];
        $file_path     = $target_dir . $namaFile;

        // Ekstensi file dijadikan huruf kecil
        $imageFileType = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        // Checkpoint 1 (Autentikasi): apakah file benar-benar gambar
        $cek = getimagesize($fileSementara);
        if ($cek === false) {
            $pesan[] = "File bukan gambar.";
            $uploadOk = 0;
        }

        // Checkpoint 2 (Duplikasi): apakah nama file sudah ada di folder tujuan
        if (file_exists($file_path)) {
            $pesan[] = "Maaf, file " . htmlspecialchars($namaFile) . " sudah ada.";
            $uploadOk = 0;
        }

        // Checkpoint 3 (Ukuran file): maksimal 1 MB
        if ($ukuranFile > 1000000) {
            $pesan[] = "Maaf, ukuran file melebihi 1 MB.";
            $uploadOk = 0;
        }

        // Checkpoint 4 (Format file): hanya JPG, JPEG, PNG, GIF
        if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            $pesan[] = "Maaf, hanya file JPG, JPEG, PNG & GIF yang diijinkan.";
            $uploadOk = 0;
        }

        // Jika semua pengecekan lolos
        if ($uploadOk == 1) {
            // Pindahkan file ke folder tujuan
            if (move_uploaded_file($fileSementara, $file_path)) {

                // Koneksi ke database db5d
                require_once __DIR__ . "/koneksi.php";

                // Simpan nama file dan lokasinya ke tabel galeri
                // (id dan waktu_upload diisi otomatis oleh database)
                $stmt = $conn->prepare(
                    "INSERT INTO galeri (nama_file, lokasi_file) VALUES (?, ?)"
                );
                $stmt->bind_param("ss", $namaFile, $file_path);

                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: bacagaleri.php");
                    exit;
                } else {
                    $pesan[] = "Gagal menyimpan ke database: " . $stmt->error;
                    $stmt->close();
                }
            } else {
                $pesan[] = "Maaf, terjadi kesalahan saat memindahkan file.";
            }
        }
    }

    // Tampilkan pesan error (hanya sampai sini kalau ada yang gagal)
    foreach ($pesan as $p) {
        echo $p . "<br>";
    }
    echo '<br><a href="javascript:history.back()">Kembali</a>';
}
?>