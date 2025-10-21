<?php
declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Ensure the shop manager role can access user management tools needed for password resets.
 */
function sbcc_permission_control_setup_capabilities(): void
{
    $role = get_role('shop_manager');

    if (!$role) {
        return;
    }

    $caps_to_add = [
        'list_users',
        'edit_users',
        'create_users',
        'delete_users',
        'promote_users',
        'remove_users',
        'send_password_reset',
    ];

    foreach ($caps_to_add as $capability) {
        if (!$role->has_cap($capability)) {
            $role->add_cap($capability);
        }
    }
}
add_action('init', 'sbcc_permission_control_setup_capabilities');

/**
 * Helper to check for shop manager role.
 */
function sbcc_permission_control_user_is_shop_manager(?int $user_id = null): bool
{
    $user = $user_id ? get_userdata($user_id) : wp_get_current_user();

    if (!$user instanceof WP_User) {
        return false;
    }

    return in_array('shop_manager', (array) $user->roles, true);
}

/**
 * Remove Administrators from the list visible to shop managers.
 */
function sbcc_permission_control_hide_administrators(WP_User_Query $query): void
{
    if (!is_admin() || !sbcc_permission_control_user_is_shop_manager()) {
        return;
    }

    if (!function_exists('get_current_screen')) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen || 'users' !== $screen->id) {
        return;
    }

    $roles_to_exclude = (array) $query->get('role__not_in');

    if (!in_array('administrator', $roles_to_exclude, true)) {
        $roles_to_exclude[] = 'administrator';
    }

    $query->set('role__not_in', $roles_to_exclude);
}
add_action('pre_get_users', 'sbcc_permission_control_hide_administrators');

/**
 * Hide selected menu pages from the shop manager.
 */
function sbcc_permission_control_hide_admin_menus(): void
{
    if (!sbcc_permission_control_user_is_shop_manager()) {
        return;
    }

    $menus_to_remove = [
        'edit.php', // Posts
        'edit.php?post_type=page', // Pages
        'nasa-theme-options',
        'edit.php?post_type=portfolio',
        'edit.php?post_type=templates',
        'edit.php?post_type=elementor_library',
    ];

    foreach ($menus_to_remove as $menu_slug) {
        remove_menu_page($menu_slug);
    }
}
add_action('admin_menu', 'sbcc_permission_control_hide_admin_menus', 999);

/**
 * Redirect shop managers who click Dashboard to the YITH POS store page.
 */
function sbcc_permission_control_redirect_dashboard_to_pos(): void
{
    if (!sbcc_permission_control_user_is_shop_manager()) {
        return;
    }

    $target_url = admin_url('edit.php?post_type=yith-pos-store&yith-plugin-fw-panel-skip-redirect=1');

    wp_safe_redirect($target_url);
    exit;
}
add_action('load-index.php', 'sbcc_permission_control_redirect_dashboard_to_pos');

/**
 * Remove unwanted UI elements from YITH POS store screen for shop managers.
 */
function sbcc_permission_control_prepare_pos_store_screen(): void
{
    if (!sbcc_permission_control_user_is_shop_manager()) {
        return;
    }

    $screen = get_current_screen();

    if (!$screen) {
        return;
    }

    $target_screen_ids = [
        'edit-yith-pos-store',
        'yith-pos_page_yith_pos_panel',
    ];

    if (!in_array($screen->id, $target_screen_ids, true)) {
        return;
    }

    $screen->remove_help_tabs();

    add_action('admin_head', 'sbcc_permission_control_hide_pos_store_meta_links');
}
add_action('current_screen', 'sbcc_permission_control_prepare_pos_store_screen');

/**
 * Output CSS to hide YITH "Your store tools" and help menu entries.
 */
