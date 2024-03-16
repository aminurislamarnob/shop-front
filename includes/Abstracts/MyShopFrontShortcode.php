<?php

namespace WeLabs\MyShopFront\Abstracts;

abstract class MyShopFrontShortcode {

    protected $shortcode = '';

    public function __construct() {
        add_shortcode( $this->shortcode, [ $this, 'render_shortcode' ] );
    }

    public function get_shortcode() {
        return $this->shortcode;
    }

    abstract public function render_shortcode( $atts );
}
