<?php
session_start();
include 'koneksi.php'; 

$error = "";
$username_input = ""; // Membuat variabel kosong untuk menyimpan teks username

// Inisialisasi session salah login khusus customer
if (!isset($_SESSION['salah_login_customer'])) {
    $_SESSION['salah_login_customer'] = 0;
}

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = 'customer'; 

    // Simpan teks username yang diketik user ke variabel penampung agar tidak hilang saat refresh
    $username_input = $username;

    // 1. Cek dulu apakah usernamenya ada di database dengan role customer
    $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND role='$role'");

    if (mysqli_num_rows($cek_user) > 0) {
        // Jika username ADA, sekarang cek apakah passwordnya benar
        $user_data = mysqli_fetch_assoc($cek_user);
        
        if ($user_data['password'] === $password) {
            // JIKA LOGIN SUKSES
            $_SESSION['salah_login_customer'] = 0; 
            $_SESSION['username'] = $username; 
            $_SESSION['role'] = $role; 
            
            header("Location: menu.php"); 
            exit(); 
        } else {
            // JIKA USERNAME BENAR, TAPI PASSWORD SALAH (Teks username akan tetap ditahan di kolom)
            $_SESSION['salah_login_customer'] += 1;
            $error = "Username atau Password Customer salah! (Percobaan ke-" . $_SESSION['salah_login_customer'] . ")"; 
        }
    } else {
        // JIKA USERNAME SALAH / TIDAK ADA (Maka kita kosongkan variabel penampungnya agar ikut ter-refresh bersih)
        $username_input = ""; 
        $_SESSION['salah_login_customer'] += 1;
        $error = "Username atau Password Customer salah! (Percobaan ke-" . $_SESSION['salah_login_customer'] . ")"; 
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warung Rizky - Login Customer</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: url('images/background.png') no-repeat center center fixed; background-size: cover; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(5px); padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 350px; text-align: center; }
        h2 { color: #ee4d2d; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #ee4d2d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #d73d17; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; background: #fdd; padding: 10px; border-radius: 5px; border: 1px solid #fcc; }
        .alert-warning { background: #fff3cd; color: #856404; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; border: 1px solid #ffeeba; text-align: left; }
        .btn-ubah-pass { display: block; background: #ffc107; color: #212529; padding: 12px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-bottom: 15px; font-size: 14px; box-sizing: border-box; }
        .btn-ubah-pass:hover { background: #e0a800; }
        .link-footer { display: block; margin-top: 15px; font-size: 13px; color: #555; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Login Customer</h2> 
        
        <?php if($error != "") { echo "<div class='error'>$error</div>"; } ?> 

        <!-- Tombol Ubah Password khusus Customer jika salah 3x -->
        <?php if ($_SESSION['salah_login_customer'] >= 3) { ?>
            <div class="alert-warning">
                ⚠️ Anda salah memasukkan password sebanyak <b><?php echo $_SESSION['salah_login_customer']; ?>x</b>.
            </div>
            <a href="ubah_password.php" class="btn-ubah-pass">🔒 Ubah Password via WA</a>
            <hr style="border: 0; border-top: 1px dashed #ccc; margin-bottom: 15px;">
        <?php } ?>

        <form action="" method="POST"> 
            <!-- Ditambahkan value untuk menahan teks username agar tidak hilang -->
            <input type="text" name="username" placeholder="Username Customer" value="<?php echo htmlspecialchars($username_input); ?>" required> 
            <input type="password" name="password" placeholder="Password" required> 
            <button type="submit" name="submit">MASUK SEBAGAI CUSTOMER</button> 
        </form> 

        <a href="register.php" class="link-footer">Belum punya akun? <b style="color: #ee4d2d;">Daftar disini</b></a>
        <a href="login_admin.php" class="link-footer" style="color: #007bff; margin-top: 10px;">Login sebagai Admin/Pemilik &raquo;</a>
    </div>
</body>
</html>