function sbcc_permission_control_hide_pos_store_meta_links(): void
{
    ?>
    <style>
        /* Hide Your Store Tools tab/link */
        .wrap a[href*="tab=your-store-tools"],
        .wrap [data-tab="your-store-tools"],
        .wrap #yith-plugin-fw__panel__your-store-tools-tab,
        .wrap .yith-plugin-fw__panel__nav__menu-item--your-store-tools,
        #yith-plugin-fw__panel__your-store-tools-tab,
        .yith-plugin-fw__panel__nav__menu-item--your-store-tools {
            display: none !important;
        }

        /* Hide help menu button/tab */
        .wrap a[href*="tab=help"],
        .wrap [data-tab="help"],
        .wrap .yith-plugin-fw__panel__nav__menu-item--help,
        #yith_plugin_fw_panel_help_tab,
        .wrap .yith-plugin-fw__panel__top-links__help,
        .yith-plugin-fw__panel__top-links__help,
        .yith-plugin-fw__panel__nav__menu-item--help,
        a.yith-plugin-fw__panel__top-links__button[href*="tab=help"] {
            display: none !important;
        }
    </style>
    <?php
}

/**
 * Remove Your Store Tools and Help tabs from YITH POS panel for shop managers.
 *
 * @param array $args Panel arguments.
 *
 * @return array
 */
function sbcc_permission_control_filter_yith_panel_args(array $args): array
{
    if (!sbcc_permission_control_user_is_shop_manager()) {
        return $args;
    }

    if (($args['page'] ?? '') !== 'yith_pos_panel') {
        return $args;
    }

    unset($args['your_store_tools'], $args['help_tab']);

    if (isset($args['admin-tabs'])) {
        unset($args['admin-tabs']['your-store-tools'], $args['admin-tabs']['help']);
    }

    return $args;
}
add_filter('yit_plugin_fw_wc_panel_option_args', 'sbcc_permission_control_filter_yith_panel_args', 50);

/**
 * Customize the admin bar for shop managers.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function sbcc_permission_control_customize_admin_bar(WP_Admin_Bar $wp_admin_bar): void
{
    if (!sbcc_permission_control_user_is_shop_manager()) {
        return;
    }

    $pos_url = '';

    if (function_exists('yith_pos_get_pos_page_url')) {
        $pos_url = (string) yith_pos_get_pos_page_url();
    }

    if (!$pos_url) {
        $pos_url = admin_url('edit.php?post_type=yith-pos-store&yith-plugin-fw-panel-skip-redirect=1');
    }

    $wp_admin_bar->add_node([
        'id'    => 'sbcc-go-to-pos',
        'title' => esc_html__('Go to POS Store', 'swiss-bakery-custom-codes'),
        'href'  => $pos_url,
        'meta'  => [
            'class' => 'sbcc-admin-bar-pos-link',
        ],
    ]);

    $nodes_to_remove = [
        'new-post',
        'new-page',
        'new-templates',
        'new-portfolio',
        'new-elementor_library',
    ];

    foreach ($nodes_to_remove as $node_id) {
        $wp_admin_bar->remove_node($node_id);
    }
}
add_action('admin_bar_menu', 'sbcc_permission_control_customize_admin_bar', 100);

/**
 * Prevent shop managers from managing administrator accounts.
 *
 * @param array  $caps Capabilities for meta capability.
 * @param string $cap Capability requested.
 * @param int    $user_id Current user ID.
 * @param array  $args Additional args (first index is target user ID).
 *
 * @return array
 */
function sbcc_permission_control_block_admin_user_management(array $caps, string $cap, int $user_id, array $args): array
{
    $restricted_caps = [
        'edit_user',
        'delete_user',
        'promote_user',
        'remove_user',
    ];

    if (!in_array($cap, $restricted_caps, true)) {
        return $caps;
    }

    if (!sbcc_permission_control_user_is_shop_manager($user_id)) {
        return $caps;
    }

    $target_user_id = isset($args[0]) ? (int) $args[0] : 0;

    if (!$target_user_id) {
        return $caps;
    }

    $target_user = get_userdata($target_user_id);

    if ($target_user instanceof WP_User && in_array('administrator', (array) $target_user->roles, true)) {
        return ['do_not_allow'];
    }

    return $caps;
}
add_filter('map_meta_cap', 'sbcc_permission_control_block_admin_user_management', 10, 4);
