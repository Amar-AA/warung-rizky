<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// --- LOGIKA MANAJEMEN PRODUK (TAMBAH / EDIT / HAPUS) ---
if (isset($_GET['hapus_produk'])) {
    $id_hapus = $_GET['hapus_produk'];
    mysqli_query($conn, "DELETE FROM produk WHERE id='$id_hapus'");
    header("Location: admin.php");
    exit();
}

if (isset($_POST['tambah_produk'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = $_POST['kategori'];
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    
    $foto = $_FILES['foto']['name'];
    $target = "images/" . basename($foto);
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        mysqli_query($conn, "INSERT INTO produk (nama_produk, kategori, harga, stok, foto) VALUES ('$nama', '$kategori', '$harga', '$stok', '$foto')");
    }
    header("Location: admin.php");
    exit();
}

if (isset($_POST['edit_produk'])) {
    $id_edit = $_POST['id_produk'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama_produk']);
    $kategori = $_POST['kategori'];
    $harga = intval($_POST['harga']);
    $stok = intval($_POST['stok']);
    
    if ($_FILES['foto']['name'] != "") {
        $foto = $_FILES['foto']['name'];
        move_uploaded_file($_FILES['foto']['tmp_name'], "images/".$foto);
        mysqli_query($conn, "UPDATE produk SET nama_produk='$nama', kategori='$kategori', harga='$harga', stok='$stok', foto='$foto' WHERE id='$id_edit'");
    } else {
        mysqli_query($conn, "UPDATE produk SET nama_produk='$nama', kategori='$kategori', harga='$harga', stok='$stok' WHERE id='$id_edit'");
    }
    header("Location: admin.php");
    exit();
}

// --- PEMBAGIAN QUERY TRANSAKSI BERDASARKAN FILTER WAKTU & STATUS ---
$query_aktif = mysqli_query($conn, "SELECT * FROM transaksi WHERE LOWER(status) != 'selesai' ORDER BY id_transaksi DESC");
$query_hari_ini = mysqli_query($conn, "SELECT * FROM transaksi WHERE DATE(waktu) = CURDATE() ORDER BY id_transaksi DESC");
$query_sebulan = mysqli_query($conn, "SELECT * FROM transaksi WHERE waktu >= NOW() - INTERVAL 1 MONTH ORDER BY id_transaksi DESC");

// Query list produk untuk tabel manajemen komoditas
$query_produk = mysqli_query($conn, "SELECT * FROM produk ORDER BY id DESC");

// Ambil data produk jika sedang mode Edit
$produk_edit = null;
if (isset($_GET['edit_id'])) {
    $id_ed = $_GET['edit_id'];
    $res_ed = mysqli_query($conn, "SELECT * FROM produk WHERE id='$id_ed'");
    $produk_edit = mysqli_fetch_assoc($res_ed);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Manajemen - Warung Pojok</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 25px; border-radius: 12px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
        h2, h3 { color: #ee4d2d; margin-top: 0; padding-bottom: 8px; }
        .nav-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; background: #ee4d2d; color: white; padding: 15px 20px; border-radius: 8px; }
        
        /* GAYA ACCORDION BUKA-TUTUP */
        details {
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        summary {
            background: #f8f9fa;
            padding: 16px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 8px;
            outline: none;
            list-style: none; /* Sembunyikan panah default */
            border-left: 5px solid #ee4d2d;
            transition: background 0.3s;
        }
        summary:hover { background: #e2e6ea; }
        summary::-webkit-details-marker { display: none; }
        summary::after { content: '▼'; float: right; color: #888; font-size: 14px; margin-top: 2px; }
        details[open] summary::after { content: '▲'; color: #ee4d2d; }
        details[open] summary { border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-bottom: 1px solid #ddd; background: #fff; }
        .tabel-wadah { padding: 15px; overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #555; font-weight: 600; }
        
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge-proses { background: #ffeeba; color: #856404; }
        .badge-selesai { background: #d4edda; color: #155724; }
        
        .btn { display: inline-block; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-weight: bold; font-size: 13px; cursor: pointer; border: none; }
        .btn-proses { background: #007bff; color: white; }
        .btn-edit { background: #ffc107; color: #212529; }
        .btn-delete { background: #dc3545; color: white; }
        .btn-tambah { background: #28a745; color: white; padding: 10px 20px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #e3e3e3; margin-bottom: 35px; }
        .form-group { display: flex; flex-direction: column; }
        .form-group label { font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input, .form-group select { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-header">
        <h2 style="margin:0; border:none; color:white; padding:0;">🏪 Dashboard Manajemen Warung Pojok</h2>
        <a href="logout.php" class="btn btn-delete" style="background:#fff; color:#ee4d2d;">🚪 Keluar Sistem</a>
    </div>

    <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">📊 Logistik & Pemantauan Transaksi</h3>

    <details open>
        <summary>📥 Orderan Masuk Aktif (Sedang Diproses)</summary>
        <div class="tabel-wadah">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Nama Pelanggan</th>
                        <th>Komoditas Item</th>
                        <th>Metode & Status</th>
                        <th>Tindakan Logistik</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($query_aktif) > 0) {
                        while($t = mysqli_fetch_assoc($query_aktif)) { 
                    ?>
                    <tr>
                        <td><b>#<?php echo $t['id_transaksi']; ?></b></td>
                        <td>👤 <?php echo $t['username']; ?></td>
                        <td>📦 <?php echo $t['nama_produk']; ?></td>
                        <td>
                            <small style="display:block; color:#777;"><?php echo $t['metode_pembayaran']; ?></small>
                            <span class="badge badge-proses"><?php echo !empty($t['status']) ? $t['status'] : 'Menunggu'; ?></span>
                        </td>
                        <td>
                            <a href="proses_pesanan.php?id=<?php echo $t['id_transaksi']; ?>" class="btn btn-proses">🚚 Proses & Alur Kurir</a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="5" style="text-align:center; color:#999; padding:20px;">Belum ada orderan masuk baru saat ini.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </details>

    <details>
        <summary>📅 Riwayat Transaksi Hari Ini</summary>
        <div class="tabel-wadah">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Waktu Masuk</th>
                        <th>Nama Pelanggan</th>
                        <th>Komoditas Item</th>
                        <th>Total Pendapatan</th>
                        <th>Status Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($query_hari_ini) > 0) {
                        while($t = mysqli_fetch_assoc($query_hari_ini)) { 
                    ?>
                    <tr>
                        <td><b>#<?php echo $t['id_transaksi']; ?></b></td>
                        <td>🕒 <?php echo date('H:i', strtotime($t['waktu'])); ?> WIB</td>
                        <td><?php echo $t['username']; ?></td>
                        <td><?php echo $t['nama_produk']; ?></td>
                        <td style="font-weight:bold; color:#28a745;">Rp <?php echo number_format($t['nominal'] + ($t['ongkir'] ?? 0)); ?></td>
                        <td>
                            <span class="badge <?php echo (strtolower($t['status']) == 'selesai') ? 'badge-selesai' : 'badge-proses'; ?>">
                                <?php echo $t['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">Belum ada rekam transaksi untuk hari ini.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </details>

    <details>
        <summary>🗓️ Riwayat Transaksi 1 Bulan Terakhir</summary>
        <div class="tabel-wadah">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Tanggal Transaksi</th>
                        <th>Nama Pelanggan</th>
                        <th>Komoditas Item</th>
                        <th>Total Nilai</th>
                        <th>Status Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if(mysqli_num_rows($query_sebulan) > 0) {
                        while($t = mysqli_fetch_assoc($query_sebulan)) { 
                    ?>
                    <tr>
                        <td><b>#<?php echo $t['id_transaksi']; ?></b></td>
                        <td>📆 <?php echo date('d/m/Y', strtotime($t['waktu'])); ?></td>
                        <td><?php echo $t['username']; ?></td>
                        <td><?php echo $t['nama_produk']; ?></td>
                        <td style="font-weight:bold; color:#28a745;">Rp <?php echo number_format($t['nominal'] + ($t['ongkir'] ?? 0)); ?></td>
                        <td>
                            <span class="badge <?php echo (strtolower($t['status']) == 'selesai') ? 'badge-selesai' : 'badge-proses'; ?>">
                                <?php echo $t['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr><td colspan="6" style="text-align:center; color:#999; padding:20px;">Tidak ditemukan riwayat transaksi selama 1 bulan terakhir.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </details>


    <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-top: 50px;">🛠️ <?php echo $produk_edit ? "Update Detail Komoditas Toko" : "Tambah Komoditas Baru Ke Toko"; ?></h3>
    <form method="POST" action="" enctype="multipart/form-data" class="form-grid">
        <?php if($produk_edit) { ?>
            <input type="hidden" name="id_produk" value="<?php echo $produk_edit['id']; ?>">
        <?php } ?>
        
        <div class="form-group">
            <label>Nama Produk / Komoditas:</label>
            <input type="text" name="nama_produk" required value="<?php echo $produk_edit ? $produk_edit['nama_produk'] : ''; ?>" placeholder="Contoh: Beras Premium 5kg">
        </div>
        <div class="form-group">
            <label>Kategori Produk:</label>
            <select name="kategori" required>
                <option value="Sembako" <?php echo ($produk_edit && $produk_edit['kategori'] == 'Sembako') ? 'selected' : ''; ?>>Sembako</option>
                <option value="Bumbu" <?php echo ($produk_edit && $produk_edit['kategori'] == 'Bumbu') ? 'selected' : ''; ?>>Bumbu Dapur</option>
                <option value="Jajanan" <?php echo ($produk_edit && $produk_edit['kategori'] == 'Jajanan') ? 'selected' : ''; ?>>Jajanan/Camilan</option>
                <option value="Digital" <?php echo ($produk_edit && $produk_edit['kategori'] == 'Digital') ? 'selected' : ''; ?>>Layanan Digital</option>
            </select>
        </div>
        <div class="form-group">
            <label>Harga Eceran (Rp):</label>
            <input type="number" name="harga" required value="<?php echo $produk_edit ? $produk_edit['harga'] : ''; ?>" placeholder="Contoh: 15000">
        </div>
        <div class="form-group">
            <label>Sisa Stok Tersedia:</label>
            <input type="number" name="stok" required value="<?php echo $produk_edit ? $produk_edit['stok'] : '10'; ?>">
        </div>
        <div class="form-group">
            <label>Foto Produk (<?php echo $produk_edit ? "Opsional" : "Wajib"; ?>):</label>
            <input type="file" name="foto" <?php echo $produk_edit ? "" : "required"; ?> accept="image/*">
        </div>
        <div class="form-group" style="justify-content: flex-end;">
            <?php if($produk_edit) { ?>
                <button type="submit" name="edit_produk" class="btn btn-tambah" style="background:#ffc107; color:#212529;">💾 Simpan Perubahan</button>
                <a href="admin.php" class="btn btn-delete" style="text-align:center; margin-top:5px; padding: 8px;">Batal</a>
            <?php } else { ?>
                <button type="submit" name="tambah_produk" class="btn btn-tambah">➕ Daftarkan Produk</button>
            <?php } ?>
        </div>
    </form>


    <h3 style="border-bottom: 2px solid #ccc; padding-bottom: 10px;">📦 Katalog Manajemen Komoditas Toko</h3>
    <table>
        <thead>
            <tr>
                <th width="10%">Foto</th>
                <th width="40%">Nama Komoditas</th>
                <th width="20%">Harga Eceran</th>
                <th width="15%">Sisa Stok</th>
                <th width="15%">Opsi Manajemen</th>
            </tr>
        </thead>
        <tbody>
            <?php while($p = mysqli_fetch_assoc($query_produk)) { ?>
            <tr>
                <td><img src="images/<?php echo $p['foto']; ?>" width="45" style="border-radius: 4px; object-fit: cover;"></td>
                <td><b><?php echo $p['nama_produk']; ?></b> <br><small style="color:#777;"><?php echo $p['kategori']; ?></small></td>
                <td>Rp <?php echo number_format($p['harga']); ?></td>
                <td><?php echo ($p['stok'] <= 0) ? "<span style='color:red; font-weight:bold;'>Habis Total</span>" : $p['stok'] . " unit"; ?></td>
                <td>
                    <a href="admin.php?edit_id=<?php echo $p['id']; ?>" class="btn btn-edit" style="padding: 4px 8px; font-size: 11px;">✏️ Edit</a>
                    <a href="admin.php?hapus_produk=<?php echo $p['id']; ?>" class="btn btn-delete" onclick="return confirm('Hapus produk ini dari toko?')" style="padding: 4px 8px; font-size: 11px;">🗑️ Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>