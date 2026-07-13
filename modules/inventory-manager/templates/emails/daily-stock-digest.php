<?php
/**
 * Daily low-stock digest email (HTML).
 *
 * Override by copying to yourtheme/storesuite/emails/daily-stock-digest.php.
 *
 * @var array     $items              Queued items: name, sku, qty, level (keyed by product id).
 * @var string    $email_heading      Email heading.
 * @var string    $additional_content Additional content from settings.
 * @var \WC_Email $email              Email instance.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p><?php esc_html_e( 'The following products went low on stock or out of stock since the previous digest:', 'storesuite' ); ?></p>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #e5e5e5;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Product', 'storesuite' ); ?></th>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'SKU', 'storesuite' ); ?></th>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Status', 'storesuite' ); ?></th>
			<th class="td" scope="col" style="text-align: left;"><?php esc_html_e( 'Quantity left', 'storesuite' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $items as $item ) : ?>
			<tr>
				<td class="td" style="text-align: left;"><?php echo esc_html( $item['name'] ?? '' ); ?></td>
				<td class="td" style="text-align: left;"><?php echo esc_html( ( isset( $item['sku'] ) && '' !== $item['sku'] ) ? $item['sku'] : '—' ); ?></td>
				<td class="td" style="text-align: left;">
					<?php
					echo esc_html(
						( 'out' === ( $item['level'] ?? 'low' ) )
							? __( 'Out of stock', 'storesuite' )
							: __( 'Low on stock', 'storesuite' )
					);
					?>
				</td>
				<td class="td" style="text-align: left;"><?php echo esc_html( (string) (int) ( $item['qty'] ?? 0 ) ); ?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
