<?php
/**
 * StoreSuite product import — error notice.
 *
 * Replaces WooCommerce's `.error.inline` admin notice with the dashboard's notice component.
 *
 * @var array $errors List of errors, each with a 'message' and an optional 'actions' list of
 *                    { url, label } pairs.
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php foreach ( $errors as $error ) : ?>
	<div class="storesuite-notice storesuite-notice-error storesuite-mb-24" role="alert">
		<p><?php echo esc_html( $error['message'] ); ?></p>

		<?php if ( ! empty( $error['actions'] ) ) : ?>
			<p class="storesuite-notice-actions">
				<?php foreach ( $error['actions'] as $action ) : ?>
					<a class="my-storesuite-button" href="<?php echo esc_url( $action['url'] ); ?>"><?php echo esc_html( $action['label'] ); ?></a>
				<?php endforeach; ?>
			</p>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
