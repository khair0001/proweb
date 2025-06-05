// Cart page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Sample cart data (in a real application, this would come from localStorage or a server)
    const sampleCartItems = [
        {
            id: 1,
            name: 'Smartphone Samsung Galaxy A52',
            price: 2500000,
            image: '../assets/image/product-1.jpg',
            seller: 'Ahmad Rizki',
            quantity: 1
        },
        {
            id: 2,
            name: 'Sepatu Lari Nike Air Zoom',
            price: 800000,
            image: '../assets/image/product-2.jpg',
            seller: 'Budi Santoso',
            quantity: 1
        }
    ];
    
    // Get cart from localStorage or use sample data if empty
    let cartItems = JSON.parse(localStorage.getItem('cartItems')) || sampleCartItems;
    
    // DOM elements
    const cartContainer = document.getElementById('cartContainer');
    const emptyCart = document.getElementById('emptyCart');
    const cartSummary = document.getElementById('cartSummary');
    const totalItemsElement = document.getElementById('totalItems');
    const subtotalElement = document.getElementById('subtotal');
    const shippingCostElement = document.getElementById('shippingCost');
    const totalPriceElement = document.getElementById('totalPrice');
    const checkoutBtn = document.getElementById('checkoutBtn');
    
    // Render cart items
    function renderCart() {
        if (cartItems.length === 0) {
            // Show empty cart message
            cartContainer.innerHTML = '';
            emptyCart.classList.remove('hidden');
            cartSummary.classList.add('hidden');
            return;
        }
        
        // Hide empty cart message and show cart summary
        emptyCart.classList.add('hidden');
        cartSummary.classList.remove('hidden');
        
        // Clear cart container
        cartContainer.innerHTML = '';
        
        // Add each item to cart container
        cartItems.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.dataset.id = item.id;
            
            cartItem.innerHTML = `
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h3 class="cart-item-title">${item.name}</h3>
                    <div class="cart-item-price">Rp ${item.price.toLocaleString('id-ID')}</div>
                    <div class="cart-item-seller">Penjual: ${item.seller}</div>
                    <div class="cart-item-actions">
                        <div class="quantity-control">
                            <button class="quantity-btn decrease-btn" data-id="${item.id}">-</button>
                            <input type="number" class="quantity-input" value="${item.quantity}" min="1" max="10" data-id="${item.id}">
                            <button class="quantity-btn increase-btn" data-id="${item.id}">+</button>
                        </div>
                        <div class="remove-item" data-id="${item.id}">
                            <i class="fas fa-trash"></i> Hapus
                        </div>
                    </div>
                </div>
            `;
            
            cartContainer.appendChild(cartItem);
        });
        
        // Update cart summary
        updateCartSummary();
        
        // Add event listeners to quantity buttons and remove buttons
        addEventListeners();
    }
    
    // Update cart summary
    function updateCartSummary() {
        // Calculate total items and subtotal
        const totalItems = cartItems.reduce((total, item) => total + item.quantity, 0);
        const subtotal = cartItems.reduce((total, item) => total + (item.price * item.quantity), 0);
        
        // Calculate shipping cost (in a real application, this would be based on weight, distance, etc.)
        const shippingCost = subtotal > 0 ? 20000 : 0;
        
        // Calculate total price
        const totalPrice = subtotal + shippingCost;
        
        // Update DOM elements
        totalItemsElement.textContent = totalItems;
        subtotalElement.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
        shippingCostElement.textContent = `Rp ${shippingCost.toLocaleString('id-ID')}`;
        totalPriceElement.textContent = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        
        // Save cart to localStorage
        localStorage.setItem('cartItems', JSON.stringify(cartItems));
    }
    
    // Add event listeners to quantity buttons and remove buttons
    function addEventListeners() {
        // Decrease quantity buttons
        document.querySelectorAll('.decrease-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const item = cartItems.find(item => item.id === id);
                
                if (item && item.quantity > 1) {
                    item.quantity--;
                    document.querySelector(`.quantity-input[data-id="${id}"]`).value = item.quantity;
                    updateCartSummary();
                }
            });
        });
        
        // Increase quantity buttons
        document.querySelectorAll('.increase-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const item = cartItems.find(item => item.id === id);
                
                if (item && item.quantity < 10) {
                    item.quantity++;
                    document.querySelector(`.quantity-input[data-id="${id}"]`).value = item.quantity;
                    updateCartSummary();
                }
            });
        });
        
        // Quantity input fields
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const id = parseInt(this.dataset.id);
                const item = cartItems.find(item => item.id === id);
                
                if (item) {
                    const newQuantity = parseInt(this.value);
                    
                    if (newQuantity >= 1 && newQuantity <= 10) {
                        item.quantity = newQuantity;
                    } else if (newQuantity < 1) {
                        item.quantity = 1;
                        this.value = 1;
                    } else {
                        item.quantity = 10;
                        this.value = 10;
                    }
                    
                    updateCartSummary();
                }
            });
        });
        
        // Remove item buttons
        document.querySelectorAll('.remove-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                
                if (confirm('Apakah Anda yakin ingin menghapus barang ini dari keranjang?')) {
                    cartItems = cartItems.filter(item => item.id !== id);
                    renderCart();
                }
            });
        });
    }
    
    // Checkout button
    checkoutBtn.addEventListener('click', function() {
        // In a real application, you would redirect to the checkout page
        window.location.href = 'checkout.html';
    });
    
    // Initial render
    renderCart();
});
