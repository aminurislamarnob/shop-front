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
	'use strict';

	var aiConfig =
		( typeof StoreSuite_Product !== 'undefined' && StoreSuite_Product.ai ) ||
		null;
	var imageEnabled = !! ( aiConfig && aiConfig.image && aiConfig.image.enabled );
	if ( ! aiConfig || ( ! aiConfig.enabled && ! imageEnabled ) ) {
		return;
	}

	var strings = aiConfig.i18n || {};
	var commonStrings =
		( typeof StoreSuite_Product !== 'undefined' && StoreSuite_Product.i18n ) ||
		{};
	var sharedModal =
		( window.StoreSuite && window.StoreSuite.storeSuiteModal ) || null;
	var SPINNER_ICON = '<i class="las la-spinner la-spin"></i> ';
	var MODAL_CLOSE_SELECTOR =
		'.storesuite-product-bulk-modal-cancel, .storesuite-product-bulk-modal-close';

	$( function () {
		// Suggestion modal.
		var $modal = $( '#storesuite-ai-modal' );
		var $modalText = $modal.find( '#storesuite-ai-modal-text' );
		var $modalTitle = $modal.find( '#storesuite-ai-modal-title' );
		var $insertButton = $modal.find( '.storesuite-ai-insert' );
		var $regenerateButton = $modal.find( '.storesuite-ai-regenerate' );
		var $pager = $modal.find( '.storesuite-ai-modal-pager' );
		var $previousButton = $modal.find( '.storesuite-ai-prev' );
		var $nextButton = $modal.find( '.storesuite-ai-next' );
		var $pagerStatus = $modal.find( '.storesuite-ai-pager-status' );
		var $modalSkeleton = $modal.find( '.storesuite-ai-skeleton' );

		// Prompt-input modal.
		var $promptModal = $( '#storesuite-ai-prompt-modal' );
		var $promptText = $promptModal.find( '#storesuite-ai-prompt-text' );
		var $promptGenerateButton = $promptModal.find(
			'.storesuite-ai-prompt-generate'
		);
		var $promptDismissButtons = $promptModal.find( MODAL_CLOSE_SELECTOR );

		var activeField = null; // Field currently being generated.
		var suggestions = []; // Suggestions generated this session.
		var currentIndex = -1; // Index of the suggestion on screen.
		var promptSeed = ''; // Prompt-entry keywords (kept for regeneration).

		if ( sharedModal && $modal.length ) {
			sharedModal.initOverlay( $modal, {
				fade: true,
				closeSelector: MODAL_CLOSE_SELECTOR,
			} );
		}
		if ( sharedModal && $promptModal.length ) {
			sharedModal.initOverlay( $promptModal, {
				fade: true,
				closeSelector: MODAL_CLOSE_SELECTOR,
			} );
		}

		// --- Helpers -------------------------------------------------------

		function trimmedValue( selector ) {
			return $.trim( $( selector ).val() || '' );
		}

		// Active visual TinyMCE editor for the description, or null.
		function getDescriptionEditor() {
			var editor = window.tinymce && tinymce.get( 'product_description' );
			return editor && ! editor.isHidden() ? editor : null;
		}

		function getDescription() {
			var editor = getDescriptionEditor();
			return editor
				? editor.getContent()
				: $( '#product_description' ).val() || '';
		}

		function setDescription( html ) {
			var editor = getDescriptionEditor();
			if ( editor ) {
				editor.setContent( html );
			}
			// Keep the underlying textarea in sync for the Text tab / submit.
			$( '#product_description' ).val( html );
		}

		function getSelectedCategories() {
			return $( '#product_category option:selected' )
				.map( function () {
					return $.trim( $( this ).text() );
				} )
				.get()
				.filter( Boolean );
		}

		function showAlert( icon, message ) {
			Swal.fire( {
				icon: icon,
				title: strings.error_title,
				text: message || commonStrings.unexpected_error,
				confirmButtonText: commonStrings.ok_button,
			} );
		}

		function modalTitleFor( fieldName ) {
			return ( strings.modal_titles || {} )[ fieldName ] || $modalTitle.text();
		}

		// Descriptions need a title or keywords to work from.
		function hasContext( fieldName ) {
			return (
				fieldName === 'title' ||
				!! (
					trimmedValue( '#product_title' ) ||
					trimmedValue( '#product_short_description' )
				)
			);
		}

		// True when title, description and short description are all empty.
		function isTitleFormEmpty() {
			return (
				! trimmedValue( '#product_title' ) &&
				! trimmedValue( '#product_short_description' ) &&
				! $.trim( getDescription() )
			);
		}

		// Resolves with the generated content, or rejects with a message.
		function generateSuggestion( fieldName, previousSuggestion ) {
			var deferred = $.Deferred();
			$.post( StoreSuite_Product.ajax_url, {
				action: aiConfig.action,
				nonce: aiConfig.nonce,
				field: fieldName,
				previous: previousSuggestion || '',
				// Fall back to the typed seed when the title field is empty
				// (prompt flow), so the seed survives regeneration.
				product_title: trimmedValue( '#product_title' ) || promptSeed,
				product_short_description: trimmedValue(
					'#product_short_description'
				),
				product_description: getDescription(),
				categories: getSelectedCategories(),
			} )
				.done( function ( response ) {
					if ( response && response.success && response.data ) {
						deferred.resolve( response.data.content || '' );
					} else {
						deferred.reject(
							( response &&
								response.data &&
								response.data.message ) ||
								commonStrings.unexpected_error
						);
					}
				} )
				.fail( function () {
					deferred.reject( commonStrings.unexpected_error );
				} );
			return deferred.promise();
		}

		// Persist edits to the visible suggestion so they survive navigation.
		function saveCurrentEdit() {
			if ( currentIndex > -1 ) {
				suggestions[ currentIndex ] = $modalText.val() || '';
			}
		}

		// Show the suggestion at the index and render the "‹ n/m ›" pager.
		function showSuggestion( suggestionIndex ) {
			if ( suggestionIndex < 0 || suggestionIndex >= suggestions.length ) {
				return;
			}
			currentIndex = suggestionIndex;
			$modalText.val( suggestions[ suggestionIndex ] );
			$pagerStatus.text( suggestionIndex + 1 + '/' + suggestions.length );
			$previousButton.prop( 'disabled', suggestionIndex === 0 );
			$nextButton.prop(
				'disabled',
				suggestionIndex === suggestions.length - 1
			);
			$pager.prop( 'hidden', suggestions.length < 2 );
		}

		// Show a fresh suggestion in the suggestion modal (or insert directly
		// when the shared modal is unavailable).
		function openSuggestionModal( content ) {
			if ( ! sharedModal || ! $modal.length ) {
				insertIntoField( activeField, content );
				return;
			}
			suggestions = [ content ];
			currentIndex = -1;
			$modalTitle.text( modalTitleFor( activeField ) );
			$modalText.attr( 'rows', activeField === 'description' ? 8 : 3 );
			showSuggestion( 0 );
			sharedModal.open( $modal );
		}

		// Open the prompt-input modal to collect seed keywords for a title.
		function openPromptModal() {
			$promptText.val( '' );
			sharedModal.open( $promptModal );
		}

		function insertIntoField( fieldName, content ) {
			if ( fieldName === 'title' ) {
				$( '#product_title' ).val( content ).trigger( 'change' );
			} else if ( fieldName === 'short_description' ) {
				$( '#product_short_description' )
					.val( content )
					.trigger( 'change' );
			} else if ( fieldName === 'description' ) {
				setDescription( content );
			}
		}

		// --- Events --------------------------------------------------------

		// Field buttons: generate, then open the suggestion modal.
		$( document ).on( 'click', '.storesuite-ai-generate', function ( event ) {
			event.preventDefault();
			var $button = $( this );
			if ( $button.prop( 'disabled' ) ) {
				return;
			}
			activeField = $button.data( 'field' );

			// Title on an empty form: collect seed keywords first.
			if (
				activeField === 'title' &&
				isTitleFormEmpty() &&
				sharedModal &&
				$promptModal.length
			) {
				openPromptModal();
				return;
			}

			if ( ! hasContext( activeField ) ) {
				showAlert( 'info', strings.no_context );
				return;
			}

			promptSeed = ''; // Real form context drives this request.
			var originalHtml = $button.html();
			$button.prop( 'disabled', true ).html( SPINNER_ICON + strings.generating );

			generateSuggestion( activeField, '' )
				.done( openSuggestionModal )
				.fail( function ( message ) {
					showAlert( 'error', message );
				} )
				.always( function () {
					$button.prop( 'disabled', false ).html( originalHtml );
				} );
		} );

		// Prompt modal: generate a title from the typed keywords.
		$promptGenerateButton.on( 'click', function ( event ) {
			event.preventDefault();
			if ( $promptGenerateButton.prop( 'disabled' ) ) {
				return;
			}

			promptSeed = $.trim( $promptText.val() || '' );
			if ( ! promptSeed ) {
				showAlert( 'error', strings.prompt_required );
				return;
			}

			activeField = 'title';
			var originalText = $promptGenerateButton.text();
			$promptGenerateButton.prop( 'disabled', true ).text( strings.generating );
			// Lock the prompt and dismiss controls while the title is generated.
			$promptText.prop( 'readonly', true );
			$promptDismissButtons.prop( 'disabled', true );

			generateSuggestion( 'title', '' )
				.done( function ( content ) {
					sharedModal.close( $promptModal );
					openSuggestionModal( content );
				} )
				.fail( function ( message ) {
					showAlert( 'error', message );
				} )
				.always( function () {
					$promptText.prop( 'readonly', false );
					$promptDismissButtons.prop( 'disabled', false );
					$promptGenerateButton
						.prop( 'disabled', false )
						.text( originalText );
				} );
		} );

		// Regenerate: append a fresh suggestion, steering away from the current.
		$regenerateButton.on( 'click', function ( event ) {
			event.preventDefault();
			if ( ! activeField || $regenerateButton.prop( 'disabled' ) ) {
				return;
			}

			saveCurrentEdit();
			var previousSuggestion = $modalText.val() || '';
			$regenerateButton.prop( 'disabled', true ).text( strings.regenerating );
			$insertButton.prop( 'disabled', true );
			// Swap the current text for a skeleton while the new one is generated.
			$modalText.prop( 'hidden', true );
			$pager.prop( 'hidden', true );
			$modalSkeleton.prop( 'hidden', false );

			generateSuggestion( activeField, previousSuggestion )
				.done( function ( content ) {
					suggestions.push( content );
					showSuggestion( suggestions.length - 1 );
				} )
				.fail( function ( message ) {
					showAlert( 'error', message );
				} )
				.always( function () {
					$modalSkeleton.prop( 'hidden', true );
					$modalText.prop( 'hidden', false );
					$pager.prop( 'hidden', suggestions.length < 2 );
					$regenerateButton
						.prop( 'disabled', false )
						.text( strings.regenerate );
					$insertButton.prop( 'disabled', false );
				} );
		} );

		// Pager: navigate between previously generated suggestions.
		$previousButton.on( 'click', function ( event ) {
			event.preventDefault();
			saveCurrentEdit();
			showSuggestion( currentIndex - 1 );
		} );
		$nextButton.on( 'click', function ( event ) {
			event.preventDefault();
			saveCurrentEdit();
			showSuggestion( currentIndex + 1 );
		} );

		// Insert the (possibly edited) modal text into the field.
		$insertButton.on( 'click', function ( event ) {
			event.preventDefault();
			if ( ! activeField ) {
				return;
			}
			insertIntoField( activeField, $modalText.val() || '' );
			if ( sharedModal && $modal.length ) {
				sharedModal.close( $modal );
			}
		} );

		// --- Bundle modal (global "Generate with AI") ----------------------
		//
		// Launched from the page header on Add New Product: the merchant types
		// one hint and AI drafts title, short description and long description
		// together for review before they're inserted into the form.

		var $bundleModal = $( '#storesuite-ai-bundle-modal' );
		var $bundleHint = $bundleModal.find( '#storesuite-ai-bundle-hint' );
		var $bundleHintStep = $bundleModal.find( '.storesuite-ai-bundle-hint-step' );
		var $bundleResultStep = $bundleModal.find(
			'.storesuite-ai-bundle-result-step'
		);
		var $bundleTitle = $bundleModal.find( '#storesuite-ai-bundle-result-title' );
		var $bundleShort = $bundleModal.find( '#storesuite-ai-bundle-result-short' );
		var $bundleDescription = $bundleModal.find(
			'#storesuite-ai-bundle-result-description'
		);
		var $bundleGenerate = $bundleModal.find( '.storesuite-ai-bundle-generate' );
		var $bundleRegenerate = $bundleModal.find(
			'.storesuite-ai-bundle-regenerate'
		);
		var $bundleInsert = $bundleModal.find( '.storesuite-ai-bundle-insert' );
		var $bundleFields = $bundleHint
			.add( $bundleTitle )
			.add( $bundleShort )
			.add( $bundleDescription );
		var $bundleDismissButtons = $bundleModal.find( MODAL_CLOSE_SELECTOR );

		if ( sharedModal && $bundleModal.length ) {
			sharedModal.initOverlay( $bundleModal, {
				fade: true,
				closeSelector: MODAL_CLOSE_SELECTOR,
			} );
		}

		// Lock every field and control in the bundle modal while a request is in
		// flight. The calling handler restores the busy button's own label.
		function setBundleBusy( isBusy ) {
			$bundleFields.prop( 'readonly', isBusy );
			$bundleDismissButtons.prop( 'disabled', isBusy );
			$bundleGenerate.prop( 'disabled', isBusy );
			$bundleRegenerate.prop( 'disabled', isBusy );
			$bundleInsert.prop( 'disabled', isBusy );
			$bundleModal
				.find( '.storesuite-ai-field-regenerate' )
				.prop( 'disabled', isBusy );
		}

		// The editable result control for a field key.
		function bundleField( fieldName ) {
			if ( fieldName === 'title' ) {
				return $bundleTitle;
			}
			if ( fieldName === 'short_description' ) {
				return $bundleShort;
			}
			return $bundleDescription;
		}

		// Swap a result field for its shimmer skeleton (or back) while it
		// regenerates, so the placeholder appears exactly where the new copy
		// will land.
		function toggleBundleFieldSkeleton( fieldName, show ) {
			bundleField( fieldName ).prop( 'hidden', show );
			$bundleResultStep
				.find(
					'.storesuite-ai-skeleton[data-field="' + fieldName + '"]'
				)
				.prop( 'hidden', ! show );
		}

		function toggleBundleSkeletons( show ) {
			toggleBundleFieldSkeleton( 'title', show );
			toggleBundleFieldSkeleton( 'short_description', show );
			toggleBundleFieldSkeleton( 'description', show );
		}

		// Per-field suggestion history so each field gets its own "‹ n/m ›"
		// pager: every generated/regenerated value is appended and navigable.
		var bundleHistory = { title: [], short_description: [], description: [] };
		var bundleIndex = { title: -1, short_description: -1, description: -1 };

		// Reflect a field's history into its pager (count, position, arrows).
		function renderFieldPager( fieldName ) {
			var history = bundleHistory[ fieldName ];
			var index = bundleIndex[ fieldName ];
			var $pager = $bundleResultStep.find(
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
		}

		// Persist any manual edit to the visible value before navigating away.
		function saveFieldEdit( fieldName ) {
			var index = bundleIndex[ fieldName ];
			if ( index > -1 ) {
				bundleHistory[ fieldName ][ index ] =
					bundleField( fieldName ).val() || '';
			}
		}

		// Append a freshly generated value and jump the pager to it.
		function pushFieldSuggestion( fieldName, value ) {
			bundleHistory[ fieldName ].push( value );
			bundleIndex[ fieldName ] = bundleHistory[ fieldName ].length - 1;
			renderFieldPager( fieldName );
		}

		// Show the suggestion at the given index in its field.
		function showFieldSuggestion( fieldName, index ) {
			var history = bundleHistory[ fieldName ];
			if ( index < 0 || index >= history.length ) {
				return;
			}
			bundleIndex[ fieldName ] = index;
			bundleField( fieldName ).val( history[ index ] );
			renderFieldPager( fieldName );
		}

		// Clear all per-field history and hide every pager.
		function resetFieldHistory() {
			bundleHistory = { title: [], short_description: [], description: [] };
			bundleIndex = { title: -1, short_description: -1, description: -1 };
			renderFieldPager( 'title' );
			renderFieldPager( 'short_description' );
			renderFieldPager( 'description' );
		}

		// Return the modal to the hint-entry step with everything cleared.
		function resetBundleModal() {
			setBundleBusy( false );
			toggleBundleSkeletons( false );
			resetFieldHistory();
			$bundleHint.val( '' );
			$bundleTitle.val( '' );
			$bundleShort.val( '' );
			$bundleDescription.val( '' );
			$bundleResultStep.prop( 'hidden', true );
			$bundleHintStep.prop( 'hidden', false );
			$bundleRegenerate.prop( 'hidden', true );
			$bundleInsert.prop( 'hidden', true );
			$bundleGenerate.prop( 'hidden', false );
		}

		// Request all three fields for the typed hint. Resolves with the data
		// object { title, short_description, description }, or rejects with a
		// message.
		function generateBundle( previousTitle ) {
			var deferred = $.Deferred();
			$.post( StoreSuite_Product.ajax_url, {
				action: aiConfig.bundle_action,
				nonce: aiConfig.nonce,
				hint: $.trim( $bundleHint.val() || '' ),
				previous_title: previousTitle || '',
			} )
				.done( function ( response ) {
					if ( response && response.success && response.data ) {
						deferred.resolve( response.data );
					} else {
						deferred.reject(
							( response &&
								response.data &&
								response.data.message ) ||
								commonStrings.unexpected_error
						);
					}
				} )
				.fail( function () {
					deferred.reject( commonStrings.unexpected_error );
				} );
			return deferred.promise();
		}

		// Fill the editable result fields and reveal the result step. Each value
		// is appended to its field's history so the pager advances on every
		// (re)generation of the full set.
		function showBundleResults( data ) {
			$bundleTitle.val( data.title || '' );
			$bundleShort.val( data.short_description || '' );
			$bundleDescription.val( data.description || '' );
			pushFieldSuggestion( 'title', data.title || '' );
			pushFieldSuggestion( 'short_description', data.short_description || '' );
			pushFieldSuggestion( 'description', data.description || '' );
			$bundleHintStep.prop( 'hidden', true );
			$bundleResultStep.prop( 'hidden', false );
			$bundleGenerate.prop( 'hidden', true );
			$bundleRegenerate.prop( 'hidden', false );
			$bundleInsert.prop( 'hidden', false );
		}

		// Shared runner for Generate and Regenerate.
		function runBundle( $button, busyLabel, previousTitle ) {
			if ( ! $.trim( $bundleHint.val() || '' ) ) {
				showAlert( 'error', strings.hint_required );
				return;
			}
			var originalText = $button.text();
			// Regenerating means the result step is already on screen; show
			// skeletons over the fields. First generation has nothing to cover.
			var isRegenerate = ! $bundleResultStep.prop( 'hidden' );
			setBundleBusy( true );
			if ( isRegenerate ) {
				// Preserve any manual edits at their current history positions
				// before the new set is appended.
				saveFieldEdit( 'title' );
				saveFieldEdit( 'short_description' );
				saveFieldEdit( 'description' );
				toggleBundleSkeletons( true );
			}
			$button.text( busyLabel );

			generateBundle( previousTitle )
				.done( showBundleResults )
				.fail( function ( message ) {
					showAlert( 'error', message );
				} )
				.always( function () {
					setBundleBusy( false );
					if ( isRegenerate ) {
						toggleBundleSkeletons( false );
					}
					$button.text( originalText );
				} );
		}

		// Header launcher: if the form already has any copy, skip the hint step
		// and open straight to the editable fields pre-filled with it.
		// Otherwise start on the hint step.
		$( document ).on(
			'click',
			'.storesuite-ai-bundle-launch',
			function ( event ) {
				event.preventDefault();
				if ( ! sharedModal || ! $bundleModal.length ) {
					return;
				}
				resetBundleModal();

				var title = trimmedValue( '#product_title' );
				var short = $.trim(
					$( '#product_short_description' ).val() || ''
				);
				var description = $.trim( getDescription() );

				if ( title || short || description ) {
					// Seed the (hidden) hint so Regenerate has something to work from.
					$bundleHint.val( title || short );
					showBundleResults( {
						title: title,
						short_description: short,
						description: description,
					} );
				}

				sharedModal.open( $bundleModal );
			}
		);

		$bundleGenerate.on( 'click', function ( event ) {
			event.preventDefault();
			runBundle( $bundleGenerate, strings.generating, '' );
		} );

		$bundleRegenerate.on( 'click', function ( event ) {
			event.preventDefault();
			runBundle(
				$bundleRegenerate,
				strings.regenerating,
				$.trim( $bundleTitle.val() || '' )
			);
		} );

		// Current value of a single bundle result field.
		function bundleFieldValue( fieldName ) {
			if ( fieldName === 'title' ) {
				return $.trim( $bundleTitle.val() || '' );
			}
			if ( fieldName === 'short_description' ) {
				return $bundleShort.val() || '';
			}
			return $bundleDescription.val() || '';
		}

		// Write a regenerated value back into its bundle result field.
		function setBundleField( fieldName, content ) {
			if ( fieldName === 'title' ) {
				$bundleTitle.val( content );
			} else if ( fieldName === 'short_description' ) {
				$bundleShort.val( content );
			} else {
				$bundleDescription.val( content );
			}
		}

		// Regenerate a single field from the other fields' current values,
		// steering away from the value on screen.
		$bundleModal.on(
			'click',
			'.storesuite-ai-field-regenerate',
			function ( event ) {
				event.preventDefault();
				var $button = $( this );
				if ( $button.prop( 'disabled' ) ) {
					return;
				}
				var fieldName = $button.data( 'field' );
				var hint = $.trim( $bundleHint.val() || '' );
				var title = $.trim( $bundleTitle.val() || '' );
				var short = $bundleShort.val() || '';
				// Keywords for a title: the hint, else fall back to what we have.
				var titleSeed = hint || title || $.trim( short );
				var originalHtml = $button.html();
				// Keep any manual edit in history before the new one is appended.
				saveFieldEdit( fieldName );
				setBundleBusy( true );
				toggleBundleFieldSkeleton( fieldName, true );
				$button.html( SPINNER_ICON + strings.regenerating );

				$.post( StoreSuite_Product.ajax_url, {
					action: aiConfig.action,
					nonce: aiConfig.nonce,
					field: fieldName,
					previous: bundleFieldValue( fieldName ),
					// Title leans on its seed; the others lean on the title.
					product_title: fieldName === 'title' ? titleSeed : title,
					product_short_description:
						fieldName === 'short_description'
							? ''
							: $bundleShort.val() || '',
					product_description:
						fieldName === 'description'
							? ''
							: $bundleDescription.val() || '',
					categories: [],
				} )
					.done( function ( response ) {
						if ( response && response.success && response.data ) {
							var content = response.data.content || '';
							setBundleField( fieldName, content );
							pushFieldSuggestion( fieldName, content );
						} else {
							showAlert(
								'error',
								( response &&
									response.data &&
									response.data.message ) ||
									commonStrings.unexpected_error
							);
						}
					} )
					.fail( function () {
						showAlert( 'error', commonStrings.unexpected_error );
					} )
					.always( function () {
						setBundleBusy( false );
						toggleBundleFieldSkeleton( fieldName, false );
						$button.html( originalHtml );
					} );
			}
		);

		// Per-field pager: step through that field's generated suggestions.
		$bundleResultStep.on(
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
				if ( bundleField( fieldName ).prop( 'hidden' ) ) {
					return;
				}
				saveFieldEdit( fieldName );
				var delta = $button.hasClass( 'storesuite-ai-field-next' )
					? 1
					: -1;
				showFieldSuggestion( fieldName, bundleIndex[ fieldName ] + delta );
			}
		);

		// Insert all three drafted fields into the product form.
		$bundleInsert.on( 'click', function ( event ) {
			event.preventDefault();
			insertIntoField( 'title', $.trim( $bundleTitle.val() || '' ) );
			insertIntoField( 'short_description', $bundleShort.val() || '' );
			insertIntoField( 'description', $bundleDescription.val() || '' );
			if ( sharedModal && $bundleModal.length ) {
				sharedModal.close( $bundleModal );
			}
		} );

		// --- Product image generation --------------------------------------
		//
		// A small button beside the Product Image label opens a modal: describe
		// the image, Generate a preview, then Insert it (which side-loads it into
		// the media library and sets it as the product image).

		var imageConfig = aiConfig.image || null;
		if ( imageConfig && imageConfig.enabled ) {
			var $imageModal = $( '#storesuite-ai-image-modal' );
			var $imagePrompt = $imageModal.find( '#storesuite-ai-image-prompt' );
			var $imagePreview = $imageModal.find( '.storesuite-ai-image-preview' );
			var $imagePreviewImg = $imagePreview.find( 'img' );
			var $imageSkeleton = $imageModal.find( '.storesuite-ai-image-skeleton' );
			var $imageSubmit = $imageModal.find( '.storesuite-ai-image-submit' );
			var $imageRegenerate = $imageModal.find(
				'.storesuite-ai-image-regenerate'
			);
			var $imageInsert = $imageModal.find( '.storesuite-ai-image-insert' );
			var $imageDismissButtons = $imageModal.find( MODAL_CLOSE_SELECTOR );
			var imageToken = ''; // Server-side handle to the last generated image.
			var imageTarget = 'featured'; // Where Insert puts it: featured or gallery.

			if ( sharedModal && $imageModal.length ) {
				sharedModal.initOverlay( $imageModal, {
					fade: true,
					closeSelector: MODAL_CLOSE_SELECTOR,
				} );
			}

			// Back to the prompt-entry state with no preview.
			function resetImageModal() {
				$imagePrompt.val( '' ).prop( 'readonly', false );
				$imagePreviewImg.attr( 'src', '' );
				$imagePreview.prop( 'hidden', true );
				$imageSkeleton.prop( 'hidden', true );
				$imageRegenerate.prop( 'hidden', true );
				$imageInsert.prop( 'hidden', true );
				$imageSubmit.prop( 'hidden', false );
				imageToken = '';
			}

			// Generate (or regenerate) an image preview from the typed prompt.
			function generateImage( $button, busyLabel ) {
				var prompt = $.trim( $imagePrompt.val() || '' );
				if ( ! prompt ) {
					showAlert( 'error', imageConfig.prompt_required );
					return;
				}
				var originalText = $button.text();
				$imageSubmit.prop( 'disabled', true );
				$imageRegenerate.prop( 'disabled', true );
				$imageInsert.prop( 'disabled', true );
				$button.text( busyLabel );
				// Lock the prompt and show a skeleton while the image is generated.
				$imagePrompt.prop( 'readonly', true );
				$imagePreview.prop( 'hidden', true );
				$imageSkeleton.prop( 'hidden', false );

				$.post( StoreSuite_Product.ajax_url, {
					action: imageConfig.generate_action,
					nonce: aiConfig.nonce,
					prompt: prompt,
				} )
					.done( function ( response ) {
						if ( response && response.success && response.data ) {
							imageToken = response.data.token || '';
							$imagePreviewImg.attr(
								'src',
								response.data.preview || ''
							);
							$imagePreview.prop( 'hidden', false );
							$imageSubmit.prop( 'hidden', true );
							$imageRegenerate.prop( 'hidden', false );
							$imageInsert.prop( 'hidden', false );
						} else {
							showAlert(
								'error',
								( response &&
									response.data &&
									response.data.message ) ||
									commonStrings.unexpected_error
							);
						}
					} )
					.fail( function () {
						showAlert( 'error', commonStrings.unexpected_error );
					} )
					.always( function () {
						$imageSkeleton.prop( 'hidden', true );
						$imagePrompt.prop( 'readonly', false );
						$imageSubmit.prop( 'disabled', false );
						$imageRegenerate.prop( 'disabled', false );
						$imageInsert.prop( 'disabled', false );
						$button.text( originalText );
					} );
			}

			// Wire the inserted attachment into the product image fields,
			// mirroring the manual media-library upload flow.
			function applyProductImage( attachmentId, url ) {
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
			}

			// Append the inserted attachment to the product gallery, mirroring the
			// manual gallery upload flow.
			function appendGalleryImage( attachmentId, url ) {
				var idStr = String( attachmentId );
				var ids = $.map(
					( $( '#product_image_gallery' ).val() || '' ).split( ',' ),
					function ( value ) {
						value = $.trim( value );
						return value ? value : null;
					}
				);
				var urls = $.map(
					( $( '#product_image_gallery_url' ).val() || '' ).split(
						','
					),
					function ( value ) {
						value = $.trim( value );
						return value ? value : null;
					}
				);
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
			}

			// Label button: open the image modal fresh for the clicked target.
			$( document ).on(
				'click',
				'.storesuite-ai-image-generate',
				function ( event ) {
					event.preventDefault();
					event.stopPropagation();
					if ( ! sharedModal || ! $imageModal.length ) {
						return;
					}
					imageTarget =
						$( this ).data( 'target' ) === 'gallery'
							? 'gallery'
							: 'featured';
					resetImageModal();
					sharedModal.open( $imageModal );
				}
			);

			$imageSubmit.on( 'click', function ( event ) {
				event.preventDefault();
				generateImage( $imageSubmit, strings.generating );
			} );

			$imageRegenerate.on( 'click', function ( event ) {
				event.preventDefault();
				generateImage( $imageRegenerate, strings.regenerating );
			} );

			// Side-load the generated image and set it as the product image.
			$imageInsert.on( 'click', function ( event ) {
				event.preventDefault();
				if ( ! imageToken ) {
					return;
				}
				var originalText = $imageInsert.text();
				$imageInsert.prop( 'disabled', true ).text( imageConfig.inserting );
				$imageRegenerate.prop( 'disabled', true );
				// Lock the prompt and dismiss controls while the image is inserted.
				$imagePrompt.prop( 'readonly', true );
				$imageDismissButtons.prop( 'disabled', true );

				$.post( StoreSuite_Product.ajax_url, {
					action: imageConfig.insert_action,
					nonce: aiConfig.nonce,
					token: imageToken,
				} )
					.done( function ( response ) {
						if ( response && response.success && response.data ) {
							if ( 'gallery' === imageTarget ) {
								appendGalleryImage(
									response.data.id,
									response.data.url
								);
							} else {
								applyProductImage(
									response.data.id,
									response.data.url
								);
							}
							if ( sharedModal && $imageModal.length ) {
								sharedModal.close( $imageModal );
							}
						} else {
							showAlert(
								'error',
								( response &&
									response.data &&
									response.data.message ) ||
									commonStrings.unexpected_error
							);
						}
					} )
					.fail( function () {
						showAlert( 'error', commonStrings.unexpected_error );
					} )
					.always( function () {
						$imagePrompt.prop( 'readonly', false );
						$imageDismissButtons.prop( 'disabled', false );
						$imageInsert
							.prop( 'disabled', false )
							.text( originalText );
						$imageRegenerate.prop( 'disabled', false );
					} );
			} );
		}
	} );
} )( jQuery );
