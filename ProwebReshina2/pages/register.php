<?php
// Include koneksi ke database
include '../include/koneksi.php';
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: /ProwebReshina2/pages/dashboard.php");
    exit();
}

// Jika form registrasi di submit
if (isset($_POST['submit'])) {
    // Ambil data dari form
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $city = trim($_POST['city']);
    $nomor_hp = trim($_POST['nomor_hp']);
    $error = '';

    // Cek apakah email sudah terdaftar
    $check_email = "SELECT id FROM user WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $check_email);
    
    if (mysqli_num_rows($result) > 0) {
        $error = 'Email sudah terdaftar. Silakan gunakan email lain.';
    }
    // Cek apakah password dan konfirmasi sama
    elseif ($password !== $confirmPassword) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    }

    if (empty($error)) {
        // Enkripsi password menggunakan md5
        $password = md5($password);
        
        // Buat query untuk menambahkan data user ke database
        $query = "INSERT INTO user (email, password, username, city, nomor_hp) VALUES ('$email', '$password', '$username', '$city', '$nomor_hp')";
    }

    // Jalankan query jika tidak ada error
    if (empty($error)) {
        if (mysqli_query($conn, $query)) {
            header("Location: login.php?registered=1");
            exit();
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi nanti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="/ProwebReshina2/assets/css/register.css">
</head>
<body>
  <div class="container">
    <div class="left">
      <h1>Sign Up</h1>
      <p>Register to access your account.</p>
      
      <?php if (!empty($error)): ?>
        <div class="error-message" style="color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form id="registerForm" action="register.php" method="POST">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="username" placeholder="username" required>

        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="xxxxx@gmail.com" required>

        <label for="city">Kota</label>
        <input type="text" id="city" name="city" placeholder="Mataram" required>

        <label for="nomor_hp">Nomor Telepon</label>
        <input type="text" id="nomor_hp" name="nomor_hp" placeholder="+62xxxxxxxxx" required pattern="^\+628[0-9]{6,}$" title="Nomor harus menggunakan kode negara">
        <script>
        document.getElementById('nomor_hp').addEventListener('input', function(e) {
            let v = this.value;
            if(v.startsWith('0')) {
                this.value = '+62' + v.slice(1);
            } else if(v.startsWith('62')) {
                this.value = '+62' + v.slice(2);
            }
        });
        </script>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>

        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="••••••••" required>

        <button type="submit" name="submit"class="login-btn">Register</button>
      </form>

      <div class="divider">or sign up with</div>
      <div class="social-buttons">
        <button type="button" class="google-btn">
          <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google" /> Sign in with Google
        </button>
        <button type="button" class="apple-btn">
          <img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png" alt="Apple" /> Sign in with Apple
        </button>
      </div>

      <div class="signup">
        Already have an account? <a href="login.php">Login</a>
      </div>
    </div>

    <div class="right">
      <img src="/ProwebReshina2/assets/image/regis.png" alt="Sneaker" class="shoe-img">
    </div>
  </div>

  <script src="/ProwebReshina2/assets/js/script.js"></script>
</body>
</html>

<?php
mysqli_close($conn);
?>