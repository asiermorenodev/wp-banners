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
        self::define( 'WP_BANNERS_OPTION_SUBSITES_CAN_CREATE_BANNERS', 'wp_banners_option_subsites_can_create_banners' );

        self::define( 'WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES', '_wp_banner_is_available_on_subsites' );

        self::define( 'WP_BANNERS_VALUE_YES', 'yes' );
        self::define( 'WP_BANNERS_VALUE_NO', 'no' );
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
            $is_main_site             = is_main_site();
            $can_blogs_create_banners = get_site_option( WP_BANNERS_OPTION_SUBSITES_CAN_CREATE_BANNERS );

            if ( $is_main_site || $can_blogs_create_banners ) {
                add_action( 'admin_menu', array( $this, 'add_banner_post_type_settings_submenu' ) );
            }

            if ( $is_main_site ) {
                add_action( 'admin_init', array( $this, 'register_main_site_banner_meta_boxes' ) );
                add_action( 'save_post', array( $this, 'save_main_site_banner_meta_boxes' ) );
                add_filter( 'manage_wp_banner_posts_columns', array( $this, 'set_main_site_banner_columns' ) );
                add_action( 'manage_wp_banner_posts_custom_column' , array( $this, 'set_main_site_banner_columns_content' ), 10, 2 );
                add_filter( 'manage_edit-wp_banner_sortable_columns', array( $this, 'set_main_site_banner_sortable_columns' ) );
                add_action( 'pre_get_posts', array( $this, 'set_main_site_wp_banner_custom_orderby' ) );
            }
        } else {
            add_action ( 'admin_menu', array( $this, 'add_banner_post_type_settings_submenu' ) );
        }

        add_action( 'init', array( $this, 'register_banner_post_type' ) );
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
    public function register_banner_post_type() {
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
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => apply_filters( 'wp_banners_admin_menu_position', 70 ),
            'menu_icon'             => 'dashicons-flag',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'rewrite'               => false,
            'capabilities'          => $capabilities,
            'show_in_rest'          => false,
        );
        register_post_type( 'wp_banner', $args );
    }

    public function register_main_site_banner_meta_boxes() {
        add_meta_box(
            'wp_banner_metadata_available_on_blogs',
            'Availability on subsites',
            array( $this, 'render_banner_available_on_subsites_meta_box' ),
            'wp_banner',
            'normal',
            'core'
        );
    }

    public function render_banner_available_on_subsites_meta_box() {
        global $post;
        $banner = get_post_custom( $post->ID );

        if ( isset( $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ] ) && isset( $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ][0] )  ) {
            $value = $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ][0];
        } else {
            $value = WP_BANNERS_VALUE_YES;
        }

        ?>
        <p><?php _e( 'Should this banner be available on the subsites of this network?', 'wp_banner' ) ?></p>
        <div>
            <input type="radio"
                   id="wp_banner_available_on_subsites_yes"
                   name="<?php echo WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ?>"
                   value="<?php echo WP_BANNERS_VALUE_YES ?>"
                   <?php checked( $value, WP_BANNERS_VALUE_YES ) ?>
            />
            <label for="wp_banner_available_on_subsites_yes"><?php _e( 'Yes', 'wp_banner' ) ?></label>
        </div>
        <div>
            <input type="radio"
                   id="wp_banner_available_on_subsites_no"
                   name="<?php echo WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ?>"
                   value="<?php echo WP_BANNERS_VALUE_NO ?>"
                   <?php checked( $value, WP_BANNERS_VALUE_NO ) ?>
            />
            <label for="wp_banner_available_on_subsites_no"><?php _e( 'No', 'wp_banner' ) ?></label>
        </div>
        <?php
    }

    public function save_main_site_banner_meta_boxes() {
        global $post;

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( get_post_status( $post->ID ) === 'auto-draft' ) {
            return;
        }

        $sanitized_value = sanitize_text_field( $_POST[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ] );

        update_post_meta( $post->ID, WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES, $sanitized_value );
    }

    public function set_main_site_banner_columns( $columns ) {

        // Add the availability column after the title.
        $new_columns = array(
            'cb'                                         => $columns['cb'],
            'title'                                      => $columns['title'],
            WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES => __( 'Available on subsites', 'wp_banner' )
        );

        // We want to keep the columns that other plugins may have added.
        // So let's unset the ones we've already added and merge the rest.
        unset( $columns['cb'] );
        unset( $columns['title'] );

        return array_merge( $new_columns, $columns );
    }

    public function set_main_site_banner_columns_content( $column, $post_id ) {
        switch ( $column ) {
            case WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES :
                $is_available = get_post_meta( $post_id , WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES , true );

                if ( WP_BANNERS_VALUE_YES === $is_available ) {
                    echo __( 'Yes', 'wp_banner');
                } elseif ( WP_BANNERS_VALUE_NO === $is_available ) {
                    echo __( 'No', 'wp_banner');
                } else {
                    echo __( '--', 'wp_banner');
                }
                break;
        }
    }

    public function set_main_site_banner_sortable_columns( $columns ) {
        $columns[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ] = WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES;
        return $columns;
    }

    public function set_main_site_wp_banner_custom_orderby( $query ) {

        $orderby = $query->get( 'orderby' );

        if ( WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES === $orderby ) {
            $query->set( 'meta_key', WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES );
            $query->set( 'orderby', 'meta_value' ); // For numeric values use meta_value_num.
        }
    }
}