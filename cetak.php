<?php
include 'koneksi.php';

// Mengambil ID dari URL
$id = $_GET['id'];

// Query mencari data transaksi berdasarkan ID
$sql = "SELECT * FROM transaksi WHERE id_transaksi = '$id'";
$query = mysqli_query($conn, $sql);
$data = mysqli_fetch_array($query);

// Jika datanya tidak ada, hentikan proses
if (!$data) {
    echo "Data pesanan tidak ditemukan!";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Struk - #<?php echo $data['id_transaksi']; ?></title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 300px; 
            margin: 20px auto; 
            border: 1px dashed #000; 
            padding: 15px; 
            background: #fff;
        }
        h2 { text-align: center; margin-bottom: 5px; font-size: 20px; }
        .subtitle { text-align: center; font-size: 14px; margin-top: 0; margin-bottom: 15px;}
        .garis { border-bottom: 1px dashed #000; margin: 10px 0; }
        .info { font-size: 14px; margin: 5px 0; }
        .total { font-weight: bold; font-size: 16px; margin-top: 10px; }
        .footer { text-align: center; font-size: 12px; margin-top: 20px; }

        /* Sembunyikan elemen ini saat diprint jika ada tombol tambahan */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <h2>Warung Rizky</h2>
    <p class="subtitle">Struk Pembelian</p>

    <div class="garis"></div>
    
    <p class="info">No. Order : #<?php echo $data['id_transaksi']; ?></p>
    <p class="info">Pelanggan : <?php echo $data['username']; ?></p>
    <p class="info">Waktu     : <?php echo date('d/m/Y H:i'); ?></p>
    
    <div class="garis"></div>

    <p class="info"> Produk: <br> <b><?php echo $data['nama_produk']; ?></b></p>
    <p class="info"> Nominal: <?php echo $data['nominal']; ?></p>
    <p class="info"> No. Tujuan: <?php echo $data['nomor_tujuan']; ?></p>
    
    <div class="garis"></div>

    <p class="total">TOTAL BAYAR: Rp <?php echo number_format($data['nominal']); ?></p>
    <p class="info">Metode: <?php echo $data['metode_pembayaran']; ?></p>

    <div class="footer">
        <p>Terima Kasih Telah Belanja!<br>Simpan struk ini sebagai bukti.</p>
    </div>

    <script>
        // Jalankan perintah print saat halaman selesai dimuat
        window.onload = function() {
            window.print();
        };

        // Deteksi ketika dialog print ditutup (baik Print atau Cancel)
        window.onafterprint = function() {
            // Arahkan kembali ke halaman data pesanan masuk (admin.php)
            window.location.href = 'admin.php';
        };
    </script>

</body>
</html>