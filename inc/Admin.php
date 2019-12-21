<?php
namespace CloseForShabbat;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Admin {

    const PAGE = 'cfs';

    const PREFIX = self::PAGE . '_';

    const SECTION = self::PREFIX . 'main';

    const OPTION_NAME = 'close_for_shabbat';

    const GROUP = self::OPTION_NAME . '_group';

    private $fields = [];

    public function __construct()
    {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings'] );
    }

    public function add_menu() {
        add_menu_page(
            __( 'Close For Shabbat', 'cfs' ),
            __( 'Close For Shabbat', 'cfs' ),
            'manage_options',
            self::PAGE,
            [ $this, 'render_menu' ]
        );
    }

    public function register_settings() {
        $this->register_fields();

        register_setting( self::GROUP, self::OPTION_NAME );

        add_settings_section( self::SECTION, __( 'Settings', 'cfs' ), [ $this, 'render_section' ], self::PAGE );

        foreach ( $this->fields as $field ) {
            add_settings_field( $field['id'], $field['label'], [ $this, $field['callback'] ], self::PAGE, self::SECTION, $field );
        }
    }

    private function register_fields() {
        $this->fields = [
            [
                'id' => self::PREFIX . 'minutes_enter',
                'label' => __( 'Minutes to shabbat enter after sunset', 'cfs' ),
                'type' => 'number',
                'desc' => __( 'Adjust the minutes here to match your local "Hadlakat Nerot"', 'cfs' ),
                'callback' => 'callback_input',
            ],
            [
                'id' => self::PREFIX . 'minutes_exit',
                'label' => __( 'Minutes to shabbat exit after sunset', 'cfs' ),
                'type' => 'number',
                'desc' => __( 'Adjust the minutes here to match your local "Havdala"', 'cfs' ),
                'callback' => 'callback_input',
            ],
            [
                'id' => self::PREFIX . 'lat',
                'label' => __( 'Latitude', 'cfs' ),
                'type' => 'text',
                'desc' => sprintf ( __( 'check here: %s', 'cfs' ), 'https://www.gps-coordinates.net/' ),
                'callback' => 'callback_input',
            ],
            [
                'id' => self::PREFIX . 'long',
                'label' => __( 'Longitude', 'cfs' ),
                'type' => 'text',
                'desc' => sprintf ( __( 'check here: %s', 'cfs' ), 'https://www.gps-coordinates.net/' ),
                'callback' => 'callback_input',

            ],
            [
                'id' => self::PREFIX . 'page_id',
                'label' =>  __( 'Page to redirect', 'cfs' ),
                'type' => 'select',
                'choices' => Options::get_instance()->get_pages(),
                'desc' => __( 'The page users will be redirect to when is shabbat', 'cfs' ),
                'callback' => 'callback_select',
            ]
        ];
    }

    public function sanitize( $input ){
        return $input;
    }

    public function render_menu() {
        settings_errors();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Close For Shabbat', 'cfs' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( self::GROUP );
                do_settings_sections( self::PAGE );
                submit_button();
                ?>
            </form>
            <div class="shabbat-times">
                <?php
                echo '<strong>' . __( 'Next Shabbat', 'cfs' ) . ':</strong><br>';
                $shabbat_calc = ShabbatCalc::get_instance();
                $timezone = $shabbat_calc->get_time_zone();

                $next_friday = new \DateTime();
                $next_friday->setTimezone( $timezone );
                $next_friday->modify('next friday');
                $next_friday->add(new \DateInterval('PT2H'));

                $shabbat_calc->init_dt( $next_friday->getTimestamp() );
                $times = $shabbat_calc->get_times();
                $date_format = $shabbat_calc->get_date_format();
                $time_format = $shabbat_calc->get_time_format();

                if ( $times['start'] ) {
                    $start = $shabbat_calc->get_datetime( $times['start'] );

                    echo $start->format( "{$date_format} {$time_format}") . '<br>';
                }

                if ( $times['finish'] ) {
                    $finish = $shabbat_calc->get_datetime( $times['finish'] );

                    echo $finish->format( "{$date_format} {$time_format}") . '<br>';
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function render_section() {

    }

    public function callback_input( $args ) {
        $id = $args['id'];
        $value = Options::get_instance()->$id;
        $length = strlen(substr(strrchr($value, "."), 1));
        $placeholder = "%3$.{$length}f";

        $format = '<input type="%1$s" class="%2$s" name="%2$s" value="' . $placeholder . '">';

        printf( $format, $args['type'], self::OPTION_NAME . '[' . $args['id'] . ']', esc_attr( $value ) );

        if ( $args['desc'] ) {
            $format = '<p class="description" id="%1$s">%2$s</p>';

            printf( $format, $args['id'], esc_html( $args['desc'] ) );
        }
    }

    public function callback_select( $args ) {
        $options = '';
        $id = $args['id'];
        foreach ($args['choices'] as $choice ) {
            $selected = selected( $choice['value'], Options::get_instance()->$id, false );
            $options .= sprintf('<option value="%1$s"%2$s>%3$s</option>', $choice['value'], $selected, $choice['label'] );
        }
        $format = '<select class="%1$s" name="%2$s">%3$s</select>';

        printf( $format, $args['id'], self::OPTION_NAME . '[' . $args['id'] . ']', $options );

        if ( $args['desc'] ) {
            $format = '<p class="description" id="%1$s">%2$s</p>';

            printf( $format, $args['id'], esc_html( $args['desc'] ) );
        }
    }

}