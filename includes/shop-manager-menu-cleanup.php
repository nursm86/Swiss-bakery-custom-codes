<?php
/**
 * Cleanup Shop Manager Menu.
 *
 * Removes unnecessary menu items for Shop Managers.
 *
 * @package SwissBakeryCustomCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove menu pages for shop managers.
 */
function swiss_bakery_cleanup_shop_manager_menu() {
    // Check if user is shop manager
    $user = wp_get_current_user();
    if ( ! in_array( 'shop_manager', (array) $user->roles ) ) {
        return;
    }

    // Remove Contact (Contact Form 7)
    remove_menu_page( 'wpcf7' );

    // Remove Payments (WooCommerce Payments)
    // Try common slugs
    remove_menu_page( 'wc-payments' );
    remove_menu_page( 'woocommerce-payments' );
    
    // If "Payments" is a submenu of WooCommerce
    remove_submenu_page( 'woocommerce', 'wc-settings&tab=checkout' );
    remove_submenu_page( 'woocommerce', 'wc-admin&path=/payments/overview' );
}
add_action( 'admin_menu', 'swiss_bakery_cleanup_shop_manager_menu', 999 );
