<?php

namespace PluginizeLab\ShopFront\Admin;

class Settings {
        /**
     * The constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_admin_settings_menu' ] );
    }

    public function add_admin_settings_menu(){
        add_menu_page(
            'ShopFront Settings',
            'ShopFront',
            'manage_options',
            'shop-front',
            [$this, 'settings_page_content']
        );
    }

    public function settings_page_content(){
        ?>
        <div class="wrap">
            <h1>My Plugin Settings</h1>
            <div id="shop-front-settings"></div>
        </div>
        <?php
    }
}