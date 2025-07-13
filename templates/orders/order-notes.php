<?php
/**
 * MSFC order details note
 *
 * @package ShopFront
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
<div class="msf-card">
	<ul class="order_notes">
		<?php
		if ( $notes ) {
			foreach ( $notes as $note ) {
				$css_class   = array( 'note' );
				$css_class[] = $note->customer_note ? 'customer-note' : '';
				$css_class[] = 'system' === $note->added_by ? 'system-note' : '';
				$css_class   = apply_filters( 'woocommerce_order_note_class', array_filter( $css_class ), $note );
				?>
				<li rel="<?php echo absint( $note->id ); ?>" class="<?php echo esc_attr( implode( ' ', $css_class ) ); ?>">
					<div class="note_content">
                        <?php echo wpautop( wptexturize( wp_kses_post( $note->content ) ) ); // @codingStandardsIgnoreLine ?>
					</div>
					<p class="meta">
						<abbr class="exact-date" title="<?php echo esc_attr( $note->date_created->date( 'Y-m-d H:i:s' ) ); ?>">
							<?php
							/* translators: %1$s: note date %2$s: note time */
							echo esc_html( sprintf( __( '%1$s at %2$s', 'shop-front' ), $note->date_created->date_i18n( wc_date_format() ), $note->date_created->date_i18n( wc_time_format() ) ) );
							?>
						</abbr>
						<?php
						if ( 'system' !== $note->added_by ) :
							/* translators: %s: note author */
							echo esc_html( sprintf( ' ' . __( 'by %s', 'shop-front' ), $note->added_by ) );
						endif;
						?>
						<a href="#" class="delete_note" role="button"><?php esc_html_e( 'Delete note', 'shop-front' ); ?></a>
					</p>
				</li>
				<?php
			}
		} else {
			?>
			<li class="no-items"><?php esc_html_e( 'There are no notes yet.', 'shop-front' ); ?></li>
			<?php
		}
		?>
	</ul>
	<div class="add_note">
		<p>
			<label for="add_order_note"><?php esc_html_e( 'Add note', 'shop-front' ); ?> <?php echo wc_help_tip( __( 'Add a note for your reference, or add a customer note (the user will be notified).', 'shop-front' ) ); ?></label>
			<textarea type="text" name="order_note" id="add_order_note" class="input-text" cols="20" rows="5"></textarea>
		</p>
		<p>
			<label for="order_note_type" class="screen-reader-text"><?php esc_html_e( 'Note type', 'shop-front' ); ?></label>
			<select name="order_note_type" id="order_note_type">
				<option value=""><?php esc_html_e( 'Private note', 'shop-front' ); ?></option>
				<option value="customer"><?php esc_html_e( 'Note to customer', 'shop-front' ); ?></option>
			</select>
			<button type="button" class="add_note button"><?php esc_html_e( 'Add', 'shop-front' ); ?></button>
		</p>
	</div>
</div>