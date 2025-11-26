<?php
/**
 * Helper Functions
 * Villa-Product Relationship Logic
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get villa ID from cart
 *
 * This function searches the WooCommerce cart for an item with a _calendar_id,
 * which indicates a villa booking. It then queries the database to find which
 * villa post has that calendar assigned.
 *
 * @return int|null Villa post ID or null if not found
 */
function aura_cps_get_villa_id_from_cart() {
	// Check if WooCommerce and cart are available
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return null;
	}

	// Loop through cart items looking for villa
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		// Check if item has a calendar ID (indicates villa booking)
		if ( isset( $cart_item['_calendar_id'] ) ) {
			$calendar_id = intval( $cart_item['_calendar_id'] );

			// Query database to find which villa has this calendar
			global $wpdb;
			$villa_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta}
					 WHERE meta_key = '_wpbs_calendar_id'
					 AND meta_value = %d
					 LIMIT 1",
					$calendar_id
				)
			);

			// Return villa ID if found
			if ( $villa_id ) {
				return intval( $villa_id );
			}
		}
	}

	return null;
}

/**
 * Get products related to a specific villa
 *
 * This function queries the JetEngine relations table to find all products
 * that are related to the given villa. It then filters to only include
 * products that have specific categories.
 *
 * @param int $villa_id Villa post ID
 * @param string|array $categories Category slug(s) to filter by (optional)
 * @return array Array of product IDs
 */
function aura_cps_get_related_equipment( $villa_id, $categories = null ) {
	if ( ! $villa_id ) {
		return array();
	}

	global $wpdb;

	// Query JetEngine relations table
	// parent_object_id = Product ID
	// child_object_id = Villa ID
	$related_product_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT parent_object_id
			 FROM {$wpdb->prefix}jet_rel_default
			 WHERE child_object_id = %d",
			$villa_id
		)
	);

	if ( empty( $related_product_ids ) ) {
		return array();
	}

	// If no category filter specified, return all related products
	if ( empty( $categories ) ) {
		return array_map( 'intval', $related_product_ids );
	}

	// Convert single category to array
	if ( ! is_array( $categories ) ) {
		$categories = array( $categories );
	}

	// Filter products by category
	$equipment_ids = array();
	foreach ( $related_product_ids as $product_id ) {
		foreach ( $categories as $category ) {
			if ( has_term( $category, 'product_cat', $product_id ) ) {
				$equipment_ids[] = intval( $product_id );
				break; // Product matched, no need to check other categories
			}
		}
	}

	return $equipment_ids;
}

/**
 * Get equipment products based on widget settings
 *
 * This is the main function that combines villa detection, relationship lookup,
 * and optional filters to return the final list of products to display.
 *
 * @param array $settings Widget settings from Elementor
 * @return array Array of product IDs
 */
function aura_cps_get_products( $settings = array() ) {
	$product_ids = array();

	// Step 1: Get villa ID from cart
	$villa_id = aura_cps_get_villa_id_from_cart();

	if ( ! $villa_id ) {
		return array();
	}

	// Step 2: Get products related to that villa
	$category_filter = ! empty( $settings['product_category'] ) ? $settings['product_category'] : null;
	$related_products = aura_cps_get_related_equipment( $villa_id, $category_filter );

	if ( empty( $related_products ) ) {
		return array();
	}

	// Step 3: Apply optional specific product IDs filter
	if ( ! empty( $settings['product_ids'] ) ) {
		// Parse comma-separated IDs
		$specific_ids = array_map( 'intval', array_map( 'trim', explode( ',', $settings['product_ids'] ) ) );
		$specific_ids = array_filter( $specific_ids ); // Remove empty values

		if ( ! empty( $specific_ids ) ) {
			// Check filter logic
			$filter_logic = ! empty( $settings['filter_logic'] ) ? $settings['filter_logic'] : 'and';

			if ( $filter_logic === 'and' ) {
				// Products must be BOTH related to villa AND in the specific IDs list
				$product_ids = array_intersect( $related_products, $specific_ids );
			} else {
				// Products can be EITHER related to villa OR in the specific IDs list
				$product_ids = array_unique( array_merge( $related_products, $specific_ids ) );
			}
		} else {
			$product_ids = $related_products;
		}
	} else {
		$product_ids = $related_products;
	}

	// Step 4: Apply max products limit if set
	if ( ! empty( $settings['max_products'] ) && intval( $settings['max_products'] ) > 0 ) {
		$product_ids = array_slice( $product_ids, 0, intval( $settings['max_products'] ) );
	}

	// Step 5: Verify all products exist and are published
	$verified_ids = array();
	foreach ( $product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_type( 'simple' ) && $product->get_status() === 'publish' ) {
			$verified_ids[] = $product_id;
		}
	}

	// Step 6: Sort products by menu_order (WooCommerce manual sort order)
	if ( ! empty( $verified_ids ) ) {
		global $wpdb;
		$ids_string = implode( ',', array_map( 'intval', $verified_ids ) );
		$sorted_ids = $wpdb->get_col(
			"SELECT ID FROM {$wpdb->posts}
			 WHERE ID IN ({$ids_string})
			 ORDER BY menu_order ASC, post_title ASC"
		);
		$verified_ids = array_map( 'intval', $sorted_ids );
	}

	return $verified_ids;
}

/**
 * Get WooCommerce product object
 *
 * Wrapper function to get product with error handling
 *
 * @param int $product_id Product ID
 * @return WC_Product|false Product object or false
 */
function aura_cps_get_product( $product_id ) {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return false;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product || ! $product->is_type( 'simple' ) ) {
		return false;
	}

	return $product;
}

/**
 * Get formatted product price
 *
 * @param WC_Product $product Product object
 * @return string Formatted price
 */
function aura_cps_get_product_price( $product ) {
	if ( ! $product ) {
		return '';
	}

	return $product->get_price();
}

/**
 * Get product image URL
 *
 * @param WC_Product $product Product object
 * @param string $size Image size
 * @return string Image URL
 */
function aura_cps_get_product_image_url( $product, $size = 'medium' ) {
	if ( ! $product ) {
		return '';
	}

	$image_id = $product->get_image_id();

	if ( ! $image_id ) {
		return wc_placeholder_img_src( $size );
	}

	$image_url = wp_get_attachment_image_url( $image_id, $size );

	return $image_url ? $image_url : wc_placeholder_img_src( $size );
}
