<?php
require_once __DIR__ . "/koneksi.php";

// tombol untuk Tambah Foto Galeri
echo "<a href='form_upload.php'><button>Tambah Foto</button></a>";

$sql = "SELECT * FROM galeri ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse:collapse' cellpadding='5'>";
    echo "<caption><strong>My Photo Gallery</strong></caption>";
    echo "<tr>
            <th>No</th>
            <th>Nama File</th>
            <th>Tgl Unggah</th>
            <th>Pratinjau</th>
          </tr>";

    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td>" . $row["id"] . "</td>
                <td>" . htmlspecialchars($row["nama_file"]) . "</td>
                <td>" . $row["waktu_upload"] . "</td>
                <td><img src='" . htmlspecialchars($row["lokasi_file"]) . "' width='100' height='auto'></td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results - Data not found!";
}

mysqli_close($conn);
?>