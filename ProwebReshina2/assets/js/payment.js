// Payment page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabHeaders = document.querySelectorAll('.tab-header');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Remove active class from all headers and panes
            tabHeaders.forEach(h => h.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Add active class to clicked header
            this.classList.add('active');
            
            // Show corresponding tab pane
            const tabId = this.dataset.tab;
            document.getElementById(tabId).classList.add('active');
            
            // Update payment details based on selected payment method
            updatePaymentDetails(tabId);
        });
    });
    
    // Update payment details based on selected payment method
    function updatePaymentDetails(tabId) {
        if (tabId === 'bank-transfer') {
            // Update bank transfer details based on selected bank
            const selectedBank = document.querySelector('input[name="bank"]:checked').value;
            updateBankDetails(selectedBank);
        } else if (tabId === 'e-wallet') {
            // Update e-wallet details based on selected wallet
            const selectedWallet = document.querySelector('input[name="wallet"]:checked').value;
            updateWalletDetails(selectedWallet);
        } else if (tabId === 'virtual-account') {
            // Update virtual account details based on selected bank
            const selectedVA = document.querySelector('input[name="va"]:checked').value;
            updateVADetails(selectedVA);
        }
    }
    
    // Bank selection change event
    const bankOptions = document.querySelectorAll('input[name="bank"]');
    bankOptions.forEach(option => {
        option.addEventListener('change', function() {
            updateBankDetails(this.value);
        });
    });
    
    // Update bank details based on selected bank
    function updateBankDetails(bank) {
        // Bank account details (in a real app, this would come from the server)
        const bankDetails = {
            bca: {
                name: 'BCA',
                accountNumber: '1234567890',
                accountName: 'PT. Reshina Indonesia'
            },
            mandiri: {
                name: 'Mandiri',
                accountNumber: '9876543210',
                accountName: 'PT. Reshina Indonesia'
            },
            bni: {
                name: 'BNI',
                accountNumber: '0123456789',
                accountName: 'PT. Reshina Indonesia'
            },
            bri: {
                name: 'BRI',
                accountNumber: '9870123456',
                accountName: 'PT. Reshina Indonesia'
            }
        };
        
        // Update bank details in the UI
        const details = bankDetails[bank];
        document.querySelector('.account-info .account-row:nth-child(2) span:first-child').textContent = 'Bank';
        document.querySelector('.account-info .account-row:nth-child(2) span:last-child').textContent = details.name;
        document.getElementById('accountNumber').textContent = details.accountNumber;
        document.querySelector('.account-info .account-row:nth-child(4) span:last-child').textContent = details.accountName;
    }
    
    // E-wallet selection change event
    const walletOptions = document.querySelectorAll('input[name="wallet"]');
    walletOptions.forEach(option => {
        option.addEventListener('change', function() {
            updateWalletDetails(this.value);
        });
    });
    
    // Update wallet details based on selected wallet
    function updateWalletDetails(wallet) {
        // In a real app, this would update the QR code image and instructions
        const qrImage = document.querySelector('.qr-image img');
        const walletInstructions = document.querySelector('.qr-code p');
        
        // Update QR code and instructions based on selected wallet
        switch (wallet) {
            case 'gopay':
                qrImage.src = '../assets/image/qr-code.png';
                walletInstructions.textContent = 'Buka aplikasi GoPay di smartphone Anda dan scan QR code di atas untuk melakukan pembayaran.';
                break;
            case 'ovo':
                qrImage.src = '../assets/image/qr-code.png';
                walletInstructions.textContent = 'Buka aplikasi OVO di smartphone Anda dan scan QR code di atas untuk melakukan pembayaran.';
                break;
            case 'dana':
                qrImage.src = '../assets/image/qr-code.png';
                walletInstructions.textContent = 'Buka aplikasi DANA di smartphone Anda dan scan QR code di atas untuk melakukan pembayaran.';
                break;
            case 'linkaja':
                qrImage.src = '../assets/image/qr-code.png';
                walletInstructions.textContent = 'Buka aplikasi LinkAja di smartphone Anda dan scan QR code di atas untuk melakukan pembayaran.';
                break;
        }
    }
    
    // Virtual Account selection change event
    const vaOptions = document.querySelectorAll('input[name="va"]');
    vaOptions.forEach(option => {
        option.addEventListener('change', function() {
            updateVADetails(this.value);
        });
    });
    
    // Update Virtual Account details based on selected bank
    function updateVADetails(va) {
        // Virtual Account details (in a real app, this would come from the server)
        const vaDetails = {
            'va-bca': {
                name: 'BCA',
                vaNumber: '8888 1234 5678 9012',
                accountName: 'Ahmad Rizki / Reshina'
            },
            'va-mandiri': {
                name: 'Mandiri',
                vaNumber: '8888 9876 5432 1098',
                accountName: 'Ahmad Rizki / Reshina'
            },
            'va-bni': {
                name: 'BNI',
                vaNumber: '8888 0123 4567 8901',
                accountName: 'Ahmad Rizki / Reshina'
            }
        };
        
        // Update VA details in the UI
        const details = vaDetails[va];
        document.querySelector('#virtual-account .account-info .account-row:nth-child(2) span:last-child').textContent = details.name;
        document.getElementById('vaNumber').textContent = details.vaNumber;
        document.querySelector('#virtual-account .account-info .account-row:nth-child(4) span:last-child').textContent = details.accountName;
        
        // Update payment instructions based on selected bank
        const instructionSteps = document.querySelector('.instruction-steps');
        if (va === 'va-bca') {
            instructionSteps.innerHTML = `
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <div class="step-text">Login ke m-banking atau internet banking BCA Anda</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <div class="step-text">Pilih menu "Transfer" dan pilih "Virtual Account"</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <div class="step-text">Masukkan nomor virtual account yang tertera di atas</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">4</div>
                    <div class="step-text">Pastikan jumlah transfer dan nama penerima sudah sesuai</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">5</div>
                    <div class="step-text">Konfirmasi dan selesaikan pembayaran</div>
                </div>
            `;
        } else if (va === 'va-mandiri') {
            instructionSteps.innerHTML = `
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <div class="step-text">Login ke Livin' by Mandiri atau internet banking Mandiri Anda</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <div class="step-text">Pilih menu "Pembayaran" dan pilih "Virtual Account"</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <div class="step-text">Masukkan nomor virtual account yang tertera di atas</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">4</div>
                    <div class="step-text">Periksa detail pembayaran dan pastikan jumlah sesuai</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">5</div>
                    <div class="step-text">Konfirmasi dan selesaikan pembayaran</div>
                </div>
            `;
        } else if (va === 'va-bni') {
            instructionSteps.innerHTML = `
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <div class="step-text">Login ke BNI Mobile Banking atau internet banking BNI Anda</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <div class="step-text">Pilih menu "Transfer" dan pilih "Virtual Account"</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <div class="step-text">Masukkan nomor virtual account yang tertera di atas</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">4</div>
                    <div class="step-text">Periksa detail pembayaran dan pastikan jumlah sesuai</div>
                </div>
                <div class="instruction-step">
                    <div class="step-number">5</div>
                    <div class="step-text">Konfirmasi dan selesaikan pembayaran</div>
                </div>
            `;
        }
    }
    
    // Copy to clipboard functionality
    const copyButtons = document.querySelectorAll('.copy-btn');
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.clipboardTarget;
            const textToCopy = document.querySelector(targetId).textContent;
            
            // Create a temporary textarea element to copy text
            const textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            // Show feedback
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                this.innerHTML = originalIcon;
            }, 2000);
        });
    });
    
    // Payment timer functionality
    const hoursElement = document.getElementById('hours');
    const minutesElement = document.getElementById('minutes');
    const secondsElement = document.getElementById('seconds');
    
    // Set the countdown time (24 hours from now)
    const now = new Date();
    const deadline = new Date(now.getTime() + 24 * 60 * 60 * 1000);
    
    // Update the countdown every second
    function updateCountdown() {
        const currentTime = new Date();
        const diff = deadline - currentTime;
        
        if (diff <= 0) {
            // Timer has expired
            hoursElement.textContent = '00';
            minutesElement.textContent = '00';
            secondsElement.textContent = '00';
            
            // Show expired message
            document.querySelector('.timer-note').textContent = 'Batas waktu pembayaran telah berakhir. Pesanan Anda telah dibatalkan.';
            document.querySelector('.timer-note').style.color = '#dc3545';
            
            // Disable payment buttons
            document.querySelector('.btn-primary').classList.add('disabled');
            document.querySelector('.btn-primary').style.backgroundColor = '#6c757d';
            document.querySelector('.btn-primary').style.pointerEvents = 'none';
            
            return;
        }
        
        // Calculate hours, minutes, and seconds
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Update the timer display
        hoursElement.textContent = hours.toString().padStart(2, '0');
        minutesElement.textContent = minutes.toString().padStart(2, '0');
        secondsElement.textContent = seconds.toString().padStart(2, '0');
        
        // Schedule the next update
        setTimeout(updateCountdown, 1000);
    }
    
    // Start the countdown
    updateCountdown();
    
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
        
        // Update transfer amounts
        document.getElementById('transferAmount').textContent = `Rp ${total.toLocaleString('id-ID')}`;
        document.getElementById('vaAmount').textContent = `Rp ${total.toLocaleString('id-ID')}`;
    }
});
