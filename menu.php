<?php 
session_start();
include 'koneksi.php'; 

// Cek apakah user sudah login, jika belum kembalikan ke index
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Menu Pembelian - Warung Rizky</title>
    <style>
        body { 
            font-family: sans-serif; 
            background: url('images/background.png') no-repeat center center fixed; 
            background-size: cover; 
            margin: 0; 
            padding: 20px; 
        }
        
        .container { max-width: 800px; margin: auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: rgba(0,0,0,0.5); padding: 15px; border-radius: 10px; }
        
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
        .btn-pesanan { background: #007bff; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        .btn-admin { background: #28a745; color: white; text-decoration: none; padding: 10px 15px; border-radius: 8px; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); }
        
        .keterangan-dana { color: #d9534f; font-size: 12px; font-weight: bold; margin-bottom: 10px; }

        .ikon-produk {
            width: 70px;      
            height: 70px;     
            margin-bottom: 15px; 
            object-fit: contain; 
        }

        .btn-sembako {
            background: rgba(255, 255, 255, 0.70);
            border: 2px solid rgba(255,255,255,0.8);
            padding: 15px;
            width: 100%;
            text-align: center;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            color: #111;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            display: block;
            text-decoration: none;
            box-sizing: border-box;
        }
        .btn-sembako:hover {
            background: rgba(255, 255, 255, 0.90);
            border-color: #ee4d2d;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1 style="color: white; margin: 0;">Warung Rizky</h1>
                <p style="color: #ddd; margin: 5px 0 0 0; font-size: 14px;">Halo, <?php echo $_SESSION['username']; ?>! (<?php echo ucfirst($_SESSION['role']); ?>)</p>
            </div>
            
            <div>
                <?php if($_SESSION['role'] == 'customer') { ?>
                    <a href="pesanan.php" class="btn-pesanan">🛒 Pesanan Saya</a>
                <?php } else if ($_SESSION['role'] == 'admin') { ?>
                    <a href="admin.php" class="btn-admin">⚙️ Kelola Warung (Panel)</a>
                <?php } ?>
                <a href="index.php" style="color: #ffcccc; margin-left: 15px; text-decoration: none; font-size: 14px;">Logout</a>
            </div>
        </div>

        <div class="section">
            <h3>Produk Digital</h3>
            <div class="grid">
                <?php
                $ikon_produk = [
                    'Token Listrik'  => 'ikon-pln.png',
                    'Top Up DANA'    => 'ikon-dana.png',
                    'Pulsa Reguler'  => 'ikon-pulsa.png',
                    'Paket Data'     => 'ikon-data.png'
                ];

                $sql = "SELECT * FROM produk WHERE kategori='Digital'";
                $res = mysqli_query($conn, $sql);
                while($row = mysqli_fetch_assoc($res)){
                    
                    if ($row['nama_produk'] == 'Top Up DANA') {
                        $teks_bawah = "<p class='keterangan-dana'>+ Biaya Admin Rp 5.000</p>";
                    } else {
                        $teks_bawah = "<p class='keterangan-dana'>+ Biaya Admin Rp 2.000</p>";
                    }

                    $ikon_file = isset($ikon_produk[$row['nama_produk']]) ? $ikon_produk[$row['nama_produk']] : 'ikon-default.png';

                    echo "<div class='item'>
                            <img src='images/{$ikon_file}' alt='{$row['nama_produk']}' class='ikon-produk'>
                            <p style='margin: 5px 0; color: #111;'><strong>{$row['nama_produk']}</strong></p>
                            {$teks_bawah}";
                            
                    // Cegah Admin menekan tombol beli (agar tidak error)
                    if($_SESSION['role'] == 'customer') {
                        echo "<a href='beli.php?id={$row['id']}' class='btn-beli'>Beli Sekarang</a>";
                    } else {
                        echo "<span class='btn-beli' style='background:#6c757d; cursor:not-allowed;'>Hanya Customer</span>";
                    }

                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div class="section" style="padding-bottom: 25px;">
            <a href="sembako.php" target="_blank" class="btn-sembako">
                🛒 Tampilkan Menu Sembako
            </a>
        </div>
    </div>
</body>
</html>