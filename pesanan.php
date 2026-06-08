<?php
session_start();
include 'koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username_aktif = $_SESSION['username'];

// Ambil data transaksi KHUSUS untuk user yang sedang login
$sql = "SELECT * FROM transaksi WHERE username = '$username_aktif' ORDER BY id_transaksi DESC";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Saya - Warung Pojok</title>
    <style>
        body { font-family: sans-serif; background: #fafafa; padding: 20px; }
        .container { max-width: 800px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; vertical-align: top; }
        th { background-color: #ee4d2d; color: white; }
        .badge-proses { color: #ff9800; font-weight: bold; }
        .badge-selesai { color: #28a745; font-weight: bold; }
        .btn-kembali { display: inline-block; background: #6c757d; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px; margin-bottom: 10px; font-weight: bold; }
        
        /* Gaya tambahan untuk fitur baru (tidak merusak gaya lama) */
        .tracking-box { background: #e8f4fd; padding: 6px; border-radius: 4px; border-left: 3px solid #007bff; margin-top: 5px; font-size: 12px; color: #004085; }
        .btn-bukti { background: #dc3545; color: white; padding: 4px 8px; border-radius: 4px; text-decoration: none; font-size: 11px; font-weight: bold; display: inline-block; margin-top: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="menu.php" class="btn-kembali">&laquo; Kembali ke Menu</a>
        <h2>Daftar Pesanan Saya</h2>
        
        <table>
            <tr>
                <th>No. Order</th>
                <th>Produk</th>
                <th>Nominal</th>
                <th>Total Bayar</th>
                <th>Status Pesanan</th>
            </tr>
            <?php 
            if(mysqli_num_rows($query) > 0) {
                while($row = mysqli_fetch_array($query)) { 
            ?>
            <tr>
                <td>#<?php echo $row['id_transaksi']; ?></td>
                <td><?php echo $row['nama_produk']; ?></td>
                <td><?php echo $row['nominal']; ?></td>
                
                <td>Rp <?php echo number_format($row['total']); ?></td>
                
                <td>
                    <?php if($row['status'] == 'Selesai') { ?>
                        <span class="badge-selesai">✔ Selesai</span>
                        
                        <?php if(!empty($row['bukti_transfer'])): ?>
                            <br><a href="uploads/<?php echo $row['bukti_transfer']; ?>" target="_blank" class="btn-bukti">📁 Lihat Foto Bukti Sampai</a>
                        <?php endif; ?>

                    <?php } else { ?>
                        <span class="badge-proses">⏳ Sedang Diproses</span>
                        
                        <?php if($row['status'] == 'Dipacking') { ?>
                            <div class="tracking-box">📦 Barang sedang dikemas oleh penjual.</div>
                        <?php } else if($row['status'] == 'Diantar') { ?>
                            <div class="tracking-box">🚚 Kurir sedang mengantar pesanan ke rumah Anda.</div>
                            
                            <?php if(!empty($row['lokasi_admin'])): ?>
                                <a href="https://www.google.com/maps/search/?api=1&query=<?php echo $row['lokasi_admin']; ?>" target="_blank" style="font-size:11px; color:blue; font-weight:bold; display:inline-block; margin-top:4px;">📍 Lacak Posisi Kurir di Peta</a>
                            <?php endif; ?>
                        <?php } ?>

                    <?php } ?>
                </td>
            </tr>
            <?php 
                } 
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Anda belum memiliki pesanan.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>