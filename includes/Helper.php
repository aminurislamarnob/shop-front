<?php
namespace PluginizeLab\StoreSuite;

class Helper {
	public static function storesuite_get_page_id( $page ) {
		$page = apply_filters( 'storesuite_get_' . $page . '_page_id', get_option( 'storesuite_' . $page . '_page_id' ) );
		return $page ? absint( $page ) : -1;
	}
}
