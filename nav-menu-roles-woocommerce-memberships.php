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
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/*
 * Add NMR filters if both plugins are active
 */
function nmr_wcm_init(){ 
	if( function_exists( 'wc_memberships' ) ){
		add_filter( 'nav_menu_roles', 'nmw_wcm_new_roles' );
		add_filter( 'nav_menu_roles_item_visibility', 'nmw_wcm_item_visibility', 10, 2 );
	}
}
add_action( 'plugins_loaded', 'nmr_wcm_init', 20 );


/*
 * Add custom roles to Nav Menu Roles menu list
 * param: $roles an array of all available roles, by default is global $wp_roles 
 * return: array
 */
function nmw_wcm_new_roles( $roles ){
    
	$plans = wc_memberships_get_membership_plans();
	
	foreach( $plans as $plan ){
	    
        $roles['wc_membership_' . $plan->id] = $plan->name;
    
	}
	
    return $roles;
    
}


/*
 * Change visibilty of each menu item
 * param: $visible boolean
 * param: $item object, the complete menu object. Nav Menu Roles adds its info to $item->roles
 * $item->roles can be "in" (all logged in), "out" (all logged out) or an array of specific roles
 * return boolean
 */
function nmw_wcm_item_visibility( $visible, $item ){
    
  if( ! $visible && isset( $item->roles ) && is_array( $item->roles ) ){
      
        $plans = wc_memberships_get_membership_plans();
     
        $user_id = get_current_user_id();
        
        foreach( $plans as $plan ){
            
            if( in_array( 'wc_membership_' . $plan->id, $item->roles ) ){
                  
                if ( wc_memberships_is_user_active_member( $user_id, $plan->id ) ){
                    
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
