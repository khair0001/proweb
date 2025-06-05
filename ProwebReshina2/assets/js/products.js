// Products page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // DOM elements
    const productsGrid = document.getElementById('productsGrid');
    const productCount = document.getElementById('productCount');
    const sortSelect = document.getElementById('sortProducts');
    const categoryFilters = document.querySelectorAll('.filter-list a[data-filter]');
    const typeFilters = document.querySelectorAll('.filter-list a[data-type]');
    const locationFilter = document.getElementById('locationFilter');
    const minPriceInput = document.getElementById('minPrice');
    const maxPriceInput = document.getElementById('maxPrice');
    const applyPriceFilterBtn = document.getElementById('applyPriceFilter');
    const paginationContainer = document.getElementById('productsPagination');
    
    // Filter and sort state
    let filters = {
        category: 'all',
        type: 'all',
        location: '',
        minPrice: 0,
        maxPrice: Infinity
    };
    
    let currentSort = 'newest';
    let currentPage = 1;
    const productsPerPage = 9;
    
    // Initialize products page
    function initProductsPage() {
        // Check URL parameters for initial filters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('category')) {
            filters.category = urlParams.get('category');
            setActiveFilter(categoryFilters, filters.category);
        }
        
        if (urlParams.has('type')) {
            filters.type = urlParams.get('type');
            setActiveFilter(typeFilters, filters.type);
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
                filterAndRenderProducts();
            });
        });
        
        // Add type filter click events
        typeFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                const type = this.dataset.type;
                setActiveFilter(typeFilters, type);
                filters.type = type;
                currentPage = 1;
                filterAndRenderProducts();
            });
        });
        
        // Initial render
        filterAndRenderProducts();
    }
    
    // Set active filter
    function setActiveFilter(filterElements, value) {
        filterElements.forEach(filter => {
            filter.classList.remove('active');
            if (filter.dataset.filter === value || filter.dataset.type === value) {
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
        filterAndRenderProducts();
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
        filterAndRenderProducts();
    }
    
    // Handle location filter
    function handleLocationFilter() {
        filters.location = this.value;
        currentPage = 1;
        filterAndRenderProducts();
    }
    
    // Filter and render products
    function filterAndRenderProducts() {
        // Apply filters
        let filteredProducts = sampleProducts.filter(product => {
            // Filter by category
            if (filters.category !== 'all' && product.category !== filters.category) {
                return false;
            }
            
            // Filter by type
            if (filters.type !== 'all' && product.type !== filters.type) {
                return false;
            }
            
            // Filter by location
            if (filters.location && !product.location.includes(filters.location)) {
                return false;
            }
            
            // Filter by price
            const productPrice = product.type === 'auction' ? product.currentBid : product.price;
            if (productPrice < filters.minPrice || productPrice > filters.maxPrice) {
                return false;
            }
            
            return true;
        });
        
        // Sort products
        filteredProducts = sortProducts(filteredProducts, currentSort);
        
        // Update product count
        productCount.textContent = filteredProducts.length;
        
        // Paginate products
        const totalPages = Math.ceil(filteredProducts.length / productsPerPage);
        const startIndex = (currentPage - 1) * productsPerPage;
        const paginatedProducts = filteredProducts.slice(startIndex, startIndex + productsPerPage);
        
        // Render products
        renderProducts(paginatedProducts);
        
        // Render pagination
        renderPagination(totalPages);
    }
    
    // Sort products
    function sortProducts(products, sortType) {
        const sortedProducts = [...products];
        
        switch (sortType) {
            case 'newest':
                // Already sorted by newest in the sample data
                return sortedProducts;
            case 'oldest':
                return sortedProducts.reverse();
            case 'price-low':
                return sortedProducts.sort((a, b) => {
                    const priceA = a.type === 'auction' ? a.currentBid : a.price;
                    const priceB = b.type === 'auction' ? b.currentBid : b.price;
                    return priceA - priceB;
                });
            case 'price-high':
                return sortedProducts.sort((a, b) => {
                    const priceA = a.type === 'auction' ? a.currentBid : a.price;
                    const priceB = b.type === 'auction' ? b.currentBid : b.price;
                    return priceB - priceA;
                });
            default:
                return sortedProducts;
        }
    }
    
    // Render products
    function renderProducts(products) {
        productsGrid.innerHTML = '';
        
        if (products.length === 0) {
            productsGrid.innerHTML = `
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>Tidak ada produk yang ditemukan</h3>
                    <p>Coba ubah filter pencarian Anda</p>
                </div>
            `;
            return;
        }
        
        products.forEach(product => {
            if (product.type === 'auction') {
                productsGrid.appendChild(createAuctionCard(product));
            } else if (product.type === 'donation') {
                productsGrid.appendChild(createDonationCard(product));
            } else {
                productsGrid.appendChild(createProductCard(product));
            }
        });
    }
    
    // Create product card element
    function createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        
        card.innerHTML = `
            <a href="product-detail.html?id=${product.id}" class="product-link">
                <div class="product-image">
                    <img src="${product.image}" alt="${product.title}">
                    <div class="product-badge sale">Dijual</div>
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
                filterAndRenderProducts();
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
                filterAndRenderProducts();
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
                filterAndRenderProducts();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
        paginationContainer.appendChild(nextBtn);
    }
    
    // Initialize the page
    initProductsPage();
});
