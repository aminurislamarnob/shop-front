<?php
/**
 * Module Name: Inventory Manager
 * Description: Full stock control for the StoreSuite dashboard — a stock list across all product types with inline and bulk quantity updates, a low-stock view, a stock movement log, and low-stock email alerts.
 * Version: 1.0.0
 * Author: StoreSuite
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/Installer.php';
require_once __DIR__ . '/includes/Settings.php';
require_once __DIR__ . '/includes/StockRepository.php';
require_once __DIR__ . '/includes/StockLog.php';
require_once __DIR__ . '/includes/RestController.php';
require_once __DIR__ . '/includes/Alerts.php';
require_once __DIR__ . '/includes/Module.php';

return new \PluginizeLab\StoreSuite\Modules\InventoryManager\Module( __FILE__ );
