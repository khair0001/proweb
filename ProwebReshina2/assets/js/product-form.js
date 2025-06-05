// Product Form page JavaScript
// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to fetch location suggestions
async function fetchLocationSuggestions(query) {
    if (!query || query.length < 3) return [];
    
    try {
        const response = await fetch(
            `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5&countrycodes=id&accept-language=id`
        );
        const data = await response.json();
        return data.map(item => ({
            displayName: item.display_name.split(',').slice(0, 3).join(','), // Show only first 3 parts
            fullName: item.display_name,
            lat: item.lat,
            lon: item.lon,
            address: item.address
        }));
    } catch (error) {
        console.error('Error fetching location suggestions:', error);
        return [];
    }
}

// Function to display suggestions
function showSuggestions(suggestions) {
    const container = document.getElementById('locationSuggestions');
    container.innerHTML = '';
    
    if (suggestions.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    suggestions.forEach((suggestion, index) => {
        const div = document.createElement('div');
        div.className = 'suggestion-item' + (index === 0 ? ' highlighted' : '');
        div.innerHTML = `
            <span class="main-text">${suggestion.displayName}</span>
            <span class="sub-text">${suggestion.address?.city || suggestion.address?.county || suggestion.address?.state || ''}</span>
        `;
        
        div.addEventListener('click', () => {
            selectSuggestion(suggestion);
        });
        
        container.appendChild(div);
    });
    
    container.style.display = 'block';
}

// Function to handle suggestion selection
function selectSuggestion(suggestion) {
    const input = document.getElementById('locationInput');
    const hiddenInput = document.getElementById('location');
    
    input.value = suggestion.displayName;
    hiddenInput.value = JSON.stringify({
        display_name: suggestion.fullName,
        lat: suggestion.lat,
        lon: suggestion.lon,
        address: suggestion.address
    });
    
    document.getElementById('locationSuggestions').style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const productForm = document.getElementById('productForm');
    const transactionTypeSelect = document.getElementById('transactionType');
    const priceSection = document.getElementById('priceSection');
    const auctionSection = document.getElementById('auctionSection');
    const locationInput = document.getElementById('locationInput');
    const otherLocationSection = document.getElementById('otherLocationSection');
    const storeCODCheckbox = document.getElementById('storeCOD');
    const codLocationSection = document.getElementById('codLocationSection');
    const productImagesInput = document.getElementById('productImages');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    
    // Location input handling
    if (locationInput) {
        // Debounced search function
        const debouncedSearch = debounce(async (query) => {
            const suggestions = await fetchLocationSuggestions(query);
            showSuggestions(suggestions);
        }, 300);
        
        // Input event listener
        locationInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            if (query.length >= 3) {
                debouncedSearch(query);
            } else {
                document.getElementById('locationSuggestions').style.display = 'none';
            }
        });
        
        // Handle keyboard navigation
        locationInput.addEventListener('keydown', (e) => {
            const suggestions = document.querySelectorAll('.suggestion-item');
            if (!suggestions.length) return;
            
            const highlighted = document.querySelector('.suggestion-item.highlighted');
            let index = Array.from(suggestions).indexOf(highlighted);
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (index < suggestions.length - 1) {
                    if (highlighted) highlighted.classList.remove('highlighted');
                    suggestions[index + 1].classList.add('highlighted');
                    suggestions[index + 1].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (index > 0) {
                    if (highlighted) highlighted.classList.remove('highlighted');
                    suggestions[index - 1].classList.add('highlighted');
                    suggestions[index - 1].scrollIntoView({ block: 'nearest' });
                }
            } else if (e.key === 'Enter' && highlighted) {
                e.preventDefault();
                highlighted.click();
            } else if (e.key === 'Escape') {
                document.getElementById('locationSuggestions').style.display = 'none';
            }
        });
        
        // Close suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.suggestions-container') && e.target !== locationInput) {
                document.getElementById('locationSuggestions').style.display = 'none';
            }
        });
    }
    
    // Handle image upload and preview
    productImagesInput.addEventListener('change', function() {
        const files = this.files;
        
        if (files.length > 5) {
            alert('Maksimal 5 foto yang dapat diunggah.');
            this.value = ''; // Reset the input
            return;
        }
        
        // Clear previous previews
        imagePreviewContainer.innerHTML = '';
        
        // Create preview for each selected image
        Array.from(files).forEach((file, index) => {
            // Check file size (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert(`File "${file.name}" melebihi batas ukuran 5MB.`);
                return;
            }
            
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.createElement('div');
                preview.className = 'image-preview';
                
                const img = document.createElement('img');
                img.src = e.target.result;
                img.alt = `Preview ${index + 1}`;
                
                const removeBtn = document.createElement('div');
                removeBtn.className = 'remove-image';
                removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                removeBtn.addEventListener('click', function() {
                    preview.remove();
                    // Note: This doesn't actually remove the file from the input
                    // In a real application, you would need to handle this differently
                });
                
                preview.appendChild(img);
                preview.appendChild(removeBtn);
                imagePreviewContainer.appendChild(preview);
            };
            
            reader.readAsDataURL(file);
        });
    });
    
    // Form submission
    productForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        
        // Validate form
        let isValid = true;
        
        // Check if at least one storage option is selected
        const storageOptions = document.querySelectorAll('input[name="storageOption"]:checked');
        if (storageOptions.length === 0) {
            alert('Pilih minimal satu opsi penyimpanan/pengambilan.');
            isValid = false;
        }
        
        // Check if images are uploaded
        if (productImagesInput.files.length === 0) {
            alert('Unggah minimal satu foto barang.');
            isValid = false;
        }
        
        if (!isValid) {
            return;
        }
        
        // In a real application, you would send this data to a server
        console.log('Form submitted with data:', Object.fromEntries(formData));
        
        // Show success message
        alert('Barang berhasil diunggah! Menunggu persetujuan admin.');
        
        // Redirect to my products page
        window.location.href = 'my-products.html';
    });
    
    // Set min date for auction end date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().slice(0, 16);
    document.getElementById('auctionEndDate').min = tomorrowStr;
});

// Preview images before upload
function previewImages(input) {
    const container = document.getElementById('image-preview-container');
    const maxFiles = 5;
    
    // Clear existing previews except the placeholder
    const existingPreviews = container.querySelectorAll('.image-preview');
    existingPreviews.forEach(preview => {
      container.removeChild(preview);
    });
    
    // Add new previews
    if (input.files) {
      const filesAmount = Math.min(input.files.length, maxFiles);
      
      for (let i = 0; i < filesAmount; i++) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
          const preview = document.createElement('div');
          preview.className = 'image-preview';
          
          const img = document.createElement('img');
          img.src = e.target.result;
          
          const removeBtn = document.createElement('button');
          removeBtn.className = 'remove-image';
          removeBtn.innerHTML = '<i class="fas fa-times"></i>';
          removeBtn.onclick = function(event) {
            event.preventDefault();
            container.removeChild(preview);
          };
          
          preview.appendChild(img);
          preview.appendChild(removeBtn);
          
          // Insert before placeholder
          const placeholder = container.querySelector('.image-upload-placeholder');
          container.insertBefore(preview, placeholder);
        }
        
        reader.readAsDataURL(input.files[i]);
      }
      
      // Hide placeholder if max files reached
      const placeholder = container.querySelector('.image-upload-placeholder');
      if (input.files.length >= maxFiles) {
        placeholder.style.display = 'none';
      } else {
        placeholder.style.display = 'flex';
      }
    }
  }