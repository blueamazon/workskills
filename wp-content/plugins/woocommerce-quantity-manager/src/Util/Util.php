<?php

namespace Barn2\Plugin\WC_Quantity_Manager\Util;

defined( 'ABSPATH' ) || exit;

/**
 * Role Utilities
 *
 * @package   Barn2\woocommerce-quantity-manager
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 */
final class Util {

    /**
     * Determine if the user has rules applied
     *
     * @return boolean
     */
    public static function user_has_rules() {
        $setting_roles = get_option( 'quantity_manager_roles', [] );

        if ( empty( $setting_roles ) ) {
            return true;
        }

        $user_roles = [];

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $user_roles = $user->roles;
        } else {
            $user_roles = [ 'guest_wqm' ]; // indicator for logged out users
        }

        // MS compat: super administrator privilege
        if ( function_exists( 'is_multisite' ) && \is_multisite() && is_super_admin() ) {
            $user_roles = array_merge( [ 'administrator' ], $user_roles );
        }

        return count( array_intersect( $setting_roles, $user_roles ) ) > 0;
    }

    /**
     * Gets all roles on the site and adds a guest role
     *
     * @return array
     */
    public static function get_roles() {
        $roles = array_merge( wp_roles()->get_names(), [ 'guest_wqm' => __( 'Guest', 'woocommerce-quantity-manager' ) ] );

        return $roles;
    }

    /**
     * Determines if a value is like an integer
     *
     * @param mixed $input
     * @return bool
     */
    public static function is_integer( $input ) {
        return ctype_digit( strval( $input ) );
    }
}