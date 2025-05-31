/**
 * Bookshelf Management System
 * Main JavaScript File
 */

document.addEventListener('DOMContentLoaded', function() {
    // Toggle favorite status
    const favoriteButtons = document.querySelectorAll('.toggle-favorite');
    console.log('Found favorite buttons:', favoriteButtons.length);
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.dataset.bookId;
            const action = this.dataset.action;
            
            console.log(`Toggle favorite clicked - Book ID: ${bookId}, Action: ${action}`);
            
            // Create the request data
            const requestData = { book_id: bookId, action: action };
            console.log('Sending request data:', requestData);
            
            fetch('toggle-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                
                if (data.success) {
                    // Update button text and data attribute
                    if (action === 'add') {
                        this.textContent = 'Remove from Favorites';
                        this.dataset.action = 'remove';
                        this.classList.add('active');
                        console.log('Book added to favorites');
                    } else {
                        this.textContent = 'Add to Favorites';
                        this.dataset.action = 'add';
                        this.classList.remove('active');
                        console.log('Book removed from favorites');
                    }
                    
                    // Show success message
                    showToast(data.message, 'success');
                } else {
                    console.error('Error from server:', data.message);
                    showToast(data.message, 'error');
                    
                    // Show debug info if available
                    if (data.debug_info) {
                        console.error('Debug info:', data.debug_info);
                    }
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showToast('An error occurred. Please try again.', 'error');
            });
        });
    });
    
    // Delete book confirmation
    const deleteButtons = document.querySelectorAll('.delete-book-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.dataset.bookId;
            
            if (confirm('Are you sure you want to delete this book? This action cannot be undone.')) {
                window.location.href = `delete-book.php?id=${bookId}`;
            }
        });
    });
    
    // Read more functionality
    const readMoreButtons = document.querySelectorAll('.read-more-btn');
    readMoreButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const bookId = this.dataset.bookId;
            const modal = document.getElementById(`description-modal-${bookId}`);
            
            if (modal) {
                modal.style.display = 'block';
            }
        });
    });
    
    // Close modals
    const closeButtons = document.querySelectorAll('.close-modal');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });
    
    // File input preview for image uploads
    const imageInput = document.getElementById('image_cover');
    const imagePreview = document.getElementById('image-preview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            // Clear previous preview
            imagePreview.innerHTML = '';
            imagePreview.style.display = 'none';
            
            if (this.files && this.files[0]) {
                // Check file size before preview
                const fileSize = this.files[0].size / 1024 / 1024; // in MB
                if (fileSize > 2) {
                    showToast('File is too large (maximum 2MB)', 'error');
                    this.value = ''; // Clear the input
                    return;
                }
                
                // Check file type
                const fileType = this.files[0].type;
                if (!['image/jpeg', 'image/png', 'image/gif'].includes(fileType)) {
                    showToast('Invalid file type. Only JPG, PNG, and GIF images are allowed.', 'error');
                    this.value = ''; // Clear the input
                    return;
                }
                
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.innerHTML = `<img src="${e.target.result}" alt="Cover Preview">`;
                    imagePreview.style.display = 'block';
                };
                
                reader.onerror = function() {
                    showToast('Error loading image preview', 'error');
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
});

/**
 * Show a toast notification
 * 
 * @param {string} message Message to display
 * @param {string} type Type of toast (success, error)
 */
function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    
    // Add to document
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Hide and remove toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
