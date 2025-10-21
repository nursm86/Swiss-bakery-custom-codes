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
function sbcc_permission_control_user_is_shop_manager(): bool
{
    $user = wp_get_current_user();

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
