<?php
session_start();
include 'koneksi.php'; 

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $no_telepon = mysqli_real_escape_string($conn, $_POST['no_telepon']);
    $role = 'customer'; 

    if (!empty($username) && !empty($password) && !empty($email) && !empty($no_telepon)) {
        
        // Validasi: Cek apakah Username, Email, atau No Telepon sudah terdaftar di database
        $cek_data = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' OR email='$email' OR no_telepon='$no_telepon'");
        
        if (mysqli_num_rows($cek_data) > 0) {
            $row = mysqli_fetch_assoc($cek_data);
            if ($row['username'] == $username) {
                $error = "Username sudah digunakan! Silakan pilih username lain.";
            } elseif ($row['email'] == $email) {
                $error = "Email sudah terdaftar! Gunakan email lain atau silakan login.";
            } elseif ($row['no_telepon'] == $no_telepon) {
                $error = "Nomor Telepon sudah terdaftar! Gunakan nomor lain.";
            }
        } else {
            // Jika lolos validasi (tidak ada data kembar), simpan ke database
            $sql = "INSERT INTO users (username, password, email, no_telepon, role) VALUES ('$username', '$password', '$email', '$no_telepon', '$role')";
            if (mysqli_query($conn, $sql)) {
                $success = "Akun berhasil dibuat! Silakan login.";
            } else {
                $error = "Gagal mendaftar: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "Semua kolom wajib diisi!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Warung Rizky - Daftar Akun</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: url('images/background.png') no-repeat center center fixed; 
            background-size: cover; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
        }
        .card { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(5px); 
            padding: 2rem; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.2); 
            width: 360px; 
            text-align: center; 
            margin: 20px;
        }
        h2 { color: #ee4d2d; margin-bottom: 1.5rem; }
        input { 
            width: 100%; 
            padding: 12px; 
            margin: 8px 0; 
            border: 1px solid #ccc; 
            border-radius: 6px; 
            box-sizing: border-box; 
            background: rgba(255,255,255,0.9);
        }
        button { 
            width: 100%; 
            padding: 12px; 
            background: #ee4d2d; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            font-weight: bold; 
            margin-top: 10px; 
        }
        button:hover { background: #d73d17; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; background: #fdd; padding: 10px; border-radius: 5px; border: 1px solid #fcc; text-align: left; }
        .success { color: green; font-size: 14px; margin-bottom: 10px; background: #dfd; padding: 10px; border-radius: 5px; border: 1px solid #cfc; text-align: left; }
        .link-login { display: block; margin-top: 15px; color: #333; text-decoration: none; font-size: 14px; }
        .link-login:hover { color: #ee4d2d; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Buat Akun Baru</h2>
        
        <?php if($error != "") echo "<div class='error'>$error</div>"; ?>
        <?php if($success != "") echo "<div class='success'>$success</div>"; ?>

        <form action="" method="POST">
            <input type="text" name="username" placeholder="Username Baru" required>
            <input type="email" name="email" placeholder="Alamat Email" required>
            <input type="text" name="no_telepon" placeholder="Nomor Telepon (WhatsApp/SMS)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="submit">DAFTAR SEKARANG</button>
        </form>
        
        <a href="index.php" class="link-login">Sudah punya akun? <b style="color:#ee4d2d;">Login di sini</b></a>
    </div>
</body>
</html>