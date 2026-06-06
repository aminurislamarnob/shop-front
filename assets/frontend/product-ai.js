/* global StoreSuite_Product, Swal */
/**
 * AI copy generation for the StoreSuite product form.
 *
 * Two modals, each with one job:
 *   - #storesuite-ai-prompt-modal: collects seed keywords when generating a
 *     title from an empty form (title + description + short description blank).
 *   - #storesuite-ai-modal: shows the editable suggestion with Regenerate, a
 *     history pager, and Insert.
 *
 * Reads the localized StoreSuite_Product global enqueued on the product script.
 */
( function ( $ ) {
	var StoreFrontProductAI = {
		SPINNER_ICON: '<i class="las la-spinner la-spin"></i> ',
		MODAL_CLOSE_SELECTOR:
			'.storesuite-product-bulk-modal-cancel, .storesuite-product-bulk-modal-close',

		init: function () {
			this.aiConfig =
				( typeof StoreSuite_Product !== 'undefined' &&
					StoreSuite_Product.ai ) ||
				null;
			var imageEnabled = !! (
				this.aiConfig &&
				this.aiConfig.image &&
				this.aiConfig.image.enabled
			);
			if (
				! this.aiConfig ||
				( ! this.aiConfig.enabled && ! imageEnabled )
			) {
				return;
			}

			this.strings = this.aiConfig.i18n || {};
			this.commonStrings =
				( typeof StoreSuite_Product !== 'undefined' &&
					StoreSuite_Product.i18n ) ||
				{};
			this.sharedModal =
				( window.StoreSuite && window.StoreSuite.storeSuiteModal ) ||
				null;

			// Shared suggestion state.
			this.activeField = null; // Field currently being generated.
			this.suggestions = []; // Suggestions generated this session.
			this.currentIndex = -1; // Index of the suggestion on screen.
			this.promptSeed = ''; // Prompt-entry keywords (kept for regeneration).

			// Per-field bundle suggestion history.
			this.bundleHistory = {
				title: [],
				short_description: [],
				description: [],
			};
			this.bundleIndex = {
				title: -1,
				short_description: -1,
				description: -1,
			};

			this.initTextModals();
			this.initBundleModal();
			this.initImageModal();
		},

		// --- Helpers -------------------------------------------------------

		// POST to admin-ajax and normalize the WP JSON envelope: the returned
		// promise resolves with response.data on success and rejects with a
		// human-readable message on failure (server error message, or the generic
		// fallback). Throwing inside the .then() handlers rejects the chained
		// jQuery promise under the Promises/A+ semantics of jQuery 3.x, which WP
		// 7.0 ships and this AI feature requires.
		aiPost: function ( data ) {
			var commonStrings = this.commonStrings;
			return $.post( StoreSuite_Product.ajax_url, data ).then(
				function ( response ) {
					if ( response && response.success && response.data ) {
						return response.data;
					}
					throw (
						( response &&
							response.data &&
							response.data.message ) ||
						commonStrings.unexpected_error
					);
				},
				function () {
					throw commonStrings.unexpected_error;
				}
			);
		},

		trimmedValue: function ( selector ) {
			return $.trim( $( selector ).val() || '' );
		},

		// Split a comma-separated string into a clean list (trimmed, no blanks).
		splitCsv: function ( value ) {
			return $.map( ( value || '' ).split( ',' ), function ( item ) {
				item = $.trim( item );
				return item ? item : null;
			} );
		},

		// Active visual TinyMCE editor for the description, or null.
		getDescriptionEditor: function () {
			var editor = window.tinymce && tinymce.get( 'product_description' );
			return editor && ! editor.isHidden() ? editor : null;
		},

		getDescription: function () {
			var editor = this.getDescriptionEditor();
			return editor
				? editor.getContent()
				: $( '#product_description' ).val() || '';
		},

		setDescription: function ( html ) {
			var editor = this.getDescriptionEditor();
			if ( editor ) {
				editor.setContent( html );
			}
			// Keep the underlying textarea in sync for the Text tab / submit.
			$( '#product_description' ).val( html );
		},

		getSelectedCategories: function () {
			return $( '#product_category option:selected' )
				.map( function () {
					return $.trim( $( this ).text() );
				} )
				.get()
				.filter( Boolean );
		},

		showAlert: function ( icon, message ) {
			Swal.fire( {
				icon: icon,
				title: this.strings.error_title,
				text: message || this.commonStrings.unexpected_error,
				confirmButtonText: this.commonStrings.ok_button,
			} );
		},

		modalTitleFor: function ( fieldName ) {
			return (
				( this.strings.modal_titles || {} )[ fieldName ] ||
				this.$modalTitle.text()
			);
		},

		// Descriptions need a title or keywords to work from.
		hasContext: function ( fieldName ) {
			// Generating the title itself never needs prior context.
			if ( fieldName === 'title' ) {
				return true;
			}

			var hasTitle = this.trimmedValue( '#product_title' ) ? true : false;
			var hasShortDescription = this.trimmedValue(
				'#product_short_description'
			)
				? true
				: false;

			return hasTitle || hasShortDescription;
		},

		// True when title, description and short description are all empty.
		isTitleFormEmpty: function () {
			return (
				! this.trimmedValue( '#product_title' ) &&
				! this.trimmedValue( '#product_short_description' ) &&
				! $.trim( this.getDescription() )
			);
		},

		// Resolves with the generated content, or rejects with a message.
		generateSuggestion: function ( fieldName, previousSuggestion ) {
			return this.aiPost( {
				action: this.aiConfig.action,
				nonce: this.aiConfig.nonce,
				field: fieldName,
				previous: previousSuggestion || '',
				// Fall back to the typed seed when the title field is empty
				// (prompt flow), so the seed survives regeneration.
				product_title:
					this.trimmedValue( '#product_title' ) || this.promptSeed,
				product_short_description: this.trimmedValue(
					'#product_short_description'
				),
				product_description: this.getDescription(),
				categories: this.getSelectedCategories(),
			} ).then( function ( data ) {
				return data.content || '';
			} );
		},

		// Persist edits to the visible suggestion so they survive navigation.
		saveCurrentEdit: function () {
			if ( this.currentIndex > -1 ) {
				this.suggestions[ this.currentIndex ] =
					this.$modalText.val() || '';
			}
		},

		// Show the suggestion at the index and render the "‹ n/m ›" pager.
		showSuggestion: function ( suggestionIndex ) {
			if (
				suggestionIndex < 0 ||
				suggestionIndex >= this.suggestions.length
			) {
				return;
			}
			this.currentIndex = suggestionIndex;
			this.$modalText.val( this.suggestions[ suggestionIndex ] );
			this.$pagerStatus.text(
				suggestionIndex + 1 + '/' + this.suggestions.length
			);
			this.$previousButton.prop( 'disabled', suggestionIndex === 0 );
			this.$nextButton.prop(
				'disabled',
				suggestionIndex === this.suggestions.length - 1
			);
			this.$pager.prop( 'hidden', this.suggestions.length < 2 );
		},

		// Show a fresh suggestion in the suggestion modal (or insert directly
		// when the shared modal is unavailable).
		openSuggestionModal: function ( content ) {
			if ( ! this.sharedModal || ! this.$modal.length ) {
				this.insertIntoField( this.activeField, content );
				return;
			}
			this.suggestions = [ content ];
			this.currentIndex = -1;
			this.$modalTitle.text( this.modalTitleFor( this.activeField ) );
			this.$modalText.attr(
				'rows',
				this.activeField === 'description' ? 8 : 3
			);
			this.showSuggestion( 0 );
			this.sharedModal.open( this.$modal );
		},

		// Open the prompt-input modal to collect seed keywords for a title.
		openPromptModal: function () {
			this.$promptText.val( '' );
			this.sharedModal.open( this.$promptModal );
		},

		insertIntoField: function ( fieldName, content ) {
			if ( fieldName === 'title' ) {
				$( '#product_title' ).val( content ).trigger( 'change' );
			} else if ( fieldName === 'short_description' ) {
				$( '#product_short_description' )
					.val( content )
					.trigger( 'change' );
			} else if ( fieldName === 'description' ) {
				this.setDescription( content );
			}
		},

		// --- Suggestion + prompt modals ------------------------------------

		initTextModals: function () {
			var self = this;

			// Suggestion modal.
			this.$modal = $( '#storesuite-ai-modal' );
			this.$modalText = this.$modal.find( '#storesuite-ai-modal-text' );
			this.$modalTitle = this.$modal.find( '#storesuite-ai-modal-title' );
			this.$insertButton = this.$modal.find( '.storesuite-ai-insert' );
			this.$regenerateButton = this.$modal.find(
				'.storesuite-ai-regenerate'
			);
			this.$pager = this.$modal.find( '.storesuite-ai-modal-pager' );
			this.$previousButton = this.$modal.find( '.storesuite-ai-prev' );
			this.$nextButton = this.$modal.find( '.storesuite-ai-next' );
			this.$pagerStatus = this.$modal.find(
				'.storesuite-ai-pager-status'
			);
			this.$modalSkeleton = this.$modal.find( '.storesuite-ai-skeleton' );

			// Prompt-input modal.
			this.$promptModal = $( '#storesuite-ai-prompt-modal' );
			this.$promptText = this.$promptModal.find(
				'#storesuite-ai-prompt-text'
			);
			this.$promptGenerateButton = this.$promptModal.find(
				'.storesuite-ai-prompt-generate'
			);
			this.$promptDismissButtons = this.$promptModal.find(
				this.MODAL_CLOSE_SELECTOR
			);

			if ( this.sharedModal && this.$modal.length ) {
				this.sharedModal.initOverlay( this.$modal, {
					fade: true,
					closeSelector: this.MODAL_CLOSE_SELECTOR,
					closeOnOverlayClick: false,
				} );
			}
			if ( this.sharedModal && this.$promptModal.length ) {
				this.sharedModal.initOverlay( this.$promptModal, {
					fade: true,
					closeSelector: this.MODAL_CLOSE_SELECTOR,
					closeOnOverlayClick: false,
				} );
			}

			// Field buttons: generate, then open the suggestion modal.
			$( document ).on(
				'click',
				'.storesuite-ai-generate',
				function ( event ) {
					event.preventDefault();
					var $button = $( this );
					if ( $button.prop( 'disabled' ) ) {
						return;
					}
					self.activeField = $button.data( 'field' );

					// Title on an empty form: collect seed keywords first.
					if (
						self.activeField === 'title' &&
						self.isTitleFormEmpty() &&
						self.sharedModal &&
						self.$promptModal.length
					) {
						self.openPromptModal();
						return;
					}

					if ( ! self.hasContext( self.activeField ) ) {
						self.showAlert( 'info', self.strings.no_context );
						return;
					}

					self.promptSeed = ''; // Real form context drives this request.
					var originalHtml = $button.html();
					$button
						.prop( 'disabled', true )
						.html( self.SPINNER_ICON + self.strings.generating );

					self.generateSuggestion( self.activeField, '' )
						.done( function ( content ) {
							self.openSuggestionModal( content );
						} )
						.fail( function ( message ) {
							self.showAlert( 'error', message );
						} )
						.always( function () {
							$button
								.prop( 'disabled', false )
								.html( originalHtml );
						} );
				}
			);

			// Prompt modal: generate a title from the typed keywords.
			this.$promptGenerateButton.on( 'click', function ( event ) {
				event.preventDefault();
				if ( self.$promptGenerateButton.prop( 'disabled' ) ) {
					return;
				}

				self.promptSeed = $.trim( self.$promptText.val() || '' );
				if ( ! self.promptSeed ) {
					self.showAlert( 'error', self.strings.prompt_required );
					return;
				}

				self.activeField = 'title';
				var originalText = self.$promptGenerateButton.text();
				self.$promptGenerateButton
					.prop( 'disabled', true )
					.text( self.strings.generating );
				// Lock the prompt and dismiss controls while the title is generated.
				self.$promptText.prop( 'readonly', true );
				self.$promptDismissButtons.prop( 'disabled', true );

				self.generateSuggestion( 'title', '' )
					.done( function ( content ) {
						self.sharedModal.close( self.$promptModal );
						self.openSuggestionModal( content );
					} )
					.fail( function ( message ) {
						self.showAlert( 'error', message );
					} )
					.always( function () {
						self.$promptText.prop( 'readonly', false );
						self.$promptDismissButtons.prop( 'disabled', false );
						self.$promptGenerateButton
							.prop( 'disabled', false )
							.text( originalText );
					} );
			} );

			// Regenerate: append a fresh suggestion, steering away from the current.
			this.$regenerateButton.on( 'click', function ( event ) {
				event.preventDefault();
				if (
					! self.activeField ||
					self.$regenerateButton.prop( 'disabled' )
				) {
					return;
				}

				self.saveCurrentEdit();
				var previousSuggestion = self.$modalText.val() || '';
				self.$regenerateButton
					.prop( 'disabled', true )
					.text( self.strings.regenerating );
				self.$insertButton.prop( 'disabled', true );
				// Swap the current text for a skeleton while the new one is generated.
				self.$modalText.prop( 'hidden', true );
				self.$pager.prop( 'hidden', true );
				self.$modalSkeleton.prop( 'hidden', false );

				self.generateSuggestion( self.activeField, previousSuggestion )
					.done( function ( content ) {
						self.suggestions.push( content );
						self.showSuggestion( self.suggestions.length - 1 );
					} )
					.fail( function ( message ) {
						self.showAlert( 'error', message );
					} )
					.always( function () {
						self.$modalSkeleton.prop( 'hidden', true );
						self.$modalText.prop( 'hidden', false );
						self.$pager.prop(
							'hidden',
							self.suggestions.length < 2
						);
						self.$regenerateButton
							.prop( 'disabled', false )
							.text( self.strings.regenerate );
						self.$insertButton.prop( 'disabled', false );
					} );
			} );

			// Pager: navigate between previously generated suggestions.
			this.$previousButton.on( 'click', function ( event ) {
				event.preventDefault();
				self.saveCurrentEdit();
				self.showSuggestion( self.currentIndex - 1 );
			} );
			this.$nextButton.on( 'click', function ( event ) {
				event.preventDefault();
				self.saveCurrentEdit();
				self.showSuggestion( self.currentIndex + 1 );
			} );

			// Insert the (possibly edited) modal text into the field.
			this.$insertButton.on( 'click', function ( event ) {
				event.preventDefault();
				if ( ! self.activeField ) {
					return;
				}
				self.insertIntoField(
					self.activeField,
					self.$modalText.val() || ''
				);
				if ( self.sharedModal && self.$modal.length ) {
					self.sharedModal.close( self.$modal );
				}
			} );
		},

		// --- Bundle modal (global "Generate with AI") ----------------------
		//
		// Launched from the page header on Add New Product: the merchant types
		// one hint and AI drafts title, short description and long description
		// together for review before they're inserted into the form.

		// Lock every field and control in the bundle modal while a request is in
		// flight. The calling handler restores the busy button's own label.
		setBundleBusy: function ( isBusy ) {
			this.$bundleFields.prop( 'readonly', isBusy );
			this.$bundleDismissButtons.prop( 'disabled', isBusy );
			this.$bundleGenerate.prop( 'disabled', isBusy );
			this.$bundleRegenerate.prop( 'disabled', isBusy );
			this.$bundleInsert.prop( 'disabled', isBusy );
			this.$bundleModal
				.find( '.storesuite-ai-field-regenerate' )
				.prop( 'disabled', isBusy );
		},

		// The editable result control for a field key.
		bundleField: function ( fieldName ) {
			if ( fieldName === 'title' ) {
				return this.$bundleTitle;
			}
			if ( fieldName === 'short_description' ) {
				return this.$bundleShort;
			}
			return this.$bundleDescription;
		},

		// Swap a result field for its shimmer skeleton (or back) while it
		// regenerates, so the placeholder appears exactly where the new copy
		// will land.
		toggleBundleFieldSkeleton: function ( fieldName, show ) {
			this.bundleField( fieldName ).prop( 'hidden', show );
			this.$bundleResultStep
				.find(
					'.storesuite-ai-skeleton[data-field="' + fieldName + '"]'
				)
				.prop( 'hidden', ! show );
		},

		toggleBundleSkeletons: function ( show ) {
			this.toggleBundleFieldSkeleton( 'title', show );
			this.toggleBundleFieldSkeleton( 'short_description', show );
			this.toggleBundleFieldSkeleton( 'description', show );
		},

		// Reflect a field's history into its pager (count, position, arrows).
		renderFieldPager: function ( fieldName ) {
			var history = this.bundleHistory[ fieldName ];
			var index = this.bundleIndex[ fieldName ];
			var $pager = this.$bundleResultStep.find(
				'.storesuite-ai-field-pager[data-field="' + fieldName + '"]'
			);
			if ( ! history.length ) {
				$pager.prop( 'hidden', true );
				return;
			}
			$pager.prop( 'hidden', false );
			$pager
				.find( '.storesuite-ai-field-pager-status' )
				.text( index + 1 + '/' + history.length );
			$pager
				.find( '.storesuite-ai-field-prev' )
				.prop( 'disabled', index <= 0 );
			$pager
				.find( '.storesuite-ai-field-next' )
				.prop( 'disabled', index >= history.length - 1 );
		},

		// Persist any manual edit to the visible value before navigating away.
		saveFieldEdit: function ( fieldName ) {
			var index = this.bundleIndex[ fieldName ];
			if ( index > -1 ) {
				this.bundleHistory[ fieldName ][ index ] =
					this.bundleField( fieldName ).val() || '';
			}
		},

		// Append a freshly generated value and jump the pager to it.
		pushFieldSuggestion: function ( fieldName, value ) {
			this.bundleHistory[ fieldName ].push( value );
			this.bundleIndex[ fieldName ] =
				this.bundleHistory[ fieldName ].length - 1;
			this.renderFieldPager( fieldName );
		},

		// Show the suggestion at the given index in its field.
		showFieldSuggestion: function ( fieldName, index ) {
			var history = this.bundleHistory[ fieldName ];
			if ( index < 0 || index >= history.length ) {
				return;
			}
			this.bundleIndex[ fieldName ] = index;
			this.bundleField( fieldName ).val( history[ index ] );
			this.renderFieldPager( fieldName );
		},

		// Clear all per-field history and hide every pager.
		resetFieldHistory: function () {
			this.bundleHistory = {
				title: [],
				short_description: [],
				description: [],
			};
			this.bundleIndex = {
				title: -1,
				short_description: -1,
				description: -1,
			};
			this.renderFieldPager( 'title' );
			this.renderFieldPager( 'short_description' );
			this.renderFieldPager( 'description' );
		},

		// Return the modal to the hint-entry step with everything cleared.
		resetBundleModal: function () {
			this.setBundleBusy( false );
			this.toggleBundleSkeletons( false );
			this.resetFieldHistory();
			this.$bundleHint.val( '' );
			this.$bundleTitle.val( '' );
			this.$bundleShort.val( '' );
			this.$bundleDescription.val( '' );
			this.$bundleResultStep.prop( 'hidden', true );
			this.$bundleHintStep.prop( 'hidden', false );
			this.$bundleRegenerate.prop( 'hidden', true );
			this.$bundleInsert.prop( 'hidden', true );
			this.$bundleGenerate.prop( 'hidden', false );
		},

		// Request all three fields for the typed hint. Resolves with the data
		// object { title, short_description, description }, or rejects with a
		// message.
		generateBundle: function ( previousTitle ) {
			return this.aiPost( {
				action: this.aiConfig.bundle_action,
				nonce: this.aiConfig.nonce,
				hint: $.trim( this.$bundleHint.val() || '' ),
				previous_title: previousTitle || '',
			} );
		},

		// Fill the editable result fields and reveal the result step. Each value
		// is appended to its field's history so the pager advances on every
		// (re)generation of the full set.
		showBundleResults: function ( data ) {
			this.$bundleTitle.val( data.title || '' );
			this.$bundleShort.val( data.short_description || '' );
			this.$bundleDescription.val( data.description || '' );
			this.pushFieldSuggestion( 'title', data.title || '' );
			this.pushFieldSuggestion(
				'short_description',
				data.short_description || ''
			);
			this.pushFieldSuggestion( 'description', data.description || '' );
			this.$bundleHintStep.prop( 'hidden', true );
			this.$bundleResultStep.prop( 'hidden', false );
			this.$bundleGenerate.prop( 'hidden', true );
			this.$bundleRegenerate.prop( 'hidden', false );
			this.$bundleInsert.prop( 'hidden', false );
		},

		// Shared runner for Generate and Regenerate.
		runBundle: function ( $button, busyLabel, previousTitle ) {
			var self = this;
			if ( ! $.trim( this.$bundleHint.val() || '' ) ) {
				this.showAlert( 'error', this.strings.hint_required );
				return;
			}
			var originalText = $button.text();
			// Regenerating means the result step is already on screen; show
			// skeletons over the fields. First generation has nothing to cover.
			var isRegenerate = ! this.$bundleResultStep.prop( 'hidden' );
			this.setBundleBusy( true );
			if ( isRegenerate ) {
				// Preserve any manual edits at their current history positions
				// before the new set is appended.
				this.saveFieldEdit( 'title' );
				this.saveFieldEdit( 'short_description' );
				this.saveFieldEdit( 'description' );
				this.toggleBundleSkeletons( true );
			}
			$button.text( busyLabel );

			this.generateBundle( previousTitle )
				.done( function ( data ) {
					self.showBundleResults( data );
				} )
				.fail( function ( message ) {
					self.showAlert( 'error', message );
				} )
				.always( function () {
					self.setBundleBusy( false );
					if ( isRegenerate ) {
						self.toggleBundleSkeletons( false );
					}
					$button.text( originalText );
				} );
		},

		// Current value of a single bundle result field.
		bundleFieldValue: function ( fieldName ) {
			var value = this.bundleField( fieldName ).val() || '';
			// Only the title is trimmed; descriptions keep their whitespace.
			return fieldName === 'title' ? $.trim( value ) : value;
		},

		// Write a regenerated value back into its bundle result field.
		setBundleField: function ( fieldName, content ) {
			this.bundleField( fieldName ).val( content );
		},

		initBundleModal: function () {
			var self = this;

			this.$bundleModal = $( '#storesuite-ai-bundle-modal' );
			this.$bundleHint = this.$bundleModal.find(
				'#storesuite-ai-bundle-hint'
			);
			this.$bundleHintStep = this.$bundleModal.find(
				'.storesuite-ai-bundle-hint-step'
			);
			this.$bundleResultStep = this.$bundleModal.find(
				'.storesuite-ai-bundle-result-step'
			);
			this.$bundleTitle = this.$bundleModal.find(
				'#storesuite-ai-bundle-result-title'
			);
			this.$bundleShort = this.$bundleModal.find(
				'#storesuite-ai-bundle-result-short'
			);
			this.$bundleDescription = this.$bundleModal.find(
				'#storesuite-ai-bundle-result-description'
			);
			this.$bundleGenerate = this.$bundleModal.find(
				'.storesuite-ai-bundle-generate'
			);
			this.$bundleRegenerate = this.$bundleModal.find(
				'.storesuite-ai-bundle-regenerate'
			);
			this.$bundleInsert = this.$bundleModal.find(
				'.storesuite-ai-bundle-insert'
			);
			this.$bundleFields = this.$bundleHint
				.add( this.$bundleTitle )
				.add( this.$bundleShort )
				.add( this.$bundleDescription );
			this.$bundleDismissButtons = this.$bundleModal.find(
				this.MODAL_CLOSE_SELECTOR
			);

			if ( this.sharedModal && this.$bundleModal.length ) {
				this.sharedModal.initOverlay( this.$bundleModal, {
					fade: true,
					closeSelector: this.MODAL_CLOSE_SELECTOR,
					closeOnOverlayClick: false,
				} );
			}

			// Header launcher: if the form already has any copy, skip the hint
			// step and open straight to the editable fields pre-filled with it.
			// Otherwise start on the hint step.
			$( document ).on(
				'click',
				'.storesuite-ai-bundle-launch',
				function ( event ) {
					event.preventDefault();
					if ( ! self.sharedModal || ! self.$bundleModal.length ) {
						return;
					}
					self.resetBundleModal();

					var title = self.trimmedValue( '#product_title' );
					var short = $.trim(
						$( '#product_short_description' ).val() || ''
					);
					var description = $.trim( self.getDescription() );

					if ( title || short || description ) {
						// Seed the (hidden) hint so Regenerate has something to work from.
						self.$bundleHint.val( title || short );
						self.showBundleResults( {
							title: title,
							short_description: short,
							description: description,
						} );
					}

					self.sharedModal.open( self.$bundleModal );
				}
			);

			this.$bundleGenerate.on( 'click', function ( event ) {
				event.preventDefault();
				self.runBundle( self.$bundleGenerate, self.strings.generating, '' );
			} );

			this.$bundleRegenerate.on( 'click', function ( event ) {
				event.preventDefault();
				self.runBundle(
					self.$bundleRegenerate,
					self.strings.regenerating,
					$.trim( self.$bundleTitle.val() || '' )
				);
			} );

			// Regenerate a single field from the other fields' current values,
			// steering away from the value on screen.
			this.$bundleModal.on(
				'click',
				'.storesuite-ai-field-regenerate',
				function ( event ) {
					event.preventDefault();
					var $button = $( this );
					if ( $button.prop( 'disabled' ) ) {
						return;
					}
					var fieldName = $button.data( 'field' );
					var hint = $.trim( self.$bundleHint.val() || '' );
					var title = $.trim( self.$bundleTitle.val() || '' );
					var short = self.$bundleShort.val() || '';
					// Keywords for a title: the hint, else fall back to what we have.
					var titleSeed = hint || title || $.trim( short );
					var originalHtml = $button.html();
					// Keep any manual edit in history before the new one is appended.
					self.saveFieldEdit( fieldName );
					self.setBundleBusy( true );
					self.toggleBundleFieldSkeleton( fieldName, true );
					$button.html(
						self.SPINNER_ICON + self.strings.regenerating
					);

					self.aiPost( {
						action: self.aiConfig.action,
						nonce: self.aiConfig.nonce,
						field: fieldName,
						previous: self.bundleFieldValue( fieldName ),
						// Title leans on its seed; the others lean on the title.
						product_title:
							fieldName === 'title' ? titleSeed : title,
						product_short_description:
							fieldName === 'short_description'
								? ''
								: self.$bundleShort.val() || '',
						product_description:
							fieldName === 'description'
								? ''
								: self.$bundleDescription.val() || '',
						categories: [],
					} )
						.done( function ( data ) {
							var content = data.content || '';
							self.setBundleField( fieldName, content );
							self.pushFieldSuggestion( fieldName, content );
						} )
						.fail( function ( message ) {
							self.showAlert( 'error', message );
						} )
						.always( function () {
							self.setBundleBusy( false );
							self.toggleBundleFieldSkeleton( fieldName, false );
							$button.html( originalHtml );
						} );
				}
			);

			// Per-field pager: step through that field's generated suggestions.
			this.$bundleResultStep.on(
				'click',
				'.storesuite-ai-field-prev, .storesuite-ai-field-next',
				function ( event ) {
					event.preventDefault();
					var $button = $( this );
					if ( $button.prop( 'disabled' ) ) {
						return;
					}
					var fieldName = $button
						.closest( '.storesuite-ai-field-pager' )
						.data( 'field' );
					// Ignore while this field is mid-regeneration (skeleton shown).
					if ( self.bundleField( fieldName ).prop( 'hidden' ) ) {
						return;
					}
					self.saveFieldEdit( fieldName );
					var delta = $button.hasClass( 'storesuite-ai-field-next' )
						? 1
						: -1;
					self.showFieldSuggestion(
						fieldName,
						self.bundleIndex[ fieldName ] + delta
					);
				}
			);

			// Insert all three drafted fields into the product form.
			this.$bundleInsert.on( 'click', function ( event ) {
				event.preventDefault();
				self.insertIntoField(
					'title',
					$.trim( self.$bundleTitle.val() || '' )
				);
				self.insertIntoField(
					'short_description',
					self.$bundleShort.val() || ''
				);
				self.insertIntoField(
					'description',
					self.$bundleDescription.val() || ''
				);
				if ( self.sharedModal && self.$bundleModal.length ) {
					self.sharedModal.close( self.$bundleModal );
				}
			} );
		},

		// --- Product image generation --------------------------------------
		//
		// A small button beside the Product Image label opens a modal: describe
		// the image, Generate a preview, then Insert it (which side-loads it into
		// the media library and sets it as the product image).

		// Back to the prompt-entry state with no preview.
		resetImageModal: function () {
			this.$imagePrompt.val( '' ).prop( 'readonly', false );
			this.$imagePreviewImg.attr( 'src', '' );
			this.$imagePreview.prop( 'hidden', true );
			this.$imageSkeleton.prop( 'hidden', true );
			this.$imageRegenerate.prop( 'hidden', true );
			this.$imageInsert.prop( 'hidden', true );
			this.$imageSubmit.prop( 'hidden', false );
			this.imageToken = '';
		},

		// Generate (or regenerate) an image preview from the typed prompt.
		generateImage: function ( $button, busyLabel ) {
			var self = this;
			var prompt = $.trim( this.$imagePrompt.val() || '' );
			if ( ! prompt ) {
				this.showAlert( 'error', this.imageConfig.prompt_required );
				return;
			}
			var originalText = $button.text();
			this.$imageSubmit.prop( 'disabled', true );
			this.$imageRegenerate.prop( 'disabled', true );
			this.$imageInsert.prop( 'disabled', true );
			$button.text( busyLabel );
			// Lock the prompt and show a skeleton while the image is generated.
			this.$imagePrompt.prop( 'readonly', true );
			this.$imagePreview.prop( 'hidden', true );
			this.$imageSkeleton.prop( 'hidden', false );

			this.aiPost( {
				action: this.imageConfig.generate_action,
				nonce: this.aiConfig.nonce,
				prompt: prompt,
			} )
				.done( function ( data ) {
					self.imageToken = data.token || '';
					self.$imagePreviewImg.attr( 'src', data.preview || '' );
					self.$imagePreview.prop( 'hidden', false );
					self.$imageSubmit.prop( 'hidden', true );
					self.$imageRegenerate.prop( 'hidden', false );
					self.$imageInsert.prop( 'hidden', false );
				} )
				.fail( function ( message ) {
					self.showAlert( 'error', message );
				} )
				.always( function () {
					self.$imageSkeleton.prop( 'hidden', true );
					self.$imagePrompt.prop( 'readonly', false );
					self.$imageSubmit.prop( 'disabled', false );
					self.$imageRegenerate.prop( 'disabled', false );
					self.$imageInsert.prop( 'disabled', false );
					$button.text( originalText );
				} );
		},

		// Wire the inserted attachment into the product image fields,
		// mirroring the manual media-library upload flow.
		applyProductImage: function ( attachmentId, url ) {
			$( '#product_thumbnail_id' ).val( attachmentId );
			$( '#product_thumbnail_url' ).val( url );
			$( '#product_thumb_img' ).html(
				'<img src="' + url + '" alt="" />'
			);
			var $container = $( '#product-single-image' );
			$container.addClass( 'image-drop-bg' );
			$container
				.find( '.image-drop-text span' )
				.text(
					( typeof storeSuiteFrontScript !== 'undefined' &&
						storeSuiteFrontScript.remove_image_text ) ||
						''
				);
			// Notify the dirty-state tracker (sticky "Unsaved Changes" bar).
			$( '#product_thumbnail_id' ).trigger( 'change' );
		},

		// Append the inserted attachment to the product gallery, mirroring the
		// manual gallery upload flow.
		appendGalleryImage: function ( attachmentId, url ) {
			var idStr = String( attachmentId );
			var ids = this.splitCsv( $( '#product_image_gallery' ).val() );
			var urls = this.splitCsv( $( '#product_image_gallery_url' ).val() );
			if ( $.inArray( idStr, ids ) !== -1 ) {
				return;
			}
			ids.push( idStr );
			urls.push( url );
			$( '#product_gallery_img' ).append(
				'<div class="preview-image-box"><a href="#" class="remove-gallery-image" data-id="' +
					idStr +
					'">×</a><img src="' +
					url +
					'" alt="" /></div>'
			);
			$( '#product_image_gallery' ).val( ids.join( ',' ) );
			$( '#product_image_gallery_url' ).val( urls.join( ',' ) );
			$( '#product_image_gallery' ).trigger( 'change' );
			$( '#product-gallery-images' ).addClass(
				'sm-gallery-image-uploader'
			);
			$( '.product-gallery-images-wrapper' ).removeClass(
				'gallery-has-no-image'
			);
		},

		initImageModal: function () {
			var self = this;

			this.imageConfig = this.aiConfig.image || null;
			if ( ! this.imageConfig || ! this.imageConfig.enabled ) {
				return;
			}

			this.$imageModal = $( '#storesuite-ai-image-modal' );
			this.$imagePrompt = this.$imageModal.find(
				'#storesuite-ai-image-prompt'
			);
			this.$imagePreview = this.$imageModal.find(
				'.storesuite-ai-image-preview'
			);
			this.$imagePreviewImg = this.$imagePreview.find( 'img' );
			this.$imageSkeleton = this.$imageModal.find(
				'.storesuite-ai-image-skeleton'
			);
			this.$imageSubmit = this.$imageModal.find(
				'.storesuite-ai-image-submit'
			);
			this.$imageRegenerate = this.$imageModal.find(
				'.storesuite-ai-image-regenerate'
			);
			this.$imageInsert = this.$imageModal.find(
				'.storesuite-ai-image-insert'
			);
			this.$imageDismissButtons = this.$imageModal.find(
				this.MODAL_CLOSE_SELECTOR
			);
			this.imageToken = ''; // Server-side handle to the last generated image.
			this.imageTarget = 'featured'; // Where Insert puts it: featured or gallery.

			if ( this.sharedModal && this.$imageModal.length ) {
				this.sharedModal.initOverlay( this.$imageModal, {
					fade: true,
					closeSelector: this.MODAL_CLOSE_SELECTOR,
				} );
			}

			// Label button: open the image modal fresh for the clicked target.
			$( document ).on(
				'click',
				'.storesuite-ai-image-generate',
				function ( event ) {
					event.preventDefault();
					event.stopPropagation();
					if ( ! self.sharedModal || ! self.$imageModal.length ) {
						return;
					}
					self.imageTarget =
						$( this ).data( 'target' ) === 'gallery'
							? 'gallery'
							: 'featured';
					self.resetImageModal();
					self.sharedModal.open( self.$imageModal );
				}
			);

			this.$imageSubmit.on( 'click', function ( event ) {
				event.preventDefault();
				self.generateImage( self.$imageSubmit, self.strings.generating );
			} );

			this.$imageRegenerate.on( 'click', function ( event ) {
				event.preventDefault();
				self.generateImage(
					self.$imageRegenerate,
					self.strings.regenerating
				);
			} );

			// Side-load the generated image and set it as the product image.
			this.$imageInsert.on( 'click', function ( event ) {
				event.preventDefault();
				if ( ! self.imageToken ) {
					return;
				}
				var originalText = self.$imageInsert.text();
				self.$imageInsert
					.prop( 'disabled', true )
					.text( self.imageConfig.inserting );
				self.$imageRegenerate.prop( 'disabled', true );
				// Lock the prompt and dismiss controls while the image is inserted.
				self.$imagePrompt.prop( 'readonly', true );
				self.$imageDismissButtons.prop( 'disabled', true );

				self.aiPost( {
					action: self.imageConfig.insert_action,
					nonce: self.aiConfig.nonce,
					token: self.imageToken,
				} )
					.done( function ( data ) {
						if ( 'gallery' === self.imageTarget ) {
							self.appendGalleryImage( data.id, data.url );
						} else {
							self.applyProductImage( data.id, data.url );
						}
						if ( self.sharedModal && self.$imageModal.length ) {
							self.sharedModal.close( self.$imageModal );
						}
					} )
					.fail( function ( message ) {
						self.showAlert( 'error', message );
					} )
					.always( function () {
						self.$imagePrompt.prop( 'readonly', false );
						self.$imageDismissButtons.prop( 'disabled', false );
						self.$imageInsert
							.prop( 'disabled', false )
							.text( originalText );
						self.$imageRegenerate.prop( 'disabled', false );
					} );
			} );
		},
	};
	StoreFrontProductAI.init();
} )( jQuery );
