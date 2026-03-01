<?php
/**
 * StoreSuite order details note
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( 0 !== $order_id ) {
	$notes = wc_get_order_notes( $args );
} else {
	$notes = array();
}
?>
<!-- Order Notes Section -->
<div id="new_order_notes" class="storesuite-card">
	<h3 class="storesuite-card-title"><?php esc_html_e( 'Order notes', 'storesuite' ); ?></h3>
	<div class="storesuite-card-content">
		<!-- Existing Notes -->
		<ul class="existing-notes order_notes">
		<?php
		if ( $notes ) {
			foreach ( $notes as $note ) {
				$css_class   = array( 'note' );
				$css_class[] = $note->customer_note ? 'customer-note' : '';
				$css_class[] = 'system' === $note->added_by ? 'system-note' : '';
				$css_class   = apply_filters( 'woocommerce_order_note_class', array_filter( $css_class ), $note );
				?>
					<li rel="<?php echo absint( $note->id ); ?>" data-id="<?php echo absint( $note->id ); ?>" class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
						<div class="note_content">
						<?php echo wpautop( wptexturize( wp_kses_post( $note->content ) ) ); // @codingStandardsIgnoreLine ?>
						</div>
						<p class="meta">
							<abbr class="exact-date" title="<?php echo esc_attr( $note->date_created->date( 'Y-m-d H:i:s' ) ); ?>">
							<?php
							/* translators: %1$s: note date %2$s: note time */
							echo esc_html( sprintf( __( '%1$s at %2$s', 'storesuite' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ) );
							?>
							</abbr>
							<?php
							if ( 'system' !== $note->added_by ) :
								/* translators: %s: note author */
								echo esc_html( sprintf( ' ' . __( 'by %s', 'storesuite' ), $note->added_by ) );
							endif;
							?>
							<a href="#" class="delete_note" role="button"><?php esc_html_e( 'Delete note', 'storesuite' ); ?></a>
						</p>
					</li>
					<?php
			}
		} else {
			?>
				<li class="note no-items">
					<div class="note_content">
						<p><?php esc_html_e( 'There are no notes yet.', 'storesuite' ); ?></p>
					</div>
				</li>
				<?php
		}
		?>
		</ul>
		
		<!-- Add Note Section -->
		<div class="add-note-section">
			<div class="add-note-header">
				<h4 class="add-note-title"><?php esc_html_e( 'Add Note', 'storesuite' ); ?></h4>
			</div>
			<div class="storesuite-form-group">
				<textarea id="add_order_note" class="storesuite-form-control note-textarea" placeholder="<?php echo esc_attr__( 'Enter your note here...', 'storesuite' ); ?>" rows="4"></textarea>
				<small><?php esc_html_e( 'Add a note for your reference, or add a customer note (the user will be notified).', 'storesuite' ); ?></small>
			</div>
			<div class="note-options">
				<div class="storesuite-form-group">
					<select class="storesuite-form-control" id="order_note_type">
						<option><?php esc_html_e( 'Internal note', 'storesuite' ); ?></option>
						<option value="customer"><?php esc_html_e( 'Note to customer', 'storesuite' ); ?></option>
					</select>
				</div>
				<button type="button" class="add-note add-note-btn"><?php esc_html_e( 'Add', 'storesuite' ); ?></button>
			</div>
		</div>
	</div>
</div>