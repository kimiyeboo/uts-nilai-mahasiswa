<?php
require_once __DIR__ . "/koneksi.php";
// sql to create table
$sql = "CREATE TABLE NilaiMahasiswa (
nim INT(10) UNSIGNED PRIMARY KEY,
namalengkap VARCHAR(70) NOT NULL,
nilaiakhir decimal(5,2) NOT NULL,
nilaihuruf VARCHAR(5) NOT NULL,
entry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
  echo "Table created successfully";
} else {
  echo "Error creating table: " . mysqli_error($conn);
}

mysqli_close($conn);
?>