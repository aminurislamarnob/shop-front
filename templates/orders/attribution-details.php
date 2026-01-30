<?php
/**
 * MSFC order details customer attribution template.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables used in this file.
 *
 * @var array $meta Array of meta data.
 * @var bool  $has_more_details Whether to show the more details toggle.
 */
?>
<div class="msf-card">
	<div class="order-attribution-metabox">

		<?php if ( array_key_exists( 'origin', $meta ) ) : ?>
			<h4><?php esc_html_e( 'Origin', 'storesuite' ); ?></h4>
		<?php endif; ?>

		<div class="woocommerce-order-attribution-origin-container">

			<?php if ( array_key_exists( 'origin', $meta ) ) : ?>
				<span class="order-attribution-origin">
					<?php echo esc_html( $meta['origin'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $has_more_details ) : ?>

				<a href="" class="woocommerce-order-attribution-details-toggle" aria-expanded="false">
					<span class="toggle-text show"><?php esc_html_e( 'Show details', 'storesuite' ); ?></span>
					<span class="toggle-text hide" aria-hidden="true"><?php esc_html_e( 'Hide details', 'storesuite' ); ?></span>
					<span class="toggle-indicator" aria-hidden="true"></span>
				</a>
			<?php endif; ?>

		</div>

		<div class="woocommerce-order-attribution-details-container">
			<?php if ( array_key_exists( 'source_type', $meta ) ) : ?>
				<h4><?php esc_html_e( 'Source type', 'storesuite' ); ?></h4>
				<span class="order-attribution-source_type">
					<?php echo esc_html( $meta['source_type'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_campaign', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Campaign', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-campaign">
					<?php echo esc_html( $meta['utm_campaign'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_source', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Source', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-source">
					<?php echo esc_html( $meta['utm_source'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_medium', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Medium', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-medium">
					<?php echo esc_html( $meta['utm_medium'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_source_platform', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Source platform', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-source-platform">
					<?php echo esc_html( $meta['utm_source_platform'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_creative_format', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Creative format', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-creative-format">
					<?php echo esc_html( $meta['utm_creative_format'] ); ?>
				</span>
			<?php endif; ?>

			<?php if ( array_key_exists( 'utm_marketing_tactic', $meta ) ) : ?>
				<h4>
					<?php esc_html_e( 'Marketing tactic', 'storesuite' ); ?>
				</h4>
				<span class="order-attribution-utm-marketing-tactic">
					<?php echo esc_html( $meta['utm_marketing_tactic'] ); ?>
				</span>
			<?php endif; ?>

		</div>

		<?php if ( array_key_exists( 'device_type', $meta ) ) : ?>
			<h4><?php esc_html_e( 'Device type', 'storesuite' ); ?></h4>
			<span class="order-attribution-device_type">
				<?php echo esc_html( $meta['device_type'] ); ?>
			</span>
		<?php endif; ?>

		<?php if ( array_key_exists( 'session_pages', $meta ) ) : ?>
			<h4>
				<?php
				esc_html_e( 'Session page views', 'storesuite' );
				echo wp_kses_post(
					wc_help_tip(
						__(
							'The number of unique pages viewed by the customer prior to this order.',
							'storesuite'
						)
					)
				);
				?>
			</h4>
			<span class="order-attribution-utm-session-pages">
				<?php echo esc_html( $meta['session_pages'] ); ?>
			</span>
		<?php endif; ?>
		<!-- A placeholder for the OA install banner React component. -->
		<div id="order-attribution-install-banner-slotfill"></div>
	</div>
</div>
