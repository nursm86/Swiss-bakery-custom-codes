<?php
/**
 * Plugin Name: Swiss Bakery Custom Codes
 * Plugin URI: https://nurislam.online
 * Description: Central location for Swiss Bakery custom functionality. Separate PHP files can be dropped into the plugin directory for specific tasks.
 * Version: 1.0.1
 * Author: Md. Nur Islam
 * Author URI: https://nurislam.online
 * Text Domain: swiss-bakery-custom-codes
 * GitHub Plugin URI: nursm86/Swiss-bakery-custom-codes
 * Primary Branch: main
 *
 * @package SwissBakeryCustomCodes
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (!defined('SWISS_BAKERY_CUSTOM_CODES_VERSION')) {
    define('SWISS_BAKERY_CUSTOM_CODES_VERSION', '1.0.0');
}

define('SWISS_BAKERY_CUSTOM_CODES_PATH', plugin_dir_path(__FILE__));
define('SWISS_BAKERY_CUSTOM_CODES_URL', plugin_dir_url(__FILE__));

/**
 * Bootstrap the plugin.
 */
function swiss_bakery_custom_codes_bootstrap(): void
{
    $includes_dir = SWISS_BAKERY_CUSTOM_CODES_PATH . 'includes';

    if (!is_dir($includes_dir)) {
        return;
    }

    foreach (glob($includes_dir . '/*.php') ?: [] as $file_path) {
        // Allow dropping task-specific PHP files into includes/.
        require_once $file_path;
    }
}

add_action('plugins_loaded', 'swiss_bakery_custom_codes_bootstrap');
