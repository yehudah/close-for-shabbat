<?php

/**
 * Plugin Name:       Close for shabbat
 * Plugin URI:        https://example.com/plugins/the-basics/
 * Description:       Close for shabbat.
 * Version:           1.0
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * Author:            Yehuda Hassine
 * Author URI:        https://author.example.com/
 * License:           GPL v3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       cfs
*/

namespace CloseForShabbat;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once 'vendor/autoload.php';

class Plugin {

    public function __construct()
    {
        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        add_action( 'init', [ $this, 'load_textdomain' ] );
        add_action( 'template_redirect', [ $this, 'check_if_shabbat' ], 999 );
        add_action( 'wp', [ $this, 'print_header' ], 999 );
        add_action( 'wp_footer', [ $this, 'print_credit' ], 999 );

        $this->init();
    }

    private function init() {
        new Admin();
    }

    public function activate() {
        if ( ! Options::get_instance()->load() ) {
            add_option( Admin::OPTION_NAME, [
                Admin::PREFIX . 'minutes_enter' => 40,
                Admin::PREFIX . 'minutes_exit' => 50,
                Admin::PREFIX . 'lat' => 32.434048,
                Admin::PREFIX . 'long' => 34.919651,
                Admin::PREFIX . 'zenith' => 90.50,
                Admin::PREFIX . 'page_id' => '',
            ]);
        }
    }

    public function deactivate() {
        delete_option( Admin::OPTION_NAME );
    }


    public function load_textdomain() {
        load_plugin_textdomain( Admin::PAGE, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function check_if_shabbat() {
        global $post;

        if ( ! ShabbatCalc::get_instance()->is_shabbat() ) {
            return;
        }

        $close_for_shabbat = Options::get_instance()->get_shabbat_page();
        if ( $close_for_shabbat === $post->ID ) {
            return;
        }

        if ( $close_for_shabbat ) {
            wp_redirect( esc_url( get_permalink( $close_for_shabbat ) ) );
            exit;
        }
    }

    public function print_header() {
        global $post;

        $close_for_shabbat = Options::get_instance()->get_shabbat_page();

        if ( $close_for_shabbat === $post->ID ) {
            header('HTTP/1.1 503 Service Temporarily Unavailable');
            header('Status: 503 Service Temporarily Unavailable');
            header('Retry-After: ' . Options::get_instance()->get_retry_after() );
        }
    }

    public function print_credit() {
        if ( ! ShabbatCalc::get_instance()->is_shabbat() ) {
            return;
        }

        echo '<div style="display: flex; justify-content: center; padding: 5px;">';
        echo '<p><a href="' . esc_url( Options::REPO_URL ) . '" target="_blank">Powered By Close For Shabbat</a></p>';
        echo '</div>';
    }
}

new Plugin();
