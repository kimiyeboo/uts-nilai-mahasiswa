<?php
require_once __DIR__ . "/koneksi.php";

$nim = $_POST['nim'];
$namalengkap = $_POST['namalengkap'];
$nilaiakhir = $_POST['nilaiakhir'];

if (0.00 <= $nilaiakhir && $nilaiakhir <= 34.50) {
    $nilaihuruf = 'E';
    $predikat = 'Tidak Lulus';
} elseif ($nilaiakhir > 34.50 && $nilaiakhir <= 50.50) {
    $nilaihuruf = 'D';
    $predikat = "Kurang Memuaskan";
} elseif ($nilaiakhir > 50.50 && $nilaiakhir <= 60.50) {
    $nilaihuruf = 'C';
    $predikat = 'Cukup Memuaskan';
} elseif ($nilaiakhir > 60.50 && $nilaiakhir <= 80.50) {
    $nilaihuruf = 'B';
    $predikat = 'Memuaskan';
} elseif ($nilaiakhir > 80.50 && $nilaiakhir <= 100.00) {
    $nilaihuruf = 'A';
    $predikat = 'Sangat Memuaskan';
} 

// ====== PROSES UPLOAD FOTO ======
$foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $boleh = ['jpg', 'jpeg', 'png'];

    if (in_array($ext, $boleh) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $foto = $nim . '_' . time() . '.' . $ext;   // contoh: 3123244444_1793115927.jpg
        move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/uploads/' . $foto);
    }
}

$sql = "INSERT INTO nilaimahasiswa (nim, namalengkap, nilaiakhir, nilaihuruf, predikat, foto)
        VALUES ('$nim', '$namalengkap', '$nilaiakhir', '$nilaihuruf', '$predikat', " . ($foto ? "'$foto'" : "NULL") . ")";

if (mysqli_query($conn, $sql)) {
    header('Location: viewdata.php');
    exit;
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

// mysqli_close($conn);
header ('Location: viewdata.php');
?>