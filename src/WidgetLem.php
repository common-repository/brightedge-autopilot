<?php
namespace BrightEdge\Wordpress;

use WP_Widget;

if ( !class_exists( __NAMESPACE__ . 'BEIXFWidgetLem' ) ) {
    class BEIXFWidgetLem extends WP_Widget {
        // class constructor
        public function __construct() {

            $description = esc_html__( 'Generates BrightEdge Link Block', 'be_ixf_php_wp' );

            parent::__construct(
                'wp_be_ixf_lem_widget',
                __( 'BrightEdge Link Equity Block', 'be_ixf_php_wp' ),
                array( 'classname' => 'wp_be_ixf_lem_widget', 'description' => $description ),
                array( 'width' => 200, 'height' => 250, 'id_base' => 'wp_be_ixf_lem_widget' )
            );
        }


        public function widget( $args, $instance ) {

            $options = BEIXFController::getPluginOptions();

            if ($options['strategy'] != 'Widget' || $options['disabled'] == 'Disabled' ){
                return;
            }
            extract( $args );
            echo $args['before_widget'];

            $lem_controller = new BEIXFController();
            if ( $lem_controller->loaded ) {
                $lem_view = new BEIXFView($lem_controller);
                echo $lem_view->ixfRenderWidgetBlock();
            }

            echo $args['after_widget'];
        }

        // output the option form field in admin Widgets screen
        public function form( $instance ) {

            $options = BEIXFController::getPluginOptions();
            if (!isset($options['heading'])) {
                $title = '';
            } else {
                $title = $options['heading'];
            }
            ?>
            <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
            <?php esc_attr_e( 'Title:', 'be_ixf_php_wp' ); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                type="text"
                value="<?php echo esc_attr( $title ); ?>">
            </p>
            <?php
        }

        // save options
        public function update( $new_instance, $old_instance ) {
            $instance = array();
            $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
            // get option settings
            $options = get_option('be_ixf');
            $options['heading'] = $instance['title'];
            // save to plugin options table
            update_option('be_ixf', $options);
            return $instance;
        }
    }
}
