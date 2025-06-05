document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi variabel global
    let selectedFile = null;
    
    // Fungsi untuk menangani navigasi tab
    function initTabNavigation() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Jika tombol memiliki atribut onclick, biarkan browser menanganinya
                if (this.hasAttribute('onclick')) {
                    return;
                }
                
                const tabId = this.getAttribute('data-tab');
                
                // Hapus class active dari semua tab dan button
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Tambahkan class active ke tab yang diklik
                this.classList.add('active');
                const targetTab = document.getElementById(tabId);
                
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            });
        });
    }
    
    // Fungsi untuk menangani filter produk
    function initProductFilters() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const productCards = document.querySelectorAll('.product-card');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Hapus class active dari semua filter button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Tambahkan class active ke filter button yang diklik
                this.classList.add('active');
                
                // Tampilkan/sembunyikan produk berdasarkan filter
                productCards.forEach(card => {
                    if (filter === 'all' || card.getAttribute('data-status') === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    
    // Fungsi untuk menangani edit profil
    function initEditProfile() {
        const editProfileBtn = document.getElementById('editProfileBtn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        const profileInfoView = document.getElementById('profileInfoView');
        const editProfileForm = document.getElementById('editProfileForm');
        
        if (editProfileBtn && profileInfoView && editProfileForm) {
            editProfileBtn.addEventListener('click', function() {
                profileInfoView.classList.add('hidden');
                editProfileForm.classList.remove('hidden');
            });
        }
        
        if (cancelEditBtn && profileInfoView && editProfileForm) {
            cancelEditBtn.addEventListener('click', function() {
                editProfileForm.classList.add('hidden');
                profileInfoView.classList.remove('hidden');
            });
        }
    }
    
    // Fungsi untuk menangani upload foto profil
    function initPhotoUpload() {
        const editFotoBtn = document.getElementById('editFotoBtn');
        const photoUploadModal = document.getElementById('photoUploadModal');
        const closePhotoModal = document.getElementById('closePhotoModal');
        const cancelUpload = document.getElementById('cancelUpload');
        const dropArea = document.getElementById('dropArea');
        const photoInput = document.getElementById('photoInput');
        const imagePreview = document.getElementById('imagePreview');
        const previewImage = document.getElementById('previewImage');
        
        // Buka modal upload foto
        if (editFotoBtn && photoUploadModal) {
            editFotoBtn.addEventListener('click', function() {
                photoUploadModal.style.display = 'flex';
            });
        }
        
        // Tutup modal upload foto
        if (closePhotoModal && photoUploadModal) {
            closePhotoModal.addEventListener('click', function() {
                photoUploadModal.style.display = 'none';
                resetPhotoUpload();
            });
        }
        
        // Batal upload foto
        if (cancelUpload && photoUploadModal) {
            cancelUpload.addEventListener('click', function() {
                photoUploadModal.style.display = 'none';
                resetPhotoUpload();
            });
        }
        
        // Klik pada drop area untuk memilih file
        if (dropArea && photoInput) {
            dropArea.addEventListener('click', function() {
                photoInput.click();
            });
        }
        
        // Handle file yang dipilih
        if (photoInput && imagePreview && previewImage) {
            photoInput.addEventListener('change', function(e) {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Handle drag and drop
        if (dropArea && photoInput) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Highlight drop area saat drag over
            ['dragenter', 'dragover'].forEach(eventName => {
                dropArea.addEventListener(eventName, function() {
                    dropArea.style.borderColor = 'var(--primary-color)';
                    dropArea.style.backgroundColor = 'var(--light-gray)';
                });
            });
            
            // Unhighlight drop area saat drag leave
            ['dragleave', 'drop'].forEach(eventName => {
                dropArea.addEventListener(eventName, function() {
                    dropArea.style.borderColor = 'var(--medium-gray)';
                    dropArea.style.backgroundColor = 'transparent';
                });
            });
            
            // Handle file yang di-drop
            dropArea.addEventListener('drop', function(e) {
                const file = e.dataTransfer.files[0];
                if (file) {
                    photoInput.files = e.dataTransfer.files;
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        imagePreview.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        // Reset form upload foto
        function resetPhotoUpload() {
            if (photoInput) photoInput.value = '';
            if (imagePreview) imagePreview.style.display = 'none';
        }
    }
    
    // Fungsi untuk menangani hapus akun
    function initDeleteAccount() {
        const deleteAccountBtn = document.getElementById('deleteAccountBtn');
        const deleteAccountModal = document.getElementById('deleteAccountModal');
        const closeDeleteModal = document.getElementById('closeDeleteModal');
        const cancelDelete = document.getElementById('cancelDelete');
        
        // Buka modal konfirmasi hapus akun
        if (deleteAccountBtn && deleteAccountModal) {
            deleteAccountBtn.addEventListener('click', function() {
                deleteAccountModal.style.display = 'flex';
            });
        }
        
        // Tutup modal konfirmasi hapus akun
        if (closeDeleteModal && deleteAccountModal) {
            closeDeleteModal.addEventListener('click', function() {
                deleteAccountModal.style.display = 'none';
            });
        }
        
        // Batal hapus akun
        if (cancelDelete && deleteAccountModal) {
            cancelDelete.addEventListener('click', function() {
                deleteAccountModal.style.display = 'none';
            });
        }
    }
    
    // Fungsi untuk menangani hapus produk
    function initDeleteProduct() {
        const deleteButtons = document.querySelectorAll('.delete-product');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                    // Kirim request hapus produk ke server
                    window.location.href = 'delete-product.php?id=' + productId;
                }
            });
        });
    }
    
    // Inisialisasi semua fungsi
    initTabNavigation();
    initProductFilters();
    initEditProfile();
    initPhotoUpload();
    initDeleteAccount();
    initDeleteProduct();
    
    // Hapus parameter URL setelah beberapa detik
    if (window.location.search.includes('update=success') || window.location.search.includes('photo=success')) {
        setTimeout(function() {
            const url = new URL(window.location);
            url.searchParams.delete('update');
            url.searchParams.delete('photo');
            window.history.replaceState({}, document.title, url.pathname + url.search);
        }, 3000);
    }
});