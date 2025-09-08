<?php
/**
 * Footer Social Icons Widget - Dynamic Version
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
			array( 'description' => esc_html__( 'Displays social media icons in the footer with dynamic configuration.', 'kintaelectric' ) )
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
				<?php 
				$social_networks = $this->get_social_networks();
				$social_links = ! empty( $instance['social_links'] ) ? $instance['social_links'] : array();
				
				foreach ( $social_networks as $network => $data ) {
					$is_enabled = ! empty( $social_links[ $network ]['enabled'] ) ? (bool) $social_links[ $network ]['enabled'] : false;
					$url = ! empty( $social_links[ $network ]['url'] ) ? $social_links[ $network ]['url'] : '';
					
					if ( $is_enabled && ! empty( $url ) ) :
				?>
					<li><a class="<?php echo esc_attr( $data['class'] ); ?>" target="_blank" href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $data['name'] ); ?>"></a></li>
				<?php 
					endif;
				}
				?>
			</ul>
		</div>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$social_links = ! empty( $instance['social_links'] ) ? $instance['social_links'] : array();
		$social_networks = $this->get_social_networks();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<h4><?php esc_html_e( 'Social Networks Configuration:', 'kintaelectric' ); ?></h4>
		<div class="social-networks-config">
			<?php foreach ( $social_networks as $network => $data ) : 
				$is_enabled = ! empty( $social_links[ $network ]['enabled'] ) ? (bool) $social_links[ $network ]['enabled'] : false;
				$url = ! empty( $social_links[ $network ]['url'] ) ? $social_links[ $network ]['url'] : '';
			?>
			<div class="social-network-item" style="border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 4px;">
				<p>
					<input class="checkbox" type="checkbox" <?php checked( $is_enabled ); ?> id="<?php echo esc_attr( $this->get_field_id( 'social_links_' . $network . '_enabled' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'social_links[' . $network . '][enabled]' ) ); ?>">
					<label for="<?php echo esc_attr( $this->get_field_id( 'social_links_' . $network . '_enabled' ) ); ?>"><strong><?php echo esc_html( $data['name'] ); ?></strong></label>
				</p>
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'social_links_' . $network . '_url' ) ); ?>"><?php echo esc_html( $data['name'] ); ?> URL:</label>
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'social_links_' . $network . '_url' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'social_links[' . $network . '][url]' ) ); ?>" type="url" value="<?php echo esc_attr( $url ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>">
				</p>
			</div>
			<?php endforeach; ?>
		</div>
		
		<style>
		.social-networks-config {
			max-height: 400px;
			overflow-y: auto;
		}
		.social-network-item {
			background: #f9f9f9;
		}
		.social-network-item:hover {
			background: #f0f0f0;
		}
		</style>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		
		// Process social links
		$social_links = array();
		$social_networks = $this->get_social_networks();
		
		foreach ( $social_networks as $network => $data ) {
			$social_links[ $network ] = array(
				'enabled' => ( ! empty( $new_instance['social_links'][ $network ]['enabled'] ) ) ? (bool) $new_instance['social_links'][ $network ]['enabled'] : false,
				'url' => ( ! empty( $new_instance['social_links'][ $network ]['url'] ) ) ? esc_url_raw( $new_instance['social_links'][ $network ]['url'] ) : '',
			);
		}
		
		$instance['social_links'] = $social_links;
		return $instance;
	}

	/**
	 * Get available social networks configuration
	 */
	private function get_social_networks() {
		return array(
			'facebook' => array(
				'name' => 'Facebook',
				'class' => 'fab fa-facebook',
				'placeholder' => 'https://facebook.com/yourpage'
			),
			'twitter' => array(
				'name' => 'Twitter',
				'class' => 'fab fa-twitter',
				'placeholder' => 'https://twitter.com/yourhandle'
			),
			'instagram' => array(
				'name' => 'Instagram',
				'class' => 'fab fa-instagram',
				'placeholder' => 'https://instagram.com/yourhandle'
			),
			'linkedin' => array(
				'name' => 'LinkedIn',
				'class' => 'fab fa-linkedin',
				'placeholder' => 'https://linkedin.com/in/yourprofile'
			),
			'youtube' => array(
				'name' => 'YouTube',
				'class' => 'fab fa-youtube',
				'placeholder' => 'https://youtube.com/channel/yourchannel'
			),
			'pinterest' => array(
				'name' => 'Pinterest',
				'class' => 'fab fa-pinterest',
				'placeholder' => 'https://pinterest.com/yourprofile'
			),
			'tiktok' => array(
				'name' => 'TikTok',
				'class' => 'fab fa-tiktok',
				'placeholder' => 'https://tiktok.com/@yourhandle'
			),
			'whatsapp_mobile' => array(
				'name' => 'WhatsApp Mobile',
				'class' => 'fab fa-whatsapp mobile',
				'placeholder' => 'whatsapp://send?phone=1234567890'
			),
			'whatsapp_desktop' => array(
				'name' => 'WhatsApp Desktop',
				'class' => 'fab fa-whatsapp desktop',
				'placeholder' => 'https://web.whatsapp.com/send?phone=1234567890'
			),
			'telegram' => array(
				'name' => 'Telegram',
				'class' => 'fab fa-telegram',
				'placeholder' => 'https://t.me/yourhandle'
			),
			'discord' => array(
				'name' => 'Discord',
				'class' => 'fab fa-discord',
				'placeholder' => 'https://discord.gg/yourserver'
			),
			'github' => array(
				'name' => 'GitHub',
				'class' => 'fab fa-github',
				'placeholder' => 'https://github.com/yourusername'
			),
			'rss' => array(
				'name' => 'RSS Feed',
				'class' => 'fas fa-rss',
				'placeholder' => 'https://yoursite.com/feed'
			),
			'email' => array(
				'name' => 'Email',
				'class' => 'fas fa-envelope',
				'placeholder' => 'mailto:your@email.com'
			),
			'phone' => array(
				'name' => 'Phone',
				'class' => 'fas fa-phone',
				'placeholder' => 'tel:+1234567890'
			)
		);
	}
}