/**
 * Base JavaScript for ProwebReshina2
 * Contains common functionality used across all pages
 */

// Document Ready Function
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all common components
    initDropdowns();
    initMobileMenu();
    initBackToTop();
    initTooltips();
    initFormValidation();
});

/**
 * Initialize dropdown menus
 */
function initDropdowns() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                if (dropdown !== menu) {
                    dropdown.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            menu.classList.toggle('active');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown-menu') && !e.target.closest('.dropdown-toggle')) {
            document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileNavClose = document.querySelector('.mobile-nav-close');
    const mobileNav = document.querySelector('.mobile-nav');
    
    if (mobileMenuBtn && mobileNavClose && mobileNav) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileNav.classList.add('active');
            document.body.classList.add('no-scroll');
        });
        
        mobileNavClose.addEventListener('click', function() {
            mobileNav.classList.remove('active');
            document.body.classList.remove('no-scroll');
        });
    }
}

/**
 * Initialize back to top button
 */
function initBackToTop() {
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        // Scroll to top when clicked
        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const text = this.getAttribute('data-tooltip');
            
            if (!text) return;
            
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = text;
            
            document.body.appendChild(tooltipEl);
            
            const rect = this.getBoundingClientRect();
            const tooltipRect = tooltipEl.getBoundingClientRect();
            
            tooltipEl.style.top = `${rect.top - tooltipRect.height - 10 + window.scrollY}px`;
            tooltipEl.style.left = `${rect.left + (rect.width / 2) - (tooltipRect.width / 2)}px`;
            tooltipEl.style.opacity = '1';
            
            this.addEventListener('mouseleave', function onMouseLeave() {
                tooltipEl.remove();
                this.removeEventListener('mouseleave', onMouseLeave);
            });
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Get all required inputs
            const requiredInputs = form.querySelectorAll('[required]');
            
            requiredInputs.forEach(input => {
                // Remove existing error messages
                const existingError = input.parentElement.querySelector('.error-message');
                if (existingError) {
                    existingError.remove();
                }
                
                input.classList.remove('is-invalid');
                
                // Check if input is empty
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('is-invalid');
                    
                    // Add error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'error-message';
                    errorMessage.textContent = 'Field is required';
                    input.parentElement.appendChild(errorMessage);
                }
                
                // Email validation
                if (input.type === 'email' && input.value.trim()) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(input.value.trim())) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.textContent = 'Please enter a valid email address';
                        input.parentElement.appendChild(errorMessage);
                    }
                }
                
                // Password validation
                if (input.type === 'password' && input.dataset.minLength && input.value.trim()) {
                    const minLength = parseInt(input.dataset.minLength);
                    if (input.value.length < minLength) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.textContent = `Password must be at least ${minLength} characters`;
                        input.parentElement.appendChild(errorMessage);
                    }
                }
                
                // Password confirmation validation
                if (input.dataset.confirmPassword && input.value.trim()) {
                    const passwordInput = document.getElementById(input.dataset.confirmPassword);
                    if (passwordInput && input.value !== passwordInput.value) {
                        isValid = false;
                        input.classList.add('is-invalid');
                        
                        // Add error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'error-message';
                        errorMessage.textContent = 'Passwords do not match';
                        input.parentElement.appendChild(errorMessage);
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Format price with thousand separator
 * @param {number} price - Price to format
 * @returns {string} - Formatted price
 */
function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

/**
 * Format date to Indonesian format
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted date
 */
function formatDate(date) {
    const d = new Date(date);
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return d.toLocaleDateString('id-ID', options);
}

/**
 * Format time to Indonesian format
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted time
 */
function formatTime(date) {
    const d = new Date(date);
    const options = { hour: 'numeric', minute: 'numeric' };
    return d.toLocaleTimeString('id-ID', options);
}

/**
 * Format date and time to Indonesian format
 * @param {string|Date} date - Date to format
 * @returns {string} - Formatted date and time
 */
function formatDateTime(date) {
    const d = new Date(date);
    const options = { day: 'numeric', month: 'long', year: 'numeric', hour: 'numeric', minute: 'numeric' };
    return d.toLocaleDateString('id-ID', options);
}

/**
 * Get time ago from date
 * @param {string|Date} date - Date to format
 * @returns {string} - Time ago
 */
function timeAgo(date) {
    const d = new Date(date);
    const now = new Date();
    const seconds = Math.floor((now - d) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval >= 1) {
        return interval + ' tahun yang lalu';
    }
    
    interval = Math.floor(seconds / 2592000);
    if (interval >= 1) {
        return interval + ' bulan yang lalu';
    }
    
    interval = Math.floor(seconds / 604800);
    if (interval >= 1) {
        return interval + ' minggu yang lalu';
    }
    
    interval = Math.floor(seconds / 86400);
    if (interval >= 1) {
        return interval + ' hari yang lalu';
    }
    
    interval = Math.floor(seconds / 3600);
    if (interval >= 1) {
        return interval + ' jam yang lalu';
    }
    
    interval = Math.floor(seconds / 60);
    if (interval >= 1) {
        return interval + ' menit yang lalu';
    }
    
    return 'Baru saja';
}

/**
 * Truncate text to specified length
 * @param {string} text - Text to truncate
 * @param {number} length - Maximum length
 * @returns {string} - Truncated text
 */
function truncateText(text, length) {
    if (text.length <= length) {
        return text;
    }
    
    return text.substring(0, length) + '...';
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Create icon based on type
    const icon = document.createElement('i');
    switch (type) {
        case 'success':
            icon.className = 'fas fa-check-circle';
            break;
        case 'error':
            icon.className = 'fas fa-times-circle';
            break;
        case 'warning':
            icon.className = 'fas fa-exclamation-triangle';
            break;
        default:
            icon.className = 'fas fa-info-circle';
    }
    
    // Create message element
    const messageEl = document.createElement('span');
    messageEl.textContent = message;
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'notification-close';
    closeBtn.innerHTML = '&times;';
    
    // Add elements to notification
    notification.appendChild(icon);
    notification.appendChild(messageEl);
    notification.appendChild(closeBtn);
    
    // Add notification to container or create container if it doesn't exist
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    container.appendChild(notification);
    
    // Show notification with animation
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Hide notification after duration
    const timeout = setTimeout(() => {
        hideNotification(notification);
    }, duration);
    
    // Close notification on click
    closeBtn.addEventListener('click', () => {
        clearTimeout(timeout);
        hideNotification(notification);
    });
}

/**
 * Hide notification
 * @param {HTMLElement} notification - Notification element
 */
function hideNotification(notification) {
    notification.classList.remove('show');
    notification.classList.add('hide');
    
    // Remove notification after animation
    setTimeout(() => {
        notification.remove();
        
        // Remove container if empty
        const container = document.querySelector('.notification-container');
        if (container && container.children.length === 0) {
            container.remove();
        }
    }, 300);
}

/**
 * Make AJAX request
 * @param {string} url - Request URL
 * @param {string} method - Request method (GET, POST, PUT, DELETE)
 * @param {object} data - Request data
 * @param {function} callback - Callback function
 */
function ajax(url, method = 'GET', data = null, callback = null) {
    const xhr = new XMLHttpRequest();
    
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (callback) {
                        callback(null, response);
                    }
                } catch (error) {
                    if (callback) {
                        callback(error);
                    }
                }
            } else {
                if (callback) {
                    callback(new Error(`Request failed with status ${xhr.status}`));
                }
            }
        }
    };
    
    if (data) {
        xhr.send(JSON.stringify(data));
    } else {
        xhr.send();
    }
}
