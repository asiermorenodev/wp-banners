<?php

namespace WP_Banners;

/**
 *
 * @package    RR
 * @author     Asier Moreno <asier@asiermoreno.com>
 */
class WPB_Public extends WPB_Core {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    public function register_hooks() {
        error_log('WPB_Public: register_hooks');
        add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
    }

    public function register_rest_route() {
        register_rest_route( 'rr/v1', '/wp-banner/',
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'get_banner_data' ),
            )
        );
    }

    public function get_banner_data( \WP_REST_Request $request ){
        // TODO Sanitize and check errors.

        $site_id = $request->get_param( 'site-id' );
        $banner_id = $request->get_param( 'banner-id' );

        if ( ! isset( $banner_id ) ) {
            $banner_id = '5'; //TODO
        }

        $site = get_site( $site_id );

        $name = $site->blogname;
        $url  = $site->siteurl;

        $data = array(
            'id' => $site_id,
            'name' => $name,
            'url' => $url,
            'assets' => array(
                array(
                    'id'    => 'rr-banner-css',
                    'type'  => 'text/css',
                    'href'  => network_home_url() . 'wp-content/plugins/wp-banners/assets/css/rr-banner.css',
                    'rel'   => 'stylesheet',
                    'media' => 'all',
                ),
                array(
                    'id'   => 'rr-quicksand-woff',
                    'href' => network_home_url() . 'wp-content/themes/recycling-rules-subsites/fonts/Quicksand-VariableFont_wght.woff',
                    'type' => 'font/woff',
                    'rel'  => 'preload',
                    'as'   => 'font',
                ),
                array(
                    'id'   => 'rr-quicksand-bold-woff',
                    'href' => network_home_url() . 'wp-content/themes/recycling-rules-subsites/fonts/Quicksand-Bold.woff',
                    'type' => 'font/woff',
                    'rel'  => 'preload',
                    'as'   => 'font',
                ),
            ),
            'html' => $this->get_banner_HTML( $banner_id, $site_id),
        );
        return $data;
    }

    private function get_banner_HTML( $id, $site_id ) {

        // https://www.mugo.ca/Blog/Adding-complex-fields-to-WordPress-custom-post-types

        $wp_banner = get_post( $id );

        if ( isset ( $wp_banner ) ) {
            return $wp_banner->post_content;
        } else {
            ob_start();
            ?>
            <p>Banner not found</p>
            <?php
            return ob_get_clean();
        }


        /***************/



        $site = get_site( $site_id );

        switch_to_blog( $site_id );
        $paragraph_name = get_option( RR_OPTION_PARAGRAPH_NAME );
        $logo_url       = get_option( RR_OPTION_LOGO_IMAGE );
        $site_url       = $site->siteurl;
        $rrprogramtype                    = defined( 'RR_OPTION_RECYCLING_PROGRAM' ) ? get_option( RR_OPTION_RECYCLING_PROGRAM ) : '';
        $rr_display_recycling_center_link = do_shortcode('[rro-field-value form-id="1" field-id="62"]');
        restore_current_blog();

        ob_start();

        switch ( $version ) {
            case '3':
                ?>
                <div class="rr-1-2">
                    <div class="rr-banner-logo">
                        <img src="<?php echo esc_url( $logo_url ) ?>" alt="<?php echo esc_attr( sprintf( 'Recycling Rules %s Logo', $paragraph_name ) ) ?>" title="<?php echo esc_attr( sprintf( 'Recycling Rules %s Logo', $paragraph_name ) ) ?>"/>
                    </div>
                    <div class="rr-banner-text">
                        <h3><?php echo esc_html( sprintf( 'Everything you wanted to know about recycling in %s', $paragraph_name ) ) ?></h3>
                    </div>
                    <div class="rr-banner-cta">
                        <a href="<?php echo esc_url( $site_url )?>" target="_blank">Go To Recycling Rules ></a>
                    </div>
                </div>
                <div class="rr-2-2">
                    <ul>
                        <li class="rr-recycling-guide">
                            <a href="<?php echo esc_url( $site_url . '/recycling-guide' )?>" target="_blank"><span>Recycling Guide</span></a>
                        </li>
                        <?php if (true ) : ?>
                            <li class="rr-recycling-center">
                                <a href="<?php echo esc_url( $site_url . '/recycling-center' )?>" target="_blank"><span>Recycling Center</span></a>
                            </li>
                        <?php endif; ?>
                        <li class="rr-where">
                            <a href="<?php echo esc_url( $site_url . '/where-does-my-recycling-go' )?>" target="_blank"><span>Where's My Stuff Go?</span></a>
                        </li>
                        <li class="rr-recycling-economics">
                            <a href="<?php echo esc_url( $site_url . '/recycling-economics' )?>" target="_blank"><span>Recycling Economics</span></a>
                        </li>
                        <li class="rr-disposal-dictionary">
                            <a href="<?php echo esc_url( $site_url . '/disposal-dictionary' )?>" target="_blank"><span>Disposal Dictionary</span></a>
                        </li>
                    </ul>
                </div>
                <?php
                break;

            case '2':
                ?>
                <div class="rr-banner-text v2">
                    <p><?php echo esc_html( sprintf( "We've partnered with the nonprofit Recycling Rules to provide residents of %s with the following recycling information", $paragraph_name ) ) ?></p>
                </div>
                <div class="rr-banner-cta v2">
                    <a href="<?php echo esc_url( $site_url )?>" target="_blank"><?php echo esc_html( sprintf( 'Recycling Rules %s', $paragraph_name ) ) ?></a>
                </div>
                <?php
                break;

            case '1':
            default :
                ?>
                <div class="rr-banner-text v1">
                    <h3><?php echo esc_html( sprintf( 'Recycling Rules %s', $paragraph_name ) ) ?></h3>
                    <p><?php echo esc_html( sprintf( 'Everything you wanted to know about recycling in %s', $paragraph_name ) ) ?></p>
                </div>
                <div class="rr-banner-cta v1">
                    <a href="<?php echo esc_url( $site_url )?>" target="_blank"><?php echo esc_html( sprintf( 'Recycling Rules %s >', $paragraph_name ) ) ?></a>
                </div>
                <div class="rr-banner-logo v1">
                    <img src="<?php echo esc_url( $logo_url ) ?>" alt="<?php echo esc_attr( sprintf( 'Recycling Rules %s Logo', $paragraph_name ) ) ?>" title="<?php echo esc_attr( sprintf( 'Recycling Rules %s Logo', $paragraph_name ) ) ?>"/>
                </div>
            <?php
        }

        return ob_get_clean();
    }
}
