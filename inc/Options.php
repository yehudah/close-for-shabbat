<?php
namespace CloseForShabbat;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Options {

    const REPO_URL = 'https://github.com/yehudah/close-for-shabbat';

    private static $instance;

    private $options;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->options = get_option( Admin::OPTION_NAME );
    }

    public function __get($name)
    {
        if ( isset( $this->options[$name] ) ) {
            return $this->options[$name];
        }

        return '';
    }

    public function load() {
        return $this->options;
    }

    public function get_shabbat_page() {
        return $this->options['cfs_page_id'] ? absint( $this->options['cfs_page_id'] ) : false;
    }

    public function get_pages() {
        $pages = new \WP_Query([
            'post_type' => apply_filters( Admin::PREFIX . 'pages_cpt', 'page' ),
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ]);

        return array_map(function ($key, $value) {
            return array(
                'label' => $value->post_title,
                'value' => $value->ID
            );
        }, array_keys($pages->posts), $pages->posts);
    }

    public function get_retry_after() {
        $shabbat_calc = ShabbatCalc::get_instance();
        $times = $shabbat_calc->get_times();

        return  $times['finish'] - $shabbat_calc->get_current_time( false );
    }
}