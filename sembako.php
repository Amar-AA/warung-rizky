<?php 
session_start();
include 'koneksi.php'; 

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu Sembako - Warung Rizky</title>
    <style>
        body { 
            font-family: sans-serif; 
            background: url('images/background.png') no-repeat center center fixed; 
            background-size: cover; 
            margin: 0; 
            padding: 20px; 
        }
        
        .container { max-width: 800px; margin: auto; }
        
        .section { 
            background: rgba(255, 255, 255, 0.40); 
            backdrop-filter: blur(5px); 
            padding: 20px; 
            border-radius: 12px; 
            margin-bottom: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
        }

        h3 { 
            border-left: 5px solid #ee4d2d; 
            padding-left: 10px; 
            color: #111; 
            text-shadow: 1px 1px 3px rgba(255,255,255,0.9); 
        }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; }
        
        .item { 
            background: rgba(255, 255, 255, 0.65); 
            border: 1px solid rgba(255,255,255,0.6); 
            padding: 15px; 
            text-align: center; 
            border-radius: 8px; 
            transition: 0.3s; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: space-between; 
            height: 100%; 
        }
        .item:hover { 
            border-color: #ee4d2d; 
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            background: rgba(255, 255, 255, 0.85); 
        }

        .btn-beli { background: #ee4d2d; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 13px; display: inline-block; margin-top: 5px; font-weight: bold; width: 80%; }
        
        .btn-kembali {
            background: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }

        /* --- Style Keterangan Admin --- */
        .keterangan-admin {
            background: #e9ecef;
            padding: 8px;
            border-radius: 5px;
            font-size: 11px;
            color: #333;
            width: 90%;
            text-align: left;
            margin-top: 5px;
            border-left: 3px solid #17a2b8;
        }
        .keterangan-admin p {
            margin: 3px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:window.close();" class="btn-kembali">⬅ Tutup Tab Sembako</a>

        <div class="section">
            <h3>🛒 Daftar Sembako</h3>
            <div class="grid">
                <?php
                // Tampilkan produk sembako terbaru di urutan paling atas
                $sql = "SELECT * FROM produk WHERE kategori='Sembako' ORDER BY id DESC";
                $res = mysqli_query($conn, $sql);
                
                if(mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)){
                        // Cek apakah ada file fotonya di folder images
                        $gambar = (!empty($row['foto'])) ? $row['foto'] : 'default.png';
                        
                        echo "<div class='item'>
                                <div style='width: 100%; height: 120px; overflow: hidden; border-radius: 8px; margin-bottom: 10px;'>
                                    <img src='images/$gambar' style='width: 100%; height: 100%; object-fit: cover;'>
                                </div>
                                <p style='margin: 5px 0; color: #111; font-size: 16px;'><strong>{$row['nama_produk']}</strong></p>
                                <p style='color: #ee4d2d; font-weight: bold; margin-bottom: 5px; font-size: 15px;'>Rp ".number_format($row['harga'])."</p>";
                                
                        // --- LOGIKA STOK BARANG ---
                        if($row['stok'] > 0) {
                            echo "<p style='color: green; font-size: 12px; font-weight: bold; margin-bottom: 10px;'>Tersedia: {$row['stok']}</p>";
                            
                            // Jika ada stok, tampilkan tombol sesuai role
                            if($_SESSION['role'] == 'customer') {
                                echo "<a href='beli.php?id={$row['id']}' class='btn-beli'>Beli Sekarang</a>";
                            } else if ($_SESSION['role'] == 'admin') {
                                echo "<div class='keterangan-admin'>
                                        <p>📝 <b>Keterangan:</b></p>
                                        <p>ID Produk: #{$row['id']}</p>
                                        <p>Status: <span style='color: green; font-weight: bold;'>Aktif</span></p>
                                      </div>";
                            }
                        } else {
                            echo "<p style='color: red; font-size: 12px; font-weight: bold; margin-bottom: 10px;'>STOK HABIS</p>";
                            
                            // Jika stok habis, tombol beli dimatikan (abu-abu)
                            if($_SESSION['role'] == 'customer') {
                                echo "<button class='btn-beli' style='background:#ccc; cursor:not-allowed;' disabled>Habis</button>";
                            } else if ($_SESSION['role'] == 'admin') {
                                echo "<div class='keterangan-admin'>
                                        <p>📝 <b>Keterangan:</b></p>
                                        <p>ID Produk: #{$row['id']}</p>
                                        <p>Status: <span style='color: red; font-weight: bold;'>Habis</span></p>
                                      </div>";
                            }
                        }
                        
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color: #111; font-weight: bold;'>Belum ada produk sembako.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>