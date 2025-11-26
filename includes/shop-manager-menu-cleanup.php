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

    // Remove SeedProd
    remove_menu_page( 'seedprod_lite' );
    remove_menu_page( 'seedprod_pro' );

    // Remove Payments (WooCommerce Payments)
    remove_menu_page( 'wc-payments' );
    remove_menu_page( 'woocommerce-payments' );
    remove_menu_page( 'wc-admin&path=/payments/overview' );
    
    // Based on user URL: admin.php?page=wc-settings&tab=checkout
    remove_menu_page( 'wc-settings' );
    remove_menu_page( 'wc-settings&tab=checkout' );

    // Debug: Log menu structure to help identify stubborn items
    // file_put_contents( WP_CONTENT_DIR . '/menu_dump.txt', print_r( $GLOBALS['menu'], true ) );
}
add_action( 'admin_menu', 'swiss_bakery_cleanup_shop_manager_menu', 999 );
