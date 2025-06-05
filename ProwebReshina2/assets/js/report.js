// Report page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const reportForm = document.getElementById('reportForm');
    const reportEvidenceInput = document.getElementById('reportEvidence');
    const evidencePreviewContainer = document.getElementById('evidencePreviewContainer');
    
    // Handle file uploads and preview
    reportEvidenceInput.addEventListener('change', function() {
        const files = this.files;
        
        if (files.length > 5) {
            alert('Maksimal 5 file yang dapat diunggah.');
            this.value = ''; // Reset the input
            return;
        }
        
        // Clear previous previews
        evidencePreviewContainer.innerHTML = '';
        
        // Create preview for each selected file
        Array.from(files).forEach((file, index) => {
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert(`File "${file.name}" melebihi batas ukuran 5MB.`);
                return;
            }
            
            const preview = document.createElement('div');
            preview.className = 'evidence-preview';
            
            // Different preview based on file type
            if (file.type.startsWith('image/')) {
                // Image file
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.alt = `Preview ${index + 1}`;
                    preview.appendChild(img);
                };
                
                reader.readAsDataURL(file);
            } else {
                // PDF or other file types
                const fileIcon = document.createElement('div');
                fileIcon.className = 'file-icon';
                
                const icon = document.createElement('i');
                icon.className = file.type === 'application/pdf' ? 'fas fa-file-pdf' : 'fas fa-file';
                
                const fileName = document.createElement('span');
                fileName.textContent = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
                
                fileIcon.appendChild(icon);
                fileIcon.appendChild(fileName);
                preview.appendChild(fileIcon);
            }
            
            // Add remove button
            const removeBtn = document.createElement('div');
            removeBtn.className = 'remove-evidence';
            removeBtn.innerHTML = '<i class="fas fa-times"></i>';
            removeBtn.addEventListener('click', function() {
                preview.remove();
                // Note: This doesn't actually remove the file from the input
                // In a real application, you would need to handle this differently
            });
            
            preview.appendChild(removeBtn);
            evidencePreviewContainer.appendChild(preview);
        });
    });
    
    // Contact method change
    const contactMethodSelect = document.getElementById('contactMethod');
    const contactInfoInput = document.getElementById('contactInfo');
    
    contactMethodSelect.addEventListener('change', function() {
        const method = this.value;
        
        // Update placeholder based on selected contact method
        if (method === 'email') {
            contactInfoInput.placeholder = 'Contoh: nama@email.com';
            contactInfoInput.type = 'email';
        } else if (method === 'phone' || method === 'whatsapp') {
            contactInfoInput.placeholder = 'Contoh: +62 812 3456 7890';
            contactInfoInput.type = 'tel';
        } else {
            contactInfoInput.placeholder = 'Email/No. Telepon/WhatsApp';
            contactInfoInput.type = 'text';
        }
    });
    
    // Form submission
    reportForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        
        // Validate form
        let isValid = true;
        
        // Check if files are uploaded
        if (reportEvidenceInput.files.length === 0) {
            alert('Unggah minimal satu file sebagai bukti.');
            isValid = false;
        }
        
        // Check terms agreement
        if (!document.getElementById('termsAgreement').checked) {
            alert('Anda harus menyetujui pernyataan kebenaran informasi.');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        // In a real application, you would send this data to a server
        console.log('Report submitted with data:', Object.fromEntries(formData));
        
        // Show success message and redirect
        alert('Laporan Anda telah berhasil dikirim. Tim kami akan meninjau laporan Anda dan menghubungi Anda dalam 1-3 hari kerja. Terima kasih atas laporan Anda.');
        
        // Redirect to home page
        window.location.href = '../index.html';
    });
    
    // Reset form button
    const resetButton = document.querySelector('button[type="reset"]');
    
    resetButton.addEventListener('click', function() {
        // Clear evidence previews
        evidencePreviewContainer.innerHTML = '';
        
        // Reset contact info placeholder
        contactInfoInput.placeholder = 'Email/No. Telepon/WhatsApp';
        contactInfoInput.type = 'text';
        
        // Confirm reset
        return confirm('Apakah Anda yakin ingin mengatur ulang formulir?');
    });
});
