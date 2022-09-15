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
        self::define( 'WP_BANNERS_PAGE_SLUG', 'wp-banners' );
        self::define( 'WP_BANNERS_SETTINGS_PAGE_SLUG', 'wp-banners-settings' );
        self::define( 'WP_BANNERS_SETTINGS_OPTIONS', 'wp-banners-settings' );

        self::define( 'WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES', 'wp-banners-settings-option-activable-on-subsites' );
        self::define( 'WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE', 'wp-banners-settings-option-subsites-mode' );

        self::define( 'WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES', '_wp_banner_is_available_on_subsites' );

        /*
         * WordPress' update_option stores the string '1' for true and an empty string for false.
         * For convenience, we will use the strings true and false instead.
         */
        self::define( 'WP_BANNERS_VALUE_TRUE', 'true' );
        self::define( 'WP_BANNERS_VALUE_FALSE', 'false' );

        self::define( 'WP_BANNERS_SUBSITES_MODE_CRUD', 'crud' );
        self::define( 'WP_BANNERS_SUBSITES_MODE_READONLY', 'readonly' );
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
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'init', array( $this, 'register_wp_banner_post_type' ) );
        add_action( 'admin_init', array( $this, 'register_wp_banner_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_wp_banner_meta_boxes' ) );
        add_filter( 'manage_wp_banner_posts_columns', array( $this, 'set_wp_banner_columns' ) );
        add_action( 'manage_wp_banner_posts_custom_column' , array( $this, 'set_wp_banner_columns_content' ), 10, 2 );
        add_filter( 'manage_edit-wp_banner_sortable_columns', array( $this, 'set_wp_banner_sortable_columns' ) );
        add_action( 'pre_get_posts', array( $this, 'set_wp_banner_custom_orderby' ) );

        // TODO: Conditional
        add_action( 'admin_init', array( $this, 'settings_page_init') );
    }

    public function add_admin_menu() {

        /**
         * The menu is created when registering the wp_banner post type.
         * Se we just need to add the settings submenu.
         */
        if ( is_main_site() ) {

            add_submenu_page(
                    'edit.php?post_type=wp_banner',
                    __( 'WP Banners', 'wp_banners' ) ,
                    __( 'Settings', 'wp_banners' ),
                    'manage_options',
                    WP_BANNERS_SETTINGS_PAGE_SLUG,
                    array( $this, 'render_settings_page' ),
                    30
            );
        } /*elseif ( $this->is_plugin_activable_on_subsites() && WP_BANNERS_SUBSITES_MODE_CRUD === $this->get_subsites_mode() ) {

            add_menu_page(
                __( 'WP Banners', 'wp_banners' ) ,
                __( 'WP Bannes', 'wp_banners' ),
                'manage_options',
                WP_BANNERS_PAGE_SLUG,
                array( $this, 'render_banner_post_type_settings_page' ),
                'dashicons-flag',
                70
            );
        }*/
    }

    public function settings_page_init() {

        $args = array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'validate_settings_page_options' ),
            'default'           => null
        );

        register_setting( WP_BANNERS_SETTINGS_OPTIONS, WP_BANNERS_SETTINGS_OPTIONS, $args );

        add_settings_section(
            'wp_banners_settings',
            __( 'Settings', 'wp_settings' ),
            array( $this, 'render_settings_section' ),
            WP_BANNERS_SETTINGS_PAGE_SLUG
        );

        add_settings_field(
            'wp_banners_is_activable_on_subsites',
            __( 'Activable on subsites', 'wp_banners' ),
            array( $this, 'render_is_activable_on_subsites_field' ),
            WP_BANNERS_SETTINGS_PAGE_SLUG,
            'wp_banners_settings',
        );

        add_settings_field(
            'wp_banners_subsites_mode',
            __( 'Subsites mode', 'wp_banners' ),
            array( $this, 'render_subsites_mode_field' ),
            WP_BANNERS_SETTINGS_PAGE_SLUG,
            'wp_banners_settings',
        );
    }

    public function get_settings_options() {
        return array_replace( $this->get_default_options(), get_option( WP_BANNERS_SETTINGS_OPTIONS, array() ) );
    }
    /**
     *
     * @param array $args
     */
    public function render_is_activable_on_subsites_field( $args ) {
        $options = $this->get_settings_options();
        $option  = $options[ WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES ];
        $values  = [ [ WP_BANNERS_VALUE_FALSE, __( 'No', 'wp_banner' ) ], [ WP_BANNERS_VALUE_TRUE, __( 'Yes', 'wp_banner' ) ] ];

        foreach ( $values as $value ) {
            ?>
            <div>
                <input type="radio"
                       id="wp-banner-activable-on-subsites-<?php echo esc_attr( $value[0] ) ?>"
                       name="<?php echo esc_attr( sprintf( '%s[%s]', WP_BANNERS_SETTINGS_OPTIONS, WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES ) ) ?>"
                       value="<?php echo esc_attr( $value[0] ) ?>"
                    <?php checked( $option, $value[0] ) ?>
                />
                <label for="wp-banner-activable-on-subsites-<?php echo esc_attr( $value[0] ) ?>"><?php echo esc_html( $value[1] ) ?></label>
            </div>
            <?php
        }
        ?>
        <p class="description"><?php esc_html_e( 'Whether the plugin can be activated on the subsites of this network or not.'. 'wp_banners' ) ?></p>
        <?php
    }

    // TODO Display field only ic activable on subsites.
    public function render_subsites_mode_field( $args ) {
        $options = $this->get_settings_options();
        $option  = $options[ WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE ];
        $values  = [
                        [ WP_BANNERS_SUBSITES_MODE_CRUD, __( 'CRUD. Subsites admins can Create, Read, Update and Delete their own banners. Banners created on the main site are NOT accesible.', 'wp_banner' ) ],
                        [ WP_BANNERS_SUBSITES_MODE_READONLY, __( 'Read only. Subsites admins can NOT Create their own banners. They can access the banners created on the main site (read only). ', 'wp_banner' ) ]
                    ];

        foreach ( $values as $value ) {
            ?>
            <div>
                <input type="radio"
                       id="wp-banner-subsites-mode-<?php echo esc_attr( $value[0] ) ?>"
                       name="<?php echo esc_attr( sprintf( '%s[%s]', WP_BANNERS_SETTINGS_OPTIONS, WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE ) ) ?>"
                       value="<?php echo esc_attr( $value[0] ) ?>"
                    <?php checked( $option, $value[0] ) ?>
                />
                <label for="wp-banner-activable-on-subsites-<?php echo esc_attr( $value[0] ) ?>"><?php echo esc_html( $value[1] ) ?></label>
            </div>
            <?php
        }
    }

    public function get_default_options() {
        return array(
            WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES => WP_BANNERS_VALUE_FALSE,
            WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE         => WP_BANNERS_SUBSITES_MODE_CRUD,
        );
    }

     /**
     *
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    public function render_settings_section( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi leo lacus, efficitur et lacus id, interdum ornare tortor.', 'wp_banners' ); ?>
        </p>
        <?php
    }

    public function render_settings_page() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( empty( get_settings_errors( WP_BANNERS_SETTINGS_OPTIONS ) ) && isset( $_GET['settings-updated'] ) ) {
            add_settings_error( WP_BANNERS_SETTINGS_OPTIONS, 'wp-banners-settings-updated', __( 'Settings Saved', 'wp_banners' ), 'updated' );
        }

        // show error/update messages
        settings_errors( WP_BANNERS_SETTINGS_OPTIONS );

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting
                settings_fields( WP_BANNERS_SETTINGS_OPTIONS );

                // output setting sections and their fields
                do_settings_sections( WP_BANNERS_SETTINGS_PAGE_SLUG );

                // output save settings button
                submit_button( __( 'Save Settings', 'wp_banners' ) );
                ?>
            </form>
        </div>
        <?php
    }

    public function validate_settings_page_options( $input ) {
        $output = array();

        foreach ( $input as $key => $value ) {
            switch ( $key ) {
                case WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES:

                    if ( ! in_array( $value, [ WP_BANNERS_VALUE_FALSE, WP_BANNERS_VALUE_TRUE ] ) ) {
                        add_settings_error(
                            WP_BANNERS_SETTINGS_OPTIONS,
                            'wp-banners-error-incorrect-value',
                            __( 'Incorrect value entered.', 'wp_banners' ),
                            'error'
                        );
                    }

                    $output[ $key ] = $value;
                    break;

                case WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE:
                    if ( ! in_array( $value, [ WP_BANNERS_SUBSITES_MODE_CRUD, WP_BANNERS_SUBSITES_MODE_READONLY ] ) ) {
                        add_settings_error(
                            WP_BANNERS_SETTINGS_OPTIONS,
                            'wp-banners-error-incorrect-value',
                            __( 'Incorrect value entered.', 'wp_banners' ),
                            'error'
                        );
                    }

                    $output[ $key ] = $value;
                    break;
                default:

            }
        }

        return $output;
    }


    // Register Custom Post Type
    public function register_wp_banner_post_type() {
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
            'show_ui'               => false,
            'show_in_menu'          => false,
            'menu_position'         => 70,
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

        if ( $this->display_banner_cpt_admin_menu() ) {
            $args[ 'show_ui' ]      = true;
            $args[ 'show_in_menu' ] = true;
        }

        register_post_type( 'wp_banner', $args );
    }

    private function display_banner_cpt_admin_menu() {
        return is_main_site() || ( $this->is_plugin_activable_on_subsites() && WP_BANNERS_SUBSITES_MODE_CRUD === $this->get_subsites_mode() );
    }

    public function register_wp_banner_meta_boxes() {
        
        if ( is_multisite() && $this->is_plugin_activable_on_subsites() && WP_BANNERS_SUBSITES_MODE_READONLY === $this->get_subsites_mode() ) {
            add_meta_box(
                'wp_banner_metadata_available_on_subsites',
                'Availability on subsites',
                array( $this, 'render_banner_available_on_subsites_meta_box' ),
                'wp_banner',
                'normal',
                'core'
            );
        }

        // Register the meta box that displays the code to paste on sites in order to load the banner.
        add_meta_box(
            'wp_banner_metadata_banner-code',
            'Banner code',
            array( $this, 'render_banner_code_meta_box' ),
            'wp_banner',
            'normal',
            'core'
        );
    }

    public function render_banner_available_on_subsites_meta_box() {
        global $post;
        $banner = get_post_custom( $post->ID );

        if ( isset( $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ] ) && isset( $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ][0] )  ) {
            $is_available_on_subsites = $banner[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ][0];
        } else {
            $is_available_on_subsites = WP_BANNERS_VALUE_FALSE;
        }

        $values  = [ [ WP_BANNERS_VALUE_FALSE, __( 'No', 'wp_banner' ) ], [ WP_BANNERS_VALUE_TRUE, __( 'Yes', 'wp_banner' ) ] ];

        ?>
        <p><?php _e( 'Should this banner be available on the subsites of this network?', 'wp_banner' ) ?></p>
        <?php

        foreach ( $values as $value ) {
            ?>
            <div>
                <input type="radio"
                       id="wp_banner_available_on_subsites_<?php echo esc_attr( $value[0] ) ?>"
                       name="<?php echo esc_attr( WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ) ?>"
                       value="<?php echo esc_attr( $value[0] ) ?>"
                    <?php checked( $is_available_on_subsites, $value[0] ) ?>
                />
                <label for="wp_banner_available_on_subsites_<?php echo esc_attr( $value[0] ) ?>"><?php echo esc_html( $value[1] ) ?></label>
            </div>
            <?php
        }
    }

    public function render_banner_code_meta_box() {
        global $post;

        $attributes = array(
            'type' => 'text/javascript',
            'src' => esc_url( WP_BANNERS_PLUGIN_URL . '/assets/js/wp-banners.js' ),
            'id' => 'wp-banners-script',
            'data-id' => esc_attr( $post->ID ),
        );

        if ( is_multisite() && $this->is_plugin_activable_on_subsites() && WP_BANNERS_SUBSITES_MODE_CRUD === $this->get_subsites_mode() ) {
            $attributes['data-site-id'] = get_current_blog_id();
        }

        $output = '<code>&lt;script';
        foreach ( $attributes as $key => $value ) {
            $output .= sprintf( ' %s="%s"', $key, $value );
        }
        $output .= '&gt;&lt;/script&gt;</code>';
        echo $output;
    }

    public function save_wp_banner_meta_boxes() {
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

    public function set_wp_banner_columns( $columns ) {

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

    public function set_wp_banner_columns_content( $column, $post_id ) {
        switch ( $column ) {
            case WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES :
                $is_available = get_post_meta( $post_id , WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES , true );

                if ( WP_BANNERS_VALUE_TRUE === $is_available ) {
                    echo __( 'Yes', 'wp_banner');
                } elseif ( WP_BANNERS_VALUE_FALSE === $is_available ) {
                    echo __( 'No', 'wp_banner');
                } else {
                    echo __( '--', 'wp_banner');
                }
                break;
        }
    }

    public function set_wp_banner_sortable_columns( $columns ) {
        $columns[ WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES ] = WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES;
        return $columns;
    }

    public function set_wp_banner_custom_orderby( $query ) {

        $orderby = $query->get( 'orderby' );

        if ( WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES === $orderby ) {
            $query->set( 'meta_key', WP_BANNERS_META_KEY_IS_AVAILABLE_ON_SUBSITES );
            $query->set( 'orderby', 'meta_value' ); // For numeric values use meta_value_num.
        }
    }

    public function is_plugin_activable_on_subsites() {
        switch_to_blog( get_main_site_id( ) );
        $options = $this->get_settings_options();
        $is_activable_on_subsites = WP_BANNERS_VALUE_TRUE === $options[ WP_BANNERS_SETTINGS_OPTION_ACTIVABLE_ON_SUBSITES ];
        restore_current_blog();
        return $is_activable_on_subsites;
    }

    public function get_subsites_mode() {
        switch_to_blog( get_main_site_id( ) );
        $options = $this->get_settings_options();
        $subsites_mode = $options[ WP_BANNERS_SETTINGS_OPTION_SUBSITES_MODE ];
        restore_current_blog();
        return $subsites_mode;
    }

    // TODO
    public function is_plugin_active_on_subsites() {
        return true;
    }
}


