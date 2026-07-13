<?php
/**
 * Low/out-of-stock alert email (HTML).
 *
 * Override by copying to yourtheme/storesuite/emails/low-stock-alert.php.
 *
 * @var \WC_Product $product            Product that triggered the alert.
 * @var string      $level              low|out.
 * @var string      $email_heading      Email heading.
 * @var string      $additional_content Additional content from settings.
 * @var \WC_Email   $email              Email instance.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

$storesuite_status = 'out' === $level ? __( 'is out of stock', 'storesuite' ) : __( 'is low on stock', 'storesuite' );
?>

<p>
	<?php
	printf(
		/* translators: 1: product name, 2: status phrase, 3: quantity */
		esc_html__( '%1$s %2$s (current quantity: %3$s).', 'storesuite' ),
		'<strong>' . esc_html( $product->get_name() ) . '</strong>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped above.
		esc_html( $storesuite_status ),
		esc_html( (string) (int) $product->get_stock_quantity() )
	);
	?>
</p>

<?php if ( $product->get_sku() ) : ?>
	<p>
		<?php
		printf(
			/* translators: %s: product SKU */
			esc_html__( 'SKU: %s', 'storesuite' ),
			esc_html( $product->get_sku() )
		);
		?>
	</p>
<?php endif; ?>

<?php
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

do_action( 'woocommerce_email_footer', $email );
