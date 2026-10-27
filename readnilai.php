<?php
require_once __DIR__ . "/koneksi.php"; 

//membuat tombol untuk tambah data
echo "<a href='formnilai.php'><button>Tambah Data</button></a><br><br>";
$sql = "SELECT * from nilaimahasiswa order by nilaiakhir asc";    
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
  // output data of each row
    echo "<table border=1 cellpadding=5px";
    echo "<caption><strong>Data Nilai Mahasiswa</strong></caption>";
    echo "<tr>";
    echo "<th>NIM</th>";
    echo "<th>Nama Lengkap</th>";
    echo "<th>Nilai Akhir</th>";
    echo "<th>Nilai Huruf</th>";
    echo "<th>Predikat</th>";
    echo "<th>Tanggal Dibuat</th>";
    echo "<th>Tanggal Diubah</th>";
    echo "<th>Aksi</th>";
    echo "</tr>";
  while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" .$row['nim']. "</td>";
    echo "<td>" .$row['namalengkap']. "</td>";
    echo "<td>" .$row['nilaiakhir']. "</td>";
    echo "<td>" .$row['nilaihuruf']. "</td>";
    echo "<td>" .$row['predikat']. "</td>";
    echo "<td>" .$row['entry_date']. "</td>";
    echo "<td>" .$row['modified_at']. "</td>";
    echo "<td><a href='#'><button>Edit Data</button></a> | <button>Hapus Data</button></td>";

    echo "</tr>";
  }
  echo "</table>";
} else {
  echo "0 results - Data Not Found!";
}

mysqli_close($conn);

?>