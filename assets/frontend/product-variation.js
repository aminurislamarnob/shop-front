( function ( $ ) {

    /* ---------------------------------------------------------------
     * Variations: load, display and paginate product variations.
     * --------------------------------------------------------------- */
    var StoreSuiteVariations = {
        page: 1,

        init: function() {
            $(document).on('click', '.storesuite-toggle-variation', this.toggleVariation);
            $(document).on('click', '.storesuite-variation-pagination a[data-page]', this.onPaginationClick.bind(this));
            $(document).on('click', '#storesuite-do-variation-action', this.onToolbarAction.bind(this));
            $(document).on('click', '#storesuite-save-variations-btn', this.saveVariations.bind(this));
            $(document).on('click', '.storesuite-remove-variation', this.onRemoveVariationClick.bind(this));

            // Mark rows as needing update on any input change.
            $(document).on('change input', '#storesuite-variations-container :input', this.onVariationFieldChange);

            // Toggle shipping fields when virtual checkbox changes.
            $(document).on('change', '.variable_is_virtual', this.onVirtualChange);

            // Toggle stock qty field when manage stock checkbox changes.
            $(document).on('change', '.variable_manage_stock', this.onManageStockChange);

            // Variation image upload and remove.
            $(document).on('click', '.storesuite-variation-image-upload img', this.onImageUploadClick);
            $(document).on('click', '.storesuite-remove-variation-image', this.onRemoveImageClick);

            // Auto-load variations on edit page.
            if ( StoreSuiteVariation.product_id > 0 && $('#post_type').val() === 'variable' ) {
                this.reload();
            }

            // Reload when product type changes to variable.
            $(document).on('change', '#post_type', function() {
                if ( $(this).val() === 'variable' && StoreSuiteVariation.product_id > 0 ) {
                    StoreSuiteVariations.reload();
                }
            });
        },

        reload: function() {
            this.loadPage( this.page );
        },

        loadPage: function( page ) {
            var self = this;
            var $container = $('#storesuite-variations-container');
            var $pagination = $('.storesuite-variation-pagination');

            if ( ! $container.length ) {
                return;
            }

            window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action:     'storesuite_load_variations',
                    security:   StoreSuiteVariation.nonce,
                    product_id: StoreSuiteVariation.product_id,
                    page:       page,
                    per_page:   StoreSuiteVariation.per_page,
                },
                success: function( response ) {
                    if ( response && response.success ) {
                        var data = response.data;
                        self.page = data.page;
                        $container.attr('data-total', data.total).attr('data-page', data.page);
                        $container.html( data.html || '<p class="storesuite-text-muted">' + StoreSuiteVariation.i18n.no_variations + '</p>' );
                        self.buildPagination( data.total, StoreSuiteVariation.per_page, data.page, data.total_pages );
                    }
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                },
            });
        },

        buildPagination: function( total, perPage, currentPage, totalPages ) {
            var $pagination = $('.storesuite-variation-pagination');
            $pagination.empty();

            if ( totalPages <= 1 ) {
                return;
            }

            var html = '<span class="storesuite-variation-page-info">' + total + ' ' + ( total === 1 ? 'variation' : 'variations' ) + '</span> ';

            if ( currentPage > 1 ) {
                html += '<a href="#" data-page="' + ( currentPage - 1 ) + '" class="storesuite-variation-page-link">&laquo; ' + '</a> ';
            }

            for ( var i = 1; i <= totalPages; i++ ) {
                if ( i === currentPage ) {
                    html += '<span class="storesuite-variation-page-link current">' + i + '</span> ';
                } else {
                    html += '<a href="#" data-page="' + i + '" class="storesuite-variation-page-link">' + i + '</a> ';
                }
            }

            if ( currentPage < totalPages ) {
                html += '<a href="#" data-page="' + ( currentPage + 1 ) + '" class="storesuite-variation-page-link">' + ' &raquo;</a>';
            }

            $pagination.html( html );
        },

        onToolbarAction: function( e ) {
            e.preventDefault();
            var action = $('#storesuite-variation-actions').val();

            switch ( action ) {
                case 'add_variation':
                    this.addVariation();
                    break;
                case 'generate_variations':
                    this.generateAll();
                    break;
                case 'variable_regular_price':
                case 'variable_sale_price':
                case 'variable_stock_status':
                case 'toggle_enabled':
                case 'delete_all':
                    this.bulkAction( action );
                    break;
                default:
                    break;
            }
        },

        generateAll: function() {
            var self = this;

            Swal.fire({
                title: StoreSuiteVariation.i18n.confirm_generate || 'Generate variations?',
                text: StoreSuiteVariation.i18n.confirm_generate_text || 'This will create variations for all attribute combinations.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: StoreSuiteVariation.i18n.ok_button || 'OK',
            }).then( function( result ) {
                if ( ! result.isConfirmed ) {
                    return;
                }

                window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

                $.ajax({
                    url:  StoreSuiteVariation.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action:     'storesuite_generate_variations',
                        security:   StoreSuiteVariation.nonce,
                        product_id: StoreSuiteVariation.product_id,
                    },
                    success: function( response ) {
                        if ( response && response.success ) {
                            Swal.fire({ icon: 'success', text: response.data.message, timer: 3000, showConfirmButton: false });
                            self.page = 1;
                            self.reload();
                        } else {
                            Swal.fire({ icon: 'error', text: response.data.message || 'Error generating variations.' });
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', text: 'An unexpected error occurred.' });
                    },
                    complete: function() {
                        window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                    },
                });
            });
        },

        /**
         * Prompt user for bulk action value and execute.
         */
        bulkAction: function( action ) {
            var self = this;
            var i18n = StoreSuiteVariation.i18n;

            switch ( action ) {
                case 'variable_regular_price':
                case 'variable_sale_price':
                    var title = action === 'variable_regular_price'
                        ? ( i18n.set_regular_price || 'Set regular price for all variations' )
                        : ( i18n.set_sale_price || 'Set sale price for all variations' );

                    Swal.fire({
                        title: title,
                        input: 'text',
                        inputLabel: i18n.enter_price || 'Enter price',
                        showCancelButton: true,
                        confirmButtonText: i18n.ok_button || 'OK',
                        inputValidator: function( val ) {
                            if ( ! val || isNaN( parseFloat( val ) ) ) {
                                return i18n.enter_price || 'Enter a valid price.';
                            }
                        },
                    }).then( function( result ) {
                        if ( result.isConfirmed ) {
                            self.executeBulkAction( action, result.value );
                        }
                    });
                    break;

                case 'variable_stock_status':
                    Swal.fire({
                        title: i18n.select_stock_status || 'Select stock status for all variations',
                        input: 'select',
                        inputOptions: {
                            instock:     i18n.in_stock || 'In stock',
                            outofstock:  i18n.out_of_stock || 'Out of stock',
                            onbackorder: i18n.on_backorder || 'On backorder',
                        },
                        showCancelButton: true,
                        confirmButtonText: i18n.ok_button || 'OK',
                    }).then( function( result ) {
                        if ( result.isConfirmed ) {
                            self.executeBulkAction( action, result.value );
                        }
                    });
                    break;

                case 'toggle_enabled':
                    Swal.fire({
                        title: i18n.confirm_toggle_enabled || 'Toggle enabled/disabled status for all variations?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: i18n.ok_button || 'OK',
                    }).then( function( result ) {
                        if ( result.isConfirmed ) {
                            self.executeBulkAction( action, '' );
                        }
                    });
                    break;

                case 'delete_all':
                    Swal.fire({
                        title: i18n.confirm_delete_all || 'Delete all variations? This cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d63638',
                        confirmButtonText: i18n.ok_button || 'OK',
                    }).then( function( result ) {
                        if ( result.isConfirmed ) {
                            self.executeBulkAction( action, '' );
                        }
                    });
                    break;
            }
        },

        /**
         * Send bulk action AJAX request and reload variations.
         */
        executeBulkAction: function( action, value ) {
            var self = this;

            window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action:      'storesuite_bulk_edit_variations',
                    security:    StoreSuiteVariation.bulk_edit_nonce,
                    product_id:  StoreSuiteVariation.product_id,
                    bulk_action: action,
                    value:       value,
                },
                success: function( response ) {
                    if ( response && response.success ) {
                        Swal.fire({ icon: 'success', text: response.data.message, timer: 2000, showConfirmButton: false });
                        self.page = 1;
                        self.reload();
                    } else {
                        Swal.fire({ icon: 'error', text: ( response.data && response.data.message ) || 'Error performing bulk action.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', text: 'An unexpected error occurred.' });
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                },
            });
        },

        /**
         * Add a single blank variation via AJAX.
         */
        addVariation: function() {
            var self = this;

            window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action:     'storesuite_add_variation',
                    security:   StoreSuiteVariation.add_variation_nonce,
                    product_id: StoreSuiteVariation.product_id,
                },
                success: function( response ) {
                    if ( response && response.success ) {
                        var $container = $( '#storesuite-variations-container' );
                        $container.prepend( response.data.html );
                        Swal.fire({ icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', text: ( response.data && response.data.message ) || 'Error adding variation.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', text: 'An unexpected error occurred.' });
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                },
            });
        },

        /**
         * Handle remove variation button click with SweetAlert2 confirmation.
         */
        onRemoveVariationClick: function( e ) {
            e.preventDefault();
            var self         = this;
            var $btn         = $( e.currentTarget );
            var variationId  = $btn.data( 'variation-id' );
            var $row         = $btn.closest( '.storesuite-variation-row' );

            Swal.fire({
                title: StoreSuiteVariation.i18n.confirm_remove || 'Remove this variation?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: StoreSuiteVariation.i18n.ok_button || 'OK',
            }).then( function( result ) {
                if ( ! result.isConfirmed ) {
                    return;
                }

                self.removeVariation( variationId, $row );
            });
        },

        /**
         * Delete a variation via AJAX and remove the row from the DOM.
         */
        removeVariation: function( variationId, $row ) {
            window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

            var self = this;

            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action:       'storesuite_remove_variation',
                    security:     StoreSuiteVariation.remove_variation_nonce,
                    variation_id: variationId,
                },
                success: function( response ) {
                    if ( response && response.success ) {
                        $row.slideUp( 200, function() {
                            $row.remove();
                            // Reload to refresh pagination and totals.
                            self.reload();
                        });
                    } else {
                        Swal.fire({ icon: 'error', text: ( response.data && response.data.message ) || 'Error removing variation.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', text: 'An unexpected error occurred.' });
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                },
            });
        },

        /**
         * Mark a variation row as needing an update and enable the save button.
         */
        onVariationFieldChange: function() {
            var $row = $(this).closest('.storesuite-variation-row');
            if ( ! $row.hasClass('variation-needs-update') ) {
                $row.addClass('variation-needs-update');
            }
            $('#storesuite-save-variations-btn').prop('disabled', false);
        },

        /**
         * Toggle shipping fields visibility when virtual checkbox changes.
         */
        onVirtualChange: function() {
            var $row = $(this).closest('.storesuite-variation-row');
            if ( $(this).is(':checked') ) {
                $row.find('.hide_if_variation_virtual').hide();
            } else {
                $row.find('.hide_if_variation_virtual').show();
            }
        },

        /**
         * Toggle stock qty field visibility when manage stock checkbox changes.
         */
        onManageStockChange: function() {
            var $row = $(this).closest('.storesuite-variation-row');
            if ( $(this).is(':checked') ) {
                $row.find('.show_if_variation_manage_stock').show();
            } else {
                $row.find('.show_if_variation_manage_stock').hide();
            }
        },

        /**
         * Open WordPress media library to choose a variation image.
         */
        onImageUploadClick: function( e ) {
            e.preventDefault();
            var $upload = $( this ).closest( '.storesuite-variation-image-upload' );
            var $row    = $upload.closest( '.storesuite-variation-row' );
            var i18n    = StoreSuiteVariation.i18n;

            var frame = wp.media({
                title:    i18n.choose_variation_image || 'Choose variation image',
                button:   { text: i18n.set_image || 'Set image' },
                multiple: false,
                library:  { type: 'image' },
            });

            frame.on( 'select', function() {
                var attachment = frame.state().get( 'selection' ).first().toJSON();
                var thumbUrl   = ( attachment.sizes && attachment.sizes.thumbnail )
                    ? attachment.sizes.thumbnail.url
                    : attachment.url;

                $upload.find( 'img' ).attr( 'src', thumbUrl );
                $upload.find( 'input[type="hidden"]' ).val( attachment.id ).trigger( 'change' );
                $upload.find( '.storesuite-remove-variation-image' ).show();

                // Mark row as modified.
                if ( ! $row.hasClass( 'variation-needs-update' ) ) {
                    $row.addClass( 'variation-needs-update' );
                }
                $( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
            });

            frame.open();
        },

        /**
         * Remove the variation image and reset to placeholder.
         */
        onRemoveImageClick: function( e ) {
            e.preventDefault();
            var $upload = $( this ).closest( '.storesuite-variation-image-upload' );
            var $row    = $upload.closest( '.storesuite-variation-row' );

            $upload.find( 'img' ).attr( 'src', StoreSuiteVariation.placeholder_img );
            $upload.find( 'input[type="hidden"]' ).val( 0 ).trigger( 'change' );
            $( this ).hide();

            // Mark row as modified.
            if ( ! $row.hasClass( 'variation-needs-update' ) ) {
                $row.addClass( 'variation-needs-update' );
            }
            $( '#storesuite-save-variations-btn' ).prop( 'disabled', false );
        },

        /**
         * Save only the variation rows that have been modified.
         */
        saveVariations: function() {
            var self      = this;
            var $dirty    = $('#storesuite-variations-container .variation-needs-update');

            if ( ! $dirty.length ) {
                return;
            }

            window.StoreSuite.storeSuiteLoader.block( $( '.my-storesuite-wrapper' ) );

            var formData = new FormData();
            formData.append( 'action', 'storesuite_save_variations' );
            formData.append( 'security', StoreSuiteVariation.save_variations_nonce );
            formData.append( 'product_id', StoreSuiteVariation.product_id );

            // Collect inputs only from dirty rows.
            $dirty.each( function() {
                $( this ).find( ':input' ).each( function() {
                    var $input = $( this );
                    var name   = $input.attr( 'name' );

                    if ( ! name ) {
                        return;
                    }

                    if ( $input.is( ':checkbox' ) ) {
                        if ( $input.is( ':checked' ) ) {
                            formData.append( name, $input.val() );
                        }
                    } else if ( $input.is( 'select[multiple]' ) ) {
                        var values = $input.val() || [];
                        values.forEach( function( v ) {
                            formData.append( name, v );
                        });
                    } else {
                        formData.append( name, $input.val() );
                    }
                });
            });

            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function( response ) {
                    if ( response && response.success ) {
                        Swal.fire({ icon: 'success', text: response.data.message, timer: 2000, showConfirmButton: false });
                        $dirty.removeClass( 'variation-needs-update' );
                        $( '#storesuite-save-variations-btn' ).prop( 'disabled', true );
                    } else {
                        Swal.fire({ icon: 'error', text: ( response.data && response.data.message ) || 'Error saving variations.' });
                    }
                },
                error: function() {
                    Swal.fire({ icon: 'error', text: 'An unexpected error occurred.' });
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock( $( '.my-storesuite-wrapper' ) );
                },
            });
        },

        toggleVariation: function( e ) {
            e.preventDefault();
            $(this).closest('.storesuite-variation-row').find('.storesuite-variation-body').slideToggle(200);
        },

        onPaginationClick: function( e ) {
            e.preventDefault();
            var page = $(e.currentTarget).data('page');
            if ( page ) {
                this.loadPage( page );
            }
        },
    };

    /* ---------------------------------------------------------------
     * Attributes: add, save, remove product attributes.
     * --------------------------------------------------------------- */
    var StoreSuiteAttributes = {
        index: 0, // track attribute row count for naming

        init: function() {
            this.index = $('#storesuite-attributes-list .storesuite-attribute-row').length;

            $(document).on('click', '#storesuite-add-attribute-btn', this.addAttribute.bind(this));
            $(document).on('click', '#storesuite-save-attributes-btn', this.saveAttributes.bind(this));
            $(document).on('click', '.storesuite-remove-attribute', this.removeAttribute);
            $(document).on('click', '.storesuite-toggle-attribute', this.toggleAttribute);
            $(document).on('click', '.storesuite-select-all-terms', this.selectAllTerms);
            $(document).on('click', '.storesuite-select-no-terms', this.selectNoTerms);
        },

        addAttribute: function() {
            var taxonomy = $('#storesuite-add-attribute-select').val();
            var index    = this.index++;
        
            window.StoreSuite.storeSuiteLoader.block(
                $( '.my-storesuite-wrapper' )
            );
        
            $.ajax({
                url:  StoreSuiteVariation.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action:       'storesuite_add_attribute',
                    security:     StoreSuiteVariation.add_attribute_nonce,
                    product_id:   StoreSuiteVariation.product_id || 0,
                    product_type: $('#post_type').val(),
                    taxonomy:     taxonomy,
                    i:            index,
                },
                success: function( response ) {
                    if ( response && response.success && response.data && response.data.html ) {
                        $('#storesuite-attributes-list').append( response.data.html );
        
                        $('.storesuite-select2').filter(':not(.enhanced)').each(function() {
                            $(this).selectWoo({
                                allowClear: true,
                                placeholder: $(this).data('placeholder') || '',
                                tags: $(this).data('tags') || false,
                                tokenSeparators: ['|'],
                                width: '100%',
                            }).addClass('enhanced');
                        });
        
                        if ( taxonomy ) {
                            $('#storesuite-add-attribute-select option[value="' + taxonomy + '"]').remove();
                        }
                    }
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
                },
            });
        },

        saveAttributes: function() {
            window.StoreSuite.storeSuiteLoader.block(
                $( '.my-storesuite-wrapper' )
            );

            var formData = new FormData();
            formData.append('action', 'storesuite_save_attributes');
            formData.append('security', StoreSuiteVariation.save_attributes_nonce);
            formData.append('product_id', StoreSuiteVariation.product_id);
            formData.append('product_type', $('#post_type').val());

            // Collect all attribute fields
            $('#storesuite-attributes-list :input').each(function() {
                var $input = $(this);
                var name = $input.attr('name');
                if (!name) return;

                if ($input.is(':checkbox')) {
                    if ($input.is(':checked')) {
                        formData.append(name, $input.val());
                    }
                } else if ($input.is('select[multiple]')) {
                    var values = $input.val() || [];
                    values.forEach(function(v) {
                        formData.append(name, v);
                    });
                } else {
                    formData.append(name, $input.val());
                }
            });

            $.ajax({
                url: StoreSuiteVariation.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({ icon: 'success', text: response.data.message, timer: 1500, showConfirmButton: false });
                        // Reload variations section (attributes may have changed).
                        StoreSuiteVariations.reload();
                    } else {
                        Swal.fire({ icon: 'error', text: response.data });
                    }
                },
                complete: function() {
                    window.StoreSuite.storeSuiteLoader.unblock(
						$( '.my-storesuite-wrapper' )
					);
                }
            });
        },

        removeAttribute: function(e) {
            e.preventDefault();
            $(this).closest('.storesuite-attribute-row').slideUp(200, function() { $(this).remove(); });
        },

        toggleAttribute: function(e) {
            e.preventDefault();
            $(this).closest('.storesuite-attribute-row').find('.storesuite-attribute-body').slideToggle(200);
        },

        selectAllTerms: function(e) {
            e.preventDefault();
            var $select = $(this).closest('.storesuite-form-group').find('select');
            $select.find('option').prop('selected', true);
            $select.trigger('change');
        },

        selectNoTerms: function(e) {
            e.preventDefault();
            var $select = $(this).closest('.storesuite-form-group').find('select');
            $select.find('option').prop('selected', false);
            $select.trigger('change');
        }
    };
    StoreSuiteAttributes.init();
    StoreSuiteVariations.init();
} )( jQuery );