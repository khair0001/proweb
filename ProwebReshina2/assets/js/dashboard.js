// Dashboard page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Carousel functionality
    const carouselTrack = document.getElementById('carouselTrack');
    const slides = document.querySelectorAll('.carousel-slide');
    const indicators = document.querySelectorAll('.indicator');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    let currentSlide = 0;
    let slideInterval;

    // Initialize carousel
    function initCarousel() {
        // Show first slide
        showSlide(0);
        
        // Start automatic sliding
        startSlideInterval();
        
        // Add event listeners
        prevBtn.addEventListener('click', prevSlide);
        nextBtn.addEventListener('click', nextSlide);
        
        // Add indicator click events
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                resetSlideInterval();
            });
        });
        
        // Pause autoplay on hover
        carouselTrack.addEventListener('mouseenter', () => {
            clearInterval(slideInterval);
        });
        
        // Resume autoplay on mouse leave
        carouselTrack.addEventListener('mouseleave', () => {
            startSlideInterval();
        });
    }
    
    // Show slide by index
    function showSlide(index) {
        // Hide all slides
        slides.forEach(slide => {
            slide.classList.remove('active');
        });
        
        // Remove active class from all indicators
        indicators.forEach(indicator => {
            indicator.classList.remove('active');
        });
        
        // Show current slide and activate indicator
        slides[index].classList.add('active');
        indicators[index].classList.add('active');
        
        // Update current slide index
        currentSlide = index;
    }
    
    // Go to next slide
    function nextSlide() {
        let nextIndex = currentSlide + 1;
        if (nextIndex >= slides.length) {
            nextIndex = 0;
        }
        showSlide(nextIndex);
        resetSlideInterval();
    }
    
    // Go to previous slide
    function prevSlide() {
        let prevIndex = currentSlide - 1;
        if (prevIndex < 0) {
            prevIndex = slides.length - 1;
        }
        showSlide(prevIndex);
        resetSlideInterval();
    }
    
    // Start automatic sliding
    function startSlideInterval() {
        slideInterval = setInterval(nextSlide, 5000);
    }
    
    // Reset slide interval
    function resetSlideInterval() {
        clearInterval(slideInterval);
        startSlideInterval();
    }
    
    // Initialize carousel
    initCarousel();
    
    
    // Load latest products
    function loadLatestProducts() {
        const latestProductsContainer = document.getElementById('latestProducts');
        
        // Get all sale products
        const saleProducts = sampleProducts.filter(product => product.type === 'sale');
        
        // Get first 5 sale products
        const latestSaleProducts = saleProducts.slice(0, 5);
        
        // Render products
        latestProductsContainer.innerHTML = '';
        latestSaleProducts.forEach(product => {
            latestProductsContainer.appendChild(createProductCard(product));
        });
    }
    
    // Load popular products
    function loadPopularProducts() {
        const popularProductsContainer = document.getElementById('popularProducts');
        
        // Get all sale products
        const saleProducts = sampleProducts.filter(product => product.type === 'sale');
        
        // Get products 5-10 (simulating popular products)
        const popularSaleProducts = saleProducts.slice(5, 10);
        
        // Render products
        popularProductsContainer.innerHTML = '';
        popularSaleProducts.forEach(product => {
            popularProductsContainer.appendChild(createProductCard(product));
        });
    }
    
    // Load latest auctions
    function loadLatestAuctions() {
        const latestAuctionsContainer = document.getElementById('latestAuctions');
        
        // Get all auction products
        const auctionProducts = sampleProducts.filter(product => product.type === 'auction');
        
        // Render products
        latestAuctionsContainer.innerHTML = '';
        auctionProducts.forEach(product => {
            latestAuctionsContainer.appendChild(createAuctionCard(product));
        });
    }
    
    // Load latest donations
    function loadLatestDonations() {
        const latestDonationsContainer = document.getElementById('latestDonations');
        
        // Get all donation products
        const donationProducts = sampleProducts.filter(product => product.type === 'donation');
        
        // Render products
        latestDonationsContainer.innerHTML = '';
        donationProducts.forEach(product => {
            latestDonationsContainer.appendChild(createDonationCard(product));
        });
    }
    
    // Create product card element
    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        
        let badgeHTML = '';
        if (product.type === 'sale') {
            badgeHTML = '<div class="product-badge sale">Dijual</div>';
        }
        
        card.innerHTML = `
            <a href="product-detail.html?id=${product.id}" class="product-link">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.title}">
                    ${badgeHTML}
                </div>
                <div class="product-details">
                    <h3 class="product-title">${product.title}</h3>
                    <div class="product-price">Rp ${product.price.toLocaleString('id-ID')}</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> ${product.location}</div>
                    <div class="product-meta">
                        <div class="product-time"><i class="far fa-clock"></i> ${product.time}</div>
                    </div>
                </div>
            </a>
        `;
        
        return card;
    }
    
    // Create auction card element
    function createAuctionCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card auction-card';
        
        // Calculate time remaining
        const endTime = new Date(product.endTime);
        const now = new Date();
        const timeRemaining = getTimeRemaining(endTime, now);
        
        card.innerHTML = `
            <a href="product-detail.html?id=${product.id}" class="product-link">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.title}">
                    <div class="product-badge auction">Lelang</div>
                </div>
                <div class="product-details">
                    <div class="auction-timer">${timeRemaining}</div>
                    <h3 class="product-title">${product.title}</h3>
                    <div class="product-price">Rp ${product.currentBid.toLocaleString('id-ID')}</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> ${product.location}</div>
                    <div class="auction-bids">
                        <div>Penawaran: <span>${product.bidCount}</span></div>
                        <div>Sisa waktu: <span>${timeRemaining}</span></div>
                    </div>
                </div>
            </a>
        `;
        
        return card;
    }
    
    // Create donation card element
    function createDonationCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        
        card.innerHTML = `
            <a href="product-detail.html?id=${product.id}" class="product-link">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.title}">
                    <div class="product-badge donation">Donasi</div>
                </div>
                <div class="product-details">
                    <h3 class="product-title">${product.title}</h3>
                    <div class="product-price">Gratis</div>
                    <div class="product-location"><i class="fas fa-map-marker-alt"></i> ${product.location}</div>
                    <div class="product-meta">
                        <div class="product-time"><i class="far fa-clock"></i> ${product.time}</div>
                    </div>
                </div>
            </a>
        `;
        
        return card;
    }
    
    // Get formatted time remaining
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
    
    // Load all products
    loadLatestProducts();
    loadPopularProducts();
    loadLatestAuctions();
    loadLatestDonations();
});
