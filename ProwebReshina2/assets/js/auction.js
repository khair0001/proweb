// Auction page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const featuredAuctionsContainer = document.getElementById('featuredAuctions');
    const auctionsGrid = document.getElementById('auctionsGrid');
    const auctionCount = document.getElementById('auctionCount');
    const sortSelect = document.getElementById('sortAuctions');
    const categoryFilters = document.querySelectorAll('.filter-list a[data-filter]');
    const statusFilters = document.querySelectorAll('.filter-list a[data-status]');
    const locationFilter = document.getElementById('locationFilter');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const applyPriceFilterBtn = document.getElementById('applyPriceFilter');
    const paginationContainer = document.getElementById('auctionsPagination');
    
    // Filter and sort state
    let filters = {
        category: 'all',
        status: 'all',
        location: '',
        minPrice: 0,
        maxPrice: Infinity
    };
    
    let currentSort = 'ending-soon';
    let currentPage = 1;
    const auctionsPerPage = 6;
    
    // Initialize auction page
    function initAuctionPage() {
        // Check URL parameters for initial filters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('category')) {
            filters.category = urlParams.get('category');
            setActiveFilter(categoryFilters, filters.category);
        }
        
        if (urlParams.has('status')) {
            filters.status = urlParams.get('status');
            setActiveFilter(statusFilters, filters.status);
        }
        
        if (urlParams.has('sort')) {
            currentSort = urlParams.get('sort');
            sortSelect.value = currentSort;
        }
        
        // Format price inputs
        minPriceInput.addEventListener('input', formatPriceInput);
        maxPriceInput.addEventListener('input', formatPriceInput);
        
        // Add event listeners
        sortSelect.addEventListener('change', handleSortChange);
        applyPriceFilterBtn.addEventListener('click', handlePriceFilter);
        locationFilter.addEventListener('change', handleLocationFilter);
        
        // Add category filter click events
        categoryFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const category = this.dataset.filter;
                setActiveFilter(categoryFilters, category);
                filters.category = category;
                currentPage = 1;
                filterAndRenderAuctions();
            });
        });
        
        // Add status filter click events
        statusFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const status = this.dataset.status;
                setActiveFilter(statusFilters, status);
                filters.status = status;
                currentPage = 1;
                filterAndRenderAuctions();
            });
        });
        
        // Initial render
        filterAndRenderAuctions();
    }
    
    // Set active filter
    function setActiveFilter(filterElements, value) {
        filterElements.forEach(filter => {
            filter.classList.remove('active');
            if (filter.dataset.filter === value || filter.dataset.status === value) {
                filter.classList.add('active');
            }
        });
    }
    
    // Format price input
    function formatPriceInput() {
        // Remove all non-digit characters
        let value = this.value.replace(/\D/g, '');
        
        // Format with thousand separators
        if (value !== '') {
            value = parseInt(value, 10).toLocaleString('id-ID');
        }
        
        // Update the input value
        this.value = value;
    }
    
    // Handle sort change
    function handleSortChange() {
        currentSort = this.value;
        currentPage = 1;
        filterAndRenderAuctions();
    }
    
    // Handle price filter
    function handlePriceFilter() {
        // Parse min price
        let minPrice = minPriceInput.value.replace(/\D/g, '');
        filters.minPrice = minPrice ? parseInt(minPrice, 10) : 0;
        
        // Parse max price
        let maxPrice = maxPriceInput.value.replace(/\D/g, '');
        filters.maxPrice = maxPrice ? parseInt(maxPrice, 10) : Infinity;
        
        currentPage = 1;
        filterAndRenderAuctions();
    }
    
    // Handle location filter
    function handleLocationFilter() {
        filters.location = this.value;
        currentPage = 1;
        filterAndRenderAuctions();
    }
    
    // Filter and render auctions
    function filterAndRenderAuctions() {
        // Apply filters
        let filteredAuctions = sampleAuctions.filter(auction => {
            // Filter by category
            if (filters.category !== 'all' && auction.category !== filters.category) {
                return false;
            }
            
            // Filter by status
            if (filters.status !== 'all') {
                const now = new Date();
                const endTime = new Date(auction.endTime);
                const timeRemaining = endTime - now;
                
                if (filters.status === 'active' && timeRemaining <= 0) {
                    return false;
                }
                
                if (filters.status === 'ending-soon' && (timeRemaining <= 0 || timeRemaining > 24 * 60 * 60 * 1000)) {
                    return false;
                }
                
                if (filters.status === 'ended' && timeRemaining > 0) {
                    return false;
                }
            }
            
            // Filter by location
            if (filters.location && !auction.location.includes(filters.location)) {
                return false;
            }
            
            // Filter by price
            if (auction.currentBid < filters.minPrice || auction.currentBid > filters.maxPrice) {
                return false;
            }
            
            return true;
        });
        
        // Sort auctions
        filteredAuctions = sortAuctions(filteredAuctions, currentSort);
        
        // Update auction count
        auctionCount.textContent = filteredAuctions.length;
        
        // Render featured auctions
        renderFeaturedAuctions(filteredAuctions.filter(auction => auction.featured));
        
        // Paginate auctions
        const totalPages = Math.ceil(filteredAuctions.length / auctionsPerPage);
        const startIndex = (currentPage - 1) * auctionsPerPage;
        const paginatedAuctions = filteredAuctions.slice(startIndex, startIndex + auctionsPerPage);
        
        // Render auctions
        renderAuctions(paginatedAuctions);
        
        // Render pagination
        renderPagination(totalPages);
    }
    
    // Sort auctions
    function sortAuctions(auctions, sortType) {
        const sortedAuctions = [...auctions];
        const now = new Date();
        
        switch (sortType) {
            case 'ending-soon':
                return sortedAuctions.sort((a, b) => {
                    const timeA = new Date(a.endTime) - now;
                    const timeB = new Date(b.endTime) - now;
                    
                    // Put ended auctions at the end
                    if (timeA <= 0 && timeB > 0) return 1;
                    if (timeA > 0 && timeB <= 0) return -1;
                    
                    return timeA - timeB;
                });
            case 'newest':
                // Already sorted by newest in the sample data
                return sortedAuctions;
            case 'price-low':
                return sortedAuctions.sort((a, b) => a.currentBid - b.currentBid);
            case 'price-high':
                return sortedAuctions.sort((a, b) => b.currentBid - a.currentBid);
            case 'most-bids':
                return sortedAuctions.sort((a, b) => b.bidCount - a.bidCount);
            default:
                return sortedAuctions;
        }
    }
    
    // Render featured auctions
    function renderFeaturedAuctions(featuredAuctions) {
        featuredAuctionsContainer.innerHTML = '';
        
        if (featuredAuctions.length === 0) {
            featuredAuctionsContainer.innerHTML = `
                <div class="no-auctions">
                    <i class="fas fa-search"></i>
                    <h3>Tidak ada lelang unggulan yang ditemukan</h3>
                </div>
            `;
            return;
        }
        
        // Take only the first 2 featured auctions
        const displayedFeatured = featuredAuctions.slice(0, 2);
        
        displayedFeatured.forEach(auction => {
            featuredAuctionsContainer.appendChild(createFeaturedAuctionCard(auction));
        });
    }
    
    // Render auctions
    function renderAuctions(auctions) {
        auctionsGrid.innerHTML = '';
        
        if (auctions.length === 0) {
            auctionsGrid.innerHTML = `
                <div class="no-auctions">
                    <i class="fas fa-search"></i>
                    <h3>Tidak ada lelang yang ditemukan</h3>
                    <p>Coba ubah filter pencarian Anda</p>
                </div>
            `;
            return;
        }
        
        auctions.forEach(auction => {
            auctionsGrid.appendChild(createAuctionCard(auction));
        });
    }
    
    // Create featured auction card element
    function createFeaturedAuctionCard(auction) {
        const card = document.createElement('div');
        card.className = 'featured-auction';
        
        // Calculate time remaining
        const endTime = new Date(auction.endTime);
        const now = new Date();
        const timeRemaining = getTimeRemaining(endTime, now);
        
        card.innerHTML = `
            <div class="featured-image">
                <img src="${auction.image}" alt="${auction.title}">
                <div class="featured-badge">Lelang Unggulan</div>
            </div>
            <div class="featured-details">
                <div class="featured-timer"><i class="far fa-clock"></i> ${timeRemaining}</div>
                <a href="product-detail.html?id=${auction.id}" class="featured-link">
                    <h3 class="featured-title">${auction.title}</h3>
                </a>
                <div class="featured-price">Rp ${auction.currentBid.toLocaleString('id-ID')}</div>
                <div class="featured-location"><i class="fas fa-map-marker-alt"></i> ${auction.location}</div>
                <div class="featured-bids">
                    <div>Penawaran: <span>${auction.bidCount}</span></div>
                    <div>Sisa waktu: <span>${timeRemaining}</span></div>
                </div>
                <a href="product-detail.html?id=${auction.id}" class="featured-btn">Lihat Detail</a>
            </div>
        `;
        
        return card;
    }
    
    // Create auction card element
    function createAuctionCard(auction) {
        const card = document.createElement('div');
        card.className = 'auction-card';
        
        // Calculate time remaining
        const endTime = new Date(auction.endTime);
        const now = new Date();
        const timeRemaining = getTimeRemaining(endTime, now);
        
        card.innerHTML = `
            <a href="product-detail.html?id=${auction.id}" class="auction-link">
                <div class="auction-image">
                    <img src="${auction.image}" alt="${auction.title}">
                    <div class="auction-badge">Lelang</div>
                </div>
                <div class="auction-details">
                    <div class="auction-timer">${timeRemaining}</div>
                    <h3 class="auction-title">${auction.title}</h3>
                    <div class="auction-price">Rp ${auction.currentBid.toLocaleString('id-ID')}</div>
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
    
    // Render pagination
    function renderPagination(totalPages) {
        paginationContainer.innerHTML = '';
        
        if (totalPages <= 1) {
            return;
        }
        
        // Previous button
        const prevBtn = document.createElement('button');
        prevBtn.className = 'pagination-btn';
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.disabled = currentPage === 1;
        prevBtn.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                filterAndRenderAuctions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
        paginationContainer.appendChild(prevBtn);
        
        // Page buttons
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `pagination-btn ${i === currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.addEventListener('click', () => {
                currentPage = i;
                filterAndRenderAuctions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            paginationContainer.appendChild(pageBtn);
        }
        
        // Next button
        const nextBtn = document.createElement('button');
        nextBtn.className = 'pagination-btn';
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.addEventListener('click', () => {
            if (currentPage < totalPages) {
                currentPage++;
                filterAndRenderAuctions();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
        paginationContainer.appendChild(nextBtn);
    }
    
    // Initialize the page
    initAuctionPage();
});
