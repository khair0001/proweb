// Forgot Password page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            const email = document.getElementById('email').value;
            
            // Validate input
            if (!email) {
                alert('Silakan masukkan email Anda');
                return;
            }
            
            // In a real application, you would send this data to a server
            // For now, we'll simulate a successful password reset request
            console.log('Password reset request for:', email);
            
            // Show success message
            alert('Tautan reset password telah dikirim ke email Anda. Silakan periksa kotak masuk Anda.');
            
            // Redirect to login page
            window.location.href = 'login.html';
        });
    }
});
