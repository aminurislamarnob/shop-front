<?php
namespace WeLabs\MyShopFront;

class Helper {
    public static function msfc_get_page_id( $page ) {
        $page = apply_filters( 'my_shop_front_get_' . $page . '_page_id', get_option( 'my_shop_front_' . $page . '_page_id' ) );
        return $page ? absint( $page ) : -1;
    }
}
