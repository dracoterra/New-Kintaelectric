<?php
/**
 * Footer Social Icons Widget
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Footer_Social_Icons_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'footer_social_icons_widget',
			esc_html__( 'Footer Social Icons', 'kintaelectric' ),
			array( 'description' => esc_html__( 'Displays social media icons in the footer.', 'kintaelectric' ) )
		);
	}

	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		?>
		<div class="footer-social-icons">
			<?php if ( ! empty( $instance['title'] ) ) : ?>
				<?php echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title']; ?>
			<?php endif; ?>
			<ul class="social-icons list-unstyled nav align-items-center">
				<?php if ( ! empty( $instance['facebook_url'] ) ) : ?>
					<li><a class="fab fa-facebook" target="_blank" href="<?php echo esc_url( $instance['facebook_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['whatsapp_mobile_url'] ) ) : ?>
					<li><a class="fab fa-whatsapp mobile" target="_blank" href="<?php echo esc_url( $instance['whatsapp_mobile_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['whatsapp_desktop_url'] ) ) : ?>
					<li><a class="fab fa-whatsapp desktop" target="_blank" href="<?php echo esc_url( $instance['whatsapp_desktop_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['pinterest_url'] ) ) : ?>
					<li><a class="fab fa-pinterest" target="_blank" href="<?php echo esc_url( $instance['pinterest_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['linkedin_url'] ) ) : ?>
					<li><a class="fab fa-linkedin" target="_blank" href="<?php echo esc_url( $instance['linkedin_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['instagram_url'] ) ) : ?>
					<li><a class="fab fa-instagram" target="_blank" href="<?php echo esc_url( $instance['instagram_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['youtube_url'] ) ) : ?>
					<li><a class="fab fa-youtube" target="_blank" href="<?php echo esc_url( $instance['youtube_url'] ); ?>"></a></li>
				<?php endif; ?>
				<?php if ( ! empty( $instance['rss_url'] ) ) : ?>
					<li><a class="fas fa-rss" target="_blank" href="<?php echo esc_url( $instance['rss_url'] ); ?>"></a></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$facebook_url = ! empty( $instance['facebook_url'] ) ? $instance['facebook_url'] : 'http://themeforest.net/user/madrasthemes/portfolio';
		$whatsapp_mobile_url = ! empty( $instance['whatsapp_mobile_url'] ) ? $instance['whatsapp_mobile_url'] : 'whatsapp://send?phone=919876543210';
		$whatsapp_desktop_url = ! empty( $instance['whatsapp_desktop_url'] ) ? $instance['whatsapp_desktop_url'] : 'https://web.whatsapp.com/send?phone=919876543210';
		$pinterest_url = ! empty( $instance['pinterest_url'] ) ? $instance['pinterest_url'] : 'http://themeforest.net/user/madrasthemes/portfolio';
		$linkedin_url = ! empty( $instance['linkedin_url'] ) ? $instance['linkedin_url'] : 'http://themeforest.net/user/madrasthemes/portfolio';
		$instagram_url = ! empty( $instance['instagram_url'] ) ? $instance['instagram_url'] : 'http://themeforest.net/user/madrasthemes/portfolio';
		$youtube_url = ! empty( $instance['youtube_url'] ) ? $instance['youtube_url'] : 'http://themeforest.net/user/madrasthemes/portfolio';
		$rss_url = ! empty( $instance['rss_url'] ) ? $instance['rss_url'] : 'feed/index.htm';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'facebook_url' ) ); ?>"><?php esc_attr_e( 'Facebook URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'facebook_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'facebook_url' ) ); ?>" type="url" value="<?php echo esc_attr( $facebook_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'whatsapp_mobile_url' ) ); ?>"><?php esc_attr_e( 'WhatsApp Mobile URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'whatsapp_mobile_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'whatsapp_mobile_url' ) ); ?>" type="url" value="<?php echo esc_attr( $whatsapp_mobile_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'whatsapp_desktop_url' ) ); ?>"><?php esc_attr_e( 'WhatsApp Desktop URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'whatsapp_desktop_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'whatsapp_desktop_url' ) ); ?>" type="url" value="<?php echo esc_attr( $whatsapp_desktop_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'pinterest_url' ) ); ?>"><?php esc_attr_e( 'Pinterest URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'pinterest_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'pinterest_url' ) ); ?>" type="url" value="<?php echo esc_attr( $pinterest_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'linkedin_url' ) ); ?>"><?php esc_attr_e( 'LinkedIn URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'linkedin_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'linkedin_url' ) ); ?>" type="url" value="<?php echo esc_attr( $linkedin_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'instagram_url' ) ); ?>"><?php esc_attr_e( 'Instagram URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'instagram_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'instagram_url' ) ); ?>" type="url" value="<?php echo esc_attr( $instagram_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'youtube_url' ) ); ?>"><?php esc_attr_e( 'YouTube URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'youtube_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'youtube_url' ) ); ?>" type="url" value="<?php echo esc_attr( $youtube_url ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'rss_url' ) ); ?>"><?php esc_attr_e( 'RSS URL:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'rss_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'rss_url' ) ); ?>" type="url" value="<?php echo esc_attr( $rss_url ); ?>">
		</p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['facebook_url'] = ( ! empty( $new_instance['facebook_url'] ) ) ? esc_url_raw( $new_instance['facebook_url'] ) : '';
		$instance['whatsapp_mobile_url'] = ( ! empty( $new_instance['whatsapp_mobile_url'] ) ) ? esc_url_raw( $new_instance['whatsapp_mobile_url'] ) : '';
		$instance['whatsapp_desktop_url'] = ( ! empty( $new_instance['whatsapp_desktop_url'] ) ) ? esc_url_raw( $new_instance['whatsapp_desktop_url'] ) : '';
		$instance['pinterest_url'] = ( ! empty( $new_instance['pinterest_url'] ) ) ? esc_url_raw( $new_instance['pinterest_url'] ) : '';
		$instance['linkedin_url'] = ( ! empty( $new_instance['linkedin_url'] ) ) ? esc_url_raw( $new_instance['linkedin_url'] ) : '';
		$instance['instagram_url'] = ( ! empty( $new_instance['instagram_url'] ) ) ? esc_url_raw( $new_instance['instagram_url'] ) : '';
		$instance['youtube_url'] = ( ! empty( $new_instance['youtube_url'] ) ) ? esc_url_raw( $new_instance['youtube_url'] ) : '';
		$instance['rss_url'] = ( ! empty( $new_instance['rss_url'] ) ) ? esc_url_raw( $new_instance['rss_url'] ) : '';
		return $instance;
	}
}
