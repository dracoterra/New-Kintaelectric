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
		$image_id = ! empty( $instance['image_id'] ) ? $instance['image_id'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '';
		$alt_text = ! empty( $instance['alt_text'] ) ? $instance['alt_text'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '329px';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '359px';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>"><?php esc_html_e( 'Image:', 'kintaelectric' ); ?></label>
			<div class="kintaelectric-image-widget-container">
				<input type="hidden" class="kintaelectric-image-id" id="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>" value="<?php echo esc_attr( $image_id ); ?>">
				<div class="kintaelectric-image-preview" style="<?php echo empty( $image_id ) ? 'display: none;' : ''; ?>">
					<?php if ( ! empty( $image_id ) ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'thumbnail' ); ?>
					<?php endif; ?>
				</div>
				<button type="button" class="button kintaelectric-select-image"><?php esc_html_e( 'Select Image', 'kintaelectric' ); ?></button>
				<button type="button" class="button kintaelectric-remove-image" style="<?php echo empty( $image_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'kintaelectric' ); ?></button>
			</div>
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
			<label for="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>"><?php esc_html_e( 'Width:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'width' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'width' ) ); ?>" type="text" value="<?php echo esc_attr( $width ); ?>" placeholder="329px, 100%, auto">
			<small><?php esc_html_e( 'Examples: 329px, 100%, auto, 50vw', 'kintaelectric' ); ?></small>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_html_e( 'Height:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" type="text" value="<?php echo esc_attr( $height ); ?>" placeholder="359px, auto, 50vh">
			<small><?php esc_html_e( 'Examples: 359px, auto, 50vh, 100%', 'kintaelectric' ); ?></small>
		</p>
		<?php
	}

	/**
	 * Update widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['image_id'] = ( ! empty( $new_instance['image_id'] ) ) ? absint( $new_instance['image_id'] ) : '';
		$instance['link_url'] = ( ! empty( $new_instance['link_url'] ) ) ? esc_url_raw( $new_instance['link_url'] ) : '';
		$instance['alt_text'] = ( ! empty( $new_instance['alt_text'] ) ) ? sanitize_text_field( $new_instance['alt_text'] ) : '';
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? sanitize_text_field( $new_instance['width'] ) : '329px';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? sanitize_text_field( $new_instance['height'] ) : '359px';
		
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance ) {
		$image_id = ! empty( $instance['image_id'] ) ? $instance['image_id'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '#';
		$alt_text = ! empty( $instance['alt_text'] ) ? $instance['alt_text'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '329px';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '359px';

		echo $args['before_widget'];
		?>
		<aside class="widget clearfix widget_media_image">
			<div class="body">
				<?php if ( ! empty( $image_id ) ) : ?>
					<?php
					$image_url = wp_get_attachment_image_url( $image_id, 'full' );
					$image_alt = ! empty( $alt_text ) ? $alt_text : get_post_meta( $image_id, '_wp_attachment_image_alt', true );
					?>
					<a href="<?php echo esc_url( $link_url ); ?>">
						<img loading="lazy" 
							src="<?php echo esc_url( $image_url ); ?>" 
							class="image attachment-full size-full" 
							alt="<?php echo esc_attr( $image_alt ); ?>" 
							style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>; max-width: 100%;" 
							decoding="async">
					</a>
				<?php else : ?>
					<!-- Fallback placeholder -->
					<a href="<?php echo esc_url( $link_url ); ?>">
						<img loading="lazy" 
							src="<?php echo kintaelectric_ASSETS_URL; ?>images/placeholder-329x359.png" 
							class="image attachment-full size-full" 
							alt="<?php echo esc_attr( $alt_text ); ?>" 
							style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>; max-width: 100%;" 
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
