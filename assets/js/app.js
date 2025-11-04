/**
 * CirclePoint Homes - Main JavaScript
 */

// Mobile menu toggle (already in header but as backup)
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide flash messages after 5 seconds
    const flashMessages = document.querySelectorAll('[class*="bg-green-"], [class*="bg-red-"]');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.transition = 'opacity 0.5s';
            message.style.opacity = '0';
            setTimeout(() => message.remove(), 500);
        }, 5000);
    });

    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    if (preview) {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Confirm dialogs for delete actions
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    // Auto-submit forms on select change
    const autoSubmitSelects = document.querySelectorAll('[data-auto-submit]');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
});

// Utility: Format price
function formatPrice(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Utility: Debounce function for search
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

// Image carousel functionality
class ImageCarousel {
    constructor(element) {
        this.element = element;
        this.images = element.querySelectorAll('img');
        this.currentIndex = 0;
        this.init();
    }

    init() {
        this.showImage(0);

        const prevBtn = this.element.querySelector('.carousel-prev');
        const nextBtn = this.element.querySelector('.carousel-next');

        if (prevBtn) prevBtn.addEventListener('click', () => this.prev());
        if (nextBtn) nextBtn.addEventListener('click', () => this.next());
    }

    showImage(index) {
        this.images.forEach((img, i) => {
            img.classList.toggle('hidden', i !== index);
        });
        this.currentIndex = index;
    }

    next() {
        const nextIndex = (this.currentIndex + 1) % this.images.length;
        this.showImage(nextIndex);
    }

    prev() {
        const prevIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this.showImage(prevIndex);
    }
}

// Initialize carousels
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-carousel]').forEach(el => {
        new ImageCarousel(el);
    });
});
