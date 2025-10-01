<?php
/**
 * Footer Call Us Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Footer_Call_Us_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'footer_call_us_widget',
			esc_html__( 'Footer Call Us', 'kintaelectric' ),
			array( 'description' => esc_html__( 'Displays call us information in the footer.', 'kintaelectric' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		?>
		<div class="footer-call-us">
			<?php if ( ! empty( $instance['title'] ) ) : ?>
				<?php echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; ?>
			<?php endif; ?>
			<div class="media d-flex">
				<?php if ( ! empty( $instance['icon_class'] ) ) : ?>
					<span class="media-left call-us-icon media-middle"><i class="<?php echo esc_attr( $instance['icon_class'] ); ?>"></i></span>
				<?php endif; ?>
				<div class="media-body">
					<?php if ( ! empty( $instance['call_us_text'] ) ) : ?>
						<span class="call-us-text"><?php echo esc_html( $instance['call_us_text'] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $instance['phone_number'] ) ) : ?>
						<span class="call-us-number"><?php echo esc_html( $instance['phone_number'] ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$call_us_text = ! empty( $instance['call_us_text'] ) ? $instance['call_us_text'] : esc_html__( 'Got Questions ? Call us 24/7!', 'kintaelectric' );
		$phone_number = ! empty( $instance['phone_number'] ) ? $instance['phone_number'] : esc_html__( '(800) 8001-8588, (0600) 874 548', 'kintaelectric' );
		$icon_class = ! empty( $instance['icon_class'] ) ? $instance['icon_class'] : 'ec ec-support';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'call_us_text' ) ); ?>"><?php esc_attr_e( 'Call Us Text:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'call_us_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'call_us_text' ) ); ?>" type="text" value="<?php echo esc_attr( $call_us_text ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'phone_number' ) ); ?>"><?php esc_attr_e( 'Phone Number:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'phone_number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'phone_number' ) ); ?>" type="text" value="<?php echo esc_attr( $phone_number ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'icon_class' ) ); ?>"><?php esc_attr_e( 'Icon Class (e.g., ec ec-support):', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'icon_class' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'icon_class' ) ); ?>" type="text" value="<?php echo esc_attr( $icon_class ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['call_us_text'] = ( ! empty( $new_instance['call_us_text'] ) ) ? sanitize_text_field( $new_instance['call_us_text'] ) : '';
		$instance['phone_number'] = ( ! empty( $new_instance['phone_number'] ) ) ? sanitize_text_field( $new_instance['phone_number'] ) : '';
		$instance['icon_class'] = ( ! empty( $new_instance['icon_class'] ) ) ? sanitize_text_field( $new_instance['icon_class'] ) : '';
		return $instance;
	}
}
