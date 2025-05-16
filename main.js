// Add any JavaScript functionality here
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button clicks with improved event handling
    const deleteButtons = document.querySelectorAll('.btn-delete');
    if (deleteButtons.length > 0) {
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent the default action first
                e.preventDefault();
                
                // If confirmed, navigate to the href
                if (confirm('Are you sure you want to delete this item?')) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields');
            }
        });
    });
});
