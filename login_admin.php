<?php
session_start();
include 'koneksi.php'; 

$error = "";

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = 'admin'; 

    // Cek hanya untuk role admin
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$role'"; 
    $result = mysqli_query($conn, $sql); 

    if (mysqli_num_rows($result) > 0) { 
        $_SESSION['username'] = $username; 
        $_SESSION['role'] = $role; 
        
        header("Location: admin.php"); // Melempar admin ke halaman dashboard admin
        exit(); 
    } else {
        $error = "Username atau Password Admin salah! Akses ditolak."; 
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Warung Rizky - Login Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #343a40; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.3); width: 350px; text-align: center; }
        h2 { color: #007bff; margin-bottom: 1.5rem; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #0056b3; }
        .error { color: red; font-size: 14px; margin-bottom: 10px; background: #fdd; padding: 10px; border-radius: 5px; border: 1px solid #fcc; }
        .link-kembali { display: block; margin-top: 20px; font-size: 13px; color: #6c757d; text-decoration: none; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Panel Admin</h2> 
        
        <?php if($error != "") { echo "<div class='error'>$error</div>"; } ?> 

        <form action="" method="POST"> 
            <input type="text" name="username" placeholder="Username Admin" required> 
            <input type="password" name="password" placeholder="Password" required> 
            <button type="submit" name="submit">MASUK KONTROL PANEL</button> 
        </form> 

        <a href="index.php" class="link-kembali">&laquo; Kembali ke Login Customer</a>
    </div>
</body>
</html>