<?php
/**
 * Fix Shop Manager Capabilities.
 *
 * Ensures Shop Managers can access Bakery Production Manager.
 *
 * @package SwissBakeryCustomCodes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add manage_woocommerce capability to shop_manager if missing.
 */
function swiss_bakery_fix_shop_manager_caps() {
    $role = get_role( 'shop_manager' );
    if ( $role && ! $role->has_cap( 'manage_woocommerce' ) ) {
        $role->add_cap( 'manage_woocommerce' );
    }
}
add_action( 'admin_init', 'swiss_bakery_fix_shop_manager_caps' );
