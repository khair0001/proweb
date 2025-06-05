/**
 * index.js - JavaScript for the index page
 * Handles hero slider, testimonials, and loading dynamic content
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initHeroSlider();
    initTestimonialSlider();
    loadFeaturedProducts();
    loadLatestAuctions();
    loadLatestDonations();
});

/**
 * Initialize hero slider
 */
function initHeroSlider() {
    const slides = document.querySelectorAll('.hero-slide');
    const indicators = document.querySelectorAll('.hero-indicators .indicator');
    const prevBtn = document.querySelector('.hero-prev');
    const nextBtn = document.querySelector('.hero-next');
    let currentSlide = 0;
    let slideInterval;

    // Start automatic slider
    startSlideInterval();

    // Previous button click
    prevBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        currentSlide = (currentSlide === 0) ? slides.length - 1 : currentSlide - 1;
        updateSlider();
        startSlideInterval();
    });

    // Next button click
    nextBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
        updateSlider();
        startSlideInterval();
    });

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
            clearInterval(slideInterval);
            currentSlide = index;
            updateSlider();
            startSlideInterval();
        });
    });

    // Update slider
    function updateSlider() {
        slides.forEach((slide, index) => {
            slide.classList.remove('active');
            if (index === currentSlide) {
                slide.classList.add('active');
            }
        });

        indicators.forEach((indicator, index) => {
            indicator.classList.remove('active');
            if (index === currentSlide) {
                indicator.classList.add('active');
            }
        });
    }

    // Start automatic slider interval
    function startSlideInterval() {
        slideInterval = setInterval(function() {
            currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
            updateSlider();
        }, 5000);
    }
}

/**
 * Initialize testimonial slider
 */
function initTestimonialSlider() {
    const slides = document.querySelectorAll('.testimonial-slide');
    const indicators = document.querySelectorAll('.testimonial-indicators .indicator');
    const prevBtn = document.querySelector('.testimonial-prev');
    const nextBtn = document.querySelector('.testimonial-next');
    let currentSlide = 0;
    let slideInterval;

    // Start automatic slider
    startSlideInterval();

    // Previous button click
    prevBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        currentSlide = (currentSlide === 0) ? slides.length - 1 : currentSlide - 1;
        updateSlider();
        startSlideInterval();
    });

    // Next button click
    nextBtn.addEventListener('click', function() {
        clearInterval(slideInterval);
        currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
        updateSlider();
        startSlideInterval();
    });

    // Indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', function() {
            clearInterval(slideInterval);
            currentSlide = index;
            updateSlider();
            startSlideInterval();
        });
    });

    // Update slider
    function updateSlider() {
        slides.forEach((slide, index) => {
            slide.classList.remove('active');
            if (index === currentSlide) {
                slide.classList.add('active');
            }
        });

        indicators.forEach((indicator, index) => {
            indicator.classList.remove('active');
            if (index === currentSlide) {
                indicator.classList.add('active');
            }
        });
    }

    // Start automatic slider interval
    function startSlideInterval() {
        slideInterval = setInterval(function() {
            currentSlide = (currentSlide === slides.length - 1) ? 0 : currentSlide + 1;
            updateSlider();
        }, 6000);
    }
}

/**
 * Load featured products
 */
function loadFeaturedProducts() {
    const featuredProductsContainer = document.getElementById('featuredProducts');

    
    // Clear loading indicator
    featuredProductsContainer.innerHTML = '';
    
    // Create product cards
    sampleProducts.forEach(product => {
        const productCard = createProductCard(product);
        featuredProductsContainer.appendChild(productCard);
    });
}

/**
 * Create product card element
 * @param {Object} product - Product data
 * @returns {HTMLElement} - Product card element
 */
function createProductCard(product) {
    const card = document.createElement('div');
    card.className = 'product-card';
    
    card.innerHTML = `
        <a href="pages/product-detail.php?id=${product.id}" class="product-link">
            <div class="product-image">
                <img src="${product.image}" alt="${product.title}">
                <div class="product-badge">${product.category}</div>
            </div>
            <div class="product-details">
                <h3 class="product-title">${product.title}</h3>
                <div class="product-price">Rp ${formatPrice(product.price)}</div>
                <div class="product-location"><i class="fas fa-map-marker-alt"></i> ${product.location}</div>
                <div class="product-time"><i class="far fa-clock"></i> ${product.time}</div>
            </div>
        </a>
    `;
    
    return card;
}

/**
 * Load latest auctions
 */
