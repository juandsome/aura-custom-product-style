<?php
/**
 * Plugin Name: Aura Custom Product Style
 * Plugin URI: https://collectionaura.com
 * Description: Elementor widgets to display WooCommerce products related to villas in cart with multiple layout options
 * Version: 1.7.2
 * Author: Collection Aura
 * Author URI: https://collectionaura.com
 * License: GPL v2 or later
 * Text Domain: aura-custom-product-style
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'AURA_CPS_VERSION', '1.7.2' );
define( 'AURA_CPS_DIR', plugin_dir_path( __FILE__ ) );
define( 'AURA_CPS_URL', plugin_dir_url( __FILE__ ) );
define( 'AURA_CPS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main Plugin Class - Singleton Pattern
 */
class Aura_Custom_Product_Style {

	/**
	 * Single instance of the class
	 *
	 * @var Aura_Custom_Product_Style
	 */
	private static $instance = null;

	/**
	 * Get single instance of the class
	 *
	 * @return Aura_Custom_Product_Style
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_hooks();
		$this->includes();
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks() {
		// Plugin initialization
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Elementor hooks
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_widget_categories' ) );

		// AJAX hooks
		add_action( 'wp_ajax_aura_cps_update_product_quantity', array( $this, 'ajax_update_product_quantity' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_update_product_quantity', array( $this, 'ajax_update_product_quantity' ) );
		add_action( 'wp_ajax_aura_cps_get_cart_quantities', array( $this, 'ajax_get_cart_quantities' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_get_cart_quantities', array( $this, 'ajax_get_cart_quantities' ) );
		add_action( 'wp_ajax_aura_cps_save_arrival_details', array( $this, 'ajax_save_arrival_details' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_save_arrival_details', array( $this, 'ajax_save_arrival_details' ) );

		// Equipment rental AJAX hooks (NEW - with confirm checkbox)
		add_action( 'wp_ajax_aura_cps_add_equipment_rental', array( $this, 'ajax_add_equipment_rental' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_add_equipment_rental', array( $this, 'ajax_add_equipment_rental' ) );
		add_action( 'wp_ajax_aura_cps_remove_equipment_rental', array( $this, 'ajax_remove_equipment_rental' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_remove_equipment_rental', array( $this, 'ajax_remove_equipment_rental' ) );
		add_action( 'wp_ajax_aura_cps_update_equipment_dates', array( $this, 'ajax_update_equipment_dates' ) );
		add_action( 'wp_ajax_nopriv_aura_cps_update_equipment_dates', array( $this, 'ajax_update_equipment_dates' ) );

		// WooCommerce hooks
		add_action( 'woocommerce_checkout_create_order', array( $this, 'add_arrival_details_to_order' ), 10, 2 );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_arrival_details_in_admin' ), 10, 1 );

		// Equipment rental metadata display hooks
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_equipment_rental_metadata_in_cart' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_equipment_rental_metadata_to_order' ), 10, 4 );
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once AURA_CPS_DIR . 'includes/helpers.php';
		require_once AURA_CPS_DIR . 'includes/cart-functions.php';
	}

	/**
	 * Load plugin textdomain for translations
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'aura-custom-product-style',
			false,
			dirname( AURA_CPS_BASENAME ) . '/languages'
		);
	}

	/**
	 * Enqueue scripts and styles
	 */
	public function enqueue_scripts() {
		// Only enqueue if WooCommerce is active
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		// Enqueue base CSS
		wp_enqueue_style(
			'aura-cps-widget-base',
			AURA_CPS_URL . 'assets/css/widget-base.css',
			array(),
			AURA_CPS_VERSION
		);

		// Enqueue card layout CSS
		wp_enqueue_style(
			'aura-cps-layout-card',
			AURA_CPS_URL . 'assets/css/layout-card.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue checkbox layout CSS
		wp_enqueue_style(
			'aura-cps-layout-checkbox',
			AURA_CPS_URL . 'assets/css/layout-checkbox.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue wine layout CSS
		wp_enqueue_style(
			'aura-cps-layout-wine',
			AURA_CPS_URL . 'assets/css/layout-wine.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue kids corner layout CSS
		wp_enqueue_style(
			'aura-cps-layout-kids',
			AURA_CPS_URL . 'assets/css/layout-kids.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue moments layout CSS
		wp_enqueue_style(
			'aura-cps-layout-moments',
			AURA_CPS_URL . 'assets/css/layout-moments.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue form layout CSS
		wp_enqueue_style(
			'aura-cps-layout-form',
			AURA_CPS_URL . 'assets/css/layout-form.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue equipment rental layout CSS
		wp_enqueue_style(
			'aura-cps-layout-equipment',
			AURA_CPS_URL . 'assets/css/layout-equipment.css',
			array( 'aura-cps-widget-base' ),
			AURA_CPS_VERSION
		);

		// Enqueue Flatpickr CSS (for equipment rental date picker)
		wp_enqueue_style(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css',
			array(),
			'4.6.13'
		);

		// Enqueue Flatpickr JS (for equipment rental date picker)
		wp_enqueue_script(
			'flatpickr',
			'https://cdn.jsdelivr.net/npm/flatpickr',
			array(),
			'4.6.13',
			true
		);

		// Enqueue JavaScript
		wp_enqueue_script(
			'aura-cps-widget-js',
			AURA_CPS_URL . 'assets/js/products-widget.js',
			array( 'jquery' ),
			AURA_CPS_VERSION,
			true
		);

		// Enqueue equipment rental layout JS
		wp_enqueue_script(
			'aura-cps-layout-equipment',
			AURA_CPS_URL . 'assets/js/layout-equipment.js',
			array( 'jquery', 'flatpickr' ),
			AURA_CPS_VERSION,
			true
		);

		// Get cart quantities for initial state
		$cart_quantities = array();
		if ( WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( ! isset( $cart_quantities[ $product_id ] ) ) {
					$cart_quantities[ $product_id ] = 0;
				}
				$cart_quantities[ $product_id ] += $cart_item['quantity'];
			}
		}

		// Localize script
		wp_localize_script(
			'aura-cps-widget-js',
			'auraCpsData',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'aura_cps_nonce' ),
				'cartQuantities' => $cart_quantities,
				'currency'      => get_woocommerce_currency_symbol(),
			)
		);

		// Localize script for equipment layout
		wp_localize_script(
			'aura-cps-layout-equipment',
			'auraCPS',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'aura_cps_nonce' ),
			)
		);
	}

	/**
	 * Register Elementor widgets
	 *
	 * @param object $widgets_manager Elementor widgets manager
	 */
	public function register_elementor_widgets( $widgets_manager ) {
		// Register Products Widget
		require_once AURA_CPS_DIR . 'includes/elementor/class-products-widget.php';
		$widgets_manager->register( new \Aura_CPS\Elementor\Products_Widget() );

		// Register Arrival Form Widget
		require_once AURA_CPS_DIR . 'includes/elementor/class-arrival-form-widget.php';
		$widgets_manager->register( new \Aura_CPS\Elementor\Arrival_Form_Widget() );
	}

	/**
	 * Add custom Elementor widget category
	 *
	 * @param object $elements_manager Elementor elements manager
	 */
	public function add_elementor_widget_categories( $elements_manager ) {
		$elements_manager->add_category(
			'aura-rental',
			array(
				'title' => esc_html__( 'Aura Rental', 'aura-custom-product-style' ),
				'icon'  => 'fa fa-plug',
			)
		);
	}

	/**
	 * AJAX: Update product quantity in cart
	 */
	public function ajax_update_product_quantity() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['quantity'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing required parameters', 'aura-custom-product-style' ),
			) );
		}

		$product_id = intval( $_POST['product_id'] );
		$quantity   = intval( $_POST['quantity'] );

		// Update cart
		$result = aura_cps_update_cart_product( $product_id, $quantity );

		if ( $result['success'] ) {
			// Get updated cart total
			$cart_total = WC()->cart ? WC()->cart->get_cart_total() : '';

			wp_send_json_success( array(
				'quantity'   => $quantity,
				'action'     => $result['action'],
				'cart_total' => $cart_total,
				'message'    => $result['message'],
			) );
		} else {
			wp_send_json_error( array(
				'message' => $result['message'],
			) );
		}
	}

	/**
	 * AJAX: Get current cart quantities
	 */
	public function ajax_get_cart_quantities() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		$cart_quantities = array();
		if ( function_exists( 'WC' ) && WC()->cart ) {
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				$product_id = $cart_item['product_id'];
				if ( ! isset( $cart_quantities[ $product_id ] ) ) {
					$cart_quantities[ $product_id ] = 0;
				}
				$cart_quantities[ $product_id ] += $cart_item['quantity'];
			}
		}

		wp_send_json_success( array(
			'quantities' => $cart_quantities,
		) );
	}

	/**
	 * AJAX: Save arrival details to session
	 */
	public function ajax_save_arrival_details() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		$arrival_details = isset( $_POST['arrival_details'] ) ? sanitize_textarea_field( wp_unslash( $_POST['arrival_details'] ) ) : '';
		$confirmed = isset( $_POST['confirmed'] ) && $_POST['confirmed'] === 'true';

		// Save to WooCommerce session
		if ( function_exists( 'WC' ) && WC()->session ) {
			WC()->session->set( 'aura_arrival_details', $arrival_details );
			WC()->session->set( 'aura_arrival_confirmed', $confirmed );
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => 'Session not available' ) );
		}
	}

	/**
	 * Add arrival details to order as private note
	 *
	 * @param WC_Order $order The order object
	 * @param array    $data  The order data
	 */
	public function add_arrival_details_to_order( $order, $data ) {
		// Check if we have arrival details in session
		if ( ! function_exists( 'WC' ) || ! WC()->session ) {
			return;
		}

		$arrival_details = WC()->session->get( 'aura_arrival_details', '' );
		$confirmed = WC()->session->get( 'aura_arrival_confirmed', false );

		// Only add note if confirmed and has details
		if ( $confirmed && ! empty( $arrival_details ) ) {
			$note = sprintf(
				"Arrival Information:\n\n%s",
				$arrival_details
			);

			// Add as private note (not visible to customer)
			$order->add_order_note( $note, false, false );

			// Also save as order meta for easier access
			$order->update_meta_data( '_aura_arrival_details', $arrival_details );
			$order->save();

			// Clear session data after adding to order
			WC()->session->set( 'aura_arrival_details', '' );
			WC()->session->set( 'aura_arrival_confirmed', false );
		}
	}

	/**
	 * Display arrival details in order admin page
	 *
	 * @param WC_Order $order The order object
	 */
	public function display_arrival_details_in_admin( $order ) {
		$arrival_details = $order->get_meta( '_aura_arrival_details' );

		if ( ! empty( $arrival_details ) ) {
			?>
			<div class="order_data_column" style="clear:both; padding-top: 13px;">
				<h3><?php esc_html_e( 'Arrival Information', 'aura-custom-product-style' ); ?></h3>
				<div style="padding: 12px; background: #f8f8f8; border: 1px solid #ddd; border-radius: 4px;">
					<p style="margin: 0; white-space: pre-wrap; font-family: monospace;"><?php echo esc_html( $arrival_details ); ?></p>
				</div>
			</div>
			<?php
		}
	}

	/**
	 * AJAX: Add equipment rental to cart (NEW - with confirm checkbox logic)
	 * Adds specified quantity with rental dates
	 */
	public function ajax_add_equipment_rental() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['quantity'] ) || ! isset( $_POST['rental_start_date'] ) || ! isset( $_POST['rental_end_date'] ) ) {
			error_log( 'AURA CPS: Missing required parameters in add_equipment_rental' );
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing required parameters', 'aura-custom-product-style' ),
			) );
		}

		$product_id        = intval( $_POST['product_id'] );
		$quantity          = intval( $_POST['quantity'] );
		$rental_start_date = sanitize_text_field( $_POST['rental_start_date'] );
		$rental_end_date   = sanitize_text_field( $_POST['rental_end_date'] );

		error_log( 'AURA CPS: Add rental - Product ID: ' . $product_id . ' | Quantity: ' . $quantity . ' | Dates: ' . $rental_start_date . ' to ' . $rental_end_date );

		if ( $quantity <= 0 ) {
			error_log( 'AURA CPS: Invalid quantity: ' . $quantity );
			wp_send_json_error( array(
				'message' => esc_html__( 'Invalid quantity', 'aura-custom-product-style' ),
			) );
		}

		// Check if item already exists in cart for this product
		$cart_item_key = null;
		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				$cart_item_key = $item_key;
				error_log( 'AURA CPS: Found existing cart item, will update it' );
				break;
			}
		}

		if ( $cart_item_key ) {
			// Update existing item
			WC()->cart->set_quantity( $cart_item_key, $quantity );
			WC()->cart->cart_contents[ $cart_item_key ]['_equipment_rental_start'] = $rental_start_date;
			WC()->cart->cart_contents[ $cart_item_key ]['_equipment_rental_end'] = $rental_end_date;
			WC()->cart->set_session();
			error_log( 'AURA CPS: Updated existing cart item' );
		} else {
			// Add new item
			$cart_item_data = array(
				'_equipment_rental_start' => $rental_start_date,
				'_equipment_rental_end'   => $rental_end_date,
			);

			WC()->cart->add_to_cart(
				$product_id,
				$quantity,
				0,
				array(),
				$cart_item_data
			);
			error_log( 'AURA CPS: Added new cart item' );
		}

		error_log( 'AURA CPS: Add rental successful' );
		wp_send_json_success( array(
			'message' => esc_html__( 'Added to cart', 'aura-custom-product-style' ),
		) );
	}

	/**
	 * AJAX: Remove equipment rental from cart (NEW - with confirm checkbox logic)
	 * Removes the product completely from cart
	 */
	public function ajax_remove_equipment_rental() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) ) {
			error_log( 'AURA CPS: Missing product_id in remove_equipment_rental' );
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing product ID', 'aura-custom-product-style' ),
			) );
		}

		$product_id = intval( $_POST['product_id'] );
		error_log( 'AURA CPS: Remove rental - Product ID: ' . $product_id );

		// Find and remove cart item
		$cart_item_key = null;
		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				$cart_item_key = $item_key;
				break;
			}
		}

		if ( ! $cart_item_key ) {
			error_log( 'AURA CPS: Product ID ' . $product_id . ' not found in cart for removal' );
			wp_send_json_error( array(
				'message' => esc_html__( 'Product not found in cart', 'aura-custom-product-style' ),
			) );
		}

		// Remove from cart
		WC()->cart->remove_cart_item( $cart_item_key );
		error_log( 'AURA CPS: Removed from cart successfully' );

		wp_send_json_success( array(
			'message' => esc_html__( 'Removed from cart', 'aura-custom-product-style' ),
		) );
	}

	/**
	 * AJAX: Increase equipment quantity (adds or updates cart with rental dates)
	 * DEPRECATED - keeping for backwards compatibility
	 */
	public function ajax_increase_equipment() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing product ID', 'aura-custom-product-style' ),
			) );
		}

		$product_id        = intval( $_POST['product_id'] );
		$rental_start_date = isset( $_POST['rental_start_date'] ) ? sanitize_text_field( $_POST['rental_start_date'] ) : '';
		$rental_end_date   = isset( $_POST['rental_end_date'] ) ? sanitize_text_field( $_POST['rental_end_date'] ) : '';

		error_log( 'AURA CPS: Increase request for product ID: ' . $product_id );
		error_log( 'AURA CPS: Dates - Start: "' . $rental_start_date . '" | End: "' . $rental_end_date . '"' );

		// Find existing cart item
		$cart_item_key = null;
		$current_quantity = 0;

		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				// If we have dates, match them exactly
				if ( ! empty( $rental_start_date ) && ! empty( $rental_end_date ) ) {
					if ( isset( $cart_item['_equipment_rental_start'] ) &&
						 $cart_item['_equipment_rental_start'] === $rental_start_date &&
						 isset( $cart_item['_equipment_rental_end'] ) &&
						 $cart_item['_equipment_rental_end'] === $rental_end_date ) {
						$cart_item_key = $item_key;
						$current_quantity = $cart_item['quantity'];
						error_log( 'AURA CPS: Found existing item WITH matching dates, current quantity: ' . $current_quantity );
						break;
					}
				} else {
					// No dates provided, find any item for this product
					$cart_item_key = $item_key;
					$current_quantity = $cart_item['quantity'];
					error_log( 'AURA CPS: Found existing item WITHOUT dates requirement, current quantity: ' . $current_quantity );
					break;
				}
			}
		}

		$new_quantity = $current_quantity + 1;
		error_log( 'AURA CPS: New quantity after increase: ' . $new_quantity );

		if ( $cart_item_key ) {
			// Update existing cart item
			error_log( 'AURA CPS: Updating existing cart item' );
			WC()->cart->set_quantity( $cart_item_key, $new_quantity );
		} else {
			// Add new cart item with rental metadata (if dates provided)
			error_log( 'AURA CPS: Adding NEW cart item' );
			$cart_item_data = array();
			if ( ! empty( $rental_start_date ) && ! empty( $rental_end_date ) ) {
				$cart_item_data = array(
					'_equipment_rental_start' => $rental_start_date,
					'_equipment_rental_end'   => $rental_end_date,
				);
				error_log( 'AURA CPS: Adding with dates metadata' );
			} else {
				error_log( 'AURA CPS: Adding WITHOUT dates metadata' );
			}

			WC()->cart->add_to_cart(
				$product_id,
				1,
				0,
				array(),
				$cart_item_data
			);
		}

		error_log( 'AURA CPS: Increase successful, returning quantity: ' . $new_quantity );
		wp_send_json_success( array(
			'quantity' => $new_quantity,
			'message'  => esc_html__( 'Equipment added to cart', 'aura-custom-product-style' ),
		) );
	}

	/**
	 * AJAX: Decrease equipment quantity (reduces or removes from cart)
	 */
	public function ajax_decrease_equipment() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) ) {
			error_log( 'AURA CPS: Missing product_id in decrease request' );
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing product ID', 'aura-custom-product-style' ),
			) );
		}

		$product_id = intval( $_POST['product_id'] );
		error_log( 'AURA CPS: Decrease request for product ID: ' . $product_id );

		// Find cart item (first one found for this product)
		// Note: We don't require _equipment_rental_start because items can be added without dates
		$cart_item_key = null;
		$current_quantity = 0;

		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				$cart_item_key = $item_key;
				$current_quantity = $cart_item['quantity'];
				error_log( 'AURA CPS: Found product in cart, current quantity: ' . $current_quantity );
				break;
			}
		}

		if ( ! $cart_item_key ) {
			error_log( 'AURA CPS: Product ID ' . $product_id . ' not found in cart for decrease' );
			error_log( 'AURA CPS: Cart contents: ' . print_r( WC()->cart->get_cart(), true ) );
			wp_send_json_error( array(
				'message' => 'Product not found in cart',
			) );
		}

		$new_quantity = max( 0, $current_quantity - 1 );
		error_log( 'AURA CPS: New quantity after decrease: ' . $new_quantity );

		if ( $new_quantity === 0 ) {
			// Remove from cart
			error_log( 'AURA CPS: Removing item from cart' );
			WC()->cart->remove_cart_item( $cart_item_key );
		} else {
			// Update quantity
			error_log( 'AURA CPS: Updating quantity to: ' . $new_quantity );
			WC()->cart->set_quantity( $cart_item_key, $new_quantity );
		}

		error_log( 'AURA CPS: Decrease successful, returning quantity: ' . $new_quantity );
		wp_send_json_success( array(
			'quantity' => $new_quantity,
		) );
	}

	/**
	 * AJAX: Update equipment rental dates
	 */
	public function ajax_update_equipment_dates() {
		// Verify nonce
		check_ajax_referer( 'aura_cps_nonce', 'nonce' );

		// Check required parameters
		if ( ! isset( $_POST['product_id'] ) || ! isset( $_POST['rental_start_date'] ) || ! isset( $_POST['rental_end_date'] ) ) {
			wp_send_json_error( array(
				'message' => esc_html__( 'Missing required parameters', 'aura-custom-product-style' ),
			) );
		}

		$product_id        = intval( $_POST['product_id'] );
		$rental_start_date = sanitize_text_field( $_POST['rental_start_date'] );
		$rental_end_date   = sanitize_text_field( $_POST['rental_end_date'] );

		// Find cart item
		$cart_item_key = null;

		foreach ( WC()->cart->get_cart() as $item_key => $cart_item ) {
			if ( $cart_item['product_id'] === $product_id ) {
				$cart_item_key = $item_key;
				break;
			}
		}

		if ( ! $cart_item_key ) {
			wp_send_json_error();
		}

		// Update cart item metadata
		WC()->cart->cart_contents[ $cart_item_key ]['_equipment_rental_start'] = $rental_start_date;
		WC()->cart->cart_contents[ $cart_item_key ]['_equipment_rental_end'] = $rental_end_date;
		WC()->cart->set_session();

		wp_send_json_success();
	}

	/**
	 * Display equipment rental dates in cart and checkout
	 *
	 * @param array $item_data Existing item data
	 * @param array $cart_item Cart item data
	 * @return array Modified item data
	 */
	public function display_equipment_rental_metadata_in_cart( $item_data, $cart_item ) {
		// Check if this item has rental dates
		if ( isset( $cart_item['_equipment_rental_start'] ) && isset( $cart_item['_equipment_rental_end'] ) ) {
			$start_date = $cart_item['_equipment_rental_start'];
			$end_date = $cart_item['_equipment_rental_end'];

			// Calculate rental days
			$start = new DateTime( $start_date );
			$end = new DateTime( $end_date );
			$diff = $start->diff( $end );
			$days = $diff->days + 1; // Include both start and end dates

			$item_data[] = array(
				'key'   => __( 'Rental Period', 'aura-custom-product-style' ),
				'value' => sprintf(
					__( '%s to %s (%d days)', 'aura-custom-product-style' ),
					$start_date,
					$end_date,
					$days
				),
			);

			error_log( 'AURA CPS: Displaying rental metadata in cart - Start: ' . $start_date . ' | End: ' . $end_date . ' | Days: ' . $days );
		}

		return $item_data;
	}

	/**
	 * Save equipment rental dates to order items
	 *
	 * @param WC_Order_Item_Product $item Order item
	 * @param string $cart_item_key Cart item key
	 * @param array $values Cart item values
	 * @param WC_Order $order Order object
	 */
	public function save_equipment_rental_metadata_to_order( $item, $cart_item_key, $values, $order ) {
		// Check if this item has rental dates
		if ( isset( $values['_equipment_rental_start'] ) && isset( $values['_equipment_rental_end'] ) ) {
			$start_date = $values['_equipment_rental_start'];
			$end_date = $values['_equipment_rental_end'];

			// Calculate rental days
			$start = new DateTime( $start_date );
			$end = new DateTime( $end_date );
			$diff = $start->diff( $end );
			$days = $diff->days + 1; // Include both start and end dates

			// Save as order item meta
			$item->add_meta_data( __( 'Rental Start', 'aura-custom-product-style' ), $start_date, true );
			$item->add_meta_data( __( 'Rental End', 'aura-custom-product-style' ), $end_date, true );
			$item->add_meta_data( __( 'Rental Days', 'aura-custom-product-style' ), $days, true );

			error_log( 'AURA CPS: Saving rental metadata to order item - Start: ' . $start_date . ' | End: ' . $end_date . ' | Days: ' . $days );
		}
	}
}

/**
 * Initialize the plugin
 *
 * @return Aura_Custom_Product_Style
 */
function aura_custom_product_style_init() {
	return Aura_Custom_Product_Style::get_instance();
}

// Initialize on plugins_loaded
add_action( 'plugins_loaded', 'aura_custom_product_style_init' );
