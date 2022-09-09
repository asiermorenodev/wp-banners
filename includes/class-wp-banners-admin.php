<?php

namespace WP_Banners;

/**
 * The assets-specific functionality of the plugin.
 *
 * @link       https://getkeypress.com
 * @since      1.0.0
 *
 * @package    RR
 * @subpackage RR/assets
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @package    RRB
 * @author     Asier Moreno <asier@asiermoreno.com>
 */
class WPB_Admin extends WPB_Core {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct() {
        parent::__construct();
        $this->define_constants();
    }

    private function define_constants() {
        self::define( 'WP_BANNERS_SETTINGS_PAGE_SLUG', 'wp-banners-settings' );
        self::define( 'WP_BANNERS_OPTION_BLOGS_CAN_CREATE_BANNERS', 'wp_banners_option_blogs_can_create_banners' );
    }

    public function run() {
        $this->register_hooks();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    public function register_hooks() {
        if ( is_multisite() ) {

            //TODO
            $can_blogs_create_banners = get_site_option( WP_BANNERS_OPTION_BLOGS_CAN_CREATE_BANNERS );

            if ( is_main_site() || $can_blogs_create_banners ) {
                add_action( 'init', array( $this, 'banner_post_type' ), 0 );
                add_action( 'admin_menu', array( $this, 'add_banner_post_type_settings_submenu' ) );
            }
        } else {
            add_action ( 'admin_menu', array( $this, 'add_banner_post_type_settings_submenu' ) );
        }
    }

    public function add_banner_post_type_settings_submenu() {
        $capability    = 'manage_options';
        $page_title    = apply_filters( 'wp_banners_settings_page_title', __( 'WP Banners Settings', 'wp_banners' ) );
        $submenu_title = apply_filters( 'wp_banners_settings_submenu_title', __( 'Settings', 'wp_banners' ) );
        $parent_slug   = 'edit.php?post_type=wp_banner';
        $submenu_slug  = WP_BANNERS_SETTINGS_PAGE_SLUG;
        $callback      = array( $this, 'render_banner_post_type_settings_page' );
        $position      = 30;

        add_submenu_page( $parent_slug, $page_title, $submenu_title, $capability, $submenu_slug, $callback, $position );
    }

    public function render_banner_post_type_settings_page() {
        ?>
        <h1><?php echo apply_filters( 'wp_banners_admin_page_heading', __( 'Settings', 'wp_banners' ) ); ?></h1>
        <?php
    }


    // Register Custom Post Type
    public function banner_post_type() {
        $labels = array(
            'name'                  => _x( 'Banners', 'Post Type General Name', 'wp_banners' ),
            'singular_name'         => _x( 'Banner', 'Post Type Singular Name', 'wp_banners' ),
            'menu_name'             => __( 'WP Banners', 'wp_banners' ),
            'name_admin_bar'        => __( 'Post Type', 'wp_banners' ),
            'archives'              => __( 'Banner Archives', 'wp_banners' ),
            'attributes'            => __( 'Banner Attributes', 'wp_banners' ),
            'parent_item_colon'     => __( 'Parent Banner:', 'wp_banners' ),
            'all_items'             => __( 'All Banners', 'wp_banners' ),
            'add_new_item'          => __( 'Add New Banner', 'wp_banners' ),
            'add_new'               => __( 'Add New', 'wp_banners' ),
            'new_item'              => __( 'New Banner', 'wp_banners' ),
            'edit_item'             => __( 'Edit Banner', 'wp_banners' ),
            'update_item'           => __( 'Update Banner', 'wp_banners' ),
            'view_item'             => __( 'View Banner', 'wp_banners' ),
            'view_items'            => __( 'View Banner', 'wp_banners' ),
            'search_items'          => __( 'Search Banner', 'wp_banners' ),
            'not_found'             => __( 'Not found', 'wp_banners' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'wp_banners' ),
            'featured_image'        => __( 'Featured Image', 'wp_banners' ),
            'set_featured_image'    => __( 'Set featured image', 'wp_banners' ),
            'remove_featured_image' => __( 'Remove featured image', 'wp_banners' ),
            'use_featured_image'    => __( 'Use as featured image', 'wp_banners' ),
            'insert_into_item'      => __( 'Insert into banner', 'wp_banners' ),
            'uploaded_to_this_item' => __( 'Uploaded to this banner', 'wp_banners' ),
            'items_list'            => __( 'Banners list', 'wp_banners' ),
            'items_list_navigation' => __( 'Banners list navigation', 'wp_banners' ),
            'filter_items_list'     => __( 'Filter banners list', 'wp_banners' ),
        );
        $capabilities = array(
            'edit_post'             => 'edit_post',
            'read_post'             => 'read_post',
            'delete_post'           => 'delete_post',
            'edit_posts'            => 'edit_posts',
            'edit_others_posts'     => 'edit_others_posts',
            'publish_posts'         => 'publish_posts',
            'read_private_posts'    => 'read_private_posts',
        );
        $args = array(
            'label'                 => __( 'Banner', 'wp_banners' ),
            'description'           => __( 'Post Type Description', 'wp_banners' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'editor' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => apply_filters( 'wp_banners_admin_menu_position', 70 ),
            'menu_icon'             => 'dashicons-flag',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'rewrite'               => false,
            'capabilities'          => $capabilities,
            'show_in_rest'          => false,
        );
        register_post_type( 'wp_banner', $args );
    }
}