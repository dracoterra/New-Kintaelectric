<?php
/**
 * Image Widget for Footer
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Image Widget Class
 */
class KintaElectric_Image_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'kintaelectric_image_widget',
			esc_html__( 'Footer Image Widget', 'kintaelectric' ),
			array(
				'description' => esc_html__( 'Display an image with custom URL link in footer.', 'kintaelectric' ),
			)
		);
	}

	/**
	 * Widget form
	 */
	public function form( $instance ) {
		$image_url = ! empty( $instance['image_url'] ) ? $instance['image_url'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '';
		$alt_text = ! empty( $instance['alt_text'] ) ? $instance['alt_text'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '329';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '359';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'image_url' ) ); ?>"><?php esc_html_e( 'Image URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'image_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_url' ) ); ?>" type="url" value="<?php echo esc_attr( $image_url ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>"><?php esc_html_e( 'Link URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_url' ) ); ?>" type="url" value="<?php echo esc_attr( $link_url ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>"><?php esc_html_e( 'Alt Text:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text' ) ); ?>" type="text" value="<?php echo esc_attr( $alt_text ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>"><?php esc_html_e( 'Width (px):', 'kintaelectric' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" type="number" value="<?php echo esc_attr( $width ); ?>" min="100" max="800">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_html_e( 'Height (px):', 'kintaelectric' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" type="number" value="<?php echo esc_attr( $height ); ?>" min="100" max="800">
		</p>
		<?php
	}

	/**
	 * Update widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['image_url'] = ( ! empty( $new_instance['image_url'] ) ) ? esc_url_raw( $new_instance['image_url'] ) : '';
		$instance['link_url'] = ( ! empty( $new_instance['link_url'] ) ) ? esc_url_raw( $new_instance['link_url'] ) : '';
		$instance['alt_text'] = ( ! empty( $new_instance['alt_text'] ) ) ? sanitize_text_field( $new_instance['alt_text'] ) : '';
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? absint( $new_instance['width'] ) : 329;
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? absint( $new_instance['height'] ) : 359;
		
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance ) {
		$image_url = ! empty( $instance['image_url'] ) ? $instance['image_url'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '#';
		$alt_text = ! empty( $instance['alt_text'] ) ? $instance['alt_text'] : '';
		$width = ! empty( $instance['width'] ) ? absint( $instance['width'] ) : 329;
		$height = ! empty( $instance['height'] ) ? absint( $instance['height'] ) : 359;

		echo $args['before_widget'];
		?>
		<aside class="widget clearfix widget_media_image">
			<div class="body">
				<?php if ( ! empty( $image_url ) ) : ?>
					<a href="<?php echo esc_url( $link_url ); ?>">
						<img loading="lazy" width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( $height ); ?>" 
							src="<?php echo esc_url( $image_url ); ?>" 
							class="image attachment-full size-full" 
							alt="<?php echo esc_attr( $alt_text ); ?>" 
							style="max-width: 100%; height: auto;" 
							decoding="async">
					</a>
				<?php else : ?>
					<!-- Fallback placeholder -->
					<a href="<?php echo esc_url( $link_url ); ?>">
						<img loading="lazy" width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( $height ); ?>" 
							src="<?php echo kintaelectric_ASSETS_URL; ?>images/placeholder-<?php echo esc_attr( $width ); ?>x<?php echo esc_attr( $height ); ?>.png" 
							class="image attachment-full size-full" 
							alt="<?php echo esc_attr( $alt_text ); ?>" 
							style="max-width: 100%; height: auto;" 
							decoding="async">
					</a>
				<?php endif; ?>
			</div>
		</aside>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Register Image Widget
 */
function kintaelectric_register_image_widget() {
	register_widget( 'KintaElectric_Image_Widget' );
}
add_action( 'widgets_init', 'kintaelectric_register_image_widget' );
