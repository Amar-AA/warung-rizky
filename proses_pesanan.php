<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin.php");
    exit();
}
$id_tr = $_GET['id'];

// --- LOGIKA UPDATE STATUS BERTAHAP ---
if (isset($_POST['update_status_alur'])) {
    $aksi = $_POST['aksi_status'];

    // 1. Ambil daftar struktur asli ENUM langsung dari database Anda
    $result_enum = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status'");
    $row_enum = mysqli_fetch_assoc($result_enum);
    preg_match_all("/'(.*?)'/", $row_enum['Type'], $matches);
    $list_opsi_db = $matches[1]; // Berisi semua opsi status yang terdaftar di phpMyAdmin

    $status_final = '';

    // 2. Pencocokan otomatis cerdas (mengatasi masalah huruf besar/kecil)
    if ($aksi == 'packing') {
        if (in_array('Dipacking', $list_opsi_db)) $status_final = 'Dipacking';
        elseif (in_array('dipacking', $list_opsi_db)) $status_final = 'dipacking';
        elseif (in_array('Dikemas', $list_opsi_db)) $status_final = 'Dikemas';
        elseif (in_array('dikemas', $list_opsi_db)) $status_final = 'dikemas';
    } elseif ($aksi == 'antar') {
        if (in_array('Diantar', $list_opsi_db)) $status_final = 'Diantar';
        elseif (in_array('diantar', $list_opsi_db)) $status_final = 'diantar';
        elseif (in_array('Dikirim', $list_opsi_db)) $status_final = 'Dikirim';
        elseif (in_array('dikirim', $list_opsi_db)) $status_final = 'dikirim';
    } elseif ($aksi == 'sampai') {
        if (in_array('Sampai', $list_opsi_db)) $status_final = 'Sampai';
        elseif (in_array('sampai', $list_opsi_db)) $status_final = 'sampai';
    }

    // 3. Jika tidak ada kata yang cocok dengan ENUM database, tampilkan panduan eror
    if (empty($status_final)) {
        $teks_opsi = implode(", ", $list_opsi_db);
        echo "<script>
            alert('❌ GAGAL UPDATE STATUS!\\n\\nKata tidak dikenali oleh database Anda.\\nPilihan ENUM yang terdaftar di phpMyAdmin Anda saat ini adalah:\\n[ $teks_opsi ]\\n\\nSilakan sesuaikan struktur ENUM di phpMyAdmin Anda agar mengandung kata Dipacking, Diantar, Sampai.');
            window.location='proses_pesanan.php?id=$id_tr';
        </script>";
        exit();
    }

    // 4. Jalankan query dengan kata yang sudah tervalidasi aman bagi database
    mysqli_query($conn, "UPDATE transaksi SET status='$status_final' WHERE id_transaksi='$id_tr'");
    echo "<script>alert('Status Berhasil Diubah menjadi: $status_final'); window.location='proses_pesanan.php?id=$id_tr';</script>";
    exit();
}

// --- LOGIKA UPDATE KOORDINAT LOKASI KURIR ---
if (isset($_POST['update_lokasi_admin'])) {
    $koordinat_admin = mysqli_real_escape_string($conn, $_POST['koordinat_admin']);
    mysqli_query($conn, "UPDATE transaksi SET lokasi_admin='$koordinat_admin' WHERE id_transaksi='$id_tr'");
    header("Location: proses_pesanan.php?id=" . $id_tr);
    exit();
}

// --- LOGIKA FINAL: UPLOAD FOTO BUKTI SELESAI ---
if (isset($_POST['proses_selesai_pesanan'])) {
    $foto_bukti = $_FILES['foto_bukti']['name'];
    if ($foto_bukti != "") {
        $nama_foto_bukti = time() . "_bukti_" . $foto_bukti;
        if (move_uploaded_file($_FILES['foto_bukti']['tmp_name'], "uploads/" . $nama_foto_bukti)) {
            // Cari tahu apakah kata 'Selesai' menggunakan huruf kapital atau kecil di DB
            $res_selesai = mysqli_query($conn, "SHOW COLUMNS FROM transaksi LIKE 'status'");
            $r_selesai = mysqli_fetch_assoc($res_selesai);
            $status_selesai = (strpos($r_selesai['Type'], "'Selesai'") !== false) ? 'Selesai' : 'selesai';

            mysqli_query($conn, "UPDATE transaksi SET status='$status_selesai', bukti_transfer='$nama_foto_bukti' WHERE id_transaksi='$id_tr'");
            echo "<script>alert('Pesanan selesai dan bukti terkirim!'); window.location='admin.php';</script>";
            exit();
        } else {
            echo "<script>alert('Gagal mengunggah foto bukti.');</script>";
        }
    } else {
        echo "<script>alert('Wajib memilih foto bukti pengiriman!');</script>";
    }
}

