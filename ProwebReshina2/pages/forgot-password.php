<!DOCTYPE html>
<?php
// Konfigurasi email admin
$admin_email = 'admin@reshina.com';
$notif = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $user_email = trim($_POST['email']);
    // Validasi email sederhana
    if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        // Kirim email ke admin
        $subject = 'Permintaan Reset Password Pengguna';
        $message = "Ada permintaan reset password dari user dengan email: $user_email\nSilakan proses reset password sesuai prosedur.";
        $headers = "From: noreply@reshina.com\r\nReply-To: $user_email";
        // Fungsi mail() hanya bekerja jika server support. Bisa diganti PHPMailer di production.
        @mail($admin_email, $subject, $message, $headers);
        $notif = '<div class="notif-message success">Permintaan reset password telah dikirim.<br>Silakan tunggu konfirmasi dari admin melalui email Anda.</div>';
    } else {
        $notif = '<div class="notif-message error">Format email tidak valid.</div>';
    }
}
?>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | ProwebReshina</title>
    <link rel="stylesheet" href="../assets/css/forgot-password.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="forgot-password-form">
            <?php if ($notif) echo $notif; ?>
            <?php if (!$notif || strpos($notif,'error')!==false): ?>
            <div class="logo-center">
                <a href="../index.php"><img src="../assets/image/logo.png" alt="Reshina Logo" class="logo-img"></a>
                <h2 class="brand-title">Reshina</h2>
            </div>
            <h1>Lupa Password</h1>
            <p class="form-description">Masukkan email Anda untuk menerima tautan reset password.<br><span class="security-tip"><i class="fas fa-shield-alt"></i> Jangan bagikan link reset ke siapapun.</span></p>
            <form id="forgotPasswordForm" method="POST" autocomplete="off" novalidate>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="nama@email.com" autocomplete="off" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    <span class="input-error" id="emailError"></span>
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span id="btnText">Kirim Tautan Reset</span>
                    <span class="spinner" id="spinner" style="display:none;"></span>
                </button>
            </form>
            <?php endif; ?>
            <div id="notif" class="notif-message" style="display:none;"></div>
            <div class="form-links">
                <a href="login.php"><i class="fas fa-arrow-left"></i> Kembali ke halaman login</a>
                <a href="../index.php"><i class="fas fa-home"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
    <script>
    // Validasi email dan feedback
    const form = document.getElementById('forgotPasswordForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const notif = document.getElementById('notif');
    const btnText = document.getElementById('btnText');
    const spinner = document.getElementById('spinner');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        emailError.textContent = '';
        notif.style.display = 'none';
        notif.className = 'notif-message';
        const email = emailInput.value.trim();
        if (!validateEmail(email)) {
            emailError.textContent = 'Format email tidak valid.';
            return;
        }
        btnText.style.display = 'none';
        spinner.style.display = 'inline-block';
        // Simulasi AJAX
        setTimeout(function() {
            spinner.style.display = 'none';
            btnText.style.display = 'inline';
            // Simulasi sukses/gagal
            if (email === 'tes@email.com') {
                notif.textContent = 'Tautan reset berhasil dikirim ke email Anda.';
                notif.classList.add('success');
            } else {
                notif.textContent = 'Email tidak ditemukan. Silakan cek kembali atau hubungi admin.';
                notif.classList.add('error');
            }
            notif.style.display = 'block';
        }, 1500);
    });
    function validateEmail(email) {
        // Validasi regex sederhana
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    </script>
</body>
</html>
