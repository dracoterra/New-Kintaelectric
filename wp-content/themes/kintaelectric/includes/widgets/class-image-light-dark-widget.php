<?php
/**
 * Image Light and Dark Widget for Footer
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Image Light and Dark Widget Class
 */
class KintaElectric_Image_Light_Dark_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'kintaelectric_image_light_dark_widget',
			esc_html__( 'Footer Image Widget Light and Dark', 'kintaelectric' ),
			array(
				'description' => esc_html__( 'Display an image with custom URL link in footer. Supports light and dark mode with separate images.', 'kintaelectric' ),
			)
		);
	}

	/**
	 * Widget form
	 */
	public function form( $instance ) {
		$image_light_id = ! empty( $instance['image_light_id'] ) ? $instance['image_light_id'] : '';
		$image_dark_id = ! empty( $instance['image_dark_id'] ) ? $instance['image_dark_id'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '';
		$alt_text_light = ! empty( $instance['alt_text_light'] ) ? $instance['alt_text_light'] : '';
		$alt_text_dark = ! empty( $instance['alt_text_dark'] ) ? $instance['alt_text_dark'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '329px';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '359px';
		?>
		<p>
			<strong><?php esc_html_e( 'Light Mode Image:', 'kintaelectric' ); ?></strong>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'image_light_id' ) ); ?>"><?php esc_html_e( 'Image (Light Mode):', 'kintaelectric' ); ?></label>
			<div class="kintaelectric-image-widget-container">
				<input type="hidden" class="kintaelectric-image-id" id="<?php echo esc_attr( $this->get_field_id( 'image_light_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_light_id' ) ); ?>" value="<?php echo esc_attr( $image_light_id ); ?>">
				<div class="kintaelectric-image-preview" style="<?php echo empty( $image_light_id ) ? 'display: none;' : ''; ?>">
					<?php if ( ! empty( $image_light_id ) ) : ?>
						<?php echo wp_get_attachment_image( $image_light_id, 'thumbnail' ); ?>
					<?php endif; ?>
				</div>
				<button type="button" class="button kintaelectric-select-image"><?php esc_html_e( 'Select Image', 'kintaelectric' ); ?></button>
				<button type="button" class="button kintaelectric-remove-image" style="<?php echo empty( $image_light_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'kintaelectric' ); ?></button>
			</div>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text_light' ) ); ?>"><?php esc_html_e( 'Alt Text (Light Mode):', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text_light' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text_light' ) ); ?>" type="text" value="<?php echo esc_attr( $alt_text_light ); ?>">
		</p>
		
		<p>
			<strong><?php esc_html_e( 'Dark Mode Image:', 'kintaelectric' ); ?></strong>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'image_dark_id' ) ); ?>"><?php esc_html_e( 'Image (Dark Mode):', 'kintaelectric' ); ?></label>
			<div class="kintaelectric-image-widget-container">
				<input type="hidden" class="kintaelectric-image-id" id="<?php echo esc_attr( $this->get_field_id( 'image_dark_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'image_dark_id' ) ); ?>" value="<?php echo esc_attr( $image_dark_id ); ?>">
				<div class="kintaelectric-image-preview" style="<?php echo empty( $image_dark_id ) ? 'display: none;' : ''; ?>">
					<?php if ( ! empty( $image_dark_id ) ) : ?>
						<?php echo wp_get_attachment_image( $image_dark_id, 'thumbnail' ); ?>
					<?php endif; ?>
				</div>
				<button type="button" class="button kintaelectric-select-image"><?php esc_html_e( 'Select Image', 'kintaelectric' ); ?></button>
				<button type="button" class="button kintaelectric-remove-image" style="<?php echo empty( $image_dark_id ) ? 'display: none;' : ''; ?>"><?php esc_html_e( 'Remove Image', 'kintaelectric' ); ?></button>
			</div>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'alt_text_dark' ) ); ?>"><?php esc_html_e( 'Alt Text (Dark Mode):', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'alt_text_dark' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'alt_text_dark' ) ); ?>" type="text" value="<?php echo esc_attr( $alt_text_dark ); ?>">
		</p>
		
		<p>
			<strong><?php esc_html_e( 'Common Settings:', 'kintaelectric' ); ?></strong>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>"><?php esc_html_e( 'Link URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_url' ) ); ?>" type="url" value="<?php echo esc_attr( $link_url ); ?>">
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
		$instance['image_light_id'] = ( ! empty( $new_instance['image_light_id'] ) ) ? absint( $new_instance['image_light_id'] ) : '';
		$instance['image_dark_id'] = ( ! empty( $new_instance['image_dark_id'] ) ) ? absint( $new_instance['image_dark_id'] ) : '';
		$instance['link_url'] = ( ! empty( $new_instance['link_url'] ) ) ? esc_url_raw( $new_instance['link_url'] ) : '';
		$instance['alt_text_light'] = ( ! empty( $new_instance['alt_text_light'] ) ) ? sanitize_text_field( $new_instance['alt_text_light'] ) : '';
		$instance['alt_text_dark'] = ( ! empty( $new_instance['alt_text_dark'] ) ) ? sanitize_text_field( $new_instance['alt_text_dark'] ) : '';
		$instance['width'] = ( ! empty( $new_instance['width'] ) ) ? sanitize_text_field( $new_instance['width'] ) : '329px';
		$instance['height'] = ( ! empty( $new_instance['height'] ) ) ? sanitize_text_field( $new_instance['height'] ) : '359px';
		
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance ) {
		$image_light_id = ! empty( $instance['image_light_id'] ) ? $instance['image_light_id'] : '';
		$image_dark_id = ! empty( $instance['image_dark_id'] ) ? $instance['image_dark_id'] : '';
		$link_url = ! empty( $instance['link_url'] ) ? $instance['link_url'] : '#';
		$alt_text_light = ! empty( $instance['alt_text_light'] ) ? $instance['alt_text_light'] : '';
		$alt_text_dark = ! empty( $instance['alt_text_dark'] ) ? $instance['alt_text_dark'] : '';
		$width = ! empty( $instance['width'] ) ? $instance['width'] : '329px';
		$height = ! empty( $instance['height'] ) ? $instance['height'] : '359px';

		echo $args['before_widget'];
		?>
		<aside class="widget clearfix widget_media_image widget_image_light_dark">
			<div class="body">
				<?php
				$has_light = ! empty( $image_light_id );
				$has_dark = ! empty( $image_dark_id );
				
				// Light mode image
				if ( $has_light ) {
					$image_light_url = wp_get_attachment_image_url( $image_light_id, 'full' );
					$image_light_alt = ! empty( $alt_text_light ) ? $alt_text_light : get_post_meta( $image_light_id, '_wp_attachment_image_alt', true );
					// If only light image exists, show in both modes (add class for CSS)
					$light_class = $has_dark ? 'footer-image-light' : 'footer-image-light footer-image-both';
					?>
					<a href="<?php echo esc_url( $link_url ); ?>" class="footer-image-link">
						<img loading="lazy" 
							src="<?php echo esc_url( $image_light_url ); ?>" 
							class="image attachment-full size-full <?php echo esc_attr( $light_class ); ?>" 
							alt="<?php echo esc_attr( $image_light_alt ); ?>" 
							style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>; max-width: 100%;" 
							decoding="async">
					</a>
					<?php
				} elseif ( ! $has_dark ) {
					// Fallback placeholder if no images are set
					?>
					<a href="<?php echo esc_url( $link_url ); ?>" class="footer-image-link">
						<img loading="lazy" 
							src="<?php echo kintaelectric_ASSETS_URL; ?>images/placeholder-329x359.png" 
							class="image attachment-full size-full footer-image-light footer-image-both" 
							alt="<?php echo esc_attr( $alt_text_light ); ?>" 
							style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>; max-width: 100%;" 
							decoding="async">
					</a>
					<?php
				}
				
				// Dark mode image
				if ( $has_dark ) {
					$image_dark_url = wp_get_attachment_image_url( $image_dark_id, 'full' );
					$image_dark_alt = ! empty( $alt_text_dark ) ? $alt_text_dark : get_post_meta( $image_dark_id, '_wp_attachment_image_alt', true );
					// If only dark image exists, show in both modes (add class for CSS)
					$dark_class = $has_light ? 'footer-image-dark' : 'footer-image-dark footer-image-both';
					?>
					<a href="<?php echo esc_url( $link_url ); ?>" class="footer-image-link">
						<img loading="lazy" 
							src="<?php echo esc_url( $image_dark_url ); ?>" 
							class="image attachment-full size-full <?php echo esc_attr( $dark_class ); ?>" 
							alt="<?php echo esc_attr( $image_dark_alt ); ?>" 
							style="width: <?php echo esc_attr( $width ); ?>; height: <?php echo esc_attr( $height ); ?>; max-width: 100%;" 
							decoding="async">
					</a>
					<?php
				}
				?>
			</div>
		</aside>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Register Image Light and Dark Widget
 */
function kintaelectric_register_image_light_dark_widget() {
	register_widget( 'KintaElectric_Image_Light_Dark_Widget' );
}
add_action( 'widgets_init', 'kintaelectric_register_image_light_dark_widget' );

