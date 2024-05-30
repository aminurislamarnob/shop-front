<?php

namespace PluginizeLab\ShopFront\Shortcodes;

use PluginizeLab\ShopFront\Shortcodes\Dashboard;

class Shortcodes {

    private $shortcodes = [];

    /**
     *  Register My Shop Front shortcodes
     *
     * @return void
     */
    public function __construct() {
        $this->shortcodes = apply_filters(
            'my_shop_front_shortcodes', [
				'my_shop_front_dashboard' => new Dashboard(),
			]
        );
    }

    /**
     * Get registered shortcode classes
     *
     * @return array
     */
    public function get_shortcodes() {
        return $this->shortcodes;
    }
}
