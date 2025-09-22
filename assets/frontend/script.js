(function($) {
    var StoreFrontCommonConfig = {
        init: function() {
            this.bindEvents();
        },
        bindEvents: function() {
            this.handleDropdown(); //Handle
            this.closeDropdownOutside(); // Close dropdown when clicking outside
            this.uploadProductImage(); // Upload product image
            this.uploadProductGallaryImages(); // Upload product gallery images
        },
        handleDropdown: function(){            
            $(document).on('click', '.msfc-dropdown-icon', function(){
                $('.msfc-dropdown-menu').hide();
                $(this).closest('.msfc-dropdown').find('.msfc-dropdown-menu').toggle();
            });
        },
        closeDropdownOutside: function() {
            $(document).on('click', function(event) {
                if (!$(event.target).closest('.msfc-dropdown').length) {
                    $('.msfc-dropdown-menu').hide();
                }
            });
        },
        uploadProductImage: function() {
            $('#product-single-image').click(function(event){
                event.preventDefault();
    
                var targetContainer = $(this);
                // If the media frame already exists, reopen it.
                if ( frame ) {
                    frame.open();
                    return false;
                }
    
                // Create a new media frame
                var frame = wp.media({
                    title: "Upload Product Image",
                    button:{
                        text: "Insert Image"
                    },
                    multiple: false
                });
    
                frame.on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    
                    // Send the attachment id to our hidden input
                    $('#product_thumbnail_id').val(attachment.id);
    
                    // Send the attachment URL to our custom image input field.
                    $('#product_thumb_img').html( '<img src="'+attachment.sizes.thumbnail.url+'" alt="Product Image"/>' );
                    $('#product_thumbnail_url').val( attachment.sizes.thumbnail.url );
    
                    //add class to hide text normaly
                    $(targetContainer).addClass('image-drop-bg');
                });
    
                frame.open();
            });
        },
        uploadProductGallaryImages: function() {
            $('#product-gallery-images').click(function(event){
                event.preventDefault();
    
                var targetContainer = $(this);
                // If the media frame already exists, reopen it.
                if ( gframe ) {
                    gframe.open();
                    return false;
                }
    
                // Create a new media frame
                var gframe = wp.media({
                    title: "Upload Product Image",
                    button:{
                        text: "Insert Image"
                    },
                    multiple: true
                });
    
                gframe.on('select', function(){
                    $('#product_gallery_img').html('');
                    var galleryImageIds = [];
                    var galleryImageUrls = [];
                    var attachments = gframe.state().get('selection').toJSON();
                    
                    for(var singleItem in attachments){
                        var attachment = attachments[singleItem];
                        galleryImageIds.push(attachment.id);
                        galleryImageUrls.push(attachment.sizes.thumbnail.url);
                        $('#product_gallery_img').append( '<img src="'+attachment.sizes.thumbnail.url+'" alt="Product Gallery Image"/>' );
                        
                    }
                    // Send the attachment ids to our hidden input
                    $('#product_image_gallery').val(galleryImageIds.join(','));
                    $('#product_image_gallery_url').val( galleryImageUrls.join(',') );
    
                    //add class to hide text normaly
                    $(targetContainer).addClass('image-drop-bg');
                });
    
                gframe.open();
            });
        }
    }
    StoreFrontCommonConfig.init();
})(jQuery)