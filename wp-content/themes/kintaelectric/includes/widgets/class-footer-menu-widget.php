<?php
/**
 * Footer Menu Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Footer_Menu_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'footer_menu_widget',
			esc_html__( 'Footer Menu', 'kintaelectric' ),
			array( 'description' => esc_html__( 'Displays a navigation menu in the footer.', 'kintaelectric' ) )
		);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;
		$menu_class = ! empty( $instance['menu_class'] ) ? $instance['menu_class'] : 'menu';

		if ( ! $nav_menu ) {
			return;
		}

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		wp_nav_menu( array(
			'fallback_cb' => '',
			'menu'        => $nav_menu,
			'container'   => 'div',
			'menu_class'  => $menu_class,
		) );

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$menu_class = ! empty( $instance['menu_class'] ) ? $instance['menu_class'] : 'menu';

		// Get menus
		$menus = wp_get_nav_menus();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>"><?php esc_attr_e( 'Select Menu:', 'kintaelectric' ); ?></label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'nav_menu' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'nav_menu' ) ); ?>" class="widefat">
				<option value="0"><?php esc_html_e( '&mdash; Select &mdash;', 'kintaelectric' ); ?></option>
				<?php foreach ( $menus as $menu ) : ?>
					<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu, $menu->term_id ); ?>>
						<?php echo esc_html( $menu->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'menu_class' ) ); ?>"><?php esc_attr_e( 'Menu CSS Class:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'menu_class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'menu_class' ) ); ?>" type="text" value="<?php echo esc_attr( $menu_class ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['nav_menu'] = ( ! empty( $new_instance['nav_menu'] ) ) ? (int) $new_instance['nav_menu'] : 0;
		$instance['menu_class'] = ( ! empty( $new_instance['menu_class'] ) ) ? sanitize_text_field( $new_instance['menu_class'] ) : '';
		return $instance;
	}
}
