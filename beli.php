<?php
session_start();
include 'koneksi.php';

if ($_SESSION['role'] != 'customer') { die("Hanya customer yang bisa membeli."); }

$id_produk = $_GET['id'];
$sql = "SELECT * FROM produk WHERE id=$id_produk";
$produk = mysqli_fetch_assoc(mysqli_query($conn, $sql));

if (isset($_POST['submit'])) {
    $username = $_SESSION['username'];
    $nama_produk = $produk['nama_produk'];
    $nomor_tujuan = $_POST['nomor_tujuan'];
    $metode_pembayaran = $_POST['metode'];
    
    // LOGIKA PINTAR: Membedakan nominal berdasarkan kategori produk
    if ($produk['kategori'] == 'Digital') {
        $nominal = $_POST['nominal']; // Mengambil dari pilihan pulsa
        $harga_final = $nominal;
    } else {
        $jumlah_beli = intval($_POST['jumlah_beli']); // Jumlah pcs sembako
        $nominal = $produk['harga']; // Harga satuan sembako
        $harga_final = $produk['harga'] * $jumlah_beli; // Total harga barang
    }

    // Ambil data peta & ongkir otomatis
    $alamat_kirim = isset($_POST['alamat_kirim']) ? mysqli_real_escape_string($conn, $_POST['alamat_kirim']) : '';
    $koordinat_customer = isset($_POST['koordinat_customer']) ? $_POST['koordinat_customer'] : '';
    $jarak_km = isset($_POST['jarak_km']) ? floatval($_POST['jarak_km']) : 0.00;
    $ongkir = isset($_POST['ongkir_hidden']) ? intval($_POST['ongkir_hidden']) : 0;

    $bukti_transfer = "";
    if ($metode_pembayaran == 'Transfer') {
        $nama_file = time() . "_" . $_FILES['bukti']['name'];
        $tmp_file = $_FILES['bukti']['tmp_name'];
        move_uploaded_file($tmp_file, "uploads/" . $nama_file);
        $bukti_transfer = $nama_file;
    }

    // Simpan data transaksi ke database
    $insert = "INSERT INTO transaksi (username, nama_produk, nominal, harga, jarak_km, ongkir, nomor_tujuan, alamat_kirim, koordinat_customer, metode_pembayaran, bukti_transfer, status) 
               VALUES ('$username', '$nama_produk', '$nominal', '$harga_final', '$jarak_km', '$ongkir', '$nomor_tujuan', '$alamat_kirim', '$koordinat_customer', '$metode_pembayaran', '$bukti_transfer', 'Menunggu Konfirmasi')";
    
    if(mysqli_query($conn, $insert)) {
        // Kurangi stok berdasarkan jumlah pembelian
        $pengurang = ($produk['kategori'] == 'Digital') ? 1 : $jumlah_beli;
        mysqli_query($conn, "UPDATE produk SET stok = stok - $pengurang WHERE id = '$id_produk'");
        echo "<script>alert('Pesanan berhasil dibuat!'); window.location='pesanan.php';</script>";
        exit();
    } else {
        echo "<script>alert('Terjadi kesalahan database: " . mysqli_error($conn) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Beli - Warung Rizky</title>
    <style>
        body { font-family: sans-serif; padding: 20px; background: url('images/background.png') no-repeat center center fixed; background-size: cover; }
        .card { background: rgba(255,255,255,0.9); padding: 20px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 2px 5px rgba(0,0,0,0.1); backdrop-filter: blur(5px); }
        input, select, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { background: #ee4d2d; color: white; padding: 10px; border: none; width: 100%; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .info-transfer { background: #eef; padding: 15px; border-radius: 5px; margin-bottom: 15px; display: none; border-left: 5px solid blue;}
        .kirim-div { display: none; background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 5px solid #ffc107; margin-bottom: 10px; }
        .ongkir-box { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-top: 5px; font-weight: bold; font-size: 14px; text-align: center; border: 1px solid #c3e6cb; }
        .harga-asli-box { background: #e2e3e5; color: #383d41; padding: 10px; border-radius: 5px; font-weight: bold; margin-bottom: 10px; }
    </style>
    <script>
        const LOKASI_WARUNG_LAT = -7.796347; 
        const LOKASI_WARUNG_LON = 110.301389;

        function cekMetode() {
            var metode = document.getElementById("metode").value;
            var uploadDiv = document.getElementById("upload_bukti");
            var infoDiv = document.getElementById("info_transfer");
            var kirimDiv = document.getElementById("div_pengiriman");

            if (metode === "Cash" || metode === "COD") {
                kirimDiv.style.display = "block";
                document.getElementById("alamat_kirim").required = true;
                ambilLokasiOtomatis();
            } else {
                kirimDiv.style.display = "none";
                document.getElementById("alamat_kirim").required = false;
            }

            if (metode === "Transfer") {
                uploadDiv.style.display = "block";
                infoDiv.style.display = "block";
                document.getElementById("file_bukti").required = true;
            } else {
                uploadDiv.style.display = "none";
                infoDiv.style.display = "none";
                document.getElementById("file_bukti").required = false;
            }
        }

        function ambilLokasiOtomatis() {
            var infoBox = document.getElementById("info_ongkir");
            infoBox.style.display = "block";
            infoBox.innerHTML = "⏳ Sedang membaca radar GPS peta Anda...";

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var custLat = position.coords.latitude;
                    var custLon = position.coords.longitude;
                    document.getElementById("koordinat_customer").value = custLat + "," + custLon;

                    var R = 6371; 
                    var dLat = (custLat - LOKASI_WARUNG_LAT) * Math.PI / 180;
                    var dLon = (custLon - LOKASI_WARUNG_LON) * Math.PI / 180;
                    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                            Math.cos(LOKASI_WARUNG_LAT * Math.PI / 180) * Math.cos(custLat * Math.PI / 180) *
                            Math.sin(dLon/2) * Math.sin(dLon/2);
                    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    var jarak = R * c; 
                    
                    document.getElementById("jarak_km").value = jarak.toFixed(2);

                    // --- LOGIKA ONGKIR BARU ---
                    var ongkir = 0;
                    if (jarak <= 1.0) { 
                        ongkir = 3000; 
                    } else { 
                        ongkir = 3000 + (Math.ceil(jarak - 1) * 2000); 
                    }

                    document.getElementById("ongkir_hidden").value = ongkir;
                    infoBox.innerHTML = "🎯 Terdeteksi! Jarak Anda ke Warung: " + jarak.toFixed(2) + " KM <br> 💰 Ongkir Otomatis: Rp " + ongkir.toLocaleString('id-ID');

                }, function() {
                    infoBox.style.background = "#f8d7da";
                    infoBox.style.color = "#721c24";
                    infoBox.innerHTML = "⚠️ Akses lokasi ditolak browser/HP. Menggunakan Ongkir Flat Rp 3.000";
                    document.getElementById("ongkir_hidden").value = 3000;
                });
            }
        }
    </script>
</head>
<body>
    <div class="card">
        <h2>Pesan <?php echo $produk['nama_produk']; ?></h2>
        
        <?php if ($produk['kategori'] != 'Digital') { ?>
            <div class="harga-asli-box">
                💵 Harga Barang: Rp <?php echo number_format($produk['harga']); ?> / pcs <br>
                📦 Stok Tersedia: <?php echo $produk['stok']; ?> pcs
            </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            
            <label>Nomor Tujuan / Catatan Tambahan:</label>
            <input type="text" name="nomor_tujuan" placeholder="<?php echo ($produk['kategori'] == 'Digital') ? 'Contoh: 081234567890' : 'Contoh: Rumah pagar hitam'; ?>" required>

            <?php if ($produk['kategori'] == 'Digital') { ?>
                <label>Pilih Nominal Pulsa / Token:</label>
                <select name="nominal" required>
                    <option value="">-- Pilih Nominal --</option>
                    <option value="10000">Rp 10.000</option>
                    <option value="20000">Rp 20.000</option>
                    <option value="50000">Rp 50.000</option>
                    <option value="100000">Rp 100.000</option>
                </select>
            <?php } else { ?>
                <label>Jumlah yang Ingin Dibeli (Pcs/Kg):</label>
                <input type="number" name="jumlah_beli" min="1" max="<?php echo $produk['stok']; ?>" value="1" required>
            <?php } ?>

            <label>Metode Pembayaran:</label>
            <select name="metode" id="metode" onchange="cekMetode()" required>
                <option value="">-- Pilih Metode --</option>
                <option value="Cash">Cash (Diantar ke Alamat)</option>
                <option value="Transfer">Transfer (DANA / Bank)</option>
                <option value="COD">COD (Bayar Saat Barang Sampai)</option>
            </select>

            <div id="div_pengiriman" class="kirim-div">
                <label><b>Alamat Lengkap Pengiriman:</b></label>
                <textarea name="alamat_kirim" id="alamat_kirim" placeholder="Tulis nama jalan, nomor rumah, atau patokan objek terdekat..."></textarea>
                
                <div id="info_ongkir" class="ongkir-box" style="display:none;">Menghitung koordinat...</div>
                
                <input type="hidden" name="koordinat_customer" id="koordinat_customer">
                <input type="hidden" name="jarak_km" id="jarak_km">
                <input type="hidden" name="ongkir_hidden" id="ongkir_hidden">
            </div>

            <div id="info_transfer" class="info-transfer">
                <p>Silakan transfer ke nomor DANA: <b>0812-3456-7890</b><br>Kemudian upload screenshot bukti transfer di bawah ini.</p>
            </div>

            <div id="upload_bukti" style="display: none;">
                <label>Upload Bukti Transfer:</label>
                <input type="file" name="bukti" id="file_bukti" accept="image/*">
            </div>

            <button type="submit" name="submit" class="btn">KIRIM PESANAN</button>
            <a href="menu.php" style="display:block; text-align:center; margin-top:10px; color: #555; text-decoration:none; font-size:14px;">Kembali ke Dashboard</a>
        </form>
    </div>
</body>
</html>