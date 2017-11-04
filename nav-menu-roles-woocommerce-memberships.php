<?php

/**
 * Plugin Name:       Nav Menu Roles + WooCommerce Memberships Bridge
 * Plugin URI:        http://github.com/helgatheviking/nav-menu-roles-woocommerce-memberships
 * Description:       Add WooCommerce Membership Plans to Nav Menu Roles
 * Version:           1.0.0
 * Author:            Kathy Darling
 * Author URI:        http://kathyisawesome.com
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package Nav Menu Role WooCommerce Memberships Bridge
 * @category Core
 * @author Kathy Darling
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Add NMR filters if both plugins are active
 * @since 1.0.0
 */
function nmr_wcm_init(){ 
	if( function_exists( 'wc_memberships' ) ){
		add_filter( 'nav_menu_roles', 'nmw_wcm_new_roles' );
		add_filter( 'nav_menu_roles_item_visibility', 'nmw_wcm_item_visibility', 10, 2 );
	}
}
add_action( 'plugins_loaded', 'nmr_wcm_init', 20 );


/*
 * Add custom roles to Nav Menu Roles menu options
 * 
 * @param array $roles An array of all available roles, by default is global $wp_roles 
 * @return array
 * @since 1.0.0
 */
function nmw_wcm_new_roles( $roles ){
	return array_merge( $roles, nmr_wcm_get_roles_wrapper() );
}


/*
 * Change visibilty of each menu item
 * NMR settings can be "in" (all logged in), "out" (all logged out) or an array of specific roles
 * 
 * @param bool $visible
 * @param object $item The menu item object. Nav Menu Roles adds its info to $item->roles
 * @return boolean
 * @since 1.0.0
 */
function nmw_wcm_item_visibility( $visible, $item ){
    
  if( ! $visible && isset( $item->roles ) && is_array( $item->roles ) ){
      
        // Get the plugin-specific roles for this menu item. 
        $roles = nmr_wcm_get_relevant_roles_wrapper( $item->roles );

        if( count( $roles ) > 0 ) {

            // Only need to look through the relevant roles.
            foreach( $roles as $role ) {

                // Test if the current user has the specific plan membership.
                if ( nmr_wcm_current_user_can_wrapper( $role ) ){
                    $visible = true;
                    break;
                } else {
                    $visible = false;
                }
            }

        }
                
    }
    
    return $visible;
    
}

/*-----------------------------------------------------------------------------------*/
/* Helper Functions */
/*-----------------------------------------------------------------------------------*/

/*
 * Get the plugin-specific "roles" returned in an array, with ID => Name key pairs
 * 
 * @return array
 * @since 1.1.0
 */
function nmr_wcm_get_roles_wrapper(){
    $roles = array();

    $plans = wc_memberships_get_membership_plans();
    
    if( ! empty( $plans ) ) {
        foreach( $plans as $plan ){
            $roles['wc_membership_' . $plan->id] = $plan->name;
        }
    }

    return $roles;
}

/*
 * Get the plugin-specific "roles" relevant to this menu item
 * 
 * @return array
 * @since 1.1.0
 */
function nmr_wcm_get_relevant_roles_wrapper( $roles = array() ){
    return preg_grep( '/^wc_membership_*/', $roles );
}

/*
 * Check the current user has plugin-specific level capability
 *
 * @param string $role_id | The ID of the "role" with a plugin-specific prefix
 * @return bool
 * @since 1.1.0
 */
function nmr_wcm_current_user_can_wrapper( $role_id = false ) {

    $user_id = get_current_user_id();

    if( ! $user_id || ! $role_id ) {
        return false;
    }

    $role_id = str_replace( 'wc_membership_', '', $role_id );

    return wc_memberships_is_user_active_member( $user_id, $role_id );

}