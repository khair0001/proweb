// Payment Confirmation page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const paymentConfirmationForm = document.getElementById('paymentConfirmationForm');
    const paymentProofInput = document.getElementById('paymentProof');
    const fileNameElement = document.querySelector('.file-name');
    const fileSizeElement = document.querySelector('.file-size');
    const filePreview = document.querySelector('.file-preview');
    const imagePreview = document.getElementById('imagePreview');
    const removePreviewButton = document.querySelector('.remove-preview');
    const transferAmountInput = document.getElementById('transferAmount');
    const confirmationSuccess = document.getElementById('confirmationSuccess');
    const confirmationFormContainer = document.querySelector('.confirmation-form-container');
    const confirmationDateElement = document.getElementById('confirmationDate');
    
    // Format currency input
    transferAmountInput.addEventListener('input', function(e) {
        // Remove all non-digit characters
        let value = this.value.replace(/\D/g, '');
        
        // Format with thousand separators
        if (value !== '') {
            value = parseInt(value, 10).toLocaleString('id-ID');
        }
        
        // Update the input value
        this.value = value;
    });
    
    // File upload preview
    paymentProofInput.addEventListener('change', function(e) {
        const file = this.files[0];
        
        if (file) {
            // Update file info
            fileNameElement.textContent = file.name;
            
            // Format file size
            const fileSize = file.size / 1024; // KB
            if (fileSize < 1024) {
                fileSizeElement.textContent = fileSize.toFixed(2) + ' KB';
            } else {
                fileSizeElement.textContent = (fileSize / 1024).toFixed(2) + ' MB';
            }
            
            // Validate file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file terlalu besar. Maksimum 5MB.');
                resetFileInput();
                return;
            }
            
            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.');
                resetFileInput();
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                filePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Remove preview
    removePreviewButton.addEventListener('click', function() {
        resetFileInput();
    });
    
    // Reset file input
    function resetFileInput() {
        paymentProofInput.value = '';
        fileNameElement.textContent = 'Tidak ada file dipilih';
        fileSizeElement.textContent = '';
        filePreview.style.display = 'none';
    }
    
    // Form submission
    paymentConfirmationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate form
        if (!validateForm()) {
            return;
        }
        
        // Simulate form submission (in a real app, this would send data to the server)
        simulateFormSubmission();
    });
    
    // Validate form
    function validateForm() {
        // Check if file is selected
        if (!paymentProofInput.files[0]) {
            alert('Silakan upload bukti pembayaran.');
            return false;
        }
        
        // Check if transfer amount is valid
        const transferAmount = transferAmountInput.value.replace(/\D/g, '');
        if (transferAmount === '' || parseInt(transferAmount, 10) === 0) {
            alert('Silakan masukkan jumlah transfer yang valid.');
            transferAmountInput.focus();
            return false;
        }
        
        return true;
    }
    
    // Simulate form submission
    function simulateFormSubmission() {
        // Show loading state
        const submitButton = paymentConfirmationForm.querySelector('button[type="submit"]');
        const originalText = submitButton.textContent;
        submitButton.textContent = 'Mengirim...';
        submitButton.disabled = true;
        
        // Simulate API call delay
        setTimeout(function() {
            // Update confirmation date
            const now = new Date();
            const options = { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            confirmationDateElement.textContent = now.toLocaleDateString('id-ID', options) + ' WIB';
            
            // Show success message
            confirmationFormContainer.classList.add('hidden');
            confirmationSuccess.classList.remove('hidden');
            
            // Update steps
            const steps = document.querySelectorAll('.step');
            steps[0].classList.add('completed');
            steps[1].classList.add('active');
            
            // Update status in sidebar
            const statusElement = document.querySelector('.status');
            statusElement.textContent = 'Menunggu Verifikasi';
            statusElement.classList.remove('pending');
            statusElement.classList.add('verified');
            
            // Reset form and button
            paymentConfirmationForm.reset();
            resetFileInput();
            submitButton.textContent = originalText;
            submitButton.disabled = false;
            
            // Save confirmation status to localStorage
            saveConfirmationStatus();
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }, 2000);
    }
    
    // Save confirmation status to localStorage
    function saveConfirmationStatus() {
        // Get order details from the page
        const orderItems = [];
        document.querySelectorAll('.order-item').forEach(item => {
            const name = item.querySelector('h3').textContent;
            const price = item.querySelector('.item-price').textContent;
            const quantity = item.querySelector('.item-quantity').textContent.split(': ')[1];
            
            orderItems.push({
                name: name,
                price: price,
                quantity: quantity
            });
        });
        
        const confirmationData = {
            orderId: 'ORD12345678',
            status: 'waiting_verification',
            date: new Date().toISOString(),
            items: orderItems,
            total: document.querySelector('.total-row.total span:last-child').textContent,
            paymentMethod: document.querySelector('.info-row:first-child span:last-child').textContent
        };
        
        // Save to localStorage
        const confirmations = JSON.parse(localStorage.getItem('paymentConfirmations')) || [];
        confirmations.push(confirmationData);
        localStorage.setItem('paymentConfirmations', JSON.stringify(confirmations));
        
        // Create a notification
        createNotification(confirmationData);
    }
    
    // Create a notification for the payment confirmation
    function createNotification(confirmationData) {
        const notifications = JSON.parse(localStorage.getItem('notifications')) || [];
        
        const newNotification = {
            id: Date.now(),
            type: 'transaction',
            title: 'Konfirmasi Pembayaran Diterima',
            message: `Konfirmasi pembayaran Anda untuk pesanan #${confirmationData.orderId} telah diterima dan sedang diverifikasi oleh admin.`,
            time: 'Baru saja',
            read: false,
            link: 'profile.html'
        };
        
        notifications.unshift(newNotification);
        localStorage.setItem('notifications', JSON.stringify(notifications));
    }
    
    // Get order details from localStorage (in a real app, this would come from the server)
    const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
    const orderSummary = document.querySelector('.order-items');
    
    if (cartItems.length > 0) {
        // Clear default item
        orderSummary.innerHTML = '';
        
        // Calculate total
        let subtotal = 0;
        
        // Add each item to the order summary
        cartItems.forEach(item => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;
            
            const itemElement = document.createElement('div');
            itemElement.className = 'order-item';
            itemElement.innerHTML = `
                <div class="item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="item-details">
                    <h3>${item.name}</h3>
                    <div class="item-price">Rp ${item.price.toLocaleString('id-ID')}</div>
                    <div class="item-quantity">Jumlah: ${item.quantity}</div>
                </div>
            `;
            
            orderSummary.appendChild(itemElement);
        });
        
        // Set shipping cost (in a real app, this would be calculated based on location, weight, etc.)
        const shippingCost = 20000;
        
        // Update order totals
        const total = subtotal + shippingCost;
        document.querySelector('.total-row:nth-child(1) span:last-child').textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        document.querySelector('.total-row:nth-child(2) span:last-child').textContent = `Rp ${shippingCost.toLocaleString('id-ID')}`;
        document.querySelector('.total-row.total span:last-child').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }
});
