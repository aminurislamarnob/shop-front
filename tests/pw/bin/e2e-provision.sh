#!/usr/bin/env bash
#
# Provision a WordPress site for the StoreSuite Playwright suite.
#
# Requirements: a running WordPress install reachable by wp-cli, with the
# WooCommerce and StoreSuite plugin directories present under wp-content.
# Point WP_CLI at a wrapper for remote/wp-env use, e.g.:
#   WP_CLI="npx wp-env run cli wp" bash tests/pw/bin/e2e-provision.sh
set -euo pipefail

WP_CLI="${WP_CLI:-wp}"

$WP_CLI plugin activate woocommerce
$WP_CLI plugin activate storesuite

$WP_CLI rewrite structure '/%postname%/'
$WP_CLI rewrite flush

$WP_CLI user create manager manager@example.com --role=shop_manager --user_pass=password || true
$WP_CLI user create customer customer@example.com --role=customer --user_pass=password || true

$WP_CLI option update woocommerce_notify_low_stock_amount 3

# Seed the products and coupon that utils/testData.ts describes.
$WP_CLI eval '
$specs = [
	[ "Blue Hoodie", "45", "HOOD-1", 20 ],
	[ "Red Cap", "15", "CAP-1", 2 ],
	[ "Green Scarf", "25", "SCARF-1", null ],
	[ "Black Mug", "12", "MUG-1", 8 ],
	[ "Sticker Pack", "5", "STICK-1", 1 ],
];
foreach ( $specs as $s ) {
	if ( wc_get_product_id_by_sku( $s[2] ) ) {
		continue;
	}
	$p = new WC_Product_Simple();
	$p->set_name( $s[0] );
	$p->set_regular_price( $s[1] );
	$p->set_sku( $s[2] );
	if ( null !== $s[3] ) {
		$p->set_manage_stock( true );
		$p->set_stock_quantity( $s[3] );
	}
	$p->save();
}
if ( ! wc_get_coupon_id_by_code( "welcome10" ) ) {
	$c = new WC_Coupon();
	$c->set_code( "welcome10" );
	$c->set_amount( 10 );
	$c->set_discount_type( "percent" );
	$c->save();
}
echo "Seeded.\n";
'

echo "Provisioning complete."
