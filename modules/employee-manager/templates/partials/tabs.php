<?php
/**
 * Employee Manager — Team screen tab navigation.
 *
 * Expects $current_view in scope (employees|roles|activity).
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$storesuite_current_view = isset( $current_view ) ? $current_view : 'employees';
$storesuite_base_url     = storesuite_get_navigation_url( 'employees' );

$storesuite_tabs = array(
	'employees' => __( 'Employees', 'storesuite' ),
	'roles'     => __( 'Roles', 'storesuite' ),
	'activity'  => __( 'Activity log', 'storesuite' ),
);
?>
<nav class="storesuite-team-tabs" aria-label="<?php esc_attr_e( 'Team sections', 'storesuite' ); ?>">
	<ul class="storesuite-team-tabs-list">
		<?php foreach ( $storesuite_tabs as $storesuite_view => $storesuite_label ) : ?>
			<?php $storesuite_url = 'employees' === $storesuite_view ? $storesuite_base_url : add_query_arg( 'view', $storesuite_view, $storesuite_base_url ); ?>
			<li>
				<a href="<?php echo esc_url( $storesuite_url ); ?>" class="storesuite-team-tab <?php echo $storesuite_current_view === $storesuite_view ? 'active' : ''; ?>">
					<?php echo esc_html( $storesuite_label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
