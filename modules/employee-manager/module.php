<?php
/**
 * Module Name: Employee Manager
 * Description: Role-based staff access for the StoreSuite dashboard — create employees, assign predefined or custom roles with granular permissions, a frontend-only login, and a per-employee activity log.
 * Version: 1.0.0
 * Author: StoreSuite
 *
 * @package StoreSuite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/includes/Installer.php';
require_once __DIR__ . '/includes/Capabilities.php';
require_once __DIR__ . '/includes/Roles.php';
require_once __DIR__ . '/includes/Settings.php';
require_once __DIR__ . '/includes/EmployeeManager.php';
require_once __DIR__ . '/includes/ActivityLogger.php';
require_once __DIR__ . '/includes/PermissionsEnforcer.php';
require_once __DIR__ . '/includes/LoginHandler.php';
require_once __DIR__ . '/includes/WelcomeEmail.php';
require_once __DIR__ . '/includes/AjaxController.php';
require_once __DIR__ . '/includes/Module.php';

return new \PluginizeLab\StoreSuite\Modules\EmployeeManager\Module( __FILE__ );
