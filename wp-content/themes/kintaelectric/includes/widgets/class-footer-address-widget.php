<?php
/**
 * Footer Address Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Footer_Address_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'footer_address_widget',
			esc_html__( 'Footer Address', 'kintaelectric' ),
			array( 'description' => esc_html__( 'Displays address and contact info in the footer.', 'kintaelectric' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		?>
		<div class="footer-address">
			<?php if ( ! empty( $instance['title'] ) ) : ?>
				<strong class="footer-address-title"><?php echo esc_html( $instance['title'] ); ?></strong>
			<?php endif; ?>
			<?php if ( ! empty( $instance['address'] ) ) : ?>
				<address><?php echo nl2br( esc_html( $instance['address'] ) ); ?></address>
			<?php endif; ?>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Contact Info', 'kintaelectric' );
		$address = ! empty( $instance['address'] ) ? $instance['address'] : esc_html__( '17 Princess Road, London, Greater London NW1 8JR, UK', 'kintaelectric' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>"><?php esc_attr_e( 'Address:', 'kintaelectric' ); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'address' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'address' ) ); ?>" rows="5"><?php echo esc_textarea( $address ); ?></textarea>
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['address'] = ( ! empty( $new_instance['address'] ) ) ? sanitize_textarea_field( $new_instance['address'] ) : '';
		return $instance;
	}
}
