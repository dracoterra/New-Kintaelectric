<?php
/**
 * Electro Mode Switcher Widget
 * Widget con apariencia moderna basada en index.html pero funcionalidad del header.php
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Electro Mode Switcher Widget Class
 */
class Electro_Mode_Switcher_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'electro_mode_switcher',
			esc_html__( 'Electro Mode Switcher', 'kintaelectric' ),
			array(
				'description' => esc_html__( 'Dark/Light mode toggle button with modern design. Can be used as shortcode: [electro_mode_switcher]', 'kintaelectric' ),
				'classname'   => 'widget_electro_mode_switcher',
			)
		);
	}

	/**
	 * Widget output
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$show_title = ! empty( $instance['show_title'] ) ? $instance['show_title'] : false;

		echo $args['before_widget'];

		if ( $show_title && $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
		}

		// Render the mode switcher with modern design
		$this->render_mode_switcher();

		echo $args['after_widget'];
	}

	/**
	 * Render the mode switcher HTML
	 * Modern design from index.html with independent functionality
	 */
	private function render_mode_switcher() {
		?>
		<div class="electro-mode-switcher-widget">
			<input class="checkbox" type="checkbox" id="electro-mode-toggle-widget" />
			<label class="toggle" for="electro-mode-toggle-widget">
				<i class="bi bi-sun icon icon--light"></i>
				<i class="bi bi-moon icon icon--dark"></i>
				<span class="ball"></span>
			</label>
		</div>
		<?php
	}

	/**
	 * Widget form
	 *
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$show_title = ! empty( $instance['show_title'] ) ? $instance['show_title'] : false;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_html_e( 'Title:', 'kintaelectric' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
				   name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
				   type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_title ); ?> 
				   id="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>" 
				   name="<?php echo esc_attr( $this->get_field_name( 'show_title' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_title' ) ); ?>">
				<?php esc_html_e( 'Show title', 'kintaelectric' ); ?>
			</label>
		</p>
		<p>
			<strong><?php esc_html_e( 'Shortcode:', 'kintaelectric' ); ?></strong><br>
			<code>[electro_mode_switcher]</code>
		</p>
		<?php
	}

	/**
	 * Update widget settings
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $old_instance Old widget instance.
	 * @return array Updated instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['show_title'] = ! empty( $new_instance['show_title'] );

		return $instance;
	}

	/**
	 * Shortcode handler
	 * Usage: [electro_mode_switcher]
	 */
	public static function shortcode_handler( $atts ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'show_title' => false,
		), $atts );

		ob_start();
		
		if ( $atts['show_title'] && $atts['title'] ) {
			echo '<h4 class="widget-title">' . esc_html( $atts['title'] ) . '</h4>';
		}
		
		// Create a temporary instance to use the render method
		$widget = new self();
		$widget->render_mode_switcher();
		
		return ob_get_clean();
	}
}

// Register the shortcode
add_shortcode( 'electro_mode_switcher', array( 'Electro_Mode_Switcher_Widget', 'shortcode_handler' ) );
