// Search Results page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sample product data (in a real application, this would come from a server)
    const sampleProducts = [
        {
            id: 1,
            title: 'Smartphone Samsung Galaxy A52',
            price: 2500000,
            image: '../assets/image/product-1.jpg',
            distance: 1.2,
            type: 'sell',
            category: 'elektronik',
            condition: 'seperti_baru',
            location: 'jakarta'
        },
        {
            id: 2,
            title: 'Sepatu Lari Nike Air Zoom',
            price: 800000,
            image: '../assets/image/product-2.jpg',
            distance: 2.5,
            type: 'sell',
            category: 'olahraga',
            condition: 'baik',
            location: 'bandung'
        },
        {
            id: 3,
            title: 'Buku Novel Harry Potter Set',
            price: 0,
            image: '../assets/image/product-3.jpg',
            distance: 3.7,
            type: 'donate',
            category: 'buku',
            condition: 'cukup_baik',
            location: 'jakarta'
        },
        {
            id: 4,
            title: 'Meja Kerja Minimalis',
            price: 1200000,
            image: '../assets/image/product-4.jpg',
            distance: 5.1,
            type: 'auction',
            category: 'furniture',
            condition: 'baik',
            location: 'surabaya'
        },
        {
            id: 5,
            title: 'Kamera DSLR Canon EOS 700D',
            price: 4500000,
            image: '../assets/image/product-5.jpg',
            distance: 0.8,
            type: 'sell',
            category: 'elektronik',
            condition: 'baik',
            location: 'yogyakarta'
        },
        {
            id: 6,
            title: 'Jaket Denim Uniqlo',
            price: 350000,
            image: '../assets/image/product-6.jpg',
            distance: 4.2,
            type: 'sell',
            category: 'pakaian',
            condition: 'seperti_baru',
            location: 'bandung'
        },
        {
            id: 7,
            title: 'Mainan Edukasi Anak',
            price: 0,
            image: '../assets/image/product-7.jpg',
            distance: 2.9,
            type: 'donate',
            category: 'lainnya',
            condition: 'baik',
            location: 'jakarta'
        },
        {
            id: 8,
            title: 'Sepeda Lipat Polygon',
            price: 3000000,
            image: '../assets/image/product-8.jpg',
            distance: 1.5,
            type: 'auction',
            category: 'olahraga',
            condition: 'baik',
            location: 'surabaya'
        }
    ];

    // Function to render products
    function renderProducts(products) {
        const resultsContainer = document.getElementById('resultsContainer');
        resultsContainer.innerHTML = '';

        if (products.length === 0) {
            resultsContainer.innerHTML = '<p class="no-results">Tidak ada hasil yang ditemukan.</p>';
            return;
        }

        products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            
            // Format price based on transaction type
            let priceDisplay;
            if (product.type === 'donate') {
                priceDisplay = 'Donasi';
            } else if (product.type === 'auction') {
                priceDisplay = `Rp ${product.price.toLocaleString('id-ID')} (Lelang)`;
            } else {
                priceDisplay = `Rp ${product.price.toLocaleString('id-ID')}`;
            }
            
            // Set type label
            let typeLabel, typeClass;
            if (product.type === 'sell') {
                typeLabel = 'Jual';
                typeClass = 'sell';
            } else if (product.type === 'donate') {
                typeLabel = 'Donasi';
                typeClass = 'donate';
            } else {
                typeLabel = 'Lelang';
                typeClass = 'auction';
            }
            
            // Create placeholder image URL if the image doesn't exist
            const imageSrc = product.image || 'https://via.placeholder.com/300x200?text=No+Image';
            
            productCard.innerHTML = `
                <div class="product-image">
                    <img src="${imageSrc}" alt="${product.title}">
                </div>
                <div class="product-info">
                    <h3 class="product-title">${product.title}</h3>
                    <div class="product-price">${priceDisplay}</div>
                    <div class="product-meta">
                        <div class="product-distance">
                            <i class="fas fa-map-marker-alt"></i> ${product.distance} km
                        </div>
                        <div class="product-type ${typeClass}">
                            ${typeLabel}
                        </div>
                    </div>
                </div>
            `;
            
            productCard.addEventListener('click', () => {
                window.location.href = `product-detail.html?id=${product.id}`;
            });
            
            resultsContainer.appendChild(productCard);
        });
    }

    // Initial render
    renderProducts(sampleProducts);

    // Filter functionality
    const applyFiltersBtn = document.getElementById('applyFilters');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const sortBySelect = document.getElementById('sortBy');

    applyFiltersBtn.addEventListener('click', function() {
        // Get filter values
        const selectedCategories = Array.from(document.querySelectorAll('input[name="category"]:checked'))
            .map(input => input.value);
        
        const selectedLocation = document.getElementById('locationFilter').value;
        
        const selectedCondition = document.querySelector('input[name="condition"]:checked')?.value;
        
        const minPrice = document.getElementById('minPrice').value ? 
            parseInt(document.getElementById('minPrice').value) : 0;
        
        const maxPrice = document.getElementById('maxPrice').value ? 
            parseInt(document.getElementById('maxPrice').value) : Infinity;
        
        const selectedTransactionTypes = Array.from(document.querySelectorAll('input[name="transaction"]:checked'))
            .map(input => input.value);
        
        // Filter products
        let filteredProducts = sampleProducts.filter(product => {
            // Category filter
            if (selectedCategories.length > 0 && !selectedCategories.includes(product.category)) {
                return false;
            }
            
            // Location filter
            if (selectedLocation && product.location !== selectedLocation) {
                return false;
            }
            
            // Condition filter
            if (selectedCondition && product.condition !== selectedCondition) {
                return false;
            }
            
            // Price filter
            if (product.price < minPrice || product.price > maxPrice) {
                return false;
            }
            
            // Transaction type filter
            if (selectedTransactionTypes.length > 0 && !selectedTransactionTypes.includes(product.type)) {
                return false;
            }
            
            return true;
        });
        
        // Sort products
        const sortBy = sortBySelect.value;
        sortProducts(filteredProducts, sortBy);
        
        // Render filtered products
        renderProducts(filteredProducts);
    });

    resetFiltersBtn.addEventListener('click', function() {
        // Reset all filter inputs
        document.querySelectorAll('input[type="checkbox"]').forEach(input => {
            input.checked = false;
        });
        
        document.querySelectorAll('input[type="radio"]').forEach(input => {
            input.checked = false;
        });
        
        document.getElementById('locationFilter').value = '';
        document.getElementById('minPrice').value = '';
        document.getElementById('maxPrice').value = '';
        
        // Reset to default sort
        sortBySelect.value = 'relevance';
        
        // Render all products
        renderProducts(sampleProducts);
    });

    sortBySelect.addEventListener('change', function() {
        const sortBy = this.value;
        const currentProducts = Array.from(document.querySelectorAll('.product-card')).map(card => {
            const id = parseInt(card.querySelector('a')?.href.split('id=')[1] || '0');
            return sampleProducts.find(product => product.id === id) || sampleProducts[0];
        });
        
        sortProducts(currentProducts, sortBy);
        renderProducts(currentProducts);
    });

    function sortProducts(products, sortBy) {
        switch (sortBy) {
            case 'price_low':
                products.sort((a, b) => a.price - b.price);
                break;
            case 'price_high':
                products.sort((a, b) => b.price - a.price);
                break;
            case 'distance':
                products.sort((a, b) => a.distance - b.distance);
                break;
            case 'newest':
                // In a real app, you would sort by date
                // Here we'll just reverse the array to simulate newest first
                products.reverse();
                break;
            default:
                // 'relevance' - no specific sorting
                break;
        }
    }

    // Search functionality
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');

    searchForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        if (!searchTerm) {
            renderProducts(sampleProducts);
            return;
        }
        
        const searchResults = sampleProducts.filter(product => 
            product.title.toLowerCase().includes(searchTerm) || 
            product.category.toLowerCase().includes(searchTerm)
        );
        
        renderProducts(searchResults);
    });

    // Pagination (simplified)
    const paginationButtons = document.querySelectorAll('.pagination-btn');
    
    paginationButtons.forEach(button => {
        if (!button.disabled) {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                paginationButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // In a real app, you would load the appropriate page of results
                // For this demo, we'll just show the same products
                renderProducts(sampleProducts);
                
                // Scroll to top of results
                document.querySelector('.results-header').scrollIntoView({ behavior: 'smooth' });
            });
        }
    });
});
