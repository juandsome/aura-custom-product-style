<?php
/**
 * Cart Functions
 * WooCommerce Cart Integration
 *
 * @package Aura_Custom_Product_Style
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find a product in the cart
 *
 * Searches the WooCommerce cart for a specific product and returns its cart item key.
 *
 * @param int $product_id Product ID to find
 * @return string|false Cart item key or false if not found
 */
function aura_cps_find_product_in_cart( $product_id ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return false;
	}

	$product_id = intval( $product_id );

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( $cart_item['product_id'] === $product_id ) {
			return $cart_item_key;
		}
	}

	return false;
}

/**
 * Get product quantity in cart
 *
 * Returns the total quantity of a specific product in the cart.
 *
 * @param int $product_id Product ID
 * @return int Quantity in cart (0 if not found)
 */
function aura_cps_get_product_quantity_in_cart( $product_id ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	$product_id = intval( $product_id );
	$total_quantity = 0;

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		if ( $cart_item['product_id'] === $product_id ) {
			$total_quantity += $cart_item['quantity'];
		}
	}

	return $total_quantity;
}

/**
 * Update cart product quantity
 *
 * Main function to add, update, or remove a product from the cart.
 *
 * @param int $product_id Product ID
 * @param int $quantity Desired quantity (0 to remove)
 * @return array Result array with 'success', 'action', and 'message' keys
 */
function aura_cps_update_cart_product( $product_id, $quantity ) {
	// Check if WooCommerce is available
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array(
			'success' => false,
			'message' => esc_html__( 'Cart is not available', 'aura-custom-product-style' ),
		);
	}

	$product_id = intval( $product_id );
	$quantity   = intval( $quantity );

	// Verify product exists and is purchasable
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return array(
			'success' => false,
			'message' => esc_html__( 'Product not found', 'aura-custom-product-style' ),
		);
	}

	if ( ! $product->is_purchasable() ) {
		return array(
			'success' => false,
			'message' => esc_html__( 'Product is not available for purchase', 'aura-custom-product-style' ),
		);
	}

	// Check if product is in cart
	$cart_item_key = aura_cps_find_product_in_cart( $product_id );

	// CASE 1: Remove product from cart
	if ( $quantity === 0 && $cart_item_key ) {
		$removed = WC()->cart->remove_cart_item( $cart_item_key );

		if ( $removed ) {
			WC()->cart->calculate_totals();

			return array(
				'success' => true,
				'action'  => 'removed',
				'message' => esc_html__( 'Product removed from cart', 'aura-custom-product-style' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => esc_html__( 'Could not remove product from cart', 'aura-custom-product-style' ),
			);
		}
	}

	// CASE 2: Update existing cart item quantity
	if ( $cart_item_key ) {
		// Check stock availability
		if ( ! $product->has_enough_stock( $quantity ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: available stock quantity */
					esc_html__( 'Not enough stock. Only %s available.', 'aura-custom-product-style' ),
					$product->get_stock_quantity()
				),
			);
		}

		$updated = WC()->cart->set_quantity( $cart_item_key, $quantity, true );

		if ( $updated ) {
			WC()->cart->calculate_totals();

			return array(
				'success' => true,
				'action'  => 'updated',
				'message' => esc_html__( 'Cart updated', 'aura-custom-product-style' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => esc_html__( 'Could not update cart', 'aura-custom-product-style' ),
			);
		}
	}

	// CASE 3: Add new product to cart
	if ( $quantity > 0 ) {
		// Check stock availability
		if ( ! $product->has_enough_stock( $quantity ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: available stock quantity */
					esc_html__( 'Not enough stock. Only %s available.', 'aura-custom-product-style' ),
					$product->get_stock_quantity()
				),
			);
		}

		// Add custom cart item data if needed
		$cart_item_data = array();

		// Add to cart
		$cart_item_key = WC()->cart->add_to_cart(
			$product_id,
			$quantity,
			0, // Variation ID (0 for simple products)
			array(), // Variation attributes
			$cart_item_data
		);

		if ( $cart_item_key ) {
			WC()->cart->calculate_totals();

			return array(
				'success' => true,
				'action'  => 'added',
				'message' => esc_html__( 'Product added to cart', 'aura-custom-product-style' ),
			);
		} else {
			return array(
				'success' => false,
				'message' => esc_html__( 'Could not add product to cart', 'aura-custom-product-style' ),
			);
		}
	}

	// Fallback
	return array(
		'success' => false,
		'message' => esc_html__( 'Invalid operation', 'aura-custom-product-style' ),
	);
}

/**
 * Get all cart quantities as associative array
 *
 * Returns an array with product IDs as keys and quantities as values.
 *
 * @return array Associative array of product_id => quantity
 */
function aura_cps_get_all_cart_quantities() {
	$quantities = array();

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $quantities;
	}

	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product_id = $cart_item['product_id'];

		if ( ! isset( $quantities[ $product_id ] ) ) {
			$quantities[ $product_id ] = 0;
		}

		$quantities[ $product_id ] += $cart_item['quantity'];
	}

	return $quantities;
}

/**
 * Clear all equipment products from cart
 *
 * Removes all products with a specific category from the cart.
 * Useful when villa changes or is removed.
 *
 * @param string $category Category slug to remove
 * @return int Number of products removed
 */
function aura_cps_clear_category_from_cart( $category = 'rent-equipment' ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return 0;
	}

	$removed_count = 0;
	$cart_items = WC()->cart->get_cart();

	foreach ( $cart_items as $cart_item_key => $cart_item ) {
		$product_id = $cart_item['product_id'];

		if ( has_term( $category, 'product_cat', $product_id ) ) {
			WC()->cart->remove_cart_item( $cart_item_key );
			$removed_count++;
		}
	}

	if ( $removed_count > 0 ) {
		WC()->cart->calculate_totals();
	}

	return $removed_count;
}
