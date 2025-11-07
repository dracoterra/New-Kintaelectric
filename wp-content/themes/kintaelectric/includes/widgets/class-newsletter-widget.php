<?php
/**
 * Newsletter Widget for Footer
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Newsletter Widget Class
 */
class KintaElectric_Newsletter_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'kintaelectric_newsletter_widget',
			esc_html__( 'Footer V1 Widget Newsletter', 'kintaelectric' ),
			array(
				'description' => esc_html__( 'Newsletter signup widget for footer with customizable text and Contact Form 7 shortcode support.', 'kintaelectric' ),
			)
		);
	}

	/**
	 * Widget form
	 */
	public function form( $instance ) {
		// Si existe contenido_text, usarlo; si no, intentar migrar desde los campos antiguos
		if ( ! empty( $instance['content_text'] ) ) {
			$content_text = $instance['content_text'];
		} else {
			// Migración: combinar campos antiguos si existen
			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$marketing_text = ! empty( $instance['marketing_text'] ) ? $instance['marketing_text'] : '';
			$coupon_text = ! empty( $instance['coupon_text'] ) ? $instance['coupon_text'] : '';
			$content_text = trim( $title . ' ' . $marketing_text . ' ' . $coupon_text );
			if ( empty( $content_text ) ) {
				$content_text = esc_html__( 'Suscríbete al boletín informativo ...y recibe un cupón de $20 para tu primera compra.', 'kintaelectric' );
			}
		}
		
		$form_shortcode = ! empty( $instance['form_shortcode'] ) ? $instance['form_shortcode'] : '';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'content_text' ) ); ?>"><?php esc_html_e( 'Texto del contenido:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'content_text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'content_text' ) ); ?>" type="text" value="<?php echo esc_attr( $content_text ); ?>" placeholder="<?php esc_attr_e( 'Ejemplo: Suscríbete al boletín informativo ...y recibe un cupón de $20 para tu primera compra.', 'kintaelectric' ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_shortcode' ) ); ?>"><?php esc_html_e( 'Contact Form 7 Shortcode:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'form_shortcode' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'form_shortcode' ) ); ?>" type="text" value="<?php echo esc_attr( $form_shortcode ); ?>" placeholder="[contact-form-7 id='123' title='Newsletter']">
			<small><?php esc_html_e( 'Example: [contact-form-7 id="123" title="Newsletter"]', 'kintaelectric' ); ?></small>
		</p>
		
		<?php
	}

	/**
	 * Update widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['content_text'] = ( ! empty( $new_instance['content_text'] ) ) ? sanitize_text_field( $new_instance['content_text'] ) : '';
		$instance['form_shortcode'] = ( ! empty( $new_instance['form_shortcode'] ) ) ? sanitize_text_field( $new_instance['form_shortcode'] ) : '';
		
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance ) {
		// Si existe content_text, usarlo; si no, intentar migrar desde los campos antiguos
		if ( ! empty( $instance['content_text'] ) ) {
			$content_text = $instance['content_text'];
		} else {
			// Migración: combinar campos antiguos si existen
			$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
			$marketing_text = ! empty( $instance['marketing_text'] ) ? $instance['marketing_text'] : '';
			$coupon_text = ! empty( $instance['coupon_text'] ) ? $instance['coupon_text'] : '';
			$content_text = trim( $title . ' ' . $marketing_text . ' ' . $coupon_text );
			if ( empty( $content_text ) ) {
				$content_text = esc_html__( 'Suscríbete al boletín informativo ...y recibe un cupón de $20 para tu primera compra.', 'kintaelectric' );
			}
		}
		
		$form_shortcode = ! empty( $instance['form_shortcode'] ) ? $instance['form_shortcode'] : '';

		echo $args['before_widget'];
		?>
		<div class="footer-newsletter">
			<div class="container">
				<div class="footer-newsletter-inner row">
					<div class="newsletter-content col-lg-8 d-flex align-items-center">
						<div class="col-12">
							<span class="newsletter-marketing-text">
								<?php echo esc_html( $content_text ); ?>
							</span>
						</div>
					</div>
					<div class="newsletter-form col-lg-4 align-self-center">
						<?php if ( ! empty( $form_shortcode ) ) : ?>
							<!-- Contact Form 7 Shortcode -->
							<div class="wpforms-container wpforms-container-full ec-newsletter-form">
								<?php echo do_shortcode( $form_shortcode ); ?>
							</div>
						<?php else : ?>
							<!-- No form configured message -->
							<div class="no-form-message">
								<p><?php esc_html_e( 'Please configure a Contact Form 7 shortcode in the widget settings.', 'kintaelectric' ); ?></p>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Register Newsletter Widget
 */
function kintaelectric_register_newsletter_widget() {
	register_widget( 'KintaElectric_Newsletter_Widget' );
}
add_action( 'widgets_init', 'kintaelectric_register_newsletter_widget' );

/**
 * Handle Newsletter Form Submission
 */
function kintaelectric_handle_newsletter_signup() {
	// Verify nonce
	if ( ! wp_verify_nonce( $_POST['nonce'], 'newsletter_signup_nonce' ) ) {
		wp_die( 'Security check failed' );
	}

	$email = sanitize_email( $_POST['newsletter_email'] );
	$hp = sanitize_text_field( $_POST['newsletter_hp'] ); // Honeypot field

	// Check honeypot (should be empty)
	if ( ! empty( $hp ) ) {
		wp_die( 'Spam detected' );
	}

	// Validate email
	if ( ! is_email( $email ) ) {
		wp_die( 'Invalid email address' );
	}

	// Here you can add your newsletter integration
	// For example: MailChimp, ConvertKit, etc.
	
	// For now, just store in WordPress options (you can modify this)
	$subscribers = get_option( 'kintaelectric_newsletter_subscribers', array() );
	if ( ! in_array( $email, $subscribers ) ) {
		$subscribers[] = $email;
		update_option( 'kintaelectric_newsletter_subscribers', $subscribers );
		
		// Send success response
		wp_send_json_success( array( 'message' => __( 'Thank you for subscribing!', 'kintaelectric' ) ) );
	} else {
		wp_send_json_error( array( 'message' => __( 'Email already subscribed.', 'kintaelectric' ) ) );
	}
}
add_action( 'wp_ajax_kintaelectric_newsletter_signup', 'kintaelectric_handle_newsletter_signup' );
add_action( 'wp_ajax_nopriv_kintaelectric_newsletter_signup', 'kintaelectric_handle_newsletter_signup' );
