<?php

namespace PluginizeLab\StoreSuite\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class MyStoreSuiteShortcode {

    protected $shortcode = '';

    public function __construct() {
        add_shortcode( $this->shortcode, [ $this, 'render_shortcode' ] );
    }

    public function get_shortcode() {
        return $this->shortcode;
    }

    abstract public function render_shortcode( $atts );
}
