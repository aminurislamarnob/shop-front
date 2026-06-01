<?php
/**
 * StoreSuite product import: done summary.
 *
 * @package StoreSuite
 *
 * @var int   $imported Number of products created.
 * @var int   $updated  Number of products updated.
 * @var int   $failed   Number of products that failed.
 * @var int   $skipped  Number of products skipped.
 * @var array $errors   WP_Error objects for failed/skipped rows.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$imported = isset( $imported ) ? (int) $imported : 0;
$updated  = isset( $updated ) ? (int) $updated : 0;
$failed   = isset( $failed ) ? (int) $failed : 0;
$skipped  = isset( $skipped ) ? (int) $skipped : 0;
$errors   = isset( $errors ) ? (array) $errors : array();
?>
<div class="wc-progress-form-content woocommerce-importer">
	<section class="woocommerce-importer-done">
		<?php
		$storesuite_results = array();

		if ( 0 < $imported ) {
			/* translators: %s: number of products */
			$storesuite_results[] = sprintf( _n( '%s product imported', '%s products imported', $imported, 'storesuite' ), '<strong>' . number_format_i18n( $imported ) . '</strong>' );
		}
		if ( 0 < $updated ) {
			/* translators: %s: number of products */
			$storesuite_results[] = sprintf( _n( '%s product updated', '%s products updated', $updated, 'storesuite' ), '<strong>' . number_format_i18n( $updated ) . '</strong>' );
		}
		if ( 0 < $skipped ) {
			/* translators: %s: number of products */
			$storesuite_results[] = sprintf( _n( '%s product was skipped', '%s products were skipped', $skipped, 'storesuite' ), '<strong>' . number_format_i18n( $skipped ) . '</strong>' );
		}
		if ( 0 < $failed ) {
			/* translators: %s: number of products */
			$storesuite_results[] = sprintf( _n( 'Failed to import %s product', 'Failed to import %s products', $failed, 'storesuite' ), '<strong>' . number_format_i18n( $failed ) . '</strong>' );
		}
		if ( 0 < $failed || 0 < $skipped ) {
			$storesuite_results[] = '<a href="#" class="woocommerce-importer-done-view-errors">' . esc_html__( 'View import log', 'storesuite' ) . '</a>';
		}

		echo wp_kses_post( esc_html__( 'Import complete!', 'storesuite' ) . ' ' . implode( '. ', $storesuite_results ) );
		?>
	</section>

	<section class="wc-importer-error-log" style="display:none">
		<table class="widefat wc-importer-error-log-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Row', 'storesuite' ); ?></th>
					<th><?php esc_html_e( 'Reason for failure', 'storesuite' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $errors as $storesuite_error ) {
					if ( ! is_wp_error( $storesuite_error ) ) {
						continue;
					}
					$storesuite_error_data = $storesuite_error->get_error_data();
					?>
					<tr>
						<th><code><?php echo esc_html( isset( $storesuite_error_data['row'] ) ? $storesuite_error_data['row'] : '' ); ?></code></th>
						<td><?php echo esc_html( $storesuite_error->get_error_message() ); ?></td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</section>

	<script type="text/javascript">
		jQuery( function ( $ ) {
			$( '.woocommerce-importer-done-view-errors' ).on( 'click', function () {
				$( '.wc-importer-error-log' ).slideToggle();
				return false;
			} );
		} );
	</script>

	<div class="wc-actions">
		<a class="my-storesuite-button" href="<?php echo esc_url( storesuite_get_navigation_url( 'products' ) ); ?>"><?php esc_html_e( 'View products', 'storesuite' ); ?></a>
	</div>
</div>