$query = mysqli_query($conn, "SELECT * FROM transaksi WHERE id_transaksi = '$id_tr'");
$tr = mysqli_fetch_assoc($query);

if (!$tr) {
    echo "Pesanan tidak ditemukan.";
    exit();
}

// Ubah ke huruf kecil semua khusus untuk pengecekan logika tampilan HTML agar kebal eror kapital
$status_cek = isset($tr['status']) ? strtolower(trim($tr['status'])) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Driver Shopee Flow — Pesanan #<?php echo $tr['id_transaksi']; ?></title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; padding: 15px; margin: 0; }
        .driver-container { max-width: 500px; margin: auto; background: white; padding: 20px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .shopee-orange { background: #ee4d2d; color: white; padding: 15px; border-radius: 12px; margin-bottom: 20px; text-align: center; }
        .timeline { display: flex; justify-content: space-between; margin: 20px 0; padding: 0; list-style: none; font-size: 11px; font-weight: bold; }
        .timeline li { flex: 1; text-align: center; color: #ccc; border-top: 3px solid #ccc; padding-top: 8px; position: relative; }
        .timeline li.active { color: #ee4d2d; border-top-color: #ee4d2d; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px dashed #eee; font-size: 14px; }
        .info-label { color: #666; }
        .info-value { font-weight: bold; text-align: right; }
        .btn-action { width: 100%; padding: 14px; border-radius: 10px; font-weight: bold; font-size: 15px; border: none; cursor: pointer; margin-top: 15px; display: block; text-align: center; text-decoration: none; box-sizing: border-box; }
        .btn-orange { background: #ee4d2d; color: white; }
        .btn-gray { background: #6c757d; color: white; }
        .btn-blue { background: #007bff; color: white; }
    </style>
    <script>
        function dapatkanLokasiAdmin() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById("kod_admin").value = position.coords.latitude + "," + position.coords.longitude;
                    alert("🎯 Posisi GPS Kurir Berhasil Diperbarui!");
                });
            }
        }
    </script>
</head>
<body>

<div class="driver-container">
    <a href="admin.php" style="text-decoration: none; color: #ee4d2d; font-weight: bold; font-size: 14px;">← Kembali ke Dashboard</a>
    
    <div class="shopee-orange">
        <h3 style="margin: 0; font-size: 16px;">ORDER PENGANTARAN</h3>
        <h2 style="margin: 5px 0 0 0; font-size: 22px;">#<?php echo $tr['id_transaksi']; ?></h2>
    </div>

    <!-- Progress Timeline Bar -->
    <ul class="timeline">
        <li class="active">Masuk</li>
        <li class="<?php echo in_array($status_cek, ['dipacking', 'dikemas', 'proses', 'diantar', 'dikirim', 'sampai', 'selesai']) ? 'active' : ''; ?>">Kemas</li>
        <li class="<?php echo in_array($status_cek, ['diantar', 'dikirim', 'sampai', 'selesai']) ? 'active' : ''; ?>">Kirim</li>
        <li class="<?php echo in_array($status_cek, ['sampai', 'selesai']) ? 'active' : ''; ?>">Sampai</li>
        <li class="<?php echo ($status_cek == 'selesai') ? 'active' : ''; ?>">Selesai</li>
    </ul>

    <div class="info-row"><div class="info-label">Nama Pelanggan</div><div class="info-value">👤 <?php echo $tr['username']; ?></div></div>
    <div class="info-row"><div class="info-label">Item Pesanan</div><div class="info-value">📦 <?php echo $tr['nama_produk']; ?></div></div>
    <div class="info-row"><div class="info-label">Alamat Kirim</div><div class="info-value" style="max-width: 60%;">📍 <?php echo !empty($tr['alamat_kirim']) ? $tr['alamat_kirim'] : 'Ambil di Tempat'; ?></div></div>
    <div class="info-row">
        <div class="info-label">Total Tagihan Tunai</div>
        <div class="info-value" style="color: #ee4d2d; font-size: 18px;">Rp <?php echo number_format($tr['nominal'] + ($tr['ongkir'] ?? 0)); ?></div>
    </div>
    <div class="info-row"><div class="info-label">Metode Bayar</div><div class="info-value">💵 <?php echo $tr['metode_pembayaran']; ?></div></div>

    <?php if(!empty($tr['koordinat_customer'])): ?>
        <a href="https://www.google.com/maps/dir/?api=1&destination=<?php echo $tr['koordinat_customer']; ?>" target="_blank" class="btn-action btn-blue" style="font-size: 13px; padding: 10px 0; text-align: center;">🌐 Buka Panduan Navigasi</a>
    <?php endif; ?>

    <h3 style="margin-top: 25px; font-size: 14px; color: #555;">Tindakan Pengantaran Driver:</h3>

    <?php if(empty($status_cek) || in_array($status_cek, ['menunggu konfirmasi', 'menunggu'])) { ?>
        <form method="POST" action="">
            <input type="hidden" name="aksi_status" value="packing">
            <button type="submit" name="update_status_alur" class="btn-action btn-orange">📦 Mulai Mengemas Barang</button>
        </form>
    
    <?php } elseif(in_array($status_cek, ['dipacking', 'dikemas', 'proses'])) { ?>
        <form method="POST" action="">
            <input type="hidden" name="aksi_status" value="antar">
            <button type="submit" name="update_status_alur" class="btn-action btn-orange" style="background: #17a2b8;">🚚 Konfirmasi Kemasan & Mulai Jalan</button>
        </form>
    
    <?php } elseif(in_array($status_cek, ['diantar', 'dikirim'])) { ?>
        <form method="POST" action="" style="background: #fff3cd; padding: 12px; border: 1px solid #ffeeba; border-radius: 8px; margin-bottom: 10px;">
            <input type="hidden" name="koordinat_admin" id="kod_admin">
            <button type="button" onclick="dapatkanLokasiAdmin()" class="btn-action btn-gray" style="margin-top: 0; font-size: 12px; padding: 8px 0;">🎯 Share/Kunci GPS Saya Saat Ini</button>
            <button type="submit" name="update_lokasi_admin" class="btn-action btn-blue" style="font-size: 12px; padding: 8px 0; margin-top: 5px;">Kirim Pembaruan Lokasi Ke Customer</button>
        </form>

        <form method="POST" action="">
            <input type="hidden" name="aksi_status" value="sampai">
            <button type="submit" name="update_status_alur" class="btn-action btn-gray" style="background:#4b5157;">🏁 Saya Sudah Sampai Di Lokasi Rumah</button>
        </form>
    
    <?php } elseif($status_cek == 'sampai') { ?>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 10px; border: 2px dashed #ccc;">
            <form method="POST" action="" enctype="multipart/form-data">
                <label style="font-size:13px; font-weight:bold; display:block; margin-bottom:8px; color: #ee4d2d;">📸 Ambil Foto Bukti Serah Terima Barang:</label>
                <input type="file" name="foto_bukti" accept="image/*" capture="environment" required style="width: 100%; margin-bottom: 10px;">
                <button type="submit" name="proses_selesai_pesanan" class="btn-action btn-orange">✅ Kirim Bukti & Selesaikan Pesanan</button>
            </form>
        </div>
    
    <?php } elseif($status_cek == 'selesai') { ?>
        <div style="text-align: center; color: #28a745; font-weight: bold; padding: 15px; background: #e8f5e9; border-radius: 10px; font-size: 15px;">
            🎉 Tugas Selesai! Selamat Bekerja Kembali.
        </div>
    <?php } ?>
</div>

</body>
</html>