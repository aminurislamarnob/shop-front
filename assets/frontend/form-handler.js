//Add category
function categoryFormHandler() {
    return {
        message: '',  // Success message
        error: '',    // Error message
        
        async handleCategorySubmission() {
            // Clear messages before submission
            this.message = '';
            this.error = '';

            // Collect form data
            let formData = new FormData(document.getElementById('msfc-add-category'));

            try {
                // Send AJAX request using fetch API
                let response = await fetch(My_Shop_Front_Form_Handler.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                let result = await response.json();

                // Check for success or error
                if (result.success) {
                    this.message = result.data.message;
                    document.getElementById('msfc-add-category').reset();  // Reset form fields
                } else {
                    this.error = result.data.error;
                }
            } catch (err) {
                // Handle any other errors
                this.error = 'An unexpected error occurred. Please try again later.';
            }
        }
    };
}

//Edit category
function categoryEditFormHandler(categoryId) {
    return {
        message: '',  // Success message
        error: '',    // Error message

        async handleCategoryEditSubmission() {
            // Clear messages before submission
            this.message = '';
            this.error = '';

            // Collect form data
            let formData = new FormData(document.getElementById('msfc-edit-category'));

            try {
                // Send AJAX request using fetch API
                let response = await fetch(My_Shop_Front_Form_Handler.ajax_url, {
                    method: 'POST',
                    body: formData
                });

                let result = await response.json();

                // Check for success or error
                if (result.success) {
                    this.message = result.data.message;
                } else {
                    this.error = result.data.error;
                }
            } catch (err) {
                // Handle any other errors
                this.error = 'An unexpected error occurred. Please try again later.';
            }
        }
    };
}