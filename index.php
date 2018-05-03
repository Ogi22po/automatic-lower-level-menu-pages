<?php
/**
 * @package Automatic lower-level menu pages
 * @version 1.0.0
 */
/*
Plugin Name: Automatic lower-level menu pages
Plugin URI: https://wordpress.org/plugins/automatic-lower-level-menu-pages/
Description: By default Wordpress will automatically add just top level pages, this plugin will add every level of pages to the menu.
Author: Peter "PayteR" Gašparík
Version: 1.0.0
Text Domain: automatic-lower-level-menu-pages
*/
defined( 'ABSPATH' ) or die( 'Ha, gotcha!' );

remove_action( 'transition_post_status',     '_wp_auto_add_pages_to_menu', 10 );

add_action( 'transition_post_status',     'ptr_wp_auto_add_pages_to_menu', 10, 3 );

/**
 * Automatically add newly published page objects to menus with that as an option.
 *
 * @since 3.0.0
 * @access private
 *
 * @param string $new_status The new status of the post object.
 * @param string $old_status The old status of the post object.
 * @param object $post       The post object being transitioned from one status to another.
 */
function ptr_wp_auto_add_pages_to_menu( $new_status, $old_status, $post ) {
    if ( 'publish' != $new_status || 'publish' == $old_status || 'page' != $post->post_type )
        return;
    $auto_add = get_option( 'nav_menu_options' );
    if ( empty( $auto_add ) || ! is_array( $auto_add ) || ! isset( $auto_add['auto_add'] ) )
        return;
    $auto_add = $auto_add['auto_add'];
    if ( empty( $auto_add ) || ! is_array( $auto_add ) )
        return;

    $args = array(
        'menu-item-object-id' => $post->ID,
        'menu-item-object' => $post->post_type,
        'menu-item-type' => 'post_type',
        'menu-item-status' => 'publish',
    );

    foreach ( $auto_add as $menu_id ) {
        $items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish,draft' ) );
        if ( ! is_array( $items ) )
            continue;
        foreach ( $items as $item ) {
            if ( $post->ID == $item->object_id )
                continue 2;
        }

        if ( ! empty( $post->post_parent ) ) {
            foreach ( $items as $item ) {
                if( $post->post_parent == $item->object_id ) {
                    $args['menu-item-parent-id'] = $item->ID;
                    break;
                }
            }
        }

        wp_update_nav_menu_item( $menu_id, 0, $args );


    }
}