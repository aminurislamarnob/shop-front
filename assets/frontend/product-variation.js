( function ( $ ) {

    /* ---------------------------------------------------------------
     * Variations: load, display and paginate product variations.
     * --------------------------------------------------------------- */
    var StoreSuiteVariations = {
        page: 1,

        init: function() {
            $(document).on('click', '.storesuite-toggle-variation', this.toggleVariation);
            $(document).on('click', '.storesuite-variation-pagination a[data-page]', this.onPaginationClick.bind(this));

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