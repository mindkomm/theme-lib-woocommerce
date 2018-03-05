<?php

namespace Theme\Plugin\WooCommerce;

/**
 * Class Yoast
 *
 * Improves compatibility of WooCommerce with Yoast SEO.
 */
class Yoast {
	/**
	 * Init hooks.
	 */
	public function init() {
		// Optimize WooCommerce products
		add_filter( 'wpseo_opengraph_type', [ $this, 'set_product_type' ] );
		add_action( 'wpseo_opengraph', [ $this, 'add_product_tags' ], 40 );

		// Optimize WooCommerce categories
		add_action( 'wpseo_add_opengraph_additional_images', [ $this, 'load_product_cat_images' ] );
	}

	/**
	 * Filters the OpenGraph type.
	 *
	 * When a product page is displayed, set the opengraph type to 'product'.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/product/
	 *
	 * @param string $type The OpenGraph type.
	 *
	 * @return string The OpenGraph type.
	 */
	public function set_product_type( $type ) {
		if ( is_product() ) {
			return 'product';
		}

		return $type;
	}

	/**
	 * Adds additional OpenGraph price and availability tags for single product views.
	 *
	 * @link https://developers.facebook.com/docs/reference/opengraph/object-type/product/
	 */
	public function add_product_tags() {
		if ( ! is_product() ) {
			return;
		}

		// Get WooCommerce product
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		$price = wc_get_price_to_display( $product );

		if ( ! empty( $price ) ) {
			echo '<meta property="product:price:amount" content="', esc_attr( $price ), '">', "\n";

			$currency = get_woocommerce_currency();

			if ( ! empty( $currency ) ) {
				echo '<meta property="product:price:currency" content="', esc_attr( $currency ), '">', "\n";
			}
		}

		if ( $product->is_in_stock() ) {
			echo '<meta property="product:availability" content="instock">', "\n";
		}
	}

	/**
	 * Adds OpenGraph tags for product category image if no special image was specified in Yoast
	 * settings.
	 *
	 * @param \WPSEO_OpenGraph_Image $opengraph_image An opengraph image object.
	 */
	public function load_product_cat_images( $opengraph_image ) {
		$images = $opengraph_image->get_images();

		// Bailout if images are already set or if it’s not a product category
		if ( ! empty( $images ) || ! is_product_category() ) {
			return;
		}

		$image_id = get_term_meta( get_queried_object_id(), 'thumbnail_id', true );

		if ( ! is_numeric( $image_id ) ) {
			return;
		}

		$image = wp_get_attachment_image_src( $image_id, apply_filters( 'wpseo_opengraph_image_size', 'original' ) );

		if ( ! is_array( $image ) ) {
			return;
		}

		/**
		 * Manually output image tags, because the Yoast’s opengraph image class doesn’t provide
		 * public access to overwrite the dimensions for an image.
		 *
		 * @see WPSEO_OpenGraph_Image::get_featured_image()
		 */
		echo '<meta property="og:image:width" content="', esc_attr( absint( $image[1] ) ), '">', "\n";
		echo '<meta property="og:image:height" content="', esc_attr( absint( $image[2] ) ), '">', "\n";

		$opengraph_image->add_image( $image[0] );
	}
}
