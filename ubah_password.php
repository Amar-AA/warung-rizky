<?php
session_start();
include 'koneksi.php'; 

$error = "";
$success = "";
$show_step_2 = false;

// LANGKAH 1: Request Kode Verifikasi (OTP)
if (isset($_POST['request_kode'])) {
    $identitas = mysqli_real_escape_string($conn, $_POST['identitas']);

    // KUNCI KEAMANAN: Hanya mencari user yang rolenya 'customer'
    $query = mysqli_query($conn, "SELECT * FROM users WHERE (email='$identitas' OR no_telepon='$identitas') AND role='customer'");
    
    if (mysqli_num_rows($query) > 0) {
        $user_data = mysqli_fetch_assoc($query);
        
        // Membuat kode acak 6 digit sebagai OTP
        $kode_otp = rand(100000, 999999);
        
        // Simpan identitas & kode ke session sementara
        $_SESSION['reset_username'] = $user_data['username'];
        $_SESSION['system_otp'] = $kode_otp;
        
        // ---- PROSES PENGIRIMAN WHATSAPP ASLI (FONNTE) ----
        $target_pelanggan = $user_data['no_telepon'];
        $pesan_wa  = "Halo *" . $user_data['username'] . "*,\n\nBerikut adalah kode referensi untuk mengubah password Anda di Warung Rizky: *" . $kode_otp . "*\n\n_Mohon jangan bagikan kode ini kepada siapapun demi keamanan akun Anda._";

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.fonnte.com/send',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => array(
            'target' => $target_pelanggan,
            'message' => $pesan_wa,
          ),
          CURLOPT_HTTPHEADER => array(
            'Authorization: 2WV7YHSjAZEFLt4rCPqa' // Token Fonnte Anda
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        // ---- AKHIR PROSES PENGIRIMAN ----

        $success = "⚡ Kode verifikasi telah dikirim langsung melalui WhatsApp ke nomor Anda (<b>" . $target_pelanggan . "</b>). Silakan periksa ponsel Anda!";
        $show_step_2 = true;
    } else {
        // Jika ada yang memasukkan Email/No HP Admin, sistem akan berbohong & menolak dengan pesan ini demi keamanan
        $error = "Akses ditolak! Email atau Nomor Telepon tidak ditemukan atau Anda tidak memiliki akses untuk fitur ini.";
    }
}

// LANGKAH 2: Verifikasi Kode & Ganti Password Baru
if (isset($_POST['update_password'])) {
    $input_otp = $_POST['input_otp'];
    $password_baru = mysqli_real_escape_string($conn, $_POST['password_baru']);
    $username_target = $_SESSION['reset_username'];

    if ($input_otp == $_SESSION['system_otp']) {
        // Update password baru ke database
        $update = mysqli_query($conn, "UPDATE users SET password='$password_baru' WHERE username='$username_target' AND role='customer'");
        if ($update) {
            $_SESSION['salah_login_customer'] = 0; // Reset hitungan salah login customer kembali ke 0
            unset($_SESSION['system_otp']);
            unset($_SESSION['reset_username']);
            echo "<script>alert('Password Customer berhasil diperbarui! Silakan login kembali dengan password baru Anda.'); window.location='index.php';</script>";
            exit();
        } else {
            $error = "Gagal memperbarui database: " . mysqli_error($conn);
        }
    } else {
        $error = "Kode Verifikasi (OTP) yang Anda masukkan salah!";
        $show_step_2 = true; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Warung Rizky - Atur Ulang Password Customer</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: url('images/background.png') no-repeat center center fixed; background-size: cover; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 360px; text-align: center; }
        h2 { color: #ee4d2d; margin-bottom: 0.5rem; }
        p { font-size: 13px; color: #555; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #ee4d2d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #d73d17; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; background: #fdd; padding: 10px; border-radius: 5px; text-align: left; border: 1px solid #fcc; }
        .success { color: #155724; font-size: 14px; margin-bottom: 15px; background: #d4edda; padding: 12px; border-radius: 5px; text-align: left; border: 1px solid #c3e6cb; line-height: 1.5; }
        .link-kembali { display: block; margin-top: 20px; font-size: 14px; color: #6c757d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Atur Ulang Password</h2>
        <p style="color: #ee4d2d; font-weight: bold;">(Khusus Akun Customer)</p>

        <?php if($error != "") echo "<div class='error'>$error</div>"; ?>
        <?php if($success != "") echo "<div class='success'>$success</div>"; ?>

        <!-- FORM TAHAP 1: Minta OTP -->
        <?php if (!$show_step_2) { ?>
            <form action="" method="POST">
                <input type="text" name="identitas" placeholder="Masukkan Email / No HP Customer" required>
                <button type="submit" name="request_kode">MINTA KODE VERIFIKASI (WA)</button>
            </form>
        <?php } ?>

        <!-- FORM TAHAP 2: Input OTP & Password Baru -->
        <?php if ($show_step_2) { ?>
            <form action="" method="POST">
                <input type="number" name="input_otp" placeholder="Masukkan 6 Digit Kode OTP" required>
                <input type="password" name="password_baru" placeholder="Ketik Password Baru Anda" required>
                <button type="submit" name="update_password" style="background:#28a745;">KONFIRMASI PASSWORD BARU</button>
            </form>
        <?php } ?>

        <a href="index.php" class="link-kembali">&laquo; Kembali ke Halaman Login</a>
    </div>
</body>
</html>