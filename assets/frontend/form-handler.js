// Common function to handle AJAX requests with loading delay
async function ajaxRequestWithLoading(url, formData, loadingMessage) {
    Swal.fire({
        title: loadingMessage,
        text: 'Please wait while we process your request.',
        icon: 'info',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const minLoadingDelay = new Promise(resolve => setTimeout(resolve, 1000)); // Minimum delay of 1 second

    // Send AJAX request and wait for both the request and the minimum delay to complete
    const response = await Promise.all([
        fetch(url, {
            method: 'POST',
            body: formData,
        }).then(res => res.json()),
        minLoadingDelay
    ]);
    
    Swal.close();

    return response[0];
}

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
                let result = await ajaxRequestWithLoading(My_Shop_Front_Form_Handler.ajax_url, formData, 'Adding...');

                // Check for success or error
                if (result.success) {
                    this.message = result.data.message;

                    Swal.fire({
                        icon: 'success',
                        title: 'Added!',
                        text: this.message,
                        confirmButtonText: 'OK'
                    });

                    document.getElementById('msfc-add-category').reset();  // Reset form fields
                } else {
                    this.error = result.data.error;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: this.error,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (err) {
                // Handle any other errors
                this.error = 'An unexpected error occurred. Please try again later.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: this.error,
                    confirmButtonText: 'OK'
                });
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
                let result = await ajaxRequestWithLoading(My_Shop_Front_Form_Handler.ajax_url, formData, 'Updating...');

                // Check for success or error
                if (result.success) {
                    this.message = result.data.message;

                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: this.message,
                        confirmButtonText: 'OK'
                    });
                } else {
                    this.error = result.data.error;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: this.error,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (err) {
                // Handle any other errors
                this.error = 'An unexpected error occurred. Please try again later.';

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: this.error,
                    confirmButtonText: 'OK'
                });
            }
        }
    };
}

//Delete category
function deleteCategoryHandler() {
    return {
        message: '', // Success message
        error: '',   // Error message

        async deleteCategory(categoryId) {
            // Show confirmation dialog with SweetAlert2
            const confirmation = await Swal.fire({
                title: 'Are you sure?',
                text: 'This action will permanently delete the category.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            // If user cancels, exit the function
            if (!confirmation.isConfirmed) return;
            
            // Clear messages
            this.message = '';
            this.error = '';

            // Prepare form data
            let formData = new FormData();
            formData.append('id', categoryId);
            formData.append('action', 'msfc_delete_product_category');
            formData.append('msfc_delete_product_category_nonce', My_Shop_Front_Form_Handler.category_delete_nonce);

            try {
                let result = await ajaxRequestWithLoading(My_Shop_Front_Form_Handler.ajax_url, formData, 'Deleting...');

                if (result.success) {
                    this.message = result.data.message;

                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: this.message,
                        confirmButtonText: 'OK'
                    });
                    
                    // Optionally remove the deleted category row from the DOM
                    document.getElementById(`category-row-${categoryId}`).remove();
                } else {
                    this.error = result.data.error;

                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: this.error,
                        confirmButtonText: 'OK'
                    });
                }
            } catch (err) {
                this.error = 'An unexpected error occurred. Please try again later.';

                Swal.fire({
                    icon: 'error',
                    title: 'Unexpected Error',
                    text: this.error,
                    confirmButtonText: 'OK'
                });
            }
        }
    };
}