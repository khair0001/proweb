// Product Detail page JavaScript
document.addEventListener('DOMContentLoaded', function() {    
    // Find the product by ID
    const product = products.find(p => p.id == productId) || products[0];
    
    // Update page title
    document.title = `${product.name} | ProwebReshina`;
    
    // Update product details in the DOM
    document.getElementById('productTitle').textContent = product.name;
    document.getElementById('productName').textContent = product.name;
    
    // Format price based on transaction type
    let priceDisplay;
    if (product.type === 'donate') {
        priceDisplay = 'Donasi';
    } else if (product.type === 'auction') {
        priceDisplay = `Rp ${product.price.toLocaleString('id-ID')} (Lelang)`;
    } else {
        priceDisplay = `Rp ${product.price.toLocaleString('id-ID')}`;
    }
    document.getElementById('productPrice').textContent = priceDisplay;
    
    // Set type label
    let typeLabel, typeClass;
    if (product.type === 'sell') {
        typeLabel = 'Jual';
        typeClass = 'sell';
        document.getElementById('actionButton').textContent = 'Beli Sekarang';
        document.getElementById('cartButton').style.display = 'block';
    } else if (product.type === 'donate') {
        typeLabel = 'Donasi';
        typeClass = 'donate';
        document.getElementById('actionButton').textContent = 'Klaim Donasi';
        document.getElementById('cartButton').style.display = 'none';
    } else {
        typeLabel = 'Lelang';
        typeClass = 'auction';
        document.getElementById('actionButton').textContent = 'Ikut Lelang';
        document.getElementById('cartButton').style.display = 'none';
    }
    
    const productTypeElement = document.getElementById('productType');
    productTypeElement.textContent = typeLabel;
    productTypeElement.className = `product-type ${typeClass}`;
    
    // Update other product details
    document.getElementById('productLocation').textContent = product.location;
    document.getElementById('productDistance').textContent = `(${product.distance} km dari lokasi Anda)`;
    document.getElementById('productCondition').textContent = product.condition;
    document.getElementById('productDescription').textContent = product.description;
    
    // Update seller info
    document.getElementById('sellerName').textContent = product.seller.name;
    
    // Update main image
    const mainImage = document.getElementById('mainImage');
    mainImage.src = product.images[0];
    mainImage.alt = product.name;
    
    // Generate thumbnails
    const thumbnailGallery = document.getElementById('thumbnailGallery');
    thumbnailGallery.innerHTML = '';
    
    product.images.forEach((image, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = `thumbnail ${index === 0 ? 'active' : ''}`;
        
        const img = document.createElement('img');
        img.src = image;
        img.alt = `${product.name} - Image ${index + 1}`;
        
        thumbnail.appendChild(img);
        thumbnailGallery.appendChild(thumbnail);
        
        // Add click event to switch main image
        thumbnail.addEventListener('click', function() {
            mainImage.src = image;
            mainImage.alt = `${product.name} - Image ${index + 1}`;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
    
    // Find similar products (same category but different ID)
    const similarProducts = products.filter(p => p.category === product.category && p.id !== product.id);
    
    // Render similar products
    const similarProductsContainer = document.getElementById('similarProductsContainer');
    similarProductsContainer.innerHTML = '';
    
    similarProducts.forEach(product => {
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
        
        productCard.innerHTML = `
            <div class="product-card-image">
                <img src="${product.images[0]}" alt="${product.name}">
            </div>
            <div class="product-card-info">
                <h3 class="product-card-title">${product.name}</h3>
                <div class="product-card-price">${priceDisplay}</div>
                <div class="product-card-meta">
                    <div class="product-distance">
                        <i class="fas fa-map-marker-alt"></i> ${product.distance} km
                    </div>
                    <div class="product-type ${product.type}">
                        ${product.type === 'sell' ? 'Jual' : product.type === 'donate' ? 'Donasi' : 'Lelang'}
                    </div>
                </div>
            </div>
        `;
        
        productCard.addEventListener('click', () => {
            window.location.href = `product-detail.html?id=${product.id}`;
        });
        
        similarProductsContainer.appendChild(productCard);
    });
    
    // Action button click event
    document.getElementById('actionButton').addEventListener('click', function() {
        if (product.type === 'sell') {
            window.location.href = `checkout.html?id=${product.id}`;
        } else if (product.type === 'donate') {
            window.location.href = `claim.html?id=${product.id}`;
        } else {
            window.location.href = `auction.html?id=${product.id}`;
        }
    });
    
    // Cart button click event
    if (document.getElementById('cartButton')) {
        document.getElementById('cartButton').addEventListener('click', function() {
            // In a real application, you would add the product to the cart
            alert('Produk telah ditambahkan ke keranjang');
        });
    }
});
