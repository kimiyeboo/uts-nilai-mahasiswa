<?php
require_once __DIR__ . "/koneksi.php";

// Proses simpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int) $_POST['id'];
    $nim         = $_POST['nim'];
    $namalengkap = $_POST['namalengkap'];
    $nilaiakhir  = (float) $_POST['nilaiakhir'];

    // Hitung ulang nilai huruf dan predikat
    if ($nilaiakhir <= 34.50) {
        $nilaihuruf = 'E'; $predikat = 'Tidak Lulus';
    } elseif ($nilaiakhir <= 50.50) {
        $nilaihuruf = 'D'; $predikat = 'Kurang Memuaskan';
    } elseif ($nilaiakhir <= 60.50) {
        $nilaihuruf = 'C'; $predikat = 'Cukup Memuaskan';
    } elseif ($nilaiakhir <= 80.50) {
        $nilaihuruf = 'B'; $predikat = 'Memuaskan';
    } else {
        $nilaihuruf = 'A'; $predikat = 'Sangat Memuaskan';
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE nilaimahasiswa
         SET nim = ?, namalengkap = ?, nilaiakhir = ?, nilaihuruf = ?, predikat = ?
         WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssdssi", $nim, $namalengkap, $nilaiakhir, $nilaihuruf, $predikat, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header('Location: viewdata.php');
    exit;
}

// Ambil data lama untuk diisi ke form
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: viewdata.php');
    exit;
}

$id = (int) $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM nilaimahasiswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    header('Location: viewdata.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Nilai Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5" style="max-width: 500px;">
    <div class="card">
        <div class="card-header">Edit Data Nilai</div>
        <div class="card-body">
            <form action="editnilai.php" method="POST">
                <input type="hidden" name="id" value="<?= $data['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">NIM</label>
                    <input type="text" name="nim" class="form-control"
                           value="<?= htmlspecialchars($data['nim']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="namalengkap" class="form-control"
                           value="<?= htmlspecialchars($data['namalengkap']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nilai Akhir</label>
                    <input type="number" step="0.01" min="0" max="100" name="nilaiakhir"
                           class="form-control" value="<?= $data['nilaiakhir'] ?>" required>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="viewdata.php" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>