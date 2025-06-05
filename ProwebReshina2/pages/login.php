<?php
// Mulai session untuk mengambil data user yang sedang login
session_start();

// Cek apakah user sudah login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
  // Jika user sudah login, maka arahkan ke halaman home
  header("Location: dashboard.php");
  exit();
}

// Koneksi ke database
include '../include/koneksi.php';

if (isset($_POST['submit'])) {
    // Ambil data email dan password dari form
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    // Cek apakah inputan email dan password kosong
    if (empty($email) || empty($password)) {
        // Jika kosong, maka tampilkan pesan error
        $error_message = "Email dan password harus diisi.";
    } else {
        // Buat query untuk mengambil data user dari database
        $query = "SELECT * FROM user WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        // Cek apakah hasil query ada dan hanya 1
        if ($result && mysqli_num_rows($result) === 1) {
            // Ambil data user dari hasil query
            $userData = mysqli_fetch_assoc($result);

            // Cek apakah password yang diinputkan sama dengan password di database
            if ($password === $userData['password']) {
                // Jika sama, maka simpan data user ke session
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['username']; // Menggunakan kolom 'name' dari database
                $_SESSION['email'] = $userData['email'];
                $_SESSION['role'] = $userData['role']; // <-- simpan role ke session
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                // Arahkan ke halaman dashboard
                header("Location: /ProwebReshina2/pages/dashboard.php");
                exit();
            } else {
                // Jika password salah, maka tampilkan pesan error
                $error_message = "Password salah.";
            }
        } else {
            // Jika email tidak ditemukan, maka tampilkan pesan error
            $error_message = "Email tidak ditemukan.";
        }
    }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="../assets/css/login.css" />
</head>

<body>
  <div class="container">
    <div class="left">
      <h1>Login</h1>
      <p>Enter your email and password to log in</p>

      <form id="loginForm" method="POST" action="">
        <?php if (!empty($error_message)): ?>
          <div style="color: red; margin-bottom: 15px; padding: 10px; background-color: #ffeeee; border-radius: 5px;">
            <?php echo $error_message; ?>
          </div>
        <?php endif; ?>
        
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required />

        <div class="password-wrapper">
          <label for="password">
            Password <a href="forgot-password.php" class="forgot">forgot password</a>
          </label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required />
          <img src="https://cdn-icons-png.flaticon.com/512/709/709612.png"
               alt="Show Password"
               class="toggle-password"
               onclick="togglePasswordVisibility('password', this)" />
        </div>

        <div class="privasi">
          <input type="checkbox" id="privasi" required />
          <label for="privasi">I agree to the Terms and Conditions and Privacy Policy.</label>
        </div>

        <button type="submit" name="submit" class="login-btn">Login</button>

        <div class="divider">Or</div>

        <div class="social-buttons">
          <button type="button" class="google-btn">
            <img src="https://img.icons8.com/color/48/google-logo.png" alt="Google" /> Sign in with Google
          </button>
          <button type="button" class="apple-btn">
            <img src="https://img.icons8.com/ios-filled/50/000000/mac-os.png" alt="Apple" /> Sign in with Apple
          </button>
        </div>

        <p class="signup">Don't have an account? <a href="register.php">Sign Up</a></p>
      </form>
    </div>

    <div class="right">
      <img src="../assets/image/regis.png" alt="Nike Shoe" class="shoe-img" />
    </div>
  </div>

  <script src="../assets/js/login.js"></script>
</body>

<?php
mysqli_close($conn);
?>