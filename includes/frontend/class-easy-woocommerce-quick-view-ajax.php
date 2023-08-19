<?php

require_once EASY_WOO_QUICK_VIEW_PATH . 'includes/backend/class-easy-woocommerce-quick-settings.php';

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Easy_WooCommerce_Quick_View_Ajax {

	/**
     * The single instance of the class.
     */
    protected static $instance;

    /**
     * Returns single instance of the class
     */
    public static function get_instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

	public function __construct() {

		add_action( 'wp_ajax_easy_woocommerce_quick_view', [$this, 'easy_woocommerce_quick_view'] );
        add_action( 'wp_ajax_nopriv_easy_woocommerce_quick_view', [$this, 'easy_woocommerce_quick_view'] );

		$settings 		= Easy_WooCommerce_Quick_View_Settings::get_settings();
		$title 			= isset($settings['ewqv_title_switch']) ? $settings['ewqv_title_switch'] : '';
		$rating 		= isset($settings['ewqv_rating_switch']) ? $settings['ewqv_rating_switch'] : '';
		$price 			= isset($settings['ewqv_Price_switch']) ? $settings['ewqv_Price_switch'] : '';
		$excerpt 		= isset($settings['ewqv_excerpt_switch']) ? $settings['ewqv_excerpt_switch'] : '';
		$add_to_cart 	= isset($settings['ewqv_add_to_cart_switch']) ? $settings['ewqv_add_to_cart_switch'] : '';
		$meta 			= isset($settings['ewqv_meta_switch']) ? $settings['ewqv_meta_switch'] : '';
		$social_share			= isset($settings['ewqv_social_switch']) ? $settings['ewqv_social_switch'] : '';

		if($title){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_title', 5 );
		}
		if($rating){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_rating', 10 );
		}
		if($price){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_price', 15 );
		}
		if($excerpt){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_excerpt', 20 );
		}
		if($add_to_cart){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_add_to_cart', 25 );
		}
		if($meta){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_meta', 30 );
		}
		if($social_share){
			add_action( 'easy_wqv_product_summary', 'woocommerce_template_single_sharing', 50 );
		}

		add_filter( 'woocommerce_add_to_cart_form_action', array( $this, 'easy_woocommerce_quick_view_avoid_redirecting_to_single_page' ), 10, 1 );
	}

	public function easy_woocommerce_quick_view() {
		$nonce = $_POST['nonce'];
	
		if ( ! wp_verify_nonce( $nonce, 'easy_woocommerce_quick_view_nonce' ) ) {
			die( 'Nonce not verified!' );
		}
	
		global $post, $product;
		$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
		$product = wc_get_product( $product_id );
		$product_name = $product->get_name();
	
		// Get an array of attachment IDs for product images
		$attachment_ids = array();
		
		// Add featured image
		$featured_image_id = get_post_thumbnail_id( $product_id );
		if ( $featured_image_id ) {
			$attachment_ids[] = $featured_image_id;
		}
	
		// Add gallery images
		$gallery_attachment_ids = $product->get_gallery_image_ids();
		if ( ! empty( $gallery_attachment_ids ) ) {
			$attachment_ids = array_merge( $attachment_ids, $gallery_attachment_ids );
		}
	
		// Remove duplicates and re-index the array
		$attachment_ids = array_values( array_unique( $attachment_ids ) );
	
		if ( $product && ! empty( $attachment_ids ) ) {
			$post = get_post( $product_id );
			setup_postdata( $post );
			?>
			<div class="easy-wqv-product-modal">
				<div class="easy-wqv-product-images">
					<div id="easy-wqv-image-slider" class="easy-wqv-product-images-slider">
						<?php foreach ( $attachment_ids as $attachment_id ) : ?>
							<div class="easy-wqv-product-image">
								<img src="<?php echo wp_get_attachment_image_url( $attachment_id, 'full' ); ?>" alt="<?php echo esc_attr( $product_name ); ?>">
							</div>
						<?php endforeach; ?>
					</div>
				</div>
				<div class="summary entry-summary easy-wqv-summary-wrapper">
					<div class="summary-content easy-wqv-summary-content">
						<?php do_action( 'easy_wqv_product_summary'); ?>
					</div>
				</div>
			</div>
			<?php
			wp_reset_postdata();
		}
		wp_die();
	}
	
	/**
	 * Check if is quick view
	 */
	public function easy_woocommerce_quick_view_is_quick_view() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && 'easy_woocommerce_quick_view' === $_REQUEST['action'] );
    }

	/**
	 * Avoid redirect to single product page on add to cart action in quick view
	 */
	public function easy_woocommerce_quick_view_avoid_redirecting_to_single_page($value){
		if ( $this->easy_woocommerce_quick_view_is_quick_view() ) {
            return '';
        }
        return $value;
	}

}
Easy_WooCommerce_Quick_View_Ajax::get_instance();