function loadLatestAuctions() {
    const latestAuctionsContainer = document.getElementById('latestAuctions');
    
    // In a real application, this would be an AJAX call to the server
    // For demonstration, we'll use sample data
    const sampleAuctions = [
        {
            id: 7,
            title: 'Sepeda Lipat Polygon',
            price: 3000000,
            location: 'Yogyakarta, DIY',
            image: 'assets/image/product-7.jpg',
            time: '12 jam yang lalu',
            category: 'olahraga',
            endTime: '2025-06-15T14:30:00',
            currentBid: 3000000,
            bidCount: 5
        },
        {
            id: 10,
            title: 'Jam Tangan Fossil Original',
            price: 1200000,
            location: 'Jakarta Pusat, DKI Jakarta',
            image: 'assets/image/product-10.jpg',
            time: '2 hari yang lalu',
            category: 'fashion',
            endTime: '2025-06-10T18:00:00',
            currentBid: 1200000,
            bidCount: 3
        },
        {
            id: 14,
            title: 'Lukisan Kanvas Pemandangan',
            price: 2500000,
            location: 'Yogyakarta, DIY',
            image: 'assets/image/product-14.jpg',
            time: '4 hari yang lalu',
            category: 'lainnya',
            endTime: '2025-06-20T12:00:00',
            currentBid: 2500000,
            bidCount: 8
        }
    ];
    
    // Clear loading indicator
    latestAuctionsContainer.innerHTML = '';
    
    // Create auction cards
    sampleAuctions.forEach(auction => {
        const auctionCard = createAuctionCard(auction);
        latestAuctionsContainer.appendChild(auctionCard);
    });
}

/**
 * Create auction card element
 * @param {Object} auction - Auction data
 * @returns {HTMLElement} - Auction card element
 */
function createAuctionCard(auction) {
    const card = document.createElement('div');
    card.className = 'auction-card';
    
    // Calculate time remaining
    const endTime = new Date(auction.endTime);
    const now = new Date();
    const timeRemaining = getTimeRemaining(endTime, now);
    
    card.innerHTML = `
        <a href="pages/product-detail.php?id=${auction.id}" class="auction-link">
            <div class="auction-image">
                <img src="${auction.image}" alt="${auction.title}">
                <div class="auction-badge">Lelang</div>
            </div>
            <div class="auction-details">
                <div class="auction-timer">${timeRemaining}</div>
                <h3 class="auction-title">${auction.title}</h3>
                <div class="auction-price">Rp ${formatPrice(auction.currentBid)}</div>
                <div class="auction-location"><i class="fas fa-map-marker-alt"></i> ${auction.location}</div>
                <div class="auction-bids">
                    <div>Penawaran: <span>${auction.bidCount}</span></div>
                    <div>Sisa waktu: <span>${timeRemaining}</span></div>
                </div>
            </div>
        </a>
    `;
    
    return card;
}

/**
 * Get formatted time remaining
 * @param {Date} endTime - End time
 * @param {Date} now - Current time
 * @returns {string} - Formatted time remaining
 */
function getTimeRemaining(endTime, now) {
    const totalSeconds = Math.floor((endTime - now) / 1000);
    
    if (totalSeconds <= 0) {
        return 'Lelang berakhir';
    }
    
    const days = Math.floor(totalSeconds / (24 * 60 * 60));
    const hours = Math.floor((totalSeconds % (24 * 60 * 60)) / (60 * 60));
    const minutes = Math.floor((totalSeconds % (60 * 60)) / 60);
    
    if (days > 0) {
        return `${days} hari ${hours} jam`;
    } else if (hours > 0) {
        return `${hours} jam ${minutes} menit`;
    } else {
        return `${minutes} menit`;
    }
}

/**
 * Load latest donations
 */
function loadLatestDonations() {
    const latestDonationsContainer = document.getElementById('latestDonations');
    
    // In a real application, this would be an AJAX call to the server
    // For demonstration, we'll use sample data
    const sampleDonations = [
        {
            id: 6,
            title: 'Buku Novel Harry Potter Set',
            location: 'Bandung, Jawa Barat',
            image: 'assets/image/product-6.jpg',
            time: '10 jam yang lalu',
            category: 'buku'
        },
        {
            id: 12,
            title: 'Pakaian Anak Bekas Layak Pakai',
            location: 'Semarang, Jawa Tengah',
            image: 'assets/image/product-12.jpg',
            time: '3 hari yang lalu',
            category: 'fashion'
        },
        {
            id: 15,
            title: 'Mainan Edukasi Anak',
            location: 'Jakarta Timur, DKI Jakarta',
            image: 'assets/image/product-15.jpg',
            time: '5 hari yang lalu',
            category: 'lainnya'
        }
    ];
    
    // Clear loading indicator
    latestDonationsContainer.innerHTML = '';
    
    // Create donation cards
    sampleDonations.forEach(donation => {
        const donationCard = createDonationCard(donation);
        latestDonationsContainer.appendChild(donationCard);
    });
}

/**
 * Create donation card element
 * @param {Object} donation - Donation data
 * @returns {HTMLElement} - Donation card element
 */
function createDonationCard(donation) {
    const card = document.createElement('div');
    card.className = 'donation-card';
    
    card.innerHTML = `
        <a href="pages/product-detail.php?id=${donation.id}" class="donation-link">
            <div class="donation-image">
                <img src="${donation.image}" alt="${donation.title}">
                <div class="donation-badge">Donasi</div>
            </div>
            <div class="donation-details">
                <h3 class="donation-title">${donation.title}</h3>
                <div class="donation-location"><i class="fas fa-map-marker-alt"></i> ${donation.location}</div>
                <div class="donation-time"><i class="far fa-clock"></i> ${donation.time}</div>
                <div class="donation-category"><i class="fas fa-tag"></i> ${donation.category}</div>
                <div class="donation-claim-btn">Klaim Donasi</div>
            </div>
        </a>
    `;
    
    return card;
}

/**
 * Format price with thousand separator
 * @param {number} price - Price to format
 * @returns {string} - Formatted price
 */
function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
