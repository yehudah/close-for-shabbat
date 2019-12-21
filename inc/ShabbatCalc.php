<?php
namespace CloseForShabbat;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Rarst\WordPress\DateTime\WpDateTimeZone;

class ShabbatCalc {

    private $dt;

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->init_dt();
    }

    public function is_shabbat() {
        $current_time = $this->get_current_time();
        $times = $this->get_times();

        return $current_time >= $times['start'] && $current_time <= $times['finish'];
    }

    /**
     * @param null $current_time Timestamp
     */
    public function init_dt( $current_time = null ) {
        $current_time = $current_time ? $current_time : $this->get_current_time();

        $this->dt = $this->get_datetime( $current_time );
    }

    /**
     * @return \DateTimeZone
     */
    public function get_time_zone() {
        $wp_version = get_bloginfo( 'version' );
        if ( version_compare( $wp_version, '5.3') >= 0 ) {
            return wp_timezone();
        }

        return new \DateTimeZone( WpDateTimeZone::getWpTimezone()->getName() );
    }

    /**
     * @return int
     */
    public function get_current_time() {
        $current_time = current_time( 'timestamp' );
        $dt = $this->get_datetime( $current_time );
        $hour = $dt->format( 'H' );

        /**
         * @todo
         * If current time is between 00:00-02:00
         * The sunset will return the previous day sunset
         */
        if ( absint( $hour ) < 3 ) {
            $dt->add(new \DateInterval('PT2H'));
        }

        return $dt->getTimestamp();
    }

    public function get_date_format() {
        return get_option( 'date_format' );
    }

    public function get_time_format() {
        return get_option( 'time_format' );
    }

    /**
     * @return array Shabbat start and finish timestamps
     */
    public function get_times() {
        $times = [];
        $day = $this->dt->format( 'l' );

        if ( $day === 'Friday' ) {
            $times['start'] = $this->calc_friday();
            $this->dt->modify('+1 day');
            $times['finish'] = $this->calc_saturday();
            $this->dt->modify('-1 day');
        }

        if ( $day === 'Saturday' ) {
            $this->dt->modify('-1 day');
            $times['start'] = $this->calc_friday();
            $this->dt->modify('+1 day');
            $times['finish'] = $this->calc_saturday();
        }

        return $times;
    }

    /**
     * @return int
     */
    public function calc_friday() {

        $sunset = $this->calc_sunset();

        // Shabta is 30 minutes before sunset
        $minutes_enter = Options::get_instance()->cfs_minutes_enter;
        $time = $sunset - ( $minutes_enter * 60 );

        // Sunset
        return $time;

    }

    /**
     * @return int
     */
    public function calc_saturday() {

        // Same sunset as Friday
        $sunset = $this->calc_sunset();

        // Shabat is finish 40 minutes after sunset
        $minutes_exit = Options::get_instance()->cfs_minutes_exit;
        $time = $sunset + ( $minutes_exit * 60 );

        // Sunset
        return $time;
    }

    /**
     * @return mixed
     */
    private function calc_sunset() {
        // Timezone offset in seconds
        $format = $this->dt->format('Z');

        // Specified in hours
        $offset = $format / 60 / 60;

        // Returns time of sunset for a given day and location
        $lat = Options::get_instance()->cfs_lat;
        $long = Options::get_instance()->cfs_long;

        return date_sunset( $this->dt->getTimestamp(), SUNFUNCS_RET_TIMESTAMP, $lat, $long, 90.50, $offset );
    }

    /**
     * Get DateTime object by timezone
     *
     * @param $timestamp
     * @return \DateTime
     */
    public function get_datetime( $timestamp ) {
        $dt = new \DateTime();
        $dt->setTimezone( $this->get_time_zone() );
        $dt->setTimestamp( $timestamp );

        return $dt;
    }

}