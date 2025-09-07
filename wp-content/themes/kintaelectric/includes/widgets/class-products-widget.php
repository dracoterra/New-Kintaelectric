<?php
/**
 * Products Widget for Footer
 *
 * @package kintaelectric
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Products Widget Class
 */
class KintaElectric_Products_Widget extends WP_Widget {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			'kintaelectric_products_widget',
			esc_html__( 'Footer Products Widget', 'kintaelectric' ),
			array(
				'description' => esc_html__( 'Display WooCommerce products in footer with customizable title and product type.', 'kintaelectric' ),
			)
		);
	}

	/**
	 * Widget form
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Featured Products', 'kintaelectric' );
		$product_type = ! empty( $instance['product_type'] ) ? $instance['product_type'] : 'featured';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		$limit = ! empty( $instance['limit'] ) ? $instance['limit'] : 3;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'kintaelectric' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'product_type' ) ); ?>"><?php esc_html_e( 'Product Type:', 'kintaelectric' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'product_type' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'product_type' ) ); ?>">
				<option value="featured" <?php selected( $product_type, 'featured' ); ?>><?php esc_html_e( 'Featured Products', 'kintaelectric' ); ?></option>
				<option value="onsale" <?php selected( $product_type, 'onsale' ); ?>><?php esc_html_e( 'On Sale Products', 'kintaelectric' ); ?></option>
				<option value="top_rated" <?php selected( $product_type, 'top_rated' ); ?>><?php esc_html_e( 'Top Rated Products', 'kintaelectric' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>"><?php esc_html_e( 'Category (optional):', 'kintaelectric' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>">
				<option value=""><?php esc_html_e( 'All Categories', 'kintaelectric' ); ?></option>
				<?php
				if ( class_exists( 'WooCommerce' ) ) {
					$categories = get_terms( array(
						'taxonomy' => 'product_cat',
						'hide_empty' => false,
					) );
					
					if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
						foreach ( $categories as $cat ) {
							echo '<option value="' . esc_attr( $cat->slug ) . '" ' . selected( $category, $cat->slug, false ) . '>' . esc_html( $cat->name ) . '</option>';
						}
					}
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>"><?php esc_html_e( 'Number of products:', 'kintaelectric' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'limit' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'limit' ) ); ?>" type="number" value="<?php echo esc_attr( $limit ); ?>" min="1" max="10">
		</p>
		<?php
	}

	/**
	 * Update widget
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['product_type'] = ( ! empty( $new_instance['product_type'] ) ) ? sanitize_text_field( $new_instance['product_type'] ) : 'featured';
		$instance['category'] = ( ! empty( $new_instance['category'] ) ) ? sanitize_text_field( $new_instance['category'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? absint( $new_instance['limit'] ) : 3;
		
		return $instance;
	}

	/**
	 * Display widget
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Featured Products', 'kintaelectric' );
		$product_type = ! empty( $instance['product_type'] ) ? $instance['product_type'] : 'featured';
		$category = ! empty( $instance['category'] ) ? $instance['category'] : '';
		$limit = ! empty( $instance['limit'] ) ? absint( $instance['limit'] ) : 3;

		echo $args['before_widget'];
		?>
		<aside class="widget clearfix woocommerce widget_products">
			<div class="body">
				<h4 class="widget-title"><?php echo esc_html( $title ); ?></h4>
				<ul class="product_list_widget">
					<?php
					if ( class_exists( 'WooCommerce' ) ) {
						$query_args = array(
							'limit' => $limit,
							'status' => 'publish',
						);

						// Add product type filter
						switch ( $product_type ) {
							case 'featured':
								$query_args['featured'] = true;
								break;
							case 'onsale':
								$query_args['on_sale'] = true;
								break;
							case 'top_rated':
								$query_args['orderby'] = 'rating';
								$query_args['order'] = 'DESC';
								break;
						}

						// Add category filter
						if ( ! empty( $category ) ) {
							$query_args['category'] = array( $category );
						}

						$products = wc_get_products( $query_args );
						
						foreach ( $products as $product ) {
							?>
							<li>
								<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
									<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
									<span class="product-title"><?php echo esc_html( $product->get_name() ); ?></span>
								</a>
								<?php
								if ( $product->get_average_rating() > 0 ) {
									echo wc_get_rating_html( $product->get_average_rating() );
								}
								?>
								<span class="electro-price"><?php echo $product->get_price_html(); ?></span>
							</li>
							<?php
						}
					} else {
						// Fallback content
						?>
						<li>
							<a href="#">
								<img src="<?php echo kintaelectric_ASSETS_URL; ?>images/placeholder-300x300.png" alt="Product" width="300" height="300">
								<span class="product-title"><?php esc_html_e( 'Sample Product 1', 'kintaelectric' ); ?></span>
							</a>
							<span class="electro-price">$99.00</span>
						</li>
						<li>
							<a href="#">
								<img src="<?php echo kintaelectric_ASSETS_URL; ?>images/placeholder-300x300.png" alt="Product" width="300" height="300">
								<span class="product-title"><?php esc_html_e( 'Sample Product 2', 'kintaelectric' ); ?></span>
							</a>
							<span class="electro-price">$149.00</span>
						</li>
						<?php
					}
					?>
				</ul>
			</div>
		</aside>
		<?php
		echo $args['after_widget'];
	}
}

/**
 * Register Products Widget
 */
function kintaelectric_register_products_widget() {
	register_widget( 'KintaElectric_Products_Widget' );
}
add_action( 'widgets_init', 'kintaelectric_register_products_widget' );
