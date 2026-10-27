<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Nilai Mahasiswa</title>
</head>
<body>
    <h3>Form Nilai Mahasiswa</h3>
    <form action="inputnilai.php" method="post">
        <label for="NIM">NIM:</label><br>
        <input type="text" name="nim" required><br>
        <label for="namalengkap">Nama Lengkap:</label><br>
        <input type="text" name="namalengkap" required><br>
        <label for="nilaiakhir">Nilai Akhir:</label><br>
        <input type="text" name="nilaiakhir" required><br>
        <input type="submit" value="Simpan"><br>
    </form>
</body>
</html>
