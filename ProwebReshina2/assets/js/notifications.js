// Notifications page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sample notifications data (in a real application, this would come from a server)
    const sampleNotifications = [
        {
            id: 1,
            type: 'auction',
            title: 'Lelang Telah Berakhir',
            message: 'Lelang untuk "Meja Kerja Minimalis" telah berakhir. Anda adalah penawar tertinggi dengan harga Rp 1.500.000.',
            time: '10 menit yang lalu',
            read: false,
            link: 'auction-result.html?id=4'
        },
        {
            id: 2,
            type: 'transaction',
            title: 'Pembayaran Berhasil',
            message: 'Pembayaran Anda untuk "Smartphone Samsung Galaxy A52" sebesar Rp 2.500.000 telah berhasil diverifikasi.',
            time: '2 jam yang lalu',
            read: false,
            link: 'transaction-detail.html?id=1'
        },
        {
            id: 3,
            type: 'chat',
            title: 'Pesan Baru',
            message: 'Anda menerima pesan baru dari Budi Santoso mengenai "Sepatu Lari Nike Air Zoom".',
            time: '5 jam yang lalu',
            read: false,
            link: 'chat.html?user=2'
        },
        {
            id: 4,
            type: 'transaction',
            title: 'Barang Diklaim',
            message: 'Donasi Anda "Buku Novel Harry Potter Set" telah diklaim oleh Siti Nurhaliza.',
            time: '1 hari yang lalu',
            read: false,
            link: 'transaction-detail.html?id=3'
        },
        {
            id: 5,
            type: 'report',
            title: 'Tanggapan Komplain',
            message: 'Admin telah menanggapi komplain Anda mengenai "Kamera DSLR Canon EOS 700D".',
            time: '2 hari yang lalu',
            read: false,
            link: 'report-detail.html?id=1'
        },
        {
            id: 6,
            type: 'transaction',
            title: 'Pesanan Dikirim',
            message: 'Pesanan Anda "Jaket Denim Uniqlo" telah dikirim oleh penjual. Estimasi tiba: 3-5 hari.',
            time: '3 hari yang lalu',
            read: true,
            link: 'transaction-detail.html?id=6'
        },
        {
            id: 7,
            type: 'auction',
            title: 'Penawaran Terlampaui',
            message: 'Penawaran Anda untuk "Sepeda Lipat Polygon" telah dilampaui. Penawaran tertinggi saat ini: Rp 3.200.000.',
            time: '4 hari yang lalu',
            read: true,
            link: 'auction-detail.html?id=8'
        },
        {
            id: 8,
            type: 'chat',
            title: 'Pesan Baru',
            message: 'Anda menerima pesan baru dari Dian Sastro mengenai "Meja Kerja Minimalis".',
            time: '5 hari yang lalu',
            read: true,
            link: 'chat.html?user=4'
        }
    ];
    
    // DOM elements
    const notificationsList = document.getElementById('notificationsList');
    const emptyNotifications = document.getElementById('emptyNotifications');
    const unreadCountElement = document.getElementById('unreadCount');
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    const filterLinks = document.querySelectorAll('.filter-list a');
    const sortSelect = document.getElementById('sortNotifications');
    const paginationButtons = document.querySelectorAll('.pagination-btn');
    
    // Get notifications from localStorage or use sample data if empty
    let notifications = JSON.parse(localStorage.getItem('notifications')) || sampleNotifications;
    
    // Current filter and sort
    let currentFilter = 'all';
    let currentSort = 'newest';
    
    // Render notifications
    function renderNotifications() {
        // Filter notifications
        let filteredNotifications = notifications;
        if (currentFilter !== 'all') {
            filteredNotifications = notifications.filter(notification => notification.type === currentFilter);
        }
        
        // Sort notifications
        if (currentSort === 'newest') {
            // Already sorted by newest in the sample data
        } else if (currentSort === 'oldest') {
            filteredNotifications = [...filteredNotifications].reverse();
        } else if (currentSort === 'unread') {
            filteredNotifications = filteredNotifications.filter(notification => !notification.read);
        }
        
        // Update unread count
        const unreadCount = notifications.filter(notification => !notification.read).length;
        unreadCountElement.textContent = unreadCount;
        
        // Show empty state if no notifications
        if (filteredNotifications.length === 0) {
            notificationsList.innerHTML = '';
            emptyNotifications.classList.remove('hidden');
            return;
        }
        
        // Hide empty state
        emptyNotifications.classList.add('hidden');
        
        // Clear notifications list
        notificationsList.innerHTML = '';
        
        // Add each notification to the list
        filteredNotifications.forEach(notification => {
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${notification.read ? '' : 'unread'}`;
            notificationItem.dataset.id = notification.id;
            
            // Set icon based on notification type
            let iconClass;
            switch (notification.type) {
                case 'transaction':
                    iconClass = 'fa-receipt';
                    break;
                case 'auction':
                    iconClass = 'fa-gavel';
                    break;
                case 'chat':
                    iconClass = 'fa-comment';
                    break;
                case 'report':
                    iconClass = 'fa-flag';
                    break;
                default:
                    iconClass = 'fa-bell';
            }
            
            notificationItem.innerHTML = `
                <div class="notification-icon ${notification.type}">
                    <i class="fas ${iconClass}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-meta">
                        <div class="notification-time">${notification.time}</div>
                        <div class="notification-actions">
                            ${!notification.read ? `<div class="notification-action mark-read" data-id="${notification.id}">Tandai dibaca</div>` : ''}
                            <div class="notification-action delete" data-id="${notification.id}">Hapus</div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add click event to notification item
            notificationItem.addEventListener('click', function(e) {
                // Ignore clicks on action buttons
                if (e.target.classList.contains('notification-action') || e.target.closest('.notification-action')) {
                    return;
                }
                
                // Mark as read
                if (!notification.read) {
                    markAsRead(notification.id);
                }
                
                // Navigate to link
                window.location.href = notification.link;
            });
            
            notificationsList.appendChild(notificationItem);
        });
        
        // Add event listeners to action buttons
        addActionListeners();
        
        // Save notifications to localStorage
        localStorage.setItem('notifications', JSON.stringify(notifications));
    }
    
    // Add event listeners to notification action buttons
    function addActionListeners() {
        // Mark as read buttons
        document.querySelectorAll('.mark-read').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                markAsRead(id);
            });
        });
        
        // Delete buttons
        document.querySelectorAll('.delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                deleteNotification(id);
            });
        });
    }
    
    // Mark notification as read
    function markAsRead(id) {
        const notification = notifications.find(n => n.id === id);
        if (notification) {
            notification.read = true;
            renderNotifications();
        }
    }
    
    // Delete notification
    function deleteNotification(id) {
        if (confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
            notifications = notifications.filter(n => n.id !== id);
            renderNotifications();
        }
    }
    
    // Mark all notifications as read
    markAllReadBtn.addEventListener('click', function() {
        notifications.forEach(notification => {
            notification.read = true;
        });
        renderNotifications();
    });
    
    // Filter notifications
    filterLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active filter
            filterLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            // Update current filter
            currentFilter = this.dataset.filter;
            
            // Render filtered notifications
            renderNotifications();
        });
    });
    
    // Sort notifications
    sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        renderNotifications();
    });
    
    // Pagination (simplified)
    paginationButtons.forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                paginationButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // In a real application, you would load the appropriate page of notifications
                // For this demo, we'll just show the same notifications
                renderNotifications();
                
                // Scroll to top of notifications
                notificationsList.scrollTop = 0;
            });
        }
    });
    
    // Initial render
    renderNotifications();
});
