<?php
/**
 * MSFC pagination template
 *
 * @package ShopFront
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $total_pages > 1 ) {
	$start_item = ( $current_page - 1 ) * $per_page + 1;
	$end_item   = min( $total_items, $current_page * $per_page );
	$big_num    = 999999999;
	$page_links = paginate_links(
		array(
			'base'      => str_replace( $big_num, '%#%', esc_url( get_pagenum_link( $big_num ) ) ),
			'format'    => '?page=%#%',
			'add_args'  => false,
			'current'   => $current_page,
			'total'     => $total_pages,
			'type'      => 'array', // list.
			'prev_text' => '&larr;',
			'next_text' => '&rarr;',
			'end_size'  => 3,
			'mid_size'  => 3,
		)
	);

	echo '<div class="msfc-pagination-wrap">';

	echo '<div class="msfc-result-text">';
	/* translators: %1$s: Start Item, %2$s: End Item, %3$s: Total Items */
	printf( esc_html__( 'Showing %1$s to %2$s of %3$s', 'shop-front' ), esc_html( $start_item ), esc_html( $end_item ), esc_html( $total_items ) );
	echo '</div>';

	if ( ! empty( $page_links ) ) {
		echo '<ul class="msfc-pagination"><li>';
		echo wp_kses_post( join( '</li><li>', $page_links ) );
		echo '</li></ul>';
	}
	echo '</div>';
}
